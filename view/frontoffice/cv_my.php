<?php 
$pageTitle = "Mes CVs"; 
$pageCSS = "cv_premium.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$id_candidat = $_SESSION['user_id'] ?? null;

$cvc = new CVC();
$tc  = new TemplateC();

$cvList   = $cvc->listCVByCandidat($id_candidat);
$totalCVs = count($cvList);

// Calculate custom stats
$readyCount = 0;
$lastEditDate = null;

foreach ($cvList as $cv) {
    if (!$lastEditDate || strtotime($cv['dateMiseAJour']) > strtotime($lastEditDate)) {
        $lastEditDate = $cv['dateMiseAJour'];
    }
    
    if ($cv['ai_analysis']) {
        $ai = json_decode($cv['ai_analysis'], true);
        if (isset($ai['score_ats']) && $ai['score_ats'] >= 80) {
            $readyCount++;
        }
    }
}

$formattedLastEdit = $lastEditDate ? date('d M', strtotime($lastEditDate)) : '—';
if ($lastEditDate && date('Y-m-d') === date('Y-m-d', strtotime($lastEditDate))) {
    $formattedLastEdit = "Aujourd'hui";
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
/* ── COMPACT APTUS THEME ── */
.dashboard-wrap {
    padding: 3rem 0;
    max-width: 1300px;
    margin: 0 auto;
}

.dashboard-header-aptus {
    margin-bottom: 4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 850;
    color: var(--text-primary);
    letter-spacing: -1px;
}

.dashboard-subtitle {
    color: var(--text-secondary);
    font-size: 1rem;
    margin-top: 5px;
}

/* ── COMPACT GRID ── */
.cv-compact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 2.5rem;
}

.cv-miniature-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 12px;
    transition: all 0.3s ease;
    position: relative;
    cursor: default;
    box-shadow: var(--shadow-sm);
}

.cv-miniature-card:hover {
    border-color: var(--accent-primary);
    background: var(--bg-card-hover);
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.cv-miniature__preview {
    width: 100%;
    aspect-ratio: 1 / 1.414; /* A4 Ratio */
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.cv-miniature__iframe {
    width: 794px;
    height: 1123px;
    border: none;
    transform-origin: top left;
    pointer-events: none;
}

.cv-miniature__overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    opacity: 0;
    z-index: 2;
}

.cv-miniature-card:hover .cv-miniature__overlay {
    background: rgba(15, 23, 42, 0.4);
    opacity: 1;
}

.btn-aptus-eye {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    transform: scale(0.8);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.cv-miniature-card:hover .btn-aptus-eye {
    transform: scale(1);
}

.cv-miniature__info {
    padding: 15px 5px 5px 5px;
}

.cv-miniature__name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cv-miniature__role {
    font-size: 0.85rem;
    color: var(--text-secondary);
    display: block;
    margin-top: 2px;
}

.cv-miniature__actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color-light);
}

.cv-action-group {
    display: flex;
    gap: 8px;
}

.btn-action-small {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
}

/* Edit Hover */
.btn-action-small.edit:hover {
    background: var(--accent-primary);
    color: #fff;
    border-color: var(--accent-primary);
    box-shadow: 0 5px 15px rgba(107, 52, 163, 0.3);
}

/* Print Hover */
.btn-action-small.print:hover {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

/* AI Button - GOLD BY DEFAULT */
.btn-action-small.ai {
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.2);
}

.btn-action-small.ai:hover {
    background: #f59e0b;
    color: #fff;
    border-color: #f59e0b;
    box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
}

/* Danger (Delete) - RED BY DEFAULT */
.btn-action-small.danger {
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.2);
    background: rgba(239, 68, 68, 0.05);
}

.btn-action-small.danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: #fff;
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

/* ── TOTAL COMPLETE MODAL ── */
.aptus-total-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: var(--bg-overlay);
    backdrop-filter: blur(15px);
    z-index: 10000;
    display: none;
    opacity: 0;
    transition: all 0.4s ease;
    flex-direction: column;
}

.aptus-total-modal.active {
    display: flex;
    opacity: 1;
}

.total-modal-header {
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--text-primary);
}

.total-modal-body {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    padding: 2rem;
}

.total-modal-cv-container {
    height: 100%;
    aspect-ratio: 1 / 1.414;
    background: white;
    box-shadow: var(--shadow-2xl);
    position: relative;
}

.total-modal-iframe {
    width: 794px;
    height: 1123px;
    border: none;
    transform-origin: top left;
}

.total-modal-footer {
    padding: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.btn-aptus-close-large {
    background: var(--bg-secondary);
    border: none; color: var(--text-primary);
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
}
.btn-aptus-close-large:hover { background: #ef4444; color: #fff; }

/* ── STATS ROW ── */
.stats-row-aptus {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-box-small {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    padding: 1.2rem;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
}

.stat-box-small .label { font-size: 0.75rem; color: var(--text-tertiary); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
.stat-box-small .val { font-size: 1.8rem; font-weight: 850; color: var(--text-primary); display: block; margin-top: 5px; }

</style>

<div class="dashboard-wrap">
    
    <div class="dashboard-header-aptus">
        <div>
            <h1 class="dashboard-title">Mes CVs créés</h1>
            <p class="dashboard-subtitle">Gérez vos CVs optimisés avec le style emblématique d'Aptus.</p>
        </div>
        <a href="cv_templates.php" class="btn-aptus-primary" style="background: var(--accent-primary); color: #fff; padding: 12px 25px; border-radius: 14px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="plus"></i> Créer
        </a>
    </div>

    <!-- STATS -->
    <div class="stats-row-aptus">
        <div class="stat-box-small" title="Nombre total de déclinaisons de CV présentes dans votre portfolio.">
            <span class="label">Total des documents</span>
            <span class="val"><?php echo $totalCVs; ?> <small style="font-size: 0.9rem; color: var(--text-tertiary); font-weight: 500;">Versions</small></span>
        </div>
        <div class="stat-box-small" title="Nombre de CV ayant obtenu un score d'optimisation IA supérieur à 80%.">
            <span class="label">Prêt à l'envoi</span>
            <span class="val" style="color: #10b981;"><?php echo $readyCount; ?> <small style="font-size: 0.9rem; color: var(--text-tertiary); font-weight: 500;">CVs Validés</small></span>
        </div>
        <div class="stat-box-small" title="Date de la modification la plus récente effectuée sur l'un de vos documents.">
            <span class="label">Dernière Édition</span>
            <span class="val" style="color: var(--accent-primary);"><?php echo $formattedLastEdit; ?></span>
        </div>
    </div>

    <!-- GALLERY -->
    <?php if($totalCVs > 0): ?>
    <div class="cv-compact-grid">
        <?php foreach ($cvList as $cv): 
            $tpl = $tc->getTemplateById($cv['id_template']);
            $theme = $cv['couleurTheme'] ?: '#6B34A3';
            $parts = array_map('trim', explode('|', $cv['infoContact'] ?? ''));
            $contactStr = implode(' | ', array_filter([$parts[0] ?? '', $parts[1] ?? '', $parts[2] ?? '']));
            
            $aiData = !empty($cv['ai_analysis']) ? json_decode($cv['ai_analysis'], true) : null;
            
            // Prepare Preview HTML
            $previewHtml = $tpl['structureHtml'] ?? '';
            $isFullHtml = stripos($previewHtml, '<html') !== false;
            if (!$isFullHtml) {
                $previewHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"></head><body style="margin:0;padding:0;">' . $previewHtml . '</body></html>';
            }
            $previewHtml = str_ireplace('</head>', '<style>html,body{overflow:hidden!important;}::-webkit-scrollbar{display:none!important;}</style></head>', $previewHtml);
            
            $cvPayload = [
                'nomComplet' => $cv['nomComplet'],
                'titrePoste' => $cv['titrePoste'],
                'infoContact' => $contactStr,
                'resume' => $cv['resume'],
                'experience' => $cv['experience'],
                'competences' => str_replace(',', ' • ', $cv['competences']),
                'formation' => $cv['formation'],
                'langues' => $cv['langues'],
                'urlPhoto' => $cv['urlPhoto'] ?? ''
            ];

            $injector = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const d = ".json_encode($cvPayload).";
                    const setVal = (sel, val, isHtml = false) => { 
                        document.querySelectorAll(sel).forEach(e => { if (isHtml) e.innerHTML = val; else e.innerText = val; });
                    };
                    setVal('.cv-name, #preview-nomComplet, h1', d.nomComplet);
                    setVal('.cv-title, #preview-titrePoste, h2', d.titrePoste);
                    setVal('.summary-text, #preview-resume, .summary, .cv-summary', d.resume, true);
                    setVal('#preview-experience, .cv-exp, .experience-list, .cv-experience', d.experience, true);
                    setVal('#preview-competences, .cv-skills, .skills-list, .cv-competences', d.competences, true);
                    setVal('#preview-langues, .cv-languages, .languages-list, .cv-langues', d.langues, true);
                    setVal('#preview-formation, .cv-edu, .education-list, .cv-formation', d.formation, true);
                    if (d.infoContact) setVal('.contact-info, #preview-infoContact, .cv-contact, .contact-details', d.infoContact.split('|').join('<br>'), true);
                    if (d.urlPhoto) {
                        const pi = document.querySelectorAll('#preview-photo, .cv-photo img, .profile-img, #profile-pic');
                        pi.forEach(i => { i.src = d.urlPhoto; i.style.display = 'block'; });
                        const txt = document.querySelectorAll('#photo-text, .photo-text');
                        txt.forEach(t => t.style.display = 'none');
                    }
                });
            </script>";
            $previewHtml = str_ireplace('</body>', $injector . '</body>', $previewHtml);
        ?>
        
        <div class="cv-miniature-card" id="cv-card-<?php echo $cv['id_cv']; ?>">
            <div class="cv-miniature__preview">
                <iframe id="iframe-<?php echo $cv['id_cv']; ?>" srcdoc="<?php echo htmlspecialchars($previewHtml); ?>" class="cv-miniature__iframe"></iframe>
                <div class="cv-miniature__overlay">
                    <button class="btn-aptus-eye" onclick="showTotalPreview(<?php echo $cv['id_cv']; ?>, '<?php echo $theme; ?>')" title="Visualisation Totale">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <div class="cv-miniature__info">
                <h3 class="cv-miniature__name"><?php echo htmlspecialchars($cv['nomComplet'] ?: 'Sans Nom'); ?></h3>
                <span class="cv-miniature__role"><?php echo htmlspecialchars($cv['titrePoste'] ?: 'Candidat'); ?></span>
                
                <div class="cv-miniature__actions">
                    <div style="font-size: 0.75rem; color: var(--text-tertiary);">
                        <?php echo date('d M', strtotime($cv['dateMiseAJour'])); ?>
                    </div>
                    <div class="cv-action-group">
                        <a href="cv_form.php?cv_id=<?php echo $cv['id_cv']; ?>" class="btn-action-small edit" title="Éditer">
                            <i data-lucide="edit-2" style="width: 14px;"></i>
                        </a>
                        <button onclick="generatePDF(<?php echo $cv['id_cv']; ?>)" class="btn-action-small print" title="Imprimer">
                            <i data-lucide="printer" style="width: 14px;"></i>
                        </button>
                        <?php if($aiData): ?>
                        <button onclick="showAIAudit(<?php echo htmlspecialchars(json_encode($aiData), ENT_QUOTES); ?>)" class="btn-action-small ai" title="IA">
                            <i data-lucide="sparkles" style="width: 14px;"></i>
                        </button>
                        <?php endif; ?>
                        <button onclick="deleteCV(<?php echo $cv['id_cv']; ?>)" class="btn-action-small danger" title="Supprimer">
                            <i data-lucide="trash-2" style="width: 14px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 8rem 0;">
        <i data-lucide="file-plus" style="width: 64px; height: 64px; opacity: 0.2; margin-bottom: 1rem;"></i>
        <h2 style="color: #fff;">Aucun CV</h2>
        <a href="cv_templates.php" style="color: var(--accent-primary); text-decoration: none;">Commencer la création →</a>
    </div>
    <?php endif; ?>

</div>

<!-- ── TOTAL PREVIEW MODAL ── -->
<div id="aptus-total-modal" class="aptus-total-modal" onclick="if(event.target===this) closeTotalPreview();">
    <div class="total-modal-header">
        <span style="font-weight: 800; opacity: 0.8;">VISUALISATION COMPLÈTE</span>
        <button class="btn-aptus-close-large" onclick="closeTotalPreview()">
            <i data-lucide="x"></i>
        </button>
    </div>
    <div class="total-modal-body">
        <div class="total-modal-cv-container" id="total-cv-container">
            <iframe id="total-iframe-target" class="total-modal-iframe"></iframe>
        </div>
    </div>
    <div class="total-modal-footer">
        <button class="btn-aptus-primary" id="total-print-btn" style="background: #fff; color: #000; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="printer"></i> Imprimer / PDF
        </button>
    </div>
</div>

<!-- AI MODAL (Minimalist Site Theme) -->
<div id="view-audit-modal" class="aptus-modal-overlay" onclick="if(event.target===this) this.classList.remove('active');">
    <div class="aptus-modal-content" style="max-width: 600px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 30px; padding: 2.5rem;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="color: var(--text-primary); margin:0;"><i data-lucide="sparkles" style="color: #f59e0b; margin-right: 10px;"></i>Audit IA</h3>
            <div style="font-size: 1.5rem; font-weight: 900; color: var(--text-primary);"><span id="view-score-value">0</span>%</div>
        </div>
        <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 20px; margin-bottom: 1.5rem;">
            <h4 style="color: #10b981; font-size: 0.9rem; margin-bottom: 10px;">POINTS FORTS</h4>
            <ul id="view-strengths" style="color: var(--text-secondary); font-size: 0.85rem; padding-left: 1rem;"></ul>
        </div>
        <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 20px;">
            <h4 style="color: #f59e0b; font-size: 0.9rem; margin-bottom: 10px;">À AMÉLIORER</h4>
            <ul id="view-weaknesses" style="color: var(--text-secondary); font-size: 0.85rem; padding-left: 1rem;"></ul>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('view-audit-modal').classList.remove('active');" style="width: 100%; margin-top: 2rem; border-radius: 12px; padding: 12px;">Fermer</button>
    </div>
</div>

<iframe id="print-frame" style="display:none;"></iframe>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(window.lucide) lucide.createIcons();
    
    // Auto-scale miniature iframes
    document.querySelectorAll('.cv-miniature__iframe').forEach(ifr => {
        const parent = ifr.parentElement;
        const scale = parent.clientWidth / 794;
        ifr.style.transform = `scale(${scale})`;
    });

    const link = document.getElementById('nav-cv-my');
    if(link) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        link.classList.add('active');
    }
});

function showTotalPreview(cvId, theme) {
    const modal = document.getElementById('aptus-total-modal');
    const sourceIframe = document.getElementById('iframe-' + cvId);
    const targetIframe = document.getElementById('total-iframe-target');
    const printBtn = document.getElementById('total-print-btn');
    const container = document.getElementById('total-cv-container');

    // Inject scaling, theme, and disable interactions (Read-Only Mode)
    let content = sourceIframe.srcdoc;
    content = content.replace('</head>', `<style>
        :root{--cv-accent:${theme};} 
        body{ 
            background:white; 
            display:flex; 
            justify-content:center; 
            align-items:flex-start; 
            pointer-events: none; /* Disable all clicks/interactions */
            user-select: none; /* Disable text selection */
        }
    </style></head>`);
    
    targetIframe.srcdoc = content;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Scale to fit body height
    setTimeout(() => {
        const bodyH = document.querySelector('.total-modal-body').clientHeight;
        const scale = bodyH / 1123;
        targetIframe.style.transform = `scale(${scale})`;
        container.style.width = (794 * scale) + 'px';
    }, 100);

    printBtn.onclick = () => generatePDF(cvId);
}

function closeTotalPreview() {
    document.getElementById('aptus-total-modal').classList.remove('active');
    document.body.style.overflow = '';
}

function generatePDF(cvId) {
    const frame = document.getElementById('print-frame');
    frame.src = 'cv_print.php?id=' + cvId;
    frame.onload = function() { frame.contentWindow.print(); };
}

async function deleteCV(cvId) {
    const ok = await aptusConfirm('Supprimer ?', 'Voulez-vous vraiment supprimer ce CV ?');
    if(ok) window.location.href = 'cv_delete.php?id=' + cvId;
}

function showAIAudit(r) {
    const modal = document.getElementById('view-audit-modal');
    modal.classList.add('active');
    let start = 0;
    const target = r.score_ats || 0;
    const valEl = document.getElementById('view-score-value');
    const timer = setInterval(() => {
        if(start >= target) { clearInterval(timer); start = target; }
        valEl.textContent = start;
        start += 2;
    }, 20);
    const sList = document.getElementById('view-strengths'); sList.innerHTML = '';
    (r.points_forts || []).forEach(pt => { const li = document.createElement('li'); li.textContent = pt; sList.appendChild(li); });
    const wList = document.getElementById('view-weaknesses'); wList.innerHTML = '';
    (r.points_faibles || []).forEach(pt => { const li = document.createElement('li'); li.textContent = pt; wList.appendChild(li); });
}

window.addEventListener('resize', () => {
    document.querySelectorAll('.cv-miniature__iframe').forEach(ifr => {
        const parent = ifr.parentElement;
        const scale = parent.clientWidth / 794;
        ifr.style.transform = `scale(${scale})`;
    });
    
    if(document.getElementById('aptus-total-modal').classList.contains('active')) {
        const bodyH = document.querySelector('.total-modal-body').clientHeight;
        const scale = bodyH / 1123;
        const targetIframe = document.getElementById('total-iframe-target');
        const container = document.getElementById('total-cv-container');
        targetIframe.style.transform = `scale(${scale})`;
        container.style.width = (794 * scale) + 'px';
    }
});
</script>

