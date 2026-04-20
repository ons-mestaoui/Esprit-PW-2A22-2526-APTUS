<?php
require_once 'config.php';

try {
    $db = config::getConnexion();

    // 1. Table Notifications
    $sql1 = "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        type VARCHAR(50),
        message TEXT NOT NULL,
        url_action VARCHAR(255),
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 2. Table Peer Sessions
    $sql2 = "CREATE TABLE IF NOT EXISTS peer_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        formation_id INT NOT NULL,
        requester_id INT NOT NULL,
        mentor_id INT NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        meeting_link VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 3. Table Peer Reviews
    $sql3 = "CREATE TABLE IF NOT EXISTS peer_reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 4. Table Messages (Chat Hybride)
    $sql4 = "CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        formation_id INT,
        content TEXT,
        is_auto_reply TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql1);
    $db->exec($sql2);
    $db->exec($sql3);
    $db->exec($sql4);

    echo "Tables d'engagement créées avec succès !";

} catch (Exception $e) {
    die("Erreur lors de la mise à jour de la base de données : " . $e->getMessage());
}
