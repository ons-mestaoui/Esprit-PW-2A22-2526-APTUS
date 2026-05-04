<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/RapportIA.php';

class RapportIAController {
    private $apiKey;
    private $apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    private $model = "llama-3.3-70b-versatile";

    public function __construct() {
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
        $this->apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: ''; 
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }

    public function generateJSON(string $prompt, string $userInput = "", string $specificModel = null): array {
        if (empty($this->apiKey)) throw new Exception("Clé API Groq manquante.");
        $modelToUse = $specificModel ?: $this->model;
        $payload = json_encode([
            "model" => $modelToUse,
            "messages" => [
                ["role" => "system", "content" => $prompt],
                ["role" => "user", "content" => $userInput]
            ],
            "temperature" => 0.1,
            "response_format" => ["type" => "json_object"]
        ]);
        $response = $this->callGroq($payload);
        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) return $json;
        }
        return json_decode($content, true) ?: [];
    }

    public function analyzeCV(string $cvText, string $additionalContext = ""): string {
        if (empty(trim($cvText)) || empty($this->apiKey)) return json_encode(['error' => 'Texte vide ou API Key manquante.']);
        $systemPrompt = "Tu es un Auditeur de Carrière Senior. Analyse le CV et retourne UNIQUEMENT un objet JSON. 
        Structure attendue : { 
          \"score_ats\": (0-100), 
          \"sub_scores\": { \"structure\": 0-100, \"content_quality\": 0-100, \"keyword_relevance\": 0-100, \"impact_metrics\": 0-100 },
          \"market_positioning\": { \"percentile\": 0-100, \"demand_level\": \"Élevée\"|\"Moyenne\"|\"Faible\", \"salary_estimate\": \"...\" },
          \"score_explanation\": \"...\",
          \"points_forts\": [\"string\"], 
          \"points_faibles\": [\"string\"], 
          \"detailed_recommendations\": [
            {
              \"type\": \"structure\"|\"content\"|\"keywords\"|\"impact\",
              \"impact\": \"high\"|\"medium\"|\"low\",
              \"finding\": \"Description de ce qui manque ou pose problème\",
              \"correction\": \"Action concrète pour corriger\"
            }
          ], 
          \"keywords\": [\"string\"], 
          \"missing_skills\": [\"string\"], 
          \"suggested_training_domains\": [\"string\"]
        }
        IMPORTANT : Fournis MINIMUM 3 recommandations détaillées dans 'detailed_recommendations'.";
        $payload = json_encode([
            "model" => $this->model,
            "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => "CV à analyser :\n" . $cvText]],
            "temperature" => 0.2,
            "response_format" => ["type" => "json_object"]
        ]);
        $response = $this->callGroq($payload);
        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        if (preg_match('/\{.*\}/s', $content, $matches)) return $matches[0];
        return $content ?: json_encode(['error' => 'Réponse vide de l\'IA']);
    }

    public function matchJobs(array $keywords): array {
        return [
            ['title' => 'Développeur Fullstack Senior', 'domain' => 'Développement Software', 'match_score' => 95, 'location' => 'Tunis (Hybride)', 'salary' => '2500 - 3500 DT'],
            ['title' => 'Product Manager IT', 'domain' => 'Management / Produit', 'match_score' => 82, 'location' => 'Remote', 'salary' => '3000 - 4500 DT']
        ];
    }

    public function matchTrainingsByDomain(array $domains): array {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM formation LIMIT 3");
            $query->execute();
            $results = $query->fetchAll();
            if ($results) {
                return array_map(function($f) {
                    return ['title' => $f['nomFormation'] ?? $f['titre'] ?? 'Formation Aptus', 'domain' => $f['domaine'] ?? 'Expertise', 'duration' => $f['duree'] ?? '20h', 'level' => 'Intermédiaire', 'match_score' => rand(85, 98)];
                }, $results);
            }
        } catch (Exception $e) {}
        return [
            ['title' => 'Masterclass Architecture Microservices', 'domain' => 'Backend', 'duration' => '15h', 'level' => 'Avancé', 'match_score' => 92],
            ['title' => 'UI/UX Design & Psychologie Cognitive', 'domain' => 'Design', 'duration' => '12h', 'level' => 'Intermédiaire', 'match_score' => 88]
        ];
    }

    
    public function addRapport(RapportIA $rapport) {
        $db = config::getConnexion();
        $query = $db->prepare(
            'INSERT INTO rapport_ia (id_cv, scoreGlobal, pointsForts, pointsFaibles, sectionsManquantes, suggestions, dateAnalyse) 
            VALUES (:id_cv, :score, :forts, :faibles, :manquantes, :suggestions, NOW())'
        );
        $query->execute([
            'id_cv' => $rapport->getIdCv(),
            'score' => $rapport->getScoreGlobal(),
            'forts' => $rapport->getPointsForts(),
            'faibles' => $rapport->getPointsFaibles(),
            'manquantes' => $rapport->getSectionsManquantes(),
            'suggestions' => $rapport->getSuggestions()
        ]);
        return $db->lastInsertId();
    }

    public function getRapportByCvId($id_cv) {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT * FROM rapport_ia WHERE id_cv = :id ORDER BY dateAnalyse DESC LIMIT 1');
        $query->execute(['id' => $id_cv]);
        return $query->fetch();
    }
}
?>
