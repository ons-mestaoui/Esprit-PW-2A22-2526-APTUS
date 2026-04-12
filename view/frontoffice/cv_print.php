<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

$cvc = new CVC();
$tc = new TemplateC();
$cv = $cvc->getCVById($id);
if (!$cv) die("CV non trouvé");

$tpl = $tc->getTemplateById($cv['id_template']);
if (!$tpl) die("Template non trouvé");

// Prepare the standard IDs for the template
// Since the template code we provided uses specific IDs, we'll replace placeholders manually in the HTML
$html = $tpl['structureHtml'];

$fields = [
    'preview-nomComplet' => $cv['nomComplet'],
    'preview-titrePoste' => $cv['titrePoste'],
    'preview-infoContact' => $cv['infoContact'],
    'preview-resume' => $cv['resume'],
    'preview-experience' => $cv['experience'],
    'preview-competences' => $cv['competences'],
    'preview-formation' => $cv['formation'],
    'preview-langues' => $cv['langues']
];

// Simple DOM replacement via string for the print view
foreach($fields as $idAttr => $val) {
    // We search for the ID in the HTML and inject the value. 
    // This is a bit rough but works for standard templates.
    // A better way is using DOMDocument but might be overkill for this task.
    $valHtml = nl2br(htmlspecialchars($val));
    $html = preg_replace('/id="' . $idAttr . '"([^>]*)>.*?<\/div>/is', 'id="' . $idAttr . '"$1>' . $valHtml . '</div>', $html);
}

// Handle photo
if (!empty($cv['urlPhoto'])) {
    $html = preg_replace('/id="preview-photo"([^>]*)src="[^"]*"/', 'id="preview-photo"$1 src="' . $cv['urlPhoto'] . '" style="display:block;"', $html);
} else {
    $html = preg_replace('/id="preview-photo"/', 'id="preview-photo" style="display:none;"', $html);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression CV — <?php echo htmlspecialchars($cv['nomComplet']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #fff; }
        @media print {
            @page { margin: 0; size: A4; }
            body { -webkit-print-color-adjust: exact; }
        }
        /* Injection of dynamic color */
        #cv-preview-content { --cv-accent: <?php echo $cv['couleurTheme']; ?>; }
    </style>
</head>
<body onload="window.print()">
    <div id="cv-preview-content">
        <?php echo $html; ?>
    </div>
</body>
</html>
