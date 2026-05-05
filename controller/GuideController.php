<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/GuideRecrutement.php';

class GuideController {
    private $apiKey;
    private $tailorApiKey;
    private $apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    private $model = "llama-3.3-70b-versatile";
    private $firecrawlApiKey;

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
        $this->tailorApiKey = $_ENV['GROQ_TAILOR_API_KEY'] ?? getenv('GROQ_TAILOR_API_KEY') ?: $this->apiKey;
        $this->firecrawlApiKey = $_ENV['FIRECRAWL_API_KEY'] ?? getenv('FIRECRAWL_API_KEY') ?: '';
    }

    private function callGroq(string $payload, string $specificKey = null): string {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($specificKey ?: $this->apiKey)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function generateJSON(string $prompt, string $userInput = "", string $specificModel = null, bool $useTailorKey = false): array {
        $keyToUse = $useTailorKey ? $this->tailorApiKey : $this->apiKey;
        if (empty($keyToUse)) throw new Exception("Clé API Groq manquante.");
        
        // Utiliser le modèle versatile par défaut pour plus de fiabilité
        $modelToUse = $specificModel ?: "llama-3.3-70b-versatile";
        
        $payload = json_encode([
            "model" => $modelToUse,
            "messages" => [
                ["role" => "system", "content" => $prompt],
                ["role" => "user", "content" => $userInput]
            ],
            "temperature" => 0.1,
            "response_format" => ["type" => "json_object"]
        ]);

        $response = $this->callGroq($payload, $keyToUse);
        $data = json_decode($response, true);

        // Debug API Error
        if (isset($data['error'])) {
            $err = $data['error']['message'] ?? 'Erreur API inconnue';
            error_log("Groq API Error: " . $err);
            throw new Exception("Erreur Groq : " . $err);
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        if (empty($content)) {
            error_log("Groq Error: Reponse vide. Response raw: " . $response);
            return [];
        }

        // Nettoyage et parsing JSON
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) return $json;
        }

        $json = json_decode($content, true);
        if (!$json) {
            error_log("Groq Error: JSON invalide. Content: " . $content);
            return [];
        }

        return $json;
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
     * Analyse une offre d'emploi pour en extraire les points clés
     */
    public function analyzeJobPosting(string $jobContent): array {
        // Troncature pour éviter l'erreur "Request too large" (TPM limit)
        $truncatedContent = mb_substr($jobContent, 0, 5000);

        $prompt = "Tu es un expert en recrutement (Recruitment Intelligence). Analyse cette offre d'emploi et renvoie un JSON structuré.
        JSON : {
          \"title\": \"Titre du poste\",
          \"company\": \"Nom de l'entreprise\",
          \"hard_skills\": [\"Terme technique réel uniquement (ex: Java, SolidWorks, Lean)\", ...],
          \"soft_skills\": [\"Qualité humaine réelle uniquement (ex: Leadership, Autonomie)\", ...],
          \"culture\": \"startup\"|\"formal\",
          \"culture_reason\": \"Pourquoi ce choix\",
          \"salary_range\": \"Fourchette estimée en K€ (ex: 45-55k)\",
          \"salary_negotiation_tips\": \"Conseils pour négocier ce poste\",
          \"company_summary\": \"Bref résumé de l'entreprise\",
          \"source_logo\": \"linkedin\"|\"indeed\"|\"glassdoor\"|\"default\",
          \"template_suggestion\": \"Moderne\"|\"Professionnel\"|\"Classique\"
        }";

        // Utilisation du modèle 70B pour plus de stabilité et de limites TPM plus hautes
        // On utilise la clé Tailor
        return $this->generateJSON($prompt, "Offre d'emploi (Extrait) :\n" . $truncatedContent, "llama-3.3-70b-versatile", true);
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
        - Compétences (competences) : Liste simple séparée par des VIRGULES. RÈGLE D'OR : Uniquement des noms propres de technologies, méthodologies ou soft skills (ex: 'Python', 'SolidWorks', 'Leadership'). 
        *INTERDICTION STRICTE* : Ne jamais utiliser de fragments de phrases (ex: pas de 'de tests et de validation'). Si une compétence est longue, elle doit rester un SEUL bloc entier sans virgule interne.
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

        // Utilisation du modèle 70B
        return $this->generateJSON($prompt, "Données CV : " . json_encode($cleanCV) . "\n\nCible Poste : " . json_encode($jobData), "llama-3.3-70b-versatile", true);
    }

    /**
     * Génère un Guide de Recrutement Premium via Groq (Llama 3.1)
     * Effectue une analyse différentielle entre le CV original et le CV optimisé
     */
    public function generateRecruitmentGuide(array $jobData, array $cvData, array $oldCVData = []): array {
        if (empty($this->apiKey)) {
            throw new Exception("Clé API Groq manquante.");
        }

        // 1. Préparer les données pour la comparaison (Nettoyage pour tokens)
        $cleanNewCV = [
            'resume' => mb_substr(strip_tags($cvData['resume'] ?? ''), 0, 2000),
            'experience' => mb_substr(strip_tags($cvData['experience'] ?? ''), 0, 2000),
            'competences' => mb_substr(strip_tags($cvData['competences'] ?? ''), 0, 2000)
        ];
        
        $cleanOldCV = [
            'resume' => mb_substr(strip_tags($oldCVData['resume'] ?? ''), 0, 2000),
            'experience' => mb_substr(strip_tags($oldCVData['experience'] ?? ''), 0, 2000),
            'competences' => mb_substr(strip_tags($oldCVData['competences'] ?? ''), 0, 2000)
        ];

        // 2. Demander à l'IA d'analyser les AJOUTS (Differential Analysis)
        $prompt = "Tu es l'Expert Ultime en Coaching de Carrière, Négociation Salariale et Psychologie du Recrutement.
        Ta mission est de comparer le CV ORIGINAL et le CV OPTIMISÉ du candidat par rapport à l'OFFRE D'EMPLOI fournie pour générer un guide stratégique de réussite.

        TON ET STYLE :
        - Direct, tactique et professionnel. 
        - Évite les phrases génériques. 
        - Utilise un ton 'Insider' : donne des conseils concrets que seul un recruteur senior connaîtrait.

        1. STRATÉGIE SALARIALE (PRECISION CRITIQUE) :
        - ESTIMATION : Effectue une estimation précise basée sur le marché actuel en France pour ce poste et ce type d'entreprise.
        - NÉGOCIATION : Fournis des scripts de négociation 'High-Stakes' pour maximiser la rémunération. Chaque script doit être une phrase complète, tactique et prête à être dite.

        STRUCTURE DU JSON À RENVOYER (STRICTE) :
        {
          \"justifications\": [
             {\"champ\": \"Résumé|Expériences|Compétences\", \"raison\": \"Pourquoi ce changement était critique pour l'ATS ou la crédibilité.\"}
          ],
          \"skill_gaps\": [
             {
               \"skill\": \"Nom de la compétence\",
               \"strategic_advice\": \"Conseil technique sur comment prouver la maîtrise de cette compétence en entretien.\"
             }
          ],
          \"company_insights\": { \"culture\": \"Analyse profonde de la culture (Startup vs Corporate)\", \"strategic_tips\": \"Comment se comporter pour matcher parfaitement.\" },
          \"interview_quiz\": [
             {
               \"question\": \"Question piège ou technique liée au poste\",
               \"options\": [\"Option A\", \"Option B\", \"Option C\"],
               \"correct_index\": 0,
               \"explanation\": \"Pourquoi c'est la bonne réponse tactique.\"
             }
          ],
          \"salary_strategy\": {
             \"range\": { \"min\": \"valeur\", \"max\": \"valeur\", \"currency\": \"k€\", \"period\": \"an\" },
             \"market_context\": \"Bref état du marché pour ce poste.\",
             \"negotiation_scripts\": [
                {
                  \"moment\": \"Titre tactique (ex: Expertise de niche)\",
                  \"script\": \"L'argument stratégique en général (ex: La rareté des profils maîtrisant [Compétence] sur le marché actuel).\",
                  \"developed_script\": \"ESSAI DÉVELOPPÉ (PAROLE) : La phrase exacte et percutante à prononcer à haute voix pour défendre cet argument avec assurance.\"
                }
             ]
          },
          \"soft_skills_advice\": \"Conseil comportemental global.\"
        }
        IMPORTANT : Pour les profils techniques (IT, Ingénierie), privilégie les manques techniques réels (ex: Docker, React, AWS) plutôt que des soft skills ou du management dans 'skill_gaps'.";

        $userInput = "OFFRE D'EMPLOI : " . json_encode($jobData) . "\n\n" .
                     "CV ORIGINAL : " . json_encode($cleanOldCV) . "\n\n" .
                     "CV OPTIMISÉ (CIBLE) : " . json_encode($cleanNewCV);
        
        $guide = $this->generateJSON($prompt, $userInput, "llama-3.3-70b-versatile");

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
    /**
     * Cherche une formation réelle dans la base de données basée sur une compétence
     * Utilise une recherche par mots-clés plus flexible pour éviter les échecs sur les phrases
     */
    private function findFormationMatch(string $skill): ?array {
        try {
            $pdo = config::getConnexion();
            
            // 1. Essayer d'abord la recherche exacte (avec wildcards)
            $stmt = $pdo->prepare("SELECT id_formation, titre, description, domaine 
                                 FROM formation 
                                 WHERE titre LIKE :skill 
                                 OR description LIKE :skill 
                                 OR domaine LIKE :skill 
                                 ORDER BY RAND() 
                                 LIMIT 1");
            $stmt->execute(['skill' => '%' . $skill . '%']);
            $match = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($match) return $match;

            // 2. Si ça échoue, on découpe par mots-clés (mots de plus de 3 lettres)
            $words = explode(' ', $skill);
            $keywords = [];
            foreach ($words as $w) {
                $w = trim($w, " ,.()'");
                if (mb_strlen($w) > 3) $keywords[] = $w;
            }

            if (!empty($keywords)) {
                $where = [];
                $params = [];
                foreach ($keywords as $idx => $kw) {
                    $where[] = "(titre LIKE :kw$idx OR description LIKE :kw$idx)";
                    $params["kw$idx"] = '%' . $kw . '%';
                }
                
                $sql = "SELECT id_formation, titre, description, domaine 
                        FROM formation 
                        WHERE " . implode(" OR ", $where) . " 
                        ORDER BY RAND() 
                        LIMIT 1";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Search Formation Error: " . $e->getMessage());
            return null;
        }
    }

    private function getAvailableTrainings(): array {
        // Cette fonction n'est plus utilisée directement par l'IA pour éviter les hallucinations
        return [];
    }
    
    public function addGuide(GuideRecrutement $guide) {
        $db = config::getConnexion();
        $query = $db->prepare(
            'INSERT INTO guide_recrutement (id_cv, id_candidat, titre_poste, contenu_json, date_creation) 
            VALUES (:id_cv, :id_can, :titre, :json, NOW())'
        );
        $query->execute([
            'id_cv' => $guide->getIdCv(),
            'id_can' => $guide->getIdCandidat(),
            'titre' => $guide->getTitrePoste(),
            'json' => $guide->getContenuJson()
        ]);
        return $db->lastInsertId();
    }

    public function getGuideByCvId($id_cv) {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT * FROM guide_recrutement WHERE id_cv = :id ORDER BY date_creation DESC LIMIT 1');
        $query->execute(['id' => $id_cv]);
        return $query->fetch();
    }

    public function deleteGuideByCvId($id_cv) {
        $db = config::getConnexion();
        $query = $db->prepare('DELETE FROM guide_recrutement WHERE id_cv = :id');
        $query->execute(['id' => $id_cv]);
    }
}
?>
