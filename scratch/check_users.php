<?php
include 'config.php';
$db = config::getConnexion();
$stmt = $db->query("SELECT id_utilisateur, nom FROM utilisateur LIMIT 10");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id_utilisateur'] . " - " . $row['nom'] . "\n";
}
