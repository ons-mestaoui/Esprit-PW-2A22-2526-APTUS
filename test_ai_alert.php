<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    $id_candidat = 10; // Un étudiant de test
    $id_formation = 30; // La formation que tu gères sur ton image
    $emotion = 'Confusion';
    $now = date('Y-m-d H:i:s');

    echo "<pre>Simulation d'alerte en cours...\n";

    for ($i = 0; $i < 5; $i++) {
        $stmt = $db->prepare("INSERT INTO rapport_emotions (id_candidat, id_formation, emotion_detectee, date_mesure) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_candidat, $id_formation, $emotion, $now]);
        echo "Émotion '$emotion' injectée à $now\n";
    }

    echo "\nSuccès ! Retourne sur ton Dashboard et attends 30 secondes.\n";
    echo "L'IA va détecter ces 5 émotions récentes et déclencher l'alerte.\n";
    echo "<a href='view/frontoffice/tuteur_dashboard.php?tuteur_id=1'>Retour au Dashboard</a></pre>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
