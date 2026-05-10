<?php
require_once __DIR__ . '/config.php';
$db = config::getConnexion();
try {
    $db->exec("ALTER TABLE rapport_ia ADD COLUMN IF NOT EXISTS keywords TEXT AFTER suggestions");
    echo "Column 'keywords' added or already exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
