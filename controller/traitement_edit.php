<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/FormationController.php';
require_once __DIR__ . '/../model/Formation.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
    $formationC = new FormationController();
    $id = (int)$_POST['id_formation'];
    
    // Retrieve old formation to keep the old image if not updated
    $formation_old = $formationC->getFormationById($id);
    if (!$formation_old) {
        header('Location: ../view/backoffice/formations_admin.php?msg=notfound');
        exit();
    }

    $is_online = (int) $_POST['is_online'];
    $lien_room = trim($_POST['online_url'] ?? '');

    // Image handling
    $image_base64 = $formation_old['image_base64'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        $type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
    }

    try {
        $f = new Formation(
            $_POST['titre'],
            $_POST['description'],
            $_POST['domaine'],
            $_POST['niveau'],
            $_POST['duree'] ?? '0',
            $_POST['date_formation'],
            $image_base64,
            !empty($_POST['id_tuteur']) ? (int) $_POST['id_tuteur'] : null,
            $is_online,
            $lien_room,
            !empty($_POST['prerequis_id']) ? (int)$_POST['prerequis_id'] : null,
            !empty($_POST['date_fin']) ? $_POST['date_fin'] : null
        );

        $formationC->updateFormation($f, $id);
        $_SESSION['flash_success'] = "Formation modifiée avec succès.";
        header('Location: ../view/backoffice/formations_admin.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        header('Location: ../view/backoffice/edit_formation.php?id=' . $id);
        exit();
    }
} else {
    header('Location: ../view/backoffice/formations_admin.php');
    exit();
}
