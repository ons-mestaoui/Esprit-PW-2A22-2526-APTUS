<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Template.php';
require_once __DIR__ . '/../controller/TemplateC.php';

$tc = new TemplateC();
$dbTemplates = $tc->listeTemplates();

echo "Starting Universal Template Fix...\n";

foreach ($dbTemplates as $tData) {
    $id = $tData['id_template'];
    $html = $tData['structureHtml'];
    $name = $tData['nom'];

    echo "Processing: $name (ID: $id)...\n";

    // 1. CSS Variables Injection
    $colorVarCSS = "
    :root {
        --cv-accent: #6E46AE; /* Default accent */
        --primary: var(--cv-accent);
    }";

    if (stripos($html, '<style>') !== false) {
        // Inject at start of existing style
        $html = str_ireplace('<style>', "<style>\n$colorVarCSS", $html);
    } else {
        // Create style block if missing
        $html = str_ireplace('<head>', "<head>\n<style>$colorVarCSS</style>", $html);
    }

    // 2. Replace hardcoded colors with var(--cv-accent)
    // We target common primary colors used in the app
    $colorsToReplace = ['#6E46AE', '#2563EB', '#6B34A3', '#4f46e5'];
    foreach ($colorsToReplace as $c) {
        $html = str_ireplace($c, 'var(--cv-accent)', $html);
    }

    // 3. Ensure Smart Zones (Basic Regex check/fix)
    // Note: A more robust DOMDocument approach is better but regex is faster for simple mappings
    $mappings = [
        '#preview-nomComplet' => 'data-cv-zone="nomComplet"',
        '#preview-titrePoste' => 'data-cv-zone="titrePoste"',
        '#preview-infoContact' => 'data-cv-zone="contact"',
        '#preview-resume' => 'data-cv-zone="resume"',
        '#preview-experience' => 'data-cv-zone="experience"',
        '#preview-competences' => 'data-cv-zone="competences"',
        '#preview-formation' => 'data-cv-zone="formation"',
        '#preview-langues' => 'data-cv-zone="langues"'
    ];

    foreach ($mappings as $id_sel => $zone) {
        $id_pure = ltrim($id_sel, '#');
        // If element has ID but no zone, add zone
        if (stripos($html, $id_sel) !== false && stripos($html, $zone) === false) {
            $html = str_ireplace('id="' . $id_pure . '"', 'id="' . $id_pure . '" ' . $zone, $html);
        }
    }

    // 4. Save back to DB
    $sql = "UPDATE templates SET structureHtml = ? WHERE id_template = ?";
    try {
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([$html, $id]);
        echo "Successfully updated $name.\n";
    } catch (Exception $e) {
        echo "Error updating $name: " . $e->getMessage() . "\n";
    }
}

echo "Done. All templates are now compatible with Color Picker and Smart Placeholders.\n";
?>
