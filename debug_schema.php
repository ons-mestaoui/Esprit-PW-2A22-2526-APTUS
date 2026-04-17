<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain');

try {
    $db = config::getConnexion();
    $tables = ['utilisateur', 'candidat', 'inscription', 'Formation'];
    
    foreach ($tables as $t) {
        echo "=== Table: $t ===\n";
        try {
            $stmt = $db->query("DESCRIBE $t");
            while ($row = $stmt->fetch()) {
                echo "- {$row['Field']} ({$row['Type']})\n";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Global Error: " . $e->getMessage();
}
?>
