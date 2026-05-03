<?php
require_once __DIR__ . '/../config.php';

$keys_path = __DIR__ . '/../api_keys.php';
if (file_exists($keys_path)) {
    require_once $keys_path;
} else {
    die(json_encode(['success' => false, 'message' => 'Fichier de configuration API introuvable.']));
}

class AIController
{
    private $groqKeys = [];
    private $geminiKey = '';

    /**
     * 🧠 RÉCUPÉRATION / GÉNÉRATION DE MINDMAP (MVC COMPLIANT)
     */
    public function getMindMap($id_formation)
    {
        $db = config::getConnexion();
        $s = $db->prepare("SELECT mindmap_mermaid FROM formation_ai_metadata WHERE id_formation = ?");
        $s->execute([$id_formation]);
        $cached = $s->fetch();

        if ($cached && !empty($cached['mindmap_mermaid'])) {
            return ['success' => true, 'mermaid_code' => $cached['mindmap_mermaid']];
        }

        // Sinon on génère via FormationController
        require_once __DIR__ . '/FormationController.php';
        $formC = new FormationController();
        $f = $formC->getFormationById($id_formation);
        if (!$f) return ['success' => false, 'message' => 'Formation introuvable.'];

        $mermaid = $this->generateMindMap($f['description']);
        if ($mermaid) {
            $ins = $db->prepare("INSERT INTO formation_ai_metadata (id_formation, mindmap_mermaid) VALUES (?, ?) ON DUPLICATE KEY UPDATE mindmap_mermaid = ?");
            $ins->execute([$id_formation, $mermaid, $mermaid]);
            return ['success' => true, 'mermaid_code' => $mermaid];
        }
        return ['success' => false];
    }

    /**
     * 📄 RÉCUPÉRATION / GÉNÉRATION DE FICHE RÉCAP (MVC COMPLIANT)
     */
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
        if (!$f) return ['success' => false, 'message' => 'Formation introuvable.'];

        $markdown = $this->generateCheatSheet($f['description']);
        if ($markdown) {
            $ins = $db->prepare("INSERT INTO formation_ai_metadata (id_formation, cheatsheet_markdown) VALUES (?, ?) ON DUPLICATE KEY UPDATE cheatsheet_markdown = ?");
            $ins->execute([$id_formation, $markdown, $markdown]);
            return ['success' => true, 'html_content' => $this->markdownToHtml($markdown)];
        }
        return ['success' => false];
    }

    public function __construct()
    {
        if (defined('GROQ_API_KEYS'))
            $this->groqKeys = GROQ_API_KEYS;
        if (defined('GEMINI_API_KEY'))
            $this->geminiKey = GEMINI_API_KEY;
    }

    /**
     * SYSTÈME DE FAILOVER HYBRIDE (GROQ -> GEMINI)
     */
    private function callAI($data, $timeout = 30)
    {
        // 1. Tenter avec GROQ (Rotation des clés)
        foreach ($this->groqKeys as $key) {
            $res = $this->requestGroq($data, $key, $timeout);
            if ($res['success'])
                return $res;
        }

        // 2. Tenter avec GEMINI (Fallback ultime)
        if (!empty($this->geminiKey)) {
            return $this->requestGemini($data, $this->geminiKey, $timeout);
        }

        return ['success' => false, 'message' => 'Toutes les APIs (Groq & Gemini) ont échoué.'];
    }

    /**
     * 🛠️ PARSER MARKDOWN MINIMALISTE (Pour les fiches AI)
     */
    public function markdownToHtml($markdown) {
        // Nettoyage initial
        $markdown = trim($markdown);

        // Headers (Seul le H1 reste en bleu, les autres en noir)
        $markdown = preg_replace('/^# (.*)$/m', '<h1 style="color:#00A3DA; font-size:1.75rem; margin:2rem 0 1.5rem; text-align:center; font-weight:800; text-transform:uppercase; letter-spacing:0.05em;">$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.*)$/m', '<h2 style="color:#111827; font-size:1.25rem; margin:1.8rem 0 1rem; border-left:4px solid #00A3DA; padding-left:15px; font-weight:700; text-transform:uppercase; letter-spacing:0.02em;">$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.*)$/m', '<h3 style="color:#111827; font-size:1.1rem; margin:1.5rem 0 0.8rem; font-weight:700; display:flex; align-items:center; gap:10px;">$1</h3>', $markdown);
        $markdown = preg_replace('/^#### (.*)$/m', '<h4 style="color:#111827; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.1em; margin:1.2rem 0 0.6rem; font-weight:800;">• $1</h4>', $markdown);
        
        // Bold
        $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong style="color:#111827; font-weight:700;">$1</strong>', $markdown);
        
        // Lists (Sobres)
        $markdown = preg_replace('/^\* (.*)$/m', '<div style="margin-bottom:0.8rem; padding-left:1.5rem; position:relative; color:#374151; line-height:1.6;"><span style="position:absolute; left:0; color:#00A3DA;">■</span> $1</div>', $markdown);
        $markdown = preg_replace('/^- (.*)$/m', '<div style="margin-bottom:0.8rem; padding-left:1.5rem; position:relative; color:#374151; line-height:1.6;"><span style="position:absolute; left:0; color:#00A3DA;">→</span> $1</div>', $markdown);
        
        // Paragraphs
        $markdown = nl2br($markdown);
        
        return '<div class="aptus-fiche-content" style="font-family:\'Inter\', -apple-system, sans-serif; color:#374151;">' . $markdown . '</div>';
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
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            return ['success' => true, 'content' => $json['choices'][0]['message']['content'] ?? ''];
        }
        return ['success' => false];
    }

    private function requestGemini($data, $key, $timeout)
    {
        // Adaptation du format OpenAI vers Gemini
        $prompt = "";
        foreach ($data['messages'] as $m) {
            $prompt .= $m['content'] . "\n";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;
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
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            // Nettoyage si Gemini ajoute des backticks ```json
            $text = preg_replace('/^```json\s*|```$/', '', trim($text));
            return ['success' => true, 'content' => $text];
        }
        return ['success' => false, 'message' => 'Gemini Error: ' . $httpCode];
    }

    // --- MÉTHODES MÉTIER ---

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

    public function analyzeStudentEmotions($stats)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Strict JSON Output"], ["role" => "user", "content" => "Analyse emotions JSON: " . json_encode($stats) . ". Structure: {analyseGlobale, conseils:[3 conseils actionnables]}."]],
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
            "messages" => [["role" => "user", "content" => "Veille Aptus AI. Nouveautés mois actuel pour '$titre'. JSON: {has_update:bool, headline, content}."]],
            "temperature" => 0.4,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data, 15);
        if (!$res['success'])
            return json_encode(['success' => false, 'has_update' => false]);
        $aiData = json_decode($res['content'], true);
        return json_encode([
            'success' => true,
            'has_update' => !empty($aiData['has_update']),
            'headline' => $aiData['headline'] ?? '',
            'content' => $aiData['content'] ?? ''
        ]);
    }

    public function generateCrashCourse($prompt, $catalogue)
    {
        $ctx = "";
        foreach ($catalogue as $f)
            $ctx .= "- {$f['titre']} (ID:{$f['id_formation']})\n";
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => "RAG Aptus AI. Catalogue:\n$ctx\nBesoin: '$prompt'. Génère Crash Course 30min JSON: {title, subtitle, estimated_time, modules:[{formation_id, formation_titre, chapitre, objectif, duree}], conseil_final}."]],
            "temperature" => 0.6,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false, 'message' => $res['message']]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    public function generateCourseFactory($prompt)
    {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "system", "content" => "Strict JSON Output"], ["role" => "user", "content" => "Génère formation complète JSON pour: '$prompt'. Structure: {titre, domaine, niveau, duree, description_courte, prerequis, description_riche, modules:[{titre,description,duree}], tags:[]}."]],
            "temperature" => 0.7,
            "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success'])
            return json_encode(['success' => false, 'message' => $res['message']]);
        return json_encode(['success' => true, 'data' => json_decode($res['content'], true)]);
    }

    // DB Helpers
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
                else {
                    if (strpos($desc, '<!-- APTUS_RESOURCES:') !== false)
                        $desc = str_replace('<!-- APTUS_RESOURCES:', $html . '<!-- APTUS_RESOURCES:', $desc);
                    else
                        $desc .= $html;
                }
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
        } catch (Exception $e) {
            return json_encode(['success' => false]);
        }
    }

    /**
     * 🗄️ OPTIMISATION : Consolidation des données (Point 2)
     * Transforme des milliers de lignes en un résumé IA et purge la table.
     */
    public function consolidateEmotions($id_f)
    {
        try {
            $db = config::getConnexion();
            // 1. Récupérer les stats brutes
            $s = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id GROUP BY emotion_detectee");
            $s->execute(['id' => $id_f]);
            $stats = $s->fetchAll();
            if (empty($stats))
                return json_encode(['success' => true, 'message' => 'Rien à consolider']);

            // 2. Générer le résumé via IA
            $aiRes = $this->analyzeStudentEmotions($stats);
            $report = json_decode($aiRes, true);
            $summary = $report['emotions_predominantes'] ?? "Session terminée.";

            // 3. Sauvegarder le résumé dans la formation (ou table dédiée)
            // Ici on l'ajoute à la description de la formation comme un "Bilan IA"
            $html = "\n<!-- AI_CONSOLIDATED_REPORT_START -->\n<div class='ai-report'><h4>Bilan IA de la séance</h4><p>$summary</p></div>\n<!-- AI_CONSOLIDATED_REPORT_END -->";
            $this->appendSyllabus($id_f, $html);

            // 4. PURGE : On supprime les données brutes pour éviter l'obésité de la BDD
            $d = $db->prepare("DELETE FROM rapport_emotions WHERE id_formation = :id");
            $d->execute(['id' => $id_f]);

            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 📡 OPTIMISATION : Polling Tuteur (Point 1)
     */
    /**
     * 🧠 GÉNÉRATION DE FICHE DE RÉVISION DEPUIS LE CHAT (CONFORMITÉ MVC)
     */
    public function generateFicheFromChat($history)
    {
        if (empty($history)) {
            return ['success' => false, 'message' => 'L\'historique est vide.'];
        }

        $system_prompt = "Tu es un tuteur pédagogique expert Aptus AI. Ton but est d'aider un étudiant à réviser de manière académique et professionnelle. 
        L'utilisateur va te fournir une transcription brute d'une conversation entre un étudiant et une IA concernant un cours.
        Tâche : Analyse cette conversation et extrais-en une 'Fiche de Synthèse' claire, structurée et sobre.
        Règles :
        1. Ne garde que les concepts fondamentaux, les définitions et les points techniques abordés.
        2. INTERDICTION ABSOLUE : N'utilise aucun emoji (pas d'icônes, pas de smileys).
        3. Formate le résultat en Markdown propre avec des titres hiérarchisés.
        4. Le ton doit être purement pédagogique, formel et synthétique.
        5. Ne fais aucune introduction ni conclusion, commence directement par le titre du cours.";

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
}
