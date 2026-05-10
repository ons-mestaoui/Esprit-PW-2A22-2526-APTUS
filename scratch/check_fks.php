<?php
include 'config.php';
$db = config::getConnexion();

echo "Foreign Keys for table 'formation':\n";
$res = $db->query("SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                   FROM information_schema.KEY_COLUMN_USAGE 
                   WHERE TABLE_NAME = 'formation' 
                   AND TABLE_SCHEMA = 'aptus' 
                   AND REFERENCED_TABLE_NAME IS NOT NULL");
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
