<?php
/**
 * cv_validate_step.php — Validation backend dynamique par étape
 * Appelé en Fetch depuis cv_form.php lors du clic sur "Suivant"
 */
header('Content-Type: application/json');

// Récupérer les données JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['step'])) {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
    exit;
}

$step = (int)$data['step'];
$fields = $data['data'] ?? [];

// Règles de validation (Exactement les mêmes que dans cv_save.php)
switch ($step) {
    case 1: // Informations Personnelles
        $name = trim($fields['name'] ?? '');
        $title = trim($fields['title'] ?? '');
        $email = trim($fields['email'] ?? '');
        $phone = trim($fields['phone'] ?? '');
        $location = trim($fields['location'] ?? '');

        if (strlen($name) < 3 || !preg_match('/^[\p{L}\s.\'-]+$/u', $name)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer un nom valide (min. 3 lettres).']);
            exit;
        }
        if (strlen($title) < 3 || strlen($title) > 100) {
            echo json_encode(['success' => false, 'message' => 'Le titre du poste doit contenir entre 3 et 100 caractères.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer une adresse email valide.']);
            exit;
        }
        if (!preg_match('/^\+?[0-9\s.\-()]{8,20}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Numéro de téléphone invalide (min. 8 chiffres).']);
            exit;
        }
        if (strlen($location) < 3) {
            echo json_encode(['success' => false, 'message' => 'Veuillez préciser votre localisation (min. 3 caractères).']);
            exit;
        }
        break;

    case 2: // Résumé
        $summary = trim($fields['summary'] ?? '');
        if (mb_strlen($summary) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Votre résumé est trop long (max 1000 caractères).']);
            exit;
        }
        break;

    // Équipes 3 à 6 : On peut ajouter des validations si nécessaire, 
    // ou laisser libre pour le moment selon votre choix.
}

// Si on arrive ici, tout est validé pour l'étape
echo json_encode(['success' => true]);
