<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Starting Final Synchronization...\n";

    // 1. Create notifications_formation
    $sqlNotif = "CREATE TABLE IF NOT EXISTS notifications_formation (
        id_notifs INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        type VARCHAR(50),
        message TEXT,
        url_action VARCHAR(255),
        icon VARCHAR(50) DEFAULT 'bell',
        is_read TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sqlNotif);
    echo "- Table 'notifications_formation' created.\n";

    // 2. Update user_badges (Add id_formation)
    // Check if column exists first
    $check = $db->query("SHOW COLUMNS FROM user_badges LIKE 'id_formation'");
    if (!$check->fetch()) {
        $db->exec("ALTER TABLE user_badges ADD COLUMN id_formation INT AFTER id_badge");
        echo "- Column 'id_formation' added to 'user_badges'.\n";
    } else {
        echo "- Column 'id_formation' already exists in 'user_badges'.\n";
    }

    // 3. Align Peer Sessions (Optional but recommended if controller uses them)
    // Looking at PeerLearningController, it uses: id_formation, requester_id, mentor_id, meeting_link, status, created_at
    // Existing DB has: id_hote, id_invite, lien_visio, statut, date_session
    // I will ADD the new columns or RENAME them. Renaming is better if we want to match the NEW code.
    // However, user said "it was functioning". 
    // Let's check if we can just ADD the missing ones.
    
    $checkF = $db->query("SHOW COLUMNS FROM peer_sessions LIKE 'id_formation'");
    if (!$checkF->fetch()) {
        $db->exec("ALTER TABLE peer_sessions ADD COLUMN id_formation INT AFTER id_session");
        echo "- Column 'id_formation' added to 'peer_sessions'.\n";
    }

    echo "\nSynchronization completed!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
