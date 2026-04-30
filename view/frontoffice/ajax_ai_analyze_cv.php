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

require_once __DIR__ . '/../../controller/AIController.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../model/CV.php';

// 1. Appeler l'IA via Groq Cloud (Ultra Rapide)
$ai = new AIController();
$analysisJsonString = $ai->analyzeCV($cvText);

// Vérifier si c'est du JSON valide
$decoded = json_decode($analysisJsonString, true);
if (!$decoded || !isset($decoded['score_ats'])) {
    echo json_encode(['success' => false, 'error' => 'L\'IA a renvoyé un format invalide.']);
    exit;
}

// 2. Sauvegarder dans la base de données
$cvc = new CVC();
$cvData = $cvc->getCVById($id_cv);

if ($cvData) {
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
}

// 3. Renvoyer le JSON au frontend
echo json_encode([
    'success' => true,
    'report' => $decoded
]);
