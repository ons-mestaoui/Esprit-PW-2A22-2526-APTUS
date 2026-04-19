<?php
/**
 * cv_form.php — CV Builder (Aptus Edition)
 * Fixed version with real-time sync, blocking validation, and sidebar checks.
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Template.php';
require_once __DIR__ . '/../../controller/TemplateC.php';
require_once __DIR__ . '/../../model/CV.php';
require_once __DIR__ . '/../../controller/CVC.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$tc  = new TemplateC();
$cvc = new CVC();

$template_id = (int)($_GET['template_id'] ?? 0);
$cv_id       = (int)($_GET['cv_id']       ?? 0);

$cv = [
    'nomComplet'  => '', 'email' => '', 'telephone' => '', 'adresse' => '',
    'titrePoste'  => '', 'resume' => '', 'experience' => '',
    'competences' => '', 'langues' => '', 'formation' => '',
    'urlPhoto'    => '', 'couleurTheme' => '#6B34A3'
];

if ($cv_id) {
    $row = $cvc->getCVById($cv_id);
    if ($row) {
        $parts = array_map('trim', explode('|', $row['infoContact'] ?? ''));
        $cv = array_merge($cv, [
            'nomComplet'  => $row['nomComplet'],
            'email'       => $parts[0] ?? '',
            'telephone'   => $parts[1] ?? '',
            'adresse'     => $parts[2] ?? '',
            'titrePoste'  => $row['titrePoste'],
            'resume'      => $row['resume'],
            'experience'  => $row['experience'],
            'competences' => $row['competences'],
            'langues'     => $row['langues'],
            'formation'   => $row['formation'],
            'urlPhoto'    => $row['urlPhoto'],
            'couleurTheme'=> $row['couleurTheme'] ?? '#6B34A3',
        ]);
        $template_id = $row['id_template'];
    }
}

$template = $template_id ? $tc->getTemplateById($template_id) : null;
if (!$template && !$cv_id) {
    header("Location: cv_templates.php");
    exit;
}

$pageTitle = "CV Builder";
$pageCSS   = "cv_premium.css?v=" . time();

if (!isset($content)) {
    header('Content-Type: text/html; charset=utf-8');
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<script>
    const INITIAL_DATA = <?php echo json_encode($cv); ?>;
    const CV_ID = <?php echo $cv_id ? (int)$cv_id : 'null'; ?>;
    const TEMPLATE_ID = <?php echo (int)$template_id; ?>;
</script>

<div class="builder-layout">

    <!-- LEFT: Step Sidebar -->
    <aside class="wizard-sidebar">
        <div class="stepper-container">
            <div class="wizard-step-link active" data-step="1">
                <div class="step-line"></div>
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">1</span></div>
                <div class="step-name">Infos</div>
            </div>
            <div class="wizard-step-link" data-step="2">
                <div class="step-line"></div>
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">2</span></div>
                <div class="step-name">Résumé</div>
            </div>
            <div class="wizard-step-link" data-step="3">
                <div class="step-line"></div>
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">3</span></div>
                <div class="step-name">Expérience</div>
            </div>
            <div class="wizard-step-link" data-step="4">
                <div class="step-line"></div>
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">4</span></div>
                <div class="step-name">Compétences</div>
            </div>
            <div class="wizard-step-link" data-step="5">
                <div class="step-line"></div>
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">5</span></div>
                <div class="step-name">Formation</div>
            </div>
            <div class="wizard-step-link" data-step="6">
                <div class="step-num"><span class="check-icon" style="display:none;"><i data-lucide="check" style="width:16px;height:16px;"></i></span><span class="step-txt">6</span></div>
                <div class="step-name">Langues</div>
            </div>
        </div>
        <div class="progress-section" style="margin-top:auto; padding-top:30px; border-top:1px solid var(--border-color);">
            <div class="progress-info" style="margin-bottom:12px;">
                <span style="font-weight:500; font-size:0.85rem; letter-spacing:0.5px; color:var(--text-primary)">CV Progression:</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="progress-track" style="flex:1; height:12px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:100px; overflow:hidden; position:relative;">
                    <div id="progress-bar-fill" style="width:0%; height:100%; background:linear-gradient(90deg, #6B34A3 0%, #3B82F6 50%, #00d2ff 100%); border-radius:100px; transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 0 20px rgba(107, 52, 163, 0.4);"></div>
                </div>
                <span id="progress-text" style="font-weight:800; color:var(--text-primary); font-size:1.1rem; min-width:50px;">0%</span>
            </div>
            <p style="font-size:0.7rem; color:var(--text-tertiary); margin-top:15px; text-align:left; font-style:italic;">Suivez votre progression en complétant chaque étape.</p>
        </div>
    </aside>

    <!-- CENTER: Form Area -->
    <main class="builder-form-area" id="form-container">

        <!-- STEP 1: Personal Info -->
        <div class="step-content active" id="step-1">
            <div class="step-header"><h2>Informations Personnelles</h2></div>
            
            <div class="image-upload-wrapper" id="photo-upload-wrapper">
                <i class="fa-solid fa-camera fa-2x" style="color:var(--text-tertiary);"></i>
                <p>Ajouter une photo</p>
                <img id="photo-preview-img" class="photo-preview" src="" alt="" style="display:none;">
                <input type="file" id="input-photo" accept="image/*" style="display:none;">
                <input type="hidden" id="photo-b64" value="">
            </div>

            <div class="form-group">
                <label>Nom Complet *</label>
                <div class="input-icon-group">
                    <i data-lucide="user"></i>
                    <input type="text" id="input-name" class="form-control" placeholder="Jean Dupont" required>
                </div>
            </div>
            <div class="form-group" style="position:relative;">
                <label>Titre du Poste *</label>
                <div class="input-icon-group">
                    <i data-lucide="briefcase"></i>
                    <input type="text" id="input-title" class="form-control" placeholder="ex: Développeur Full-Stack" onfocus="setupSmartAutocomplete(this, TITLES_DB)" required>
                </div>
                <div class="tag-suggestions" id="title-suggestions"></div>
            </div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Email *</label>
                    <div class="input-icon-group">
                        <i data-lucide="mail"></i>
                        <input type="email" id="input-email" class="form-control" placeholder="nom@exemple.com" required>
                    </div>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Téléphone *</label>
                    <div class="input-icon-group">
                        <i data-lucide="phone"></i>
                        <input type="text" id="input-phone" class="form-control" placeholder="+216 ..." required>
                    </div>
                </div>
            </div>
            <div class="form-group" style="position:relative;">
                <label>Localisation *</label>
                <div class="input-icon-group">
                    <i data-lucide="map-pin"></i>
                    <input type="text" id="input-location" class="form-control" placeholder="Tunis, Tunisie" onfocus="setupSmartAutocomplete(this, LOCATIONS_DB)" required>
                </div>
                <div class="tag-suggestions" id="location-suggestions"></div>
            </div>
            <div class="wizard-footer"><div></div><button class="btn-primary-cv" onclick="goToStep(2)">Suivant: Résumé</button></div>
        </div>

        <!-- STEP 2: Summary -->
        <div class="step-content" id="step-2">
            <div class="step-header"><h2>Résumé Professionnel</h2></div>
            <div class="form-group" style="position:relative;">
                <label>Votre Profil *</label>
                <textarea id="input-summary" class="form-control" placeholder="Professionnel passionné..." required style="padding-bottom:45px;"></textarea>
                <button type="button" class="btn-ai-premium" onclick="optimizeWithAI('input-summary', 'summary', this)">
                    <i data-lucide="sparkles" style="width:14px;height:14px;"></i> <span>Polish via IA</span>
                </button>
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(1)">Retour</button><button class="btn-primary-cv" onclick="goToStep(3)">Suivant: Expérience</button></div>
        </div>

        <!-- STEP 3: Experience -->
        <div class="step-content" id="step-3">
            <div class="step-header"><h2>Expérience Professionnelle</h2></div>
            <div class="form-group" style="position:relative;">
                <textarea id="input-experience" class="form-control" style="height:220px; padding-bottom:45px;" placeholder="Poste — Entreprise&#10;Dates&#10;• Mission..."></textarea>
                <button type="button" class="btn-ai-premium" onclick="optimizeWithAI('input-experience', 'experience', this)" style="bottom:12px; right:12px; position:absolute;">
                    <i data-lucide="sparkles" style="width:14px;height:14px;"></i> <span>Polish via IA</span>
                </button>
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(2)">Retour</button><button class="btn-primary-cv" onclick="goToStep(4)">Suivant: Compétences</button></div>
        </div>

        <!-- STEP 4: Skills -->
        <div class="step-content" id="step-4">
            <div class="step-header"><h2>Compétences</h2></div>
            <div class="form-group">
                <div class="tags-container" id="skills-tags-container">
                    <input type="text" id="input-skill-search" class="tag-input" placeholder="Ajouter une compétence..." autocomplete="off">
                </div>
                <input type="hidden" id="input-skills" value="">
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(3)">Retour</button><button class="btn-primary-cv" onclick="goToStep(5)">Suivant: Formation</button></div>
        </div>

        <!-- STEP 5: Education -->
        <div class="step-content" id="step-5">
            <div class="step-header"><h2>Formation</h2></div>
            <div class="form-group" style="position:relative;">
                <textarea id="input-education" class="form-control" style="height:180px; padding-bottom:45px;" placeholder="Diplôme — Université"></textarea>
                <button type="button" class="btn-ai-premium" onclick="optimizeWithAI('input-education', 'education', this)" style="bottom:12px; right:12px; position:absolute;">
                    <i data-lucide="sparkles" style="width:14px;height:14px;"></i> <span>Polish via IA</span>
                </button>
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(4)">Retour</button><button class="btn-primary-cv" onclick="goToStep(6)">Suivant: Langues</button></div>
        </div>

        <!-- STEP 6: Languages -->
        <div class="step-content" id="step-6">
            <div class="step-header"><h2>Langues</h2></div>
            <div id="dynamic-languages-container"></div>
            <button class="btn-secondary-cv" style="width:100%; border-style:dashed;" onclick="addLanguage()"><i data-lucide="plus-circle"></i> Ajouter une langue</button>
            <textarea id="input-languages" style="display:none;"></textarea>

            <div class="form-group" style="margin-top:30px; border-top:1px solid var(--border-color); padding-top:20px;">
                <label>Couleur du thème</label>
                <div style="display:flex; align-items:center; gap:1rem; margin-top:10px;">
                    <input type="color" id="color-picker" class="color-picker" value="<?php echo htmlspecialchars($cv['couleurTheme']); ?>">
                    <span style="font-size:0.8rem; color:var(--text-tertiary);">Personnalisez l'accent de votre CV</span>
                </div>
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(5)">Retour</button><button class="btn-primary-cv" id="btn-save">Enregistrer le CV</button></div>
        </div>
    </main>

    <!-- RIGHT: Live Preview -->
    <aside class="builder-preview-area" id="cv-wrapper">
        <iframe id="template-preview-frame"></iframe>
    </aside>
</div>

<!-- SYSTEME IA AUDIT ATS -->
<div id="ai-audit-overlay" class="aptus-modal-overlay">
    <!-- Étape 1 : Le Choix -->
    <div id="ai-audit-prompt" class="aptus-modal-content">
        <div class="modal-icon-circle" style="background: rgba(107, 52, 163, 0.1); color: var(--accent-primary); margin: 0 auto 1.5rem auto; display:flex; align-items:center; justify-content:center;">
            <i data-lucide="sparkles" style="width: 40px; height: 40px;"></i>
        </div>
        <h2 style="font-size:1.5rem; margin-bottom:1rem;">CV Enregistré ! 🎉</h2>
        <p style="color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.6;">
            Souhaitez-vous que notre IA experte analyse ce CV (Audit ATS, Score, et Conseils) avant de quitter ?
        </p>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button class="btn-modal-confirm" onclick="startAIAudit()">✨ Analyser mon CV (Gratuit)</button>
            <button class="btn-secondary-cv" style="border:none;" onclick="window.location.href='cv_my.php'">Non merci, aller à Mes CVs</button>
        </div>
    </div>

    <!-- Étape 2 : Le Scanner -->
    <div id="ai-audit-scanner" class="aptus-modal-content" style="display:none; text-align:center;">
        <div style="margin: 2rem 0;">
            <i data-lucide="scan-line" style="width: 70px; height: 70px; color: var(--accent-primary); animation: scanPulse 1.5s infinite;"></i>
        </div>
        <h3 style="margin-bottom:0.5rem;">Audit par Mistral IA en cours...</h3>
        <p id="audit-status-text" style="color:var(--text-tertiary); font-family:monospace; margin-bottom: 2rem;">Lecture du contenu du CV...</p>
        <div style="background:var(--bg-secondary); height:6px; border-radius:3px; overflow:hidden;">
            <div id="audit-progress" style="width:10%; height:100%; background:linear-gradient(90deg, #10b981, #3b82f6); transition:width 0.4s ease;"></div>
        </div>
    </div>

    <!-- Étape 3 : Le Dashboard -->
    <div id="ai-audit-dashboard" class="aptus-modal-content" style="display:none; max-width: 600px; text-align:left;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <h2 style="font-size:1.6rem; display:flex; align-items:center; gap:8px;"><i data-lucide="bar-chart-2" style="color:var(--accent-primary);"></i> Rapport d'Audit ATS</h2>
            </div>
            <div class="ats-score-circle" id="ats-score-circle">
                <span id="ats-score-value">0</span>%
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; max-height:400px; overflow-y:auto; padding-right:5px;">
            <div class="audit-card strengths">
                <h4 style="color:#10b981; display:flex; align-items:center; gap:6px; margin-bottom:10px;"><i data-lucide="check-circle" style="width:16px;"></i> Points Forts</h4>
                <ul id="ats-strengths" style="margin:0; padding-left:20px; color:var(--text-secondary); font-size:0.9rem; line-height:1.5;"></ul>
            </div>
            <div class="audit-card weaknesses">
                <h4 style="color:#f59e0b; display:flex; align-items:center; gap:6px; margin-bottom:10px;"><i data-lucide="alert-circle" style="width:16px;"></i> À Améliorer</h4>
                <ul id="ats-weaknesses" style="margin:0; padding-left:20px; color:var(--text-secondary); font-size:0.9rem; line-height:1.5;"></ul>
            </div>
        </div>

        <button class="btn-modal-confirm" style="width:100%; margin-top: 2rem;" onclick="window.location.href='cv_my.php'">Terminer et aller à Mes CVs</button>
    </div>
</div>

<?php
$templateHtml = $template['structureHtml'] ?? '';
$isFullHtml = stripos($templateHtml, '<html') !== false;
if (!$isFullHtml) {
    $templateHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"></head><body style="margin:0;padding:0;">' . $templateHtml . '</body></html>';
}
$overlayCSS = '<style>html,body{overflow:hidden!important;} ::-webkit-scrollbar{display:none;} .highlight-active{outline:3px solid var(--cv-accent, #6B34A3)!important; outline-offset:5px; background-color:rgba(107,52,163,0.06)!important; border-radius:4px; transition:all 0.4s ease;}</style>';
$templateHtml = str_ireplace('</head>', $overlayCSS . '</head>', $templateHtml);
?>

<script>
/* ── CV BUILDER ENGINE V3 ────────────────────────────────── */
let currentSkills = [];
let currentLangs = [];
const TITLES_DB = ['Développeur Full-Stack', 'Data Scientist', 'Chef de Projet IT', 'UX Designer', 'Commercial', 'Ingénieur', 'Designer Graphique', 'Marketing Manager', 'Content Creator', 'Social Media Manager'];
const LOCATIONS_DB = ['Paris, France', 'Tunis, Tunisie', 'Lyon, France', 'Remote', 'Casablanca, Maroc', 'Sousse, Tunisie', 'Sfax, Tunisie'];
const LANGUAGES_DB = ['Français', 'Anglais', 'Arabe', 'Allemand', 'Espagnol', 'Italien', 'Portugais'];
const SKILL_DB = ['PHP', 'JavaScript', 'HTML/CSS', 'React', 'Vue.js', 'Node.js', 'Python', 'SQL', 'Git', 'Agile', 'Gestion de projet', 'Communication', 'Leadership', 'Design Graphique', 'Figma', 'SEO', 'Marketing'];

const TEMPLATE_HTML = <?php echo json_encode($templateHtml); ?>;

/* ── Initialization ── */
document.addEventListener('DOMContentLoaded', () => {
    initIframe();
    window.addEventListener('resize', scaleIframe);

    // Initial Fill
    if (INITIAL_DATA) {
        Object.entries({ 'nomComplet':'input-name', 'titrePoste':'input-title', 'email':'input-email', 'telephone':'input-phone', 'adresse':'input-location', 'resume':'input-summary', 'experience':'input-experience', 'formation':'input-education' })
            .forEach(([k, id]) => { const el = document.getElementById(id); if(el && INITIAL_DATA[k]) el.value = INITIAL_DATA[k]; });
        if (INITIAL_DATA.langues) {
            currentLangs = INITIAL_DATA.langues.split('\n').filter(x=>x.trim()).map(line => {
                const p = line.split(/[—–-]/);
                return { lang: p[0]?.trim() || '', level: p[1]?.trim() || 'B1' };
            });
        } else { addLanguage(); }
        renderLanguages();
        if (INITIAL_DATA.competences) { currentSkills = INITIAL_DATA.competences.split(',').filter(x=>x.trim()); renderTags(); }
        if (INITIAL_DATA.urlPhoto) { document.getElementById('photo-b64').value = INITIAL_DATA.urlPhoto; const pi = document.getElementById('photo-preview-img'); if(pi) { pi.src=INITIAL_DATA.urlPhoto; pi.style.display='block'; }}
    }

    // Real-time Event Listeners
    document.querySelectorAll('.form-control').forEach(el => {
        el.addEventListener('input', () => { 
            validateInput(el); 
            syncField(el); 
            updateProgress(); 
        });
        el.addEventListener('blur', () => validateInput(el));
    });

    document.querySelectorAll('.wizard-step-link').forEach(l => l.addEventListener('click', () => goToStep(parseInt(l.dataset.step))));
    document.getElementById('btn-save').addEventListener('click', saveCV);
    document.getElementById('color-picker').addEventListener('input', (e) => {
        const ifrm = document.getElementById('template-preview-frame');
        if(ifrm && ifrm.contentDocument) ifrm.contentDocument.documentElement.style.setProperty('--cv-accent', e.target.value);
    });

    // Photo Upload
    const photoWrap = document.getElementById('photo-upload-wrapper');
    const photoInput = document.getElementById('input-photo');
    photoWrap.addEventListener('click', () => photoInput.click());
    photoInput.addEventListener('change', function() {
        if (this.files[0]) {
            const rd = new FileReader();
            rd.onload = (e) => {
                document.getElementById('photo-b64').value = e.target.result;
                const pi = document.getElementById('photo-preview-img'); pi.src = e.target.result; pi.style.display = 'block';
                const ifrm = document.getElementById('template-preview-frame');
                if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'photo', value: e.target.result }, '*');
            };
            rd.readAsDataURL(this.files[0]);
        }
    });

    updateProgress();
    
    // Attach smart autocomplete to existing fields
    setupSmartAutocomplete(document.getElementById('input-skill-search'), SKILL_DB, true);

    if (typeof lucide !== 'undefined') lucide.createIcons();
});

/* ── Verification & Steps ── */
function validateInput(inp) {
    if (!inp) return true;
    const val = inp.value.trim();
    let isOk = true;
    let msg = "";

    if (inp.required && val === "") { isOk = false; msg = "Obligatoire"; }
    else if (inp.type === "email" && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { isOk = false; msg = "Format invalide"; }
    else if (inp.id === "input-name" && val.length < 3) { isOk = false; msg = "Trop court"; }

    const gp = inp.closest('.form-group');
    if (!isOk) {
        inp.classList.add('is-invalid'); inp.classList.remove('is-valid');
        if (gp) {
            let em = gp.querySelector('.error-msg');
            if(!em) { em = document.createElement('div'); em.className='error-msg'; em.style.cssText="color:#dc2626; font-size:0.75rem; margin-top:4px;"; gp.appendChild(em); }
            em.textContent = msg;
        }
    } else {
        inp.classList.remove('is-invalid'); if(val!=="") inp.classList.add('is-valid');
        if (gp && gp.querySelector('.error-msg')) gp.querySelector('.error-msg').remove();
    }
    return isOk;
}

function validateStep(idx) {
    const stepEl = document.getElementById('step-' + idx);
    let allOk = true;
    stepEl.querySelectorAll('.form-control[required]').forEach(i => { if(!validateInput(i)) allOk = false; });
    return allOk;
}

function goToStep(idx) {
    const currentStepEl = document.querySelector('.step-content.active');
    const currentIdx = parseInt(currentStepEl.id.replace('step-', ''));

    // Block navigation if current step is invalid
    if (idx > currentIdx && !validateStep(currentIdx)) {
        const firstErr = currentStepEl.querySelector('.is-invalid');
        if(firstErr) firstErr.focus();
        return;
    }

    // Switch UI
    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.wizard-step-link').forEach(l => l.classList.remove('active'));
    document.getElementById('step-' + idx).classList.add('active');
    const link = document.querySelector(`.wizard-step-link[data-step="${idx}"]`);
    if(link) link.classList.add('active');

    // Sync Highlighting
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'highlight-section', step: idx }, '*');
}

function updateProgress() {
    let completedSteps = 0;
    const stepsCount = 6;
    
    for (let i = 1; i <= stepsCount; i++) {
        let done = false;
        if (i === 1) {
            done = ['input-name','input-title','input-email','input-phone','input-location'].every(id => {
                const el = document.getElementById(id);
                return el && el.value.trim().length >= 2;
            });
        }
        else if (i === 2) done = document.getElementById('input-summary').value.trim().length > 10;
        else if (i === 3) done = document.getElementById('input-experience').value.trim().length > 10;
        else if (i === 4) done = currentSkills.length > 0;
        else if (i === 5) done = document.getElementById('input-education').value.trim().length > 10;
        else if (i === 6) done = currentLangs.some(l => l.lang.trim().length > 0);
        
        markStep(i, done);
        if (done) completedSteps++;
    }
    
    // Mathematical Formula: (Steps / Total) * 100
    const percentage = Math.round((completedSteps / stepsCount) * 100);
    
    const bar = document.getElementById('progress-bar-fill');
    const text = document.getElementById('progress-text');
    
    if (bar) bar.style.width = percentage + '%';
    if (text) text.textContent = percentage + '%';
}

function markStep(idx, done) {
    const link = document.querySelector(`.wizard-step-link[data-step="${idx}"]`);
    if(!link) return;
    const check = link.querySelector('.check-icon');
    const txt = link.querySelector('.step-txt');
    if(done) { link.classList.add('completed'); check.style.display='block'; txt.style.display='none'; }
    else { link.classList.remove('completed'); check.style.display='none'; txt.style.display='block'; }
}

/* ── Template Sync Engine ── */
function initIframe() {
    const iframe = document.getElementById('template-preview-frame');
    if(!iframe) return;
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    const receiver = `
    <style>
        .highlight-active {
            outline: 2px solid #6B34A3 !important;
            outline-offset: 4px;
            background: rgba(107, 52, 163, 0.05) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 10;
        }
    </style>
    <script>
        window.addEventListener('message', function(e) {
            if (e.data.type === 'cv-update') {
                const d = e.data;
                const setVal = (sel, val, isHtml = false) => { 
                    const el = document.querySelectorAll(sel); 
                    el.forEach(e => {
                        if (isHtml) e.innerHTML = val;
                        else e.innerText = val;
                    });
                };
                if (d.field === 'nomComplet') setVal('.cv-name, #preview-nomComplet, h1', d.value);
                else if (d.field === 'titrePoste') setVal('.cv-title, #preview-titrePoste, h2', d.value);
                else if (d.field === 'resume') setVal('.summary-text, #preview-resume, .summary, .cv-summary', d.value);
                else if (d.field === 'experience') setVal('#preview-experience, .cv-exp, .experience-list, .cv-experience', d.value, true);
                else if (d.field === 'competences') setVal('#preview-competences, .cv-skills, .skills-list, .cv-competences', d.value);
                else if (d.field === 'langues') setVal('#preview-langues, .cv-languages, .languages-list, .cv-langues', d.value, true);
                else if (d.field === 'formation') setVal('#preview-formation, .cv-edu, .education-list, .cv-formation', d.value, true);
                else if (d.field === 'infoContact') {
                    const clean = d.value.split('|').map(s => s.trim()).join('<br>');
                    setVal('.contact-info, #preview-infoContact, .cv-contact, .contact-details', clean, true);
                }
                else if (d.field === 'photo') { const pi = document.querySelectorAll('#preview-photo, .cv-photo img, .profile-img'); pi.forEach(i => i.src = d.value); }
                return;
            }
            if (e.data.type === 'highlight-section') {
                document.querySelectorAll('.highlight-active').forEach(el => {
                    el.classList.remove('highlight-active');
                    el.style.outline = 'none';
                    el.style.background = 'none';
                });
                const step = e.data.step;
                const kMap = { 
                    2:['résumé','summary','propos','profil'], 
                    3:['expérience','experience','parcours','stages','work','emploi'], 
                    4:['compétence','skills','aptitudes','technique','outils','expert'], 
                    5:['formation','education','scolaire','academic','études','diplômes'], 
                    6:['langue','language','linguistique','linguistiques'] 
                };
                let target = null;
                if (step === 1) {
                    target = document.querySelector('.cv-header, .header-info, h1, .sidebar-header');
                } else if (kMap[step]) {
                    // Search all elements that might be headers
                    const possibleTitles = document.querySelectorAll('h1,h2,h3,h4,h5,p,div,span');
                    for (const t of possibleTitles) { 
                        const txt = t.textContent.trim().toLowerCase();
                        if (txt.length < 30 && kMap[step].some(k => txt.includes(k))) { 
                            // Climb to find the logical section
                            let current = t;
                            let best = t;
                            while(current && current.tagName !== 'BODY' && current.tagName !== 'HTML') {
                                if (current.classList.contains('cv-section') || current.classList.contains('section')) {
                                    best = current;
                                    break;
                                }
                                // If we find a div that seems to be a container (has siblings and siblings have titles)
                                if (current.tagName === 'DIV' && current.offsetHeight < document.body.offsetHeight * 0.8) {
                                    best = current;
                                }
                                current = current.parentElement;
                            }
                            target = best;
                            break; 
                        } 
                    }
                }
                if (target) { 
                    target.classList.add('highlight-active');
                    // Force styles to ensure visibility (sometimes classes aren't enough in iframes)
                    target.style.outline = '3px solid #6B34A3';
                    target.style.outlineOffset = '4px';
                    target.style.borderRadius = '4px';
                    target.style.backgroundColor = 'rgba(107, 52, 163, 0.05)';
                    target.scrollIntoView({ behavior:'smooth', block:'center' }); 
                }
            }
        });
    <\/script>`;
    doc.open(); doc.write(TEMPLATE_HTML.replace('</body>', receiver + '</body>')); doc.close();
    
    // Initial sync of all data once iframe is alive
    setTimeout(() => {
        syncAllData();
        scaleIframe();
    }, 500);
}

function scaleIframe() {
    const ifrm = document.getElementById('template-preview-frame');
    const wrap = document.getElementById('cv-wrapper');
    if(!ifrm || !wrap) return;
    
    // Stable width-based scaling
    const containerWidth = wrap.clientWidth - 40;
    const targetWidth = 794;
    let scale = containerWidth / targetWidth;
    if (scale > 1) scale = 1;

    ifrm.style.transform = `scale(${scale})`;
    ifrm.style.width = '794px';
    ifrm.style.height = '1123px';
    
    // Adjust wrap height to avoid cutting off
    wrap.style.height = (1123 * scale + 40) + 'px';
}

function syncField(el) {
    const map = { 'input-name':'nomComplet', 'input-title':'titrePoste', 'input-summary':'resume', 'input-experience':'experience', 'input-education':'formation', 'input-languages':'langues' };
    const field = map[el.id];
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) {
        if(field) ifrm.contentWindow.postMessage({ type: 'cv-update', field, value: el.value || '---' }, '*');
        if(['input-email','input-phone','input-location'].includes(el.id)) {
            const email = document.getElementById('input-email').value;
            const phone = document.getElementById('input-phone').value;
            const loc   = document.getElementById('input-location').value;
            const contact = [email, phone, loc].filter(x => x.trim()).join(' | ');
            ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'infoContact', value: contact }, '*');
        }
    }
}

function syncAllData() {
    const fields = ['input-name','input-title','input-email','input-phone','input-location','input-summary','input-experience','input-education'];
    fields.forEach(id => { const el = document.getElementById(id); if(el) syncField(el); });
    
    // Sync Skills & Languages
    renderTags(); 
    syncLangs();
    
    // Sync Photo
    const photo = document.getElementById('photo-b64').value;
    const ifrm = document.getElementById('template-preview-frame');
    if (ifrm && ifrm.contentWindow && photo) {
        ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'photo', value: photo }, '*');
    }
    
    // Sync Color
    const color = document.getElementById('color-picker').value;
    if (ifrm && ifrm.contentDocument) {
        ifrm.contentDocument.documentElement.style.setProperty('--cv-accent', color);
    }
}

/* ── Specific Systems ── */
function addLanguage() { currentLangs.push({ lang: '', level: 'B1' }); renderLanguages(); }
function removeLanguage(i) { currentLangs.splice(i, 1); renderLanguages(); syncLangs(); }
function renderLanguages() {
    const c = document.getElementById('dynamic-languages-container'); c.innerHTML = '';
    currentLangs.forEach((l, i) => {
        const d = document.createElement('div'); d.className = 'language-card';
        d.innerHTML = `
            <div class="language-card-header"><span class="language-card-title"><i data-lucide="languages" style="width:14px;"></i> Langue #${i+1}</span><button type="button" class="text-danger" style="background:none;border:none;cursor:pointer;" onclick="removeLanguage(${i})">&times;</button></div>
            <div class="input-icon-group" style="margin-bottom:0.8rem;"><i data-lucide="book-open"></i><input type="text" class="form-control" placeholder="Anglais..." value="${l.lang}" oninput="currentLangs[${i}].lang=this.value; syncLangs();" onfocus="setupSmartAutocomplete(this, LANGUAGES_DB)"></div>
            <div class="level-selector">${['A1','A2','B1','B2','C1','C2'].map(lvl => `<div class="level-pill ${l.level===lvl?'active':''}" onclick="currentLangs[${i}].level='${lvl}'; renderLanguages(); syncLangs();">${lvl}</div>`).join('')}</div>`;
        c.appendChild(d);
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function syncLangs() {
    const val = currentLangs.filter(l=>l.lang.trim()).map(l=>`${l.lang.trim()} - ${l.level}`).join('\n');
    document.getElementById('input-languages').value = val;
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'langues', value: val }, '*');
    updateProgress();
}

function renderTags() {
    const c = document.getElementById('skills-tags-container'); const i = document.getElementById('input-skill-search');
    c.querySelectorAll('.tag-item').forEach(t => t.remove());
    currentSkills.forEach(s => {
        const t = document.createElement('div'); t.className = 'tag-item';
        t.innerHTML = `<span>${s}</span><button type="button" onclick="removeSkill('${s}')" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:4px;">&times;</button>`;
        c.insertBefore(t, i);
    });
    document.getElementById('input-skills').value = currentSkills.join(',');
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'competences', value: currentSkills.join(' • ') }, '*');
    updateProgress();
}
function removeSkill(s) { currentSkills = currentSkills.filter(x=>x!==s); renderTags(); }
document.getElementById('input-skill-search')?.addEventListener('keydown', (e) => { if(e.key==='Enter' && e.target.value.trim()){ e.preventDefault(); const v=e.target.value.trim(); if(!currentSkills.includes(v)){ currentSkills.push(v); renderTags(); e.target.value=''; } } });

function setupSmartAutocomplete(inp, db, isSkill = false) {
    if (!inp) return;
    let sD = inp.parentElement.querySelector('.tag-suggestions');
    if (!sD) {
        sD = document.createElement('div');
        sD.className = 'tag-suggestions';
        inp.parentElement.style.position = 'relative';
        inp.parentElement.appendChild(sD);
    }

    inp.addEventListener('input', () => {
        const v = inp.value.toLowerCase().trim();
        sD.innerHTML = '';
        if (v.length < 1) { sD.style.display = 'none'; return; }
        
        const m = db.filter(x => x.toLowerCase().includes(v)).slice(0, 5);
        if (m.length > 0) {
            m.forEach(match => {
                const d = document.createElement('div');
                d.className = 'tag-suggest-item';
                d.innerHTML = match;
                d.onclick = () => {
                    if (isSkill) {
                        if (!currentSkills.includes(match)) { currentSkills.push(match); renderTags(); }
                        inp.value = '';
                    } else {
                        inp.value = match;
                        inp.dispatchEvent(new Event('input')); // Trigger sync
                    }
                    sD.style.display = 'none';
                };
                sD.appendChild(d);
            });
            sD.style.display = 'block';
        } else {
            sD.style.display = 'none';
        }
    });

    // Hide suggestion box when clicking outside
    document.addEventListener('click', (e) => {
        if (e.target !== inp && e.target !== sD) sD.style.display = 'none';
    });
}

let AUDIT_CV_ID = null;

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

async function saveCV() {
    const b = document.getElementById('btn-save'); b.disabled = true; b.textContent = 'Enregistrement...';
    const d = { cv_id: CV_ID, template_id: TEMPLATE_ID, name: document.getElementById('input-name').value.trim(), title: document.getElementById('input-title').value.trim(), email: document.getElementById('input-email').value.trim(), phone: document.getElementById('input-phone').value.trim(), location: document.getElementById('input-location').value.trim(), summary: document.getElementById('input-summary').value.trim(), experience: document.getElementById('input-experience').value, skills: document.getElementById('input-skills').value, education: document.getElementById('input-education').value, languages: document.getElementById('input-languages').value, photo: document.getElementById('photo-b64').value, color_theme: document.getElementById('color-picker').value };
    try {
        const rs = await fetch('cv_save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(d) });
        const rj = await rs.json(); 
        if(rj.success) {
            AUDIT_CV_ID = rj.id;
            // Ne pas rediriger, afficher la modale d'analyse IA
            document.getElementById('ai-audit-overlay').classList.add('active');
        } else {
            alert('Erreur: ' + rj.message);
        }
    } catch(e) { alert('Erreur de connexion.'); } finally { b.disabled = false; b.textContent = 'Enregistrer le CV'; }
}

async function startAIAudit() {
    document.getElementById('ai-audit-prompt').style.display = 'none';
    document.getElementById('ai-audit-scanner').style.display = 'block';
    
    // Fake progress text
    const progText = document.getElementById('audit-status-text');
    const progBar = document.getElementById('audit-progress');
    const steps = ['Lecture du contenu du CV...', 'Analyse du score ATS...', 'Vérification des mots-clés...', 'Compilation du rapport final...'];
    let stepIndex = 0;
    const interval = setInterval(() => {
        stepIndex++;
        if(stepIndex < steps.length) progText.textContent = steps[stepIndex];
        progBar.style.width = (20 + (stepIndex * 20)) + '%';
    }, 2000);

    // Build massive string
    const cvTextData = `
Titre visé: ${document.getElementById('input-title').value.trim()}
Résumé: ${document.getElementById('input-summary').value.trim()}
Expériences: ${document.getElementById('input-experience').value}
Formation: ${document.getElementById('input-education').value}
Compétences: ${document.getElementById('input-skills').value}
    `.trim();

    try {
        const response = await fetch('ajax_ai_analyze_cv.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_cv: AUDIT_CV_ID, cvText: cvTextData })
        });
        const data = await response.json();
        clearInterval(interval);
        
        if (data.success && data.report) {
            document.getElementById('ai-audit-scanner').style.display = 'none';
            document.getElementById('ai-audit-dashboard').style.display = 'block';
            
            const r = data.report;
            const circle = document.getElementById('ats-score-circle');
            
            // Lancer l'animation dynamique du score
            animateATSScore(r.score_ats || 0, 'ats-score-value');
            
            circle.className = 'ats-score-circle'; // reset
            if (r.score_ats >= 80) circle.classList.add('green');
            else if (r.score_ats >= 50) circle.classList.add('orange');
            else circle.classList.add('red');

            const sList = document.getElementById('ats-strengths');
            sList.innerHTML = '';
            (r.points_forts || []).forEach(pt => {
                const li = document.createElement('li'); li.textContent = pt; sList.appendChild(li);
            });

            const wList = document.getElementById('ats-weaknesses');
            wList.innerHTML = '';
            (r.points_faibles || []).forEach(pt => {
                const li = document.createElement('li'); li.textContent = pt; wList.appendChild(li);
            });
            
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else {
            alert("Erreur lors de l'analyse : " + (data.error || "Inconnue"));
            window.location.href = 'cv_my.php';
        }
    } catch(err) {
        clearInterval(interval);
        alert("Erreur réseau (Ollama local injoignable).");
        window.location.href = 'cv_my.php';
    }
}

    // ==========================================
    // IA Polish Integration (Ollama Local)
    // ==========================================
    async function optimizeWithAI(inputId, context, btnElement) {
        const inputField = document.getElementById(inputId);
        const textRaw = inputField.value.trim();
        
        if (!textRaw) {
            alert("Veuillez saisir un texte brouillon à améliorer d'abord.");
            return;
        }

        const spanText = btnElement.querySelector('span');
        const icon = btnElement.querySelector('i');
        const originalText = spanText.textContent;

        // UI Loading State
        btnElement.classList.add('ai-loading');
        spanText.textContent = "L'IA réfléchit...";
        btnElement.disabled = true;

        try {
            const response = await fetch('ajax_ai_polish.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: textRaw, context: context })
            });

            const data = await response.json();

            if (data.success) {
                // Update textarea with AI polished text
                inputField.value = data.polished_text;
                // Force sync update to the template iframe
                syncField(inputField);
                // Trigger visual feedback on textarea
                inputField.style.transition = 'box-shadow 0.3s ease, background 0.3s ease';
                inputField.style.boxShadow = 'inset 0 0 0 2px rgba(107, 52, 163, 0.5)';
                inputField.style.background = 'rgba(107, 52, 163, 0.03)';
                setTimeout(() => {
                    inputField.style.boxShadow = '';
                    inputField.style.background = '';
                }, 1500);
            } else {
                alert("Erreur de l'IA : " + (data.error || "Erreur inconnue."));
            }
        } catch (error) {
            console.error("Erreur dans l'IA Polish:", error);
            alert("Erreur côté client : l'application n'a pas pu traiter la réponse.");
        } finally {
            // Restore UI
            btnElement.classList.remove('ai-loading');
            spanText.textContent = originalText;
            btnElement.disabled = false;
        }
    }
</script>
