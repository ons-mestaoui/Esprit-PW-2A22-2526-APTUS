<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Renaming Peer Columns and Adding FKs...\n";

    // 1. Rename peer_sessions columns
    $db->exec("ALTER TABLE peer_sessions CHANGE id_hote mentor_id INT NOT NULL");
    $db->exec("ALTER TABLE peer_sessions CHANGE id_invite requester_id INT NOT NULL");
    $db->exec("ALTER TABLE peer_sessions CHANGE lien_visio meeting_link VARCHAR(255)");
    $db->exec("ALTER TABLE peer_sessions CHANGE statut status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending'");
    $db->exec("ALTER TABLE peer_sessions CHANGE date_session created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    echo "- Table 'peer_sessions' columns renamed and updated.\n";

    // 2. Rename peer_reviews columns
    $db->exec("ALTER TABLE peer_reviews CHANGE note rating INT NOT NULL");
    $db->exec("ALTER TABLE peer_reviews CHANGE commentaire comment TEXT");
    $db->exec("ALTER TABLE peer_reviews CHANGE date_review created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    echo "- Table 'peer_reviews' columns renamed.\n";

    // 3. Re-add FKs
    function addFK($db, $table, $column, $refTable, $refColumn) {
        try {
            $constraintName = "fk_{$table}_{$column}";
            $db->exec("ALTER TABLE $table ADD CONSTRAINT $constraintName 
                       FOREIGN KEY ($column) REFERENCES $refTable($refColumn) 
                       ON DELETE CASCADE ON UPDATE CASCADE");
            echo "- FK added: $table($column) -> $refTable($refColumn)\n";
        } catch (Exception $e) {
            echo "- Error on $table($column): " . $e->getMessage() . "\n";
        }
    }

    addFK($db, 'peer_sessions', 'mentor_id', 'utilisateur', 'id_utilisateur');
    addFK($db, 'peer_sessions', 'requester_id', 'utilisateur', 'id_utilisateur');

    echo "\nFinal stabilization completed!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
