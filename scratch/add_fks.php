<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Securing Database with Foreign Keys...\n";

    // Helper to add FK safely
    function addFK($db, $table, $column, $refTable, $refColumn) {
        try {
            $constraintName = "fk_{$table}_{$column}";
            $db->exec("ALTER TABLE $table ADD CONSTRAINT $constraintName 
                       FOREIGN KEY ($column) REFERENCES $refTable($refColumn) 
                       ON DELETE CASCADE ON UPDATE CASCADE");
            echo "- FK added: $table($column) -> $refTable($refColumn)\n";
        } catch (Exception $e) {
            echo "- Skip/Error on $table($column): " . $e->getMessage() . "\n";
        }
    }

    // 1. Notifications
    addFK($db, 'notifications_formation', 'id_utilisateur', 'utilisateur', 'id_utilisateur');

    // 2. User Badges
    addFK($db, 'user_badges', 'id_formation', 'formation', 'id_formation');
    addFK($db, 'user_badges', 'id_user', 'utilisateur', 'id_utilisateur');
    addFK($db, 'user_badges', 'id_badge', 'badge', 'id_badge');

    // 3. Peer Sessions
    addFK($db, 'peer_sessions', 'id_formation', 'formation', 'id_formation');
    addFK($db, 'peer_sessions', 'mentor_id', 'utilisateur', 'id_utilisateur');
    addFK($db, 'peer_sessions', 'requester_id', 'utilisateur', 'id_utilisateur');

    // 4. Peer Reviews
    addFK($db, 'peer_reviews', 'id_session', 'peer_sessions', 'id_session');

    echo "\nIntegrity check completed!\n";

} catch (Exception $e) {
    echo "\nGLOBAL ERROR: " . $e->getMessage() . "\n";
}
