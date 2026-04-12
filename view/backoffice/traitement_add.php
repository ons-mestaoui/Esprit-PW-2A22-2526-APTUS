<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../model/Formation.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formationC = new FormationController();

    if (strtotime($_POST['date_formation']) < strtotime(date('Y-m-d'))) {
        die("Erreur: La date de formation ne peut pas être dans le passé.");
    }

    $is_online = (int)$_POST['is_online'];
    $lien_room = trim($_POST['online_url'] ?? '');
    if ($is_online === 1 && empty($lien_room)) {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($_POST['titre']));
        $lien_room = "https://meet.jit.si/Aptus_" . $slug . "_" . uniqid();
    }

    $image_base64 = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        $type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
    }

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
        $lien_room
    );
    
    $formationC->addFormation($f);
    header('Location: formations_admin.php?msg=added');
    exit();
}
