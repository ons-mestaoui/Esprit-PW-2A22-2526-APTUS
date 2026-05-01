<?php
require_once __DIR__ . '/../config.php';

class AIController {
    private $apiKey;
    private $apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    private $model = "llama-3.3-70b-versatile";

    public function __construct() {
        // Load API Key from .env manually if not already loaded by a framework
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $_ENV[trim($name)] = trim($value);
                }
            }
        }

        $this->apiKey = $_ENV['GROQ_API_KEY'] ?? ''; 
        if (empty($this->apiKey) && defined('GROQ_API_KEY')) {
            $this->apiKey = GROQ_API_KEY;
        }
    }

    private function callGroq(string $payload): string {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return json_encode(['error' => "Erreur CURL : $error"]);
        }

        if ($httpCode !== 200) {
            $res = json_decode($response, true);
            $msg = $res['error']['message'] ?? "Erreur HTTP $httpCode";
            return json_encode(['error' => "Erreur API Groq : $msg"]);
        }

        return $response;
    }

    public function analyzeCV(string $cvText, string $additionalContext = ""): string {
        if (empty(trim($cvText)) || empty($this->apiKey)) {
            return json_encode(['error' => 'Texte vide ou API Key manquante.']);
        }

        $systemPrompt = "Tu es un Auditeur de Carrière Senior. Analyse le CV et retourne UNIQUEMENT un objet JSON. 
        Structure : { 
          \"score_ats\": (0-100), 
          \"sub_scores\": { \"structure\": 0-100, \"content_quality\": 0-100, \"keyword_relevance\": 0-100, \"impact_metrics\": 0-100 },
          \"market_positioning\": { \"percentile\": 0-100, \"demand_level\": \"Élevée\"|\"Moyenne\"|\"Faible\", \"salary_estimate\": \"...\" },
          \"score_explanation\": \"...\",
          \"points_forts\": [], \"points_faibles\": [], \"detailed_recommendations\": [], \"keywords\": [], \"missing_skills\": [], \"suggested_training_domains\": []
        }";

        $payload = json_encode([
            "model" => $this->model,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "CV à analyser :\n" . $cvText]
            ],
            "temperature" => 0.2,
            "response_format" => ["type" => "json_object"]
        ]);

        $response = $this->callGroq($payload);
        $data = json_decode($response, true);
        
        if (isset($data['error'])) return $response;

        $content = $data['choices'][0]['message']['content'] ?? '';
        
        // Robust JSON extraction
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            return $matches[0];
        }
        
        return $content ?: json_encode(['error' => 'Réponse vide de l\'IA']);
    }

    public function polishText(string $text, string $context, string $mode = 'polish'): string {
        // Mock or implement similarly if needed
        return $text;
    }
}
