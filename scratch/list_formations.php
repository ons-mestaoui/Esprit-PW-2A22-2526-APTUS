<?php
include 'config.php';
$db = config::getConnexion();

echo "Existing Formations:\n";
$res = $db->query('SELECT id_formation, titre FROM formation');
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id_formation'] . ': ' . $row['titre'] . "\n";
}
