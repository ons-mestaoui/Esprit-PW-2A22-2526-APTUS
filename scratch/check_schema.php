<?php
include 'config.php';
$db = config::getConnexion();
$tables_res = $db->query("SHOW TABLES");
$all_tables = [];
echo "Available tables:\n";
while($row = $tables_res->fetch(PDO::FETCH_NUM)) {
    echo "- " . $row[0] . "\n";
    $all_tables[] = $row[0];
}
echo "\n";

$target_tables = ['utilisateur', 'User', 'formation', 'inscription', 'candidat', 'notifications_formation', 'notification', 'notifications', 'peer_sessions', 'peer_reviews'];
foreach($target_tables as $t) {
    if (!in_array($t, $all_tables) && !in_array(strtolower($t), array_map('strtolower', $all_tables))) {
        echo "Table $t NOT FOUND in database.\n\n";
        continue;
    }
    try {
        $res = $db->query("DESCRIBE $t");
        echo "Table $t:\n";
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            echo '  ' . $row['Field'] . "\n";
        }
    } catch(Exception $e) {
        echo "Error describing $t: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
