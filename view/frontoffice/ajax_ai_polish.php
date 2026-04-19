<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$text = $input['text'] ?? '';
$context = $input['context'] ?? '';

if (empty(trim($text)) || empty($context)) {
    echo json_encode(['success' => false, 'error' => 'Le texte à améliorer est vide.']);
    exit;
}

require_once __DIR__ . '/../../controller/AIController.php';

$ai = new AIController();
$polishedText = $ai->polishText($text, $context);

if (strpos($polishedText, '[Erreur') === 0) {
    echo json_encode(['success' => false, 'error' => $polishedText]);
} else {
    echo json_encode(['success' => true, 'polished_text' => $polishedText]);
}
