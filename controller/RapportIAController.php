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
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $db = config::getConnexion();
            $db->exec("
                CREATE TABLE IF NOT EXISTS rapport_ia (
                    id_rapport_ia  INT AUTO_INCREMENT PRIMARY KEY,
                    id_cv          INT DEFAULT NULL,
                    scoreGlobal    INT DEFAULT 0,
                    pointsForts    TEXT DEFAULT NULL,
                    pointsFaibles  TEXT DEFAULT NULL,
                    sectionsManquantes TEXT DEFAULT NULL,
                    suggestions    TEXT DEFAULT NULL,
                    keywords       TEXT DEFAULT NULL,
                    dateAnalyse    DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_cv (id_cv)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
        } catch (Exception $e) { /* ignore — table already exists or DB unreachable */ }
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
            // Nettoyer et garder uniquement les mots-clés de taille >= 2
            $validKeywords = array_filter($keywords, function($kw) {
                return strlen(trim($kw)) >= 2;
            });
            $validKeywordsCount = count($validKeywords);
            
            // Si pas de mots-clés, on fait une recherche TRÈS large sur les dernières offres actives
            if ($validKeywordsCount === 0) {
                $sql = "SELECT o.*, u.nom as company, p.ville 
                        FROM offreemploi o 
                        LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur 
                        LEFT JOIN profil p ON o.id_entreprise = p.id_utilisateur 
                        WHERE o.statut = 'Actif'
                        ORDER BY o.date_publication DESC LIMIT 5";
                $stmt = $db->query($sql);
                $results = $stmt->fetchAll();
                
                $matchedJobs = [];
                foreach ($results as $o) {
                    $matchedJobs[] = [
                        'id_offre' => $o['id_offre'],
                        'titre' => $o['titre'],
                        'domaine' => $o['domaine'] ?: 'Expertise',
                        'match_score' => 50, // Score arbitraire pour le fallback
                        'lieu' => ($o['ville'] ?: 'Tunis') . ' (Aptus)',
                        'entreprise' => $o['company'] ?? 'Aptus Partner',
                        'salaire' => $o['salaire'] ? ($o['salaire'] . ' TND') : 'Non précisé',
                        'created_at' => $o['date_publication'] ?? ''
                    ];
                }
                return $matchedJobs;
            }

            // Recherche large (toutes les offres qui matchent au moins un mot-clé)
            $where = [];
            $params = [];
            $idx = 0;
            foreach ($validKeywords as $kw) {
                $where[] = "(o.titre LIKE :kw$idx OR o.domaine LIKE :kw$idx OR o.competences_requises LIKE :kw$idx)";
                $params["kw$idx"] = '%' . trim($kw) . '%';
                $idx++;
            }

            $whereStr = "WHERE (" . implode(" OR ", $where) . ")";
            $sql = "SELECT o.*, u.nom as company, p.ville 
                    FROM offreemploi o 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur 
                    LEFT JOIN profil p ON o.id_entreprise = p.id_utilisateur 
                    $whereStr AND o.statut = 'Actif'";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            if ($results) {
                return array_map(function($j) use ($validKeywords) {
                    $score = 0;
                    $titleLower = mb_strtolower($j['titre']);
                    $descLower = mb_strtolower($j['competences_requises']);
                    $domLower = mb_strtolower($j['domaine']);
                    
                    foreach ($validKeywords as $kw) {
                        $kwL = mb_strtolower($kw);
                        if (str_contains($titleLower, $kwL)) $score += 30;
                        if (str_contains($descLower, $kwL)) $score += 10;
                        if (str_contains($domLower, $kwL)) $score += 20;
                    }
                    
                    $scorePercent = min(98, 20 + $score); 
                    
                    return [
                        'id' => $j['id_offre'],
                        'title' => $j['titre'],
                        'match_score' => $scorePercent,
                        'domain' => $j['domaine'],
                        'location' => ($j['ville'] ?: 'Tunis'),
                        'image' => null
                    ];
                }, $results);
            }
        } catch (Exception $e) {
            error_log("MatchJobs Error: " . $e->getMessage());
        }

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
                return [];
            }

            if ($results) {
                return array_map(function($f) use ($cleanTerms) {
                    $score = 0;
                    $titleLower = mb_strtolower($f['titre']);
                    $descLower = mb_strtolower($f['description']);
                    
                    foreach ($cleanTerms as $kw) {
                        $kwL = mb_strtolower($kw);
                        if (str_contains($titleLower, $kwL)) $score += 30;
                        if (str_contains($descLower, $kwL)) $score += 10;
                    }
                    
                    $scorePercent = min(98, 30 + $score); 

                    return [
                        'id' => $f['id_formation'],
                        'title' => $f['titre'],
                        'nom_formation' => $f['titre'],
                        'domain' => $f['domaine'] ?: 'Expertise',
                        'domaine' => $f['domaine'] ?: 'Expertise',
                        'duree_formation' => 'Flexible',
                        'level' => $f['niveau'] ?: 'Intermédiaire',
                        'niveau' => $f['niveau'] ?: 'Intermédiaire',
                        'match_score' => $scorePercent,
                        'image' => null
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
        
        // On vérifie si la colonne keywords existe (pour compatibilité)
        $hasKeywords = false;
        try {
            $db->query("SELECT keywords FROM rapport_ia LIMIT 1");
            $hasKeywords = true;
        } catch (Exception $e) { $hasKeywords = false; }

        if ($hasKeywords) {
            $query = $db->prepare(
                'INSERT INTO rapport_ia (id_cv, scoreGlobal, pointsForts, pointsFaibles, sectionsManquantes, suggestions, keywords, dateAnalyse) 
                VALUES (:id_cv, :score, :forts, :faibles, :manquantes, :suggestions, :keywords, NOW())'
            );
            $params = [
                'id_cv' => $rapport->getIdCv(),
                'score' => $rapport->getScoreGlobal(),
                'forts' => $rapport->getPointsForts(),
                'faibles' => $rapport->getPointsFaibles(),
                'manquantes' => $rapport->getSectionsManquantes(),
                'suggestions' => $rapport->getSuggestions(),
                'keywords' => $rapport->getKeywords()
            ];
        } else {
            $query = $db->prepare(
                'INSERT INTO rapport_ia (id_cv, scoreGlobal, pointsForts, pointsFaibles, sectionsManquantes, suggestions, dateAnalyse) 
                VALUES (:id_cv, :score, :forts, :faibles, :manquantes, :suggestions, NOW())'
            );
            $params = [
                'id_cv' => $rapport->getIdCv(),
                'score' => $rapport->getScoreGlobal(),
                'forts' => $rapport->getPointsForts(),
                'faibles' => $rapport->getPointsFaibles(),
                'manquantes' => $rapport->getSectionsManquantes(),
                'suggestions' => $rapport->getSuggestions()
            ];
        }
        
        $query->execute($params);
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
