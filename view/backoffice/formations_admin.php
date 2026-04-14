<?php
// On démarre la session ici (pas dans config.php pour respecter le squelette du tuteur)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Inclusion du contrôleur et du modèle (architecture MVC)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../model/Formation.php';

$formationC = new FormationController();

// Traitement de la suppression via GET
// On passe par le contrôleur (pas de requête SQL directe dans la vue)
if (isset($_GET['delete_id'])) {
    try {
        $formationC->deleteFormation($_GET['delete_id']);
        $_SESSION['flash_success'] = "Formation supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    header('Location: formations_admin.php');
    exit();
}

// Récupération des données pour peupler la vue
$listeFormations = $formationC->listerFormations()->fetchAll();
$tuteurs = $formationC->getTuteurs();
$totalFormations = count($listeFormations);

// Valeurs statiques pour les autres stats (à dynamiser plus tard avec InscriptionC)
$statsGlobales = $formationC->getStatsGlobales();
$totalInscrits = $statsGlobales['total_inscrits'];
$certificats = $statsGlobales['certificats'];
$tauxCompletion = $statsGlobales['taux_completion'];

$pageTitle = "Formations";
$pageCSS = "formations.css";
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
            <h1>Gestion des Formations</h1>
            <p>Catalogue actuel : <strong>
                    <?php echo $totalFormations; ?>
                </strong> formations publiées</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'has_inscrits'): ?>
            <div style="color: red; margin: 0 15px; font-weight: bold;">
                Erreur: Impossible de supprimer une formation qui a des étudiants inscrits.
            </div>
        <?php endif; ?>


        <button class="btn btn-primary" data-modal="add-formation-modal" id="add-formation-btn">
            <i data-lucide="plus" style="width:18px;height:18px;"></i>
            Ajouter une formation
        </button>
    </div>
</div>

<div class="grid grid-4 gap-6 mb-8 stagger">
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Total Formations</div>
            <div class="stat-card__value">
                <?php echo $totalFormations; ?>
            </div>
        </div>
        <div class="stat-card__icon purple"><i data-lucide="graduation-cap" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Étudiants inscrits</div>
            <div class="stat-card__value">
                <?php echo number_format($totalInscrits); ?>
            </div>
        </div>
        <div class="stat-card__icon teal"><i data-lucide="users" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Certificats délivrés</div>
            <div class="stat-card__value">
                <?php echo $certificats; ?>
            </div>
        </div>
        <div class="stat-card__icon blue"><i data-lucide="award" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Taux de complétion</div>
            <div class="stat-card__value"><?php echo $tauxCompletion; ?>%</div>
        </div>
        <div class="stat-card__icon orange"><i data-lucide="target" style="width:22px;height:22px;"></i></div>
    </div>
</div>

<div class="card-flat" style="overflow:hidden;">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
        <h3 class="text-md fw-semibold">Liste du catalogue</h3>
        <div class="search-bar" style="max-width:280px;">
            <i data-lucide="search" style="width:16px;height:16px;"></i>
            <input type="text" class="input" placeholder="Rechercher..." id="admin-formation-search">
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Formation</th>
                <th>Domaine</th>
                <th>Niveau</th>
                <th>Lieu</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listeFormations as $f):
                $levelClass = 'badge-neutral';
                switch ($f['niveau']) {
                    case 'Débutant':
                        $levelClass = 'badge-success';
                        break;
                    case 'Intermédiaire':
                        $levelClass = 'badge-warning';
                        break;
                    case 'Avancé':
                        $levelClass = 'badge-danger';
                        break;
                    case 'Expert':
                        $levelClass = 'badge-primary';
                        break;
                }
                ?>
                <tr>
                    <td class="fw-medium">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="<?php echo $f['image_base64']; ?>" alt=""
                                style="width:45px; height:25px; object-fit:cover; border-radius:4px; background:#eee;">
                            <?php echo htmlspecialchars($f['titre']); ?>
                        </div>
                    </td>
                    <td><span class="badge badge-info">
                            <?php echo htmlspecialchars($f['domaine']); ?>
                        </span></td>
                    <td><span class="badge <?php echo $levelClass; ?>">
                            <?php echo $f['niveau']; ?>
                        </span></td>
                    <td class="text-sm">
                        <i data-lucide="<?php echo $f['is_online'] ? 'video' : 'map-pin'; ?>"
                            style="width:14px; height:14px; vertical-align:middle; margin-right:5px;"></i>
                        <?php echo $f['is_online'] ? 'En ligne' : 'Présentiel'; ?>
                    </td>
                    <td class="text-sm text-secondary">
                        <?php echo date('d M. Y', strtotime($f['date_formation'])); ?>
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <a href="edit_formation.php?id=<?php echo $f['id_formation']; ?>" class="btn btn-sm btn-ghost"
                                title="Éditer">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </a>
                            <a href="formations_admin.php?delete_id=<?php echo $f['id_formation']; ?>"
                                class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"
                                onclick="return confirm('Supprimer définitivement cette formation ?');">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="add-formation-modal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3>Nouvelle Formation</h3>
            <button class="modal-close btn-icon"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <form action="traitement_add.php" method="POST" enctype="multipart/form-data" id="add-formation-form"
                class="auth-form">

                <div class="form-group">
                    <label class="form-label">Titre de la formation</label>
                    <input type="text" class="input" name="titre" placeholder="Ex: Masterclass IA">
                </div>

                <div class="form-group" style="padding-bottom: 25px;">
                    <label class="form-label">Description (Contenu Riche)</label>
            <!-- Textarea caché qui contient le HTML de Quill -->
            <!-- C'est ce champ qui est envoyé dans le POST, pas le div Quill -->
                    <textarea class="textarea" name="description" id="hidden-description" style="display:none;"></textarea>
                    <div id="quill-editor" style="height: 150px; background: var(--bg-surface);"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Domaine</label>
                        <input type="text" class="input" name="domaine">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Niveau</label>
                        <select class="select" name="niveau">
                            <option>Débutant</option>
                            <option>Intermédiaire</option>
                            <option>Avancé</option>
                            <option>Expert</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Date de début</label>
                        <input type="date" class="input" name="date_formation">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Durée (ex: 10h)</label>
                        <input type="text" class="input" name="duree">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tuteur</label>
                    <select class="select" name="id_tuteur">
                        <option value="">Sélectionnez un tuteur...</option>
                        <?php foreach ($tuteurs as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Image (16:9)</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Format</label>
                    <select class="select" name="is_online" id="lieu-select">
                        <option value="0">📍 Présentiel</option>
                        <option value="1">🌐 En ligne</option>
                    </select>
                </div>

                <!-- Si online et pas d'URL fournie, le contrôleur génère un lien Jitsi auto -->
                <div class="form-group" id="url-field" style="display:none;">
                    <label class="form-label">URL Room (Laissez vide pour Jitsi auto)</label>
                    <input type="url" class="input" name="online_url">
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Annuler</button>
            <button class="btn btn-primary" type="submit" form="add-formation-form">Créer la formation</button>
        </div>
    </div>
</div>

<script>
    // Initialisation de Quill (Éditeur de texte riche)
    var quill = new Quill('#quill-editor', {
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

    // Synchroniser Quill avec le textarea caché avant l'envoi du formulaire
    var form = document.querySelector('#add-formation-form');
    form.onsubmit = function() {
        var hiddenDesc = document.querySelector('#hidden-description');
        hiddenDesc.value = quill.root.innerHTML;
        // On check si c'est pas vide
        if (quill.getText().trim().length === 0) {
            hiddenDesc.value = "";
        }
    };

    // Toggle dynamique du champ URL
    document.getElementById('lieu-select').addEventListener('change', function () {
        document.getElementById('url-field').style.display = (this.value == '1') ? 'block' : 'none';
    });
</script>