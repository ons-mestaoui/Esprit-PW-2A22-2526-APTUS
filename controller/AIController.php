<?php

class AIController {
    
    private string $ollamaEndpoint = 'http://127.0.0.1:11434/api/generate';
    private string $model = 'mistral';

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
}
