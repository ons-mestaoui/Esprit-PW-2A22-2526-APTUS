<?php
require_once '../../controller/offreC.php';
$offreC = new offreC();

// On simule un ID candidat (à remplacer par la session plus tard)
$id_candidat = 1;

if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $id_offre = intval($_POST['id_offre']);
    $result = $offreC->toggleFavori($id_candidat, $id_offre);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'get_favoris') {
    $favoris = $offreC->getFavorisByUser($id_candidat);
    $results = [];
    if ($favoris) {
        while ($o = $favoris->fetch()) {
            $results[] = $o;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'results' => $results]);
    exit();
}
?>