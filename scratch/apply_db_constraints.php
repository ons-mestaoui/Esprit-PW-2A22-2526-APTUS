<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Hardening Database Constraints...\n";

    // 1. S'assurer que les dates sont cohérentes (Pas de 1970)
    // Note: MySQL 8.0+ supporte les CHECK constraints, pour les versions plus anciennes on utilise des valeurs par défaut
    $db->exec("UPDATE formation SET date_formation = NOW() WHERE date_formation < '2024-01-01' OR date_formation IS NULL");
    
    // 2. Ajouter les Foreign Keys manquantes pour garantir l'intégrité du parcours neuronal
    try {
        $db->exec("ALTER TABLE formation ADD CONSTRAINT fk_prerequis FOREIGN KEY (prerequis_id) REFERENCES formation(id_formation) ON DELETE SET NULL");
        echo "- FK prerequis added.\n";
    } catch (Exception $e) { echo "- FK prerequis already exists or error: " . $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE formation ADD CONSTRAINT fk_tuteur_formation FOREIGN KEY (id_tuteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE");
        echo "- FK tuteur added.\n";
    } catch (Exception $e) { 
        try {
            $db->exec("ALTER TABLE formation ADD CONSTRAINT fk_tuteur_formation FOREIGN KEY (id_tuteur) REFERENCES User(id_utilisateur) ON DELETE CASCADE");
            echo "- FK tuteur added (User table).\n";
        } catch (Exception $e2) {
            echo "- FK tuteur error: " . $e2->getMessage() . "\n"; 
        }
    }

    echo "\nDatabase constraints synchronized!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
