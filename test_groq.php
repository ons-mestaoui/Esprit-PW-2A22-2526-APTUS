<?php
require_once __DIR__ . '/controller/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

$groqApiKey = $_ENV['GROQ_API_KEY'];
echo "Testing Groq API with key: " . substr($groqApiKey, 0, 10) . "...\n";

$groqData = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful AI assistant. Output a valid JSON with keys 'spoken_text' and 'action'."],
        ["role" => "user", "content" => "Bonjour !"]
    ],
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
$httpCode = curl_getinfo($gCh, CURLINFO_HTTP_CODE);
curl_close($gCh);

echo "HTTP Code: $httpCode\n";
echo "Response: $gRes\n";
