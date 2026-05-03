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
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    cursor: default;
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(10px);
}

.cv-miniature-card:hover {
    border-color: var(--accent-primary);
    background: var(--bg-card-hover);
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
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


/* ── STYLISH SCROLLBAR ── */
.stylish-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.stylish-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 10px;
}
.stylish-scrollbar::-webkit-scrollbar-thumb {
    background: var(--gradient-primary);
    border-radius: 10px;
    border: 2px solid transparent;
    background-clip: content-box;
}
.stylish-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--accent-primary);
}

/* ── PREMIUM BUTTONS ── */
.btn-aptus-primary {
    background: var(--gradient-primary);
    color: #fff !important;
    border: none;
    padding: 12px 25px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: var(--transition-bounce);
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(107, 52, 163, 0.3);
}

.btn-aptus-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(107, 52, 163, 0.4);
    filter: brightness(1.1);
}

.btn-aptus-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color);
    padding: 12px 25px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: var(--transition-base);
    cursor: pointer;
}

.btn-aptus-secondary:hover {
    background: var(--bg-tertiary);
    transform: translateY(-2px);
}

/* ── PREMIUM CARDS ── */
.ai-report-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    padding: 2rem;
    border-radius: 30px;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    transition: all 0.3s ease;
    text-align: left;
    color: var(--text-primary);
}

.ai-report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
    border-color: rgba(139, 92, 246, 0.2);
}

/* ── TAILOR PROGRESS ── */
.tailor-steps-container {
    display: none;
    flex-direction: column;
    gap: 20px;
    margin-top: 20px;
}

.tailor-step {
    display: flex;
    align-items: center;
    gap: 15px;
    opacity: 0.4;
    transition: all 0.4s ease;
}

.tailor-step.active {
    opacity: 1;
    color: var(--accent-primary);
}

.tailor-step.completed {
    opacity: 1;
    color: #10b981;
}

.tailor-step-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 800;
}

.tailor-step.active .tailor-step-icon {
    background: var(--accent-primary);
    color: #fff;
    box-shadow: 0 0 15px rgba(139, 92, 246, 0.4);
}

.tailor-step.completed .tailor-step-icon {
    background: #10b981;
    color: #fff;
}

.tailor-progress-bar {
    height: 6px;
    background: var(--bg-secondary);
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
    display: none;
}

.tailor-progress-fill {
    height: 100%;
    width: 0%;
    background: var(--gradient-primary);
    transition: width 0.5s ease;
}
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
                <?php if(isset($cv['is_tailored']) && $cv['is_tailored']): ?>
                <div class="tailor-badge" style="position: absolute; top: 15px; left: 15px; z-index: 5; background: rgba(255,255,255,0.95); padding: 6px 12px; border-radius: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.7rem; font-weight: 850; border: 1px solid var(--accent-primary); box-shadow: 0 4px 15px rgba(107, 52, 163, 0.15); letter-spacing: 1px;">
                    <?php 
                        $url = $cv['target_job_url'] ?? '';
                        $icon = 'briefcase';
                        if(stripos($url, 'linkedin') !== false) $icon = 'linkedin';
                        else if(stripos($url, 'indeed') !== false) $icon = 'globe';
                    ?>
                    <i data-lucide="<?php echo $icon; ?>" style="width: 13px; color: var(--accent-primary);"></i>
                    <span style="color: var(--accent-primary);">OPTIMISÉ SUR MESURE</span>
                </div>
                <?php endif; ?>
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
                        <button onclick="openTailorModal(<?php echo $cv['id_cv']; ?>)" class="btn-action-small tailor" title="Sur Mesure" style="color: #8b5cf6; border-color: rgba(139, 92, 246, 0.2);">
                            <i data-lucide="wand-2" style="width: 14px;"></i>
                        </button>
                        <?php if(isset($cv['is_tailored']) && $cv['is_tailored']): ?>
                        <a href="cv_tailor_guide.php?id=<?php echo $cv['id_cv']; ?>" class="btn-action-small guide" title="Voir le Guide" style="color: #0ea5e9; border-color: rgba(14, 165, 233, 0.2);">
                            <i data-lucide="book-open" style="width: 14px;"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($aiData): ?>
                        <button onclick="showAIAudit(<?php echo htmlspecialchars(json_encode($aiData), ENT_QUOTES); ?>, <?php echo $cv['id_cv']; ?>)" class="btn-action-small ai" title="IA">
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

<!-- AI MODAL (Centered Aptus Theme) -->
<div id="view-audit-modal"  class="aptus-modal-overlay" onclick="if(event.target===this) this.classList.remove('active');">
    <div class="aptus-modal-content" style="max-width: 700px; max-height: 90vh; overflow: hidden; background: #f8fafc; border: none; border-radius: 20px; padding: 0; text-align: center; position: relative;">
        <!-- Move absolute elements outside the scrollable area if needed, or keep inside -->
        <i data-lucide="sparkles" style="color: #f59e0b; position: absolute; top: 30px; left: 30px; width: 30px; height: 30px; z-index: 10;"></i>
        
        <div class="stylish-scrollbar" style="max-height: 90vh; overflow-y: auto; padding: 3.5rem;">
            <h2 style="color: #1e293b; font-size: 2.2rem; font-weight: 800; margin-bottom: 5px;">Audit IA Stratégique</h2>
            <div style="font-size: 3rem; font-weight: 900; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 2.5rem;"><span id="view-score-value">0</span>%</div>
            
            <div class="ai-report-card">
                <h4 style="color: #10b981; font-size: 0.9rem; font-weight: 800; margin-bottom: 15px; letter-spacing: 1px; text-transform: uppercase;">Points Forts</h4>
                <div id="view-strengths" style="color: #475569; font-size: 1rem; line-height: 1.6; display: flex; flex-direction: column; gap: 10px;"></div>
            </div>
            
            <div class="ai-report-card">
                <h4 style="color: #f59e0b; font-size: 0.9rem; font-weight: 800; margin-bottom: 15px; letter-spacing: 1px; text-transform: uppercase;">À Améliorer</h4>
                <div id="view-weaknesses" style="color: #475569; font-size: 1rem; line-height: 1.6; display: flex; flex-direction: column; gap: 10px;"></div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 2.5rem;">
                <button id="btn-view-details" class="btn-modal-confirm" style="background: #fff; color: var(--text-primary); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 12px 20px; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="external-link" style="width:16px;"></i> Détails Stratégiques
                </button>
                <button class="btn-modal-confirm" onclick="document.getElementById('view-audit-modal').classList.remove('active');" style="background: linear-gradient(135deg, #0ea5e9 0%, #8b5cf6 50%, #d946ef 100%); border: none; color: #fff; padding: 12px 20px; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<iframe id="print-frame" style="display:none;"></iframe>

<!-- TAILOR MODAL -->
<div id="tailor-modal" class="aptus-modal-overlay" onclick="if(event.target===this) this.classList.remove('active');">
    <div class="aptus-modal-content" style="max-width: 450px; padding: 2.5rem; border-radius: 30px; background: #fff;">
        <div id="tailor-modal-form">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 22px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);">
                    <i data-lucide="wand-2" style="color: #fff; width: 34px; height: 34px;"></i>
                </div>
                <h2 style="font-size: 1.8rem; font-weight: 850; letter-spacing: -1px; color: #1e293b; margin-bottom: 8px;">CV Sur Mesure</h2>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">L'IA va scanner l'offre d'emploi et optimiser chaque section de votre CV pour ce poste.</p>
            </div>
            
            <input type="hidden" id="tailor-cv-id">
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 10px; font-weight: 800; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Lien de l'offre (LinkedIn, Indeed...)</label>
                <input type="url" id="tailor-job-url" placeholder="https://..." style="width: 100%; padding: 16px; border-radius: 16px; border: 2px solid #f1f5f9; background: #f8fafc; color: #1e293b; font-size: 1rem; transition: all 0.3s ease;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button onclick="startTailoring()" id="btn-start-tailor" class="btn-aptus-primary" style="height: 58px; font-size: 1.05rem; width: 100%;">
                    Lancer l'Analyse ✨
                </button>
                <button onclick="document.getElementById('tailor-modal').classList.remove('active')" style="background: none; border: none; color: #94a3b8; font-weight: 700; font-size: 0.9rem; cursor: pointer; padding: 10px;">
                    Annuler
                </button>
            </div>
        </div>

        <!-- PROGRESS VIEW -->
        <div id="tailor-modal-processing" style="display: none;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="spin" style="width: 60px; height: 60px; border: 4px solid var(--accent-primary); border-top-color: transparent; border-radius: 50%; margin: 0 auto 1.5rem;"></div>
                <h2 style="font-size: 1.6rem; font-weight: 850; color: #1e293b;">Analyse IA Stratégique</h2>
                <p style="color: #64748b; font-size: 0.9rem;">Veuillez patienter, nos agents IA travaillent sur votre dossier...</p>
            </div>

            <div class="tailor-progress-bar" style="display: block; margin-bottom: 25px;">
                <div class="tailor-progress-fill" id="tailor-progress-fill"></div>
            </div>

            <div class="tailor-steps-container" style="display: flex;">
                <div class="tailor-step active" id="step-scrape">
                    <div class="tailor-step-icon">1</div>
                    <div style="font-weight: 700; font-size: 0.95rem;">Scraping de l'offre d'emploi...</div>
                </div>
                <div class="tailor-step" id="step-analyze">
                    <div class="tailor-step-icon">2</div>
                    <div style="font-weight: 700; font-size: 0.95rem;">Analyse des compétences...</div>
                </div>
                <div class="tailor-step" id="step-tailor">
                    <div class="tailor-step-icon">3</div>
                    <div style="font-weight: 700; font-size: 0.95rem;">Optimisation de votre CV...</div>
                </div>
            </div>
        </div>
    </div>
</div>

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

function showAIAudit(r, cvId) {
    const modal = document.getElementById('view-audit-modal');
    modal.classList.add('active');
    let start = 0;
    const target = r.score_ats || 0;
    const valEl = document.getElementById('view-score-value');
    const timer = setInterval(() => {
        if(start >= target) { clearInterval(timer); start = target; }
        valEl.textContent = start;
        if (start === target) return;
        start += 1;
    }, 15);
    const sList = document.getElementById('view-strengths'); sList.innerHTML = '';
    (r.points_forts || []).forEach(pt => { 
        const div = document.createElement('div'); 
        div.style.display = 'flex'; div.style.alignItems = 'start'; div.style.gap = '10px';
        div.innerHTML = `<i data-lucide="check-circle-2" style="color:#10b981; width:18px; flex-shrink:0; margin-top:3px;"></i> <span>${pt}</span>`;
        sList.appendChild(div); 
    });
    const wList = document.getElementById('view-weaknesses'); wList.innerHTML = '';
    (r.points_faibles || []).forEach(pt => { 
        const div = document.createElement('div'); 
        div.style.display = 'flex'; div.style.alignItems = 'start'; div.style.gap = '10px';
        div.innerHTML = `<i data-lucide="alert-circle" style="color:#f59e0b; width:18px; flex-shrink:0; margin-top:3px;"></i> <span>${pt}</span>`;
        wList.appendChild(div); 
    });
    
    document.getElementById('btn-view-details').onclick = () => {
        window.location.href = 'cv_audit_details.php?id=' + (cvId || '');
    };
    if(window.lucide) lucide.createIcons();
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

/* ── TAILORING LOGIC ── */
function openTailorModal(cvId) {
    document.getElementById('tailor-cv-id').value = cvId;
    document.getElementById('tailor-job-url').value = '';
    document.getElementById('tailor-modal').classList.add('active');
    setTimeout(() => document.getElementById('tailor-job-url').focus(), 100);
}

async function startTailoring() {
    const cvId = document.getElementById('tailor-cv-id').value;
    const url = document.getElementById('tailor-job-url').value;
    const formView = document.getElementById('tailor-modal-form');
    const processingView = document.getElementById('tailor-modal-processing');
    const progressFill = document.getElementById('tailor-progress-fill');
    
    if(!url || !url.startsWith('http')) {
        aptusAlert('Erreur', 'Veuillez saisir un lien valide.');
        return;
    }

    // Switch views
    formView.style.display = 'none';
    processingView.style.display = 'block';

    const steps = [
        { id: 'step-scrape', duration: 8000, progress: 33 },
        { id: 'step-analyze', duration: 5000, progress: 66 },
        { id: 'step-tailor', duration: 7000, progress: 95 }
    ];

    let currentStepIndex = 0;
    
    function updateSteps() {
        if (currentStepIndex >= steps.length) return;
        
        const step = steps[currentStepIndex];
        const el = document.getElementById(step.id);
        
        // Mark previous as completed
        if (currentStepIndex > 0) {
            const prev = document.getElementById(steps[currentStepIndex-1].id);
            prev.classList.remove('active');
            prev.classList.add('completed');
            prev.querySelector('.tailor-step-icon').innerHTML = '<i data-lucide="check" style="width:16px;"></i>';
            if(window.lucide) lucide.createIcons();
        }

        el.classList.add('active');
        progressFill.style.width = step.progress + '%';
        
        currentStepIndex++;
        if (currentStepIndex < steps.length) {
            setTimeout(updateSteps, steps[currentStepIndex-1].duration);
        }
    }

    updateSteps();

    try {
        const response = await fetch('ajax_job_analyze.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cv_id=${cvId}&url=${encodeURIComponent(url)}`
        });
        
        if (!response.ok) {
            throw new Error(`Le serveur a répondu avec une erreur (${response.status}).`);
        }

        const text = await response.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (e) {
            console.error("Réponse non-JSON :", text);
            throw new Error("Le serveur a renvoyé une réponse invalide. Vérifiez les logs.");
        }

        if(res.success) {
            progressFill.style.width = '100%';
            setTimeout(() => {
                window.location.href = `cv_form.php?cv_id=${cvId}&tailor_mode=1`;
            }, 800);
        } else {
            throw new Error(res.error || 'Une erreur est survenue.');
        }
    } catch (e) {
        console.error(e);
        alert('Erreur d\'Analyse : ' + (e.message || 'Impossible de contacter le serveur.'));
        formView.style.display = 'block';
        processingView.style.display = 'none';
    }
}
</script>

