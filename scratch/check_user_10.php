<?php
include 'config.php';
$db = config::getConnexion();

$userIdsToCheck = [10, 21, 23, 24, 26, 28];
foreach($userIdsToCheck as $id) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM utilisateur WHERE id_utilisateur = ?');
    $stmt->execute([$id]);
    $exists = $stmt->fetchColumn();
    echo "User $id exists: " . ($exists ? 'YES' : 'NO') . "\n";
}
