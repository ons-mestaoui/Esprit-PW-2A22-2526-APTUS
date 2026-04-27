<?php
require_once __DIR__ . '/../config.php';

// En haut du fichier, on inclut le fichier des clés secrètes
$keys_path = __DIR__ . '/../api_keys.php';
if (file_exists($keys_path)) {
    require_once $keys_path;
} else {
    die(json_encode(['success' => false, 'message' => 'Fichier de configuration API introuvable.']));
}

class AIController
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
    }

    public function generateSyllabus($titre, $domaine, $niveau)
    {
        if (empty($this->apiKey)) {
            return json_encode(['success' => false, 'message' => 'Clé API Groq manquante.']);
        }

        $endpoint = "https://api.groq.com/openai/v1/chat/completions";

        $prompt = "Tu es un expert en pédagogie pour la plateforme Aptus AI. 
        Génère un syllabus détaillé pour une formation intitulée '$titre' dans le domaine '$domaine' pour un niveau '$niveau'. 
        
        RÉPONDS UNIQUEMENT AU FORMAT JSON avec la structure suivante :
        {
            \"syllabus\": [
                {\"chapitre\": \"Nom du chapitre 1\", \"description\": \"Contenu court du chapitre\", \"duree\": \"1h30\"},
                ...
            ],
            \"resume_global\": \"Une courte introduction captivante pour le cours.\"
        }
        
        Ne rajoute aucune phrase avant ou après le JSON. Sois professionnel et précis.";

        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.7,
            "response_format" => ["type" => "json_object"]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . curl_error($ch)]);
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            $aiText = $result['choices'][0]['message']['content'];
            $decoded = json_decode($aiText, true);

            if ($decoded) {
                return json_encode([
                    'success' => true,
                    'data' => $decoded
                ]);
            } else {
                return json_encode(['success' => false, 'message' => 'L\'IA n\'a pas renvoyé un JSON valide.', 'raw' => $aiText]);
            }
        }

        if (isset($result['error']['message'])) {
            return json_encode(['success' => false, 'message' => 'Erreur API Groq : ' . $result['error']['message']]);
        }

        return json_encode(['success' => false, 'message' => 'Erreur de réponse de l\'IA.', 'raw' => $result]);
    }
    public function analyzeStudentEmotions($stats)
    {
        if (empty($this->apiKey)) {
            return json_encode(['success' => false, 'message' => 'Clé API Groq manquante.']);
        }

        $endpoint = "https://api.groq.com/openai/v1/chat/completions";

        $prompt = "Tu es un Agent IA expert en pédagogie et psychologie cognitive. 
        Ton rôle est d'analyser les statistiques faciales d'un étudiant pendant un cours en ligne et de donner 3 conseils ultra-courts et actionnables au professeur pour l'aider à mieux l'accompagner.
        Voici le bilan de la session de l'étudiant : " . json_encode($stats) . "
        Rédige ton analyse en répondant UNIQUEMENT sous la forme d'un JSON valide avec la structure suivante :
        {
            \"analyse_globale\": \"Un court paragraphe résumant l'état de l'étudiant\",
            \"conseils\": [\"Conseil 1\", \"Conseil 2\", \"Conseil 3\"]
        }
        Ne rajoute aucun texte avant ou après le JSON.";

        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "Tu es un assistant IA qui ne répond strictment qu'en JSON."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.5,
            "response_format" => ["type" => "json_object"]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . curl_error($ch)]);
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            $aiText = $result['choices'][0]['message']['content'];
            $decoded = json_decode($aiText, true);

            if ($decoded) {
                return json_encode([
                    'success' => true,
                    'data' => $decoded
                ]);
            } else {
                return json_encode(['success' => false, 'message' => 'L\'IA n\'a pas renvoyé un JSON valide.', 'raw' => $aiText]);
            }
        }

        if (isset($result['error']['message'])) {
            return json_encode(['success' => false, 'message' => 'Erreur API Groq : ' . $result['error']['message']]);
        }

        return json_encode(['success' => false, 'message' => 'Erreur de réponse de l\'IA.', 'raw' => $result]);
    }

    public function appendSyllabus($id_formation, $html_content)
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
            $stmt->execute(['id' => $id_formation]);
            $row = $stmt->fetch();
            if ($row) {
                $desc = $row['description'];
                
                // Si un syllabus existe déjà, on le remplace
                if (strpos($desc, '<!-- AI_SYLLABUS_START -->') !== false) {
                    $desc = preg_replace('/<!-- AI_SYLLABUS_START -->.*?<!-- AI_SYLLABUS_END -->/s', $html_content, $desc);
                } else {
                    // Sinon on l'insère avant les ressources ou à la fin
                    if (strpos($desc, '<!-- APTUS_RESOURCES:') !== false) {
                        $desc = str_replace('<!-- APTUS_RESOURCES:', $html_content . '<!-- APTUS_RESOURCES:', $desc);
                    } else {
                        $desc .= $html_content;
                    }
                }
                
                $stmtU = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
                $success = $stmtU->execute(['desc' => $desc, 'id' => $id_formation]);
                return json_encode(['success' => $success]);
            }
            return json_encode(['success' => false]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
        }
    }

    public function getEmotionStats($id_candidat, $id_formation)
    {
        try {
            $db = config::getConnexion();
            if ($id_candidat > 0) {
                $stmt = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_candidat = :id_candidat AND id_formation = :id_formation GROUP BY emotion_detectee");
                $stmt->execute(['id_candidat' => $id_candidat, 'id_formation' => $id_formation]);
            } else {
                $stmt = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id_formation GROUP BY emotion_detectee");
                $stmt->execute(['id_formation' => $id_formation]);
            }
            $stats = $stmt->fetchAll();
            return json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
        }
    }

    public function saveStudentEmotion($id_candidat, $id_formation, $emotion)
    {
        if (!$id_candidat || !$emotion) {
            return json_encode(['success' => false, 'message' => 'Données manquantes']);
        }
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("INSERT INTO rapport_emotions (id_candidat, id_formation, emotion_detectee) VALUES (:id_candidat, :id_formation, :emotion)");
            $stmt->execute([
                'id_candidat' => $id_candidat,
                'id_formation' => $id_formation,
                'emotion' => $emotion
            ]);
            return json_encode(['success' => true, 'message' => 'Emotion sauvegardée']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
        }
    }
}
