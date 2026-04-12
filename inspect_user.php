<?php
require 'config.php';
$db = config::getConnexion();

// Show utilisateur structure
$cols = $db->query("DESCRIBE utilisateur")->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . ' (' . $col['Type'] . ')' . "\n";
}

echo "\n--- Existing utilisateurs ---\n";
$rows = $db->query("SELECT * FROM utilisateur LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo implode(' | ', $r) . "\n";
}

echo "\n--- Existing candidats ---\n";
$rows2 = $db->query("SELECT * FROM candidat LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows2 as $r) {
    echo implode(' | ', $r) . "\n";
}
