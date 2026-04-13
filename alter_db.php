<?php
include_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    $db->exec("ALTER TABLE utilisateur ADD COLUMN photo VARCHAR(255) NULL");
    echo "Column 'photo' added successfully to 'utilisateur' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'photo' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
