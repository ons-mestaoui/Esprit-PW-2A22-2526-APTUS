<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Starting Data Injection...\n";

    // 1. Create Tuteurs if missing
    $tuteurs = [
        [1, 'Tuteur', 'Web', 'tuteur1@aptus.com', 'tuteur123', 'Tuteur'],
        [901, 'Expert', 'JS', 'expert901@aptus.com', 'expert123', 'Tuteur']
    ];

    foreach ($tuteurs as $t) {
        $stmt = $db->prepare("INSERT IGNORE INTO utilisateur (id_utilisateur, nom, prenom, email, motDePasse, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($t);
    }
    echo "- Tuteurs checked/created.\n";

    // 2. Inject Badges
    $badges = [
        [1, 'Expert Aptus', 'Badge décerné pour la réussite d\'une formation complète', NULL],
        [2, 'Débutant', 'Badge décerné pour la réussite d\'une formation de niveau Débutant.', NULL],
        [3, 'Intermédiaire', 'Badge décerné pour la réussite d\'une formation de niveau Intermédiaire.', NULL],
        [4, 'Expert', 'Badge décerné pour la réussite d\'une formation de niveau Expert.', NULL]
    ];

    foreach ($badges as $b) {
        $stmt = $db->prepare("INSERT IGNORE INTO badge (id_badge, nom, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute($b);
    }
    echo "- Badges injected.\n";

    // 3. Inject Candidates
    $candidats = [
        [1, NULL, NULL, 'Débutant'],
        [10, NULL, NULL, 'Débutant']
    ];

    foreach ($candidats as $c) {
        $stmt = $db->prepare("INSERT IGNORE INTO candidat (id_candidat, competences, niveauEtudes, niveau) VALUES (?, ?, ?, ?)");
        $stmt->execute($c);
    }
    echo "- Candidates injected.\n";

    // 4. Inject Formations
    // We'll use the data from the user's SQL dump
    $formations = [
        [
            25, ' Initiation au Web (HTML & CSS)', 
            '<p>Découvrez comment structurer et styliser une page web de A à Z.</p><hr style=\"margin: 2rem 0; border: none; border-top: 1px dashed var(--border-color);\"><h4 style=\"color:var(--accent-primary);\">Syllabus Proposé</h4><p style=\"margin-bottom:1rem;\">Plongez dans l\'univers de la création numérique avec cette formation complète conçue pour les débutants. Maîtrisez les fondations du Web en apprenant à structurer vos idées avec HTML5 et à leur donner vie visuellement avec CSS3, pour devenir autonome dans la réalisation de vos premiers sites internet.</p><ul style=\"list-style-type:none; padding:0; margin:0;\">\r\n                <li style=\"margin-bottom: 1rem; background: var(--bg-card); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent-primary);\">\r\n                    <div style=\"font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;\">Introduction et Architecture du Web <span style=\"font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); float: right;\">⏳ 1h00</span></div>\r\n                    <div style=\"font-size: 0.95rem; color: var(--text-secondary);\">Comprendre le fonctionnement d\'un navigateur, le modèle client-serveur et configurer l\'environnement de développement (VS Code).</div>\r\n                </li>\r\n            </ul><hr style=\"margin: 2rem 0; border: none; border-top: 1px dashed var(--border-color);\"><h4 style=\"color:var(--accent-primary);\">Syllabus Proposé</h4><p style=\"margin-bottom:1rem;\">Devenez autonome dans la création de sites web en maîtrisant les deux langages piliers du web : le HTML pour la structure et le CSS pour le design. Une formation pratique conçue pour transformer les débutants en créateurs de contenus web modernes.</p><ul style=\"list-style-type:none; padding:0; margin:0;\">\r\n                <li style=\"margin-bottom: 1rem; background: var(--bg-card); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent-primary);\">\r\n                    <div style=\"font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;\">Introduction au Web et fondamentaux du HTML <span style=\"font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); float: right;\">⏳ 1h30</span></div>\r\n                    <div style=\"font-size: 0.95rem; color: var(--text-secondary);\">Comprendre le fonctionnement du web (client/serveur) et créer sa première structure de page avec les balises de base.</div>\r\n                </li>\r\n            </ul><hr style=\"margin: 2rem 0; border: none; border-top: 1px dashed var(--border-color);\"><h4 style=\"color:var(--accent-primary);\">Syllabus Proposé</h4><p style=\"margin-bottom:1rem;\">Devenez l\'architecte du web en maîtrisant les fondations essentielles : structurez vos idées avec HTML5 et donnez-leur vie avec le design élégant de CSS3.</p><ul style=\"list-style-type:none; padding:0; margin:0;\">\r\n                <li style=\"margin-bottom: 1rem; background: var(--bg-card); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent-primary);\">\r\n                    <div style=\"font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;\">Introduction au Web et à l\'HTML5 <span style=\"font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); float: right;\">⏳ 1h30</span></div>\r\n                    <div style=\"font-size: 0.95rem; color: var(--text-secondary);\">Comprendre le fonctionnement d\'Internet, le rôle du navigateur et la structure fondamentale d\'un document HTML.</div>\r\n                </li>\r\n            \r\n                <li style=\"margin-bottom: 1rem; background: var(--bg-card); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent-primary);\">\r\n                    <div style=\"font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;\">Introduction au CSS3 : Styliser le Web <span style=\"font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); float: right;\">⏳ 2h30</span></div>\r\n                    <div style=\"font-size: 0.95rem; color: var(--text-secondary);\">Découverte des sélecteurs, des propriétés de couleur, de typographie et l\'application de styles externes.</div>\r\n                </li>\r\n            </ul>', 
            'web ', 'Débutant', '2026-04-17 00:00:00', NULL, '', 1, 0, '', 'active', NULL, NULL
        ],
        [
            26, 'Programmation Interactive (JavaScript)', 
            '<p><strong>Apprenez à rendre vos pages dynamiques avec JavaScript.</strong></p><!-- AI_SYLLABUS_START --><hr style=\"margin: 2rem 0; border: none; border-top: 1px dashed var(--border-color);\"><h4 style=\"color:var(--accent-primary);\">Syllabus Proposé</h4>', 
            'web ', 'Intermédiaire', '2026-04-20 00:00:00', NULL, '', 901, 0, '', 'active', 25, NULL
        ],
        [
            27, 'Web Apps avec React.js', 
            '<p>Créez des interfaces modernes avec le framework le plus populaire.</p>', 
            'web ', 'Avancé', '2026-05-20 00:00:00', NULL, '', 901, 0, '', 'active', 26, NULL
        ]
    ];

    foreach ($formations as $f) {
        $stmt = $db->prepare("INSERT IGNORE INTO formation (id_formation, titre, description, domaine, niveau, date_formation, image_base64, duree, id_tuteur, is_online, lien_api_room, statut, prerequis_id, date_fin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($f);
    }
    echo "- Formations injected.\n";

    echo "\nInjection completed successfully!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
