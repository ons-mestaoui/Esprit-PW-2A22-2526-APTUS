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

    public function __construct()
    {
        if (defined('GROQ_API_KEYS')) $this->groqKeys = GROQ_API_KEYS;
        if (defined('GEMINI_API_KEY')) $this->geminiKey = GEMINI_API_KEY;
    }

    /**
     * SYSTÈME DE FAILOVER HYBRIDE (GROQ -> GEMINI)
     */
    private function callAI($data, $timeout = 30)
    {
        // 1. Tenter avec GROQ (Rotation des clés)
        foreach ($this->groqKeys as $key) {
            $res = $this->requestGroq($data, $key, $timeout);
            if ($res['success']) return $res;
        }

        // 2. Tenter avec GEMINI (Fallback ultime)
        if (!empty($this->geminiKey)) {
            return $this->requestGemini($data, $this->geminiKey, $timeout);
        }

        return ['success' => false, 'message' => 'Toutes les APIs (Groq & Gemini) ont échoué.'];
    }

    private function requestGroq($data, $key, $timeout) {
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

    private function requestGemini($data, $key, $timeout) {
        // Adaptation du format OpenAI vers Gemini
        $prompt = "";
        foreach($data['messages'] as $m) { $prompt .= $m['content'] . "\n"; }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;
        $body = [ "contents" => [[ "parts" => [[ "text" => $prompt ]] ]] ];

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

    public function generateSyllabus($titre, $domaine, $niveau) {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [[ "role" => "system", "content" => "Strict JSON Output" ], [ "role" => "user", "content" => "Expert Aptus AI. Génère syllabus JSON pour '$titre' ($domaine, $niveau). Structure: {syllabus:[{chapitre,description,duree}],resume_global}." ]],
            "temperature" => 0.7, "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success']) return json_encode(['success'=>false, 'message'=>$res['message']]);
        return json_encode(['success'=>true, 'data'=>json_decode($res['content'],true)]);
    }

    public function analyzeStudentEmotions($stats) {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [[ "role" => "system", "content" => "Strict JSON Output" ], [ "role" => "user", "content" => "Analyse emotions JSON: " . json_encode($stats) . ". Structure: {analyseGlobale, conseils:[3 conseils actionnables]}." ]],
            "temperature" => 0.6, "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success']) return json_encode(['success'=>false, 'message'=>$res['message']]);
        return json_encode(['success'=>true, 'data'=>json_decode($res['content'],true)]);
    }

    public function selfHealingSyllabus($titre) {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [[ "role" => "user", "content" => "Veille Aptus AI. Nouveautés mois actuel pour '$titre'. JSON: {has_update:bool, headline, content}." ]],
            "temperature" => 0.4, "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data, 15);
        if (!$res['success']) return json_encode(['success'=>false, 'has_update'=>false]);
        $aiData = json_decode($res['content'], true);
        return json_encode([
            'success' => true,
            'has_update' => !empty($aiData['has_update']),
            'headline' => $aiData['headline'] ?? '',
            'content' => $aiData['content'] ?? ''
        ]);
    }

    public function generateCrashCourse($prompt, $catalogue) {
        $ctx = ""; foreach($catalogue as $f) $ctx .= "- {$f['titre']} (ID:{$f['id_formation']})\n";
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [[ "role" => "user", "content" => "RAG Aptus AI. Catalogue:\n$ctx\nBesoin: '$prompt'. Génère Crash Course 30min JSON: {title, subtitle, estimated_time, modules:[{formation_id, formation_titre, chapitre, objectif, duree}], conseil_final}." ]],
            "temperature" => 0.6, "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success']) return json_encode(['success'=>false, 'message'=>$res['message']]);
        return json_encode(['success'=>true, 'data'=>json_decode($res['content'],true)]);
    }

    public function generateCourseFactory($prompt) {
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role"=>"system","content"=>"Strict JSON Output"],["role"=>"user","content"=>"Génère formation complète JSON pour: '$prompt'. Structure: {titre, domaine, niveau, duree, description_courte, prerequis, description_riche, modules:[{titre,description,duree}], tags:[]}."]],
            "temperature" => 0.7, "response_format" => ["type" => "json_object"]
        ];
        $res = $this->callAI($data);
        if (!$res['success']) return json_encode(['success'=>false, 'message'=>$res['message']]);
        return json_encode(['success'=>true, 'data'=>json_decode($res['content'],true)]);
    }

    // DB Helpers
    public function appendSyllabus($id, $html) {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("SELECT description FROM formation WHERE id_formation = :id"); $s->execute(['id'=>$id]);
            $row = $s->fetch();
            if ($row) {
                $desc = $row['description'];
                if (strpos($desc, '<!-- AI_SYLLABUS_START -->') !== false) $desc = preg_replace('/<!-- AI_SYLLABUS_START -->.*?<!-- AI_SYLLABUS_END -->/s', $html, $desc);
                else {
                    if (strpos($desc, '<!-- APTUS_RESOURCES:') !== false) $desc = str_replace('<!-- APTUS_RESOURCES:', $html . '<!-- APTUS_RESOURCES:', $desc);
                    else $desc .= $html;
                }
                $u = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
                return json_encode(['success' => $u->execute(['desc'=>$desc, 'id'=>$id])]);
            }
        } catch (Exception $e) { return json_encode(['success'=>false]); }
        return json_encode(['success'=>false]);
    }

    public function saveStudentEmotion($id_c, $id_f, $em) {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("INSERT INTO rapport_emotions (id_candidat, id_formation, emotion_detectee) VALUES (:c, :f, :e)");
            return json_encode(['success' => $s->execute(['c'=>$id_c, 'f'=>$id_f, 'e'=>$em])]);
        } catch (Exception $e) { return json_encode(['success'=>false]); }
    }

    /**
     * 🗄️ OPTIMISATION : Consolidation des données (Point 2)
     * Transforme des milliers de lignes en un résumé IA et purge la table.
     */
    public function consolidateEmotions($id_f) {
        try {
            $db = config::getConnexion();
            // 1. Récupérer les stats brutes
            $s = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id GROUP BY emotion_detectee");
            $s->execute(['id'=>$id_f]);
            $stats = $s->fetchAll();
            if (empty($stats)) return json_encode(['success'=>true, 'message'=>'Rien à consolider']);

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
            $d->execute(['id'=>$id_f]);

            return json_encode(['success'=>true]);
        } catch (Exception $e) { return json_encode(['success'=>false, 'message'=>$e->getMessage()]); }
    }

    /**
     * 📡 OPTIMISATION : Polling Tuteur (Point 1)
     */
    public function getEmotionStats($id_f) {
        try {
            $db = config::getConnexion();
            $s = $db->prepare("SELECT emotion_detectee, COUNT(*) as count FROM rapport_emotions WHERE id_formation = :id GROUP BY emotion_detectee");
            $s->execute(['id'=>$id_f]);
            return ['success'=>true, 'stats'=>$s->fetchAll()];
        } catch (Exception $e) { return ['success'=>false]; }
    }
}
