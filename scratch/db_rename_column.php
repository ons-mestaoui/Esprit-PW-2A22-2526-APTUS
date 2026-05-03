<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = config::getConnexion();

    // Check if job_url exists and rename it
    $pdo->exec("ALTER TABLE cv CHANGE job_url target_job_url VARCHAR(512) DEFAULT NULL");

    echo "Base de données mise à jour (renommage job_url -> target_job_url) avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
