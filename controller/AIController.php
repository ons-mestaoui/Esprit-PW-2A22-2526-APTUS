<?php

class AIController {
    
    private string $ollamaEndpoint = 'http://127.0.0.1:11434/api/generate';
    // Le modèle de polish est changé pour llama3.2:3b comme demandé
    private string $model = 'llama3.2:3b';

    public function polishText(string $text, string $context): string {
        if (empty(trim($text))) {
            return "";
        }

        $prompt = $this->getPromptForContext($context, $text);

        $payload = json_encode([
            "model" => $this->model,
            "prompt" => $prompt,
            "stream" => false
        ]);

        $ch = curl_init($this->ollamaEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout pour laisser le temps à l'IA locale de répondre

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            error_log("Ollama AI Error: " . $error);
            return "[Erreur] Connexion à l'IA locale ignorée (Code ". $httpCode . " | Détail: " . ($error ?: 'Aucun') . "). Vérifiez qu'Ollama écoute bien sur le port 11434 (127.0.0.1).";
        }

        $responseData = json_decode($response, true);
        $filteredResponse = $responseData['response'] ?? '';

        return $this->cleanAIResponse(trim($filteredResponse));
    }

    private function getPromptForContext(string $context, string $text): string {
        $baseInstruction = "";
        
        switch ($context) {
            case 'summary':
                $baseInstruction = "Agis comme un expert en recrutement (ATS). Réécris ce résumé de CV. \nRègle absolue 1 : Le texte DOIT être exclusivement en français professionnel. \nRègle absolue 2 : Conserve STRICTEMENT TOUS les mots-clés initiaux (outils, noms de diplômes comme licence, master, technologies). \nRègle absolue 3 : Le style doit être impersonnel (N'utilise JAMAIS les pronoms je, moi, mon, tu, nous). \nRègle absolue 4 : Ne rajoute SURTOUT PAS le titre de la section (ex: 'Résumé' ou 'Profil') au début du texte. \nSois concis (3 à 5 lignes maximum). N'inclus aucune phrase d'introduction ni de conclusion. Renvoie UNIQUEMENT le texte pur synthétisé.";
                break;
            case 'experience':
                $baseInstruction = "Agis comme un expert en recrutement (ATS). Réécris cette expérience professionnelle. \nRègle absolue 1 : Le texte DOIT être exclusivement en français professionnel. \nRègle absolue 2 : Conserve STRICTEMENT les mots-clés techniques initiaux. \nRègle absolue 3 : N'utilise JAMAIS aucun pronom personnel (je, moi, mon). Rédige uniquement sous forme de liste avec tirets (-), en commençant chaque point par un nom d'action (ex: 'Conception', 'Gestion'). \nRègle absolue 4 : Ne rajoute SURTOUT PAS le titre de la section (ex: 'Expérience Professionnelle') au début du texte. \nSois impactant et concis (3 à 5 lignes maximum). N'inclus aucune introduction ou conclusion. Renvoie UNIQUEMENT le texte listé.";
                break;
            case 'education':
                $baseInstruction = "Agis comme un correcteur académique de CV. Améliore la lisibilité de cette formation. \nRègle absolue 1 : Le texte DOIT être exclusivement en français formel. \nRègle absolue 2 : Garde l'intégrité ABSOLUE des noms de diplômes (ex: licence, DUT, master) et des établissements. \nRègle absolue 3 : Zéro pronom personnel (je, mon, etc.). \nRègle absolue 4 : Ne rajoute SURTOUT PAS le titre (ex: 'Formation', 'Études') au texte généré. \nRends le texte extrêmement clair et concis. N'inclus aucune introduction ou conclusion. Renvoie UNIQUEMENT le contenu final pur.";
                break;
            default:
                $baseInstruction = "Corrige et améliore ce texte pour un CV professionnel en français. N'inclus aucun titre de section, aucun pronom personnel, et garde les mots clés. Renvoie uniquement le texte corrigé.";
        }

        return $baseInstruction . "\n\nTexte brut à traiter :\n\"\"\"\n" . $text . "\n\"\"\"";
    }

    private function cleanAIResponse(string $response): string {
        $response = preg_replace('/^(Voici.*?:|Texte final :|Texte révisé :|Texte corrigé :|Texte .*?:)\s*/im', '', $response);
        $response = trim($response, "\"\'\n\r");
        return $response;
    }

    public function analyzeCV(string $cvText): string {
        if (empty(trim($cvText))) {
            return json_encode(['error' => 'Texte du CV vide.']);
        }

        $prompt = "Tu es un auditeur ATS impitoyable et un expert en recrutement. Fais une analyse EXTRÊMEMENT DÉTAILLÉE du CV suivant. " .
                  "Traque particulièrement les erreurs de logique (ex: la même langue répétée avec des niveaux différents), les manques de précision, et l'absence de résultats chiffrés. " .
                  "Tu DOIS retourner EXCLUSIVEMENT un objet JSON valide (sans aucun texte avant ou après), et il doit respecter ce schéma exact : " .
                  "{" .
                  "\"score_ats\": 85, " .
                  "\"points_forts\": [\"Argument détaillé 1\", \"Argument détaillé 2\", \"Argument détaillé 3\"], " .
                  "\"points_faibles\": [\"Critique détaillée 1 expliquant comment corriger\", \"Critique détaillée 2 signalant une erreur de logique\", \"Critique détaillée 3\", \"Critique détaillée 4\"]" .
                  "}. Le score_ats est un entier entre 0 et 100. Trouve au moins 4 points faibles très spécifiques. " .
                  "Voici le CV à auditer :\n\n" . $cvText;

        $payload = json_encode([
            "model" => "mistral", // L'analyse d'audit utilise explicitement le modèle plus puissant "mistral"
            "prompt" => $prompt,
            "stream" => false,
            "format" => "json"
        ]);

        $ch = curl_init($this->ollamaEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            error_log("Ollama AI Analysis Error: " . $error);
            return json_encode(["score_ats" => 0, "points_forts" => [], "points_faibles" => ["Erreur de connexion à Mistral. Vérifiez qu'Ollama est actif."]]);
        }

        $responseData = json_decode($response, true);
        $rawText = $responseData['response'] ?? '{}';
        
        // Ensure mistral returned valid JSON (sometimes it still prefixes something)
        $jsonStart = strpos($rawText, '{');
        $jsonEnd = strrpos($rawText, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $rawText = substr($rawText, $jsonStart, $jsonEnd - $jsonStart + 1);
        }
        
        return $rawText;
    }
}
