<?php
include 'config.php';
$db = config::getConnexion();
$res = $db->query('SELECT COUNT(*) FROM peer_sessions');
echo 'Peer Sessions Count: ' . $res->fetchColumn() . "\n";
