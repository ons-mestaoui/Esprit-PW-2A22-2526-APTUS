<?php
require_once 'config.php';
$db = config::getConnexion();
$stmt = $db->query("SELECT id_entreprise, raisonSociale FROM entreprise LIMIT 5");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
