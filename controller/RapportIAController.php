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
          \"keywords\": [\"Mots-clés techniques précis (ex: PHP 8, React, Docker, Kubernetes)\"], 
          \"missing_skills\": [\"string\"], 
          \"suggested_training_domains\": [\"string\"]
        }
        IMPORTANT : Fournis MINIMUM 3 recommandations détaillées dans 'detailed_recommendations'.
        IMPORTANT : Pour les profils techniques (Informatique, Ingénierie, Science), privilégie ABSOLUMENT les compétences techniques manquantes (Frameworks, Langages, Outils) plutôt que le management ou les soft skills dans 'missing_skills'. Ne suggère du management que si le profil est explicitement orienté vers la gestion d'équipe.";
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

    /**
     * Match des offres d'emploi réelles de la base de données
     */
    public function matchJobs(array $keywords): array {
        $db = config::getConnexion();
        try {
            // Recherche par mots-clés (titre, domaine ou compétences)
            $where = [];
            $params = [];
            foreach ($keywords as $idx => $kw) {
                if (strlen($kw) < 3) continue;
                $where[] = "(o.titre LIKE :kw$idx OR o.domaine LIKE :kw$idx OR o.competences_requises LIKE :kw$idx)";
                $params["kw$idx"] = '%' . $kw . '%';
            }

            if (empty($where)) return []; // Pas de mots-clés -> pas de match ciblé

            $whereStr = "WHERE " . implode(" OR ", $where);
            $sql = "SELECT o.*, e.raisonSociale as company, p.ville 
                    FROM offreemploi o 
                    JOIN entreprise e ON o.id_entreprise = e.id_entreprise 
                    LEFT JOIN profil p ON e.id_entreprise = p.id_utilisateur 
                    $whereStr 
                    ORDER BY RAND() LIMIT 3";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            if ($results) {
                return array_map(function($o) {
                    return [
                        'title' => $o['titre'],
                        'domain' => $o['domaine'] ?: 'IT / Tech',
                        'match_score' => rand(75, 95), // Score simulé basé sur la présence en DB
                        'location' => ($o['ville'] ?: 'Tunis') . ' (Aptus)',
                        'salary' => $o['salaire'] ? ($o['salaire'] . '€ / an') : 'Non précisé'
                    ];
                }, $results);
            }
        } catch (Exception $e) {
            error_log("MatchJobs Error: " . $e->getMessage());
        }

        // Fallback vide si vraiment rien en DB (mieux que du hardcoded faux)
        return [];
    }

    /**
     * Match des formations réelles de la base de données basées sur les lacunes
     */
    public function matchTrainingsByDomain(array $searchTerms): array {
        $db = config::getConnexion();
        try {
            $where = [];
            $params = [];
            
            // On nettoie les termes de recherche (souvent des listes d'IA)
            $cleanTerms = [];
            foreach ($searchTerms as $term) {
                $trimmed = trim($term);
                if (strlen($trimmed) > 2) $cleanTerms[] = $trimmed;
            }

            if (!empty($cleanTerms)) {
                foreach ($cleanTerms as $idx => $t) {
                    $where[] = "(domaine LIKE :t$idx OR titre LIKE :t$idx OR description LIKE :t$idx)";
                    $params["t$idx"] = '%' . $t . '%';
                }
                $whereStr = "WHERE " . implode(" OR ", $where);
                $stmt = $db->prepare("SELECT * FROM formation $whereStr ORDER BY RAND() LIMIT 3");
                $stmt->execute($params);
                $results = $stmt->fetchAll();
            } else {
                // Si rien n'est passé, on ne renvoie rien pour éviter le hors-sujet
                return [];
            }

            if ($results) {
                return array_map(function($f) {
                    return [
                        'id' => $f['id_formation'],
                        'title' => $f['titre'],
                        'domain' => $f['domaine'] ?: 'Expertise',
                        'duration' => 'Flexible',
                        'level' => $f['niveau'] ?: 'Intermédiaire'
                    ];
                }, $results);
            }
        } catch (Exception $e) {
            error_log("MatchTrainings Error: " . $e->getMessage());
        }
        
        return [];
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
