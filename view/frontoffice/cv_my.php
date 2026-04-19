<?php 
$pageTitle = "Mes CVs"; $pageCSS = "cv_premium.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$id_candidat = $_SESSION['user_id'] ?? null;

$cvc = new CVC();
$tc  = new TemplateC();

// Si pas de session utilisateur, on affiche tous les CVs (mode dév)
$cvList   = $cvc->listCVByCandidat($id_candidat);
$totalCVs = count($cvList);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="file-text" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mes CVs
      </h1>
      <p class="page-header__subtitle">Gérez, modifiez et téléchargez vos CVs générés.</p>
    </div>
    <a href="cv_templates.php" class="btn btn-primary">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Créer un nouveau CV
    </a>
  </div>
</div>

<!-- ═══ Stats Summary ═══ -->
<div class="grid grid-3 gap-6 mb-8">
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Total CVs</div>
      <div class="stat-card__value"><?php echo $totalCVs; ?></div>
    </div>
    <div class="stat-card__icon purple">
      <i data-lucide="file-text" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Statut</div>
      <div class="stat-card__value" style="font-size:var(--fs-sm);"><?php echo $totalCVs > 0 ? 'Actif' : 'Inactif'; ?></div>
    </div>
    <div class="stat-card__icon teal">
      <i data-lucide="check-circle" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Dernière mise à jour</div>
      <div class="stat-card__value" style="font-size:var(--fs-sm);">
        <?php echo $totalCVs > 0 ? date('d/m/Y', strtotime($cvList[0]['dateMiseAJour'])) : 'N/A'; ?>
      </div>
    </div>
    <div class="stat-card__icon blue">
      <i data-lucide="clock" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ CV Cards Grid (cv-builder-v2 dashboard style) ═══ -->
<?php if($totalCVs > 0): ?>
<div class="dashboard-grid">
  <?php foreach ($cvList as $cv): 
      $tpl = $tc->getTemplateById($cv['id_template']);
      $tplNom = strtolower($tpl['nom'] ?? 'standard');
      $tplClass = strtolower(preg_replace('/[^a-z0-9]/i', '-', $tpl['nom'] ?? 'standard'));
      $theme = $cv['couleurTheme'] ?: '#6B34A3';
      $contact = implode(' | ', array_filter([$cv['infoContact'] ?? '']));
      $isManager = str_contains($tplNom, 'manager') || str_contains($tplNom, 'sidebar');
      
      // Parse contact info
      $parts = array_map('trim', explode('|', $cv['infoContact'] ?? ''));
      $email = $parts[0] ?? '';
      $phone = $parts[1] ?? '';
      $location = $parts[2] ?? '';
      $contactStr = implode(' | ', array_filter([$email, $phone, $location]));
  ?>
  
  <div class="dashboard-card" id="resume-<?php echo $cv['id_cv']; ?>">
    
    <!-- Scaled CV Preview (exact same technique as cv-builder-v2) -->
    <div class="dashboard-preview-wrapper" style="--cv-accent: <?php echo htmlspecialchars($theme); ?>;">
      
      <!-- Eye overlay on hover -->
      <div class="overlay-eye" onclick="window.location.href='cv_form.php?cv_id=<?php echo $cv['id_cv']; ?>'">
        <i class="fa-solid fa-eye"></i>
      </div>

      <!-- Scaled-down full CV render via iframe (uses actual template HTML from DB) -->
      <?php
        $previewHtml = $tpl['structureHtml'] ?? '';
        $isFullHtml = stripos($previewHtml, '<!DOCTYPE') !== false || stripos($previewHtml, '<html') !== false;
        if (!$isFullHtml) {
            $previewHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></head><body style="margin:0;padding:0;">' . $previewHtml . '</body></html>';
        }
        // Inject CSS to hide scrollbars inside the iframe
        $hideScrollbarCSS = '<style>html,body{overflow:hidden!important;scrollbar-width:none!important;-ms-overflow-style:none!important;}::-webkit-scrollbar{display:none!important;}</style>';
        if (stripos($previewHtml, '</head>') !== false) {
            $previewHtml = str_ireplace('</head>', $hideScrollbarCSS . '</head>', $previewHtml);
        } else {
            $previewHtml = $hideScrollbarCSS . $previewHtml;
        }
      ?>
      <div class="dashboard-scaled-cv" style="--cv-accent: <?php echo htmlspecialchars($theme); ?>;">
        <iframe 
          class="cv-preview-iframe" 
          data-cv-id="<?php echo $cv['id_cv']; ?>"
          data-cv='<?php echo htmlspecialchars(json_encode([
            'nomComplet' => $cv['nomComplet'],
            'titrePoste' => $cv['titrePoste'],
            'infoContact' => $contactStr,
            'resume' => $cv['resume'],
            'experience' => $cv['experience'],
            'competences' => str_replace(',', ' • ', $cv['competences']),
            'formation' => $cv['formation'],
            'langues' => $cv['langues'],
            'urlPhoto' => $cv['urlPhoto'] ?? ''
          ]), ENT_QUOTES); ?>'
          srcdoc="<?php echo htmlspecialchars($previewHtml); ?>"
          style="width:794px; height:1123px; transform-origin:top left; border:none; pointer-events:none;"
        ></iframe>
      </div>
    </div>

    <!-- Card Info -->
    <div class="dashboard-info">
      <h3><?php echo htmlspecialchars($cv['nomComplet'] ?: 'CV sans titre'); ?></h3>
      <p>
        Poste: <?php echo htmlspecialchars($cv['titrePoste']); ?><br>
        Modifié le <?php echo date('d M Y', strtotime($cv['dateMiseAJour'])); ?>
      </p>
      <div class="dashboard-actions">
        <div>
          <a href="cv_form.php?cv_id=<?php echo $cv['id_cv']; ?>" class="btn-primary-cv" style="text-decoration:none; font-size:0.8rem; padding:0.5rem 1rem;">
            <i data-lucide="edit-3" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> Éditer
          </a>
        </div>
        <div style="display:flex; gap:0.5rem;">
          <button class="btn-secondary-cv" style="font-size:0.8rem; padding:0.5rem 1rem;" onclick="generatePDF(<?php echo $cv['id_cv']; ?>)">
            <i data-lucide="download" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> PDF
          </button>
          <button class="btn-secondary-cv" style="font-size:0.8rem; padding:0.5rem 0.75rem; color:var(--accent-tertiary);" onclick="deleteCV(<?php echo $cv['id_cv']; ?>)">
            <i data-lucide="trash-2" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center; padding: 60px 20px;">
  <div style="margin-bottom:20px; opacity:0.3;">
    <i data-lucide="file-plus" style="width:64px;height:64px;"></i>
  </div>
  <h3>Aucun CV créé</h3>
  <p style="color:var(--text-secondary);">Commencez par choisir un template et créez votre premier CV professionnel.</p>
  <a href="cv_templates.php" class="btn btn-primary" style="margin-top:20px;">
    <i data-lucide="plus" style="width:18px;height:18px;"></i>
    Créer mon premier CV
  </a>
</div>
<?php endif; ?>

<!-- Hidden Print Frame -->
<iframe id="print-frame" style="display:none;"></iframe>

<script>
// Set active nav
document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('nav-cv-my');
    if(link) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        link.classList.add('active');
    }
    
    // Scale and populate iframe previews
    document.querySelectorAll('.cv-preview-iframe').forEach(iframe => {
        iframe.addEventListener('load', function() {
            // Scale iframe to fit container
            const container = iframe.closest('.dashboard-scaled-cv');
            if (container) {
                const cw = container.clientWidth;
                const ch = container.clientHeight;
                const scale = Math.min(cw / 794, ch / 1123, 1);
                iframe.style.transform = `scale(${scale})`;
            }
            
            // Inject CV data into iframe
            const cvData = JSON.parse(iframe.dataset.cv || '{}');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc) return;
            
            function setText(selectors, value) {
                for (const sel of selectors) {
                    try {
                        const el = doc.querySelector(sel);
                        if (el) { el.innerText = value; return true; }
                    } catch(e) {}
                }
                return false;
            }
            
            function findByTitle(keywords, clearSiblings = false) {
                const titles = doc.querySelectorAll('.side-title, .section-title, .main-title, h3, h4, .cv-section-title');
                for (const t of titles) {
                    const txt = t.textContent.toLowerCase();
                    for (const kw of keywords) {
                        if (txt.includes(kw)) {
                            let next = t.nextElementSibling;
                            if (next) {
                                if (clearSiblings) {
                                    let sibling = next.nextElementSibling;
                                    while (sibling && !(sibling.tagName === 'H3' || sibling.tagName === 'H4' || sibling.classList.contains('main-title') || sibling.classList.contains('section-title'))) {
                                        const toRemove = sibling;
                                        sibling = sibling.nextElementSibling;
                                        toRemove.remove();
                                    }
                                }
                                return next;
                            }
                        }
                    }
                }
                return null;
            }
            
            // Name
            if (cvData.nomComplet) {
                setText(['#preview-nomComplet', '.sidebar h1', '.header-info h1', '.header-text h1', '.cv-name', 'h1[contenteditable]', 'h1'], cvData.nomComplet);
            }
            
            // Title
            if (cvData.titrePoste) {
                setText(['#preview-titrePoste', '.sidebar h2', '.header-info h2', '.header-text h2', '.cv-title', 'h2[contenteditable]'], cvData.titrePoste);
            }
            
            // Contact
            if (cvData.infoContact) {
                if (!setText(['#preview-infoContact', '.contact-info', '.cv-contact'], cvData.infoContact)) {
                    // Try individual contact items
                    const items = doc.querySelectorAll('.contact-item');
                    const parts = cvData.infoContact.split('|').map(s => s.trim());
                    items.forEach((item, i) => { if (parts[i]) item.innerText = parts[i]; });
                }
            }
            
            // Resume
            if (cvData.resume) {
                if (!setText(['#preview-resume', '.summary-text', '.summary'], cvData.resume)) {
                    const el = findByTitle(['résumé', 'resume', 'profil', 'professionnel']);
                    if (el) el.innerText = cvData.resume;
                }
            }
            
            // Experience  
            if (cvData.experience) {
                if (!setText(['#preview-experience'], cvData.experience)) {
                    const el = findByTitle(['expérience', 'experience'], true);
                    if (el) el.innerText = cvData.experience;
                }
            }
            
            // Competences
            if (cvData.competences) {
                if (!setText(['#preview-competences'], cvData.competences)) {
                    const el = findByTitle(['compétence', 'competence', 'skills']);
                    if (el) el.innerText = cvData.competences;
                }
            }
            
            // Formation
            if (cvData.formation) {
                if (!setText(['#preview-formation'], cvData.formation)) {
                    const el = findByTitle(['formation', 'education', 'études']);
                    if (el) el.innerText = cvData.formation;
                }
            }
            
            // Languages
            if (cvData.langues) {
                if (!setText(['#preview-langues'], cvData.langues)) {
                    const el = findByTitle(['langue', 'language']);
                    if (el) el.innerText = cvData.langues;
                }
            }
            
            // Photo
            if (cvData.urlPhoto) {
                const photos = doc.querySelectorAll('#preview-photo, #profile-pic, .cv-photo, .header-photo img');
                photos.forEach(img => {
                    if (img.tagName === 'IMG') { img.src = cvData.urlPhoto; img.style.display = 'block'; }
                });
                const photoText = doc.querySelector('#photo-text, .photo-text');
                if (photoText) photoText.style.display = 'none';
                // For templates with header-photo div but no img
                const headerPhoto = doc.querySelector('.header-photo');
                if (headerPhoto && !headerPhoto.querySelector('img')) {
                    headerPhoto.innerHTML = '<img src="' + cvData.urlPhoto + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
                }
            }
        });
    });
});

function generatePDF(cvId) {
    const frame = document.getElementById('print-frame');
    frame.src = 'cv_print.php?id=' + cvId;
    frame.onload = function() {
        frame.contentWindow.print();
    };
}

async function deleteCV(cvId) {
    const card = document.querySelector(`#resume-${cvId}`);
    const name = card ? card.querySelector('h3').textContent : 'ce CV';
    
    const ok = await aptusConfirm(
        'Supprimer le CV ?', 
        `Êtes-vous sûr de vouloir supprimer le CV de "${name}" ? Cette action est irréversible.`
    );
    
    if(ok) {
        window.location.href = 'cv_delete.php?id=' + cvId;
    }
}
</script>
