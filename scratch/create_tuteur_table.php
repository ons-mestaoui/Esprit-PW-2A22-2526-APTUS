<?php
require_once 'config.php';

try {
    $db = config::getConnexion();
    
    // Create tuteur table
    $sql = "CREATE TABLE IF NOT EXISTS `tuteur` (
      `id_tuteur` int(11) NOT NULL,
      `specialite` varchar(255) DEFAULT NULL,
      `experience` varchar(255) DEFAULT NULL,
      `biographie` text DEFAULT NULL,
      PRIMARY KEY (`id_tuteur`),
      CONSTRAINT `fk_tuteur_utilisateur` FOREIGN KEY (`id_tuteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "Table 'tuteur' créée avec succès !\n";
    
} catch (Exception $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage() . "\n";
}
?>
