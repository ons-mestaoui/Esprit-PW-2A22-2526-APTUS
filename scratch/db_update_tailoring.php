<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = config::getConnexion();

    // Add is_tailored column
    $pdo->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS is_tailored TINYINT(1) DEFAULT 0");
    
    // Add tailoring_report column
    $pdo->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS tailoring_report LONGTEXT DEFAULT NULL");
    
    // Add job_url column
    $pdo->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS job_url VARCHAR(512) DEFAULT NULL");

    echo "Base de données mise à jour avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
