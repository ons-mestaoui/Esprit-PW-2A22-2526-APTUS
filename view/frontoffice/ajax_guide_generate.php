<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AIController.php';
require_once __DIR__ . '/../../controller/CVC.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

$cvId = $_POST['cv_id'] ?? null;
$jobData = $_SESSION['tailor_job_data'] ?? null;

if (!$cvId || !$jobData) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes pour la génération du guide.']);
    exit();
}

try {
    $ai = new AIController();
    $cvc = new CVC();
    $pdo = config::getConnexion();

    // 1. Get the current CV content (as saved by the user)
    $cv = $cvc->getCVById($cvId);
    if (!$cv) throw new Exception("CV introuvable.");

    // Retrieve Original CV Data from Session for comparison
    $oldCv = $_SESSION['original_cv_data'] ?? $cv;

    // 2. Generate the Recruitment Guide via Gemini
    // We pass the job data, the original CV data, and the current CV data
    $guide = $ai->generateRecruitmentGuide($jobData, $cv, $oldCv);

    if (!$guide) throw new Exception("Erreur lors de la génération du guide.");

    // 3. Save the guide to the database
    $stmt = $pdo->prepare("UPDATE cv SET tailoring_report = ?, is_tailored = 1 WHERE id_cv = ?");
    $stmt->execute([json_encode($guide), $cvId]);

    // 4. Clear session data
    unset($_SESSION['tailor_guide']);
    
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    error_log("Guide Generation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
