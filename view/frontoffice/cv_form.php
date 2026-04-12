<?php
$pageTitle = "Resume Builder";
$pageCSS = "cv.css";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Template.php';
require_once __DIR__ . '/../../controller/TemplateC.php';
require_once __DIR__ . '/../../model/CV.php';
require_once __DIR__ . '/../../controller/CVC.php';

$tc = new TemplateC();
$cvc = new CVC();

$template_id = $_GET['template_id'] ?? null;
$cv_id = $_GET['cv_id'] ?? null;

$template = null;
if ($template_id) {
    $template = $tc->getTemplateById($template_id);
}

$cv_data = [
    'nomComplet' => '', 'email' => '', 'telephone' => '', 'adresse' => '',
    'titrePoste' => '', 'resume' => '', 'experience' => '',
    'competences' => '', 'langues' => '', 'formation' => '',
    'urlPhoto' => '', 'couleurTheme' => '#2563eb'
];

if ($cv_id) {
    $fetched = $cvc->getCVById($cv_id);
    if ($fetched) {
        $cv_data = array_merge($cv_data, $fetched);
        $template_id = $fetched['id_template'];
        $template = $tc->getTemplateById($template_id);
    }
}

if (!$template) {
    header("Location: cv_templates.php");
    exit;
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="builder-layout-container">
    <div class="builder-layout">
        <!-- ═══ WIZARD SIDEBAR ═══ -->
        <aside class="wizard-sidebar">
            <div class="wizard-step-link active" data-step="1"><div class="step-num">1</div> Informations Personnelles</div>
            <div class="wizard-step-link" data-step="2"><div class="step-num">2</div> Résumé / Profil</div>
            <div class="wizard-step-link" data-step="3"><div class="step-num">3</div> Expériences Professionnelles</div>
            <div class="wizard-step-link" data-step="4"><div class="step-num">4</div> Compétences & Tags</div>
            <div class="wizard-step-link" data-step="5"><div class="step-num">5</div> Études & Formation</div>
            <div class="wizard-step-link" data-step="6"><div class="step-num">6</div> Langues & Finition</div>
        </aside>

        <!-- ═══ FORM AREA ═══ -->
        <main class="builder-form-area" id="builder-form">
            
            <!-- STEP 1: PERSONAL -->
            <div class="step-content active" id="step-1">
                <div class="step-header"><h2>Informations Personnelles</h2></div>
                <p class="help-text">Commencez par vos coordonnées de base.</p>
                
                <div class="image-upload-wrapper" id="photo-upload-wrapper">
                   <?php if($cv_data['urlPhoto']): ?>
                        <img src="<?php echo $cv_data['urlPhoto']; ?>" id="preview-photo-mask" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                   <?php else: ?>
                        <i data-lucide="camera" style="width:32px;height:32px;color:rgba(255,255,255,0.2);margin-bottom:8px;"></i>
                        <p>Photo de Profil</p>
                   <?php endif; ?>
                   <input type="file" id="input-photo" accept="image/*" style="display:none;">
                   <input type="hidden" id="photo-b64" value="<?php echo htmlspecialchars($cv_data['urlPhoto']); ?>">
                </div>

                <div class="form-group">
                    <label>Nom Complet *</label>
                    <input type="text" id="input-nomComplet" class="form-control" placeholder="Ex: Jean Dupont" value="<?php echo htmlspecialchars($cv_data['nomComplet']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Titre du Poste *</label>
                    <input type="text" id="input-titrePoste" class="form-control" placeholder="Ex: Développeur Full-Stack" value="<?php echo htmlspecialchars($cv_data['titrePoste']); ?>" required>
                </div>
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="input-email" class="form-control" placeholder="jean.dupont@email.com" value="<?php echo htmlspecialchars($cv_data['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" id="input-telephone" class="form-control" placeholder="+216 00 000 000" value="<?php echo htmlspecialchars($cv_data['telephone']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Adresse / Localisation *</label>
                    <input type="text" id="input-adresse" class="form-control" placeholder="Tunis, Tunisie" value="<?php echo htmlspecialchars($cv_data['adresse']); ?>" required>
                </div>
                
                <div class="wizard-footer">
                    <div></div>
                    <button class="btn btn-primary" onclick="goToStep(2)">Suivant: Résumé</button>
                </div>
            </div>

            <!-- STEP 2: SUMMARY -->
            <div class="step-content" id="step-2">
                <div class="step-header"><h2>Résumé Professionnel</h2></div>
                <p class="help-text">Décrivez en quelques lignes votre profil et vos objectifs.</p>
                <div class="form-group">
                    <textarea id="input-resume" class="form-control" rows="8"><?php echo htmlspecialchars($cv_data['resume']); ?></textarea>
                </div>
                <div class="wizard-footer">
                    <button class="btn btn-ghost" onclick="goToStep(1)">Retour</button>
                    <button class="btn btn-primary" onclick="goToStep(3)">Suivant: Expériences</button>
                </div>
            </div>

            <!-- STEP 3: EXPERIENCE -->
            <div class="step-content" id="step-3">
                <div class="step-header"><h2>Expériences Professionnelles</h2></div>
                <p class="help-text">Listez vos postes passés. Utilisez des tirets pour les tâches.</p>
                <div class="form-group">
                    <textarea id="input-experience" class="form-control" rows="10"><?php echo htmlspecialchars($cv_data['experience']); ?></textarea>
                </div>
                <div class="wizard-footer">
                    <button class="btn btn-ghost" onclick="goToStep(2)">Retour</button>
                    <button class="btn btn-primary" onclick="goToStep(4)">Suivant: Compétences</button>
                </div>
            </div>

            <!-- STEP 4: SKILLS -->
            <div class="step-content" id="step-4">
                <div class="step-header"><h2>Compétences</h2></div>
                <p class="help-text">Ajoutez vos compétences clés séparées par des virgules.</p>
                <div class="form-group">
                    <textarea id="input-competences" class="form-control" rows="5" placeholder="PHP, JavaScript, SQL, Gestion de projet..."><?php echo htmlspecialchars($cv_data['competences']); ?></textarea>
                </div>
                <div class="wizard-footer">
                    <button class="btn btn-ghost" onclick="goToStep(3)">Retour</button>
                    <button class="btn btn-primary" onclick="goToStep(5)">Suivant: Formation</button>
                </div>
            </div>

            <!-- STEP 5: EDUCATION -->
            <div class="step-content" id="step-5">
                <div class="step-header"><h2>Formation & Études</h2></div>
                <p class="help-text">Détaillez votre parcours académique.</p>
                <div class="form-group">
                    <textarea id="input-formation" class="form-control" rows="8"><?php echo htmlspecialchars($cv_data['formation']); ?></textarea>
                </div>
                <div class="wizard-footer">
                    <button class="btn btn-ghost" onclick="goToStep(4)">Retour</button>
                    <button class="btn btn-primary" onclick="goToStep(6)">Suivant: Langues</button>
                </div>
            </div>

            <!-- STEP 6: LANGUAGES -->
            <div class="step-content" id="step-6">
                <div class="step-header"><h2>Langues & Finition</h2></div>
                <p class="help-text">Quelles langues parlez-vous ?</p>
                <div class="form-group">
                    <textarea id="input-langues" class="form-control" rows="5"><?php echo htmlspecialchars($cv_data['langues']); ?></textarea>
                </div>
                
                <div class="form-group mt-6">
                    <label>Couleur du Thème</label>
                    <input type="color" id="input-couleurTheme" value="<?php echo $cv_data['couleurTheme']; ?>" style="width:100%; height:40px; border-radius:8px; border:none; cursor:pointer;">
                </div>

                <div class="wizard-footer">
                    <button class="btn btn-ghost" onclick="goToStep(5)">Retour</button>
                    <button class="btn btn-primary" id="btn-save-cv">Enregistrer le CV</button>
                </div>
            </div>
        </main>

        <!-- ═══ LIVE PREVIEW ═══ -->
        <aside class="builder-preview-area">
            <div class="preview-sticky">
                <div class="preview-header">
                    <span>Aperçu en temps réel</span>
                    <button class="btn btn-sm btn-ghost" id="btn-zoom-preview"><i data-lucide="zoom-in" style="width:14px;height:14px;"></i></button>
                </div>
                <div class="cv-preview-frame">
                    <div id="cv-preview-content" style="--cv-accent: <?php echo $cv_data['couleurTheme']; ?>;">
                        <?php echo $template['structureHtml']; ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
.builder-layout-container { padding: 40px; max-width: 1400px; margin: 0 auto; }
.builder-layout { display: grid; grid-template-columns: 280px 1fr 450px; gap: 40px; align-items: start; }

.wizard-sidebar { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; position: sticky; top: 100px; }
.wizard-step-link { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; cursor: pointer; transition: all 0.3s; color: var(--text-secondary); margin-bottom: 8px; font-weight: 500; }
.wizard-step-link:hover { background: rgba(255,255,255,0.05); color: var(--text-primary); }
.wizard-step-link.active { background: var(--accent-primary-light); color: var(--accent-primary); }
.step-num { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.wizard-step-link.active .step-num { background: var(--accent-primary); color: white; }

.builder-form-area { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; min-height: 600px; }
.step-content { display: none; }
.step-content.active { display: block; animation: slideUp 0.4s ease; }
.step-header h2 { margin-top: 0; color: var(--text-primary); }
.help-text { color: var(--text-tertiary); font-size: 14px; margin-bottom: 32px; }

.image-upload-wrapper { width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.05); border: 2px dashed var(--border-color); margin: 0 auto 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; position: relative; }
.image-upload-wrapper:hover { border-color: var(--accent-primary); background: rgba(56, 189, 248, 0.05); }
.image-upload-wrapper p { font-size: 10px; text-align: center; color: var(--text-tertiary); margin: 0; }

.form-group { margin-bottom: 24px; }
.form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: var(--text-secondary); }
.form-control { width: 100%; padding: 12px 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); transition: all 0.3s; }
.form-control:focus { border-color: var(--accent-primary); outline: none; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1); }

.wizard-footer { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border-color); }

.builder-preview-area { position: sticky; top: 100px; }
.preview-sticky { display: flex; flex-direction: column; gap: 16px; }
.preview-header { display: flex; justify-content: space-between; align-items: center; color: var(--text-tertiary); font-size: 13px; font-weight: 500; }
.cv-preview-frame { width: 100%; aspect-ratio: 1 / 1.414; background: white; border-radius: 8px; overflow-y: auto; overflow-x: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3); transform-origin: top center; transition: all 0.3s; }

/* Injected styles normalization */
#cv-preview-content { color: #000; }

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 1200px) {
    .builder-layout { grid-template-columns: 1fr; }
    .wizard-sidebar { position: static; }
    .builder-preview-area { position: static; max-width: 600px; margin: 40px auto; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateId = <?php echo (int)$template_id; ?>;
    const cvId = <?php echo $cv_id ? (int)$cv_id : 'null'; ?>;
    
    // --- Mapping Logic ---
    // We try to find placeholders in the injected HTML.
    const fields = ['nomComplet', 'titrePoste', 'email', 'telephone', 'adresse', 'resume', 'experience', 'competences', 'formation', 'langues'];
    
    let originalHTML = document.getElementById('cv-preview-content').innerHTML;
    
    function updatePreview() {
        // Handle merged contact info
        const email = document.getElementById('input-email').value;
        const phone = document.getElementById('input-telephone').value;
        const address = document.getElementById('input-adresse').value;
        const mergedContact = [email, phone, address].filter(x => x.trim() !== '').join(' • ');

        const contactTarget = document.getElementById('preview-infoContact') || document.getElementById('cv-infoContact');
        if(contactTarget) contactTarget.innerText = mergedContact || 'Email • Téléphone • Adresse';

        fields.forEach(field => {
            const input = document.getElementById('input-' + field);
            if (!input || field === 'email' || field === 'telephone' || field === 'adresse') return;
            const val = input.value;
            
            const target = document.getElementById('preview-' + field) || document.getElementById('cv-' + field);
            if (target) {
                target.innerText = val || getDefaultText(field);
            }
        });
        
        // Handle Photo
        const photoB64 = document.getElementById('photo-b64').value;
        const photoTarget = document.getElementById('preview-photo') || document.getElementById('cv-photo');
        if (photoTarget) {
            if (photoB64) {
                photoTarget.src = photoB64;
                photoTarget.style.display = 'block';
            } else {
                photoTarget.style.display = 'none';
            }
        }
        
        // Handle Color
        const color = document.getElementById('input-couleurTheme').value;
        const previewContainer = document.getElementById('cv-preview-content');
        previewContainer.style.setProperty('--cv-accent', color);
        
        // Force update on nested .cv-render elements (which might have their own local variable)
        const nestedRender = previewContainer.querySelector('.cv-render');
        if (nestedRender) {
            nestedRender.style.setProperty('--cv-accent', color);
        }
    }

    function getDefaultText(field) {
        if (field === 'nomComplet') return 'JEAN DUPONT';
        if (field === 'titrePoste') return 'VOTRE TITRE';
        return '';
    }

    fields.forEach(field => {
        document.getElementById('input-' + field).addEventListener('input', updatePreview);
    });
    document.getElementById('input-couleurTheme').addEventListener('input', updatePreview);

    // Initial fill if data exists
    updatePreview();

    // Wizard Nav
    window.goToStep = function(step) {
        document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.wizard-step-link').forEach(l => l.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        document.querySelector('.wizard-step-link[data-step="' + step + '"]').classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    document.querySelectorAll('.wizard-step-link').forEach(link => {
        link.addEventListener('click', () => goToStep(link.dataset.step));
    });

    // Image Upload
    const photoWrapper = document.getElementById('photo-upload-wrapper');
    const photoInput = document.getElementById('input-photo');
    photoWrapper.addEventListener('click', () => photoInput.click());
    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const b64 = e.target.result;
                document.getElementById('photo-b64').value = b64;
                photoWrapper.innerHTML = `<img src="${b64}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">`;
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
    });

    // Save Action
    document.getElementById('btn-save-cv').addEventListener('click', function() {
        // Validation basic
        const requiredFields = ['nomComplet', 'titrePoste', 'email', 'telephone', 'adresse'];
        for (let f of requiredFields) {
            const el = document.getElementById('input-' + f);
            if (!el.value.trim()) {
                alert('Veuillez remplir le champ obligatoire : ' + f);
                el.focus();
                return;
            }
        }

        const data = {
            id_cv: cvId,
            id_template: templateId,
            nomComplet: document.getElementById('input-nomComplet').value,
            titrePoste: document.getElementById('input-titrePoste').value,
            email: document.getElementById('input-email').value,
            telephone: document.getElementById('input-telephone').value,
            adresse: document.getElementById('input-adresse').value,
            resume: document.getElementById('input-resume').value,
            experience: document.getElementById('input-experience').value,
            competences: document.getElementById('input-competences').value,
            formation: document.getElementById('input-formation').value,
            langues: document.getElementById('input-langues').value,
            urlPhoto: document.getElementById('photo-b64').value,
            couleurTheme: document.getElementById('input-couleurTheme').value
        };

        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enregistrement...';

        fetch('save_cv.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error("Server returned non-JSON:", text);
                throw new Error("Erreur de formatage du serveur.");
            }
        })
        .then(res => {
            if(res.success) {
                alert('CV enregistré avec succès !');
                window.location.href = 'cv_my.php';
            } else {
                alert('Erreur: ' + res.message);
                this.disabled = false;
                this.innerHTML = 'Enregistrer le CV';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur technique: ' + err.message);
            this.disabled = false;
            this.innerHTML = 'Enregistrer le CV';
        });
    });
});
</script>
