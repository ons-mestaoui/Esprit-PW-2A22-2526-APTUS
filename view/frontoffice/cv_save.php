<?php
/**
 * cv_save.php — Backend d'enregistrement CV (propre, sans IA)
 * Utilisé par cv_form.php
 */
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Garantir que toute erreur retourne du JSON
set_exception_handler(function(Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    throw new \ErrorException($errstr, $errno);
});

require_once __DIR__ . '/../../config.php';

// Lire le JSON
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données JSON introuvables ou invalides.']);
    exit;
}

// Champs requis
$name  = trim($data['name']  ?? '');
$email = trim($data['email'] ?? '');
$title = trim($data['title'] ?? '');
$phone = trim($data['phone'] ?? '');
$location = trim($data['location'] ?? '');
$summary = trim($data['summary'] ?? '');

// Validation Backend (Sécurité additionnelle)
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
if (mb_strlen($summary) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Votre résumé est trop long (max 1000 caractères).']);
    exit;
}

try {
    require_once __DIR__ . '/../../controller/CVC.php';
    require_once __DIR__ . '/../../model/CV.php';

    // Session user
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_candidat = $_SESSION['user_id'] ?? null; // NULL est acceptable (colonne DEFAULT NULL)

    // Construire l'infoContact consolidée
    $infoContact = implode(' | ', array_filter([$email, $phone, $location]));

    $cv_id       = !empty($data['cv_id'])       ? (int)$data['cv_id']       : null;
    $template_id = !empty($data['template_id']) ? (int)$data['template_id'] : null;
    $photo       = $data['photo']       ?? '';
    $couleur     = $data['color_theme'] ?? '#2563eb';
    $experience  = $data['experience']  ?? '';
    $skills      = $data['skills']      ?? '';
    $education   = $data['education']   ?? '';
    $languages   = $data['languages']   ?? '';
    $ndoc        = 'CV ' . $name;

    // Instanciation stricte du Modèle MVC
    $cvModel = new CV(
        $cv_id,
        $id_candidat,
        $template_id,
        $ndoc,
        $name,
        $title,
        $summary,
        $infoContact,
        $experience,
        $education,
        $skills,
        $languages,
        $photo,
        $couleur,
        'en_attente' // statut par défaut
    );

    // Appel du Contrôleur MVC
    $cvc = new CVC();

    if ($cv_id) {
        // UPDATE (Délégation au Contrôleur)
        $cvc->updateCV($cv_id, $cvModel);
        $newId = $cv_id;
    } else {
        // INSERT (Délégation au Contrôleur)
        $newId = $cvc->addCV($cvModel);
    }

    echo json_encode(['success' => true, 'id' => (int)$newId]);

} catch (Throwable $e) {
    // Tentative de réactiver FK en cas d'erreur
    try { $db->exec('SET FOREIGN_KEY_CHECKS = 1'); } catch (Throwable $ex) {}
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
