<?php
include 'config.php';
$db = config::getConnexion();
$tables = ['messages', 'notifications_formation'];
foreach ($tables as $table) {
    echo "\nTable $table:\n";
    try {
        $stmt = $db->query("DESCRIBE $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) { echo "  NOT FOUND\n"; }
}
