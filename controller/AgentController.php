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

        // 2. PHASE INTELLIGENCE : On envoie le texte final à OpenRouter
        $response = $this->askOpenRouter($finalUserPrompt);
        
        // 3. FALLBACK : Si OpenRouter échoue, on tente Gemini en direct
        if (!$response && !empty($this->geminiKey)) {
            $response = $this->askGeminiDirect($finalUserPrompt);
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

    private function askOpenRouter($prompt) {
        if (empty($this->openRouterKey)) return null;

        $systemPrompt = $this->getSystemPrompt();
        
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
            "model" => "google/gemini-2.0-flash-001", // Modèle ultra-rapide et performant
            "messages" => $messages,
            "response_format" => ["type" => "json_object"]
        ];

        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openRouterKey,
            'HTTP-Referer: http://localhost/aptus',
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

    private function askGeminiDirect($prompt) {
        if (empty($this->geminiKey)) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->geminiKey;
        
        $contents = [];
        foreach ($_SESSION['agent_history'] as $h) $contents[] = $h;
        $contents[] = ["role" => "user", "parts" => [["text" => $prompt]]];

        $payload = [
            "system_instruction" => ["parts" => [["text" => $this->getSystemPrompt()]]],
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

    private function getSystemPrompt() {
        return "Tu es l'assistant d'accessibilité IA du site Aptus. Tu parles français.
Réponds TOUJOURS en JSON uniquement :
{
  \"spoken_text\": \"Texte court à dire\",
  \"action\": { \"type\": \"navigate\"|\"script\", \"target\": \"URL\", \"code\": \"JS\" }
}
### CARTE :
- Veille : /aptus_first_official_version/view/frontoffice/veille_feed.php
- Admin Veille : /aptus_first_official_version/view/backoffice/veille_admin.php
- Dashboard : /aptus_first_official_version/view/backoffice/dashboard.php";
    }
}

if (basename($_SERVER['PHP_SELF']) === 'AgentController.php') {
    $controller = new AgentController();
    $controller->handleRequest();
}