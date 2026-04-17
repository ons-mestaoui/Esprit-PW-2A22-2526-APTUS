<?php
require_once __DIR__ . '/config.php';

echo "<h2 style='font-family:sans-serif;'>Configuration du Skill Tree Aptus</h2>";

try {
    $db = config::getConnexion();
    
    // 1. Ajouter la colonne si elle n'existe pas
    try {
        $db->exec("ALTER TABLE Formation ADD COLUMN prerequis_id INT DEFAULT NULL;");
        echo "<p style='color:green;'>✅ Colonne 'prerequis_id' ajoutée avec succès à la table Formation.</p>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
            echo "<p style='color:gray;'>ℹ️ La colonne 'prerequis_id' existe déjà.</p>";
        } else {
            throw $e;
        }
    }

    // 2. Lier deux formations au hasard pour créer un arbre de démonstration !
    $stmt = $db->query("SELECT id_formation, titre FROM Formation LIMIT 2");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($formations) >= 2) {
        $parent = $formations[0];
        $child = $formations[1];

        // Lier la formation 2 à la formation 1
        $update = $db->prepare("UPDATE Formation SET prerequis_id = :parent_id WHERE id_formation = :child_id");
        $update->execute([
            'parent_id' => $parent['id_formation'],
            'child_id' => $child['id_formation']
        ]);

        echo "<p style='color:green;'>✅ <b>Démonstration prête !</b> La formation « {$child['titre']} » dépend maintenant de la formation « {$parent['titre']} ».</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Vous devez avoir au moins 2 formations créées dans votre catalogue pour voir l'arbre fonctionner.</p>";
    }

    echo "<hr>";
    echo "<a href='view/frontoffice/skill_tree.php' style='display:inline-block; padding:10px 20px; background:#6366f1; color:white; text-decoration:none; border-radius:8px;'>Retourner voir l'Arbre de Compétences</a>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur critique : " . $e->getMessage() . "</p>";
}
?>
