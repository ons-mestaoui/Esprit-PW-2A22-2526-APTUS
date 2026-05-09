<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Template.php';
require_once __DIR__ . '/../controller/TemplateC.php';

$tc = new TemplateC();
$dbTemplates = $tc->listeTemplates();

if (!empty($dbTemplates)) {
    $t = $dbTemplates[0];
    echo "--- TEMPLATE: " . $t['nom'] . " ---\n";
    echo $t['structureHtml'];
    echo "\n--- END ---\n";
} else {
    echo "No templates found.";
}
?>
