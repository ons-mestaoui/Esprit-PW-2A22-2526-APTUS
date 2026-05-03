<?php
require_once __DIR__ . '/controller/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

$models = [
    'gemini-1.5-flash-latest',
    'gemini-1.5-flash',
    'gemini-1.5-pro-latest',
    'gemini-pro',
    'gemini-flash-latest' // the original one
];

$apiKey = $_ENV['GEMINI_API_KEY'];

foreach ($models as $model) {
    echo "Testing $model...\n";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;
    
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Say hello"]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        echo "Error: " . $result['error']['message'] . "\n\n";
    } else if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Success! Output: " . trim($result['candidates'][0]['content']['parts'][0]['text']) . "\n\n";
    } else {
        echo "Unknown response: $response\n\n";
    }
}
