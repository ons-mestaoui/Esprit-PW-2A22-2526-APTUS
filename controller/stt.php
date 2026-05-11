<?php
// No session_start — avoid session file lock on concurrent STT requests
require_once __DIR__ . '/EnvLoader.php';

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) $envPath = dirname(__DIR__) . '/.env';
EnvLoader::load($envPath);

header('Content-Type: application/json; charset=utf-8');

$groqApiKey = $_ENV['GROQ_API_KEY'] ?? $_SERVER['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?? '';
if (empty($groqApiKey)) {
    echo json_encode(['text' => '']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$base64Audio = $input['audio'] ?? '';
$mimeType = $input['mimeType'] ?? 'audio/webm';

if (empty($base64Audio)) {
    echo json_encode(['text' => '']);
    exit;
}

$audioData = base64_decode($base64Audio);
$ext = (strpos($mimeType, 'mp4') !== false) ? '.mp4' : '.webm';
$tmpFile = sys_get_temp_dir() . '/stt_' . uniqid() . $ext;
file_put_contents($tmpFile, $audioData);

$ch = curl_init("https://api.groq.com/openai/v1/audio/transcriptions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file'     => curl_file_create($tmpFile, $mimeType, 'audio' . $ext),
    'model'    => 'whisper-large-v3-turbo',
    'language' => 'fr'
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $groqApiKey]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$res = curl_exec($ch);
curl_close($ch);
@unlink($tmpFile);

$data = json_decode($res, true);
echo json_encode(['text' => $data['text'] ?? '']);
