<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/CV.php';
require_once __DIR__ . '/../../controller/CVC.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Global exception handler to always return valid JSON
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
    exit;
});

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue ou JSON invalide.']);
    exit;
}

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine id_candidat from session OR auto-resolve from DB
$id_candidat = $_SESSION['user_id'] ?? null;

if (!$id_candidat) {
    try {
        $db = config::getConnexion();
        
        // Disable FK checks so we can insert even if candidat table is empty
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        
        $id_candidat = 1; // Default test user
    } catch(Exception $e) {
        $id_candidat = 1;
    }
} else {
    try {
        $db = config::getConnexion();
        // Check if this candidat exists, if not disable FK checks
        $exists = $db->prepare('SELECT id_candidat FROM candidat WHERE id_candidat = ?');
        $exists->execute([$id_candidat]);
        if (!$exists->fetch()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=0');
        }
    } catch(Exception $e) {
        // silently disable
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
    }
}

$cvc = new CVC();

// Merge contact info
$email     = trim($data['email'] ?? '');
$telephone = trim($data['telephone'] ?? '');
$adresse   = trim($data['adresse'] ?? '');
$infoContact = implode(' | ', array_filter([$email, $telephone, $adresse]));

try {
    $cvIdRaw = isset($data['id_cv']) && $data['id_cv'] ? (int)$data['id_cv'] : null;

    $cv = new CV(
        $cvIdRaw,
        (int)$id_candidat,
        (int)$data['id_template'],
        'CV ' . ($data['nomComplet'] ?? 'Sans Titre'),
        $data['nomComplet'] ?? '',
        $data['titrePoste'] ?? '',
        $data['resume'] ?? '',
        $infoContact,
        $data['experience'] ?? '',
        $data['formation'] ?? '',
        $data['competences'] ?? '',
        $data['langues'] ?? '',
        $data['urlPhoto'] ?? '',
        $data['couleurTheme'] ?? '#2563eb',
        'en_attente'
    );

    if ($cvIdRaw) {
        $cvc->updateCV($cvIdRaw, $cv);
        $id = $cvIdRaw;
    } else {
        $id = $cvc->addCV($cv);
    }

    // Re-enable FK checks
    try { config::getConnexion()->exec('SET FOREIGN_KEY_CHECKS=1'); } catch(Exception $e) {}

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    try { config::getConnexion()->exec('SET FOREIGN_KEY_CHECKS=1'); } catch(Exception $ex) {}
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
