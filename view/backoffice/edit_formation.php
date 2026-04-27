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
$listeFormations = $formationC->listerFormations()->fetchAll();

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
    <form action="../../controller/traitement_edit.php" method="POST" enctype="multipart/form-data" class="auth-form" style="max-width: 600px;">
        <input type="hidden" name="id_formation" value="<?php echo $formation['id_formation']; ?>">

        <div class="form-group">
            <label class="form-label">Titre de la formation <span class="required-star">*</span></label>
            <div class="input-validated-wrap" style="position:relative;">
                <input type="text" class="input iv-field" name="titre" id="ef-titre" data-min="3" data-label="Titre"
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
                    <input type="text" class="input iv-field" name="domaine" id="ef-domaine" data-min="2" data-label="Domaine"
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
                    <input type="date" class="input iv-field" name="date_formation" id="ef-date" min="<?php echo date('Y-m-d'); ?>" data-min="1" data-label="Date de début"
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
            <label class="form-label">Durée</label>
            <input type="text" class="input" name="duree"
                value="<?php echo htmlspecialchars($formation['duree']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Tuteur <span class="required-star">*</span></label>
            <div class="input-validated-wrap" style="position:relative;">
                <select class="select iv-field" name="id_tuteur" id="ef-tuteur" data-min="1" data-label="Tuteur" style="appearance:auto;">
                    <option value="">Sélectionnez un tuteur...</option>
                    <?php foreach ($tuteurs as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php if ($formation['id_tuteur'] == $t['id'])
                            echo 'selected'; ?>>
                            <?php echo htmlspecialchars($t['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="iv-status" style="position:absolute;right:32px;top:50%;transform:translateY(-50%);display:none;"></span>
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
            <label class="form-label">Format</label>
            <select class="select" name="is_online" id="lieu-select-edit">
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

        <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>

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
    // ── VALIDATION CHAMPS EN TEMPS RÉEL (iv = inline-validation) ──
    function ivValidate(input) {
        const wrap     = input.closest('.input-validated-wrap');
        const statusEl = wrap ? wrap.querySelector('.iv-status') : null;
        const msgEl    = document.getElementById(input.id + '-msg');
        const min      = parseInt(input.dataset.min || 0);
        const label    = input.dataset.label || 'Ce champ';
        const val      = input.value.trim();
        let valid;

        if (input.tagName === 'SELECT') {
            valid = val !== '';
        } else if (input.type === 'date') {
            valid = val !== '' && !isNaN(Date.parse(val));
            if (valid && input.hasAttribute('min')) {
                valid = val >= input.getAttribute('min');
            }
        } else {
            valid = val.length >= min;
        }

        input.classList.toggle('is-valid',   valid);
        input.classList.toggle('is-invalid', !valid);

        if (statusEl) {
            statusEl.className = 'iv-status ' + (valid ? 'valid' : 'invalid');
            statusEl.style.display = 'inline-flex';
            statusEl.innerHTML = valid
                ? '<i data-lucide="check" style="width:14px;height:14px;color:#10b981;"></i>'
                : '<i data-lucide="alert-circle" style="width:14px;height:14px;color:#ef4444;"></i>';
            if (window.lucide) lucide.createIcons();
        }

        if (msgEl) {
            if (!valid) {
                if (input.type === 'date') {
                    const valDate = new Date(val);
                    const now = new Date();
                    now.setHours(0,0,0,0);
                    if (val === '') {
                        msgEl.textContent = `${label} est requis.`;
                    } else if (valDate < now) {
                        msgEl.textContent = `La date ne peut pas être dans le passé.`;
                    } else {
                        msgEl.textContent = `Date invalide.`;
                    }
                } else {
                    msgEl.textContent = (val.length === 0)
                        ? `${label} est requis.`
                        : `Trop court (min. ${min} caractères).`;
                }
                msgEl.style.display = 'block';
            } else {
                msgEl.textContent = '';
                msgEl.style.display = 'none';
            }
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
    };

    document.getElementById('lieu-select-edit').addEventListener('change', function () {
        document.getElementById('url-field-edit').style.display = (this.value == '1') ? 'block' : 'none';
    });
</script>