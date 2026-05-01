<?php
/**
 * ajax_ai_translate.php
 * Handles AI translation of the entire CV data object.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AIController.php';

$data = json_decode(file_get_contents('php://input'), true);
$cvData = $data['cvData'] ?? null;
$targetLang = $data['targetLang'] ?? 'Anglais';

if (!$cvData) {
    echo json_encode(['success' => false, 'error' => 'Données CV manquantes']);
    exit;
}

$ai = new AIController();

try {
    $translatedData = $ai->translateCV($cvData, $targetLang);
    echo json_encode(['success' => true, 'data' => $translatedData]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
