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

<style>
    /* Premium Audit Dashboard V3 */
    .ats-score-badge {
        width: 110px;
        height: 110px;
        background: #ef4444 !important; /* Force Red as requested */
        color: white !important;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 800;
        box-shadow: 0 12px 25px rgba(239, 68, 68, 0.4);
        flex-shrink: 0;
    }
    .audit-card-v3 {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .audit-card-v3.strengths { border-left: 6px solid #10b981; }
    .audit-card-v3.weaknesses { border-left: 6px solid #f59e0b; }
    
    .audit-card-v3 .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }
    .audit-card-v3 h4 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .audit-card-v3.strengths h4 { color: #10b981; }
    .audit-card-v3.weaknesses h4 { color: #f59e0b; }
    
    .audit-card-v3 ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .audit-card-v3 li {
        margin-bottom: 15px;
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.6;
        padding-left: 0;
        position: relative;
    }
    .audit-card-v3 li:last-child { margin-bottom: 0; }

    /* --- ADVANCED FEATURES STYLES --- */
    :root { --stat-teal: #10b981; }
    
    .aptus-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .aptus-switch input { opacity: 0; width: 0; height: 0; }
    .aptus-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 24px; }
    .aptus-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .aptus-slider { background-color: var(--stat-teal); }
    input:checked + .aptus-slider:before { transform: translateX(20px); }
    
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    .role-card { position: relative; transition: all 0.3s ease; }
    .role-card:hover { border-color: var(--accent-primary); }
    
    .btn-ai-premium { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-ai-premium:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 4px 12px rgba(107, 52, 163, 0.3); }
    
    .magic-fill-container:hover { border-color: var(--accent-primary); background: linear-gradient(135deg, rgba(107, 52, 163, 0.08) 0%, rgba(59, 130, 246, 0.08) 100%); }

    /* --- SOFT SKILLS CLUSTERS --- */
    .soft-skills-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-top: 15px;
    }
    .soft-skill-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .soft-skill-card:hover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.04);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.1);
    }
    .soft-skill-card.active {
        border-color: #10b981;
        background: #10b981;
        color: white;
    }
    .soft-skill-card i {
        width: 24px;
        height: 24px;
        transition: transform 0.3s ease;
    }
    .soft-skill-card.active i { color: white; transform: scale(1.1); }
    .soft-skill-card span { font-size: 0.8rem; font-weight: 700; }

    .skill-suggestions-box {
        margin-top: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .chip-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .skill-chip {
        padding: 6px 12px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .skill-chip:hover {
        border-color: #10b981;
        color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }
    .skill-chip.selected {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* --- MODAL SYSTEM --- */
    .aptus-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .aptus-modal-overlay.active {
        display: flex;
    }
    .aptus-modal-content {
        background: white;
        padding: 35px;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 500px;
        position: relative;
        animation: modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes modalSlideIn {
        from { transform: scale(0.9) translateY(20px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }
</style>

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

            <!-- MAGIC AUTO-FILL SECTION -->
            <div class="magic-fill-container" style="background: linear-gradient(135deg, rgba(107, 52, 163, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%); border: 2px dashed var(--accent-primary); border-radius: 20px; padding: 25px; margin-bottom: 30px; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                <div style="position: absolute; top: -20px; right: -20px; opacity: 0.1; transform: rotate(15deg);">
                    <i data-lucide="sparkles" style="width: 120px; height: 120px; color: var(--accent-primary);"></i>
                </div>
                <div style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
                    <div style="background: var(--accent-primary); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(107, 52, 163, 0.3);">
                        <i data-lucide="wand-2" style="width: 22px;"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-primary);">Remplissage Magique IA</h3>
                        <p style="margin:0; font-size:0.85rem; color:var(--text-tertiary);">Gagnez du temps ! Collez votre LinkedIn ou votre ancien CV.</p>
                    </div>
                </div>
                
                <div id="magic-input-area">
                    <textarea id="magic-paste-text" class="form-control" placeholder="Collez ici le texte de votre profil LinkedIn ou le contenu de votre CV..." style="min-height: 100px; background: white; border-radius: 12px; margin-bottom: 12px; font-size: 0.9rem; line-height: 1.5;"></textarea>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-ai-premium" id="btn-magic-fill" onclick="runMagicFill()" style="flex: 2; justify-content: center; height: 45px;">
                            <i data-lucide="zap"></i> <span>Analyser & Remplir Tout</span>
                        </button>
                        <button type="button" class="btn-secondary-cv" onclick="document.getElementById('magic-paste-text').value=''" style="flex: 1; height: 45px; border-style: solid;">
                            <i data-lucide="trash-2"></i> Effacer
                        </button>
                    </div>
                </div>

                <div id="magic-loading" style="display:none; text-align:center; padding: 20px 0;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; margin-bottom: 15px;"></div>
                    <p style="font-weight: 700; color: var(--accent-primary); animation: pulse 1.5s infinite;">L'IA structure vos données... ✨</p>
                </div>
            </div>
            
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
                      oninput="syncField(this, this.innerHTML)"></div>

                <button type="button" class="btn-ai-premium" onclick="openAIPolishModal('input-summary', 'summary', this)" style="bottom:12px; right:12px; position:absolute;">
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
            <div class="step-header">
                <h2>Vos Compétences</h2>
                <p class="help-text">Distinguez vos savoir-faire techniques de vos qualités humaines.</p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 35px;">
                <!-- TOP: Hard Skills -->
                <div style="background: rgba(107, 52, 163, 0.02); padding: 20px; border-radius: 16px; border: 1px solid rgba(107, 52, 163, 0.1);">
                    <h3 style="font-size:1.1rem; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                        <i data-lucide="cpu" style="color:var(--accent-primary); width:20px;"></i> Compétences Techniques
                    </h3>
                    <div class="form-group" style="margin-bottom:0;">
                        <div style="display:flex; gap:10px; position:relative;">
                            <div class="input-icon-group" style="flex:1;">
                                <i data-lucide="search"></i>
                                <input type="text" id="input-skill-search" class="form-control" placeholder="ex: React, Python, Finance, Gestion de projet..." autocomplete="off" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addSkillFromInput(); }">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM: Soft Skills Clusters -->
                <div>
                    <h3 style="font-size:1.1rem; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                        <i data-lucide="heart" style="color:#10b981; width:20px;"></i> Qualités Humaines (Soft Skills)
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-tertiary); margin-bottom: 15px;">Sélectionnez une catégorie ou tapez votre propre qualité :</p>
                    
                    <!-- Barre de recherche manuelle pour Soft Skills -->
                    <div class="input-icon-group" style="margin-bottom: 15px;">
                        <i data-lucide="sparkles" style="color:#10b981;"></i>
                        <input type="text" id="input-soft-skill-search" class="form-control" placeholder="Ajouter manuellement (ex: Diplomatie, Empathie...)" autocomplete="off" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addSkillFromInput(null, 'soft'); }">
                    </div>

                    <div class="soft-skills-grid">
                        <div class="soft-skill-card" onclick="toggleSkillCluster('communication', this)">
                            <i data-lucide="message-square" style="color:#10b981;"></i>
                            <span>Communication</span>
                        </div>
                        <div class="soft-skill-card" onclick="toggleSkillCluster('leadership', this)">
                            <i data-lucide="crown" style="color:#f59e0b;"></i>
                            <span>Leadership</span>
                        </div>
                        <div class="soft-skill-card" onclick="toggleSkillCluster('organisation', this)">
                            <i data-lucide="layers" style="color:#3b82f6;"></i>
                            <span>Organisation</span>
                        </div>
                        <div class="soft-skill-card" onclick="toggleSkillCluster('adaptabilite', this)">
                            <i data-lucide="zap" style="color:#ef4444;"></i>
                            <span>Adaptabilité</span>
                        </div>
                    </div>

                    <!-- Suggestions contextuelles -->
                    <div id="soft-skill-suggestions" class="skill-suggestions-box">
                        <p style="font-size:0.75rem; font-weight:700; color:var(--text-tertiary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Suggestions pour <span id="cluster-name-display" style="color:#10b981;">---</span> :</p>
                        <div id="chips-container" class="chip-container"></div>
                    </div>
                </div>
            </div>

            <!-- Global Tags Container (Filtered display) -->
            <div style="margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color);">
                <div id="skills-tags-container" class="tags-container" style="border:none; padding:0; background:transparent;">
                    <!-- Les tags s'afficheront ici -->
                </div>
            </div>

            <input type="hidden" id="input-skills" value="">
            
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

            <!-- MULTILINGUAL TRANSLATION SECTION -->
            <div class="translation-container" style="margin-top:30px; border-top:1px solid var(--border-color); padding-top:20px;">
                <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; font-weight:700; color:var(--text-primary);">
                    <i data-lucide="languages" style="color:var(--accent-primary); width:18px;"></i> Traduction Polyglotte IA
                </label>
                <div style="display:flex; gap:10px;">
                    <select id="target-lang-select" class="form-control" style="flex:1; border-radius:12px; height:45px; background:var(--bg-secondary);">
                        <option value="Anglais">🇬🇧 Anglais (UK/US)</option>
                        <option value="Espagnol">🇪🇸 Espagnol</option>
                        <option value="Allemand">🇩🇪 Allemand</option>
                        <option value="Italien">🇮🇹 Italien</option>
                        <option value="Arabe">🇹🇳 Arabe</option>
                        <option value="Français">🇫🇷 Français</option>
                    </select>
                    <button type="button" class="btn-ai-premium" id="btn-translate-cv" onclick="runTranslateCV()" style="padding: 0 20px; height:45px; border-radius:12px;">
                        <i data-lucide="refresh-cw" id="translate-icon"></i> <span id="translate-btn-text">Traduire Tout</span>
                    </button>
                </div>
                <p style="font-size:0.75rem; color:var(--text-tertiary); margin-top:8px;">
                    L'IA adapte intelligemment les termes techniques au marché cible.
                </p>
            </div>

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
    <aside class="builder-preview-area" id="cv-wrapper" style="position:relative;">
        <!-- SMART FIT CONTROLS -->
        <div class="smart-fit-toolbar" style="background: white; padding: 10px 15px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; border-radius: 12px 12px 0 0;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:10px; height:10px; background:#10b981; border-radius:50%; animation: pulse 2s infinite;"></div>
                <span style="font-size:0.8rem; font-weight:700; color:var(--text-primary);">Smart-Fit Auto</span>
            </div>
            <label class="aptus-switch">
                <input type="checkbox" id="toggle-smart-fit" onchange="toggleSmartFit(this.checked)">
                <span class="aptus-slider round"></span>
            </label>
        </div>
        <iframe id="template-preview-frame"></iframe>
        
        <div id="smart-fit-indicator" style="display:none; position:absolute; bottom:20px; left:50%; transform:translateX(-50%); background:rgba(15,23,42,0.9); color:white; padding:8px 16px; border-radius:20px; font-size:0.75rem; font-weight:600; z-index:100; pointer-events:none;">
            <i data-lucide="shrink" style="width:14px; height:14px; vertical-align:middle; margin-right:5px;"></i> Ajustement en cours...
        </div>
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
        <h3 id="audit-main-title" style="margin-bottom:0.5rem;">Audit IA en cours...</h3>
        <p id="audit-status-text" style="color:var(--text-tertiary); font-family:monospace; margin-bottom: 2rem;">Lecture du contenu du CV...</p>
        <div style="background:var(--bg-secondary); height:6px; border-radius:3px; overflow:hidden;">
            <div id="audit-progress" style="width:10%; height:100%; background:linear-gradient(90deg, #10b981, #3b82f6); transition:width 0.4s ease;"></div>
        </div>
    </div>

    <!-- Étape 3 : Le Dashboard V3 -->
    <div id="ai-audit-dashboard" class="aptus-modal-content" style="display:none; max-width: 750px; text-align:left; border-radius: 24px; padding: 40px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 35px; gap: 20px;">
            <div>
                <h2 style="font-size:2.2rem; font-weight:800; display:flex; align-items:center; gap:15px; color:#1e293b; margin:0;">
                    <i data-lucide="bar-chart-2" style="color:var(--accent-primary); width:36px; height:36px;"></i> Rapport d'Audit ATS
                </h2>
                <p style="color:#64748b; margin-top:8px; font-size:1.1rem; font-weight:500;">
                    Score de compatibilité avec les systèmes ATS de recrutement
                </p>
            </div>
            <div class="ats-score-badge" id="ats-score-circle">
                <span id="ats-score-value">0</span>%
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom:35px;">
            <div class="audit-card-v3 strengths">
                <div class="card-header">
                    <i data-lucide="check-circle" style="width:24px; height:24px; color:#10b981;"></i>
                    <h4>Points Forts</h4>
                </div>
                <ul id="ats-strengths" style="padding-left:0;"></ul>
            </div>
            <div class="audit-card-v3 weaknesses">
                <div class="card-header">
                    <i data-lucide="alert-circle" style="width:24px; height:24px; color:#f59e0b;"></i>
                    <h4>À Améliorer</h4>
                </div>
                <ul id="ats-weaknesses" style="padding-left:0;"></ul>
            </div>
        </div>

        <button class="btn-modal-confirm" style="width:100%; padding: 18px; font-size: 1.15rem; border-radius: 16px; background: #6D3AB7; box-shadow: 0 8px 20px rgba(109, 58, 183, 0.3); font-weight: 700;" onclick="window.location.href='cv_my.php'">
            Terminer et aller à Mes CVs
        </button>
    </div>
</div>

<!-- MODALE IA POLISH -->
<div id="ai-polish-modal" class="aptus-modal-overlay">
    <div class="aptus-modal-content" style="max-width: 450px; text-align: center;">
        <div class="modal-icon-circle" style="background: rgba(107, 52, 163, 0.1); color: var(--accent-primary); margin: 0 auto 1.5rem auto; display:flex; align-items:center; justify-content:center;">
            <i data-lucide="sparkles" style="width: 40px; height: 40px;"></i>
        </div>
        <h2 style="font-size:1.5rem; margin-bottom:0.5rem;">Optimisation IA</h2>
        <p style="color:var(--text-secondary); margin-bottom:1.5rem; font-size:0.9rem;">
            Comment souhaitez-vous améliorer votre texte ?
        </p>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button class="btn-modal-confirm" onclick="applyAIPolish('correct')" style="display:flex; align-items:center; justify-content:center; gap:10px;">
                <i data-lucide="languages" style="width:18px;"></i> Correction Linguistique
            </button>
            <button class="btn-modal-confirm" onclick="applyAIPolish('polish')" style="background: var(--gradient-primary); display:flex; align-items:center; justify-content:center; gap:10px;">
                <i data-lucide="sparkles" style="width:18px;"></i> Reformuler Professionnellement
            </button>
            <button class="btn-secondary-cv" style="border:none; margin-top:5px;" onclick="closeAIPolishModal()">Annuler</button>
        </div>
    </div>
</div>

<!-- MODALE ROI CALCULATOR -->
<div id="ai-roi-modal" class="aptus-modal-overlay">
    <div class="aptus-modal-content" style="max-width: 500px; text-align: left;">
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
            <div class="modal-icon-circle" style="background: rgba(16, 185, 129, 0.1); color: #10b981; margin:0;">
                <i data-lucide="trending-up" style="width: 30px; height: 30px;"></i>
            </div>
            <h2 style="font-size:1.4rem; margin:0;">Calculateur d'Impact (ROI)</h2>
        </div>
        
        <div id="roi-step-1">
            <p style="color:var(--text-secondary); margin-bottom:1.5rem; font-size:0.95rem; line-height:1.5;">
                L'IA va vous aider à quantifier cette mission. Quelle est la mesure principale de votre succès ?
            </p>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:20px;">
                <button class="btn-secondary-cv" onclick="selectROIMetric('Productivité / Temps')" style="font-size:0.8rem; border-style:solid; padding:10px;">⏱️ Temps / Prod</button>
                <button class="btn-secondary-cv" onclick="selectROIMetric('Argent / Chiffre d\'affaires')" style="font-size:0.8rem; border-style:solid; padding:10px;">💰 Argent / CA</button>
                <button class="btn-secondary-cv" onclick="selectROIMetric('Qualité / Satisfaction')" style="font-size:0.8rem; border-style:solid; padding:10px;">⭐ Qualité / CSAT</button>
                <button class="btn-secondary-cv" onclick="selectROIMetric('Volume / Quantité')" style="font-size:0.8rem; border-style:solid; padding:10px;">📦 Volume / Qté</button>
            </div>
            <input type="text" id="roi-user-value" class="form-control" placeholder="Ex: 20%, 50k€, 30 personnes..." style="margin-bottom:20px;">
            <button class="btn-modal-confirm" onclick="generateROISuggestion()" style="width:100%; background:var(--stat-teal); border:none;">Générer l'Impact ✨</button>
        </div>

        <div id="roi-step-2" style="display:none;">
            <div style="background:var(--bg-secondary); padding:15px; border-radius:12px; border:1px solid var(--border-color); margin-bottom:20px;">
                <p id="roi-suggestion-text" style="font-size:0.95rem; font-style:italic; color:var(--text-primary); margin:0; line-height:1.6;"></p>
            </div>
            <div style="display:flex; gap:10px;">
                <button class="btn-modal-confirm" onclick="applyROISuggestion()" style="flex:2;">Appliquer au CV</button>
                <button class="btn-secondary-cv" onclick="resetROI()" style="flex:1; border-style:solid;">Recommencer</button>
            </div>
        </div>
        
        <button class="btn-secondary-cv" style="border:none; margin-top:15px; width:100%;" onclick="closeROIModal()">Annuler</button>
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
    /* Reset margins for consistent spacing across templates */
    h1, h2, h3, h4, h5, h6, p, ul, ol, li { margin: 0; padding: 0; }
    
    /* Universal Template Support - Ensuring complex items look good in all templates */
    .item { margin-bottom: 12px; width: 100%; text-align: left; }
    .item-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0px; gap: 10px; flex-wrap: wrap; text-align: left; }
    .item-title { font-weight: 700; font-size: 1.05rem; color: #0f172a; flex: 1; margin: 0; }
    .item-date { color: #64748b; font-size: 0.8rem; font-style: normal; white-space: nowrap; font-weight: 500; }
    .item-company { font-weight: 600; margin: 0; color: var(--cv-accent, #2563eb); font-size: 0.9rem; text-align: left; line-height: 1.2; }
    .item-desc { margin: 4px 0 0 15px; padding: 0; list-style-type: disc; text-align: left; }
    .item-desc li { margin-bottom: 2px; color: #334155; line-height: 1.4; font-size: 0.85rem; }
    
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
const TITLES_DB = [
    'Développeur Full-Stack', 'Data Scientist', 'Chef de Projet IT', 'UX Designer', 'Designer Graphique',
    'Comptable Senior', 'Analyste Financier', 'Contrôleur de Gestion', 
    'Chef de Cuisine', 'Pâtissier', 'Maître d\'Hôtel', 
    'Commercial Sédentaire', 'Business Developer', 'Responsable Marketing', 'Social Media Manager',
    'Infirmier', 'Aide-Soignant', 'Pharmacien',
    'Architecte', 'Ingénieur Civil', 'Conducteur de Travaux', 'Électricien',
    'Avocat', 'Juriste d\'Entreprise', 'Assistant RH', 'Recruteur'
];
const LOCATIONS_DB = [
    'Paris, France', 'Lyon, France', 'Marseille, France', 'Bordeaux, France', 'Lille, France',
    'Tunis, Tunisie', 'Sousse, Tunisie', 'Sfax, Tunisie', 'Hammamet, Tunisie',
    'Casablanca, Maroc', 'Rabat, Maroc', 'Marrakech, Maroc', 'Alger, Algérie',
    'Genève, Suisse', 'Bruxelles, Belgique', 'Montréal, Canada', 'Québec, Canada',
    'New York, USA', 'Londres, UK', 'Berlin, Allemagne', 'Madrid, Espagne', 'Remote / Télétravail'
];
const LANGUAGES_DB = ['Français', 'Anglais', 'Arabe', 'Allemand', 'Espagnol', 'Italien', 'Portugais', 'Russe', 'Chinois', 'Japonais'];
const SKILL_DB = [
    'PHP', 'JavaScript', 'React', 'Vue.js', 'Angular', 'Node.js', 'Python', 'SQL', 'Git', 'Docker',
    'Photoshop', 'Illustrator', 'Figma', 'Indesign', 'Adobe Premiere',
    'HACCP', 'Gestion des stocks', 'Cuisine Française', 'Pâtisserie fine',
    'Audit Financier', 'Liasse Fiscale', 'SAP', 'Sage', 'IFRS', 'Fiscalité',
    'SEO', 'Google Ads', 'Copywriting', 'CRM', 'Stratégie Marketing',
    'AutoCAD', 'BIM', 'Gestion de chantier', 'Réglementation Thermique',
    'Droit du Travail', 'Gestion de la Paie', 'Recrutement IT', 'Formation'
];

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
    setupSmartAutocomplete(document.getElementById('input-soft-skill-search'), SOFT_SKILLS_DB, true); // true for isSkill but we handle type in addSkillFromInput
    setupSmartAutocomplete(document.getElementById('input-title'), TITLES_DB);
    setupSmartAutocomplete(document.getElementById('input-location'), LOCATIONS_DB);

    // Correction linguistique auto sur perte de focus (blur)
    document.getElementById('input-title').addEventListener('blur', function() {
        this.value = smartLinguisticFix(this.value, 'title');
        syncField(this);
    });
    document.getElementById('input-location').addEventListener('blur', function() {
        this.value = smartLinguisticFix(this.value, 'location');
        syncField(this);
    });
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
    const styleId = "dyslexia-style-parent";
    let existing = document.getElementById(styleId);
    
    if (enabled) {
        document.body.classList.add('dyslexia-mode');
        if (!existing) {
            const style = document.createElement("style");
            style.id = styleId;
            style.innerHTML = `
                @import url('https://cdn.jsdelivr.net/npm/opendyslexic@1.0.3/dist/opendyslexic.css');
                body.dyslexia-mode * { 
                    font-family: 'OpenDyslexic', sans-serif !important; 
                    line-height: 1.6 !important;
                    letter-spacing: 0.03em !important;
                }
            `;
            document.head.appendChild(style);
        }
    } else {
        document.body.classList.remove('dyslexia-mode');
        if (existing) existing.remove();
    }
    
    const ifrm = document.getElementById('template-preview-frame');
    if (ifrm && ifrm.contentWindow) {
        ifrm.contentWindow.postMessage({ type: 'toggle-dyslexia', enabled: enabled }, '*');
    }
    setTimeout(scaleIframe, 150);
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

/* ── ADVANCED FEATURES : MAGIC FILL ── */
async function runMagicFill() {
    const text = document.getElementById('magic-paste-text').value.trim();
    if (!text) {
        showToast("Veuillez coller du texte d'abord.", 'alert-circle');
        return;
    }

    const inputArea = document.getElementById('magic-input-area');
    const loadingArea = document.getElementById('magic-loading');
    
    inputArea.style.display = 'none';
    loadingArea.style.display = 'block';

    try {
        const response = await fetch('ajax_ai_autofill.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: text })
        });
        const result = await response.json();

        if (result.success && result.data) {
            populateFormWithJSON(result.data);
            showToast("CV rempli magiquement ! ✨", 'sparkles');
            // Hide magic area after success
            document.querySelector('.magic-fill-container').style.display = 'none';
        } else {
            showToast("Erreur IA : " + (result.error || "Inconnu"), 'alert-circle');
            inputArea.style.display = 'block';
            loadingArea.style.display = 'none';
        }
    } catch (e) {
        showToast("Erreur de connexion.", 'alert-circle');
        inputArea.style.display = 'block';
        loadingArea.style.display = 'none';
    }
}

function populateFormWithJSON(data) {
    // 1. Infos de base
    if(data.nomComplet) document.getElementById('input-name').value = data.nomComplet;
    if(data.titrePoste) document.getElementById('input-title').value = data.titrePoste;
    if(data.email) document.getElementById('input-email').value = data.email;
    if(data.telephone) document.getElementById('input-phone').value = data.telephone;
    if(data.adresse) document.getElementById('input-location').value = data.adresse;
    
    // 2. Résumé
    if(data.resume) document.getElementById('input-summary').innerHTML = data.resume;

    // 3. Expériences
    if(data.experience && Array.isArray(data.experience)) {
        currentRoles = data.experience.map(r => ({
            role: r.role || '',
            company: r.company || '',
            dates: r.dates || '',
            achievements: (r.achievements || []).map(a => ({ text: a.text || '', cat: 'Impact' }))
        }));
        renderRoles();
        syncExperience();
    }

    // 4. Compétences
    if(data.skills && Array.isArray(data.skills)) {
        currentSkills = data.skills;
        renderTags();
    }

    // 5. Éducation
    if(data.education && Array.isArray(data.education)) {
        currentDegrees = data.education.map(e => ({
            degree: e.degree || '',
            school: e.school || '',
            dates: e.dates || '',
            honors: false,
            courses: []
        }));
        renderDegrees();
        syncEducation();
    }

    // 6. Langues
    if(data.languages && Array.isArray(data.languages)) {
        currentLangs = data.languages.map(l => ({
            lang: l.lang || '',
            level: l.level || 'B1'
        }));
        renderLanguages();
        syncLangs();
    }

    // Update everything
    syncAllData();
    updateProgress();
}

/* ── ADVANCED FEATURES : TRANSLATION ── */
async function runTranslateCV() {
    const lang = document.getElementById('target-lang-select').value;
    const btn = document.getElementById('btn-translate-cv');
    const icon = document.getElementById('translate-icon');
    const txt = document.getElementById('translate-btn-text');

    if (!confirm(`Voulez-vous traduire tout votre CV en ${lang} ? Les textes actuels seront remplacés par la version traduite.`)) return;

    // Build current CV object for translation
    const currentCV = {
        nomComplet: document.getElementById('input-name').value,
        titrePoste: document.getElementById('input-title').value,
        resume: document.getElementById('input-summary').innerHTML,
        experience: currentRoles,
        education: currentDegrees,
        skills: currentSkills.map(s => typeof s === 'string' ? s : s.name),
        languages: currentLangs
    };

    btn.disabled = true;
    icon.classList.add('animate-spin');
    txt.textContent = 'Traduction...';

    try {
        const response = await fetch('ajax_ai_translate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cvData: currentCV, targetLang: lang })
        });
        const result = await response.json();

        if (result.success && result.data) {
            populateFormWithJSON(result.data);
            showToast(`Traduction en ${lang} terminée ! 🌍`, 'globe');
        } else {
            showToast("Erreur IA : " + (result.error || "Inconnu"), 'alert-circle');
        }
    } catch (e) {
        showToast("Erreur de connexion.", 'alert-circle');
    } finally {
        btn.disabled = false;
        icon.classList.remove('animate-spin');
        txt.textContent = 'Traduire Tout';
    }
}

/* ── ADVANCED FEATURES : ROI CALCULATOR ── */
let roiActiveRoleIdx = null;
let roiActiveAchIdx = null;
let roiSelectedMetric = "";

function openROICalculator(roleIdx, achIdx) {
    roiActiveRoleIdx = roleIdx;
    roiActiveAchIdx = achIdx;
    document.getElementById('ai-roi-modal').classList.add('active');
    resetROI();
}

function closeROIModal() {
    document.getElementById('ai-roi-modal').classList.remove('active');
}

function selectROIMetric(metric) {
    roiSelectedMetric = metric;
    showToast(`Métrique choisie : ${metric}`, 'check-circle');
}

async function generateROISuggestion() {
    const userVal = document.getElementById('roi-user-value').value.trim();
    const originalText = currentRoles[roiActiveRoleIdx].achievements[roiActiveAchIdx].text;

    if (!userVal) {
        showToast("Veuillez entrer une valeur (chiffre, %, etc.)", 'alert-circle');
        return;
    }

    const btn = document.querySelector('#roi-step-1 .btn-modal-confirm');
    btn.disabled = true;
    btn.textContent = 'Calcul de l\'impact...';

    try {
        const response = await fetch('ajax_ai_roi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                text: originalText, 
                value: userVal, 
                metric: roiSelectedMetric,
                role: currentRoles[roiActiveRoleIdx].role
            })
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('roi-suggestion-text').textContent = result.suggestion;
            document.getElementById('roi-step-1').style.display = 'none';
            document.getElementById('roi-step-2').style.display = 'block';
        } else {
            showToast("Erreur : " + result.error, 'alert-circle');
        }
    } catch (e) {
        showToast("Erreur de connexion.", 'alert-circle');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Générer l\'Impact ✨';
    }
}

function applyROISuggestion() {
    const sug = document.getElementById('roi-suggestion-text').textContent;
    currentRoles[roiActiveRoleIdx].achievements[roiActiveAchIdx].text = sug;
    renderRoles();
    syncExperience();
    closeROIModal();
    showToast("Impact ROI appliqué ! 📈", 'trending-up');
}

function resetROI() {
    document.getElementById('roi-step-1').style.display = 'block';
    document.getElementById('roi-step-2').style.display = 'none';
    document.getElementById('roi-user-value').value = '';
    roiSelectedMetric = "";
}

/* ── ADVANCED FEATURES : SMART-FIT AUTO ── */
let smartFitEnabled = false;
let smartFitBaseSpacing = 1.0;
let smartFitBaseFont = 1.0;

function toggleSmartFit(enabled) {
    smartFitEnabled = enabled;
    if (enabled) {
        runSmartFit();
        showToast("Smart-Fit activé : l'IA ajuste votre mise en page. 📏", 'shrink');
    } else {
        resetSmartFit();
    }
}

function runSmartFit() {
    if (!smartFitEnabled) return;
    const ifrm = document.getElementById('template-preview-frame');
    if (!ifrm || !ifrm.contentDocument) return;

    const doc = ifrm.contentDocument;
    const body = doc.body;
    const indicator = document.getElementById('smart-fit-indicator');

    // Target Height for A4 at 96dpi is roughly 1123px
    const targetHeight = 1120; 
    let currentHeight = body.scrollHeight;

    if (currentHeight > targetHeight) {
        indicator.style.display = 'block';
        
        // Recursive shrink
        let attempts = 0;
        let spacing = 1.0;
        let fontSize = 1.0;

        const shrink = () => {
            if (body.scrollHeight > targetHeight && attempts < 20) {
                spacing -= 0.05;
                fontSize -= 0.01;
                doc.documentElement.style.setProperty('--cv-spacing', spacing);
                doc.documentElement.style.setProperty('--font-size-base', fontSize + 'rem');
                
                // Also target common template classes if they don't use variables
                doc.querySelectorAll('.cv-section, .item, .item-desc li').forEach(el => {
                    el.style.marginBottom = (parseFloat(getComputedStyle(el).marginBottom) * 0.9) + 'px';
                });

                attempts++;
                setTimeout(shrink, 50);
            } else {
                indicator.style.display = 'none';
                if (attempts >= 20) showToast("Ajustement maximum atteint.", 'alert-triangle');
            }
        };
        shrink();
    }
}

function resetSmartFit() {
    const ifrm = document.getElementById('template-preview-frame');
    if (!ifrm || !ifrm.contentDocument) return;
    const doc = ifrm.contentDocument;
    doc.documentElement.style.removeProperty('--cv-spacing');
    doc.documentElement.style.removeProperty('--font-size-base');
    // Reload iframe to clear manual styles
    ifrm.srcdoc = TEMPLATE_HTML;
    setTimeout(syncAllData, 100);
}

// Hook into syncAllData to auto-trigger smart fit
const originalSyncAllData = syncAllData;
syncAllData = function() {
    originalSyncAllData();
    if (smartFitEnabled) {
        setTimeout(runSmartFit, 500); // Wait for render
    }
};

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
                    
                    <button type="button" class="btn-ai-premium" onclick="openAIPolishModal('exp-desc-${i}-${j}', 'experience', this)" style="position:absolute; bottom:5px; right:5px; padding:2px 8px; font-size:0.7rem;">
                        <i data-lucide="sparkles" style="width:10px;"></i> <span>Polish</span>
                    </button>
                    <button type="button" class="btn-ai-premium" onclick="openROICalculator('${i}', '${j}')" style="position:absolute; bottom:5px; left:5px; padding:2px 8px; font-size:0.7rem; background:var(--stat-teal); border:none;">
                        <i data-lucide="trending-up" style="width:10px;"></i> <span>Impact ROI</span>
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
const softSkillClusters = {
    communication: ["Écoute active", "Négociation", "Prise de parole", "Communication non-verbale", "Esprit de synthèse", "Diplomatie", "Storytelling", "Rédaction professionnelle"],
    leadership: ["Prise de décision", "Gestion d'équipe", "Délégation", "Pensée stratégique", "Motivation des troupes", "Mentoring", "Gestion de projet", "Visionnaire"],
    organisation: ["Gestion du temps", "Rigueur", "Planification", "Priorisation", "Polyvalence", "Sens du détail", "Gestion des ressources", "Fiabilité"],
    adaptabilite: ["Flexibilité", "Résilience", "Gestion du stress", "Apprentissage rapide", "Ouverture d'esprit", "Créativité", "Curiosité", "Auto-formation"]
};

// Base de données à plat pour l'autocomplétion Soft Skills
const SOFT_SKILLS_DB = Object.values(softSkillClusters).flat();

let activeCluster = null;

function toggleSkillCluster(type, el) {
    const box = document.getElementById('soft-skill-suggestions');
    const container = document.getElementById('chips-container');
    const nameDisplay = document.getElementById('cluster-name-display');

    if (activeCluster === type) {
        box.style.display = 'none';
        activeCluster = null;
        el.classList.remove('active');
    } else {
        document.querySelectorAll('.soft-skill-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        activeCluster = type;
        nameDisplay.textContent = el.querySelector('span').textContent;
        
        container.innerHTML = '';
        softSkillClusters[type].forEach(skill => {
            const isSelected = currentSkills.some(s => (s.name || s) === skill);
            const chip = document.createElement('div');
            chip.className = `skill-chip ${isSelected ? 'selected' : ''}`;
            chip.innerHTML = skill;
            chip.onclick = () => {
                if (currentSkills.some(s => (s.name || s) === skill)) {
                    removeSkillObj(skill);
                    chip.classList.remove('selected');
                } else {
                    addSkillFromInput(skill, 'soft');
                    chip.classList.add('selected');
                }
            };
            container.appendChild(chip);
        });
        
        box.style.display = 'block';
    }
}

// ── MOTEUR DE CORRECTION LINGUISTIQUE (LOCAL) ──

// 1. Calcul de la distance de Levenshtein (détection de fautes de frappe)
function getLevenshteinDistance(a, b) {
    const matrix = [];
    for (let i = 0; i <= b.length; i++) matrix[i] = [i];
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) matrix[i][j] = matrix[i - 1][j - 1];
            else matrix[i][j] = Math.min(matrix[i - 1][j - 1] + 1, Math.min(matrix[i][j - 1] + 1, matrix[i - 1][j] + 1));
        }
    }
    return matrix[b.length][a.length];
}

// 2. Normalisation (Enlever les accents et passer en minuscule pour comparer)
function normalizeText(text) {
    return text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
}

// 3. Correction Intelligente (Recherche la version parfaite dans la DB)
function smartLinguisticFix(text, dbType) {
    if (!text || text.trim().length < 2) return text;
    
    let db = [];
    if (dbType === 'hard') db = SKILL_DB;
    else if (dbType === 'soft') db = SOFT_SKILLS_DB;
    else if (dbType === 'title') db = TITLES_DB;
    else if (dbType === 'location') db = LOCATIONS_DB;

    const inputNorm = normalizeText(text);
    let bestMatch = null;
    let minDistance = 3; // On ne corrige que si c'est très proche (max 2 fautes)

    for (const entry of db) {
        const entryNorm = normalizeText(entry);
        
        // Match exact (ignorant les accents/casse)
        if (inputNorm === entryNorm) return entry;

        // Match par distance (fautes de frappe)
        const dist = getLevenshteinDistance(inputNorm, entryNorm);
        if (dist < minDistance) {
            minDistance = dist;
            bestMatch = entry;
        }
    }

    if (bestMatch) return bestMatch;

    // Si aucun match, on applique au moins la majuscule propre
    return text.trim().charAt(0).toUpperCase() + text.trim().slice(1);
}

function addSkillFromInput(val, type = 'hard') {
    const hardInput = document.getElementById('input-skill-search');
    const softInput = document.getElementById('input-soft-skill-search');
    
    let v = val;
    if (!v) {
        v = (type === 'hard') ? hardInput.value : softInput.value;
    }
    
    // Correction linguistique intelligente via DB
    v = smartLinguisticFix(v, type);

    if(v) {
        const existing = currentSkills.find(s => (s.name || s) === v);
        if(!existing) {
            currentSkills.push({ name: v, type: type });
            if (!val) {
                if (type === 'hard') hardInput.value = '';
                else softInput.value = '';
            }
            renderTags();
        }
    }
}

function renderTags() {
    const c = document.getElementById('skills-tags-container'); if(!c) return;
    c.innerHTML = '';
    currentSkills.forEach(s => {
        const name = s.name || s;
        const type = s.type || 'hard';
        const t = document.createElement('div'); 
        t.className = `tag-item ${type === 'soft' ? 'soft-tag' : ''}`;
        
        // Inline style for soft tags to ensure they stand out
        if(type === 'soft') {
            t.style.background = 'rgba(16, 185, 129, 0.1)';
            t.style.borderColor = '#10b981';
            t.style.color = '#065f46';
        }
        
        t.innerHTML = `<span>${name}</span><button type="button" onclick="removeSkillObj('${name}')">&times;</button>`;
        c.appendChild(t);
    });
    const strArray = currentSkills.map(s => s.name || s);
    document.getElementById('input-skills').value = strArray.join(',');
    const ifrm = document.getElementById('template-preview-frame');
    if(ifrm && ifrm.contentWindow) ifrm.contentWindow.postMessage({ type: 'cv-update', field: 'competences', value: strArray.join(' • ') }, '*');
    updateProgress();
}

function removeSkillObj(name) {
    currentSkills = currentSkills.filter(s => (s.name || s) !== name);
    renderTags();
    
    // Update chips UI if a cluster is open
    document.querySelectorAll('.skill-chip').forEach(chip => {
        if (chip.textContent === name) chip.classList.remove('selected');
    });
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
        const v = inp.value.toLowerCase().trim();
        sD.innerHTML = '';
        if (v.length < 1) { sD.style.display = 'none'; return; }

        // Algorithme de recherche intelligent : priorité aux mots commençant par la saisie
        const startsWith = db.filter(x => x.toLowerCase().startsWith(v));
        const includes = db.filter(x => x.toLowerCase().includes(v) && !x.toLowerCase().startsWith(v));
        
        // Fusion et limitation à 6 résultats
        const m = [...startsWith, ...includes].slice(0, 6);

        if (m.length > 0) {
            m.forEach(match => {
                const d = document.createElement('div');
                d.className = 'tag-suggest-item';
                
                // Mise en gras de la partie correspondante
                const regex = new RegExp(`(${v})`, 'gi');
                const highlighted = match.replace(regex, '<strong>$1</strong>');
                d.innerHTML = highlighted;

                d.onclick = () => {
                    if (isSkill) { 
                        const type = inp.id.includes('soft') ? 'soft' : 'hard';
                        addSkillFromInput(match, type); 
                        inp.value = ''; 
                    }
                    else { 
                        inp.value = match; 
                        inp.dispatchEvent(new Event('input')); // Trigger sync
                    }
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
    if (!AUDIT_CV_ID) {
        alert("Veuillez sauvegarder votre CV au moins une fois avant de lancer l'audit.");
        return;
    }
    document.getElementById('ai-audit-prompt').style.display = 'none';
    document.getElementById('ai-audit-scanner').style.display = 'block';
    document.getElementById('audit-main-title').textContent = "Analyse Groq Cloud en cours...";
    
    // Fake progress text (Looping for longer generation)
    const progText = document.getElementById('audit-status-text');
    const progBar = document.getElementById('audit-progress');
    const steps = [
        "Connexion au Cloud sécurisé...", 
        "Analyse IA par Groq Cloud...", 
        "Optimisation du score ATS...", 
        "Finalisation du rapport..."
    ];
    let stepIndex = 0;
    const interval = setInterval(() => {
        stepIndex++;
        if (stepIndex < steps.length) {
            progText.textContent = steps[stepIndex];
        }
        const currentWidth = parseFloat(progBar.style.width || 0);
        if (currentWidth < 98) {
            progBar.style.width = (currentWidth + (98 - currentWidth) * 0.3) + '%';
        }
    }, 800);

    // Build massive string
    const cvTextData = `
Titre visé: ${document.getElementById('input-title').value.trim()}
Résumé: ${document.getElementById('input-summary').innerText.trim()}
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
            
            circle.className = 'ats-score-badge';

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
        console.error("AI Audit Error:", err);
        alert("Erreur lors de l'appel à l'IA : " + err.message + "\nVérifiez votre connexion internet.");
        window.location.href = 'cv_my.php';
    }
}

    // ==========================================
    // IA Polish Integration (Ollama Mistral 7b)
    // ==========================================
    let pendingAI = { inputId: null, context: null, btn: null };

    function openAIPolishModal(inputId, context, btnElement) {
        pendingAI = { inputId, context, btn: btnElement };
        const modal = document.getElementById('ai-polish-modal');
        if (modal) {
            modal.classList.add('active');
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeAIPolishModal() {
        const modal = document.getElementById('ai-polish-modal');
        if (modal) modal.classList.remove('active');
    }

    function applyAIPolish(mode) {
        closeAIPolishModal();
        if (pendingAI.inputId) {
            optimizeWithAI(pendingAI.inputId, pendingAI.context, pendingAI.btn, mode);
        }
    }

    async function optimizeWithAI(inputId, context, btnElement, mode = 'polish') {
        const inputField = document.getElementById(inputId);
        const textRaw = inputField.value ? inputField.value.trim() : inputField.innerText.trim();

        if (!textRaw) {
            showToast("Veuillez saisir un texte brouillon d'abord.", 'alert-circle');
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
                body: JSON.stringify({ text: textRaw, context: finalContext, mode: mode })
            });

            const data = await response.json();

            if (data.success) {
                // Update textarea or div
                if(inputField.tagName === 'TEXTAREA') {
                    inputField.value = data.polished_text;
                } else {
                    inputField.innerText = data.polished_text;
                }
                
                // Force sync update
                if (pendingAI.context === 'experience') {
                    // Update the role data
                    const ids = pendingAI.inputId.split('-'); // exp-desc-i-j
                    const i = ids[2];
                    const j = ids[3];
                    currentRoles[i].achievements[j].text = data.polished_text;
                    renderRoles();
                    syncExperience();
                } else {
                    syncField(inputField);
                }
                
                showToast(mode === 'correct' ? "Texte corrigé !" : "Texte optimisé !", 'check-circle');
                
                // Visual feedback
                inputField.style.transition = 'box-shadow 0.3s ease, background 0.3s ease';
                inputField.style.boxShadow = 'inset 0 0 0 2px rgba(16, 185, 129, 0.5)';
                setTimeout(() => { inputField.style.boxShadow = ''; }, 1500);
            } else {
                showToast(data.error || "Erreur inconnue.", 'alert-triangle');
            }
        } catch (error) {
            console.error("Erreur dans l'IA Polish:", error);
            showToast("Erreur de connexion à Ollama.", 'alert-octagon');
        } finally {
            btnElement.classList.remove('ai-loading');
            spanText.textContent = originalText;
            btnElement.disabled = false;
        }
    }
</script>
