<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    echo "<pre>Démarrage de la synchronisation des badges...\n";

    // 0. S'assurer que les badges de base existent (Débutant, Intermédiaire, Expert)
    $niveauxRequis = ['Débutant', 'Intermédiaire', 'Expert'];
    foreach ($niveauxRequis as $n) {
        $check = $db->prepare("SELECT id_badge FROM badge WHERE nom = ?");
        $check->execute([$n]);
        if (!$check->fetch()) {
            $ins = $db->prepare("INSERT INTO badge (nom, description) VALUES (?, ?)");
            $ins->execute([$n, "Badge décerné pour la réussite d'une formation de niveau $n."]);
            echo "Création du badge '$n' dans la table 'badge'.\n";
        }
    }

    // 1. On récupère toutes les inscriptions terminées
    $sql = "SELECT i.id_user, f.niveau, f.id_formation
            FROM inscription i
            JOIN formation f ON i.id_formation = f.id_formation
            WHERE i.statut = 'Terminée' OR i.progression = 100";
    
    $stmt = $db->query($sql);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($inscriptions as $ins) {
        $id_user = $ins['id_user'];
        $niveau = $ins['niveau'];
        $id_f = $ins['id_formation'];

        // 2. Trouver le badge correspondant au niveau
        $stmtB = $db->prepare("SELECT id_badge FROM badge WHERE nom = ? LIMIT 1");
        $stmtB->execute([$niveau]);
        $id_badge = $stmtB->fetchColumn();

        if ($id_badge) {
            // 3. Insérer dans user_badges s'il n'existe pas déjà
            $stmtInsert = $db->prepare("INSERT IGNORE INTO user_badges (id_user, id_badge, id_formation, date_obtention) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$id_user, $id_badge, $id_f, date('Y-m-d')]);
            
            if ($stmtInsert->rowCount() > 0) {
                echo "✅ Badge '$niveau' attribué à l'utilisateur ID $id_user.\n";
                $count++;
            }
        }
    }

    echo "\nTerminé ! $count nouveaux badges ont été synchronisés.\n";
    echo "<a href='view/frontoffice/profil_candidat.php'>Voir mon profil</a></pre>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
