<?php
require_once __DIR__ . '/EnvLoader.php';
require_once dirname(__DIR__) . '/config.php';

class VeilleAIController
{
    private $geminiApiKey;
    private $firecrawlApiKey;
    private $groqApiKey;
    private $openRouterApiKey;

    public function __construct()
    {
        // Ensure .env is loaded using multiple fallback strategies
        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            $envPath = dirname(__DIR__) . '/.env';
        }
        
        EnvLoader::load($envPath);

        // Use a more robust way to fetch env vars
        $this->geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '';
        $this->firecrawlApiKey = $_ENV['FIRECRAWL_API_KEY'] ?? $_SERVER['FIRECRAWL_API_KEY'] ?? getenv('FIRECRAWL_API_KEY') ?? '';
        $this->groqApiKey = $_ENV['GROQ_API_KEY'] ?? $_SERVER['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?? '';
        $this->openRouterApiKey = $_ENV['OPENROUTER_API_KEY'] ?? $_SERVER['OPENROUTER_API_KEY'] ?? getenv('OPENROUTER_API_KEY') ?? '';
    }

    private function callGemini($prompt)
    {
        if (empty($this->geminiApiKey)) {
            return "API Error: Clé API Gemini manquante. Vérifiez votre fichier .env.";
        }

        // Using 'gemini-flash-latest' as verified by test_api.php
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->geminiApiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 2048,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for local XAMPP SSL issues

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return "Error: " . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        
        // Handle API errors gracefully
        if (isset($result['error']) || !isset($result['candidates'])) {
            $errorMessage = $result['error']['message'] ?? 'Unknown Error';
            
            // Fallback to Groq on Rate Limit (429) or Server Overload (503)
            if (!empty($this->groqApiKey) && (strpos($errorMessage, 'high demand') !== false || strpos($errorMessage, 'quota') !== false || strpos($errorMessage, 'exceeded') !== false || strpos($errorMessage, 'overloaded') !== false)) {
                return $this->callGroq($prompt);
            }
            
            return "API Error: " . $errorMessage;
        }

        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "AI could not generate a response.";
    }

    private function callGroq($prompt)
    {
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "system", "content" => "You are a helpful Market Intelligence AI. Output exactly what is requested, with no markdown code block wrapping unless specified. Output valid JSON if requested."],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7,
            "max_tokens" => 500
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->groqApiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return "Error: " . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            return "API Error: " . $result['error']['message'];
        }

        return $result['choices'][0]['message']['content'] ?? "AI could not generate a response.";
    }

    
    public function callOpenRouter($prompt)
    {
        $url = "https://openrouter.ai/api/v1/chat/completions";
        $data = [
            "model" => "google/gemini-2.5-flash",
            "messages" => [
                ["role" => "system", "content" => "You are a helpful Market Intelligence AI. Output exactly what is requested, with no markdown code block wrapping unless specified. Output valid JSON if requested."],        
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7,
            "max_tokens" => 500
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openRouterApiKey,
            'HTTP-Referer: http://localhost',
            'X-Title: Aptus Market Intelligence'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return "Error: " . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            return "API Error: " . $result['error']['message'];
        }

        return $result['choices'][0]['message']['content'] ?? "AI could not generate a response.";
    }

    public function generateDraft($metadata)
    {
        $prompt = "You are an expert HTML content writer and Market Intelligence Analyst. Your task is to write a complete, professional market analysis report.

Metadata provided:
- Titre: {$metadata['titre']}
- Secteur: {$metadata['secteur']}
- Region: {$metadata['region']}
- Salaire moyen: {$metadata['salaire']} TND
- Tendance generale: {$metadata['tendance']}
- Niveau de demande: {$metadata['demande']}

OUTPUT RULES — YOU MUST FOLLOW THESE EXACTLY:
1. Output ONLY valid HTML. Do NOT use Markdown.
2. FORBIDDEN characters and syntax: do NOT use ---, ***, **, *, ##, ###, ####, ` ``` `, or any other Markdown syntax.
3. Use ONLY these HTML tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <br>.
4. Start your output directly with an HTML tag like <h2>. Do not add any preamble, explanation, or text before the first HTML tag.
5. The entire report content MUST be written in French.
6. Include the following disclaimer as the last paragraph, styled as an italic paragraph: <p><em>✨ Ce rapport a été généré et enrichi par l'Assistant IA Aptus. Vérifiez les données avant publication.</em></p>

REPORT STRUCTURE TO FOLLOW:
<h2>[Titre du rapport]</h2>
<h3>Résumé Exécutif</h3>
<p>[2-3 phrases de synthèse du marché]</p>
<h3>Analyse du Secteur</h3>
<p>[Analyse approfondie du secteur ciblé]</p>
<h3>Tendances Salariales</h3>
<p>[Discussion des fourchettes salariales et tendances]</p>
<ul><li>[Points clés]</li></ul>
<h3>Niveau de Demande et Perspectives</h3>
<p>[Analyse de la demande du marché]</p>
<h3>Recommandations</h3>
<ul><li>[Recommandations actionnables]</li></ul>
<p><em>✨ Ce rapport a été généré et enrichi par l'Assistant IA Aptus. Vérifiez les données avant publication.</em></p>";

        return $this->callGemini($prompt);
    }

    public function scoutMarketData($query)
    {
        // 1. Extract URLs from query
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $query, $match);
        $urls = $match[0];
        
        $scrapedContents = [];
        $urlsToScrape = array_slice($urls, 0, 3); // Max 3 URLs to avoid timeout

        foreach ($urlsToScrape as $url) {
            $scrapeUrl = "https://api.firecrawl.dev/v0/scrape";
            $scrapeData = [
                "url" => $url,
                "pageOptions" => ["onlyMainContent" => true]
            ];

            $ch = curl_init($scrapeUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($scrapeData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->firecrawlApiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for local XAMPP SSL issues

            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $scrapeResult = json_decode($response, true);
                $content = $scrapeResult['data']['content'] ?? '';
                if (!empty($content)) {
                    $scrapedContents[] = "Source ($url):\n" . substr($content, 0, 3000);
                }
            }
        }

        $scrapedText = implode("\n\n", $scrapedContents);

        // 2. Gemini Analysis
        $prompt = "You are a Market Intelligence Expert.
        The user has provided the following query or description: \"{$query}\"
        
        Extracted content from provided URLs (if any):
        {$scrapedText}
        
        Analyze the information (use your own knowledge if the text is insufficient) to extract market data points. If no text or URL is provided, simply fulfill the user's query using your general knowledge about the job market (assume salaries in TND - Tunisian Dinar unless specified otherwise).
        
        Return ONLY a JSON object with these exact keys:
        - domaine (string: the main industry/sector, e.g., 'Informatique', 'Santé')
        - competence (string: the specific job or skill, e.g., 'Développeur React', 'Infirmier')
        - region (string: geographic area mentioned or implied, e.g., 'Tunisie', 'Monde')
        - salaire_min (numeric estimate for minimum salary, return null if impossible to estimate)
        - salaire_max (numeric estimate for maximum salary, return null if impossible to estimate)
        - salaire_moyen (numeric estimate for average salary, return null if impossible)
        - tendance (string, exactly one word: 'Hausse', 'Stable', or 'Baisse')
        - demande (string, exactly one word: 'Faible', 'Moyenne', 'Forte', or 'Très forte')
        - source_summary (string: a 2-3 sentence summary of the finding, including the rationale for the salaries and mention of the sources)";

        $aiResponse = $this->callGemini($prompt);
        
        // Handle API error in the scout response
        if (strpos($aiResponse, 'API Error:') === 0) {
            return ["error" => $aiResponse];
        }

        // Extract JSON robustly
        $start = strpos($aiResponse, '{');
        $end = strrpos($aiResponse, '}');
        if ($start !== false && $end !== false) {
            $aiResponse = substr($aiResponse, $start, $end - $start + 1);
        } else {
            // Clean AI response if it contains markdown code blocks but no obvious JSON structure
            $aiResponse = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($aiResponse));
        }
        
        $decoded = json_decode($aiResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "API returned invalid JSON: " . json_last_error_msg() . " - Response: " . substr($aiResponse, 0, 100) . "..."];
        }
        return $decoded;
    }


    public function getMarketPulse($reports)
    {
        $reportsText = "";
        foreach ($reports as $r) {
            $reportsText .= "- Titre: {$r['titre']}, Secteur: {$r['secteur_principal']}, Salaire Moyen: {$r['salaire_moyen_global']} TND\n";
        }
        
        $prompt = "Voici les derniers rapports de veille marché en Tunisie:\n" . $reportsText . "\n\n" .
        "Génère exactement 5 phrases d'accroche ('pulse') très courtes et percutantes résumant ces tendances du marché. " .
        "Chaque phrase doit être une information clé. " .
        "Renvoie UNIQUEMENT un tableau JSON de chaînes de caractères, sans texte avant ni après.";

        // Use OpenRouter to save Gemini/Groq limits
        $aiResponse = $this->callOpenRouter($prompt);
        
        if (strpos($aiResponse, 'API Error:') === 0) {
            return ["error" => $aiResponse];
        }

        $start = strpos($aiResponse, '[');
        $end = strrpos($aiResponse, ']');
        if ($start !== false && $end !== false) {
            $aiResponse = substr($aiResponse, $start, $end - $start + 1);
        } else {
            $aiResponse = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($aiResponse));
        }

        $decoded = json_decode($aiResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "API returned invalid JSON: " . json_last_error_msg()];
        }
        return $decoded;
    }

    public function generateForecast($historicalData, $secteur = '')
    {
        $dataStr = json_encode($historicalData);
        $sectorContext = !empty($secteur) ? "for the '$secteur' sector" : "across all sectors";
        
        $prompt = "Based on this historical market data (Salary/Demand over time) {$sectorContext}:
        $dataStr
        
        Predict the next 6 months of trends specifically for this sector. Return ONLY a JSON array of objects, each with:
        - month (e.g., '2024-05')
        - predicted_salary (numeric)
        - predicted_demand (numeric 1-10)
        - confidence_score (0-1).
        
        IMPORTANT: Return ONLY the raw JSON array. No markdown, no text.";

        // Switch to Groq for better stability and structured output
        if (!empty($this->groqApiKey)) {
            $aiResponse = $this->callGroq($prompt);
        } else {
            $aiResponse = $this->callGemini($prompt);
        }
        
        if (strpos($aiResponse, 'API Error:') === 0) {
            // Last chance fallback to Gemini if Groq failed
            if (!empty($this->groqApiKey)) {
                $aiResponse = $this->callGemini($prompt);
                if (strpos($aiResponse, 'API Error:') === 0) return ["error" => $aiResponse];
            } else {
                return ["error" => $aiResponse];
            }
        }

        $start = strpos($aiResponse, '[');
        $end = strrpos($aiResponse, ']');
        if ($start !== false && $end !== false) {
            $aiResponse = substr($aiResponse, $start, $end - $start + 1);
        } else {
            $aiResponse = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($aiResponse));
        }
        
        $decoded = json_decode($aiResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "API returned invalid JSON: " . json_last_error_msg()];
        }
        return $decoded;
    }
}
