
<?php
require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/EnvLoader.php';

class AIController
{
    private $groqKeys = [];
    private $geminiKey = '';

    public function __construct()
    {
        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            $envPath = dirname(__DIR__) . '/.env';
        }
        EnvLoader::load($envPath);

        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '';

        $keys = [];
        $envGroqKeys = $_ENV['GROQ_API_KEYS'] ?? $_SERVER['GROQ_API_KEYS'] ?? getenv('GROQ_API_KEYS') ?? '';
        if (!empty($envGroqKeys)) {
            $keys = explode(',', $envGroqKeys);
        }

        foreach (['GROQ_KEY_1', 'GROQ_KEY_2', 'GROQ_KEY_3', 'GROQ_API_KEY'] as $var) {
            $val = $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var) ?? '';
            if (!empty($val) && !in_array($val, $keys)) {
                $keys[] = $val;
            }
        }

        $this->groqKeys = $keys;
    }

    // ============================================================
    // 🧠 PUBLIC METHODS (MVC)
    // ============================================================

    public function getMindMap($id_formation)
    {
        $db = config::getConnexion();
        $s = $db->prepare("SELECT mindmap_mermaid FROM formation_ai_metadata WHERE id_formation = ?");
        $s->execute([$id_formation]);
        $cached = $s->fetch();

        if ($cached && !empty($cached['mindmap_mermaid'])) {
            return ['success' => true, 'mermaid_code' => $cached['mindmap_mermaid']];
        }

        require_once __DIR__ . '/FormationController.php';
        $formC = new FormationController();
        $f = $formC->getFormationById($id_formation);
        if (!$f)
            return ['success' => false, 'message' => 'Formation introuvable.'];

        $mermaid = $this->generateMindMapInternal($f['description']);
        if ($mermaid) {
            $ins = $db->prepare("INSERT INTO formation_ai_metadata (id_formation, mindmap_mermaid) VALUES (?, ?) ON DUPLICATE KEY UPDATE mindmap_mermaid = ?");
            $ins->execute([$id_formation, $mermaid, $mermaid]);
            return ['success' => true, 'mermaid_code' => $mermaid];
        }
        return ['success' => false];
    }

    public function getCheatSheet($id_formation)
    {
        $db = config::getConnexion();
        $s = $db->prepare("SELECT cheatsheet_markdown FROM formation_ai_metadata WHERE id_formation = ?");
        $s->execute([$id_formation]);
        $cached = $s->fetch();

        if ($cached && !empty($cached['cheatsheet_markdown'])) {
            return ['success' => true, 'html_content' => $this->markdownToHtml($cached['cheatsheet_markdown'])];
        }

        require_once __DIR__ . '/FormationController.php';
        $formC = new FormationController();
        $f = $formC->getFormationById($id_formation);
        if (!$f)
            return ['success' => false, 'message' => 'Formation introuvable.'];

        $markdown = $this->generateCheatSheetInternal($f['description']);
        if ($markdown) {
            $ins = $db->prepare("INSERT INTO formation_ai_metadata (id_formation, cheatsheet_markdown) VALUES (?, ?) ON DUPLICATE KEY UPDATE cheatsheet_markdown = ?");
            $ins->execute([$id_formation, $markdown, $markdown]);
            return ['success' => true, 'html_content' => $this->markdownToHtml($markdown)];
        }
        return ['success' => false];
    }

    public function generateFicheFromChat($history)
    {
        if (empty($history)) {
            return ['success' => false, 'message' => 'L\'historique est vide.'];
        }

        $system_prompt = "Tu es l'IA Pédagogique Aptus. Ton but est de créer une fiche de révision riche, structurée et motivante. Analysis de conversation. RÈGLES : AUCUN EMOJI, structure claire (#, ##, ###), ton académique.";

        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "system", "content" => $system_prompt],
                ["role" => "user", "content" => $history]
            ],
            "temperature" => 0.5
        ];

        $res = $this->callAI($data);
        if ($res['success']) {
            return [
                'success' => true,
                'fiche_html' => $this->markdownToHtml($res['content'])
            ];
        }
        return ['success' => false, 'message' => 'L\'IA n\'a pas pu générer la fiche.'];
    }

    public function generateSyllabus($titre, $domaine, $niveau)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Strict JSON Output"], ["role" => "user", "content" => "Expert Aptus AI. Génère syllabus JSON pour '$titre' ($domaine, $niveau). Structure: {syllabus:[{chapitre,description,duree}],resume_global}."]],
            "temperature" => 0.7,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false, 'message' => $res['message']]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    public function getEmotionStats($id_formation)
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id GROUP BY emotion_detectee");
            $stmt->execute(['id' => $id_formation]);
            return ['success' => true, 'stats' => $stmt->fetchAll()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function analyzeStudentEmotions($stats)
    {
        if (empty($stats))
            return json_encode(['success' => true, 'data' => ['analyseGlobale' => "En attente...", 'conseils' => []]]);
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "system", "content" => "Strict JSON Output. Format: {\"analyseGlobale\": \"...\", \"conseils\": [\"...\", \"...\"]}"],
                ["role" => "user", "content" => "Analyse emotions JSON: " . json_encode($stats)]
            ],
            "temperature" => 0.6,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false, 'message' => $res['message']]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    public function selfHealingSyllabus($titre)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => "Veille Aptus AI. Nouveautés pour '$titre'. JSON: {has_update:bool, headline, content}."]],
            "temperature" => 0.4,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data, 15);
        if (!$res['success'])
            return json_encode(['success' => false, 'has_update' => false]);
        return json_encode(['success' => true] + json_decode($res['content'], true));
    }

    public function generateCrashCourse($prompt, $catalogue)
    {
        $ctx = "";
        foreach ($catalogue as $f)
            $ctx .= "- {$f['titre']} (ID:{$f['id_formation']})\n";
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => "RAG Aptus AI. Catalogue:\n$ctx\nBesoin: '$prompt'. Génère Crash Course 30min JSON: {title, modules:[{formation_id, chapitre, objectif, duree}]}."]],
            "temperature" => 0.6,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    public function generateCourseFactory($prompt)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Strict JSON"], ["role" => "user", "content" => "Génère formation JSON pour: '$prompt'. {titre, domaine, niveau, description_riche, modules:[{titre,description,duree}]}."]],
            "temperature" => 0.7,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    public function appendSyllabus($id, $html)
    {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
            $s->execute(['id' => $id]);
            $row = $s->fetch();
            if ($row) {
                $desc = $row['description'];
                if (strpos($desc, '<!-- AI_SYLLABUS_START -->') !== false)
                    $desc = preg_replace('/<!-- AI_SYLLABUS_START -->.*?<!-- AI_SYLLABUS_END -->/s', $html, $desc);
                else
                    $desc .= $html;
                $u = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
                return json_encode(['success' => $u->execute(['desc' => $desc, 'id' => $id])]);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false]);
        }
        return json_encode(['success' => false]);
    }

    public function saveStudentEmotion($id_c, $id_f, $em)
    {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("INSERT INTO rapport_emotions (id_candidat, id_formation, emotion_detectee) VALUES (:c, :f, :e)");
            return json_encode(['success' => $s->execute(['c' => $id_c, 'f' => $id_f, 'e' => $em])]);
        } catch (Exception $e) { return json_encode(['success' => false]); }
    }

    public function saveStudentTranscript($id_u, $id_f, $text)
    {
        try {
            $db = config::getConnexion();
            // Création automatique de la table si absente
            $db->exec("CREATE TABLE IF NOT EXISTS rapport_transcripts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_formation INT,
                id_utilisateur INT,
                transcript_text TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $s = $db->prepare("INSERT INTO rapport_transcripts (id_utilisateur, id_formation, transcript_text) VALUES (:u, :f, :t)");
            return json_encode(['success' => $s->execute(['u' => $id_u, 'f' => $id_f, 't' => $text])]);
        } catch (Exception $e) { return json_encode(['success' => false]); }
    }

    public function getRecentTranscripts($id_f)
    {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("SELECT t.transcript_text, u.nom, t.created_at 
                             FROM rapport_transcripts t 
                             JOIN utilisateur u ON t.id_utilisateur = u.id_utilisateur 
                             WHERE t.id_formation = ? 
                             AND t.created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                             ORDER BY t.created_at DESC LIMIT 20");
            $s->execute([$id_f]);
            return ['success' => true, 'transcripts' => $s->fetchAll()];
        } catch (Exception $e) { return ['success' => false]; }
    }

    public function consolidateEmotions($id_f)
    {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id GROUP BY emotion_detectee");
            $s->execute(['id' => $id_f]);
            $stats = $s->fetchAll();
            if (empty($stats))
                return json_encode(['success' => true]);
            $aiRes = $this->analyzeStudentEmotions($stats);
            $report = json_decode($aiRes, true);
            $summary = $report['data']['analyseGlobale'] ?? "Session terminée.";
            $html = "\n<div class='ai-report'><h4>Bilan IA de la séance</h4><p>$summary</p></div>";
            $this->appendSyllabus($id_f, $html);
            $d = $db->prepare("DELETE FROM rapport_emotions WHERE id_formation = :id");
            $d->execute(['id' => $id_f]);
            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false]);
        }
    }

    private function generateMindMapInternal($content)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Génère code Mermaid mindmap. Pas de blabla."], ["role" => "user", "content" => "Mindmap pour : " . $content]],
            "temperature" => 0.4
        ];
        $res = $this->callAI($data);
        return $res['success'] ? $res['content'] : null;
    }

    private function generateCheatSheetInternal($content)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Génère fiche Markdown. Pas d'emojis."], ["role" => "user", "content" => "Fiche pour : " . $content]],
            "temperature" => 0.5
        ];
        $res = $this->callAI($data);
        return $res['success'] ? $res['content'] : null;
    }

    public function markdownToHtml($markdown)
    {
        $markdown = trim($markdown);
        $markdown = preg_replace('/^# (.*)$/m', '<h1 style="color:#00A3DA; font-size:1.8rem; margin:0 0 2rem; text-align:center; font-weight:800; text-transform:uppercase;">$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.*)$/m', '<h2 style="color:#111827; font-size:1.25rem; margin:1.8rem 0 1rem; border-left:4px solid #00A3DA; padding-left:15px; font-weight:700;">$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.*)$/m', '<h3 style="color:#111827; font-size:1.1rem; margin:1.5rem 0 0.8rem; font-weight:700;">$1</h3>', $markdown);
        $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);
        $markdown = nl2br($markdown);
        $markdown = preg_replace('/(<br\s*\/?>)\s*(<h[1-4]|<div)/i', '$2', $markdown);
        $markdown = preg_replace('/(<\/h[1-4]>|<\/div>)\s*(<br\s*\/?>)/i', '$1', $markdown);
        return '<div class="aptus-fiche-content" style="font-family:\'Inter\', sans-serif; color:#374151;">
                <style>.aptus-fiche-content > *:first-child { margin-top: 0 !important; }</style>' . $markdown . '</div>';
    }

    /**
     * Public wrapper for the internal AI call — used by ChatController.
     * Accepts the same data array format as the Groq API.
     */
    public function generateGenericResponse(array $data): array
    {
        return $this->callAI($data);
    }

    private function callAI($data, $timeout = 30)
    {
        foreach ($this->groqKeys as $key) {
            $res = $this->requestGroq($data, $key, $timeout);
            if ($res['success'])
                return $res;
        }
        if (!empty($this->geminiKey))
            return $this->requestGemini($data, $this->geminiKey, $timeout);
        return ['success' => false, 'message' => 'APIs Unavailable'];
    }

    private function requestGroq($data, $key, $timeout)
    {
        $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $key]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            return ['success' => true, 'content' => $json['choices'][0]['message']['content'] ?? ''];
        }
        return ['success' => false];
    }

    private function requestGemini($data, $key, $timeout)
    {
        $prompt = "";
        foreach ($data['messages'] as $m)
            $prompt .= $m['content'] . "\n";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $key;
        $body = ["contents" => [["parts" => [["text" => $prompt]]]]];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $text = preg_replace('/^```json\s*|```$/', '', trim($text));
            return ['success' => true, 'content' => $text];
        }
        return ['success' => false];
    }
}
