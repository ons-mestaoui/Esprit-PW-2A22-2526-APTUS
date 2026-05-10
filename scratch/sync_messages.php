<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Synchronizing Messages Table...\n";

    // 1. Rename table if needed
    $check = $db->query("SHOW TABLES LIKE 'messages'");
    if (!$check->fetch()) {
        $db->exec("RENAME TABLE message TO messages");
        echo "- Table 'message' renamed to 'messages'.\n";
    }

    // 2. Align columns
    $cols = $db->query("SHOW COLUMNS FROM messages");
    $fields = [];
    while($row = $cols->fetch(PDO::FETCH_ASSOC)) { $fields[] = $row['Field']; }

    if (in_array('id_expediteur', $fields)) {
        $db->exec("ALTER TABLE messages CHANGE id_expediteur sender_id INT NOT NULL");
        echo "- Column 'id_expediteur' -> 'sender_id'.\n";
    }
    if (in_array('id_destinataire', $fields)) {
        $db->exec("ALTER TABLE messages CHANGE id_destinataire receiver_id INT NOT NULL");
        echo "- Column 'id_destinataire' -> 'receiver_id'.\n";
    }
    if (in_array('contenu', $fields)) {
        $db->exec("ALTER TABLE messages CHANGE contenu content TEXT NOT NULL");
        echo "- Column 'contenu' -> 'content'.\n";
    }
    if (in_array('lu', $fields)) {
        $db->exec("ALTER TABLE messages CHANGE lu is_read TINYINT DEFAULT 0");
        echo "- Column 'lu' -> 'is_read'.\n";
    }
    if (in_array('date_envoi', $fields)) {
        $db->exec("ALTER TABLE messages CHANGE date_envoi created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "- Column 'date_envoi' -> 'created_at'.\n";
    }

    // 3. Add missing columns
    $cols = $db->query("SHOW COLUMNS FROM messages");
    $fields = [];
    while($row = $cols->fetch(PDO::FETCH_ASSOC)) { $fields[] = $row['Field']; }

    if (!in_array('id_formation', $fields)) {
        $db->exec("ALTER TABLE messages ADD COLUMN id_formation INT AFTER receiver_id");
        echo "- Column 'id_formation' added.\n";
    }
    if (!in_array('is_auto_reply', $fields)) {
        $db->exec("ALTER TABLE messages ADD COLUMN is_auto_reply TINYINT DEFAULT 0 AFTER content");
        echo "- Column 'is_auto_reply' added.\n";
    }

    // 4. Secure with FKs
    try {
        $db->exec("ALTER TABLE messages ADD CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE");
        $db->exec("ALTER TABLE messages ADD CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE");
        $db->exec("ALTER TABLE messages ADD CONSTRAINT fk_messages_formation FOREIGN KEY (id_formation) REFERENCES formation(id_formation) ON DELETE CASCADE");
        echo "- Foreign Keys added to 'messages'.\n";
    } catch (Exception $e) { echo "- FK already exist or user data conflict: " . $e->getMessage() . "\n"; }

    echo "\nMessages synchronization completed!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
