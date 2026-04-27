<?php
/**
 * SCRIPT DE TEST - Aptus AI
 * Ce script génère de fausses données émotionnelles pour tester le tableau de bord du Tuteur dans Jitsi.
 */

require_once __DIR__ . '/config.php';

// ID de la formation en cours (par défaut 1, changez-le si nécessaire ou passez-le dans l'URL ?id_formation=X)
$id_formation = $_GET['id_formation'] ?? 1;

try {
    $db = config::getConnexion();
    
    // 1. On nettoie les anciennes fausses données pour cette formation (pour avoir un test propre)
    $stmt = $db->prepare("DELETE FROM rapport_emotions WHERE id_formation = :id_formation");
    $stmt->execute(['id_formation' => $id_formation]);

    // 2. On récupère les vrais IDs des candidats pour que la simulation soit réaliste
    $stmtC = $db->query("SELECT id FROM utilisateur WHERE role = 'Candidat' OR role = 'candidat'");
    $real_ids = $stmtC->fetchAll(PDO::FETCH_COLUMN);
    
    // Si on n'en trouve pas, on utilise des IDs par défaut présents dans ta capture d'écran
    if (empty($real_ids)) {
        $real_ids = [10, 900, 902];
    }

    $emotions_to_inject = [
        'neutral' => 15,   // 15 "Concentré(e)"
        'happy' => 5,      // 5 "Engagé(e)"
        'surprised' => 3,  // 3 "Surpris(e)"
        'fearful' => 8,    // 8 "Confus(e) / Perdu"
        'sad' => 4         // 4 "Ennuyé(e)"
    ];

    $count = 0;
    foreach ($emotions_to_inject as $emotion => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            // On pioche un ID réel au hasard dans la liste
            $id_candidat_fictif = $real_ids[array_rand($real_ids)];
            
            $insert = $db->prepare("INSERT INTO rapport_emotions (id_candidat, id_formation, emotion_detectee) VALUES (:id_candidat, :id_formation, :emotion)");
            $insert->execute([
                'id_candidat' => $id_candidat_fictif,
                'id_formation' => $id_formation,
                'emotion' => $emotion
            ]);
            $count++;
        }
    }

    echo "<div style='font-family: sans-serif; padding: 20px; background: #e0f2fe; color: #1e3a8a; border-radius: 8px;'>";
    echo "<h2>✅ Simulation Réussie !</h2>";
    echo "<p><strong>$count</strong> réactions émotionnelles fictives ont été ajoutées pour la formation ID: <strong>$id_formation</strong>.</p>";
    echo "<p>Scénario généré : Beaucoup d'élèves concentrés, mais une part significative de la classe est <b>confuse</b> ou <b>ennuyée</b>.</p>";
    echo "<hr>";
    echo "<p>👉 <b>Maintenant, retournez sur votre salle Jitsi Tuteur et cliquez sur le bouton 'Bilan IA de la classe'.</b></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "Erreur lors de la simulation : " . $e->getMessage();
}
