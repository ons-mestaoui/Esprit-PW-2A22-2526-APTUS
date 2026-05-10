<?php
require_once __DIR__ . '/../config.php';

try {
    $db = config::getConnexion();
    echo "Injecting Test Catalog...\n";

    $formations = [
        [
            'titre' => 'Maîtrise de l\'IA Générative et LLM',
            'description' => "Cette formation intensive explore les fondements des Large Language Models (LLM) comme GPT-4 et Claude. \n\n**Au programme :**\n- Architecture des Transformers\n- Techniques de Prompt Engineering avancées\n- Fine-tuning et RAG (Retrieval Augmented Generation)\n- Éthique et gouvernance de l'IA.\n\n**Objectif :** Devenir capable d'intégrer des agents intelligents dans des flux de travail professionnels.",
            'domaine' => 'Intelligence Artificielle',
            'niveau' => 'Expert',
            'date_formation' => '2026-05-15 09:00:00',
            'date_fin' => '2026-06-15 18:00:00',
            'duree' => '4 semaines',
            'id_tuteur' => 1,
            'statut' => 'active',
            'is_online' => 1
        ],
        [
            'titre' => 'Développement Web Moderne avec Next.js 14',
            'description' => "Apprenez à construire des applications web ultra-performantes. \n\n**Points clés :**\n- App Router et Server Components\n- Gestion d'état avec TanStack Query\n- Optimisation SEO et performance (Core Web Vitals)\n- Déploiement CI/CD sur Vercel.\n\nPréparez-vous à l'avenir du web full-stack.",
            'domaine' => 'Développement Web',
            'niveau' => 'Intermédiaire',
            'date_formation' => '2026-05-20 10:00:00',
            'date_fin' => '2026-07-20 17:00:00',
            'duree' => '2 mois',
            'id_tuteur' => 1,
            'statut' => 'active',
            'is_online' => 1
        ],
        [
            'titre' => 'Fondamentaux du Design UX/UI',
            'description' => "Transformez vos idées en interfaces intuitives et centrées sur l'humain. \n\n**Inclus :**\n- Recherche utilisateur et personas\n- Wireframing et Prototypage haute fidélité sur Figma\n- Tests d'utilisabilité et itération\n- Principes de psychologie cognitive appliqués au design.",
            'domaine' => 'Design & Créativité',
            'niveau' => 'Débutant',
            'date_formation' => '2026-05-12 14:00:00',
            'date_fin' => '2026-05-30 16:00:00',
            'duree' => '3 semaines',
            'id_tuteur' => 1,
            'statut' => 'active',
            'is_online' => 1
        ],
        [
            'titre' => 'Data Science : Analyse et Visualisation',
            'description' => "Maîtrisez le cycle de vie des données. \n\n**Contenu :**\n- Programmation Python pour la Data (Pandas, NumPy)\n- Visualisation de données complexe avec Seaborn et Plotly\n- Introduction au Machine Learning (Scikit-Learn)\n- Storytelling avec les données pour la prise de décision.",
            'domaine' => 'Data Science',
            'niveau' => 'Avancé',
            'date_formation' => '2026-06-01 09:00:00',
            'date_fin' => '2026-08-30 18:00:00',
            'duree' => '3 mois',
            'id_tuteur' => 1,
            'statut' => 'active',
            'is_online' => 1
        ],
        [
            'titre' => 'Leadership et Communication Persuasive',
            'description' => "Développez votre influence et gérez des équipes performantes. \n\n**Modules :**\n- Intelligence émotionnelle et empathie\n- Gestion des conflits et feedback constructif\n- Art oratoire et prise de parole en public\n- Stratégies de négociation gagnant-gagnant.",
            'domaine' => 'Soft Skills',
            'niveau' => 'Débutant',
            'date_formation' => '2026-05-18 09:00:00',
            'date_fin' => '2026-05-22 17:00:00',
            'duree' => '5 jours',
            'id_tuteur' => 1,
            'statut' => 'active',
            'is_online' => 0
        ]
    ];

    $sql = "INSERT INTO formation (titre, description, domaine, niveau, date_formation, date_fin, duree, id_tuteur, statut, is_online) 
            VALUES (:titre, :description, :domaine, :niveau, :date_formation, :date_fin, :duree, :id_tuteur, :statut, :is_online)";
    
    $stmt = $db->prepare($sql);

    foreach ($formations as $f) {
        $stmt->execute($f);
        echo "- Injected: " . $f['titre'] . "\n";
    }

    echo "\nInjection completed! You now have a rich catalog for testing.\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
