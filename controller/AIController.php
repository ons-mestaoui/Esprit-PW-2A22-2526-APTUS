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

    public function generateJSON(string $prompt, string $userInput = ""): array {
        if (empty($this->apiKey)) {
            throw new Exception("Clé API Groq manquante.");
        }

        $payload = json_encode([
            "model" => $this->model,
            "messages" => [
                ["role" => "system", "content" => $prompt],
                ["role" => "user", "content" => $userInput]
            ],
            "temperature" => 0.1,
            "response_format" => ["type" => "json_object"]
        ]);

        $response = $this->callGroq($payload);
        $data = json_decode($response, true);
        
        if (isset($data['error'])) {
            throw new Exception($data['error']);
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            return json_decode($matches[0], true);
        }
        
        $json = json_decode($content, true);
        if (!$json) {
            throw new Exception("L'IA n'a pas renvoyé un JSON valide.");
        }
        return $json;
    }

    public function translateCV(array $cvData, string $targetLang): array {
        $prompt = "Tu es un expert en recrutement international. Traduis le contenu de ce CV en $targetLang. 
        IMPORTANT : 
        1. Utilise la terminologie professionnelle correcte pour le pays cible (ex: en anglais, utilise 'Internship' pour stage).
        2. Garde TOUTE la structure HTML (balises <strong>, <ul>, etc.) intacte.
        3. Renvoie UNIQUEMENT l'objet JSON traduit.
        4. Si la langue est l'Arabe, assure-toi que le texte est adapté pour une lecture de droite à gauche si nécessaire (mais garde les clés JSON en anglais).";

        return $this->generateJSON($prompt, json_encode($cvData));
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
        if ($mode === 'correct') {
            $systemPrompt = "Tu es un correcteur linguistique expert. Corrige les fautes d'orthographe et de grammaire. 
            IMPORTANT : Renvoie le texte en Français, SANS AUCUNE BALISE HTML. 
            Si le texte est long, synthétise-le en 3 tirets maximum.
            Renvoie UNIQUEMENT le texte corrigé, sans introduction.";
        } else {
            $systemPrompt = "Tu es un expert en recrutement et en optimisation de CV spécialisé en ATS.
            Transforme le texte fourni en une liste ultra-professionnelle et percutante en Français.

            RÈGLES D'OR :
            1. FORMAT : Renvoie uniquement une liste utilisant des tirets (-). 
            2. QUANTITÉ : Maximum 3 tirets (points).
            3. AUCUN HTML : Interdiction formelle d'utiliser ou de garder des balises HTML (pas de <strong>, <ul>, <li>, etc.).
            4. ACTION : Utilise des verbes d'action dynamiques et puissants au début de chaque tiret.
            5. TECHNIQUE : Conserve impérativement tous les mots-clés techniques et les termes métiers du texte original.
            6. SORTIE : Renvoie UNIQUEMENT les tirets. Pas d'introduction, pas de conclusion, pas de commentaires.

            Contexte de la section : $context";
        }

        $payload = json_encode([
            "model" => $this->model,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "Texte à traiter :\n" . $text]
            ],
            "temperature" => 0.3
        ]);

        $response = $this->callGroq($payload);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            $err = $data['error'];
            return "[Erreur] " . (is_array($err) ? ($err['message'] ?? "Erreur inconnue") : $err);
        }

        return $data['choices'][0]['message']['content'] ?? $text;
    }
}
