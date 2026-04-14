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

// Traitement du formulaire d'édition (quand on clique sur "Enregistrer")
// Même validation que l'ajout : passe par validateFormation() du contrôleur
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $is_online = (int) $_POST['is_online'];
    $lien_room = trim($_POST['online_url'] ?? '');

    // On garde l'ancienne image si l'admin ne remet pas de fichier
    $image_base64 = $formation['image_base64'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        $type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
    }

    try {
        $f = new Formation(
            $_POST['titre'],
            $_POST['description'],
            $_POST['domaine'],
            $_POST['niveau'],
            $_POST['duree'] ?? '0',
            $_POST['date_formation'],
            $image_base64,
            !empty($_POST['id_tuteur']) ? (int) $_POST['id_tuteur'] : null,
            $is_online,
            $lien_room
        );

        $formationC->updateFormation($f, $_GET['id']);
        $_SESSION['flash_success'] = "Formation modifiée avec succès.";
        header('Location: formations_admin.php');
        exit();
    } catch (Exception $e) {
        $error_msg = "Erreur de validation : " . $e->getMessage();
        // Optionnel : passer à flash_error si on retourne sur la même vue après un header, mais ici il affiche sur la page
    }
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
    <form action="" method="POST" enctype="multipart/form-data" class="auth-form" style="max-width: 600px;">

        <div class="form-group">
            <label class="form-label">Titre de la formation</label>
            <input type="text" class="input" name="titre" value="<?php echo htmlspecialchars($formation['titre']); ?>">
        </div>

        <div class="form-group" style="padding-bottom: 25px;">
            <label class="form-label">Description (Contenu Riche)</label>
            <textarea class="textarea" name="description" id="hidden-description-edit" style="display:none;"><?php echo htmlspecialchars($formation['description']); ?></textarea>
            <div id="quill-editor-edit" style="height: 150px; background: var(--bg-surface);">
                <?php echo $formation['description']; ?>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div class="form-group">
                <label class="form-label">Domaine</label>
                <input type="text" class="input" name="domaine"
                    value="<?php echo htmlspecialchars($formation['domaine']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Niveau</label>
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
                <label class="form-label">Date de début</label>
                <input type="date" class="input" name="date_formation"
                    value="<?php echo date('Y-m-d', strtotime($formation['date_formation'])); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Durée</label>
                <input type="text" class="input" name="duree"
                    value="<?php echo htmlspecialchars($formation['duree']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tuteur</label>
            <select class="select" name="id_tuteur">
                <option value="">Sélectionnez un tuteur...</option>
                <?php foreach ($tuteurs as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php if ($formation['id_tuteur'] == $t['id'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t['nom']); ?></option>
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
    formEdit.onsubmit = function() {
        var hiddenDesc = document.querySelector('#hidden-description-edit');
        hiddenDesc.value = quillEdit.root.innerHTML;
        if (quillEdit.getText().trim().length === 0) {
            hiddenDesc.value = "";
        }
    };

    document.getElementById('lieu-select-edit').addEventListener('change', function () {
        document.getElementById('url-field-edit').style.display = (this.value == '1') ? 'block' : 'none';
    });
</script>