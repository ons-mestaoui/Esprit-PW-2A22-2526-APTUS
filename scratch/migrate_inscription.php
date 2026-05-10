<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Starting migration for 'inscription' table...\n";

    // 1. Check current columns
    $res = $db->query("DESCRIBE inscription");
    $columns = [];
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }

    // 2. Rename id_candidat to id_utilisateur if needed
    if (in_array('id_candidat', $columns) && !in_array('id_utilisateur', $columns)) {
        echo "- Renaming 'id_candidat' to 'id_utilisateur'...\n";
        $db->exec("ALTER TABLE inscription CHANGE id_candidat id_utilisateur INT(11)");
    }

    // 3. Add progression if missing
    if (!in_array('progression', $columns)) {
        echo "- Adding 'progression' column...\n";
        $db->exec("ALTER TABLE inscription ADD progression INT(11) DEFAULT 0");
    }

    // 4. Add chapitres_vus if missing
    if (!in_array('chapitres_vus', $columns)) {
        echo "- Adding 'chapitres_vus' column...\n";
        $db->exec("ALTER TABLE inscription ADD chapitres_vus TEXT NULL");
    }

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "\nERROR during migration: " . $e->getMessage() . "\n";
}
