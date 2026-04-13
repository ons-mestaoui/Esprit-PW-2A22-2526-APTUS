<?php
/**
 * cv_form.php — CV Builder (Aptus Edition)
 * Uses the actual template's structureHtml for live preview via iframe.
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
$pageCSS   = "cv_premium.css";

if (!isset($content)) {
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

<!-- ═══ 3-Column Builder Layout ═══ -->
<div class="builder-layout">

    <!-- LEFT: Step Sidebar -->
    <aside class="wizard-sidebar">
        <div class="wizard-step-link active" data-step="1"><div class="step-num">1</div> Infos</div>
        <div class="wizard-step-link" data-step="2"><div class="step-num">2</div> Résumé</div>
        <div class="wizard-step-link" data-step="3"><div class="step-num">3</div> Expérience</div>
        <div class="wizard-step-link" data-step="4"><div class="step-num">4</div> Compétences</div>
        <div class="wizard-step-link" data-step="5"><div class="step-num">5</div> Formation</div>
        <div class="wizard-step-link" data-step="6"><div class="step-num">6</div> Langues</div>
    </aside>

    <!-- CENTER: Form Area -->
    <main class="builder-form-area" id="form-container">

        <!-- STEP 1: Personal Info -->
        <div class="step-content active" id="step-1">
            <div class="step-header"><h2>Informations Personnelles</h2></div>
            <p class="help-text">Les recruteurs ont besoin de savoir qui vous êtes et comment vous contacter.</p>

            <div class="image-upload-wrapper" id="photo-upload-wrapper">
                <i class="fa-solid fa-camera fa-2x" style="color: var(--text-tertiary); margin-bottom:0.5rem;"></i>
                <p>Cliquez pour ajouter votre photo</p>
                <img id="photo-preview-img" class="photo-preview" src="" alt="" style="display:none;">
                <input type="file" id="input-photo" accept="image/*" style="display:none;">
                <input type="hidden" id="photo-b64" value="">
            </div>

            <div class="form-group">
                <label>Nom Complet</label>
                <input type="text" id="input-name" class="form-control" placeholder="Jean Dupont">
                <div class="error-msg" id="err-name" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
            </div>
            <div class="form-group">
                <label>Titre du Poste</label>
                <input type="text" id="input-title" class="form-control" placeholder="ex: Développeur Full-Stack">
                <div class="error-msg" id="err-title" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
            </div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Email</label>
                    <input type="text" id="input-email" class="form-control" placeholder="nom@exemple.com">
                    <div class="error-msg" id="err-email" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Téléphone</label>
                    <input type="text" id="input-phone" class="form-control" placeholder="+216 ...">
                    <div class="error-msg" id="err-phone" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
                </div>
            </div>
            <div class="form-group">
                <label>Localisation (Ville, Pays)</label>
                <input type="text" id="input-location" class="form-control" placeholder="Tunis, Tunisie">
                <div class="error-msg" id="err-location" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
            </div>

            <div class="wizard-footer">
                <div></div>
                <button class="btn-primary-cv" onclick="goToStep(2)">Suivant: Résumé</button>
            </div>
        </div>

        <!-- STEP 2: Summary -->
        <div class="step-content" id="step-2">
            <div class="step-header"><h2>Résumé Professionnel</h2></div>
            <p class="help-text">Un résumé est un bref aperçu de 2-4 phrases de votre carrière, vos meilleures réalisations et compétences.</p>
            <div class="form-group">
                <textarea id="input-summary" class="form-control" placeholder="Professionnel passionné avec une expertise en..."></textarea>
                <div class="error-msg" id="err-summary" style="color:red; font-size:12px; display:none; margin-top:4px;"></div>
                <button class="btn-ai" onclick="alert('IA: Cette fonctionnalité sera bientôt disponible !')">
                    ✨ AI Polish
                </button>
            </div>
            <div class="wizard-footer">
                <button class="btn-secondary-cv" onclick="goToStep(1)">Retour</button>
                <button class="btn-primary-cv" onclick="goToStep(3)">Suivant: Expérience</button>
            </div>
        </div>

        <!-- STEP 3: Experience -->
        <div class="step-content" id="step-3">
            <div class="step-header"><h2>Expérience Professionnelle</h2></div>
            <p class="help-text">Listez vos postes en mettant en avant les réalisations avec des verbes d'action.</p>
            <div class="form-group">
                <textarea id="input-experience" class="form-control" placeholder="Poste — Entreprise&#10;Dates&#10;• Mission principale..."></textarea>
                <button class="btn-ai" onclick="alert('IA: Optimisation du contenu bientôt disponible !')">
                    ✨ AI Content Optimizer
                </button>
            </div>
            <div class="wizard-footer">
                <button class="btn-secondary-cv" onclick="goToStep(2)">Retour</button>
                <button class="btn-primary-cv" onclick="goToStep(4)">Suivant: Compétences</button>
            </div>
        </div>

        <!-- STEP 4: Skills -->
        <div class="step-content" id="step-4">
            <div class="step-header"><h2>Compétences (Tags)</h2></div>
            <p class="help-text">Ajoutez vos compétences techniques et soft skills. Tapez et appuyez sur Entrée.</p>
            <div class="form-group">
                <div class="tags-container" id="skills-tags-container">
                    <input type="text" id="input-skill-search" class="tag-input" placeholder="Tapez: PHP, React, Leadership..." autocomplete="off">
                </div>
                <div class="tag-suggestions" id="skill-suggestions"></div>
                <input type="hidden" id="input-skills" value="">
            </div>
            <div class="wizard-footer">
                <button class="btn-secondary-cv" onclick="goToStep(3)">Retour</button>
                <button class="btn-primary-cv" onclick="goToStep(5)">Suivant: Formation</button>
            </div>
        </div>

        <!-- STEP 5: Education -->
        <div class="step-content" id="step-5">
            <div class="step-header"><h2>Formation</h2></div>
            <p class="help-text">Où avez-vous étudié ? Incluez le nom de l'établissement, le diplôme et l'année.</p>
            <div class="form-group">
                <textarea id="input-education" class="form-control" placeholder="Diplôme — Université&#10;Année"></textarea>
            </div>
            <div class="wizard-footer">
                <button class="btn-secondary-cv" onclick="goToStep(4)">Retour</button>
                <button class="btn-primary-cv" onclick="goToStep(6)">Suivant: Langues</button>
            </div>
        </div>

        <!-- STEP 6: Languages -->
        <div class="step-content" id="step-6">
            <div class="step-header"><h2>Langues</h2></div>
            <p class="help-text">Listez les langues parlées et votre niveau (ex: Français — Maternel, Anglais — B2).</p>
            <div class="form-group">
                <textarea id="input-languages" class="form-control" placeholder="Français — Maternel&#10;Anglais — Courant"></textarea>
            </div>

            <div class="form-group">
                <label>Couleur du thème</label>
                <div style="display:flex; align-items:center; gap:1rem; margin-top:0.5rem;">
                    <input type="color" id="color-picker" class="color-picker" value="<?php echo htmlspecialchars($cv['couleurTheme']); ?>" title="Changer la couleur du thème">
                    <span style="font-size:0.85rem; color:var(--text-tertiary);">Personnalisez l'accent de votre CV</span>
                </div>
            </div>

            <div class="wizard-footer">
                <button class="btn-secondary-cv" onclick="goToStep(5)">Retour</button>
                <button class="btn-primary-cv" id="btn-save">Enregistrer le CV</button>
            </div>
        </div>

    </main>

    <!-- RIGHT: Live Preview via iframe (uses actual template HTML from DB) -->
    <aside class="builder-preview-area" id="cv-wrapper">
        <iframe id="template-preview-frame" style="
            width: 210mm;
            height: 297mm;
            transform-origin: top left;
            border: none;
            background: #fff;
        "></iframe>
    </aside>
</div>

<?php
// Prepare the template HTML for the iframe
// Extract just body content + styles, or use full HTML if it's a complete page
$templateHtml = $template['structureHtml'] ?? '';

// Check if it's a full HTML document
$isFullHtml = stripos($templateHtml, '<!DOCTYPE') !== false || stripos($templateHtml, '<html') !== false;

if (!$isFullHtml) {
    // Wrap partial HTML in a basic document
    $templateHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></head><body style="margin:0;padding:0;">' . $templateHtml . '</body></html>';
}

// Inject CSS to hide scrollbars inside the iframe
$hideScrollbarCSS = '<style>html,body{overflow:hidden!important;scrollbar-width:none!important;-ms-overflow-style:none!important;}::-webkit-scrollbar{display:none!important;}</style>';
if (stripos($templateHtml, '</head>') !== false) {
    $templateHtml = str_ireplace('</head>', $hideScrollbarCSS . '</head>', $templateHtml);
} elseif (stripos($templateHtml, '<body') !== false) {
    $templateHtml = preg_replace('/<body/i', $hideScrollbarCSS . '<body', $templateHtml, 1);
} else {
    $templateHtml = $hideScrollbarCSS . $templateHtml;
}

// Inject a receiver script into the template HTML for live updates
$liveUpdateScript = <<<'SCRIPT'
<script>
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'cv-update') {
        const d = e.data;
        
        // Try multiple selectors for each field
        function setText(selectors, value) {
            for (const sel of selectors) {
                const el = document.querySelector(sel);
                if (el) {
                    el.innerText = value;
                    return true;
                }
            }
            return false;
        }
        
        if (d.field === 'nomComplet') {
            setText([
                '#preview-nomComplet',
                '.sidebar h1',
                '.header-info h1', 
                '.header-text h1',
                '.cv-name',
                'h1[contenteditable]'
            ], d.value);
        }
        else if (d.field === 'titrePoste') {
            setText([
                '#preview-titrePoste',
                '.sidebar h2',
                '.header-info h2',
                '.header-text h2',
                '.cv-title',
                'h2[contenteditable]'
            ], d.value);
        }
        else if (d.field === 'resume') {
            setText([
                '#preview-resume',
                '.summary-text',
                '.summary',
                '.cv-content:first-of-type'
            ], d.value);
        }
        else if (d.field === 'experience') {
            // For experience, find the section after resume
            let found = setText([
                '#preview-experience'
            ], d.value);
            if (!found) {
                // Try to find experience section by title
                const titles = document.querySelectorAll('.main-title, .section-title, h3');
                for (const t of titles) {
                    if (t.textContent.toLowerCase().includes('expérience') || t.textContent.toLowerCase().includes('experience')) {
                        // Get the next sibling or content area
                        let next = t.nextElementSibling;
                        while (next && (next.classList.contains('main-title') || next.classList.contains('section-title') || next.tagName === 'H3')) {
                            next = next.nextElementSibling;
                        }
                        if (next) {
                            next.innerText = d.value;
                            break;
                        }
                    }
                }
            }
        }
        else if (d.field === 'competences') {
            let found = setText([
                '#preview-competences'
            ], d.value);
            if (!found) {
                // Find competences section
                const titles = document.querySelectorAll('.side-title, .section-title, .main-title, h3');
                for (const t of titles) {
                    const txt = t.textContent.toLowerCase();
                    if (txt.includes('compétence') || txt.includes('competence') || txt.includes('skills')) {
                        let next = t.nextElementSibling;
                        if (next) {
                            next.innerText = d.value;
                            break;
                        }
                    }
                }
            }
        }
        else if (d.field === 'langues') {
            let found = setText([
                '#preview-langues'
            ], d.value);
            if (!found) {
                const titles = document.querySelectorAll('.side-title, .section-title, .main-title, h3');
                for (const t of titles) {
                    const txt = t.textContent.toLowerCase();
                    if (txt.includes('langue') || txt.includes('language')) {
                        let next = t.nextElementSibling;
                        if (next) {
                            next.innerText = d.value;
                            break;
                        }
                    }
                }
            }
        }
        else if (d.field === 'formation') {
            let found = setText([
                '#preview-formation'
            ], d.value);
            if (!found) {
                const titles = document.querySelectorAll('.side-title, .section-title, .main-title, h3');
                for (const t of titles) {
                    const txt = t.textContent.toLowerCase();
                    if (txt.includes('formation') || txt.includes('education') || txt.includes('études')) {
                        let next = t.nextElementSibling;
                        if (next) {
                            next.innerText = d.value;
                            break;
                        }
                    }
                }
            }
        }
        else if (d.field === 'infoContact') {
            let found = setText([
                '#preview-infoContact',
                '.contact-info',
                '.cv-contact'
            ], d.value);
            if (!found) {
                // Update individual contact items in sidebar templates
                const items = document.querySelectorAll('.contact-item');
                if (items.length > 0 && d.parts) {
                    if (d.parts.location && items[0]) items[0].innerText = '📍 ' + d.parts.location;
                    if (d.parts.phone && items[1]) items[1].innerText = '📞 ' + d.parts.phone;
                    if (d.parts.email && items[2]) items[2].innerText = '✉️ ' + d.parts.email;
                }
            }
        }
        else if (d.field === 'photo') {
            const photos = document.querySelectorAll('#preview-photo, #profile-pic, .cv-photo, .header-photo img');
            photos.forEach(img => {
                if (img.tagName === 'IMG') {
                    img.src = d.value;
                    img.style.display = 'block';
                }
            });
            // Hide photo text in sidebar templates
            const photoText = document.querySelector('#photo-text, .photo-text');
            if (photoText) photoText.style.display = 'none';
            // For Le Classique style
            const headerPhoto = document.querySelector('.header-photo');
            if (headerPhoto && !headerPhoto.querySelector('img')) {
                headerPhoto.innerHTML = '<img src="' + d.value + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
            }
        }
    }
});

// Signal parent that iframe is ready
window.parent.postMessage({type: 'iframe-ready'}, '*');
</script>
SCRIPT;

// Inject the script before </body> or at the end
if (stripos($templateHtml, '</body>') !== false) {
    $templateHtml = str_ireplace('</body>', $liveUpdateScript . '</body>', $templateHtml);
} else {
    $templateHtml .= $liveUpdateScript;
}
?>

<script>
/* ── CV Builder JS ── */
let currentSkills = [];
const SKILL_DB = ['PHP','MySQL','JavaScript','Python','Java','React','Vue.js','Node.js','Angular','TypeScript','Leadership','Management','Communication','Agile','Docker','Cloud','AWS','Git','Linux','CSS','HTML','SQL','MongoDB','C++','Figma','UX Design','Marketing','SEO','Data Analysis','Machine Learning'];

// Template HTML to inject into iframe
const TEMPLATE_HTML = <?php echo json_encode($templateHtml); ?>;
let iframeReady = false;

function initIframe() {
    const iframe = document.getElementById('template-preview-frame');
    if (!iframe) return;
    
    // Write the template HTML into the iframe
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(TEMPLATE_HTML);
    doc.close();
    
    // Scale the iframe to fit the preview area
    setTimeout(scaleIframe, 200);
    window.addEventListener('resize', scaleIframe);
}

function scaleIframe() {
    const iframe = document.getElementById('template-preview-frame');
    const container = document.getElementById('cv-wrapper');
    if (!iframe || !container) return;
    
    const containerWidth = container.clientWidth;
    
    // The iframe is 210mm (≈794px) wide, 297mm (≈1123px) tall
    const iframeW = 794;
    const iframeH = 1123;
    
    // Scale to fill the container width exactly
    const scale = containerWidth / iframeW;
    
    iframe.style.transform = `scale(${scale})`;
    iframe.style.width = iframeW + 'px';
    iframe.style.height = iframeH + 'px';
}

// Send a field update to the iframe
function updatePreview(field, value, parts) {
    const iframe = document.getElementById('template-preview-frame');
    if (!iframe || !iframe.contentWindow) return;
    
    const msg = { type: 'cv-update', field: field, value: value };
    if (parts) msg.parts = parts;
    iframe.contentWindow.postMessage(msg, '*');
}

document.addEventListener('DOMContentLoaded', () => {
    // Active nav link
    const navCV = document.getElementById('nav-cv');
    if(navCV) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        navCV.classList.add('active');
    }

    // Initialize iframe with template
    initIframe();

    // Photo Upload
    const photoWrap = document.getElementById('photo-upload-wrapper');
    const photoInput = document.getElementById('input-photo');
    const photoPreview = document.getElementById('photo-preview-img');

    photoWrap.addEventListener('click', () => photoInput.click());
    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const b64 = e.target.result;
                document.getElementById('photo-b64').value = b64;
                if (photoPreview) {
                    photoPreview.src = b64;
                    photoPreview.style.display = 'block';
                }
                updatePreview('photo', b64);
            };
            reader.readAsDataURL(file);
        }
    });

    // Pre-fill form from INITIAL_DATA
    if (INITIAL_DATA) {
        const map = {
            'nomComplet': 'input-name', 'titrePoste': 'input-title',
            'email': 'input-email', 'telephone': 'input-phone',
            'adresse': 'input-location', 'resume': 'input-summary',
            'experience': 'input-experience', 'formation': 'input-education',
            'langues': 'input-languages'
        };
        Object.keys(map).forEach(key => {
            const el = document.getElementById(map[key]);
            if (el && INITIAL_DATA[key]) el.value = INITIAL_DATA[key];
        });

        if (INITIAL_DATA.urlPhoto) {
            document.getElementById('photo-b64').value = INITIAL_DATA.urlPhoto;
            if (photoPreview) { photoPreview.src = INITIAL_DATA.urlPhoto; photoPreview.style.display = 'block'; }
        }

        if (INITIAL_DATA.competences) {
            currentSkills = INITIAL_DATA.competences.split(',').filter(x => x.trim());
            renderTags();
        }

        if (INITIAL_DATA.couleurTheme) {
            document.getElementById('color-picker').value = INITIAL_DATA.couleurTheme;
        }

        // Sync all previews after iframe loads
        setTimeout(() => {
            syncAllPreviews();
            if (INITIAL_DATA.urlPhoto) {
                updatePreview('photo', INITIAL_DATA.urlPhoto);
            }
        }, 500);
    }

    // Live preview listeners
    const fieldBindings = {
        'input-name': 'nomComplet',
        'input-title': 'titrePoste',
        'input-summary': 'resume',
        'input-experience': 'experience',
        'input-education': 'formation',
        'input-languages': 'langues'
    };
    
    Object.entries(fieldBindings).forEach(([inputId, field]) => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', () => {
                updatePreview(field, input.value || '---');
            });
        }
    });

    ['email','phone','location'].forEach(f => {
        const el = document.getElementById('input-' + f);
        if (el) el.addEventListener('input', syncContact);
    });

    document.getElementById('color-picker').addEventListener('input', (e) => {
        // Color theme changes are complex — just update the CSS variable
        const iframe = document.getElementById('template-preview-frame');
        if (iframe && iframe.contentDocument) {
            iframe.contentDocument.documentElement.style.setProperty('--cv-accent', e.target.value);
        }
    });

    // Sidebar step clicks
    document.querySelectorAll('.wizard-step-link').forEach(link => {
        link.addEventListener('click', () => goToStep(parseInt(link.dataset.step)));
    });

    // --- Fonctions de Validation ---
    window.validateField = function(id, val) {
        let errorMsg = null;
        if (id === 'name') {
            if (val.length < 3 || !/^[\p{L}\s.'-]+$/u.test(val)) errorMsg = 'Veuillez entrer un nom valide (min. 3 lettres, lettres/espaces).';
        } else if (id === 'title') {
            if (val.length < 3 || val.length > 100) errorMsg = 'Le titre du poste doit contenir entre 3 et 100 caractères.';
        } else if (id === 'email') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) errorMsg = 'Veuillez entrer une adresse email valide.';
        } else if (id === 'phone') {
            if (!/^\+?[0-9\s.\-()]{8,20}$/.test(val)) errorMsg = 'Numéro de téléphone invalide (min. 8 chiffres).';
        } else if (id === 'location') {
            if (val.length < 3) errorMsg = 'Veuillez préciser votre localisation.';
        } else if (id === 'summary') {
            if (val.length > 1000) errorMsg = 'Votre résumé est trop long (max 1000 caractères).';
        }

        const errorEl = document.getElementById('err-' + id);
        const inputEl = document.getElementById('input-' + id);
        
        if (errorMsg) {
            if(errorEl) { errorEl.innerText = errorMsg; errorEl.style.display = 'block'; }
            if(inputEl) { inputEl.style.borderColor = 'red'; }
            return false;
        } else {
            if(errorEl) { errorEl.style.display = 'none'; }
            if(inputEl) { inputEl.style.borderColor = 'var(--border-color)'; }
            return true;
        }
    };

    // --- Validation en temps réel (Blur) ---
    ['name', 'title', 'email', 'phone', 'location', 'summary'].forEach(fieldId => {
        const el = document.getElementById('input-' + fieldId);
        if (el) {
            el.addEventListener('blur', function() {
                window.validateField(fieldId, this.value.trim());
            });
            // Pour ne pas rester bloqué en rouge si on corrige :
            el.addEventListener('input', function() {
                if (el.style.borderColor === 'red') {
                    window.validateField(fieldId, this.value.trim());
                }
            });
        }
    });

    // Save
    document.getElementById('btn-save').addEventListener('click', async function() {
        // Validation globale JS Manuelle
        let hasError = false;

        const fieldsToValidate = ['name', 'title', 'email', 'phone', 'location', 'summary'];
        fieldsToValidate.forEach(f => {
            const v = document.getElementById('input-' + f).value.trim();
            if (!window.validateField(f, v)) {
                hasError = true;
            }
        });

        if (hasError) {
            alert('Veuillez corriger les champs en rouge avant d\'enregistrer.');
            return;
        }
        // --- Fin Validation JS ---

        const data = {
            cv_id: CV_ID, template_id: TEMPLATE_ID,
            name: document.getElementById('input-name').value.trim(),
            title: document.getElementById('input-title').value.trim(),
            email: document.getElementById('input-email').value.trim(),
            phone: document.getElementById('input-phone').value.trim(),
            location: document.getElementById('input-location').value.trim(),
            summary: document.getElementById('input-summary').value.trim(),
            experience: document.getElementById('input-experience').value,
            skills: document.getElementById('input-skills').value,
            education: document.getElementById('input-education').value,
            languages: document.getElementById('input-languages').value,
            photo: document.getElementById('photo-b64').value,
            color_theme: document.getElementById('color-picker').value
        };

        this.disabled = true;
        this.textContent = 'Enregistrement...';

        try {
            const res = await fetch('cv_save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                window.location.href = 'cv_my.php';
            } else {
                alert('Erreur: ' + result.message);
                this.textContent = 'Enregistrer le CV';
                this.disabled = false;
            }
        } catch (e) {
            console.error(e);
            alert('Erreur de connexion.');
            this.textContent = 'Enregistrer le CV';
            this.disabled = false;
        }
    });

    // Skills tag system
    const skillInput = document.getElementById('input-skill-search');
    const suggestBox = document.getElementById('skill-suggestions');

    skillInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && skillInput.value.trim()) {
            e.preventDefault();
            addSkill(skillInput.value.trim());
        }
    });

    skillInput.addEventListener('input', () => {
        const val = skillInput.value.toLowerCase();
        if (!val) { suggestBox.style.display = 'none'; return; }
        const matches = SKILL_DB.filter(s => s.toLowerCase().startsWith(val) && !currentSkills.includes(s));
        if (matches.length === 0) { suggestBox.style.display = 'none'; return; }
        suggestBox.innerHTML = matches.map(s => `<div class="tag-suggest-item">${s}</div>`).join('');
        suggestBox.style.display = 'block';
        suggestBox.querySelectorAll('.tag-suggest-item').forEach(item => {
            item.addEventListener('click', () => { addSkill(item.textContent); suggestBox.style.display = 'none'; });
        });
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
});

function goToStep(idx) {
    // Si on veut aller à une étape > 1 (depuis n'importe où), il faut que les champs de l'étape 1 soient valides
    if (idx > 1) {
        let isStep1Valid = true;
        ['name', 'title', 'email', 'phone', 'location'].forEach(f => {
            const val = document.getElementById('input-' + f).value.trim();
            if (typeof window.validateField === 'function' && !window.validateField(f, val)) {
                isStep1Valid = false;
            }
        });
        if (!isStep1Valid) {
            alert("Veuillez corriger les champs en rouge à l'étape 1 avant de continuer.");
            idx = 1; // Force le retour à l'étape 1
        }
    }
    
    // Si on veut dépasser l'étape 2, on vérifie la longueur du résumé
    if (idx > 2) {
        const val = document.getElementById('input-summary').value.trim();
        if (typeof window.validateField === 'function' && !window.validateField('summary', val)) {
            alert("Votre résumé est trop long. Veuillez le raccourcir avant de continuer.");
            idx = 2; // Force le retour à l'étape 2
        }
    }

    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.wizard-step-link').forEach(l => l.classList.remove('active'));
    document.getElementById('step-' + idx).classList.add('active');
    document.querySelector(`.wizard-step-link[data-step="${idx}"]`).classList.add('active');
}

function syncContact() {
    const e = document.getElementById('input-email').value;
    const p = document.getElementById('input-phone').value;
    const l = document.getElementById('input-location').value;
    const contactStr = [e, p, l].filter(x => x).join(' | ');
    
    updatePreview('infoContact', contactStr, {
        email: e,
        phone: p,
        location: l
    });
}

function syncAllPreviews() {
    const fields = {
        'input-name': 'nomComplet',
        'input-title': 'titrePoste',
        'input-summary': 'resume',
        'input-experience': 'experience',
        'input-education': 'formation',
        'input-languages': 'langues'
    };
    Object.entries(fields).forEach(([inputId, field]) => {
        const inp = document.getElementById(inputId);
        if (inp && inp.value) updatePreview(field, inp.value);
    });
    syncContact();
    
    // Skills
    if (currentSkills.length > 0) {
        updatePreview('competences', currentSkills.join(' • '));
    }
}

function addSkill(skill) {
    if (!currentSkills.includes(skill)) {
        currentSkills.push(skill);
        renderTags();
    }
    document.getElementById('input-skill-search').value = '';
}

function removeSkill(skill) {
    currentSkills = currentSkills.filter(s => s !== skill);
    renderTags();
}

function renderTags() {
    const container = document.getElementById('skills-tags-container');
    const input = document.getElementById('input-skill-search');
    container.querySelectorAll('.tag-item').forEach(t => t.remove());
    currentSkills.forEach(s => {
        const div = document.createElement('div');
        div.className = 'tag-item';
        div.innerHTML = `${s} <i class="fa-solid fa-times" onclick="removeSkill('${s}')"></i>`;
        container.insertBefore(div, input);
    });
    document.getElementById('input-skills').value = currentSkills.join(',');
    updatePreview('competences', currentSkills.join(' • '));
}
</script>
