<?php
include 'config.php';
$db = config::getConnexion();

echo "Tables matching notification:\n";
$res = $db->query("SHOW TABLES LIKE '%notification%'");
while($row = $res->fetch(PDO::FETCH_NUM)) {
    echo "- " . $row[0] . "\n";
}

echo "\nTables matching peer:\n";
$res = $db->query("SHOW TABLES LIKE '%peer%'");
while($row = $res->fetch(PDO::FETCH_NUM)) {
    echo "- " . $row[0] . "\n";
}
