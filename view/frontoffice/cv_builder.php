<?php
/**
 * cv_builder.php — CV Builder (Aptus Edition)
 * Same layout as cv-builder-v2/builder.php, themed with Aptus colors.
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

$tplClass = strtolower(preg_replace('/[^a-z0-9]/i', '-', $template['nom'] ?? 'default'));

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

<!-- ═══ 3-Column Builder Layout (exact cv-builder-v2 structure) ═══ -->
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
            </div>
            <div class="form-group">
                <label>Titre du Poste</label>
                <input type="text" id="input-title" class="form-control" placeholder="ex: Développeur Full-Stack">
            </div>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Email</label>
                    <input type="email" id="input-email" class="form-control" placeholder="nom@exemple.com">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Téléphone</label>
                    <input type="text" id="input-phone" class="form-control" placeholder="+216 ...">
                </div>
            </div>
            <div class="form-group">
                <label>Localisation (Ville, Pays)</label>
                <input type="text" id="input-location" class="form-control" placeholder="Tunis, Tunisie">
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

    <!-- RIGHT: Live Preview -->
    <aside class="builder-preview-area cv-render template-<?php echo htmlspecialchars($tplClass); ?>" id="cv-wrapper" style="--cv-accent: <?php echo htmlspecialchars($cv['couleurTheme']); ?>;">

        <?php
        $nom = strtolower($template['nom'] ?? '');
        $isManager = str_contains($nom, 'manager') || str_contains($nom, 'sidebar');
        ?>

        <?php if ($isManager): ?>
            <div class="template-manager">
                <div class="cv-sidebar">
                    <img id="preview-photo" class="cv-photo" src="" alt="Profile">
                    <div class="cv-header" style="text-align:center; border:none; display:block;">
                        <div class="cv-name" id="preview-name">NOM COMPLET</div>
                        <div class="cv-title" id="preview-title">Titre</div>
                        <div class="cv-contact" id="preview-contact"></div>
                    </div>
                    <div class="cv-section">
                        <div class="cv-section-title">Compétences</div>
                        <div class="cv-content" id="preview-skills"></div>
                    </div>
                    <div class="cv-section">
                        <div class="cv-section-title">Langues</div>
                        <div class="cv-content" id="preview-languages"></div>
                    </div>
                    <div class="cv-section">
                        <div class="cv-section-title">Formation</div>
                        <div class="cv-content" id="preview-education"></div>
                    </div>
                </div>
                <div class="cv-main">
                    <div class="cv-section">
                        <div class="cv-section-title">Résumé</div>
                        <div class="cv-content" id="preview-summary"></div>
                    </div>
                    <div class="cv-section">
                        <div class="cv-section-title">Expérience</div>
                        <div class="cv-content" id="preview-experience"></div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="cv-header">
                <img id="preview-photo" class="cv-photo" src="" alt="Profile">
                <div style="flex:1;">
                    <div class="cv-name" id="preview-name">NOM COMPLET</div>
                    <div class="cv-title" id="preview-title">Titre du Poste</div>
                    <div class="cv-contact" id="preview-contact"></div>
                </div>
            </div>
            <div class="cv-section">
                <div class="cv-section-title">Résumé</div>
                <div class="cv-content" id="preview-summary"></div>
            </div>
            <div class="cv-section">
                <div class="cv-section-title">Expérience</div>
                <div class="cv-content" id="preview-experience"></div>
            </div>
            <div style="display:flex; gap:20px;">
                <div class="cv-section" style="flex:1;">
                    <div class="cv-section-title">Compétences</div>
                    <div class="cv-content" id="preview-skills"></div>
                </div>
                <div class="cv-section" style="flex:1;">
                    <div class="cv-section-title">Langues</div>
                    <div class="cv-content" id="preview-languages"></div>
                </div>
            </div>
            <div class="cv-section">
                <div class="cv-section-title">Formation</div>
                <div class="cv-content" id="preview-education"></div>
            </div>
        <?php endif; ?>

    </aside>
</div>

<script>
/* ── CV Builder JS (same logic as cv-builder-v2/script.js) ── */
let currentSkills = [];
const SKILL_DB = ['PHP','MySQL','JavaScript','Python','Java','React','Vue.js','Node.js','Angular','TypeScript','Leadership','Management','Communication','Agile','Docker','Cloud','AWS','Git','Linux','CSS','HTML','SQL','MongoDB','C++','Figma','UX Design','Marketing','SEO','Data Analysis','Machine Learning'];

document.addEventListener('DOMContentLoaded', () => {
    // Active nav link
    const navCV = document.getElementById('nav-cv');
    if(navCV) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        navCV.classList.add('active');
    }

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
                const cvPhoto = document.getElementById('preview-photo');
                if (cvPhoto) {
                    cvPhoto.src = b64;
                    cvPhoto.classList.add('active');
                    cvPhoto.style.display = 'block';
                }
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
            const cvPhoto = document.getElementById('preview-photo');
            if (cvPhoto) { cvPhoto.src = INITIAL_DATA.urlPhoto; cvPhoto.classList.add('active'); cvPhoto.style.display = 'block'; }
        }

        if (INITIAL_DATA.competences) {
            currentSkills = INITIAL_DATA.competences.split(',').filter(x => x.trim());
            renderTags();
        }

        if (INITIAL_DATA.couleurTheme) {
            document.getElementById('color-picker').value = INITIAL_DATA.couleurTheme;
        }

        syncAllPreviews();
    }

    // Live preview listeners
    ['name','title','summary','experience','education','languages'].forEach(field => {
        const input = document.getElementById('input-' + field);
        const preview = document.getElementById('preview-' + field);
        if (input && preview) {
            input.addEventListener('input', () => preview.innerText = input.value || '---');
        }
    });

    ['email','phone','location'].forEach(f => {
        document.getElementById('input-' + f).addEventListener('input', syncContact);
    });

    document.getElementById('color-picker').addEventListener('input', (e) => {
        document.getElementById('cv-wrapper').style.setProperty('--cv-accent', e.target.value);
    });

    // Sidebar step clicks
    document.querySelectorAll('.wizard-step-link').forEach(link => {
        link.addEventListener('click', () => goToStep(parseInt(link.dataset.step)));
    });

    // Save
    document.getElementById('btn-save').addEventListener('click', async function() {
        const data = {
            cv_id: CV_ID, template_id: TEMPLATE_ID,
            name: document.getElementById('input-name').value,
            title: document.getElementById('input-title').value,
            email: document.getElementById('input-email').value,
            phone: document.getElementById('input-phone').value,
            location: document.getElementById('input-location').value,
            summary: document.getElementById('input-summary').value,
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
            const res = await fetch('save_cv_v2.php', {
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
    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.wizard-step-link').forEach(l => l.classList.remove('active'));
    document.getElementById('step-' + idx).classList.add('active');
    document.querySelector(`.wizard-step-link[data-step="${idx}"]`).classList.add('active');
}

function syncContact() {
    const e = document.getElementById('input-email').value;
    const p = document.getElementById('input-phone').value;
    const l = document.getElementById('input-location').value;
    const contact = document.getElementById('preview-contact');
    if (contact) contact.innerText = [e, p, l].filter(x => x).join(' | ');
}

function syncAllPreviews() {
    ['name','title','summary','experience','education','languages'].forEach(f => {
        const inp = document.getElementById('input-' + f);
        const prv = document.getElementById('preview-' + f);
        if (inp && prv) prv.innerText = inp.value || '---';
    });
    syncContact();
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
    const preview = document.getElementById('preview-skills');
    if (preview) preview.innerText = currentSkills.join(' • ');
}
</script>
