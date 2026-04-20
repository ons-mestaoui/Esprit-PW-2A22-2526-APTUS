<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AIController.php';
require_once __DIR__ . '/NotificationController.php';

class ChatController {

    /**
     * Enregistre le message de l'élève, génère une réponse IA et la sauvegarde.
     */
    public function sendMessage(int $sender_id, int $receiver_id, int $formation_id, string $content): string {
        // Sauvegarder le message de l'étudiant
        $this->saveMessage($sender_id, $receiver_id, $formation_id, $content, false);

        // Générer et retourner la réponse IA
        return $this->generateAIReply($sender_id, $receiver_id, $formation_id, $content);
    }

    /**
     * Persiste un message en base de données.
     */
    private function saveMessage(int $sender, int $receiver, int $formation_id, string $content, bool $isAI): void {
        $db = config::getConnexion();
        $sql = "INSERT INTO messages (sender_id, receiver_id, formation_id, content, is_auto_reply, created_at)
                VALUES (:sid, :rid, :fid, :content, :auto, NOW())";
        $db->prepare($sql)->execute([
            'sid'     => $sender,
            'rid'     => $receiver,
            'fid'     => $formation_id,
            'content' => $content,
            'auto'    => (int)$isAI
        ]);
    }

    /**
     * Génère une réponse IA pédagogique en utilisant le syllabus comme contexte.
     */
    private function generateAIReply(int $student_id, int $tutor_id, int $formation_id, string $student_query): string {
        $db = config::getConnexion();

        // Récupérer le contexte de la formation
        $stmt = $db->prepare("SELECT titre, description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $formation_id]);
        $formation = $stmt->fetch();

        $titre  = $formation['titre']       ?? 'cette formation';
        $syllabus = $formation['description'] ?? '';
        // Tronquer le syllabus si trop long (évite de dépasser les tokens Groq)
        if (mb_strlen($syllabus) > 2000) {
            $syllabus = mb_substr(strip_tags($syllabus), 0, 2000) . '...';
        }

        $prompt = "Tu es un assistant pédagogique bienveillant pour la plateforme Aptus AI.
Tu aides les étudiants inscrits à la formation : **$titre**.

Voici un extrait du programme du cours :
$syllabus

Question de l'étudiant : $student_query

Réponds de façon claire, encourageante et pédagogique. Si la question sort du cadre du cours, oriente gentiment l'étudiant vers son tuteur humain. Maximum 3 paragraphes courts.";

        $reply = $this->callGroq($prompt);

        // Préfixe visuel pour indiquer que c'est l'IA
        $prefixed = "🤖 L'assistant IA du tuteur :\n\n" . $reply;

        // Sauvegarder la réponse IA dans les messages
        $this->saveMessage($tutor_id, $student_id, $formation_id, $prefixed, true);

        // Notifier l'étudiant
        NotificationController::creerNotification(
            $student_id,
            'new_message',
            "L'assistant IA de votre tuteur a répondu à votre question sur « $titre ».",
            "formation_viewer.php?id=$formation_id",
            'message-circle'
        );

        return $prefixed;
    }

    /**
     * Appel direct à l'API Groq (centralisé, pas de duplication).
     */
    private function callGroq(string $prompt): string {
        if (!defined('GROQ_API_KEY')) {
            $keys_path = __DIR__ . '/../api_keys.php';
            if (file_exists($keys_path)) require_once $keys_path;
        }

        if (!defined('GROQ_API_KEY')) {
            return "Clé API manquante. Veuillez configurer GROQ_API_KEY.";
        }

        $data = [
            "model"    => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => $prompt]],
            "temperature" => 0.6,
            "max_tokens"  => 500
        ];

        $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . GROQ_API_KEY
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        if ($errno) return "Erreur de connexion à l'IA. Réessayez.";

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content']
            ?? "Désolé, je n'ai pas pu générer une réponse.";
    }

    /**
     * Récupère l'historique complet d'une conversation.
     */
    public function getHistory(int $user1, int $user2, int $formation_id): array {
        $db  = config::getConnexion();
        $sql = "SELECT m.*, 
                       TIMESTAMPDIFF(MINUTE, m.created_at, NOW()) AS age_minutes
                FROM messages m
                WHERE ((m.sender_id = :u1 AND m.receiver_id = :u2)
                    OR (m.sender_id = :u2b AND m.receiver_id = :u1b))
                  AND (m.formation_id = :fid OR :fid2 = 0)
                ORDER BY m.created_at ASC
                LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'u1' => $user1, 'u2' => $user2,
            'u2b' => $user2, 'u1b' => $user1,
            'fid' => $formation_id, 'fid2' => $formation_id
        ]);
        return $stmt->fetchAll();
    }
}
