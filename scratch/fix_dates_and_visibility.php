<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Fixing dates and visibility for injected formations...\n";

    // Mettre à jour toutes les formations de test avec des dates en 2026
    $db->exec("UPDATE formation SET 
               date_formation = '2026-05-15 09:00:00', 
               date_fin = '2026-12-31 18:00:00',
               statut = 'active'
               WHERE titre LIKE '[P-%]' OR titre LIKE 'Maîtrise%' OR titre LIKE 'Développement%'");

    echo "- Dates updated to 2026.\n";
    echo "- All test formations set to 'active'.\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
