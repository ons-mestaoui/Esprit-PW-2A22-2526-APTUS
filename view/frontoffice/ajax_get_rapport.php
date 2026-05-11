<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No ID provided']);
    exit;
}

$id = intval($_GET['id']);
$vc = new VeilleC();
$rapport = $vc->recupererRapport($id);

if ($rapport) {
    echo json_encode(['success' => true, 'contenu' => strip_tags($rapport['contenu_detaille'])]);
} else {
    echo json_encode(['success' => false, 'error' => 'Report not found']);
}
