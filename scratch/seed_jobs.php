<?php
require_once __DIR__ . '/../config.php';

$db = config::getConnexion();

try {
    // 1. Trouver une entreprise ou en créer une
    $stmt = $db->query("SELECT id_entreprise FROM entreprise LIMIT 1");
    $ent = $stmt->fetch();
    
    if (!$ent) {
        // Créer une entreprise fictive si aucune n'existe dans utilisateur
        $db->exec("INSERT INTO utilisateur (nom, prenom, email, mdp, role, date_creation) VALUES ('Aptus Global', 'Admin', 'contact@aptus.tn', 'dummy', 'Entreprise', NOW())");
        $id_entreprise = $db->lastInsertId();
    } else {
        $id_entreprise = $ent['id_entreprise'];
    }

    $jobs = [
        [
            'titre' => 'Développeur Fullstack PHP / Vue.js',
            'desc' => 'Nous recherchons un talent passionné par le web pour rejoindre notre équipe dynamique.',
            'domaine' => 'Informatique / Développement',
            'competences' => 'PHP, Laravel, Vue.js, MySQL, Git',
            'exp' => '2-3 ans',
            'salaire' => 2500,
            'type' => 'Hybride'
        ],
        [
            'titre' => 'Ingénieur DevOps',
            'desc' => 'Spécialiste Docker et Kubernetes pour automatiser nos déploiements.',
            'domaine' => 'Informatique / Infrastructure',
            'competences' => 'Docker, Kubernetes, AWS, Jenkins, Linux',
            'exp' => '3-5 ans',
            'salaire' => 4500,
            'type' => 'À distance'
        ],
        [
            'titre' => 'Data Scientist Junior',
            'desc' => 'Analysez nos données pour en extraire de la valeur stratégique.',
            'domaine' => 'Data Science',
            'competences' => 'Python, SQL, Machine Learning, Pandas',
            'exp' => '0-2 ans',
            'salaire' => 1800,
            'type' => 'Sur site'
        ],
        [
            'titre' => 'Consultant Cybersécurité',
            'desc' => 'Audit et sécurisation de nos infrastructures critiques.',
            'domaine' => 'Sécurité Informatique',
            'competences' => 'Pentesting, SOC, ISO 27001, Firewall',
            'exp' => '5 ans+',
            'salaire' => 5000,
            'type' => 'Sur site'
        ],
        [
            'titre' => 'Développeur Mobile React Native',
            'desc' => 'Créez des applications performantes pour iOS et Android.',
            'domaine' => 'Informatique / Mobile',
            'competences' => 'React Native, JavaScript, Firebase, API REST',
            'exp' => '1-3 ans',
            'salaire' => 2200,
            'type' => 'Hybride'
        ],
        [
            'titre' => 'Product Manager IT',
            'desc' => 'Faites le pont entre les besoins métiers et les équipes techniques.',
            'domaine' => 'Gestion de Projet',
            'competences' => 'Agile, Scrum, Jira, Roadmap',
            'exp' => '3 ans',
            'salaire' => 3500,
            'type' => 'Sur site'
        ]
    ];

    $stmt = $db->prepare("INSERT INTO offreemploi (id_entreprise, titre, description, domaine, competences_requises, experience_requise, salaire, question, date_publication, date_expir, type, lieu) 
                          VALUES (:id_e, :t, :d, :dom, :comp, :exp, :sal, :q, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), :type, 'Tunis')");

    foreach ($jobs as $j) {
        $stmt->execute([
            'id_e' => $id_entreprise,
            't'    => $j['titre'],
            'd'    => $j['desc'],
            'dom'  => $j['domaine'],
            'comp' => $j['competences'],
            'exp'  => $j['exp'],
            'sal'  => $j['salaire'],
            'q'    => "Décrivez votre expérience avec " . explode(',', $j['competences'])[0],
            'type' => $j['type']
        ]);
    }

    echo "Successfully injected " . count($jobs) . " job offers.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
