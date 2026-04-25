<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/InscriptionController.php';

// Redirection si ce n'est pas une requête POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../view/frontoffice/formations_catalog.php');
    exit();
}

$id_formation = isset($_POST['id_formation']) ? (int)$_POST['id_formation'] : 0;
// Simuler l'utilisateur (Candidat par défaut ID 10) - Idéalement viendrait de $_SESSION['user_id']
$id_user = $_SESSION['user_id'] ?? 10;

if ($id_formation > 0) {
    $inscriptionC = new InscriptionController();
    try {
        $inscriptionC->inscrire($id_formation, $id_user);
        $_SESSION['flash_success'] = "Inscription réussie !";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
}

// Redirection vers la page de détails
header("Location: ../view/frontoffice/formation_detail.php?id=" . $id_formation);
exit();
