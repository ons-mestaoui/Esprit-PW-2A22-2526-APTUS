<?php
// Get the text from the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$text = $data['text'] ?? 'Bonjour';

function getGoogleTranslateTTS($text, $lang = 'fr') {
    // Google Translate TTS has a character limit per request.
    // We split the text into smaller chunks without breaking words.
    $words = explode(' ', $text);
    $chunks = [];
    $chunk = '';
    
    foreach ($words as $word) {
        if (strlen($chunk . ' ' . $word) > 150) {
            $chunks[] = trim($chunk);
            $chunk = $word;
        } else {
            $chunk .= ' ' . $word;
        }
    }
    if (!empty($chunk)) {
        $chunks[] = trim($chunk);
    }
    
    $audio = '';
    foreach ($chunks as $c) {
        $url = "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=" . urlencode($c) . "&tl=" . $lang;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
        ]);
        curl_setopt($ch, CURLOPT_REFERER, "http://translate.google.com/");
        
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $res) {
            $audio .= $res;
        }
    }
    return $audio;
}

$audioBinary = getGoogleTranslateTTS($text, 'fr'); // Assuming French based on your prompt language

if (empty($audioBinary)) {
    http_response_code(500);
    echo json_encode(['error' => 'TTS generation failed']);
    exit;
}

// Return the concatenated binary MP3 audio
header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache');
echo $audioBinary;
