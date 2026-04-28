<?php
require_once __DIR__ . '/config.php';
$db = config::getConnexion();
echo "--- CV id_template distribution ---\n";
$q = $db->query("SELECT id_template, count(*) as c FROM cv GROUP BY id_template");
print_r($q->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Templates existence ---\n";
$q = $db->query("SELECT id_template, nom, estPremium FROM templates");
print_r($q->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Usage Stats Query Result ---\n";
$q = $db->query("
    SELECT t.estPremium, COUNT(c.id_cv) as usage_count 
    FROM templates t 
    JOIN cv c ON t.id_template = c.id_template 
    GROUP BY t.estPremium
");
print_r($q->fetchAll(PDO::FETCH_ASSOC));
?>
