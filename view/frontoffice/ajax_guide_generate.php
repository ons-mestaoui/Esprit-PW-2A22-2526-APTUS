<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/GuideController.php';
require_once __DIR__ . '/../../model/GuideRecrutement.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

$cvId = $_POST['cv_id'] ?? null;
$jobData = $_SESSION['tailor_job_data'] ?? null;

if (!$cvId || !$jobData) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes pour la génération du guide.']);
    exit();
}

try {
    $gc = new GuideController();
    $cvc = new CVC();
    $pdo = config::getConnexion();

    // 1. Get the current CV content (as saved by the user)
    $cv = $cvc->getCVById($cvId);
    if (!$cv) throw new Exception("CV introuvable.");

    // Retrieve Original CV Data from Session for comparison
    $oldCv = $_SESSION['original_cv_data'] ?? $cv;

    // 2. Generate the Recruitment Guide via le contrôleur spécialisé
    $guide = $gc->generateRecruitmentGuide($jobData, $cv, $oldCv);

    if (!$guide) throw new Exception("Erreur lors de la génération du guide.");

    // 3. Save the guide to the database (Backward Compatibility)
    $stmt = $pdo->prepare("UPDATE cv SET tailoring_report = ?, is_tailored = 1 WHERE id_cv = ?");
    $stmt->execute([json_encode($guide), $cvId]);

    // 4. Save to the NEW Guide Table (MVC Architecture)
    // $gc est déjà instancié plus haut
    $newGuide = new GuideRecrutement(
        null,
        (int)$cvId,
        (int)($cv['id_candidat'] ?? 0),
        $cv['titrePoste'] ?? '',
        json_encode($guide)
    );
    $gc->addGuide($newGuide);

    // 4. Clear session data
    unset($_SESSION['tailor_guide']);
    
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    error_log("Guide Generation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
