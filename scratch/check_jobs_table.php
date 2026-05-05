<?php
require_once 'config.php';
$db = config::getConnexion();
$stmt = $db->query("DESCRIBE offreemploi");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
