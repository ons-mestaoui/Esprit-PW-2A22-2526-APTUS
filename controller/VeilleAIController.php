<?php
require_once __DIR__ . '/EnvLoader.php';
require_once dirname(__DIR__) . '/config.php';

EnvLoader::load(dirname(__DIR__) . '/.env');

class VeilleAIController
{
    private $geminiApiKey;
    private $firecrawlApiKey;

    public function __construct()
    {
        $this->geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        $this->firecrawlApiKey = $_ENV['FIRECRAWL_API_KEY'] ?? '';
    }

    private function callGemini($prompt)
    {
        // Using 'gemini-flash-latest' to ensure compatibility with newer API keys
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
        if (isset($result['error'])) {
            return "API Error: " . $result['error']['message'];
        }

        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "AI could not generate a response.";
    }

    public function generateDraft($metadata)
    {
        $prompt = "As a Market Intelligence Expert, write a professional market analysis report based on the following metadata:
        Title: {$metadata['titre']}
        Sector: {$metadata['secteur']}
        Region: {$metadata['region']}
        Average Salary: {$metadata['salaire']}
        General Trend: {$metadata['tendance']}
        Demand Level: {$metadata['demande']}
        
        CRITICAL INSTRUCTIONS:
        1. The entire report MUST be written strictly in French.
        2. The report should be in HTML format (suitable for a Quill editor), including headings, bullet points, and a deep analysis.
        3. Use a professional and insightful tone.
        4. MUST include a prominent, stylized header or footer at the beginning or end of the report that clearly states: \"✨ Ce rapport a été généré et enrichi par l'Assistant IA Aptus. Vérifiez les données avant publication.\"";

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

        // Clean AI response if it contains markdown code blocks
        $aiResponse = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
        
        return json_decode($aiResponse, true);
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
        - confidence_score (0-1)";

        $aiResponse = $this->callGemini($prompt);
        
        if (strpos($aiResponse, 'API Error:') === 0) {
            return ["error" => $aiResponse];
        }

        $aiResponse = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
        
        return json_decode($aiResponse, true);
    }
}
