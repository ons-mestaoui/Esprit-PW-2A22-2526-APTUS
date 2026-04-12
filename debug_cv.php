<?php
// Test script: simulate the save_cv.php call directly
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Simulate posted JSON data
$testData = [
    'id_cv'        => null,
    'id_template'  => 1,
    'nomComplet'   => 'Test User',
    'titrePoste'   => 'Dev',
    'email'        => 't@t.com',
    'telephone'    => '123',
    'adresse'      => 'Tunis',
    'resume'       => 'Développeur passionné',
    'experience'   => '',
    'competences'  => '',
    'formation'    => '',
    'langues'      => '',
    'urlPhoto'     => '',
    'couleurTheme' => '#2563eb'
];

// Inject mock session
$_SESSION = ['user_id' => null];

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/CV.php';
require_once __DIR__ . '/controller/CVC.php';

$db = config::getConnexion();

// Check if cv table exists
$result = $db->query("SHOW TABLES LIKE 'cv'")->fetch();
echo "CV table: " . ($result ? "EXISTS" : "MISSING") . "\n";

// Check if candidat table has rows
$row = $db->query("SELECT COUNT(*) as cnt FROM candidat")->fetch();
echo "Candidat rows: " . $row['cnt'] . "\n";

// Check foreign keys on cv
$fks = $db->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='aptus' AND TABLE_NAME='cv' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
echo "CV Foreign Keys: " . count($fks) . "\n";
foreach($fks as $fk) {
    echo " - " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "\n";
}

// Try direct INSERT
try {
    $stmt = $db->prepare("INSERT INTO cv (id_candidat, id_template, nomDocument, nomComplet, titrePoste, resume, infoContact, statut, dateCreation, dateMiseAJour) VALUES (1, 1, 'Test', 'Test User', 'Dev', '', '', 'en_attente', NOW(), NOW())");
    $stmt->execute();
    $newId = $db->lastInsertId();
    echo "INSERT SUCCESS: id=" . $newId . "\n";
    // Clean up
    $db->exec("DELETE FROM cv WHERE id_cv=" . $newId);
} catch(Exception $e) {
    echo "INSERT FAILED: " . $e->getMessage() . "\n";
}
