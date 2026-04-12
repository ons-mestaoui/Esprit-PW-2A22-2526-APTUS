<?php
require 'config.php';
$db = config::getConnexion();

// Disable FK checks
$db->exec('SET FOREIGN_KEY_CHECKS=0');

// Try insert
try {
    $stmt = $db->prepare("INSERT INTO cv (id_candidat, id_template, nomDocument, nomComplet, titrePoste, resume, infoContact, experience, formation, competences, langues, urlPhoto, couleurTheme, statut, dateCreation, dateMiseAJour) VALUES (1, 1, 'CV Test', 'Jean Dupont', 'Dev', 'Mon profil', 'test@test.com', 'Exp', 'Dip', 'PHP', 'FR', '', '#2563eb', 'en_attente', NOW(), NOW())");
    $stmt->execute();
    $id = $db->lastInsertId();
    echo "SUCCESS! CV inserted with id=" . $id . "\n";
    
    // Clean up
    $db->exec("DELETE FROM cv WHERE id_cv=" . $id);
    echo "Cleaned up.\n";
} catch(Exception $e) {
    echo "STILL FAILING: " . $e->getMessage() . "\n";
}

$db->exec('SET FOREIGN_KEY_CHECKS=1');
