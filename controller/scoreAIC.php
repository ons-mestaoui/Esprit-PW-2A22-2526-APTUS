<?php
require_once __DIR__ . '/../config.php';

class scoreAIC {
    private function getApiKeys() {
        $keys = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile);
            foreach ($lines as $line) {
                if (strpos($line, 'GROQ_KEY') !== false) {
                    $parts = explode('=', $line);
                    if (isset($parts[1])) $keys[] = trim($parts[1]);
                }
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
                    ["role" => "system", "content" => "Tu es un Expert Recruteur Senior d'Aptus. Ton rôle est d'analyser une candidature et de lui attribuer un score de pertinence entre 1 et 100.
                        
                        CRITÈRES D'ÉVALUATION :
                        1. CORRESPONDANCE CV/OFFRE : Analyse si le profil (expériences, compétences) correspond aux exigences de l'offre.
                        2. RÉPONSE À LA QUESTION : Évalue si la réponse du candidat à la question de l'entreprise est pertinente, claire et démontre une réelle compétence ou motivation.
                        
                        RÈGLES STRICTES :
                        - TOLÉRANCE : Ne sois PAS sensible à la casse (Majuscules/Minuscules).
                        - LANGUE : Réponds en FRANÇAIS.
                        - ORTHOGRAPHE : Ignore les fautes d'orthographe si le sens reste clair. Focalise-toi sur le FOND et non la forme.
                        - RIGUEUR : Un candidat parfait = 90-100. Un profil intéressant mais avec des manques = 60-80. Un profil hors-sujet = < 50.
                        
                        RÉPONSE : Réponds UNIQUEMENT par le nombre entier du score. Aucun texte, aucune explication."],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.1
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers = ["Authorization: Bearer $apiKey", "Content-Type: application/json"];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $response = json_decode($result, true);
                $content = $response['choices'][0]['message']['content'] ?? '';
                preg_match('/\d+/', $content, $matches);
                if (isset($matches[0])) return intval($matches[0]);
            }
        }
        return 0;
    }

    public function genererRapportDetailed($prompt) {
        $apiKeys = $this->getApiKeys();
        foreach ($apiKeys as $apiKey) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.groq.com/openai/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $data = [
                "model" => "llama-3.3-70b-versatile",
                "messages" => [
                    [
                        "role" => "system", 
                        "content" => "Tu es un Consultant Expert en Recrutement et Psychologue du Travail chez Aptus. Ton objectif est de fournir une analyse chirurgicale stricte d'une candidature pour aider un DRH à prendre une décision.
                        
                        TON ANALYSE DOIT SUIVRE CETTE STRUCTURE STRICTE :
                        
                        💎 POINTS FORTS :
                        - Analyse la pertinence technique (Hard Skills) par rapport au poste.
                        - Identifie si son cv est compatiple avec l'offre
                        - Identifie les succès passés ou les compétences rares mentionnées.
                        - Note la clarté et la structure de sa réponse à la question.
                        
                        ⚠️ POINTS DE VIGILANCE :
                        - Relève les lacunes (Soft ou Hard Skills) par rapport aux exigences.
                        - Identifie les incohérences potentielles ou les zones d'ombre.
                        - Identifie si son cv est compatiple avec l'offre
                        - Note si la réponse est trop générique ou manque d'exemples concrets.
                        
                        🚀 IMPRESSION GÉNÉRALE :
                        - Quelle est la 'Valeur Ajoutée' unique que ce candidat apporte ?
                        - Est-ce un 'Bon Match' culturel et technique ?
                        - Verdict final argumenté (Verdict : Recruter, Interviewer, ou Écarter).
                        
                        CONTRAINTES DE STYLE : 
                        - Professionnel, analytique, direct.
                        - Utilise des listes à puces.
                        - N'utilise pas les *  .
                        - Garde les emojis 💎, ⚠️, 🚀 comme marqueurs de section."
                    ],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.5
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers = ["Authorization: Bearer $apiKey", "Content-Type: application/json"];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $response = json_decode($result, true);
                return $response['choices'][0]['message']['content'];
            } else {
                $err = json_decode($result, true);
                $msg = $err['error']['message'] ?? 'Erreur inconnue';
                curl_close($ch);
                return "Erreur IA (Code $httpCode) : $msg";
            }
        }
        return "Aucune clé API valide ou disponible.";
    }
}
?>
