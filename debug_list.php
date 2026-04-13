<?php
require 'config.php';
require 'controller/CVC.php';
$cvc = new CVC();
$list = $cvc->listCVByCandidat(null);
echo "DEBUG CV LIST:\n";
foreach($list as $cv) {
    echo "ID: " . $cv['id_cv'] . " | Nom: " . $cv['nomComplet'] . " | Template: " . $cv['id_template'] . "\n";
}
if(empty($list)) echo "AUCUN CV TROUVÉ\n";
