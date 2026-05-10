<?php
include 'config.php';
$db = config::getConnexion();

echo "Users:\n";
$res = $db->query('SELECT id_utilisateur, nom FROM utilisateur LIMIT 5');
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id_utilisateur'] . ': ' . $row['nom'] . "\n";
}

echo "\nCandidates:\n";
try {
    $res = $db->query('SELECT id_candidat FROM candidat LIMIT 5');
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id_candidat'] . "\n";
    }
} catch(Exception $e) { echo $e->getMessage(); }

echo "\nInscriptions:\n";
try {
    $res = $db->query('SELECT * FROM inscription LIMIT 5');
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch(Exception $e) { echo $e->getMessage(); }
