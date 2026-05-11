<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$jobTitle = $input['jobTitle'] ?? '';

if (empty(trim($jobTitle))) {
    echo json_encode(['success' => false, 'error' => 'Le titre du poste est vide.']);
    exit;
}

require_once __DIR__ . '/../../controller/GuideController.php';

try {
    $guideController = new GuideController();
    $suggestions = $guideController->suggestSkills($jobTitle);

    if (!empty($suggestions)) {
        echo json_encode(['success' => true, 'suggestions' => $suggestions]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Aucune suggestion générée.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
