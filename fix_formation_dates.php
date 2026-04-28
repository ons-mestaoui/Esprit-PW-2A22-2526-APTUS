<?php
// Fix : Mettre à jour la date de la formation "nos elil w nos" à une date future
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/model/Database.php';
$db = Database::getInstance()->getConnection();

// Mettre à jour toutes les formations dont la date est passée
$stmt = $db->prepare("UPDATE formation SET date_formation = '2026-12-31 23:59:59' WHERE date_formation < NOW()");
$stmt->execute();
$affected = $stmt->rowCount();

echo "<h2>✅ Fix appliqué !</h2>";
echo "<p><strong>$affected</strong> formation(s) mise(s) à jour avec une date future (31 Déc 2026).</p>";
echo "<p><a href='view/frontoffice/formations_catalog.php'>→ Aller au catalogue</a></p>";
?>
