<?php
require_once 'config.php';

try {
    $db = config::getConnexion();
    
    // Add is_tailored
    $db->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS is_tailored INT DEFAULT 0");
    echo "Column is_tailored added or exists.\n";
    
    // Add target_job_url
    $db->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS target_job_url TEXT");
    echo "Column target_job_url added or exists.\n";
    
    // Add tailoring_report
    $db->exec("ALTER TABLE cv ADD COLUMN IF NOT EXISTS tailoring_report LONGTEXT");
    echo "Column tailoring_report added or exists.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
