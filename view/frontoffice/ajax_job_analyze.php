<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/GuideController.php';
require_once __DIR__ . '/../../controller/CVC.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();
set_time_limit(120); // Laisser le temps au scraping et à l'IA

$cvId = $_POST['cv_id'] ?? null;
$url  = $_POST['url'] ?? '';

if (!$cvId || !$url) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes (CV ID ou URL).']);
    exit();
}

try {
    $gc = new GuideController();
    $cvc = new CVC();
    $pdo = config::getConnexion();

    // 1. Get Job Content
    $jobText = "";
    $jobData = [];

    // Check if it's an internal link (e.g., contains ?id= or /job/)
    if (preg_match('/id=(\d+)/', $url, $matches)) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$matches[1]]);
        $job = $stmt->fetch();
        if ($job) {
            $jobText = $job['description'];
            $internalJob = true;
            error_log("Tailor Step 1: Internal Job Found");
        }
    }

    if (empty($jobText)) {
        error_log("Tailor Step 1: Scraping URL...");
        $jobText = $gc->scrapeUrl($url);
        error_log("Tailor Step 1: Scraped Length: " . strlen($jobText));
        
        error_log("Tailor Step 1.5: Analyzing Job with Gemini...");
        $jobData = $gc->analyzeJobPosting($jobText);
    } else {
        error_log("Tailor Step 1.5: Refining Internal Job with Gemini...");
        $jobData = $gc->analyzeJobPosting($jobText);
    }

    if (!$jobData || isset($jobData['error'])) {
        throw new Exception("Impossible d'analyser l'offre d'emploi : " . ($jobData['error'] ?? 'Inconnu'));
    }

    // 2. Get Current CV Data (Before optimization)
    error_log("Tailor Step 2: Fetching Original CV Data...");
    $cv = $cvc->getCVById($cvId);
    if (!$cv) throw new Exception("CV introuvable.");

    // Store Original Data for Guide Comparison
    $_SESSION['original_cv_data'] = $cv;

    // 3. Tailor CV (Groq)
    error_log("Tailor Step 3: Tailoring CV with Groq...");
    $tailoredData = $gc->tailorCV($cv, $jobData);
    error_log("Tailor Step 3: Done");

    // 4. Store in Session for cv_form.php
    $_SESSION['tailor_job_data'] = $jobData;
    $_SESSION['tailor_cv_data']  = $tailoredData;
    $_SESSION['tailor_job_url']  = $url;
    $_SESSION['tailor_cv_id']    = $cvId;

    error_log("Tailor Complete Success");
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    $errorMsg = $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    error_log("Tailor CRITICAL ERROR: " . $errorMsg);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
