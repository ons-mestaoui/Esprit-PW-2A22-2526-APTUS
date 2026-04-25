<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/FormationController.php';

if (isset($_GET['delete_id']) || isset($_POST['delete_id'])) {
    $formationC = new FormationController();
    $id_to_delete = $_POST['delete_id'] ?? $_GET['delete_id'];
    try {
        $formationC->deleteFormation($id_to_delete);
        $_SESSION['flash_success'] = "Formation supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    header('Location: ../view/backoffice/formations_admin.php');
    exit();
}

header('Location: ../view/backoffice/formations_admin.php');
exit();
