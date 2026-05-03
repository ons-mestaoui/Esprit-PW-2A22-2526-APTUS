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
                        "content" => "Tu es un Expert Recruteur Senior d'Aptus. Ton rôle est d'analyser une candidature et de lui attribuer un score de pertinence entre 1 et 100.
                        
                        CRITÈRES D'ÉVALUATION :
                        1. CORRESPONDANCE CV/OFFRE : Analyse si le profil (expériences, compétences) correspond aux exigences de l'offre.
                        2. RÉPONSE À LA QUESTION : Évalue si la réponse du candidat à la question de l'entreprise est pertinente, claire et démontre une réelle compétence ou motivation.
                        
                        RÈGLES STRICTES :
                        - TOLÉRANCE : Ne sois PAS sensible à la casse (Majuscules/Minuscules).
                        - ORTHOGRAPHE : Ignore les fautes d'orthographe si le sens reste clair. Focalise-toi sur le FOND et non la forme.
                        - RIGUEUR : Un candidat parfait = 90-100. Un profil intéressant mais avec des manques = 60-80. Un profil hors-sujet = < 50.
                        
                        RÉPONSE : Réponds UNIQUEMENT par le nombre entier du score. Aucun texte, aucune explication."
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
