<?php
class scoreAIC {

    private function getApiKeys() {
        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) return [];
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $keys = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue; 
            list($name, $value) = explode('=', $line, 2);
            if (strpos($name, 'GROQ_KEY') !== false) {
                $keys[] = trim($value);
            }
        }
        return $keys;
    }

    public function calculerScore($prompt) {
        $apiKeys = $this->getApiKeys();
        foreach ($apiKeys as $apiKey) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.groq.com/openai/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $data = [
                "model" => "llama-3.1-8b-instant",
                "messages" => [
                    [
                        "role" => "system", 
                        "content" => "Tu es un Expert Recruteur Senior. Ton rôle est d'évaluer objectivement la réponse d'un candidat par rapport à une offre d'emploi. Réponds UNIQUEMENT par le score final (un nombre entier entre 1 et 100). Aucune phrase, aucune explication."
                    ],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.1
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers = [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json"
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $response = json_decode($result, true);
                $content = $response['choices'][0]['message']['content'] ?? '';
                preg_match('/\d+/', $content, $matches);
                if (isset($matches[0])) {
                    return intval($matches[0]);
                }
            }
        }
        return 0;
    }
}
?>
