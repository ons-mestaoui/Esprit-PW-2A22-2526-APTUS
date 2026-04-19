<?php 
$pageTitle = "Mes Postes"; 
$pageCSS = "feeds.css"; 
$userRole = "Entreprise"; 

// --- CONTROLLER INCLUSION ---
require_once '../../controller/offreC.php';
require_once '../../model/offre.php';

$offreC = new offreC();
$action = $_GET['action'] ?? 'list';

$errors = [];
$form_data = [];

// --- LOGIQUE CRUD AVEC VALIDATION PHP STRICTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération sécurisée et nettoyage
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $domaine = trim($_POST['domaine'] ?? '');
    $competences = trim($_POST['competences_requises'] ?? '');
    $experience = trim($_POST['experience_requise'] ?? '');
    $salaire = trim($_POST['salaire'] ?? '');
    $question = trim($_POST['question'] ?? '');
    
    // Forçage de la date de publication selon l'action (Add = Aujourd'hui, Edit = On garde l'ancienne date)
    if (isset($_POST['submit_add'])) {
        $date_pub = date('Y-m-d');
    } else {
        $date_pub = trim($_POST['date_publication'] ?? date('Y-m-d'));
    }
    
    $date_exp = trim($_POST['date_expir'] ?? '');

    // Traitement de l'image (si envoyée)
    $img_post_base64 = null;
    if (isset($_FILES['img_post']) && $_FILES['img_post']['error'] === UPLOAD_ERR_OK) {
        $max_size = 1 * 1024 * 1024; // 1 MB
        if ($_FILES['img_post']['size'] > $max_size) {
            $errors['img_post'] = "L'image ne doit pas dépasser 1 Mo.";
        } else {
            $file_tmp = $_FILES['img_post']['tmp_name'];
            $file_type = mime_content_type($file_tmp);
            if (strpos($file_type, 'image/') !== 0) {
                $errors['img_post'] = "Le fichier doit être une image valide.";
            } else {
                $img_data = file_get_contents($file_tmp);
                $img_post_base64 = 'data:' . $file_type . ';base64,' . base64_encode($img_data);
            }
        }
    }

    // Sauvegarde des données pour réaffichage si erreur
    $form_data = [
        'titre' => $titre,
        'description' => $description,
        'domaine' => $domaine,
        'competences_requises' => $competences,
        'experience_requise' => $experience,
        'salaire' => $salaire,
        'question' => $question,
        'date_publication' => $date_pub,
        'date_expir' => $date_exp
    ];

    // ---- RULES PHP ----
    if (strlen($titre) < 3) {
        $errors['titre'] = "Le titre doit contenir au moins 3 caractères.";
    }
    if (empty($description)) {
        $errors['description'] = "La description est obligatoire.";
    }
    if (empty($domaine)) {
        $errors['domaine'] = "Le domaine est obligatoire.";
    }
    if (!is_numeric($salaire) || (float)$salaire <= 0) {
        $errors['salaire'] = "Le salaire doit être un nombre positif.";
    }
    if (empty($competences)) {
        $errors['competences_requises'] = "Ce champ est obligatoire.";
    }
    if (empty($experience)) {
        $errors['experience_requise'] = "L'expérience requise est obligatoire.";
    }
    if (empty($question)) {
        $errors['question'] = "Veuillez poser une question pour filtrer vos candidats.";
    }
    if (empty($date_pub)) {
        $errors['date_publication'] = "La date de publication est obligatoire.";
    }
    if (empty($date_exp)) {
        $errors['date_expir'] = "La date d'expiration est obligatoire.";
    } elseif (!empty($date_pub) && strtotime($date_exp) < strtotime($date_pub)) {
        $errors['date_expir'] = "La date d'expiration ne peut pas précéder la date de publication.";
    }

    // SI AUCUNE ERREUR: ON SAUVEGARDE EN BDD
    if (empty($errors)) {
        $offre = new offre(
            $titre, 
            $description, 
            $domaine, 
            $competences, 
            $experience, 
            (float)$salaire, 
            $question, 
            $date_pub, 
            $date_exp,
            $img_post_base64
        );
        
        if (isset($_POST['submit_add'])) {
            $offreC->ajouterOffre($offre);
        } elseif (isset($_POST['submit_update']) && isset($_GET['id'])) {
            $offreC->modifierOffre($offre, $_GET['id']);
        }
        
        header("Location: hr_posts.php");
        exit();
    } else {
        // En cas d'erreur, on reste sur le formulaire (add ou edit)
        if (isset($_POST['submit_add'])) {
            $action = 'add';
        } else if (isset($_POST['submit_update'])) {
            $action = 'edit';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $offreC->supprimerOffre($_GET['id']);
    header("Location: hr_posts.php");
    exit();
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php (Enterprise view) -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="briefcase" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Mes Postes
  </h1>
  <p class="page-header__subtitle">Gérez vos offres d'emploi publiées</p>
</div>

<div class="hr-layout">
  <!-- ═══ MAIN CONTENT ═══ -->
  <div>
    <?php if ($action === 'list'): ?>
      <?php 
        $q_hr = trim($_GET['q'] ?? '');
        $filter_status = $_GET['filter_status'] ?? '';
        if ($q_hr !== '') {
            $listeOffres = $offreC->recherche_offre($q_hr);
        } elseif (!empty($filter_status) && $filter_status !== 'Tous statuts') {
            $listeOffres = $offreC->filtrerOffres(['statut' => $filter_status]);
        } else {
            $listeOffres = $offreC->afficherOffres();
        }
        $count = $listeOffres->rowCount();
      ?>
      
      <div class="results-info mb-4">
        <strong><?php echo $count; ?></strong> postes publiés
      </div>

      <div class="hr-posts-grid stagger">
        <?php foreach ($listeOffres as $offreItem): ?>
        <div class="hr-post-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
          <?php if (!empty($offreItem['img_post'])): ?>
            <div style="height: 140px; background-image: url('<?php echo htmlspecialchars($offreItem['img_post']); ?>'); background-size: cover; background-position: center; position: relative;">
          <?php else: ?>
            <div style="height: 80px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%); position: relative; display: flex; align-items: center; justify-content: center;">
               <i data-lucide="image" style="width: 32px; height: 32px; color: var(--text-secondary); opacity: 0.5;"></i>
          <?php endif; ?>
               <div style="position: absolute; top: 12px; right: 12px;">
                    <?php if (isset($offreItem['statut']) && $offreItem['statut'] === 'Expiré'): ?>
                        <span class="badge badge-danger" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Expiré</span>
                    <?php else: ?>
                        <span class="badge badge-success" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Actif</span>
                    <?php endif; ?>
               </div>
            </div>
            
            <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                <h3 class="hr-post-card__title" style="margin-top: 0;"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
                <p class="text-sm text-secondary mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($offreItem['description'] ?? ''); ?></p>
                <div class="hr-post-card__stats">
                    <span class="hr-post-card__stat" title="Domaine">
                    <i data-lucide="folder" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?>
                    </span>
                    <span class="hr-post-card__stat" title="Date de Publication">
                    <i data-lucide="calendar" style="width:14px;height:14px;"></i> Publié: <?php echo htmlspecialchars($offreItem['date_publication'] ?? ''); ?>
                    </span>
                    <span class="hr-post-card__stat" title="Salaire">
                    <i data-lucide="coins" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['salaire'] ?? ''); ?> TND
                    </span>
                </div>
                <div class="hr-post-card__actions" style="margin-top: auto; padding-top: 1rem;">
                    <a href="hr_posts.php?action=edit&id=<?php echo $offreItem['id_offre']; ?>" class="btn btn-sm btn-secondary text-decoration-none d-flex align-items-center gap-1"><i data-lucide="pencil" style="width:14px;height:14px;"></i> Éditer</a>
                    <button class="btn btn-sm btn-ghost d-flex align-items-center gap-1"><i data-lucide="users" style="width:14px;height:14px;"></i> Candidats</button>
                    <button type="button" onclick="confirmDelete(<?php echo $offreItem['id_offre']; ?>, '<?php echo htmlspecialchars(addslashes($offreItem['titre'] ?? '')); ?>')" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary); margin-left: auto;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if ($count == 0): ?>
            <div class="empty-state text-center" style="padding: 3rem; background: var(--surface-1); border-radius: 12px; grid-column: 1 / -1;">
                <p>Aucune offre trouvée. Commencez par en poster une !</p>
            </div>
        <?php endif; ?>
      </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
      <?php 
        $offreEdit = null;
        if ($action === 'edit' && isset($_GET['id']) && empty($form_data)) {
            // Uniquement si form_data est vide (donc 1er chargement GET, pas un retour suite erreur POST)
            $offreEdit = $offreC->getOffreById($_GET['id']);
        }
        
        // Helper pour afficher les valeurs
        function val($field, $form_data, $offreEdit, $default = '') {
            if (isset($form_data[$field])) return $form_data[$field];
            if (isset($offreEdit[$field])) return $offreEdit[$field];
            return $default;
        }
      ?>
      <div class="form-container" style="background:var(--surface-1); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color);">
        <h2 class="mb-4 text-xl fw-bold d-flex align-items-center gap-2">
            <i data-lucide="<?php echo $action === 'edit' ? 'pencil' : 'plus-circle'; ?>" style="width:24px;height:24px;color:var(--accent-primary);"></i>
            <?php echo $action === 'edit' ? 'Modifier l\'offre' : 'Nouvelle offre'; ?>
        </h2>
        
        <?php if (!empty($errors)): ?>
        <div style="background: rgba(220, 38, 38, 0.1); border-left: 4px solid #dc2626; color: #dc2626; padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
            <strong>Erreur !</strong> Veuillez corriger les erreurs dans le formulaire ci-dessous.
        </div>
        <?php endif; ?>

        <!-- Validation HTML5 désactivée via la suppression des attributs required -->
        <form method="POST" enctype="multipart/form-data" action="hr_posts.php?<?php echo ($action === 'edit' && isset($_GET['id'])) ? 'action=edit&id='.$_GET['id'] : 'action=add'; ?>">
            
            <div class="form-group mb-3">
                <label class="form-label" for="titre">Titre de l'offre</label>
                <input type="text" class="input <?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" name="titre" id="titre" value="<?php echo htmlspecialchars(val('titre', $form_data, $offreEdit)); ?>">
                <?php if (isset($errors['titre'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['titre']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="img_post">Image de couverture de l'offre (Optionnelle, Max 1Mo)</label>
                <!-- Le champ accepte uniquement des images -->
                <input type="file" class="input <?php echo isset($errors['img_post']) ? 'has-error' : ''; ?>" name="img_post" id="img_post" accept="image/*" style="padding: 10px;">
                <?php if ($action === 'edit' && !empty($offreEdit['img_post'])): ?>
                    <p class="text-sm text-secondary mt-1">Laissez vide pour conserver l'image actuelle.</p>
                <?php endif; ?>
                <?php if (isset($errors['img_post'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['img_post']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="textarea <?php echo isset($errors['description']) ? 'has-error' : ''; ?>" name="description" id="description" rows="4"><?php echo htmlspecialchars(val('description', $form_data, $offreEdit)); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['description']; ?></span>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap: 1rem;" class="mb-3">
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="domaine">Domaine</label>
                    <input type="text" class="input <?php echo isset($errors['domaine']) ? 'has-error' : ''; ?>" name="domaine" id="domaine" value="<?php echo htmlspecialchars(val('domaine', $form_data, $offreEdit)); ?>">
                    <?php if (isset($errors['domaine'])): ?>
                        <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['domaine']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="salaire">Salaire proposé (TND)</label>
                    <!-- Garder type text autorise le JS/PHP à capturer des mauvaises saisies pour valider explicitement en PHP -->
                    <input type="text" class="input <?php echo isset($errors['salaire']) ? 'has-error' : ''; ?>" name="salaire" id="salaire" value="<?php echo htmlspecialchars(val('salaire', $form_data, $offreEdit)); ?>">
                    <?php if (isset($errors['salaire'])): ?>
                        <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['salaire']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="competences_requises_visual">Compétences Requises (Appuyez sur Entrée)</label>
                <input type="hidden" name="competences_requises" id="competences_requises" value="<?php echo htmlspecialchars(val('competences_requises', $form_data, $offreEdit)); ?>">
                
                <div class="input <?php echo isset($errors['competences_requises']) ? 'has-error' : ''; ?>" id="tags-container" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.5rem; min-height: 45px; align-items: center; cursor: text;">
                    <!-- Les tags seront insérés ici dynamiquement -->
                    <input type="text" id="competences_requises_visual" placeholder="ex: PHP, HTML, CSS..." style="border: none; outline: none; background: transparent; flex: 1; min-width: 120px; color: var(--text-primary);">
                </div>
                <?php if (isset($errors['competences_requises'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['competences_requises']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="experience_requise">Expérience Requise</label>
                <input type="text" class="input <?php echo isset($errors['experience_requise']) ? 'has-error' : ''; ?>" name="experience_requise" id="experience_requise" value="<?php echo htmlspecialchars(val('experience_requise', $form_data, $offreEdit)); ?>" placeholder="ex: 3 à 5 ans">
                <?php if (isset($errors['experience_requise'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['experience_requise']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="question">Question Personnalisée pour les candidats</label>
                <input type="text" class="input <?php echo isset($errors['question']) ? 'has-error' : ''; ?>" name="question" id="question" value="<?php echo htmlspecialchars(val('question', $form_data, $offreEdit)); ?>">
                <?php if (isset($errors['question'])): ?>
                    <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['question']; ?></span>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap: 1rem;" class="mb-3">
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="date_publication">Date de Publication (Automatique)</label>
                    <?php 
                        // Toujours figer à aujourd'hui si Add, ou l'ancienne date si Edit
                        $default_date_pub = ($action === 'edit' && $offreEdit) ? $offreEdit['date_publication'] : date('Y-m-d'); 
                    ?>
                    <input type="date" class="input" name="date_publication" id="date_publication" value="<?php echo htmlspecialchars($default_date_pub); ?>" readonly style="background:var(--bg-body); cursor:not-allowed; color:var(--text-secondary); opacity:0.8;">
                </div>
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="date_expir">Date d'Expiration</label>
                    <input type="date" class="input <?php echo isset($errors['date_expir']) ? 'has-error' : ''; ?>" name="date_expir" id="date_expir" value="<?php echo htmlspecialchars(val('date_expir', $form_data, $offreEdit)); ?>">
                    <?php if (isset($errors['date_expir'])): ?>
                        <span style="color: #dc2626; font-size: 0.85rem; display: block; margin-top: 5px;"><i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:text-bottom;"></i> <?php echo $errors['date_expir']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4" style="display:flex; gap: 1rem;">
                <button type="submit" name="<?php echo $action === 'edit' ? 'submit_update' : 'submit_add'; ?>" class="btn btn-primary d-flex align-items-center gap-2">
                    <i data-lucide="check" style="width:16px;height:16px;"></i>
                    <?php echo $action === 'edit' ? 'Mettre à jour' : 'Publier l\'offre'; ?>
                </button>
                <a href="hr_posts.php" class="btn btn-secondary text-decoration-none">Annuler</a>
            </div>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="hr-sidebar">
    <?php if ($action === 'list'): ?>
    <div class="hr-sidebar__section" style="text-align:center;">
      <a href="hr_posts.php?action=add" class="btn btn-primary btn-lg w-full text-decoration-none d-flex align-items-center justify-content-center gap-2">
        <i data-lucide="plus" style="width:18px;height:18px;"></i> Poster une offre
      </a>
    </div>

    <div class="hr-sidebar__section">
      <form method="GET" action="hr_posts.php" style="display:flex; gap:0.5rem; max-width:100%;">
        <div class="search-bar" style="flex:1;">
          <i data-lucide="search" style="width:16px;height:16px;"></i>
          <input type="text" class="input" placeholder="Rechercher..." id="hr-search" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 0.75rem;"><i data-lucide="search" style="width:14px;height:14px;"></i></button>
      </form>
    </div>
<!-- ═══ appel fonction filtre_statut ═══ -->
    <div class="hr-sidebar__section">
      <form method="GET" action="hr_posts.php" style="margin: 0;">
          <h4 class="text-sm fw-semibold mb-3">Filtrer par statut</h4>
          <label class="cv-sidebar__option"><input type="radio" name="filter_status" value="" onchange="this.form.submit()" <?php echo (empty($_GET['filter_status']) || $_GET['filter_status'] === 'Tous statuts') ? 'checked' : ''; ?>> Tous</label>
          <label class="cv-sidebar__option"><input type="radio" name="filter_status" value="Actif" onchange="this.form.submit()" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Actif') ? 'checked' : ''; ?>> Actif</label>
          <label class="cv-sidebar__option"><input type="radio" name="filter_status" value="Expiré" onchange="this.form.submit()" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Expiré') ? 'checked' : ''; ?>> Expiré</label>
      </form>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Résumé</h4>
      <div style="display:flex;flex-direction:column;gap:var(--space-3);">
        <div class="flex items-center justify-between">
          <span class="text-sm text-secondary">Postes total</span>
          <span class="fw-semibold text-sm"><?php echo isset($count) ? $count : 0; ?></span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<!-- ═══ popup suppression ═══ -->
<script>
var deleteUrl = '';

function confirmDelete(id, titre) {
    var titleEl = document.getElementById('delete-offer-title');
    if(titleEl) titleEl.innerText = titre;
    
    deleteUrl = "hr_posts.php?action=delete&id=" + id;
    
    var overlay = document.getElementById('delete-confirm-modal');
    if (overlay) {
        overlay.classList.add('active');
        var modal = overlay.querySelector('.modal');
        if (modal) modal.classList.add('active');
    }
}

function executeDelete() {
    if(deleteUrl) {
        window.location.href = deleteUrl;
    }
}


// Ensure the cancel button removes the classes (simulating the layout logic)
document.addEventListener('DOMContentLoaded', function() {
    var closeBtns = document.querySelectorAll('.modal-close');
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var overlay = document.getElementById('delete-confirm-modal');
            if (overlay) {
                overlay.classList.remove('active');
                var modal = overlay.querySelector('.modal');
                if (modal) modal.classList.remove('active');
            }
        });
    });
});

// Tags Logic for competences_requises
document.addEventListener('DOMContentLoaded', function() {
    const hiddenInput = document.getElementById('competences_requises');
    const visualInput = document.getElementById('competences_requises_visual');
    const tagsContainer = document.getElementById('tags-container');
    
    if (!hiddenInput || !visualInput || !tagsContainer) return;

    let tags = hiddenInput.value ? hiddenInput.value.split(',').map(t => t.trim()).filter(t => t) : [];

    function renderTags() {
        // Clear all elements except the input
        Array.from(tagsContainer.children).forEach(child => {
            if (child !== visualInput) child.remove();
        });
        
        tags.forEach((tag, index) => {
            const tagEl = document.createElement('span');
            tagEl.style.cssText = "background: #e6f2fd; color: #0d6efd; padding: 0.3rem 0.8rem; border-radius: 16px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;";
            tagEl.innerText = tag;
            
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = "&times;";
            closeBtn.style.cssText = "cursor: pointer; font-weight: bold; font-size: 1.1rem; line-height: 1;";
            closeBtn.onclick = function() {
                tags.splice(index, 1);
                updateHidden();
                renderTags();
            };
            
            tagEl.appendChild(closeBtn);
            tagsContainer.insertBefore(tagEl, visualInput);
        });
    }
    
    function updateHidden() {
        hiddenInput.value = tags.join(', ').trim();
    }
    
    tagsContainer.addEventListener('click', () => visualInput.focus());
    
    visualInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = this.value.trim().replace(/^,|,$/g, '');
            if (val && !tags.includes(val)) {
                tags.push(val);
                updateHidden();
                renderTags();
            }
            this.value = '';
        } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            tags.pop();
            updateHidden();
            renderTags();
        }
    });

    // Empêcher la soumission du formulaire complet lors de la validation d'un tag avec Entrée
    visualInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    renderTags();
});
</script>

<!-- ═══ Delete Confirmation Modal ═══ -->
<div class="modal-overlay" id="delete-confirm-modal">
  <div class="modal" style="max-width:400px; text-align:center;">
    <div class="modal-body" style="padding: 2.5rem 1.5rem;">
      <div style="background: rgba(220, 38, 38, 0.08); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
        <i data-lucide="alert-triangle" style="width:34px;height:34px;color:#dc2626;"></i>
      </div>
      <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-primary);">Confirmation de suppression</h3>
      <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5;">
        Êtes-vous sûr de vouloir supprimer l'offre <br><strong id="delete-offer-title" style="color:var(--text-primary);"></strong> ?<br>Cette action est irréversible.
      </p>
      <div class="flex gap-3 justify-center">
        <button type="button" class="btn btn-secondary modal-close" style="flex:1;">Annuler</button>
        <button type="button" onclick="executeDelete()" class="btn btn-primary" style="flex:1; background: #dc2626; border-color: #dc2626;">
          Oui, Supprimer
        </button>
      </div>
    </div>
  </div>
</div>
