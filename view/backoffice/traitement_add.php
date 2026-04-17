<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../model/Formation.php';

// Ce fichier traite le formulaire d'ajout (POST uniquement)
// Il crée l'objet Formation et appelle addFormation() du contrôleur
// Si la validation échoue, l'exception est attrapée et stockée en session
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
            $prereq_val
        );
        
        $formationC->addFormation($f);
        $_SESSION['flash_success'] = "Formation ajoutée avec succès.";
        header('Location: formations_admin.php');
    } catch (Exception $e) {
        // Si validateFormation() lance une exception, on la récupère ici
        // et on la met dans la session pour l'afficher via SweetAlert
        $_SESSION['flash_error'] = "Erreur de validation : " . $e->getMessage();
        header('Location: formations_admin.php');
    }
    exit();
}
