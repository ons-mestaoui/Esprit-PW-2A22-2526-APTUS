<?php
require_once 'config.php';
$db = config::getConnexion();

$jobs = [
    [
        'titre' => 'Développeur Web Fullstack',
        'description' => 'Nous recherchons un développeur web passionné par React et Node.js pour rejoindre notre équipe agile.',
        'competences_requises' => 'React, Node.js, JavaScript, SQL, Git',
        'salaire' => 45000,
        'domaine' => 'Informatique',
        'experience_requise' => '2-3 ans'
    ],
    [
        'titre' => 'Développeur R&D Logiciel',
        'description' => 'Poste orienté innovation et recherche dans le domaine du génie logiciel et de l\'intelligence artificielle.',
        'competences_requises' => 'Python, C++, Algorithmique, Machine Learning, R&D',
        'salaire' => 52000,
        'domaine' => 'Informatique',
        'experience_requise' => 'Senior / Expert'
    ],
    [
        'titre' => 'Ingénieur DevOps',
        'description' => 'Mise en place de pipelines CI/CD et gestion de l\'infrastructure Cloud.',
        'competences_requises' => 'Docker, Kubernetes, AWS, Jenkins, Linux',
        'salaire' => 55000,
        'domaine' => 'Informatique',
        'experience_requise' => '3-5 ans'
    ],
    [
        'titre' => 'Data Scientist Senior',
        'description' => 'Analyse de données massives et création de modèles prédictifs pour nos clients.',
        'competences_requises' => 'Python, R, SQL, TensorFlow, Scikit-learn',
        'salaire' => 60000,
        'domaine' => 'Data Science',
        'experience_requise' => '5 ans+'
    ],
    [
        'titre' => 'Architecte Solutions Cloud',
        'description' => 'Conception d\'architectures scalables et sécurisées sur Azure et AWS.',
        'competences_requises' => 'Cloud Architecture, Azure, AWS, Sécurité IT',
        'salaire' => 70000,
        'domaine' => 'Informatique',
        'experience_requise' => 'Expert'
    ]
];

$stmt = $db->prepare("INSERT INTO offreemploi (id_entreprise, titre, description, competences_requises, salaire, domaine, experience_requise, date_publication) 
                    VALUES (1, ?, ?, ?, ?, ?, ?, NOW())");

foreach ($jobs as $j) {
    // Check if exists
    $check = $db->prepare("SELECT id_offre FROM offreemploi WHERE titre = ?");
    $check->execute([$j['titre']]);
    if (!$check->fetch()) {
        $stmt->execute([
            $j['titre'],
            $j['description'],
            $j['competences_requises'],
            $j['salaire'],
            $j['domaine'],
            $j['experience_requise']
        ]);
        echo "Job ajouté: " . $j['titre'] . PHP_EOL;
    }
}
echo "Seeding terminé.";
