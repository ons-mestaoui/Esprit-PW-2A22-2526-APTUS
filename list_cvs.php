<?php
require 'config.php';
$db = config::getConnexion();
$rows = $db->query("SELECT id_cv, nomComplet, titrePoste, id_candidat, dateMiseAJour FROM cv ORDER BY id_cv DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "TABLE CV VIDE\n";
} else {
    foreach($rows as $row) {
        echo "id=" . $row['id_cv'] . " | " . $row['nomComplet'] . " | candidat=" . ($row['id_candidat'] ?? 'NULL') . "\n";
    }
}
