<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $cvc = new CVC();
    // Safety: check owner if session is used
    $cvc->deleteCV($id);
}

header("Location: cv_my.php");
exit;
