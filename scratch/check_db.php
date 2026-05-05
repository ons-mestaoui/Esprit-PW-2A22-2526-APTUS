<?php
require_once 'config.php';
$db = config::getConnexion();
$stmt = $db->query("SELECT titre, domaine FROM formation");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
