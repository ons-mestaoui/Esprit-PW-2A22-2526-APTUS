<?php
require_once __DIR__ . '/../config.php';

class AIController {
    private $apiKey;
    private $apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    private $model = "llama-3.3-70b-versatile";
    private $firecrawlApiKey;

    public function __construct() {
        // Load API Key from .env manually if not already loaded by a framework
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
        $this->firecrawlApiKey = $_ENV['FIRECRAWL_API_KEY'] ?? getenv('FIRECRAWL_API_KEY') ?: '';
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

    public function generateJSON(string $prompt, string $userInput = "", string $specificModel = null): array {
        if (empty($this->apiKey)) {
            throw new Exception("Clé API Groq manquante.");
        }

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
        
        if (isset($data['error'])) {
            $msg = is_array($data['error']) ? ($data['error']['message'] ?? json_encode($data['error'])) : $data['error'];
            error_log("Groq JSON Error: " . $msg);
            throw new Exception("Erreur Groq: " . $msg);
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) return $json;
        }
        
        $json = json_decode($content, true);
        if (!$json) {
            error_log("Groq Invalid JSON Response: " . $content);
            throw new Exception("L'IA n'a pas renvoyé un JSON valide.");
        }
        return $json;
    }

    public function generateJSONGemini(string $prompt, string $userInput = ""): array {
        // Redirection vers Groq
        return $this->generateJSON($prompt, $userInput);
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

    /**
     * Analyse une offre d'emploi pour en extraire les points clés
     */
    public function analyzeJobPosting(string $jobContent): array {
        // Troncature pour éviter l'erreur "Request too large" (TPM limit)
        $truncatedContent = mb_substr($jobContent, 0, 5000);

        $prompt = "Tu es un expert en recrutement (Recruitment Intelligence). Analyse cette offre d'emploi et renvoie un JSON structuré.
        JSON : {
          \"title\": \"Titre du poste\",
          \"company\": \"Nom de l'entreprise\",
          \"hard_skills\": [\"compétence 1\", ...],
          \"soft_skills\": [\"compétence 1\", ...],
          \"culture\": \"startup\"|\"formal\",
          \"culture_reason\": \"Pourquoi ce choix\",
          \"salary_range\": \"Fourchette estimée en K€ (ex: 45-55k)\",
          \"salary_negotiation_tips\": \"Conseils pour négocier ce poste\",
          \"company_summary\": \"Bref résumé de l'entreprise\",
          \"source_logo\": \"linkedin\"|\"indeed\"|\"glassdoor\"|\"default\",
          \"template_suggestion\": \"Moderne\"|\"Professionnel\"|\"Classique\"
        }";

        // Utilisation du modèle 8B (plus léger et rapide) pour l'extraction de données
        return $this->generateJSON($prompt, "Offre d'emploi (Extrait) :\n" . $truncatedContent, "llama-3.1-8b-instant");
    }

    /**
     * Optimise les données d'un CV pour un poste spécifique
     */
    public function tailorCV(array $cvData, array $jobData): array {
        // Nettoyage rapide des données du CV pour réduire les tokens
        $cleanCV = [
            'resume' => strip_tags($cvData['resume'] ?? ''),
            'experience' => strip_tags($cvData['experience'] ?? ''),
            'competences' => strip_tags($cvData['competences'] ?? ''),
            'langues' => strip_tags($cvData['langues'] ?? ''),
            'formation' => strip_tags($cvData['formation'] ?? ''),
            'titrePoste' => strip_tags($cvData['titrePoste'] ?? '')
        ];

        $prompt = "Tu es l'Expert Ultime en Optimisation de Carrière et ATS. 
        Ta mission est d'optimiser le CV de l'utilisateur pour qu'il soit parfaitement aligné avec l'offre d'emploi fournie.

        ⚠️ RÈGLES DE CONTENU STRICTES (CRITIQUE) :
        1. NE JAMAIS AJOUTER DE NOUVELLES EXPÉRIENCES PROFESSIONNELLES OU ENTREPRISES.
        2. NE JAMAIS INVENTER DE DATES, DE LIEUX OU DE DIPLÔMES.
        3. NE JAMAIS PRÉTENDRE QUE L'UTILISATEUR TRAVAILLE DÉJÀ CHEZ L'EMPLOYEUR VISÉ (" . ($jobData['company'] ?? 'l\'entreprise') . ").
        4. NE PAS TOUCHER AUX NOMS DES ENTREPRISES OU AUX DATES EXISTANTES.
        5. RÉÉCRIS UNIQUEMENT LES MISSIONS/TÂCHES EXISTANTES.

        🛠️ RÈGLES DE FORMATAGE TECHNIQUE (OBLIGATOIRE) :
        - Expériences (experience) : Sépare CHAQUE bloc par DEUX sauts de ligne (\\n\\n). Utilise des puces '•' pour les tâches.
        - Formation (formation) : Sépare CHAQUE bloc par DEUX sauts de ligne (\\n\\n).
        - Compétences (competences) : Liste simple séparée par des VIRGULES. RÈGLE D'OR : Uniquement des noms ou groupes nominaux courts (ex: 'Java', 'Gestion de projet'). PAS DE FRAGMENTS DE PHRASE.
        - Langues (langues) : Une langue par ligne au format 'Langue - Niveau'.

        ✅ MISSIONS :
        - Compétences : Enrichis avec les mots-clés de l'offre : " . implode(', ', $jobData['hard_skills'] ?? []) . ".
        - Titre (titrePoste) : Ajuste selon le titre exact de l'offre (" . ($jobData['title'] ?? 'le poste') . ").
        - Résumé : Réécris pour souligner l'adéquation sans inventer de faits.

        Structure JSON STRICTE :
        {
          \"resume\": \"...\",
          \"experience\": \"...\",
          \"competences\": \"...\",
          \"langues\": \"...\",
          \"formation\": \"...\",
          \"titrePoste\": \"...\"
        }";

        return $this->generateJSON($prompt, "Données CV : " . json_encode($cleanCV) . "\n\nCible Poste : " . json_encode($jobData), "llama-3.1-8b-instant");
    }

    /**
     * Scrape un URL via Firecrawl
     */
    public function scrapeUrl(string $url): string {
        if (empty($this->firecrawlApiKey)) {
            throw new Exception("Clé API Firecrawl manquante.");
        }

        $payload = json_encode([
            "url" => $url,
            "formats" => ["markdown"]
        ]);

        $ch = curl_init("https://api.firecrawl.dev/v2/scrape");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->firecrawlApiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);

        error_log("Firecrawl: Scrapping " . $url);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Firecrawl Error: $httpCode - $curlError - $response");
            $data = json_decode($response, true);
            $msg = $data['error'] ?? "Erreur Firecrawl $httpCode";
            throw new Exception($msg);
        }

        $data = json_decode($response, true);
        return $data['data']['markdown'] ?? $data['data']['content'] ?? '';
    }

    /**
     * Génère un Guide de Recrutement Premium via Groq (Llama 3.1)
     */
    public function generateRecruitmentGuide(array $jobData, array $cvData, array $oldCVData = []): array {
        if (empty($this->apiKey)) {
            throw new Exception("Clé API Groq manquante.");
        }

        // 1. Préparer les données pour la comparaison
        $cleanNewCV = [
            'resume' => strip_tags($cvData['resume'] ?? ''),
            'experience' => strip_tags($cvData['experience'] ?? ''),
            'competences' => strip_tags($cvData['competences'] ?? '')
        ];
        
        $cleanOldCV = [
            'resume' => strip_tags($oldCVData['resume'] ?? ''),
            'experience' => strip_tags($oldCVData['experience'] ?? ''),
            'competences' => strip_tags($oldCVData['competences'] ?? '')
        ];

        // 2. Demander à l'IA d'analyser les AJOUTS (Differential Analysis)
        $prompt = "Tu es un Expert en Recrutement Stratégique. 
        Ta mission est de comparer le CV ORIGINAL et le CV OPTIMISÉ du candidat par rapport à l'OFFRE D'EMPLOI fournie.
        
        OBJECTIF : Identifie les 3 compétences techniques ou mots-clés qui ont été AJOUTÉS ou ENRICHIS dans le CV optimisé pour correspondre à l'offre, mais que le candidat ne possédait pas (ou peu) dans son CV original.
        
        STRUCTURE DU JSON À RENVOYER :
        {
          \"justifications\": [
             {\"champ\": \"Résumé\", \"raison\": \"...\"},
             {\"champ\": \"Expériences\", \"raison\": \"...\"},
             {\"champ\": \"Compétences\", \"raison\": \"...\"},
             {\"champ\": \"Langues\", \"raison\": \"...\"}
          ],
          \"skill_gaps\": [
             {
               \"skill\": \"Nom de la compétence ajoutée\",
               \"strategic_advice\": \"Conseil dynamique sur comment maîtriser RÉELLEMENT cette compétence ajoutée pour passer l'entretien avec succès. Explique pourquoi cet ajout était nécessaire pour l'ATS.\"
             }
          ],
          \"company_insights\": { \"culture\": \"...\", \"strategic_tips\": \"...\" },
          \"interview_quiz\": [
             {
               \"question\": \"...\",
               \"options\": [\"...\", \"...\", \"...\"],
               \"correct_index\": 2,
               \"explanation\": \"...\"
             }
          ],
          \"soft_skills_advice\": \"...\",
          \"salary_strategy\": {
             \"estimated_range\": \"...\",
             \"negotiation_points\": [\"...\", \"...\", \"...\"]
          }
        }";

        $userInput = "OFFRE D'EMPLOI : " . json_encode($jobData) . "\n\n" .
                     "CV ORIGINAL : " . json_encode($cleanOldCV) . "\n\n" .
                     "CV OPTIMISÉ (CIBLE) : " . json_encode($cleanNewCV);
        
        $guide = $this->generateJSON($prompt, $userInput, "llama-3.1-8b-instant");

        // 3. Enrichir avec les formations RÉELLES du site pour ces manques
        if (isset($guide['skill_gaps'])) {
            foreach ($guide['skill_gaps'] as &$gap) {
                $gap['real_formation'] = $this->findFormationMatch($gap['skill']);
            }
        }

        return $guide;
    }

    /**
     * Cherche une formation réelle dans la base de données basée sur une compétence
     */
    private function findFormationMatch(string $skill): ?array {
        try {
            $pdo = config::getConnexion();
            // Recherche par mot-clé dans le titre ou la description
            $stmt = $pdo->prepare("SELECT id_formation, titre, description, domaine 
                                 FROM formation 
                                 WHERE titre LIKE :skill 
                                 OR description LIKE :skill 
                                 OR domaine LIKE :skill 
                                 LIMIT 1");
            $stmt->execute(['skill' => '%' . $skill . '%']);
            $match = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $match ?: null;
        } catch (Exception $e) {
            error_log("Search Formation Error: " . $e->getMessage());
            return null;
        }
    }

    private function getAvailableTrainings(): array {
        // Cette fonction n'est plus utilisée directement par l'IA pour éviter les hallucinations
        return [];
    }
}
