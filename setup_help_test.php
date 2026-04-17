<?php
require_once __DIR__ . '/config.php';
echo "<h2 style='font-family:sans-serif;'>🧪 Test Final : Peer Learning</h2>";

try {
    $db = config::getConnexion();

    // 1. Créer le Mentor dans la table 'candidat' (pour la clé étrangère)
    $mentorEmail = 'mentor@aptus.com';
    $stmt = $db->prepare("SELECT id FROM candidat LIMIT 1"); // On vérifie s'il y a déjà quelqu'un
    $stmt->execute();
    $mentorId = $stmt->fetchColumn();

    if (!$mentorId) {
        // On crée un candidat de test si la table est vide
        $db->exec("INSERT INTO candidat (nom) VALUES ('Jean Mentor Expert')");
        $mentorId = $db->lastInsertId();
        echo "<p style='color:green;'>✅ Nouveau candidat créé pour le test (ID: $mentorId).</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ Utilisation du candidat existant (ID: $mentorId) pour le test.</p>";
    }

    // 2. Inscription à 100% pour toutes les formations
    $formations = $db->query("SELECT id_formation FROM Formation")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($formations as $f_id) {
        $db->prepare("DELETE FROM inscription WHERE id_user = ? AND id_formation = ?")->execute([$mentorId, $f_id]);
        $db->prepare("INSERT INTO inscription (id_user, id_formation, progression, statut) VALUES (?, ?, 100, 'Terminée')")
            ->execute([$mentorId, $f_id]);
    }

    echo "<p style='color:green;'>✅ Mentor prêt avec 100% de réussite !</p>";
    echo "<p><b>Action :</b> Allez dans 'Mes Formations' et cliquez sur 'Demander de l'aide'.</p>";
    echo "<hr><a href='view/frontoffice/formations_my.php' style='padding:10px;background:#10b981;color:white;text-decoration:none;border-radius:5px;'>Tester maintenant</a>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
