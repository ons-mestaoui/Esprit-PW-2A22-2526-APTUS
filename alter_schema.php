<?php
require 'config.php';
$db = config::getConnexion();
$db->exec('ALTER TABLE templates MODIFY urlMiniature LONGTEXT');
echo 'Schema updated';
