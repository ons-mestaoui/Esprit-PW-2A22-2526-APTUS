<?php
include 'config.php';
$db = config::getConnexion();
$stmt = $db->query("DESCRIBE message");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
