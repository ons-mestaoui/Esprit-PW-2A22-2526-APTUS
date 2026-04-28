<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controller/AIController.php';

$ai = new AIController();
$cvText = "Développeur web avec 3 ans d'expérience. J'aime le PHP et le JS. J'ai travaillé chez Google pendant 1 an.";
echo "Calling AI...\n";
$start = microtime(true);
$response = $ai->analyzeCV($cvText);
$end = microtime(true);
echo "Time: " . ($end - $start) . " seconds\n";
echo "Response: " . $response . "\n";
?>
