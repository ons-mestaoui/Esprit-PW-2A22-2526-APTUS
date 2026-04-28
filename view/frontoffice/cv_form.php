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

        <!-- Dyslexia & Bionic Toggles -->
        <div class="dyslexia-toggle-wrapper" style="margin-top: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="tooltip-trigger" data-tooltip="Optimise la police (OpenDyslexic) et l'espacement pour réduire la fatigue visuelle." style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="glasses" style="color: var(--accent-primary); width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">Mode Lecture</span>
                </div>
                <label class="premium-switch">
                    <input type="checkbox" id="dyslexia-toggle" onchange="toggleDyslexiaMode(this.checked)">
                    <span class="slider round"></span>
                </label>
            </div>
            
            <div class="tooltip-trigger" data-tooltip="Met en gras le début des mots pour scanner votre texte 3x plus vite et repérer les fautes." style="display: flex; align-items: center; justify-content: space-between; padding-top: 15px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="zap" style="color: var(--accent-primary); width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">Mode Bionique</span>
                </div>
                <label class="premium-switch">
                    <input type="checkbox" id="bionic-toggle" onchange="toggleBionicMode(this.checked)">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <div class="progress-section tooltip-trigger" data-tooltip="Suivez votre progression en complétant chaque étape du CV." style="margin-top:auto; padding-top:30px; border-top:1px solid var(--border-color); cursor:help;">
            <div class="progress-info" style="margin-bottom:12px;">
                <span style="font-weight:500; font-size:0.85rem; letter-spacing:0.5px; color:var(--text-primary)">CV Progression:</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="progress-track" style="flex:1; height:12px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:100px; overflow:hidden; position:relative;">
                    <div id="progress-bar-fill" style="width:0%; height:100%; background:linear-gradient(90deg, #6B34A3 0%, #3B82F6 50%, #00d2ff 100%); border-radius:100px; transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 0 20px rgba(107, 52, 163, 0.4);"></div>
                </div>
                <span id="progress-text" style="font-weight:800; color:var(--text-primary); font-size:1.1rem; min-width:50px;">0%</span>
            </div>
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

        <!-- STEP 2: Summary (The Persona Architect) -->
        <div class="step-content" id="step-2">
            <div class="step-header"><h2>Résumé Professionnel</h2></div>
            
            <div class="persona-grid">
                <div class="persona-card" onclick="selectPersona('visionary', this)">
                    <i data-lucide="rocket" class="persona-icon"></i>
                    <div class="persona-title">Le Visionnaire</div>
                </div>
                <div class="persona-card" onclick="selectPersona('expert', this)">
                    <i data-lucide="award" class="persona-icon"></i>
                    <div class="persona-title">L'Expert</div>
                </div>
                <div class="persona-card" onclick="selectPersona('leader', this)">
                    <i data-lucide="users" class="persona-icon"></i>
                    <div class="persona-title">Le Leader</div>
                </div>
            </div>

            <div class="gender-selector-premium">
                <div class="gender-btn active" id="btn-male" onclick="setGender('m')">
                    <i data-lucide="user"></i> Masculin
                </div>
                <div class="gender-btn" id="btn-female" onclick="setGender('f')">
                    <i data-lucide="user-round"></i> Féminin
                </div>
            </div>

            <div class="alchemist-box-premium" style="border-top: 4px solid var(--accent-primary);">
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="input-icon-group">
                        <i data-lucide="briefcase" style="color: var(--accent-primary); width: 16px; height: 16px;"></i>
                        <input type="text" id="ml-title" class="form-control" style="padding-left: 38px;" placeholder="Titre du poste" oninput="updateSummaryFromMadLibs()">
                    </div>
                    <div class="input-icon-group">
                        <i data-lucide="calendar" style="color: var(--accent-primary); width: 16px; height: 16px;"></i>
                        <input type="text" id="ml-years" class="form-control" style="padding-left: 38px;" placeholder="Années d'exp." oninput="updateSummaryFromMadLibs()">
                    </div>
                </div>
                <div class="input-icon-group">
                    <i data-lucide="award" style="color: var(--accent-primary); width: 16px; height: 16px;"></i>
                    <input type="text" id="ml-skill" class="form-control" style="padding-left: 38px;" placeholder="Points forts (ex: Design, Management...)" oninput="updateSummaryFromMadLibs()">
                </div>
            </div>

            <div class="form-group" style="position:relative;">
                <label>Votre Profil Final *</label>
                <div id="input-summary" contenteditable="true" class="form-control" 
                     style="padding: 15px; padding-bottom:45px; height:auto; min-height:120px; overflow-y:auto; background:white; line-height:1.6;"
                     oninput="syncField({id:'input-summary', value:this.innerHTML})"></div>
                <button type="button" class="btn-ai-premium" onclick="optimizeWithAI('input-summary', 'summary', this)" style="bottom:12px; right:12px; position:absolute;">
                    <i data-lucide="sparkles" style="width:14px;height:14px;"></i> <span>Polish via IA</span>
                </button>
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(1)">Retour</button><button class="btn-primary-cv" onclick="goToStep(3)">Suivant: Expérience</button></div>
        </div>

        <!-- STEP 3: Experience (The Impact Timeline) -->
        <div class="step-content" id="step-3">
            <div class="step-header"><h2>Expérience Professionnelle</h2></div>
            <div class="timeline-container" id="experience-timeline">
                <!-- Roles will be appended here -->
            </div>
            <button class="btn-secondary-cv" style="width:100%; border-style:dashed; margin-bottom:1.5rem;" onclick="addRoleCard()"><i data-lucide="plus-circle"></i> Ajouter un poste</button>
            
            <textarea id="input-experience" class="form-control" style="display:none;"></textarea>
            
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(2)">Retour</button><button class="btn-primary-cv" onclick="goToStep(4)">Suivant: Compétences</button></div>
        </div>

        <!-- STEP 4: Skills (The Skill Heatmap) -->
        <div class="step-content" id="step-4">
            <div class="step-header"><h2>Compétences</h2></div>
            
            <div class="form-group">
                <div class="tags-container" id="skills-tags-container" style="border:none; padding:0; background:transparent;">
                    <!-- Skills will be grouped here -->
                </div>
                <div style="display:flex; gap:10px; margin-top:1rem; position:relative;">
                    <div class="input-icon-group" style="flex:1;">
                        <i data-lucide="search"></i>
                        <input type="text" id="input-skill-search" class="form-control" placeholder="Ajouter une compétence (ex: React, Photoshop...)" autocomplete="off" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addSkillFromInput(); }">
                    </div>
                </div>
                <input type="hidden" id="input-skills" value="">
            </div>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(3)">Retour</button><button class="btn-primary-cv" onclick="goToStep(5)">Suivant: Formation</button></div>
        </div>

        <!-- STEP 5: Education (The Academic Journey) -->
        <div class="step-content" id="step-5">
            <div class="step-header"><h2>Formation</h2></div>
            <div id="education-journey">
                <!-- Degrees will be appended here -->
            </div>
            <button class="btn-secondary-cv" style="width:100%; border-style:dashed; margin-bottom:1.5rem;" onclick="addDegreeCard()"><i data-lucide="plus-circle"></i> Ajouter un diplôme</button>
            
            <textarea id="input-education" class="form-control" style="display:none;"></textarea>
            <div class="wizard-footer"><button class="btn-secondary-cv" onclick="goToStep(4)">Retour</button><button class="btn-primary-cv" onclick="goToStep(6)">Suivant: Langues</button></div>
        </div>

        <!-- STEP 6: Languages (Visual Fluency Meters) -->
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

$receiverScript = '
<style>
    .highlight-active {
        outline: 2px solid #6B34A3 !important;
        outline-offset: 4px;
        background: rgba(107, 52, 163, 0.05) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 10;
    }
    /* Universal Template Support - Ensuring complex items look good in all templates */
    .item { margin-bottom: 25px; width: 100%; text-align: left; }
    .item-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2px; gap: 15px; flex-wrap: wrap; text-align: left; }
    .item-title { font-weight: 700; font-size: 1.1rem; color: #0f172a; flex: 1; }
    .item-date { color: #64748b; font-size: 0.85rem; font-style: normal; white-space: nowrap; font-weight: 500; }
    .item-company { font-weight: 600; margin: 0 0 8px 0; color: var(--cv-accent, #2563eb); font-size: 0.95rem; text-align: left; }
    .item-desc { margin: 8px 0 0 18px; padding: 0; list-style-type: disc; text-align: left; }
    .item-desc li { margin-bottom: 4px; color: #334155; line-height: 1.5; }
    
    .skill-pill { 
        display: inline-block; 
        background: #f1f5f9; 
        color: #475569; 
        padding: 4px 12px; 
        border-radius: 6px; 
        font-size: 0.8rem; 
        margin: 0 6px 6px 0;
        border: 1px solid #e2e8f0;
        font-weight: 500;
    }
    
    .lang-row { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px; font-size: 0.9rem; }
    .lang-level { color: #64748b; font-weight: 500; }
</style>
<script>
    let isBionic = false;
    function bionify(str) {
        if (!str || typeof str !== "string") return str;
        return str.replace(/(>|^)([^<]+)(?=<|$)/g, function(match, prefix, text) {
            const words = text.split(/(\\s+)/);
            const processed = words.map(word => {
                if (word.trim().length <= 1) return word;
                const mid = Math.ceil(word.length * 0.45);
                return "<b>" + word.slice(0, mid) + "</b>" + word.slice(mid);
            }).join("");
            return prefix + processed;
        });
    }

    window.addEventListener("message", function(e) {
        if (e.data.type === "cv-update") {
            const d = e.data;
            const setVal = (sel, val, isHtml = false) => {
                const el = document.querySelectorAll(sel);
                let displayVal = val;
                const isNameOrTitle = d.field === "nomComplet" || d.field === "titrePoste";
                if (isBionic && !isNameOrTitle && typeof val === "string") {
                    displayVal = bionify(val);
                    isHtml = true;
                }
                el.forEach(e => {
                    if (d.field === "competences" && (e.id === "preview-competences" || e.classList.contains("skill-group"))) {
                        const skills = typeof val === "string" ? val.split("•").map(s=>s.trim()).filter(s=>s) : val;
                        e.innerHTML = skills.map(s => `<span class="skill-pill">${s}</span>`).join("");
                        return;
                    }
                    if (d.field === "langues" && (e.id === "preview-langues" || e.classList.contains("cv-langues"))) {
                        if (d.rawData) {
                            e.innerHTML = d.rawData.map(l => `<div class="lang-row"><strong>${l.lang}</strong><span class="lang-level">${l.level}</span></div>`).join("");
                            return;
                        }
                    }
                    if (d.field === "experience" && d.rawData) {
                        if (e.id === "preview-experience" || e.classList.contains("cv-experience")) {
                            e.innerHTML = d.rawData.map(r => {
                                if(!r.role && !r.company) return "";
                                return `<div class="item">
                                    <div class="item-header">
                                        <span class="item-title">${r.role}</span>
                                        <span class="item-date">${r.dates}</span>
                                    </div>
                                    <p class="item-company">${r.company}</p>
                                    <ul class="item-desc">
                                        ${r.achievements.map(a => a.text ? `<li>${a.text}</li>` : "").join("")}
                                    </ul>
                                </div>`;
                            }).join("");
                            return;
                        }
                    }
                    if (d.field === "formation" && d.rawData) {
                        if (e.id === "preview-formation" || e.classList.contains("cv-formation")) {
                            e.innerHTML = d.rawData.map(r => {
                                if(!r.degree && !r.school) return "";
                                return `<div class="item">
                                    <div class="item-header">
                                        <span class="item-title">${r.degree} ${r.honors ? "★" : ""}</span>
                                        <span class="item-date">${r.dates}</span>
                                    </div>
                                    <p class="item-company">${r.school}</p>
                                </div>`;
                            }).join("");
                            return;
                        }
                    }
                    if (isHtml) e.innerHTML = displayVal;
                    else e.innerText = displayVal;
                });
            };

            if (d.field === "nomComplet") setVal(".cv-name, #preview-nomComplet, h1", d.value);
            else if (d.field === "titrePoste") setVal(".cv-title, #preview-titrePoste, h2", d.value);
            else if (d.field === "resume") setVal(".summary-text, #preview-resume, .summary, .cv-summary", d.value, true);
            else if (d.field === "experience") setVal("#preview-experience, .cv-exp, .experience-list, .cv-experience", d.value, true);
            else if (d.field === "competences") setVal("#preview-competences, .cv-skills, .skills-list, .cv-competences", d.value, true);
            else if (d.field === "langues") setVal("#preview-langues, .cv-languages, .languages-list, .cv-langues", d.value, true);
            else if (d.field === "formation") setVal("#preview-formation, .cv-edu, .education-list, .cv-formation", d.value, true);
            else if (d.field === "infoContact") {
                const clean = d.value.split("|").map(s => s.trim()).join("<br>");
                setVal(".contact-info, #preview-infoContact, .cv-contact, .contact-details", clean, true);
            } else if (d.field === "photo") {
                const pi = document.querySelectorAll("#preview-photo, .cv-photo img, .profile-img, #profile-pic");
                pi.forEach(i => { i.src = d.value; i.style.display = "block"; });
                const txt = document.querySelectorAll("#photo-text, .photo-text");
                txt.forEach(t => t.style.display = "none");
            }
        } else if (e.data.type === "toggle-bionic") {
            isBionic = e.data.enabled;
            window.parent.postMessage({ type: "request-full-sync" }, "*");
        } else if (e.data.type === "highlight-section") {
            document.querySelectorAll(".highlight-active").forEach(el => {
                el.classList.remove("highlight-active");
                el.style.outline = "none";
                el.style.background = "none";
            });
            const step = e.data.step;
            const kMap = { 
                2:["résumé","summary","propos","profil"], 
                3:["expérience","experience","parcours","stages","work","emploi"], 
                4:["compétence","skills","aptitudes","technique","outils","expert"], 
                5:["formation","education","scolaire","academic","études","diplômes"], 
                6:["langue","language","linguistique","linguistiques"] 
            };
            let target = null;
            if (step === 1) target = document.querySelector(".cv-header, .header-info, h1, .sidebar-header");
            else if (kMap[step]) {
                const possibleTitles = document.querySelectorAll("h1,h2,h3,h4,h5,p,div,span");
                for (const t of possibleTitles) { 
                    const txt = t.textContent.trim().toLowerCase();
                    if (txt.length < 30 && kMap[step].some(k => txt.includes(k))) { 
                        let current = t; let best = t;
                        while(current && current.tagName !== "BODY") {
                            if (current.classList.contains("cv-section") || current.classList.contains("section")) { best = current; break; }
                            current = current.parentElement;
                        }
                        target = best; break; 
                    } 
                }
            }
            if (target) { 
                target.classList.add("highlight-active");
                target.style.outline = "3px solid #6B34A3";
                target.style.outlineOffset = "4px";
                target.scrollIntoView({ behavior:"smooth", block:"center" }); 
            }
        } else if (e.data.type === "toggle-dyslexia") {
            const existing = document.getElementById("dyslexia-style-iframe");
            if (e.data.enabled) {
                if (!existing) {
                    const style = document.createElement("style");
                    style.id = "dyslexia-style-iframe";
                    style.innerHTML = "@import url(\'https://cdn.jsdelivr.net/npm/opendyslexic@1.0.3/dist/opendyslexic.css\'); * { font-family: \'OpenDyslexic\', sans-serif !important; }";
                    document.head.appendChild(style);
                }
            } else if (existing) existing.remove();
        }
    });
</script>';

if (stripos($templateHtml, '</body>') !== false) {
    $templateHtml = str_ireplace('</body>', $receiverScript . '</body>', $templateHtml);
} else {
    $templateHtml .= $receiverScript;
}
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
    updateProgress();

    // Initial Fill
    if (INITIAL_DATA) {
        Object.entries({ 'nomComplet':'input-name', 'titrePoste':'input-title', 'email':'input-email', 'telephone':'input-phone', 'adresse':'input-location' })
            .forEach(([k, id]) => { const el = document.getElementById(id); if(el && INITIAL_DATA[k]) el.value = INITIAL_DATA[k]; });
        
        const sumEl = document.getElementById('input-summary');
        if (sumEl && INITIAL_DATA['resume']) sumEl.innerHTML = INITIAL_DATA['resume'];

        // Parse Experience
        if (INITIAL_DATA.experience) {
            const stripTags = str => { let d = document.createElement('div'); d.innerHTML = str; return (d.innerText || d.textContent || "").trim(); };
            const rolesRaw = INITIAL_DATA.experience.split('\n\n');
            currentRoles = rolesRaw.map(block => {
                const lines = block.split('\n').filter(l => l.trim());
                if(lines.length === 0) return null;
                
                const headText = stripTags(lines[0]);
                const parts = headText.split(/\s+[-—–]\s+/);
                const role = parts[0] || headText;
                const company = parts.length > 1 ? parts.slice(1).join(' - ') : '';
                
                return {
                    role: role.trim(),
                    company: company.trim(),
                    dates: lines[1] && !lines[1].includes('•') ? stripTags(lines[1]) : '',
                    achievements: lines.filter(l => l.includes('•')).map(l => ({ text: stripTags(l).replace('•', '').trim(), cat: 'Impact' }))
                };
            }).filter(r => r && r.role);
            if(currentRoles.length === 0) addRoleCard();
        } else { addRoleCard(); }
        renderRoles();
        syncExperience(); // CRITICAL FIX: Sync data to hidden fields and iframe after initial load

        // Parse Education
        if (INITIAL_DATA.formation) {
            const stripTags = str => { let d = document.createElement('div'); d.innerHTML = str; return (d.innerText || d.textContent || "").trim(); };
            const eduRaw = INITIAL_DATA.formation.split('\n\n');
            currentDegrees = eduRaw.map(block => {
                const lines = block.split('\n').filter(l => l.trim());
                if(lines.length === 0) return null;
                
                const headText = stripTags(lines[0]);
                const hasStar = headText.includes('★');
                let degree = headText.replace('★', '').trim();
                let school = '';
                let dates = '';
                
                // Handle both old format "Degree - School" and new format "Degree \n School | Dates"
                if (headText.match(/\s+[-—–]\s+/)) {
                    const parts = headText.split(/\s+[-—–]\s+/);
                    degree = parts[0].replace('★', '').trim();
                    school = parts.slice(1).join(' - ').trim();
                    dates = lines[1] ? stripTags(lines[1]) : '';
                } else {
                    const subParts = lines[1] ? stripTags(lines[1]).split('|') : [];
                    school = subParts[0]?.trim() || '';
                    dates = subParts[1]?.trim() || '';
                }
                
                return {
                    degree: degree,
                    school: school,
                    dates: dates,
                    honors: hasStar,
                    courses: lines[2]?.includes('Cours clés :') ? stripTags(lines[2]).replace('Cours clés :', '').split(',').map(c => c.trim()).filter(c => c) : []
                };
            }).filter(d => d && d.degree);
            if(currentDegrees.length === 0) addDegreeCard();
        } else { addDegreeCard(); }
        renderDegrees();
        syncEducation(); // CRITICAL FIX: Sync data to hidden fields and iframe after initial load


        // Languages
        if (INITIAL_DATA.langues) {
            currentLangs = INITIAL_DATA.langues.split('\n').filter(x=>x.trim()).map(line => {
                const p = line.split(/[—–-]/);
                return { lang: p[0]?.trim() || '', level: p[1]?.trim() || 'B1' };
            });
        } else { addLanguage(); }
        renderLanguages();
        syncLangs(); // CRITICAL FIX: Sync languages to hidden field so progress bar works

        // Skills
        if (INITIAL_DATA.competences) { 
            currentSkills = INITIAL_DATA.competences.split(',').filter(x=>x.trim()).map(s => ({ name: s.trim(), level: 'intermediate' })); 
            renderTags(); 
        }

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
    if(photoWrap) photoWrap.addEventListener('click', () => photoInput.click());
    if(photoInput) photoInput.addEventListener('change', function() {
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
                return el && (el.value || "").trim().length >= 2;
            });
        }
        else if (i === 2) {
            const el = document.getElementById('input-summary');
            done = el && el.innerText.trim().length > 10;
        }
        else if (i === 3) done = typeof currentRoles !== 'undefined' && currentRoles.length > 0 && currentRoles.some(r => r.role && r.role.trim().length > 1);
        else if (i === 4) {
            done = typeof currentSkills !== 'undefined' && currentSkills.length > 0 && currentSkills.some(s => {
                const name = typeof s === 'string' ? s : (s.name || '');
                return name.trim().length > 1;
            });
        }
        else if (i === 5) done = typeof currentDegrees !== 'undefined' && currentDegrees.length > 0 && currentDegrees.some(d => d.degree && d.degree.trim().length > 1);
        else if (i === 6) done = typeof currentLangs !== 'undefined' && currentLangs.length > 0 && currentLangs.some(l => l.lang && l.lang.trim().length > 1);
        
        markStep(i, done);
        if (done) completedSteps++;
    }
    
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
    
    // Use srcdoc for cleaner initialization
    iframe.srcdoc = TEMPLATE_HTML;
    
    // Initial sync once loaded
    iframe.onload = () => {
        syncAllData();
        scaleIframe();
    };
}

function scaleIframe() {
    const ifrm = document.getElementById('template-preview-frame');
    const wrap = document.getElementById('cv-wrapper');
    if(!ifrm || !wrap) return;
    
    // Get actual available width (ignoring padding/borders)
    const containerWidth = wrap.clientWidth; 
    const targetWidth = 794;
    
    if (containerWidth <= 0) return; // Layout not ready yet

    let scale = containerWidth / targetWidth;
    if (scale > 1) scale = 1;

    ifrm.style.transformOrigin = 'top left';
    ifrm.style.transform = `scale(${scale})`;
    ifrm.style.width = '794px';
    ifrm.style.height = '1123px';
    
    // Adjust wrapper height to prevent scrolling issues
    wrap.style.height = (1123 * scale + 20) + 'px';
}

// Aggressive triggers to ensure the scale is applied after layout settles
window.addEventListener('resize', scaleIframe);
window.addEventListener('load', () => {
    scaleIframe();
    // Re-check 3 times as layout settles
    setTimeout(scaleIframe, 300);
    setTimeout(scaleIframe, 1000);
    setTimeout(scaleIframe, 3000);
});

function toggleDyslexiaMode(enabled) {
    if (enabled) document.body.classList.add('dyslexia-mode');
    else document.body.classList.remove('dyslexia-mode');
    
    const ifrm = document.getElementById('template-preview-frame');
    if (ifrm && ifrm.contentWindow) {
        ifrm.contentWindow.postMessage({ type: 'toggle-dyslexia', enabled: enabled }, '*');
    }
    setTimeout(scaleIframe, 100);
}

function showToast(msg, icon = 'info') {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i data-lucide="${icon}" style="width:18px;height:18px;"></i> <span>${msg}</span>`;
    if (window.lucide) window.lucide.createIcons();
    toast.classList.add('show');
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => toast.classList.remove('show'), 3000);
}

window.addEventListener('keydown', (e) => {
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const ctrl = isMac ? e.metaKey : e.ctrlKey;
    const alt = e.altKey;

    // Navigation Ctrl + 1-6 (Support Digit and Numpad)
    const digitMatch = e.code.match(/^(Digit|Numpad)([1-6])$/);
    if (ctrl && digitMatch) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const targetIdx = parseInt(digitMatch[2]);
        const currentStepEl = document.querySelector('.step-content.active');
        const currentIdx = parseInt(currentStepEl.id.replace('step-', ''));

        if (targetIdx > currentIdx) {
            if (validateStep(currentIdx)) {
                goToStep(targetIdx);
                showToast(`Navigation : Étape ${targetIdx}`, 'arrow-right-circle');
            } else {
                showToast("Veuillez remplir les champs obligatoires", 'alert-circle');
                const firstErr = currentStepEl.querySelector('.is-invalid');
                if(firstErr) firstErr.focus();
            }
        } else {
            // Going backwards is always allowed
            goToStep(targetIdx);
            showToast(`Navigation : Étape ${targetIdx}`, 'arrow-right-circle');
        }
    }

    // Save Ctrl + S
    if (ctrl && e.key.toLowerCase() === 's') {
        e.preventDefault();
        const btnSave = document.getElementById('btn-save');
        if (btnSave) btnSave.click();
        showToast("Action : Sauvegarde du CV", 'save');
    }

    // AI Polish Ctrl + P
    if (ctrl && e.key.toLowerCase() === 'p') {
        e.preventDefault();
        const active = document.activeElement;
        if (active && (active.tagName === 'TEXTAREA' || active.contentEditable === 'true')) {
            const btn = active.closest('.step-content')?.querySelector('.btn-ai');
            if (btn) btn.click();
            else showToast("IA indisponible pour ce champ", 'alert-circle');
        } else {
            showToast("Cliquez dans un texte pour utiliser l'IA", 'mouse-pointer-2');
        }
    }

    // Accessibility toggles
    if (ctrl && e.key.toLowerCase() === 'd') {
        e.preventDefault();
        const tg = document.getElementById('dyslexia-toggle');
        if (tg) {
            tg.checked = !tg.checked;
            toggleDyslexiaMode(tg.checked);
            showToast(tg.checked ? "Mode Lecture Activé" : "Mode Lecture Désactivé", 'glasses');
        }
    }
    if (ctrl && e.key.toLowerCase() === 'b') {
        e.preventDefault();
        const tg = document.getElementById('bionic-toggle');
        if (tg) {
            tg.checked = !tg.checked;
            toggleBionicMode(tg.checked);
            showToast(tg.checked ? "Mode Bionique Activé" : "Mode Bionique Désactivé", 'zap');
        }
    }

    // Help Ctrl + I
    if (ctrl && e.key.toLowerCase() === 'i') {
        e.preventDefault();
        toggleHelpModal();
    }
});

function toggleHelpModal() {
    let modal = document.getElementById('power-user-help');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'power-user-help';
        modal.className = 'aptus-modal-overlay';
        modal.innerHTML = `
            <div class="aptus-modal-content" style="max-width:500px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="font-size:1.4rem; display:flex; align-items:center; gap:10px;">
                        <i data-lucide="keyboard" style="color:var(--accent-primary);"></i> Raccourcis Power User
                    </h2>
                    <button onclick="toggleHelpModal()" style="background:none; border:none; cursor:pointer; color:var(--text-tertiary);">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="shortcut-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="shortcut-item"><span>Ctrl + 1-6</span> <label>Navigation Étapes</label></div>
                    <div class="shortcut-item"><span>Ctrl + S</span> <label>Enregistrer</label></div>
                    <div class="shortcut-item"><span>Ctrl + P</span> <label>AI Polish</label></div>
                    <div class="shortcut-item"><span>Ctrl + D</span> <label>Mode Dyslexie</label></div>
                    <div class="shortcut-item"><span>Ctrl + B</span> <label>Mode Bionique</label></div>
                    <div class="shortcut-item"><span>Ctrl + I</span> <label>Aide</label></div>
                </div>
                <p style="margin-top:20px; font-size:0.8rem; color:var(--text-tertiary); text-align:center;">Devenez un pro de la création de CV au clavier !</p>
            </div>
        `;
        document.body.appendChild(modal);
        if (window.lucide) window.lucide.createIcons();
    }
    modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'flex' : 'none';
}

function toggleBionicMode(enabled) {
    const ifrm = document.getElementById('template-preview-frame');
    if (ifrm && ifrm.contentWindow) {
        ifrm.contentWindow.postMessage({ type: 'toggle-bionic', enabled: enabled }, '*');
    }
}

window.addEventListener('message', (e) => {
    if (e.data.type === 'request-full-sync') {
        syncAllData();
    }
});

// Also trigger when the iframe content loads
document.getElementById('template-preview-frame').onload = scaleIframe;

function syncField(el, directValue = null, rawData = null) {
    const map = { 
        'input-name':'nomComplet', 
        'input-title':'titrePoste', 
        'input-summary':'resume', 
        'input-experience':'experience', 
        'input-competences':'competences',
        'input-education':'formation', 
        'input-languages':'langues' 
    };
    const id = el.id || el;
    const field = map[id];
    const value = directValue !== null ? directValue : (el.value || el.innerHTML || '---');
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) {
        if(field) ifrm.contentWindow.postMessage({ type: 'cv-update', field, value: value, rawData: rawData }, '*');
        if(['input-email','input-phone','input-location'].includes(id)) {
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

/* ── Specific Systems (Dynamic Enhancements) ── */
// STEP 2: Persona Architect (Profile Alchemist)
let currentPersona = 'expert';
let currentGender = 'm';

const LANG_DB = ['Français', 'Anglais', 'Arabe', 'Allemand', 'Espagnol', 'Italien', 'Chinois', 'Japonais', 'Russe', 'Portugais'];

function setGender(g) {
    currentGender = g;
    document.getElementById('btn-male').classList.toggle('active', g === 'm');
    document.getElementById('btn-female').classList.toggle('active', g === 'f');
    updateSummaryFromMadLibs();
}

function formatSkillsList(str) {
    const skills = str.split(',').map(s => s.trim()).filter(s => s);
    if (skills.length === 0) return "";
    if (skills.length === 1) return skills[0];
    const last = skills.pop();
    return skills.join(', ') + " et " + last;
}

const personaTemplates = {
    visionary: (t, y, s) => {
        const title = `<strong>${t || (currentGender === 'm' ? "Professionnel" : "Professionnelle")}</strong>`;
        const skillsFormatted = `<strong>${formatSkillsList(s) || "mon domaine"}</strong>`;
        const passion = currentGender === 'm' ? "passionné" : "passionnée";
        const yearsStr = (y && y != "0") ? `avec <strong>${y} ans</strong> d'élan créatif` : `${passion} par l'innovation`;
        
        return `En tant que ${title} ${yearsStr}, je cherche à transformer des idées complexes en solutions créatives, en m'appuyant sur ma maîtrise de ${skillsFormatted}.`;
    },
    expert: (t, y, s) => {
        const title = `<strong>${t || "Spécialiste"}</strong>`;
        const skillsFormatted = `<strong>${formatSkillsList(s) || "mon expertise"}</strong>`;
        const focus = currentGender === 'm' ? "focalisé" : "focalisée";
        const yearsStr = (y && y != "0") ? `doté${currentGender === 'f' ? 'e' : ''} de <strong>${y} ans</strong> de rigueur technique` : `en quête d'excellence technique`;
        const adj = parseInt(y) > 5 ? (currentGender === 'm' ? "expert chevronné" : "experte chevronnée") : (currentGender === 'm' ? "spécialiste" : "spécialiste");
        
        return `${title} ${adj} ${yearsStr}, je suis ${focus} sur la maîtrise de ${skillsFormatted} pour garantir des résultats de haute qualité dès mes premiers projets.`;
    },
    leader: (t, y, s) => {
        const title = `<strong>${t || "Profil"}</strong>`;
        const skillsFormatted = `<strong>${formatSkillsList(s) || "mes compétences"}</strong>`;
        const fort = currentGender === 'm' ? "fort" : "forte";
        const yearsStr = (y && y != "0") ? `${fort}e de <strong>${y} ans</strong> d'impact` : `dynamique et moteur de projets`;
        
        return `Leader stratégique et ${title} ${yearsStr}, je mobilise mon énergie et ma maîtrise de ${skillsFormatted} pour porter des initiatives ambitieuses vers la réussite.`;
    }
};

function selectPersona(type, el) {
    currentPersona = type;
    document.querySelectorAll('.persona-card').forEach(c => c.classList.remove('active'));
    if(el) el.classList.add('active');
    updateSummaryFromMadLibs();
}

function updateSummaryFromMadLibs() {
    const t = document.getElementById('ml-title').value.trim();
    const y = document.getElementById('ml-years').value.trim();
    const s = document.getElementById('ml-skill').value.trim();
    
    const inputSum = document.getElementById('input-summary');
    if (personaTemplates[currentPersona] && inputSum) {
        const summary = personaTemplates[currentPersona](t, y, s);
        inputSum.innerHTML = summary;
        syncField('input-summary', summary);
    }
    updateProgress();
}

// STEP 3: Experience Timeline
let currentRoles = [];
function addRoleCard() {
    currentRoles.push({ role: '', company: '', dates: '', achievements: [{ text: '', cat: 'Impact' }] });
    renderRoles();
}
function removeRoleCard(idx) {
    currentRoles.splice(idx, 1);
    renderRoles();
    syncExperience();
}
function addAchievement(idx) {
    currentRoles[idx].achievements.push({ text: '', cat: 'Impact' });
    renderRoles();
}
function renderRoles() {
    const c = document.getElementById('experience-timeline'); if(!c) return;
    c.innerHTML = '';
    currentRoles.forEach((r, i) => {
        const d = document.createElement('div'); d.className = 'role-card';
        let achHtml = r.achievements.map((a, j) => `
            <div class="achievement-item" style="flex-direction:column; align-items:stretch;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <span class="achievement-cat">Mission / Tâche</span>
                    <button type="button" class="text-danger" style="background:none;border:none;cursor:pointer;" onclick="currentRoles[${i}].achievements.splice(${j},1); renderRoles(); syncExperience();">&times;</button>
                </div>
                <div style="position:relative;">
                    <textarea class="achievement-text" id="exp-desc-${i}-${j}" placeholder="Décrivez vos missions..." oninput="currentRoles[${i}].achievements[${j}].text=this.value; syncExperience();" style="width:100%; min-height:80px; padding-bottom:35px;">${a.text}</textarea>
                    <button type="button" class="btn-ai-premium" onclick="optimizeWithAI('exp-desc-${i}-${j}', 'experience', this)" style="position:absolute; bottom:5px; right:5px; padding:2px 8px; font-size:0.7rem;">
                        <i data-lucide="sparkles" style="width:10px;"></i> <span>Polish</span>
                    </button>
                </div>
            </div>
        `).join('');
        
        d.innerHTML = `
            <button type="button" class="text-danger" style="position:absolute; right:10px; top:10px; background:none; border:none; cursor:pointer; font-size:1.2rem;" onclick="removeRoleCard(${i})">&times;</button>
            <div style="display:flex; gap:10px; margin-bottom:10px; padding-right:25px;">
                <div class="input-icon-group" style="flex:1;"><i data-lucide="briefcase"></i><input type="text" class="form-control" placeholder="Poste" value="${r.role}" oninput="currentRoles[${i}].role=this.value; syncExperience();"></div>
                <div class="input-icon-group" style="flex:1;"><i data-lucide="building"></i><input type="text" class="form-control" placeholder="Entreprise" value="${r.company}" oninput="currentRoles[${i}].company=this.value; syncExperience();"></div>
            </div>
            <div class="input-icon-group" style="margin-bottom:10px;"><i data-lucide="calendar"></i><input type="text" class="form-control" placeholder="Période (ex: 2020 - Présent)" value="${r.dates}" oninput="currentRoles[${i}].dates=this.value; syncExperience();"></div>
            <div class="achievements-list">${achHtml}</div>
            <button type="button" class="btn-secondary-cv" style="width:100%; border-style:dashed; margin-top:10px;" onclick="addAchievement(${i})"><i data-lucide="plus"></i> Ajouter une mission</button>
        `;
        c.appendChild(d);
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function syncExperience() {
    let rawText = '';
    currentRoles.forEach(r => {
        if(r.role || r.company) {
            // Use HTML tags for bold and clarify period
            rawText += `<strong>${r.role} — ${r.company}</strong>\n`;
            if(r.dates) rawText += `<em style="color:#666; font-size:0.95em;">${r.dates}</em>\n`;
            r.achievements.forEach(a => { if(a.text) rawText += `• ${a.text}\n`; });
            rawText += '\n';
        }
    });
    const el = document.getElementById('input-experience');
    if(el) { el.value = rawText.trim(); syncField(el, rawText.trim(), currentRoles); }
    updateProgress();
}

// STEP 4: Skills Heatmap
function addSkillFromInput(val) {
    const input = document.getElementById('input-skill-search');
    const v = val || input.value.trim();
    if(v && !currentSkills.includes(v)) {
        currentSkills.push(v);
        if(input) input.value = '';
        renderTags();
    }
}
function renderTags() {
    const c = document.getElementById('skills-tags-container'); if(!c) return;
    c.innerHTML = '';
    currentSkills.forEach(s => {
        const name = typeof s === 'string' ? s : s.name;
        const t = document.createElement('div'); t.className = 'tag-item';
        t.innerHTML = `<span>${name}</span><button type="button" onclick="removeSkillObj('${name}')">&times;</button>`;
        c.appendChild(t);
    });
    const strArray = currentSkills.map(s => typeof s === 'string' ? s : s.name);
    document.getElementById('input-skills').value = strArray.join(',');
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'competences', value: strArray.join(' • ') }, '*');
    updateProgress();
}
function removeSkillObj(name) {
    currentSkills = currentSkills.filter(s => (typeof s === 'string' ? s : s.name) !== name);
    renderTags();
}

// STEP 5: Education Map
let currentDegrees = [];
function addDegreeCard() {
    currentDegrees.push({ degree: '', school: '', dates: '', honors: false, courses: [] });
    renderDegrees();
}
function removeDegreeCard(idx) {
    currentDegrees.splice(idx, 1);
    renderDegrees();
    syncEducation();
}
function renderDegrees() {
    const c = document.getElementById('education-journey'); if(!c) return;
    c.innerHTML = '';
    currentDegrees.forEach((d, i) => {
        const div = document.createElement('div'); div.className = 'degree-milestone';
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-right:30px;">
                <i data-lucide="star" class="honors-toggle ${d.honors?'active':''}" onclick="currentDegrees[${i}].honors=!currentDegrees[${i}].honors; renderDegrees(); syncEducation();" style="cursor:pointer;"></i>
                <button type="button" class="text-danger" style="background:none; border:none; cursor:pointer; font-size:1.1rem;" onclick="removeDegreeCard(${i})">&times;</button>
            </div>
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <div class="input-icon-group" style="flex:1;"><i data-lucide="graduation-cap"></i><input type="text" class="form-control" placeholder="Diplôme" value="${d.degree}" oninput="currentDegrees[${i}].degree=this.value; syncEducation();"></div>
                <div class="input-icon-group" style="flex:1;"><i data-lucide="school"></i><input type="text" class="form-control" placeholder="Université" value="${d.school}" oninput="currentDegrees[${i}].school=this.value; syncEducation();"></div>
            </div>
            <div class="input-icon-group"><i data-lucide="calendar"></i><input type="text" class="form-control" placeholder="Années" value="${d.dates}" oninput="currentDegrees[${i}].dates=this.value; syncEducation();"></div>
        `;
        c.appendChild(div);
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function syncEducation() {
    let rawText = '';
    currentDegrees.forEach(d => {
        if(d.degree || d.school) {
            // Degree in bold, School and Date on the same line below
            rawText += `<strong>${d.degree} ${d.honors?'★':''}</strong>\n`;
            rawText += `${d.school}${d.dates ? ' | ' + d.dates : ''}\n\n`;
        }
    });
    const el = document.getElementById('input-education');
    if(el) { el.value = rawText.trim(); syncField(el, rawText.trim(), currentDegrees); }
    updateProgress();
}

// STEP 6: Language Meters
function addLanguage() { currentLangs.push({ lang: '', level: 'B1' }); renderLanguages(); }
function removeLanguage(i) { currentLangs.splice(i, 1); renderLanguages(); syncLangs(); }
function getLangPercentage(lvl) {
    const map = { 'A1': 15, 'A2': 30, 'B1': 50, 'B2': 70, 'C1': 85, 'C2': 100 };
    return map[lvl] || 50;
}
function getFlagEmoji(lang) {
    const l = lang.toLowerCase();
    if(l.includes('fran')) return '🇫🇷';
    if(l.includes('angl') || l.includes('english')) return '🇬🇧';
    if(l.includes('arab')) return '🇹🇳';
    if(l.includes('esp')) return '🇪🇸';
    if(l.includes('ita')) return '🇮🇹';
    if(l.includes('all') || l.includes('ger')) return '🇩🇪';
    return '🌍';
}
function renderLanguages() {
    const c = document.getElementById('dynamic-languages-container'); if(!c) return;
    c.innerHTML = '';
    currentLangs.forEach((l, i) => {
        const d = document.createElement('div'); d.className = 'language-card';
        d.innerHTML = `
            <div class="language-card-header">
                <span class="language-card-title">${getFlagEmoji(l.lang)} Langue #${i+1}</span>
                <button type="button" class="text-danger" style="background:none;border:none;cursor:pointer;" onclick="removeLanguage(${i})">&times;</button>
            </div>
            <div class="input-icon-group" style="margin-bottom:10px; position:relative;">
                <i data-lucide="languages"></i>
                <input type="text" class="form-control lang-input-field" placeholder="ex: Anglais, Allemand..." value="${l.lang}" data-index="${i}" autocomplete="off">
            </div>
            <div class="level-selector">
                ${['A1','A2','B1','B2','C1','C2'].map(lvl => `
                    <div class="level-pill ${l.level===lvl?'active':''}" onclick="currentLangs[${i}].level='${lvl}'; renderLanguages(); syncLangs();">${lvl}</div>
                `).join('')}
            </div>
        `;
        c.appendChild(d);
    });

    // Re-attach listeners & suggestions to avoid focus loss bug
    document.querySelectorAll('.lang-input-field').forEach(inp => {
        setupSmartAutocomplete(inp, LANG_DB, false);
        inp.addEventListener('input', (e) => {
            const idx = e.target.dataset.index;
            currentLangs[idx].lang = e.target.value;
            syncLangs();
        });
    });
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function syncLangs() {
    const val = currentLangs.filter(l=>l.lang.trim()).map(l=>`${l.lang.trim()} - ${l.level}`).join('\n');
    document.getElementById('input-languages').value = val;
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'langues', value: val, rawData: currentLangs }, '*');
    updateProgress();
}

function setupSmartAutocomplete(inp, db, isSkill = false) {
    if (!inp) return;
    let sD = inp.parentElement.querySelector('.tag-suggestions');
    if (!sD) {
        sD = document.createElement('div'); sD.className = 'tag-suggestions';
        inp.parentElement.style.position = 'relative'; inp.parentElement.appendChild(sD);
    }
    inp.addEventListener('input', () => {
        const v = inp.value.toLowerCase().trim(); sD.innerHTML = '';
        if (v.length < 1) { sD.style.display = 'none'; return; }
        const m = db.filter(x => x.toLowerCase().includes(v)).slice(0, 5);
        if (m.length > 0) {
            m.forEach(match => {
                const d = document.createElement('div'); d.className = 'tag-suggest-item'; d.innerHTML = match;
                d.onclick = () => {
                    if (isSkill) { addSkillFromInput(match); inp.value = ''; }
                    else { inp.value = match; inp.dispatchEvent(new Event('input')); }
                    sD.style.display = 'none';
                };
                sD.appendChild(d);
            });
            sD.style.display = 'block';
        } else { sD.style.display = 'none'; }
    });
    document.addEventListener('click', (e) => { if (e.target !== inp && e.target !== sD) sD.style.display = 'none'; });
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
    const d = { 
        cv_id: CV_ID, 
        template_id: TEMPLATE_ID, 
        name: document.getElementById('input-name').value.trim(), 
        title: document.getElementById('input-title').value.trim(), 
        email: document.getElementById('input-email').value.trim(), 
        phone: document.getElementById('input-phone').value.trim(), 
        location: document.getElementById('input-location').value.trim(), 
        summary: document.getElementById('input-summary').innerHTML.trim(), 
        experience: document.getElementById('input-experience').value, 
        skills: document.getElementById('input-skills').value, 
        education: document.getElementById('input-education').value, 
        languages: document.getElementById('input-languages').value, 
        photo: document.getElementById('photo-b64').value, 
        color_theme: document.getElementById('color-picker').value 
    };
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
    
    // Fake progress text (Looping for longer generation)
    const progText = document.getElementById('audit-status-text');
    const progBar = document.getElementById('audit-progress');
    const steps = [
        "Lecture du contenu du CV...", 
        "Analyse du score ATS...", 
        "Vérification des mots-clés...", 
        "L'IA (Llama 3.2 Rapide) réfléchit intensément...",
        "Génération des recommandations détaillées...",
        "Veuillez patienter (génération ultra-rapide en cours)..."
    ];
    let stepIndex = 0;
    const interval = setInterval(() => {
        stepIndex++;
        if (stepIndex >= steps.length) {
            stepIndex = 3; // Loop back to 'L'IA réfléchit'
        }
        progText.textContent = steps[stepIndex];
        // Pseudo-progress bar that slows down but never quite stops until done
        const currentWidth = parseFloat(progBar.style.width || 0);
        if (currentWidth < 95) {
            progBar.style.width = (currentWidth + (95 - currentWidth) * 0.1) + '%';
        }
    }, 3000);

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
            let finalContext = context;
            if (context === 'summary' && typeof currentPersona !== 'undefined') {
                finalContext = `summary_as_${currentPersona}`;
            }

            const response = await fetch('ajax_ai_polish.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: textRaw, context: finalContext })
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
