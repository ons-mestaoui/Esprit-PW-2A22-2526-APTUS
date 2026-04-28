<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/FormationController.php';
require_once __DIR__ . '/../model/Formation.php';

// Détection AJAX : si appelé via fetch(), on répond en JSON directement
// Si appelé via formulaire classique, on redirige comme avant
$isAjax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'formations_admin') !== false)
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formationC = new FormationController();

    $is_online = (int)$_POST['is_online'];
    $lien_room = trim($_POST['online_url'] ?? '');

    // Gestion de l'image : on la convertit en base64 pour la stocker en BDD
    $image_base64 = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        $type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
    }

    try {
        $prereq_val = !empty($_POST['prerequis_id']) ? (int)$_POST['prerequis_id'] : null;

        $f = new Formation(
            $_POST['titre'],
            $_POST['description'],
            $_POST['domaine'],
            $_POST['niveau'],
            $_POST['duree'] ?? '0',
            $_POST['date_formation'],
            $image_base64,
            !empty($_POST['id_tuteur']) ? (int)$_POST['id_tuteur'] : null,
            $is_online,
            $lien_room,
            $prereq_val,
            !empty($_POST['date_fin']) ? $_POST['date_fin'] : null
        );

        $formationC->addFormation($f);

        // Réponse JSON directe pour AJAX (pas de redirection)
        header('Content-Type: application/json');
        echo json_encode(['type' => 'success', 'message' => 'Formation ajoutée avec succès !']);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['type' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}
