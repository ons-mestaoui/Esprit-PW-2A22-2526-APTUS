<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_cv = $input['id_cv'] ?? null;
$cvText = $input['cvText'] ?? '';

if (!$id_cv || empty(trim($cvText))) {
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides (id ou texte absent).']);
    exit;
}

require_once __DIR__ . '/../../controller/AIController.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../model/CV.php';

// 1. Appeler l'IA (Mistral explicitement configuré)
$ai = new AIController();
$analysisJsonString = $ai->analyzeCV($cvText);

// Vérifier si c'est du JSON valide
$decoded = json_decode($analysisJsonString, true);
if (!$decoded || !isset($decoded['score_ats'])) {
    echo json_encode(['success' => false, 'error' => 'L\'IA a renvoyé un format invalide. Réponse brute : ' . $analysisJsonString]);
    exit;
}

// 2. Sauvegarder dans la DB
$cvc = new CVC();
$cvData = $cvc->getCVById($id_cv);

if ($cvData) {
    // Reconstruire l'objet CV
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
        $analysisJsonString
    );
    
    // Mettre à jour (foreign keys pourraient poser problème, on désactive)
    $db = config::getConnexion();
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    $cvc->updateCV($id_cv, $cvModel);
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
}

// 3. Renvoyer le JSON au frontend
echo json_encode(['success' => true, 'report' => $decoded]);
