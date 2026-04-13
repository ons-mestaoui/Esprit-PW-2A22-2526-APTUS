<?php
require 'config.php';
$db = config::getConnexion();
$db->exec('ALTER TABLE cv MODIFY urlPhoto LONGTEXT');
echo 'Schema updated for cv.urlPhoto to LONGTEXT';
