<?php
/**
 * ajax_ai_roi.php
 * Transforms a simple task into a quantified impact statement using AI.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AIController.php';

$data = json_decode(file_get_contents('php://input'), true);
$text = $data['text'] ?? '';
$value = $data['value'] ?? '';
$metric = $data['metric'] ?? '';
$role = $data['role'] ?? '';

if (empty($text) || empty($value)) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$ai = new AIController();

$prompt = "Tu es un expert en branding personnel et optimisation de CV (Impact ROI).
Ta mission est de transformer une tâche banale en un accomplissement chiffré percutant.

RÈGLES D'OR :
1. CONCISION : Produis une seule phrase courte, dense et directe.
2. ACTION : Commence par un verbe d'action puissant (ex: Augmenté, Réduit, Optimisé).
3. CHIFFRE : Intègre la valeur fournie de manière fluide et naturelle.
4. FORMAT : Renvoie UNIQUEMENT un objet JSON : {\"suggestion\": \"La phrase courte\"}.
5. AUCUN HTML : Interdiction formelle d'utiliser des balises HTML.

Données d'entrée :
- Tâche : [TEXT]
- Valeur : [VALUE]
- Métrique : [METRIC]
- Poste : [ROLE]";

$userInput = "Tâche : $text\nValeur : $value\nMétrique : $metric\nPoste : $role";

try {
    $result = $ai->generateJSON($prompt, $userInput);
    echo json_encode(['success' => true, 'suggestion' => $result['suggestion']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
