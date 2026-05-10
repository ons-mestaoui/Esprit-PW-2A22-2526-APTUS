<?php
include 'config.php';
$db = config::getConnexion();
$ids = [1, 10, 21, 901];
foreach($ids as $id) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM utilisateur WHERE id_utilisateur = ?');
    $stmt->execute([$id]);
    echo "User $id exists: " . ($stmt->fetchColumn() ? 'YES' : 'NO') . "\n";
}
