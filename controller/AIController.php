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
}
