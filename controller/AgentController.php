<?php
session_start();
require_once __DIR__ . '/EnvLoader.php';

class AgentController {

    private $openRouterKey;
    private $groqApiKey;
    private $geminiKey;

    public function __construct() {
        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            $envPath = dirname(__DIR__) . '/.env';
        }
        EnvLoader::load($envPath);

        $this->openRouterKey = $_ENV['OPENROUTER_API_KEY'] ?? $_SERVER['OPENROUTER_API_KEY'] ?? getenv('OPENROUTER_API_KEY') ?? '';
        $this->groqApiKey = $_ENV['GROQ_API_KEY'] ?? $_SERVER['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?? '';
        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '';

        if (!isset($_SESSION['agent_history'])) {
            $_SESSION['agent_history'] = [];
        }
    }

    public function handleRequest() {
        header('Content-Type: application/json; charset=utf-8');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!isset($data['text']) && !isset($data['audio'])) {
            echo json_encode(['error' => 'No input provided']);
            return;
        }

        $userText = $data['text'] ?? '';
        $transcription = '';

        // 1. PHASE STT : Si de l'audio est envoyé, on le transcrit via GROQ (Whisper)
        if (isset($data['audio']) && !empty($data['audio'])) {
            $transcription = $this->transcribeWithGroq($data['audio'], $data['mimeType'] ?? 'audio/webm');
            if (!$transcription && !$userText) {
                echo json_encode(['spoken_text' => "Je n'ai pas bien entendu votre message audio.", 'action' => null]);
                return;
            }
        }

        // Combiner le texte tapé et la transcription
        $finalUserPrompt = trim($userText . " " . $transcription);
        
        if (empty($finalUserPrompt)) {
            echo json_encode(['spoken_text' => "Comment puis-je vous aider ?", 'action' => null]);
            return;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        // 2. PHASE INTELLIGENCE : On envoie le texte final à OpenRouter
        $response = $this->askOpenRouter($finalUserPrompt, $referer);
        
        // 3. FALLBACK : Si OpenRouter échoue, on tente Gemini en direct
        if (!$response && !empty($this->geminiKey)) {
            $response = $this->askGeminiDirect($finalUserPrompt, $referer);
        }

        if ($response) {
            echo json_encode($response);
        } else {
            echo json_encode([
                'spoken_text' => "Désolé, je rencontre des difficultés techniques avec mes services d'intelligence.",
                'action' => null
            ]);
        }
    }

    private function transcribeWithGroq($base64Audio, $mimeType) {
        if (empty($this->groqApiKey)) return null;

        $audioData = base64_decode($base64Audio);
        $ext = (strpos($mimeType, 'mp4') !== false) ? '.mp4' : '.webm';
        $tmpFile = sys_get_temp_dir() . '/stt_' . uniqid() . $ext;
        file_put_contents($tmpFile, $audioData);

        $ch = curl_init("https://api.groq.com/openai/v1/audio/transcriptions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => curl_file_create($tmpFile),
            'model' => 'whisper-large-v3-turbo',
            'language' => 'fr'
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->groqApiKey]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);
        curl_close($ch);
        @unlink($tmpFile);

        $data = json_decode($res, true);
        return $data['text'] ?? null;
    }

    private function askOpenRouter($prompt, $referer = '') {
        if (empty($this->openRouterKey)) return null;

        $systemPrompt = $this->getSystemPrompt($referer);
        
        // Construire l'historique
        $messages = [["role" => "system", "content" => $systemPrompt]];
        foreach ($_SESSION['agent_history'] as $h) {
            $role = ($h['role'] === 'user') ? 'user' : 'assistant';
            $content = "";
            foreach($h['parts'] as $p) if(isset($p['text'])) $content .= $p['text'];
            $messages[] = ["role" => $role, "content" => $content];
        }
        $messages[] = ["role" => "user", "content" => $prompt];

        $payload = [
            "model" => "google/gemini-2.5-flash", // Modèle ultra-rapide et performant
            "messages" => $messages,
            "response_format" => ["type" => "json_object"],
            "max_tokens" => 500
        ];

        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openRouterKey,
            'HTTP-Referer: http://localhost/aptus_first_official_version',
            'X-Title: Aptus AI Agent'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $rawContent = $data['choices'][0]['message']['content'];
            $parsed = json_decode($rawContent, true);
            if ($parsed) {
                // Update history
                $_SESSION['agent_history'][] = ["role" => "user", "parts" => [["text" => $prompt]]];
                $_SESSION['agent_history'][] = ["role" => "model", "parts" => [["text" => $rawContent]]];
                if (count($_SESSION['agent_history']) > 10) $_SESSION['agent_history'] = array_slice($_SESSION['agent_history'], -10);
                return $parsed;
            }
        }
        return null;
    }

    private function askGeminiDirect($prompt, $referer = '') {
        if (empty($this->geminiKey)) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->geminiKey;
        
        $contents = [];
        foreach ($_SESSION['agent_history'] as $h) $contents[] = $h;
        $contents[] = ["role" => "user", "parts" => [["text" => $prompt]]];

        $payload = [
            "system_instruction" => ["parts" => [["text" => $this->getSystemPrompt($referer)]]],
            "contents" => $contents
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($text) {
            // Extract JSON
            $start = strpos($text, '{');
            $end = strrpos($text, '}');
            if ($start !== false && $end !== false) {
                $json = substr($text, $start, $end - $start + 1);
                return json_decode($json, true);
            }
        }
        return null;
    }

    private function getSystemPrompt($referer = '') {
        $isBackoffice = (strpos($referer, '/backoffice/') !== false);
        
        $contextSpace = $isBackoffice ? "Espace Admin (Backoffice)" : "Espace Candidat/Entreprise (Frontoffice)";
        
        $prompt = "Tu es l'assistant d'accessibilité IA du site Aptus. Tu parles français.\n";
        $prompt .= "L'utilisateur se trouve actuellement dans : " . $contextSpace . ".\n";
        $prompt .= "IMPORTANT: Ne propose JAMAIS de naviguer vers le Frontoffice si l'utilisateur est dans le Backoffice, et inversement, sauf demande EXPLICITE.\n\n";
        
        $prompt .= "Réponds TOUJOURS en JSON uniquement au format suivant :\n";
        $prompt .= "{\n";
        $prompt .= "  \"spoken_text\": \"Texte court à dire\",\n";
        $prompt .= "  \"action\": { \"type\": \"navigate\"|\"script\", \"target\": \"URL\", \"code\": \"JS\" } // action peut être null\n";
        $prompt .= "}\n\n";

        $prompt .= "### ACTIONS POSSIBLES via 'script' (code JS à injecter) :\n";
        $prompt .= "Règle d'or : N'attends JAMAIS que l'utilisateur te donne un ID technique ou te demande la permission. Déduis l'ID et exécute l'action directement.\n";
        $prompt .= "Règle d'or 2 : Si l'utilisateur te demande un briefing ou un résumé d'un rapport spécifique (par exemple via le bouton Écouter), GÉNÈRE un briefing réaliste et pertinent de 3 phrases basé sur le titre fourni dans le message. Ne dis JAMAIS que tu ne peux pas le faire ou que tu n'as pas accès au contenu.\n";
        if ($isBackoffice) {
            $prompt .= "- Créer / Ajouter un nouveau rapport : `openRapportModal('add');`\n";
            $prompt .= "- Remplir un champ du formulaire : Associe naturellement le mot de l'utilisateur à l'ID. (Titre -> 'rapport-titre', Auteur -> 'rapport-auteur', Description -> 'rapport-desc', Région -> 'rapport-region', Secteur -> 'tag-input'). Exemple de script généré : `document.getElementById('rapport-titre').value = 'ValeurDemandée';`\n";
            $prompt .= "- Passer à l'étape suivante (Suivant) : `if(typeof nextStep === 'function') { nextStep(); } else { let b = document.getElementById('btn-next-step'); if(b) b.click(); }`\n";
        } else {
            $prompt .= "- Lire un rapport : `let a=document.querySelector('.report-card a[href^=\"veille_details.php\"]'); if(a) a.click(); else alert('Aucun rapport trouvé à lire.');`\n";
            $prompt .= "- Écouter l'écho / Briefing audio : `let btn=document.querySelector('button[title=\"Briefing Audio\"]'); if(btn) btn.click(); else alert('Bouton introuvable.');`\n";
            $prompt .= "- Actualiser IA : `let f=document.getElementById('btn-refresh-forecast'); if(f) f.click();`\n";
        }

        $prompt .= "\n### CARTE DE NAVIGATION (type: 'navigate') :\n";
        if ($isBackoffice) {
            $prompt .= "- Dashboard : /aptus_first_official_version/view/backoffice/dashboard.php\n";
            $prompt .= "- Admin Veille (Rapports) : /aptus_first_official_version/view/backoffice/veille_admin.php\n";
            $prompt .= "- Admin Offres : /aptus_first_official_version/view/backoffice/offres_admin.php\n";
            $prompt .= "- Admin Utilisateurs : /aptus_first_official_version/view/backoffice/users.php\n";
        } else {
            $prompt .= "- Veille (Rapports) : /aptus_first_official_version/view/frontoffice/veille_feed.php\n";
            $prompt .= "- Offres d'emploi : /aptus_first_official_version/view/frontoffice/jobs_feed.php\n";
            $prompt .= "- Espace Candidat (CV) : /aptus_first_official_version/view/frontoffice/cv_my.php\n";
        }
        
        return $prompt;
    }
}

if (basename($_SERVER['PHP_SELF']) === 'AgentController.php') {
    $controller = new AgentController();
    $controller->handleRequest();
}