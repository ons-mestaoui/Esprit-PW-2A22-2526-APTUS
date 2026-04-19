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

      $aiData = null;
      if (!empty($cv['ai_analysis'])) {
          $aiData = json_decode($cv['ai_analysis'], true);
      }
  ?>
  
  <div class="dashboard-card" id="resume-<?php echo $cv['id_cv']; ?>">
    
    <!-- Scaled CV Preview (exact same technique as cv-builder-v2) -->
    <div class="dashboard-preview-wrapper" style="--cv-accent: <?php echo htmlspecialchars($theme); ?>; position: relative;">
      
      <?php if($aiData && isset($aiData['score_ats'])): ?>
      <div style="position:absolute; top:10px; left:10px; z-index:10; background:rgba(0,0,0,0.8); color:#fff; padding:6px 14px; border-radius:20px; font-weight:bold; font-size:0.8rem; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 10px rgba(0,0,0,0.2); transition:transform 0.2s;" onclick="showAIAudit(<?php echo htmlspecialchars(json_encode($aiData), ENT_QUOTES, 'UTF-8'); ?>)" class="ats-badge-hover">
          <i data-lucide="bar-chart-2" style="width:14px;"></i> Score ATS : <?php echo $aiData['score_ats']; ?>%
      </div>
      <?php endif; ?>

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
        <div style="display:flex; gap:0.5rem; align-items:center;">
          <a href="cv_form.php?cv_id=<?php echo $cv['id_cv']; ?>" class="btn-primary-cv" style="text-decoration:none; font-size:0.8rem; padding:0.5rem 1rem;">
            <i data-lucide="edit-3" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> Éditer
          </a>
          
          <?php if($aiData && isset($aiData['score_ats'])): ?>
            <button class="btn-ai-premium" style="font-size:0.75rem; padding:0.4rem 0.8rem; border-radius:8px;" onclick="showAIAudit(<?php echo htmlspecialchars(json_encode($aiData), ENT_QUOTES, 'UTF-8'); ?>)">
              <i data-lucide="bar-chart-2" style="width:14px;height:14px;"></i> Audit IA
            </button>
          <?php endif; ?>
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

<!-- VIEW IA AUDIT MODAL -->
<div id="view-audit-modal" class="aptus-modal-overlay" onclick="if(event.target===this) this.classList.remove('active');">
    <div class="aptus-modal-content" style="max-width: 600px; text-align:left;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <h2 style="font-size:1.6rem; display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i data-lucide="bar-chart-2" style="color:var(--accent-primary);"></i> Audit IA du CV</h2>
                <p style="color:var(--text-secondary); margin:0;">Note générée par Mistral (Optimisation ATS)</p>
            </div>
            <div class="ats-score-circle" id="view-score-circle">
                <span id="view-score-value">0</span>%
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; max-height:400px; overflow-y:auto; padding-right:5px;">
            <div class="audit-card strengths">
                <h4 style="color:#10b981; display:flex; align-items:center; gap:6px; margin-bottom:10px;"><i data-lucide="check-circle" style="width:16px;"></i> Points Forts</h4>
                <ul id="view-strengths" style="margin:0; padding-left:20px; color:var(--text-secondary); font-size:0.9rem; line-height:1.5;"></ul>
            </div>
            <div class="audit-card weaknesses">
                <h4 style="color:#f59e0b; display:flex; align-items:center; gap:6px; margin-bottom:10px;"><i data-lucide="alert-circle" style="width:16px;"></i> À Améliorer</h4>
                <ul id="view-weaknesses" style="margin:0; padding-left:20px; color:var(--text-secondary); font-size:0.9rem; line-height:1.5;"></ul>
            </div>
        </div>

        <div style="text-align:right; margin-top:20px;">
            <button class="btn-modal-cancel" onclick="document.getElementById('view-audit-modal').classList.remove('active');" style="padding:10px 24px;">Fermer</button>
        </div>
    </div>
</div>

<style>
.ats-badge-hover:hover { transform: scale(1.05); background: var(--accent-primary) !important; }
</style>

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

function animateATSScore(targetScore, elementId) {
    let currentScore = 0;
    const duration = 1500; 
    const fps = 60;
    const increment = targetScore / (duration / (1000/fps));
    const el = document.getElementById(elementId);
    
    if (targetScore <= 0) { el.textContent = 0; return; }

    const interval = setInterval(() => {
        currentScore += increment;
        if (currentScore >= targetScore) {
            currentScore = targetScore;
            clearInterval(interval);
        }
        el.textContent = Math.floor(currentScore);
    }, 1000/fps);
}

function showAIAudit(r) {
    const modal = document.getElementById('view-audit-modal');
    modal.classList.add('active');
    
    const circle = document.getElementById('view-score-circle');
    
    // Animation dynamique du score à chaque ouverture
    document.getElementById('view-score-value').textContent = "0";
    animateATSScore(r.score_ats || 0, 'view-score-value');
            
    circle.className = 'ats-score-circle'; // reset
    if (r.score_ats >= 80) circle.classList.add('green');
    else if (r.score_ats >= 50) circle.classList.add('orange');
    else circle.classList.add('red');

    const sList = document.getElementById('view-strengths');
    sList.innerHTML = '';
    (r.points_forts || []).forEach(pt => {
        const li = document.createElement('li'); li.textContent = pt; sList.appendChild(li);
    });

    const wList = document.getElementById('view-weaknesses');
    wList.innerHTML = '';
    (r.points_faibles || []).forEach(pt => {
        const li = document.createElement('li'); li.textContent = pt; wList.appendChild(li);
    });
}
</script>
