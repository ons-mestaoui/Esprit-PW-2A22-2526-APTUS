<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/CV.php';

class CVC
{
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $this->apiKey]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Augmenté un peu pour les gros CV
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            error_log("Groq API Connection Error: " . $error_msg);
            return json_encode(['error' => 'Connection failed', 'details' => $error_msg]);
        }
        
        curl_close($ch);
        return $response ?: json_encode(['error' => 'Empty response from API']);
    }

    public function generateJSON(string $prompt, string $userInput = "", string $specificModel = null): array {
        if (empty($this->apiKey)) throw new Exception("Clé API Groq manquante.");
        $modelToUse = $specificModel ?: $this->model;
        $payload = json_encode([
            "model" => $modelToUse,
            "messages" => [["role" => "system", "content" => $prompt], ["role" => "user", "content" => $userInput]],
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

    public function tailorCV(array $cvData, array $jobData): array {
        $prompt = "Tu es l'Expert Ultime en Optimisation de Carrière et ATS. Optimise ce CV pour le poste cible.";
        return $this->generateJSON($prompt, "Données CV : " . json_encode($cvData) . "\n\nCible Poste : " . json_encode($jobData), "llama-3.1-8b-instant");
    }

    public function polishText(string $text, string $context, string $mode = 'polish'): string {
        $systemPrompt = "";
        if ($mode === 'correct') {
            $systemPrompt = "Tu es un correcteur linguistique expert en français. Ton but est l'EFFICACITÉ. Corrige les fautes d'orthographe, de grammaire et de ponctuation. Garde la structure originale. Ne rajoute AUCUNE phrase d'introduction ou de conclusion. Donne directement les tirets sans étapes.";
        } else {
            // Mode Polish
            $systemPrompt = "Tu es un expert RH. Reformule ce texte pour un CV de manière CONCISE, PROFESSIONNELLE et DIRECTE. 
            CONSIGNES :
            1. Utilise des tirets (listes à puces), maximum 3.
            2. Termine chaque point par un point-virgule ';'.
            3. GARDE TOUS LES MOTS TECHNIQUES. Ne pas sur-reformuler, reste factuel et percutant.
            4. Réponds UNIQUEMENT avec le contenu. AUCUNE introduction ou conclusion.";
        }

        $payload = json_encode([
            "model" => $this->model, 
            "messages" => [
                ["role" => "system", "content" => $systemPrompt], 
                ["role" => "user", "content" => $text]
            ], 
            "temperature" => 0.3
        ]);
        $response = $this->callGroq($payload);
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? $text;
    }

    public function translateCV(array $cvData, string $targetLang): array {
        $prompt = "Traduis ce CV en $targetLang en gardant la structure JSON.";
        return $this->generateJSON($prompt, json_encode($cvData));
    }
    public function addCV(CV $cv)
    {
        $db = config::getConnexion();
        $query = $db->prepare(
            'INSERT INTO cv (id_candidat, id_template, nomDocument, nomComplet, titrePoste, resume, infoContact, experience, formation, competences, langues, urlPhoto, couleurTheme, statut, dateCreation, dateMiseAJour, ai_analysis, is_tailored, target_job_url, tailoring_report) 
            VALUES (:id_candidat, :id_template, :nomDocument, :nomComplet, :titrePoste, :resume, :infoContact, :experience, :formation, :competences, :langues, :urlPhoto, :couleurTheme, :statut, NOW(), NOW(), :ai_analysis, :is_tailored, :target_job_url, :tailoring_report)'
        );
        $query->execute([
            'id_candidat' => $cv->getIdCandidat(),
            'id_template' => $cv->getIdTemplate(),
            'nomDocument' => $cv->getNomDocument(),
            'nomComplet'  => $cv->getNomComplet(),
            'titrePoste'  => $cv->getTitrePoste(),
            'resume'      => $cv->getResume(),
            'infoContact' => $cv->getInfoContact(),
            'experience'  => $cv->getExperience(),
            'formation'   => $cv->getFormation(),
            'competences' => $cv->getCompetences(),
            'langues'     => $cv->getLangues(),
            'urlPhoto'    => $cv->getUrlPhoto(),
            'couleurTheme'=> $cv->getCouleurTheme(),
            'statut'      => $cv->getStatut(),
            'ai_analysis' => $cv->getAiAnalysis(),
            'is_tailored' => $cv->getIsTailored(),
            'target_job_url' => $cv->getTargetJobUrl(),
            'tailoring_report' => $cv->getTailoringReport()
        ]);
        return $db->lastInsertId();
    }

    public function listCVByCandidat($id_candidat)
    {
        $db = config::getConnexion();
        if ($id_candidat === null) {
            // Mode dev: afficher tous les CVs
            $query = $db->prepare('SELECT * FROM cv ORDER BY dateMiseAJour DESC');
            $query->execute();
        } else {
            $query = $db->prepare('SELECT * FROM cv WHERE id_candidat = :id OR id_candidat IS NULL ORDER BY dateMiseAJour DESC');
            $query->execute(['id' => $id_candidat]);
        }
        return $query->fetchAll();
    }

    public function getCVById($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT * FROM cv WHERE id_cv = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function updateCV($id, CV $cv)
    {
        $db = config::getConnexion();
        $query = $db->prepare(
            'UPDATE cv SET 
                nomDocument   = :nomDocument,
                nomComplet    = :nomComplet,
                titrePoste    = :titrePoste,
                resume        = :resume,
                infoContact   = :infoContact,
                experience    = :experience,
                formation     = :formation,
                competences   = :competences,
                langues       = :langues,
                urlPhoto      = :urlPhoto,
                couleurTheme  = :couleurTheme,
                ai_analysis   = :ai_analysis,
                is_tailored   = :is_tailored,
                target_job_url = :target_job_url,
                tailoring_report = :tailoring_report,
                dateMiseAJour = NOW()
            WHERE id_cv = :id'
        );
        $query->execute([
            'nomDocument' => $cv->getNomDocument(),
            'nomComplet'  => $cv->getNomComplet(),
            'titrePoste'  => $cv->getTitrePoste(),
            'resume'      => $cv->getResume(),
            'infoContact' => $cv->getInfoContact(),
            'experience'  => $cv->getExperience(),
            'formation'   => $cv->getFormation(),
            'competences' => $cv->getCompetences(),
            'langues'     => $cv->getLangues(),
            'urlPhoto'    => $cv->getUrlPhoto(),
            'couleurTheme'=> $cv->getCouleurTheme(),
            'ai_analysis' => $cv->getAiAnalysis(),
            'is_tailored' => $cv->getIsTailored(),
            'target_job_url' => $cv->getTargetJobUrl(),
            'tailoring_report' => $cv->getTailoringReport(),
            'id'          => $id
        ]);
    }

    public function updateTailoring($id, $isTailored, $jobUrl, $report)
    {
        $db = config::getConnexion();
        $query = $db->prepare('UPDATE cv SET is_tailored = :is_t, target_job_url = :url, tailoring_report = :report WHERE id_cv = :id');
        $query->execute([
            'is_t' => $isTailored,
            'url' => $jobUrl,
            'report' => $report,
            'id' => $id
        ]);
    }

    public function deleteCV($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare('DELETE FROM cv WHERE id_cv = :id');
        $query->execute(['id' => $id]);
    }

    public function getTotalCVs()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) as total FROM cv');
            $result = $query->fetch();
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getCVGrowth()
    {
        $db = config::getConnexion();
        try {
            $currMonth = $db->query('SELECT COUNT(*) FROM cv WHERE MONTH(dateCreation) = MONTH(CURRENT_DATE()) AND YEAR(dateCreation) = YEAR(CURRENT_DATE())')->fetchColumn();
            $lastMonth = $db->query('SELECT COUNT(*) FROM cv WHERE dateCreation >= DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH) AND dateCreation < DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)')->fetchColumn();
            return ['current' => $currMonth, 'last' => $lastMonth];
        } catch (Exception $e) {
            return ['current' => 0, 'last' => 0];
        }
    }

    public function getRecentCVAdditionsCount()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) FROM cv WHERE dateCreation >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
            return $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
