<?php
include 'config.php';
$db = config::getConnexion();
$stmt = $db->query("DESCRIBE formation");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
