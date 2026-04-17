<?php
/**
 * ============================================================
 * PeerLearningController — Concept 1 : Matchmaker de Peer-Learning
 * ============================================================
 * Objectif : connecter un étudiant en difficulté avec un étudiant
 * expert de la même formation (progression = 100%).
 *
 * Méthodes publiques :
 *   - trouverMentor($id_formation, $id_demandeur) → retourne un tableau
 *     avec les infos du mentor et un lien Jitsi généré, ou null si aucun.
 *   - handleAjax() → point d'entrée HTTP pour les requêtes AJAX POST.
 */
require_once __DIR__ . '/../config.php';

class PeerLearningController
{
    // ----------------------------------------------------------
    // MÉTHODE PRINCIPALE : Trouver un mentor disponible
    // ----------------------------------------------------------

    /**
     * Cherche dans la table 'inscription' un utilisateur ayant 100%
     * de progression sur la formation demandée, en excluant le demandeur.
     *
     * @param int $id_formation  L'ID de la formation concernée.
     * @param int $id_demandeur  L'ID de l'étudiant qui demande de l'aide.
     * @return array|null  Tableau ['mentor' => [...], 'jitsi_link' => '...'] ou null.
     */
    public function trouverMentor(int $id_formation, int $id_demandeur): ?array
    {
        $db = config::getConnexion();

        // Requête préparée PDO avec JOIN pour récupérer les infos du mentor
        // On cherche un inscrit avec 100% de progression, différent du demandeur
        // NOTE D'INTÉGRATION : Le module "Utilisateur" utilise la table 'candidat' pour les étudiants
        $sql = "
            SELECT i.id_user,
                   COALESCE(c.nom, CONCAT('Étudiant #', i.id_user)) AS mentor_nom,
                   COALESCE(c.email, '') AS mentor_email
            FROM inscription i
            LEFT JOIN candidat c ON i.id_user = c.id
            WHERE i.id_formation = :id_formation
              AND i.id_user != :id_demandeur
              AND i.progression >= 100
            ORDER BY RAND()
            LIMIT 1
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_formation'  => $id_formation,
                'id_demandeur'  => $id_demandeur
            ]);
            $mentor = $stmt->fetch();
        } catch (\Exception $e) {
            // Fallback : essai avec la table 'Inscription' (casse alternative) et 'utilisateur' si 'candidat' n'est pas encore prêt
            try {
                $sqlFallback = "
                    SELECT i.id_user,
                           COALESCE(u.nom, CONCAT('Étudiant #', i.id_user)) AS mentor_nom,
                           COALESCE(u.email, '') AS mentor_email
                    FROM Inscription i
                    LEFT JOIN utilisateur u ON i.id_user = u.id
                    WHERE i.id_formation = :id_formation
                      AND i.id_user != :id_demandeur
                      AND i.progression >= 100
                    ORDER BY RAND()
                    LIMIT 1
                ";
                $stmt = $db->prepare($sqlFallback);
                $stmt->execute([
                    'id_formation'  => $id_formation,
                    'id_demandeur'  => $id_demandeur
                ]);
                $mentor = $stmt->fetch();
            } catch (\Exception $e2) {
                return null; // Impossible de chercher → on renvoie null
            }
        }

        // Aucun mentor trouvé ? On retourne null
        if (!$mentor) {
            return null;
        }

        // On récupère aussi le nom de la formation pour le lien Jitsi
        $titreFormation = $this->getTitreFormation($id_formation);

        return [
            'mentor'      => $mentor,
            'jitsi_link'  => $this->generateJitsiLink($titreFormation, $id_formation)
        ];
    }

    // ----------------------------------------------------------
    // MÉTHODE PRIVÉE : Génère un lien Jitsi unique et aléatoire
    // ----------------------------------------------------------

    /**
     * Génère un lien Jitsi Meet reproductible sur la session mais unique
     * par formation + timestamp, garantissant une room "fraîche" à chaque demande.
     *
     * Format : https://meet.jit.si/Aptus-PeerHelp-<slug>-<uniqid>
     *
     * @param string $titre       Titre de la formation (pour le slug lisible).
     * @param int    $id_formation ID de la formation.
     * @return string  URL Jitsi complète.
     */
    private function generateJitsiLink(string $titre, int $id_formation): string
    {
        // Nettoyage du titre pour créer un slug URL-safe
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        $slug = trim($slug, '-');

        // uniqid() garantit l'unicité même si deux demandes arrivent la même seconde
        $roomId = 'Aptus-PeerHelp-' . $slug . '-' . $id_formation . '-' . uniqid();

        return 'https://meet.jit.si/' . $roomId;
    }

    // ----------------------------------------------------------
    // MÉTHODE PRIVÉE : Récupère le titre d'une formation
    // ----------------------------------------------------------
    private function getTitreFormation(int $id_formation): string
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT titre FROM Formation WHERE id_formation = :id");
            $stmt->execute(['id' => $id_formation]);
            return $stmt->fetchColumn() ?: 'formation';
        } catch (\Exception $e) {
            return 'formation';
        }
    }

    // ----------------------------------------------------------
    // POINT D'ENTRÉE AJAX : Gère les requêtes HTTP POST
    // ----------------------------------------------------------

    /**
     * Handler AJAX : reçoit un POST avec 'id_formation' et renvoie du JSON.
     * À inclure dans index.php via une route comme ?action=peer_help
     *
     * Réponse JSON succès  : { "success": true, "mentor": {...}, "jitsi_link": "..." }
     * Réponse JSON échec   : { "success": false, "message": "..." }
     */
    public function handleAjax(): void
    {
        // Sécurité : on accepte uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        // Validation des paramètres requis
        $id_formation = isset($_POST['id_formation']) ? (int)$_POST['id_formation'] : 0;
        // On utilise la session (ou fallback 10 pour demo)
        $id_demandeur = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 10;

        if ($id_formation <= 0) {
            echo json_encode(['success' => false, 'message' => 'Formation invalide.']);
            return;
        }

        // Appel de la méthode principale
        $result = $this->trouverMentor($id_formation, $id_demandeur);

        header('Content-Type: application/json');

        if ($result) {
            echo json_encode([
                'success'    => true,
                'mentor'     => $result['mentor'],
                'jitsi_link' => $result['jitsi_link']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun expert disponible pour cette formation pour le moment. Réessayez plus tard !'
            ]);
        }
    }
}
