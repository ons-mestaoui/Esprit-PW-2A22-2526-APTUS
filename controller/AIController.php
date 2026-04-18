<?php
require_once __DIR__ . '/../config.php';

class AIController
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    }

    public function generateSyllabus($titre, $domaine, $niveau)
    {
        if (empty($this->apiKey)) {
            return json_encode(['success' => false, 'message' => 'Clé API manquante.']);
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

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
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 8192,
                "responseMimeType" => "application/json"
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['success' => false, 'message' => curl_error($ch)]);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $aiText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Nettoyage agressif des balises Markdown (```json ... ```) si l'IA en a mis
            $aiText = preg_replace('/```json/i', '', $aiText);
            $aiText = preg_replace('/```/i', '', $aiText);
            $aiText = trim($aiText);

            $decoded = json_decode($aiText, true);

            if ($decoded) {
                return json_encode([
                    'success' => true, 
                    'data' => $decoded
                ]);
            } else {
                return json_encode(['success' => false, 'message' => 'L\'IA n\'a pas renvoyé un JSON valide.', 'raw' => $aiText, 'json_error' => json_last_error_msg()]);
            }
        }

        return json_encode(['success' => false, 'message' => 'Erreur de réponse de l\'IA.', 'raw' => $result]);
    }
}
