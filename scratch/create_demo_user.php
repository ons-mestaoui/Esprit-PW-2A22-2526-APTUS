<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Creating Demo User (ID 10)...\n";

    $stmt = $db->prepare("INSERT IGNORE INTO utilisateur (id_utilisateur, nom, prenom, email, motDePasse, role) 
                          VALUES (10, 'Aptus', 'Demo', 'demo@aptus.com', 'demo123', 'Candidat')");
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Demo user created successfully.\n";
    } else {
        echo "Demo user already exists or could not be created.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
