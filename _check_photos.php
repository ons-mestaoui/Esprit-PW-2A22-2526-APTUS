<?php
require 'config.php';
$db = config::getConnexion();

// Check column type
$cols = $db->query("SHOW COLUMNS FROM cv WHERE Field='urlPhoto'")->fetch(PDO::FETCH_ASSOC);
echo "Column type: " . $cols['Type'] . "\n";

// Check stored data
$rows = $db->query("SELECT id_cv, LENGTH(urlPhoto) as len, LEFT(urlPhoto, 60) as preview FROM cv LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "CV #" . $r['id_cv'] . " → len=" . $r['len'] . " | preview=" . $r['preview'] . "\n";
}
