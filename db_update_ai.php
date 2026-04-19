<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    $db->exec("ALTER TABLE cv ADD COLUMN ai_analysis TEXT DEFAULT NULL;");
    echo "Column ai_analysis added successfully.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column ai_analysis already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
