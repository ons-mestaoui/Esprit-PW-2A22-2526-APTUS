<?php
include_once __DIR__ . '/../config.php';
$db = config::getConnexion();
try {
    $db->exec("ALTER TABLE utilisateur ADD COLUMN two_factor_secret VARCHAR(255) DEFAULT NULL;");
    $db->exec("ALTER TABLE utilisateur ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0;");
    echo "Columns added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
