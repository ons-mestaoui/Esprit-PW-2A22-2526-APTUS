<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_cv = $input['id_cv'] ?? null;
$cvText = $input['cvText'] ?? '';

if (!$id_cv || empty(trim($cvText))) {
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides.']);
    exit;
}

require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/RapportIAController.php';
require_once __DIR__ . '/../../model/CV.php';
require_once __DIR__ . '/../../model/RapportIA.php';

// 1. Appeler l'IA via le contrôleur spécialisé
$riac = new RapportIAController();
$analysisJsonString = $riac->analyzeCV($cvText);

// Vérifier si c'est du JSON valide
$decoded = json_decode($analysisJsonString, true);
if (!$decoded || !isset($decoded['score_ats'])) {
    echo json_encode(['success' => false, 'error' => 'L\'IA a renvoyé un format invalide.']);
    exit;
}

// 2. Sauvegarder dans la base de données
$cvc = new CVC();
// $riac est déjà instancié plus haut
$cvData = $cvc->getCVById($id_cv);

if ($cvData) {
    // A. Mise à jour dans la table CV (Ancienne méthode - pour compatibilité)
    $cvModel = new CV(
        $cvData['id_cv'],
        $cvData['id_candidat'],
        $cvData['id_template'],
        $cvData['nomDocument'],
        $cvData['nomComplet'],
        $cvData['titrePoste'],
        $cvData['resume'],
        $cvData['infoContact'],
        $cvData['experience'],
        $cvData['formation'],
        $cvData['competences'],
        $cvData['langues'],
        $cvData['urlPhoto'],
        $cvData['couleurTheme'],
        $cvData['statut'],
        $cvData['dateCreation'],
        $cvData['dateMiseAJour'],
        $analysisJsonString // AI analysis
    );
    $cvc->updateCV($id_cv, $cvModel);

    // B. Nouvelle méthode MVC : Sauvegarde dans rapport_ia
    $rapportIA = new RapportIA(
        null,
        (int)$id_cv,
        (int)($decoded['score_ats'] ?? 0),
        json_encode($decoded['points_forts'] ?? []),
        json_encode($decoded['points_faibles'] ?? []),
        json_encode($decoded['missing_skills'] ?? []), // Mapping suggestions/missing skills
        json_encode($decoded['detailed_recommendations'] ?? [])
    );
    $riac->addRapport($rapportIA);
}

// 3. Renvoyer le JSON au frontend
echo json_encode([
    'success' => true,
    'report' => $decoded
]);
