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

// Handle basic info formatting
$parts = array_map('trim', explode('|', $cv['infoContact'] ?? ''));
$email = $parts[0] ?? '';
$phone = $parts[1] ?? '';
$location = $parts[2] ?? '';
$contactStr = implode(' | ', array_filter([$email, $phone, $location]));

// JSON payload for the CV data
$cvPayload = json_encode([
    'nomComplet' => $cv['nomComplet'],
    'titrePoste' => $cv['titrePoste'],
    'infoContact' => $contactStr,
    'resume' => $cv['resume'],
    'experience' => $cv['experience'],
    'competences' => str_replace(',', ' • ', $cv['competences']),
    'formation' => $cv['formation'],
    'langues' => $cv['langues'],
    'urlPhoto' => $cv['urlPhoto'] ?? ''
]);
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
        #cv-preview-content { --cv-accent: <?php echo htmlspecialchars($cv['couleurTheme']); ?>; }
    </style>
</head>
<body>
    <div id="cv-preview-content">
        <?php echo $html; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const d = <?php echo $cvPayload; ?>;
            const setVal = (sel, val, isHtml = false) => { 
                if(!val) return;
                document.querySelectorAll(sel).forEach(e => {
                    if (isHtml) e.innerHTML = val; else e.innerText = val;
                });
            };
            
            // Standard dynamic injection logic
            setVal('.cv-name, #preview-nomComplet, h1', d.nomComplet);
            setVal('.cv-title, #preview-titrePoste, h2', d.titrePoste);
            setVal('.summary-text, #preview-resume, .summary, .cv-summary', d.resume, true);
            setVal('#preview-experience, .cv-exp, .experience-list, .cv-experience', d.experience, true);
            setVal('#preview-competences, .cv-skills, .skills-list, .cv-competences', d.competences, true);
            setVal('#preview-langues, .cv-languages, .languages-list, .cv-langues', d.langues, true);
            setVal('#preview-formation, .cv-edu, .education-list, .cv-formation', d.formation, true);
            
            if (d.infoContact) {
                const clean = d.infoContact.split('|').map(s => s.trim()).join('<br>');
                setVal('.contact-info, #preview-infoContact, .cv-contact, .contact-details', clean, true);
            }
            if (d.urlPhoto) { 
                const pi = document.querySelectorAll('#preview-photo, .cv-photo img, .profile-img'); 
                pi.forEach(i => { i.src = d.urlPhoto; i.style.display = 'block'; }); 
                const pt = document.querySelectorAll('#photo-text, .photo-text');
                pt.forEach(i => i.style.display = 'none');
                const hp = document.querySelector('.header-photo');
                if (hp && !hp.querySelector('img')) {
                    hp.innerHTML = '<img src=\"' + d.urlPhoto + '\" style=\"width:100%;height:100%;object-fit:cover;border-radius:8px;\">';
                }
            }
            
            // Print automatically after injection is complete and images load
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
