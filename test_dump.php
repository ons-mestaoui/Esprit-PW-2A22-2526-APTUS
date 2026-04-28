<?php
require 'config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query('SELECT id_template, nom, structureHtml FROM templates');
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('test_dump2.json', json_encode($templates, JSON_PRETTY_PRINT));
echo "Done";
