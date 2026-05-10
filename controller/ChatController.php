<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AIController.php';
require_once __DIR__ . '/NotificationController.php';

class ChatController {

    /**
     * Enregistre le message de l'élève, génère une réponse IA et la sauvegarde.
     */
    public function sendMessage(int $sender_id, int $receiver_id, int $id_formation, string $content): string {
        // Sauvegarder le message de l'étudiant
        $this->saveMessage($sender_id, $receiver_id, $id_formation, $content, false);

        // Générer et retourner la réponse IA
        return $this->generateAIReply($sender_id, $receiver_id, $id_formation, $content);
    }

    /**
     * Persiste un message en base de données.
     */
    private function saveMessage(int $sender, int $receiver, int $id_formation, string $content, bool $isAI): void {
        $db = config::getConnexion();
        $sql = "INSERT INTO messages (sender_id, receiver_id, id_formation, content, is_auto_reply, created_at)
                VALUES (:sid, :rid, :fid, :content, :auto, NOW())";
        $db->prepare($sql)->execute([
            'sid'     => $sender,
            'rid'     => $receiver,
            'fid'     => $id_formation,
            'content' => $content,
            'auto'    => (int)$isAI
        ]);
    }

    /**
     * Génère une réponse IA pédagogique en utilisant le syllabus comme contexte.
     */
    private function generateAIReply(int $student_id, int $tutor_id, int $id_formation, string $student_query): string {
        $db = config::getConnexion();

        // 1. Détecter si l'étudiant demande une fiche de révision
        $fiche_keywords = ['fiche', 'résumé', 'resume', 'révision', 'revision', 'pdf', 'synthèse'];
        $wants_fiche = false;
        foreach ($fiche_keywords as $kw) {
            if (mb_stripos($student_query, $kw) !== false) {
                $wants_fiche = true;
                break;
            }
        }

        // Récupérer le contexte de la formation
        $stmt = $db->prepare("SELECT titre, description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $formation = $stmt->fetch();

        $titre  = $formation['titre']       ?? 'cette formation';
        $syllabus = $formation['description'] ?? '';
        
        if ($wants_fiche) {
            // Logique spéciale : On génère la fiche via l'AIController
            $aiC = new AIController();
            // On récupère l'historique pour une fiche contextuelle
            $history_raw = $this->getHistory($student_id, $tutor_id, $id_formation);
            $history_text = "";
            foreach($history_raw as $msg) {
                $history_text .= ($msg['sender_id'] == $student_id ? "Étudiant: " : "IA: ") . $msg['content'] . "\n\n";
            }
            $history_text .= "Étudiant: " . $student_query; // Ajouter la requête actuelle

            $resFiche = $aiC->generateFicheFromChat($history_text);
            
            if ($resFiche['success']) {
                $reply = "C'est une excellente idée ! J'ai analysé nos échanges sur **$titre** et j'ai préparé votre fiche de révision personnalisée. \n\n[FICHE_READY]{ \"html\": " . json_encode($resFiche['fiche_html']) . " }";
            } else {
                $reply = "Je voulais vous préparer une fiche de révision, mais j'ai eu un petit souci technique. Posez-moi encore une ou deux questions pour que j'aie assez de matière !";
            }
        } else {
            // Logique standard : Réponse pédagogique
            if (mb_strlen($syllabus) > 2000) {
                $syllabus = mb_substr(strip_tags($syllabus), 0, 2000) . '...';
            }

            $prompt = "Tu es un assistant pédagogique bienveillant pour la plateforme Aptus AI.
Tu aides les étudiants inscrits à la formation : **$titre**.

Voici un extrait du programme du cours :
$syllabus

Question de l'étudiant : $student_query

Réponds de façon claire, encourageante et pédagogique. Si la question sort du cadre du cours, oriente gentiment l'étudiant vers son tuteur humain. Maximum 3 paragraphes courts.";

            $reply = $this->callAI($prompt);
        }

        // Préfixe visuel pour indiquer que c'est l'IA
        $prefixed = "🤖 L'assistant IA du tuteur :\n\n" . $reply;

        // Sauvegarder la réponse IA dans les messages
        $this->saveMessage($tutor_id, $student_id, $id_formation, $prefixed, true);

        // Notifier l'étudiant
        NotificationController::creerNotification(
            $student_id,
            'new_message',
            "L'assistant IA de votre tuteur a répondu à votre question sur « $titre ».",
            "formation_viewer.php?id=$id_formation",
            'message-circle'
        );

        return $prefixed;
    }

    /**
     * Appel à l'IA via le contrôleur centralisé (Failover & Rotation inclus).
     */
    private function callAI(string $prompt): string {
        $aiC = new AIController();
        $data = [
            "model"    => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => $prompt]],
            "temperature" => 0.6,
            "max_tokens"  => 500
        ];
        
        $res = $aiC->generateGenericResponse($data);
        return $res['success'] ? $res['content'] : "Désolé, l'assistant IA est temporairement indisponible.";
    }

    /**
     * Récupère l'historique complet d'une conversation.
     */
    public function getHistory(int $user1, int $user2, int $id_formation): array {
        $db  = config::getConnexion();
        $sql = "SELECT m.*, 
                       TIMESTAMPDIFF(MINUTE, m.created_at, NOW()) AS age_minutes
                FROM messages m
                WHERE ((m.sender_id = :u1 AND m.receiver_id = :u2)
                    OR (m.sender_id = :u2b AND m.receiver_id = :u1b))
                  AND (m.id_formation = :fid OR :fid2 = 0)
                ORDER BY m.created_at ASC
                LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'u1' => $user1, 'u2' => $user2,
            'u2b' => $user2, 'u1b' => $user1,
            'fid' => $id_formation, 'fid2' => $id_formation
        ]);
        return $stmt->fetchAll();
    }
}
