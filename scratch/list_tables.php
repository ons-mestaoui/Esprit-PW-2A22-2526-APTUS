<?php
include 'config.php';
$db = config::getConnexion();
echo "Tables in DB:\n";
$res = $db->query('SHOW TABLES');
while($row = $res->fetch(PDO::FETCH_NUM)) {
    echo "- " . $row[0] . "\n";
}
