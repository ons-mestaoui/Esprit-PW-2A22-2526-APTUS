<?php
require_once 'config.php';
$db = config::getConnexion();

$formations = [
    ['Advanced Node.js & Microservices', 'Informatique', 'Avancé', 'Maîtrisez l\'architecture microservices et Node.js haute performance.'],
    ['DevOps: Docker, Kubernetes & CI/CD', 'Informatique', 'Avancé', 'Automatisez vos déploiements avec les outils DevOps modernes.'],
    ['Full-Stack Development with Spring Boot & Angular', 'Informatique', 'Intermédiaire', 'Devenez un expert Java Full-Stack.'],
    ['Cloud Computing (AWS/Azure) Professional', 'Informatique', 'Intermédiaire', 'Déployez vos applications dans le Cloud.'],
    ['Machine Learning with TensorFlow', 'Data Science', 'Avancé', 'Créez des modèles d\'IA complexes avec TensorFlow.'],
    ['Mobile App Development with Flutter', 'Informatique', 'Intermédiaire', 'Créez des apps iOS et Android avec un seul code.'],
    ['Blockchain & Smart Contracts', 'Informatique', 'Avancé', 'Découvrez le Web3 et le développement de Smart Contracts.'],
    ['Ethical Hacking & Penetration Testing', 'Informatique', 'Avancé', 'Sécurisez vos systèmes contre les cyberattaques.'],
    ['PowerBI for Business Intelligence', 'Data Science', 'Intermédiaire', 'Transformez vos données en tableaux de bord interactifs.'],
    ['Git & GitHub for Team Collaboration', 'Informatique', 'Débutant', 'Maîtrisez le versioning pour travailler en équipe.']
];

$stmt = $db->prepare("INSERT INTO formation (titre, domaine, niveau, description) VALUES (?, ?, ?, ?)");

foreach ($formations as $f) {
    // Check if exists to avoid duplicates
    $check = $db->prepare("SELECT id_formation FROM formation WHERE titre = ?");
    $check->execute([$f[0]]);
    if (!$check->fetch()) {
        $stmt->execute($f);
        echo "Ajouté: " . $f[0] . PHP_EOL;
    }
}
echo "Terminé.";
