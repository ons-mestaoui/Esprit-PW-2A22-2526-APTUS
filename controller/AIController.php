<?php

class AIController {
    
    private string $apiKey = '';
    private string $model = '';
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private string $ollamaUrl = 'http://localhost:11434/api/chat';

    public function __construct() {
        $this->loadEnv();
    }

    private function loadEnv(): void {
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $_ENV[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
        $this->apiKey = $_ENV['GROQ_API_KEY'] ?? '';
        $this->model = $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant';
    }

    public function polishText(string $text, string $context, string $mode = 'polish'): string {
        if (empty(trim($text))) {
            return $text;
        }

        $systemPrompt = "";
        if ($mode === 'correct') {
            $systemPrompt = "Tu es un correcteur linguistique expert en français. Ton but est l'EFFICACITÉ. Corrige les fautes d'orthographe, de grammaire et de ponctuation. Garde la structure originale. Ne rajoute AUCUNE phrase d'introduction ou de conclusion. Donne directement les tirets sans étapes.";
        } else {
            // Mode Polish
            $systemPrompt = "Tu es un expert RH. Reformule ce texte pour un CV de manière CONCISE, PROFESSIONNELLE et DIRECTE. 
            CONSIGNES :
            1. Utilise des tirets (listes à puces), maximum 5.
            2. Termine chaque point par un point-virgule ';'.
            3. GARDE TOUS LES MOTS TECHNIQUES. Ne pas sur-reformuler, reste factuel et percutant.
            4. Réponds UNIQUEMENT avec le contenu. AUCUNE introduction ou conclusion.";
        }

        // Utilisation de Ollama Llama 3.1
        $payload = json_encode([
            "model" => "llama3.1",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "Texte à traiter : " . $text]
            ],
            "stream" => false
        ]);

        return $this->callOllama($payload);
    }

    private function callOllama(string $payload): string {
        $ch = curl_init($this->ollamaUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            // Fallback sur Groq si Ollama échoue
            if (!empty($this->apiKey)) {
                return $this->callGroq($payload);
            }
            return "[Erreur Ollama: Assurez-vous qu'Ollama est lancé]";
        }

        $data = json_decode($response, true);
        return trim($data['message']['content'] ?? '');
    }

    private function callGroq(string $payload): string {
        $data = json_decode($payload, true);
        $data['model'] = $this->model;
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $resData = json_decode($response, true);
        return trim($resData['choices'][0]['message']['content'] ?? '[Erreur Groq]');
    }

    public function analyzeCV(string $cvText): string {
        if (empty(trim($cvText)) || empty($this->apiKey)) {
            return json_encode(['error' => 'Texte vide ou API Key manquante.']);
        }

        $systemPrompt = "Tu es un auditeur ATS impitoyable. Analyse le CV et retourne EXCLUSIVEMENT un JSON valide avec : score_ats (0-100), points_forts (array), points_faibles (array). Trouve au moins 3 points faibles.";
        
        $payload = json_encode([
            "model" => $this->model,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "Voici le CV :\n\n" . $cvText]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => 0.2
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        $content = preg_replace('/```json\s*|```\s*/', '', $content);
        return trim($content);
    }

    private function cleanAIResponse(string $response): string {
        return trim($response, "\"\'\n\r ");
    }
}
