<?php
include 'config.php';
$db = config::getConnexion();

function checkTable($db, $tableName) {
    echo "\nTable $tableName:\n";
    try {
        $res = $db->query("DESCRIBE $tableName");
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "  NOT FOUND\n";
    }
}

$tables = ['chapitre', 'lecon', 'quiz', 'reponse', 'notifications_formation', 'user_badges', 'peer_sessions', 'peer_reviews'];
foreach($tables as $t) checkTable($db, $t);
