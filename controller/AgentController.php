<?php
session_start();
require_once __DIR__ . '/EnvLoader.php';

// Load .env from root
EnvLoader::load(__DIR__ . '/../.env');

class AgentController {

    private $apiKey;

    public function __construct() {
        $this->apiKey = getenv('GEMINI_AGENT_API_KEY') ?: getenv('GEMINI_API_KEY');

        // Initialize session history if it doesn't exist
        if (!isset($_SESSION['agent_history'])) {
            $_SESSION['agent_history'] = [];
        }
    }

    public function handleRequest() {
        header('Content-Type: application/json; charset=utf-8');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!isset($data['text']) && !isset($data['audio'])) {
            echo json_encode(['error' => 'No text or audio provided']);
            return;
        }

        // System Prompt & Navigation Map
        $systemPrompt = "Tu es l'assistant d'accessibilité IA du site Aptus (Plateforme RH et Veille).
Tu es amical, fun et non robotique. Tu parles français. Garde tes réponses courtes et directes.
Ton but est d'aider l'utilisateur à naviguer et à accomplir des tâches (surtout la Veille Marché).

Tu as deux super-pouvoirs :
1. NAVIGUER : Tu peux envoyer l'utilisateur sur n'importe quelle page.
2. AGIR : Tu peux remplir des formulaires, cliquer sur des boutons et manipuler la page actuelle via du code JavaScript (via l'objet AIAgentUtils).

### CARTE DU SITE (URLs exactes) :
- Dashboard Admin : /aptus_first_official_version/view/backoffice/dashboard.php
- Utilisateurs (Admin) : /aptus_first_official_version/view/backoffice/users.php
- Veille Marché (Admin) : /aptus_first_official_version/view/backoffice/veille_admin.php
- Templates CV (Admin) : /aptus_first_official_version/view/backoffice/cv_templates_admin.php
- Formations (Admin) : /aptus_first_official_version/view/backoffice/formations_admin.php
- Offres (Admin) : /aptus_first_official_version/view/backoffice/offres_admin.php
- Stats & Posts (Admin) : /aptus_first_official_version/view/backoffice/posts_stats.php
- Paramètres (Admin) : /aptus_first_official_version/view/backoffice/settings_admin.php
- Profil (Admin) : /aptus_first_official_version/view/backoffice/profil_admin.php
- Accueil Candidat : /aptus_first_official_version/view/frontoffice/jobs_feed.php
- Accueil Entreprise : /aptus_first_official_version/view/frontoffice/hr_posts.php
- Catalogue Formations : /aptus_first_official_version/view/frontoffice/formations_catalog.php
- Veille Marché (Public) : /aptus_first_official_version/view/frontoffice/veille_feed.php
- Paramètres (Public) : /aptus_first_official_version/view/frontoffice/settings.php

### ACTIONS POSSIBLES (via action.type == 'script') :
Tu dois utiliser l'objet 'AIAgentUtils' qui est déjà disponible sur la page.
Exemples de fonctions utiles :
- AIAgentUtils.fillField('#selector', 'valeur') : Remplit un input/textarea.
- AIAgentUtils.clickElement('#selector') : Clique sur un bouton/lien.
- AIAgentUtils.setSelect('#selector', 'valeur') : Change une option de select.
- AIAgentUtils.addSecteurTag('Secteur') : Ajoute un tag de secteur (Page Veille).
- AIAgentUtils.setQuillContent('<h1>HTML</h1>') : Remplit l'éditeur de texte riche (Page Veille).
- AIAgentUtils.goToStep(2) : Change d'étape dans le formulaire de rapport (1 à 4).
- AIAgentUtils.editReport(index) : Ouvre le modal pour modifier un rapport (index 0 pour le premier).
- AIAgentUtils.deleteReport(index) : Supprime un rapport (index 0 pour le premier).
- AIAgentUtils.readReport(index) : Ouvre la page de lecture d'un rapport (index 0 pour le premier).
- AIAgentUtils.exportPDF() : Exporte le rapport actuellement ouvert en PDF.

### SCÉNARIOS SPÉCIFIQUES (Page Veille Admin) :
Pour créer un rapport, tu peux dire : \"Je commence à remplir le rapport pour vous.\"
Puis envoyer plusieurs scripts dans le même bloc 'code' (séparés par des ;) :
1. Ouvrir le modal : AIAgentUtils.clickElement('.btn-primary[onclick*=\"openRapportModal\"]')
2. Remplir le titre : AIAgentUtils.fillField('#rapport-titre', 'Titre...')
3. Remplir l'auteur : AIAgentUtils.fillField('#rapport-auteur', 'Auteur...')
4. Ajouter un secteur : AIAgentUtils.addSecteurTag('Informatique')
5. Aller à l'étape 2 : AIAgentUtils.goToStep(2)
6. Remplir le contenu : AIAgentUtils.setQuillContent('<p>Contenu...</p>')

IMPORTANT: Tu dois TOUJOURS répondre uniquement avec un objet JSON valide, sans formatage Markdown (pas de ```json), avec la structure suivante :
{
  \"spoken_text\": \"Ce que tu vas dire à voix haute\",
  \"action\": {
    \"type\": \"navigate\" | \"script\",
    \"target\": \"URL_de_la_page_exacte\" (si navigate),
    \"code\": \"JavaScript_code\" (si script)
  }
}
L'objet 'action' est optionnel. Si l'utilisateur demande juste une info ou discute, n'inclus pas d'action, ou mets-la à null. Si l'utilisateur te parle en audio, réponds-lui naturellement en te basant sur ce que tu entends.";

        $parts = [];

        if (isset($data['text']) && !empty($data['text'])) {
            $parts[] = ["text" => $data['text']];
        }

        if (isset($data['audio']) && !empty($data['audio'])) {
            $cleanMime = explode(';', $data['mimeType'] ?? "audio/webm")[0];
            $parts[] = [
                "inlineData" => [
                    "mimeType" => $cleanMime,
                    "data" => $data['audio']
                ]
            ];
            // Provide a text prompt if none exists, Gemini requires text for context
            if (empty($data['text'])) {
                $parts[] = ["text" => "Écoute cet audio et réponds à ma demande."];
            }
        }

        // Append user message to history
        $lastIdx = count($_SESSION['agent_history']) - 1;
        if ($lastIdx >= 0 && $_SESSION['agent_history'][$lastIdx]['role'] === 'user') {
            // Replace last user message to prevent 400 Bad Request
            $_SESSION['agent_history'][$lastIdx]['parts'] = $parts;
        } else {
            $_SESSION['agent_history'][] = ["role" => "user", "parts" => $parts];
        }

        // Ensure history is not too long (keep last 10 messages)
        if (count($_SESSION['agent_history']) > 10) {
            $_SESSION['agent_history'] = array_slice($_SESSION['agent_history'], -10);
        }

        $contents = [];
        foreach ($_SESSION['agent_history'] as $msg) {
            $contents[] = $msg;
        }

        $payload = [
            "system_instruction" => [
                "parts" => [["text" => $systemPrompt]]
            ],
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.7,
                "responseMimeType" => "application/json",
            ]
        ];

        // Use gemini-flash-latest to avoid the strict 20/day quota limit of 2.5
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
            return;
        }
        curl_close($ch);

        $responseData = json_decode($response, true);

        $hasError = isset($responseData['error']) || !isset($responseData['candidates'][0]['content']['parts'][0]['text']);
        $errorMessage = $responseData['error']['message'] ?? 'Invalid API Response';
        
        if ($hasError && (strpos(strtolower($errorMessage), 'high demand') !== false || strpos(strtolower($errorMessage), 'quota') !== false || strpos(strtolower($errorMessage), 'exceeded') !== false || strpos(strtolower($errorMessage), 'overloaded') !== false)) {
            $groqApiKey = getenv('GROQ_API_KEY');
            if (!empty($groqApiKey)) {
                $messages = [
                    ["role" => "system", "content" => $systemPrompt]
                ];
                foreach ($_SESSION['agent_history'] as $msg) {
                    $role = ($msg['role'] === 'model') ? 'assistant' : 'user';
                    $text = '';
                    foreach ($msg['parts'] as $part) {
                        if (isset($part['text'])) {
                            $text .= $part['text'] . " ";
                        }
                    }
                    if (!empty(trim($text))) {
                        $messages[] = ["role" => $role, "content" => trim($text)];
                    }
                }
                
                $groqData = [
                    "model" => "llama-3.3-70b-versatile",
                    "messages" => $messages,
                    "temperature" => 0.7,
                    "response_format" => ["type" => "json_object"]
                ];
                
                $gCh = curl_init("https://api.groq.com/openai/v1/chat/completions");
                curl_setopt($gCh, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($gCh, CURLOPT_POST, true);
                curl_setopt($gCh, CURLOPT_POSTFIELDS, json_encode($groqData));
                curl_setopt($gCh, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $groqApiKey
                ]);
                curl_setopt($gCh, CURLOPT_SSL_VERIFYPEER, false);
                
                $gRes = curl_exec($gCh);
                curl_close($gCh);
                
                $gData = json_decode($gRes, true);
                if (isset($gData['choices'][0]['message']['content'])) {
                    $responseData = [
                        'candidates' => [
                            [
                                'content' => [
                                    'parts' => [
                                        ['text' => $gData['choices'][0]['message']['content']]
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
            }
        }

        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'];

            // Extract JSON robustly
            $start = strpos($rawText, '{');
            $end = strrpos($rawText, '}');
            if ($start !== false && $end !== false) {
                $cleanJson = substr($rawText, $start, $end - $start + 1);
            } else {
                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
            }
            
            $parsedResponse = json_decode($cleanJson, true);

            if ($parsedResponse && json_last_error() === JSON_ERROR_NONE) {
                // Add to history
                $_SESSION['agent_history'][] = ["role" => "model", "parts" => [["text" => json_encode($parsedResponse)]]];
                echo json_encode($parsedResponse);
            } else {
                echo json_encode([
                    "spoken_text" => "Désolé, je n'ai pas pu formater ma réponse.", 
                    "action" => null, 
                    "raw" => $rawText,
                    "debug" => json_last_error_msg()
                ]);
            }
        } else {
            // Check if it's a quota error or something else
            $errorMessage = $responseData['error']['message'] ?? 'Invalid API Response';
            echo json_encode([
                "spoken_text" => "Erreur API : " . $errorMessage,
                "action" => null,
                "error" => 'Invalid API Response', 
                "details" => $responseData
            ]);
        }
    }
}

// Handle request if file is called directly
if (basename($_SERVER['PHP_SELF']) === 'AgentController.php') {
    $controller = new AgentController();
    $controller->handleRequest();
}