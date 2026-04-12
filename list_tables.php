<?php
require 'config.php';
$db = config::getConnexion();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $tables);
