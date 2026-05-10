<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Creating Learning Path with Prerequisites...\n";

    // 1. Formation de base (Pionnier)
    $db->prepare("INSERT INTO formation (titre, description, domaine, niveau, id_tuteur, statut, is_online) 
                  VALUES ('[P-01] Initiation à la Cybersécurité', 'Découvrez les bases fondamentales : menaces, vecteurs d\'attaque et hygiène numérique.', 'Cybersécurité', 'Débutant', 1, 'active', 1)")->execute();
    $id1 = $db->lastInsertId();
    echo "- Created Level 1 (ID: $id1)\n";

    // 2. Formation intermédiaire (Dépend de la 1)
    $db->prepare("INSERT INTO formation (titre, description, domaine, niveau, id_tuteur, statut, is_online, prerequis_id) 
                  VALUES ('[P-02] Défense Réseau et Protocoles', 'Apprenez à sécuriser les infrastructures : TCP/IP, VPN, et configuration de Firewall.', 'Cybersécurité', 'Intermédiaire', 1, 'active', 1, $id1)")->execute();
    $id2 = $db->lastInsertId();
    echo "- Created Level 2 (ID: $id2, Prereq: $id1)\n";

    // 3. Formation avancée (Dépend de la 2)
    $db->prepare("INSERT INTO formation (titre, description, domaine, niveau, id_tuteur, statut, is_online, prerequis_id) 
                  VALUES ('[P-03] Pentesting et Hacking Éthique', 'Maîtrisez les outils d\'audit de sécurité : Nmap, Metasploit, et exploitation de failles.', 'Cybersécurité', 'Avancé', 1, 'active', 1, $id2)")->execute();
    $id3 = $db->lastInsertId();
    echo "- Created Level 3 (ID: $id3, Prereq: $id2)\n";

    echo "\nLearning path created successfully! Go to the Skill Tree view to see the neural connection.\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
