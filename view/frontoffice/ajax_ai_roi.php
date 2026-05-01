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

$prompt = "Tu es un coach en carrière expert. Ta mission est de transformer une tâche de CV en un accomplissement chiffré (ROI).
On te donne une tâche, une valeur chiffrée fournie par l'utilisateur, et éventuellement une métrique.
Reformule la tâche pour intégrer cette valeur de manière professionnelle et percutante.

Exemple :
Tâche : 'J'ai géré le support client'
Valeur : '20%'
Métrique : 'Productivité'
Résultat : 'Optimisation du support client ayant entraîné une augmentation de 20% de la productivité globale de l'équipe.'

Renvoie UNIQUEMENT un objet JSON : {\"suggestion\": \"La phrase reformulée\"}";

$userInput = "Poste : $role\nTâche originale : $text\nValeur chiffrée à intégrer : $value\nMétrique : $metric";

try {
    $result = $ai->generateJSON($prompt, $userInput);
    echo json_encode(['success' => true, 'suggestion' => $result['suggestion']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
