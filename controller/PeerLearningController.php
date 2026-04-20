<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/NotificationController.php';

class PeerLearningController
{
    private const MAX_REQUESTS_PER_DAY = 3;
    private const SESSION_TIMEOUT_MIN  = 30;

    // ──────────────────────────────────────────────────────────────
    // Smart Matching
    // ──────────────────────────────────────────────────────────────
    public function trouverMentor(int $id_formation, int $id_demandeur): ?array
    {
        // 1. Vérifier la limite journalière du demandeur
        if ($this->hasExceededDailyLimit($id_demandeur)) {
            return ['error' => 'daily_limit', 'message' => "Vous avez atteint votre limite de " . self::MAX_REQUESTS_PER_DAY . " demandes par jour."];
        }

        // 2. Auto-annuler les sessions en attente depuis trop longtemps
        $this->cancelStaleRequests();

        $db = config::getConnexion();

        // 3. Smart Matching :
        //    - 100% de progression
        //    - Trie par note moyenne DESC (meilleurs mentors en premier, les NULL en dernier)
        //    - Secondairement, trie par charge de travail ASC (moins de sessions ouvertes)
        //    - Évite ceux déjà en session pending
        $sql = "
            SELECT i.id_user,
                   COALESCE(c.nom, CONCAT('Étudiant #', i.id_user)) AS mentor_nom,
                   COALESCE(c.email, '') AS mentor_email,
                   (SELECT AVG(pr.rating)
                    FROM peer_reviews pr
                    JOIN peer_sessions ps ON pr.session_id = ps.id
                    WHERE ps.mentor_id = i.id_user) AS avg_rating,
                   (SELECT COUNT(*) FROM peer_sessions WHERE mentor_id = i.id_user AND status = 'pending') AS active_sessions
            FROM inscription i
            LEFT JOIN candidat c ON i.id_user = c.id
            WHERE i.id_formation = :id_formation
              AND i.id_user != :id_demandeur
              AND i.progression >= 100
              AND i.id_user NOT IN (
                    SELECT mentor_id FROM peer_sessions
                    WHERE status = 'pending'
                    AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) < :timeout
              )
            ORDER BY avg_rating DESC,
                     active_sessions ASC,
                     RAND()
            LIMIT 1
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_formation' => $id_formation,
                'id_demandeur' => $id_demandeur,
                'timeout'      => self::SESSION_TIMEOUT_MIN
            ]);
            $mentor = $stmt->fetch();

            // Fallback avec table utilisateur si candidat n'est pas disponible
            if (!$mentor) {
                $sqlFb = "
                    SELECT i.id_user,
                           COALESCE(u.nom, CONCAT('Étudiant #', i.id_user)) AS mentor_nom,
                           '' AS mentor_email, NULL AS avg_rating, 0 AS active_sessions
                    FROM inscription i
                    LEFT JOIN utilisateur u ON i.id_user = u.id
                    WHERE i.id_formation = :id_formation
                      AND i.id_user != :id_demandeur
                      AND i.progression >= 100
                    ORDER BY RAND() LIMIT 1";
                $stmtFb = $db->prepare($sqlFb);
                $stmtFb->execute(['id_formation' => $id_formation, 'id_demandeur' => $id_demandeur]);
                $mentor = $stmtFb->fetch();
            }
        } catch (\Exception $e) {
            error_log("[PeerLearning] Erreur SQL : " . $e->getMessage());
            return null;
        }

        if (!$mentor) return null;

        $titreFormation = $this->getTitreFormation($id_formation);
        $jitsiLink      = $this->generateJitsiLink($titreFormation, $id_formation);

        // 4. Enregistrer la session
        $stmtS = $db->prepare("INSERT INTO peer_sessions (formation_id, requester_id, mentor_id, meeting_link, status, created_at)
                                VALUES (:fid, :rid, :mid, :link, 'pending', NOW())");
        $stmtS->execute([
            'fid'  => $id_formation,
            'rid'  => $id_demandeur,
            'mid'  => $mentor['id_user'],
            'link' => $jitsiLink
        ]);
        $sessionId = $db->lastInsertId();

        // 5. Notifier le mentor
        NotificationController::creerNotification(
            $mentor['id_user'],
            'peer_request',
            "🎓 Vous avez été sélectionné comme mentor pour « $titreFormation » ! Un étudiant a besoin de vous.",
            $jitsiLink,
            'users'
        );

        // 6. Retourner les infos complètes
        return [
            'mentor'     => [
                'id'         => $mentor['id_user'],
                'nom'        => $mentor['mentor_nom'],
                'email'      => $mentor['mentor_email'],
                'avg_rating' => $mentor['avg_rating'] ? round($mentor['avg_rating'], 1) : null,
            ],
            'jitsi_link' => $jitsiLink,
            'session_id' => $sessionId
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // Review d'une session terminée
    // ──────────────────────────────────────────────────────────────
    public function submitReview(int $session_id, int $rating, string $comment): bool
    {
        $rating = max(1, min(5, $rating));
        $db = config::getConnexion();

        $stmt = $db->prepare("INSERT INTO peer_reviews (session_id, rating, comment, created_at)
                              VALUES (:sid, :rat, :com, NOW())");
        $ok = $stmt->execute(['sid' => $session_id, 'rat' => $rating, 'com' => $comment]);

        if ($ok) {
            $db->prepare("UPDATE peer_sessions SET status = 'completed' WHERE id = :sid")
               ->execute(['sid' => $session_id]);
        }
        return $ok;
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers privés
    // ──────────────────────────────────────────────────────────────
    private function hasExceededDailyLimit(int $user_id): bool
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM peer_sessions
                               WHERE requester_id = :uid
                               AND DATE(created_at) = CURDATE()");
        $stmt->execute(['uid' => $user_id]);
        return (int)$stmt->fetchColumn() >= self::MAX_REQUESTS_PER_DAY;
    }

    private function cancelStaleRequests(): void
    {
        $db = config::getConnexion();
        $db->prepare("UPDATE peer_sessions SET status = 'cancelled'
                      WHERE status = 'pending'
                      AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > :timeout")
           ->execute(['timeout' => self::SESSION_TIMEOUT_MIN]);
    }

    private function generateJitsiLink(string $titre, int $id_formation): string
    {
        $slug   = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        $roomId = 'Aptus-Peer-' . trim($slug, '-') . '-' . $id_formation . '-' . uniqid();
        return 'https://meet.jit.si/' . $roomId;
    }

    private function getTitreFormation(int $id_formation): string
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT titre FROM formation WHERE id_formation = :id");
            $stmt->execute(['id' => $id_formation]);
            return $stmt->fetchColumn() ?: 'formation';
        } catch (\Exception $e) {
            return 'formation';
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Point d'entrée AJAX
    // ──────────────────────────────────────────────────────────────
    public function handleAjax(): void
    {
        header('Content-Type: application/json');
        $id_formation = (int)($_POST['id_formation'] ?? 0);
        $id_demandeur = (int)($_POST['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10);

        if ($id_formation <= 0) {
            echo json_encode(['success' => false, 'message' => 'Formation manquante.']);
            return;
        }

        $result = $this->trouverMentor($id_formation, $id_demandeur);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Aucun expert disponible pour le moment. Réessayez plus tard !']);
            return;
        }

        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }

        echo json_encode([
            'success'    => true,
            'mentor'     => $result['mentor'],
            'jitsi_link' => $result['jitsi_link'],
            'session_id' => $result['session_id']
        ]);
    }
}
