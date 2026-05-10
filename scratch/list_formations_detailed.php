<?php
include 'config.php';
$db = config::getConnexion();

echo "Detailed Formations:\n";
$res = $db->query('SELECT id_formation, titre, domaine, statut FROM formation');
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id_formation'] . " | " . $row['titre'] . " | [" . $row['domaine'] . "] | " . $row['statut'] . "\n";
}
