<?php
// Session pour les messages flash
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Éditer Formation";
$pageCSS = "formations.css";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../model/Formation.php';

$formationC = new FormationController();
$tuteurs = $formationC->getTuteurs();
$listeFormations = $formationC->listerFormations();

if (isset($_GET['id'])) {
    $formation = $formationC->getFormationById($_GET['id']);
    if (!$formation) {
        header('Location: formations_admin.php?msg=notfound');
        exit();
    }
} else {
    header('Location: formations_admin.php');
    exit();
}

// Affichage des erreurs via la session (depuis traitement_edit.php)
if (isset($_SESSION['flash_error'])) {
    $errorMsg = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>

<div class="back-page-header">
    <div class="back-page-header__row">
        <div>
            <h1>Éditer la Formation</h1>
            <p>Modification de: <strong><?php echo htmlspecialchars($formation['titre']); ?></strong></p>
        </div>
        <a href="formations_admin.php" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
            Retour
        </a>
    </div>
</div>

<div class="card-flat p-4">
    <form id="edit-formation-form" enctype="multipart/form-data" class="auth-form" style="max-width: 600px; margin: 0 auto;" novalidate>
        <input type="hidden" name="action" value="edit_formation">
        <input type="hidden" name="id_formation" value="<?php echo $formation['id_formation']; ?>">

        <div class="form-group">
            <label class="form-label">Titre de la formation <span class="required-star">*</span></label>
            <div class="input-validated-wrap" style="position:relative;">
                <input type="text" class="input iv-field" name="titre" id="ef-titre"
                       value="<?php echo htmlspecialchars($formation['titre']); ?>">
                <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
            </div>
            <span class="iv-msg" id="ef-titre-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;font-weight:600;"></span>
        </div>

        <div class="form-group" style="padding-bottom: 25px;">
            <label class="form-label">Description (Contenu Riche) <span class="required-star">*</span></label>
            <textarea class="textarea" name="description" id="hidden-description-edit" style="display:none;"><?php echo htmlspecialchars($formation['description']); ?></textarea>
            <div id="quill-editor-edit" style="height: 150px; background: var(--bg-surface);">
                <?php echo $formation['description']; ?>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div class="form-group">
                <label class="form-label">Domaine <span class="required-star">*</span></label>
                <div class="input-validated-wrap" style="position:relative;">
                    <input type="text" class="input iv-field" name="domaine" id="ef-domaine"
                        value="<?php echo htmlspecialchars($formation['domaine']); ?>">
                    <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                </div>
                <span class="iv-msg" id="ef-domaine-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;font-weight:600;"></span>
            </div>
            <div class="form-group">
                <label class="form-label">Niveau <span class="required-star">*</span></label>
                <select class="select" name="niveau">
                    <option <?php if ($formation['niveau'] == 'Débutant')
                        echo 'selected'; ?>>Débutant</option>
                    <option <?php if ($formation['niveau'] == 'Intermédiaire')
                        echo 'selected'; ?>>Intermédiaire</option>
                    <option <?php if ($formation['niveau'] == 'Avancé')
                        echo 'selected'; ?>>Avancé</option>
                    <option <?php if ($formation['niveau'] == 'Expert')
                        echo 'selected'; ?>>Expert</option>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div class="form-group">
                <label class="form-label">Date de début <span class="required-star">*</span></label>
                <div class="input-validated-wrap" style="position:relative;">
                    <input type="date" class="input iv-field" name="date_formation" id="ef-date" 
                        value="<?php echo date('Y-m-d', strtotime($formation['date_formation'])); ?>">
                    <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                </div>
                <span class="iv-msg" id="ef-date-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;font-weight:600;"></span>
            </div>
            <div class="form-group">
                <label class="form-label">Date de fin (Optionnel)</label>
                <div class="input-validated-wrap" style="position:relative;">
                    <input type="date" class="input" name="date_fin" id="ef-date-fin"
                        value="<?php echo !empty($formation['date_fin']) ? date('Y-m-d', strtotime($formation['date_fin'])) : ''; ?>">
                </div>
                <p style="font-size: 0.7rem; color: #64748b; margin-top: 4px;">Le cours disparaîtra du catalogue 48h après cette date.</p>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Durée (ex: 10h)</label>
            <div class="input-validated-wrap" style="position:relative;">
                <input type="text" class="input" name="duree" id="ef-duree"
                    value="<?php echo htmlspecialchars($formation['duree']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tuteur <span class="required-star">*</span></label>
            <div class="input-validated-wrap" style="position:relative;">
                <select class="select iv-field" name="id_tuteur" id="ef-tuteur" style="appearance:auto;">
                    <option value="">Sélectionnez un tuteur...</option>
                    <?php foreach ($tuteurs as $t): ?>
                        <option value="<?php echo $t['id_utilisateur']; ?>" <?php if ($formation['id_tuteur'] == $t['id_utilisateur'])
                            echo 'selected'; ?>>
                            <?php echo htmlspecialchars($t['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="iv-msg" id="ef-tuteur-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;font-weight:600;"></span>
        </div>

        <div class="form-group">
            <label class="form-label">Prérequis (Optionnel)</label>
            <select class="select" name="prerequis_id">
                <option value="">Aucun prérequis</option>
                <?php foreach ($listeFormations as $f_pre): ?>
                    <?php if ($f_pre['id_formation'] != $formation['id_formation']): ?>
                        <option value="<?php echo $f_pre['id_formation']; ?>" <?php echo ($formation['prerequis_id'] ?? null) == $f_pre['id_formation'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($f_pre['titre']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Image (Laissez vide pour conserver l'actuelle)</label>
            <?php if ($formation['image_base64']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?php echo $formation['image_base64']; ?>" alt=""
                        style="width:120px; height:68px; object-fit:cover; border-radius:4px;">
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
        </div>

        <div class="form-group">
            <label class="form-label">Format / Lieu <span class="required-star">*</span></label>
            <select class="select iv-field" name="is_online" id="ef-lieu">
                <option value="0" <?php if ($formation['is_online'] == 0)
                    echo 'selected'; ?>>📍 Présentiel</option>
                <option value="1" <?php if ($formation['is_online'] == 1)
                    echo 'selected'; ?>>🌐 En ligne</option>
            </select>
        </div>

        <div class="form-group" id="url-field-edit"
            style="display:<?php echo $formation['is_online'] ? 'block' : 'none'; ?>;">
            <label class="form-label">URL Room</label>
            <input type="url" class="input" name="online_url"
                value="<?php echo htmlspecialchars($formation['lien_api_room'] ?? ''); ?>">
        </div>

        <button class="btn btn-primary" type="submit" id="btn-save-edit">
            <span class="btn-text">Enregistrer les modifications</span>
            <span class="btn-loader" style="display:none;"><i data-lucide="loader-2" class="animate-spin" style="width:18px;height:18px;"></i></span>
        </button>

    </form>
</div>

<script>
    // Initialisation de Quill
    var quillEdit = new Quill('#quill-editor-edit', {
        theme: 'snow',
        placeholder: 'Saisissez le corps du rapport ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    // Synchronisation onSubmit
    var formEdit = document.querySelector('form.auth-form');
    function ivValidate(input) {
        const wrap = input.closest('.input-validated-wrap');
        const statusEl = wrap ? wrap.querySelector('.iv-status') : null;
        const msgEl = document.getElementById(input.id + '-msg');
        const val = input.value.trim();
        let valid = true;
        let error = "";

        // 🛡️ LOGIQUE DE VALIDATION CENTRALISÉE (PAS DE HTML)
        if (input.id === 'ef-titre') {
            if (val.length === 0) { valid = false; error = "Le titre est obligatoire."; }
            else if (val.length < 3) { valid = false; error = "Le titre doit faire plus de 3 caractères."; }
        }
        else if (input.id === 'ef-domaine') {
            if (val.length === 0) { valid = false; error = "Le domaine est obligatoire."; }
        }
        else if (input.id === 'ef-date') {
            if (val === "") { valid = false; error = "La date est obligatoire."; }
        }
        else if (input.id === 'ef-tuteur' || input.name === 'niveau' || input.id === 'ef-lieu') {
            if (val === "") { valid = false; error = "Ce champ est obligatoire."; }
        }
        else if (input.id === 'hidden-description-edit') {
            const text = quillEdit.getText().trim();
            if (text.length === 0) { valid = false; error = "La description est obligatoire."; }
            else if (text.length < 10) { valid = false; error = "La description doit faire plus de 10 caractères."; }
        }

        input.classList.toggle('is-valid',   valid);
        input.classList.toggle('is-invalid', !valid);

        if (statusEl) {
            statusEl.style.display = (val !== '') ? 'inline-flex' : 'none';
            statusEl.className = 'iv-status ' + (valid ? 'valid' : 'invalid');
            statusEl.innerHTML = valid ? '✓' : '⚠';
        }

        if (msgEl) {
            msgEl.textContent = error;
            msgEl.style.display = error ? 'block' : 'none';
        }
        return valid;
    }

    document.querySelectorAll('.iv-field').forEach(input => {
        ['input', 'blur', 'change'].forEach(ev => {
            input.addEventListener(ev, () => ivValidate(input));
        });
        // Valider au chargement pour afficher l'état initial (édition)
        ivValidate(input);
    });

    // Blocage soumission si invalide
    formEdit.onsubmit = function(e) {
        let allOk = true;
        document.querySelectorAll('.iv-field').forEach(f => { if (!ivValidate(f)) allOk = false; });
        
        var hiddenDesc = document.querySelector('#hidden-description-edit');
        hiddenDesc.value = quillEdit.root.innerHTML;
        if (quillEdit.getText().trim().length < 10) {
            aptusAlert("Veuillez saisir une description un peu plus longue.", "error");
            allOk = false;
        }

        if (!allOk) {
            e.preventDefault();
            return false;
        }

        // --- Soumission AJAX ---
        e.preventDefault();
        const btn = document.getElementById('btn-save-edit');
        const btnText = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');

        btn.disabled = true;
        btnText.style.opacity = '0.5';
        btnLoader.style.display = 'inline-flex';

        const formData = new FormData(formEdit);
        // On s'assure que le contenu Quill est bien envoyé
        formData.set('description', quillEdit.root.innerHTML);

        fetch('ajax_handler_back.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                aptusAlert(d.message, 'success');
                setTimeout(() => window.location.href = 'formations_admin.php', 1500);
            } else {
                aptusAlert(d.message || 'Erreur lors de la modification.', 'error');
                btn.disabled = false;
                btnText.style.opacity = '1';
                btnLoader.style.display = 'none';
            }
        })
        .catch(err => {
            aptusAlert('Erreur réseau : ' + err.message, 'error');
            btn.disabled = false;
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
        });

        return false;
    };

    const lieuSelectEdit = document.getElementById('ef-lieu');
    if (lieuSelectEdit) {
        lieuSelectEdit.addEventListener('change', function () {
            document.getElementById('url-field-edit').style.display = (this.value == '1') ? 'block' : 'none';
        });
    }
</script>
