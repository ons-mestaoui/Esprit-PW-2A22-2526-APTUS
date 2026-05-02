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
        'date_expir' => $date_exp,
        'type' => trim($_POST['type'] ?? 'Sur site')
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
            $img_post_base64,
            $form_data['type']
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

<div class="hr-layout" style="<?php echo ($action === 'list') ? 'grid-template-columns: 300px 1fr;' : 'grid-template-columns: 1fr;'; ?>">
  <?php if ($action === 'list'): ?>
  <!-- ═══ SIDEBAR (Gauche) ═══ -->
  <aside class="hr-sidebar">
    
      <a href="hr_posts.php?action=add" class="text-decoration-none" style="display: flex !important; align-items: center; justify-content: center; gap: 0.75rem; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: #FFFFFF; border: none; border-radius: 12px; font-weight: 700; padding: 1rem; box-shadow: 0 4px 20px rgba(168, 100, 228, 0.3); transition: all 0.3s; cursor: pointer; width: 100%; font-size: 1rem; box-sizing: border-box;" 
         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(168, 100, 228, 0.4)';" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(168, 100, 228, 0.3)';">
        <i data-lucide="plus" style="width:20px;height:20px;"></i> 
        <span>Poster une offre</span>
      </a>
    

    <!-- ═══ appel fonction filtre_statut ═══ -->
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
      <form id="filter-form" method="GET" action="hr_posts.php" style="margin: 0;">
          <?php if(!empty($_GET['q'])): ?>
              <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>">
          <?php endif; ?>
          
          <!-- Filter: Statut -->
          <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">STATUT</h4>
          <div style="display: flex; flex-direction: column; gap: 1.25rem;">
              <label class="filter-status-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (empty($_GET['filter_status']) || $_GET['filter_status'] === 'Tous statuts') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (empty($_GET['filter_status']) || $_GET['filter_status'] === 'Tous statuts') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'status')">
                  <input type="radio" name="filter_status" value="Tous statuts" <?php echo (empty($_GET['filter_status']) || $_GET['filter_status'] === 'Tous statuts') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Tous
              </label>
              <label class="filter-status-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Actif') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Actif') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'status')">
                  <input type="radio" name="filter_status" value="Actif" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Actif') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Actif
              </label>
              <label class="filter-status-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Expiré') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Expiré') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'status')">
                  <input type="radio" name="filter_status" value="Expiré" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Expiré') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Expiré
              </label>
          </div>
      </div>

      <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
          <!-- Filter: Type -->
          <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">TYPE DE POSTE</h4>
          <div style="display: flex; flex-direction: column; gap: 1.25rem;">
              <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (empty($_GET['filter_type']) || $_GET['filter_type'] === 'all') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (empty($_GET['filter_type']) || $_GET['filter_type'] === 'all') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'type')">
                  <input type="radio" name="filter_type" value="all" <?php echo (empty($_GET['filter_type']) || $_GET['filter_type'] === 'all') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Tout
              </label>
              <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'À distance') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'À distance') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'type')">
                  <input type="radio" name="filter_type" value="À distance" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'À distance') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  À distance
              </label>
              <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Sur site') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Sur site') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'type')">
                  <input type="radio" name="filter_type" value="Sur site" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Sur site') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Sur site
              </label>
              <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Hybride') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Hybride') ? '600' : '500'; ?>; transition: all 0.2s;" onclick="handleFilterClick(this, 'type')">
                  <input type="radio" name="filter_type" value="Hybride" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Hybride') ? 'checked' : ''; ?> style="position:absolute; opacity:0; width:0; height:0;">
                  Hybride
              </label>
          </div>
      </div>
      
      <script>
      function handleFilterClick(labelElement, category) {
          // Set the radio button to checked
          const radio = labelElement.querySelector('input[type="radio"]');
          if(radio) radio.checked = true;
          
          // Update colors for the specific category
          const selector = category === 'status' ? '.filter-status-label' : '.filter-type-label';
          document.querySelectorAll(selector).forEach(lbl => {
              lbl.style.color = 'var(--text-secondary)';
              lbl.style.fontWeight = '500';
          });
          labelElement.style.color = 'var(--accent-primary)';
          labelElement.style.fontWeight = '600';
          
          // Trigger search
          const searchInput = document.getElementById('ajax-search-input');
          const query = searchInput ? searchInput.value : '';
          fetchHrPostsSearch(query);
      }
      </script>
      </form>
    </aside>
  <?php endif; ?>

  <!-- ═══ MAIN CONTENT (Droite) ═══ -->
  <div>
    <?php if ($action === 'list'): ?>
      <?php 
        $filter_status = $_GET['filter_status'] ?? '';
        if (!empty($filter_status) && $filter_status !== 'Tous statuts') {
            $listeOffres = $offreC->filtrerOffres(['statut' => $filter_status]);
        } else {
            $listeOffres = $offreC->afficherOffres();
        }
        $count = $listeOffres->rowCount();
      ?>

      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
          <div class="results-info" style="background: var(--bg-card); padding: 0.6rem 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 0.6rem; color: var(--text-primary); font-weight: 500; font-size: 0.95rem;">
            <span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;"><?php echo $count; ?></span>
            <span>postes publiés au total</span>
          </div>

          <!-- ═══ VIEW TOGGLE ═══ -->
          <div style="background: var(--bg-card); padding: 4px; border-radius: 12px; display: flex; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
              <button onclick="setViewMode('grid')" id="view-grid-btn" title="Vue Grille" style="border: none; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: white;">
                  <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
              </button>
              <button onclick="setViewMode('list')" id="view-list-btn" title="Vue Liste" style="border: none; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; color: var(--text-tertiary);">
                  <i data-lucide="list" style="width: 18px; height: 18px;"></i>
              </button>
          </div>
      </div>
      
      <!-- ═══ BARRE DE RECHERCHE DYNAMIQUE ═══ -->
      <div style="background: var(--bg-card); border-radius: 20px; padding: 0.75rem 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 2rem; border: 1px solid var(--border-color);">
          <div style="display: flex; align-items: center; gap: 1rem; margin: 0;">
              <div style="flex: 1; position: relative; display: flex; align-items: center;">
                  <i data-lucide="search" style="position: absolute; left: 1.25rem; width: 20px; height: 20px; color: var(--text-tertiary);"></i>
                  <input type="text" id="ajax-search-input" name="q" autocomplete="off" placeholder="Rechercher une offre..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                         style="width: 100%; padding: 1rem 1rem 1rem 3.5rem; border: 1px solid var(--border-color); border-radius: 14px; font-size: 1rem; outline: none; transition: all 0.2s; background: var(--bg-secondary); color: var(--text-primary);"
                         onfocus="this.style.borderColor='var(--accent-primary)'; this.style.background='var(--bg-card)';" 
                         onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-secondary)';"
                         class="search-input-field">
              </div>
              <div id="search-spinner" style="display: none;">
                  <div class="spinner-border text-primary" role="status" style="width: 24px; height: 24px; border: 3px solid rgba(168, 100, 228, 0.2); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
              </div>
          </div>
      </div>
      <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

      

      <div class="hr-posts-grid stagger" id="posts-container">
        <?php foreach ($listeOffres as $offreItem): ?>
        <div class="hr-post-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
          <?php if (!empty($offreItem['img_post'])): ?>
            <div style="height: 140px; background-image: url('<?php echo htmlspecialchars($offreItem['img_post']); ?>'); background-size: cover; background-position: center; position: relative;">
          <?php else: ?>
            <div style="height: 80px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%); position: relative; display: flex; align-items: center; justify-content: center;">
               <i data-lucide="image" style="width: 32px; height: 32px; color: var(--text-secondary); opacity: 0.5;"></i>
          <?php endif; ?>
               <div style="position: absolute; top: 12px; right: 12px;" class="image-status-badge">
                    <?php if (isset($offreItem['statut']) && $offreItem['statut'] === 'Expiré'): ?>
                        <span class="badge badge-danger" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Expiré</span>
                    <?php else: ?>
                        <span class="badge badge-success" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Actif</span>
                    <?php endif; ?>
               </div>
            </div>
            
            <div style="padding: 0.75rem 1.5rem; flex: 1; display: flex; flex-direction: column;" class="hr-post-card__content">
                <div class="hr-post-card__main-info">
                    <span class="post-id-badge">#<?php echo $offreItem['id_offre']; ?></span>
                    <h3 class="hr-post-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
                    <div class="status-badge-inline">
                        <?php if (isset($offreItem['statut']) && $offreItem['statut'] === 'Expiré'): ?>
                            <span class="badge badge-danger">Expiré</span>
                        <?php else: ?>
                            <span class="badge badge-success">Actif</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <p class="text-sm text-secondary hr-post-card__description"><?php echo htmlspecialchars($offreItem['description'] ?? ''); ?></p>
                
                <div class="hr-post-card__stats">
                    <span class="hr-post-card__stat" title="Domaine">
                        <i data-lucide="folder"></i> <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?>
                    </span>
                    <span class="hr-post-card__stat" title="Salaire">
                        <i data-lucide="coins"></i> <span class="salary-amount"><?php echo htmlspecialchars($offreItem['salaire'] ?? ''); ?> TND</span>
                    </span>
                    <span class="hr-post-card__stat date-stat" title="Date de Publication">
                        <i data-lucide="calendar"></i> <?php echo htmlspecialchars($offreItem['date_publication'] ?? ''); ?>
                    </span>
                </div>

                <div class="hr-post-card__actions">
                    <a href="hr_posts.php?action=edit&id=<?php echo $offreItem['id_offre']; ?>" class="btn-icon" title="Éditer"><i data-lucide="pencil"></i></a>
                    <a href="hr_candidatures.php?offre_id=<?php echo $offreItem['id_offre']; ?>" class="btn-icon" title="Candidats"><i data-lucide="users"></i></a>
                    <button type="button" onclick="confirmDelete(<?php echo $offreItem['id_offre']; ?>, '<?php echo htmlspecialchars(addslashes($offreItem['titre'] ?? '')); ?>')" class="btn-icon btn-icon--danger" title="Supprimer"><i data-lucide="trash-2"></i></button>
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
      <div class="form-container" style="background: var(--bg-card); padding: 2.5rem 3rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.04); max-width: 1100px; margin: 1.5rem auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 20px rgba(168, 100, 228, 0.2);">
                <i data-lucide="<?php echo $action === 'edit' ? 'pencil' : 'plus'; ?>" style="width:32px;height:32px;color:white;"></i>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">
                <?php echo $action === 'edit' ? 'Modifier l\'offre' : 'Publier une nouvelle offre'; ?>
            </h2>
            <p style="color: var(--text-tertiary); font-size: 1rem;">Remplissez les détails ci-dessous pour attirer les meilleurs candidats.</p>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1.25rem; border-radius: 12px; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
            <i data-lucide="alert-circle" style="width:24px;height:24px;"></i>
            <span style="font-weight: 500;">Certains champs nécessitent votre attention.</span>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="hr_posts.php?<?php echo ($action === 'edit' && isset($_GET['id'])) ? 'action=edit&id='.$_GET['id'] : 'action=add'; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                <!-- COLONNE GAUCHE -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- SECTION 1: INFORMATIONS DE BASE -->
                    <div class="form-section" style="border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                        <h4 class="form-section-title"><i data-lucide="info" style="width:18px;height:18px;"></i> Informations Générales</h4>
                        
                        <div class="form-group mb-4">
                            <label class="form-label" for="titre">Titre de l'offre</label>
                            <input type="text" class="input <?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" name="titre" id="titre" placeholder="ex: Développeur Full Stack Senior" value="<?php echo htmlspecialchars(val('titre', $form_data, $offreEdit)); ?>">
                            <?php if (isset($errors['titre'])): ?>
                                <span class="error-msg"><?php echo $errors['titre']; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="img_post">Image de couverture</label>
                            <div style="border: 2px dashed var(--border-color); border-radius: 12px; padding: 1rem; text-align: center; background: var(--bg-secondary);">
                                <input type="file" name="img_post" id="img_post" accept="image/*" style="width:100%; font-size: 0.85rem;">
                            </div>
                            <?php if ($action === 'edit' && !empty($offreEdit['img_post'])): ?>
                                <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem; padding:0.4rem; background:var(--bg-secondary); border-radius:8px;">
                                    <img src="<?php echo htmlspecialchars($offreEdit['img_post']); ?>" style="width:30px;height:30px;border-radius:4px;object-fit:cover;">
                                    <span style="font-size:0.75rem; color:var(--text-secondary);">Image actuelle</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SECTION 2: DÉTAILS DU POSTE -->
                    <div class="form-section">
                        <h4 class="form-section-title"><i data-lucide="briefcase" style="width:18px;height:18px;"></i> Détails du Poste</h4>
                        
                        <div class="form-group mb-4">
                            <label class="form-label" for="description">Description du poste</label>
                            <textarea class="textarea <?php echo isset($errors['description']) ? 'has-error' : ''; ?>" name="description" id="description" rows="4" placeholder="Missions et responsabilités..."><?php echo htmlspecialchars(val('description', $form_data, $offreEdit)); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <span class="error-msg"><?php echo $errors['description']; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="input-group-grid">
                            <div class="form-group">
                                <label class="form-label" for="domaine">Domaine</label>
                                <input type="text" class="input <?php echo isset($errors['domaine']) ? 'has-error' : ''; ?>" name="domaine" id="domaine" placeholder="ex: IT" value="<?php echo htmlspecialchars(val('domaine', $form_data, $offreEdit)); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="salaire">Salaire (TND)</label>
                                <input type="text" class="input <?php echo isset($errors['salaire']) ? 'has-error' : ''; ?>" name="salaire" id="salaire" placeholder="ex: 2000" value="<?php echo htmlspecialchars(val('salaire', $form_data, $offreEdit)); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="type">Type de poste</label>
                            <?php $currentType = val('type', $form_data, $offreEdit, 'Sur site'); ?>
                            <select class="input" name="type" id="type" style="cursor: pointer;">
                                <option value="Sur site" <?php echo $currentType === 'Sur site' ? 'selected' : ''; ?>>Sur site</option>
                                <option value="À distance" <?php echo $currentType === 'À distance' ? 'selected' : ''; ?>>À distance</option>
                                <option value="Hybride" <?php echo $currentType === 'Hybride' ? 'selected' : ''; ?>>Hybride</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- COLONNE DROITE -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- SECTION 3: PROFIL RECHERCHÉ -->
                    <div class="form-section" style="border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                        <h4 class="form-section-title"><i data-lucide="target" style="width:18px;height:18px;"></i> Profil Recherché</h4>
                        
                        <div class="form-group mb-4">
                            <label class="form-label" for="competences_requises_visual">Compétences (Entrée)</label>
                            <input type="hidden" name="competences_requises" id="competences_requises" value="<?php echo htmlspecialchars(val('competences_requises', $form_data, $offreEdit)); ?>">
                            <div class="input" id="tags-container" style="display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.6rem; min-height: 45px; background: var(--bg-secondary);">
                                <input type="text" id="competences_requises_visual" placeholder="Ajouter..." style="border: none; outline: none; background: transparent; flex: 1; min-width: 100px; font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label" for="experience_requise">Expérience</label>
                            <input type="text" class="input" name="experience_requise" id="experience_requise" placeholder="ex: 2 ans" value="<?php echo htmlspecialchars(val('experience_requise', $form_data, $offreEdit)); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="question">Question candidat</label>
                            <input type="text" class="input" name="question" id="question" placeholder="ex: Disponibilité ?" value="<?php echo htmlspecialchars(val('question', $form_data, $offreEdit)); ?>">
                        </div>
                    </div>

                    <!-- SECTION 4: CALENDRIER -->
                    <div class="form-section">
                        <h4 class="form-section-title"><i data-lucide="calendar" style="width:18px;height:18px;"></i> Calendrier</h4>
                        
                        <div class="input-group-grid">
                            <div class="form-group">
                                <label class="form-label">Publication</label>
                                <?php $default_date_pub = ($action === 'edit' && $offreEdit) ? $offreEdit['date_publication'] : date('Y-m-d'); ?>
                                <input type="date" class="input" name="date_publication" value="<?php echo htmlspecialchars($default_date_pub); ?>" readonly style="background:rgba(0,0,0,0.02); opacity:0.7;">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="date_expir">Expiration</label>
                                <input type="date" class="input <?php echo isset($errors['date_expir']) ? 'has-error' : ''; ?>" name="date_expir" id="date_expir" value="<?php echo htmlspecialchars(val('date_expir', $form_data, $offreEdit)); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div style="display:flex; gap: 1.5rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <button type="submit" name="<?php echo $action === 'edit' ? 'submit_update' : 'submit_add'; ?>" style="flex: 2; padding: 1.25rem; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: white; border: none; border-radius: 14px; font-weight: 700; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 10px 25px rgba(168, 100, 228, 0.3); transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 30px rgba(168, 100, 228, 0.4)';" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(168, 100, 228, 0.3)';"
                >
                    <i data-lucide="send" style="width:20px;height:20px;"></i>
                    <?php echo $action === 'edit' ? 'Enregistrer les modifications' : 'Publier l\'offre maintenant'; ?>
                </button>
                <a href="hr_posts.php" class="btn-cancel" style="flex: 1; padding: 1.25rem; background: var(--bg-secondary); color: var(--text-secondary); border-radius: 14px; text-decoration: none; font-weight: 600; text-align: center; border: 1px solid var(--border-color); transition: all 0.2s;"
                   onmouseover="this.style.background='var(--border-color)';" onmouseout="this.style.background='var(--bg-secondary)';"
                >Annuler</a>
            </div>
        </form>
      </div>
      </div>
    <?php endif; ?>
  </div>


</div>

<!-- ═══ Scripts & Styles pour le Toggle de Vue ═══ -->
<style>
.hr-post-card__description,
.post-id-badge {
    display: none !important;
}

/* ── FORM STYLES ── */
.form-section-title {
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    color: var(--accent-primary) !important;
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important;
    margin-bottom: 1.5rem !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
}

.form-section-title::after {
    content: "";
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

.input-group-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 1.5rem !important;
}

.form-label {
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    margin-bottom: 0.6rem !important;
    color: var(--text-primary) !important;
    display: block !important;
}

.input, .textarea {
    background: var(--bg-secondary) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 12px !important;
    padding: 0.85rem 1.1rem !important;
    font-size: 0.95rem !important;
    transition: all 0.2s ease !important;
    width: 100% !important;
    box-sizing: border-box !important;
    color: var(--text-primary) !important;
}

.input:focus, .textarea:focus {
    border-color: var(--accent-primary) !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 4px rgba(79, 181, 255, 0.1) !important;
    outline: none !important;
}

.error-msg {
    color: #ef4444;
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.error-msg::before {
    content: "•";
    font-weight: bold;
}

.hr-post-card__actions {
    display: flex;
    gap: 1rem;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    align-items: center;
}

.hr-post-card__actions .btn-icon--danger {
    margin-left: auto;
}

.hr-posts-grid.view-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.75rem !important;
}

.hr-posts-grid.view-list .hr-post-card {
    flex-direction: row !important;
    background: var(--bg-card) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 12px !important;
    transition: all 0.2s ease !important;
    padding: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
}

.hr-posts-grid.view-list .hr-post-card:hover {
    border-color: var(--accent-primary) !important;
    transform: translateX(4px) !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
}

.hr-posts-grid.view-list .hr-post-card > div:first-child {
    display: block !important;
    width: 140px !important;
    height: 100% !important;
    min-height: 90px !important;
    flex-shrink: 0;
    border-right: 1px solid var(--border-color);
}

.hr-posts-grid.view-list .image-status-badge {
    display: none !important;
}

.hr-posts-grid.view-list .hr-post-card__content {
    display: grid !important;
    grid-template-columns: 2.5fr 1fr 1fr 0.8fr 140px !important;
    align-items: center !important;
    padding: 0.75rem 1.5rem !important;
    gap: 1rem !important;
}

.hr-posts-grid.view-list .hr-post-card__main-info {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
}

.hr-posts-grid.view-list .post-id-badge {
    display: none !important;
}

.status-badge-inline {
    display: none;
}

.hr-posts-grid.view-list .status-badge-inline {
    display: block !important;
}

.hr-posts-grid.view-list .hr-post-card__title {
    margin: 0 !important;
    font-size: 0.95rem !important;
    font-weight: 600 !important;
    color: var(--text-primary) !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.hr-posts-grid.view-list .hr-post-card__description {
    display: none !important;
}

.hr-posts-grid.view-list .hr-post-card__stats {
    display: contents !important; /* Allow stats children to follow grid */
}

.hr-posts-grid.view-list .hr-post-card__stat {
    font-size: 0.85rem !important;
    color: var(--text-secondary) !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

.hr-posts-grid.view-list .hr-post-card__stat i {
    width: 14px !important;
    height: 14px !important;
    opacity: 0.6 !important;
}

.hr-posts-grid.view-list .salary-amount {
    background: rgba(16, 185, 129, 0.1) !important;
    color: #10b981 !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    font-weight: 600 !important;
}

.hr-posts-grid.view-list .date-stat {
    color: var(--text-tertiary) !important;
}

.hr-posts-grid.view-list .hr-post-card__actions {
    display: flex !important;
    gap: 0.5rem !important;
    justify-content: flex-end !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}

/* Icon Button Style */
.btn-icon {
    width: 34px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 8px !important;
    border: 1px solid var(--border-color) !important;
    background: var(--bg-card) !important;
    color: var(--text-secondary) !important;
    transition: all 0.2s !important;
    cursor: pointer !important;
}

.btn-icon:hover {
    background: var(--bg-secondary) !important;
    color: var(--accent-primary) !important;
    border-color: var(--accent-primary) !important;
    transform: scale(1.05) !important;
}

.btn-icon--danger:hover {
    color: #ef4444 !important;
    border-color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.05) !important;
}

.btn-icon i {
    width: 16px !important;
    height: 16px !important;
}
</style>

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

function setViewMode(mode) {
    const grid = document.getElementById('posts-container');
    const gridBtn = document.getElementById('view-grid-btn');
    const listBtn = document.getElementById('view-list-btn');
    
    if (!grid || !gridBtn || !listBtn) return;

    if (mode === 'list') {
        grid.classList.add('view-list');
        listBtn.style.background = 'linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%)';
        listBtn.style.color = 'white';
        listBtn.style.boxShadow = '0 4px 12px rgba(168, 100, 228, 0.2)';
        
        gridBtn.style.background = 'transparent';
        gridBtn.style.color = 'var(--text-tertiary)';
        gridBtn.style.boxShadow = 'none';
    } else {
        grid.classList.remove('view-list');
        gridBtn.style.background = 'linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%)';
        gridBtn.style.color = 'white';
        gridBtn.style.boxShadow = '0 4px 12px rgba(168, 100, 228, 0.2)';
        
        listBtn.style.background = 'transparent';
        listBtn.style.color = 'var(--text-tertiary)';
        listBtn.style.boxShadow = 'none';
    }
    localStorage.setItem('hr_posts_view_mode', mode);
}

// ═══ DYNAMIC AJAX SEARCH (HR POSTS - MVC) ═══
let searchTimeout;
const searchInput = document.getElementById('ajax-search-input');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value;
        const spinner = document.getElementById('search-spinner');
        clearTimeout(searchTimeout);
        if (spinner) spinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            fetchHrPostsSearch(query);
        }, 300);
    });
}

function fetchHrPostsSearch(query) {
    const formData = new FormData();
    formData.append('action', 'search_offres');
    formData.append('query', query);
    
    const checkedStatus = document.querySelector('input[name="filter_status"]:checked');
    if (checkedStatus) {
        formData.append('filter_status', checkedStatus.value);
    }

    const checkedType = document.querySelector('input[name="filter_type"]:checked');
    if (checkedType) {
        formData.append('filter_type', checkedType.value);
    }
    
    fetch('ajax_offres.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateHrPostsGrid(data.results);
        }
        const spinner = document.getElementById('search-spinner');
        if (spinner) spinner.style.display = 'none';
    })
    .catch(err => {
        console.error('Erreur recherche:', err);
        const spinner = document.getElementById('search-spinner');
        if (spinner) spinner.style.display = 'none';
    });
}

function updateHrPostsGrid(offres) {
    const container = document.getElementById('posts-container');
    const resultsInfo = document.querySelector('.results-info');
    if (!container) return;
    
    // Mise à jour du compteur
    if (resultsInfo) {
        resultsInfo.innerHTML = `<span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;">${offres.length}</span> <span>postes publiés au total</span>`;
    }
    
    if (offres.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-tertiary);">Aucun résultat trouvé</div>';
        return;
    }
    
    let html = '';
    offres.forEach(o => {
        const statut = o.statut || 'Actif';
        const badgeType = statut === 'Expiré' ? 'badge-danger' : 'badge-success';
        const titre = escapeHtml(o.titre || '');
        const domaine = escapeHtml(o.domaine || '');
        const description = escapeHtml(o.description || '');
        const competences = escapeHtml(o.competences_requises || '');
        const experience = escapeHtml(o.experience_requise || '');
        const salaire = escapeHtml(String(o.salaire || ''));
        const datePub = o.date_publication || '';
        const dateExpir = o.date_expir || '';
        const imgPost = o.img_post || '';
        
        let imgSection = '';
        if (imgPost) {
            imgSection = `<div style="height: 140px; background-image: url('${imgPost}'); background-size: cover; background-position: center; position: relative;">`;
        } else {
            imgSection = `<div style="height: 80px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%); position: relative; display: flex; align-items: center; justify-content: center;">
               <i data-lucide="image" style="width: 32px; height: 32px; color: var(--text-secondary); opacity: 0.5;"></i>`;
        }
        
        html += `<div class="hr-post-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            ${imgSection}
               <div style="position: absolute; top: 12px; right: 12px;">
                    <span class="badge ${badgeType}" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">${statut}</span>
               </div>
            </div>
            <div style="padding: 0.75rem 1.5rem; flex: 1; display: flex; flex-direction: column;" class="hr-post-card__content">
                <div class="hr-post-card__main-info">
                    <span class="post-id-badge">#${o.id_offre}</span>
                    <h3 class="hr-post-card__title">${titre}</h3>
                    <div class="status-badge-inline">
                        <span class="badge ${badgeType}">${statut}</span>
                    </div>
                </div>
                
                <p class="text-sm text-secondary hr-post-card__description">${description}</p>
                
                <div class="hr-post-card__stats">
                    <span class="hr-post-card__stat" title="Domaine">
                        <i data-lucide="folder"></i> ${domaine}
                    </span>
                    <span class="hr-post-card__stat" title="Salaire">
                        <i data-lucide="coins"></i> <span class="salary-amount">${salaire} TND</span>
                    </span>
                    <span class="hr-post-card__stat date-stat" title="Date de Publication">
                        <i data-lucide="calendar"></i> ${datePub}
                    </span>
                </div>

                <div class="hr-post-card__actions">
                    <a href="hr_posts.php?action=edit&id=${o.id_offre}" class="btn-icon" title="Éditer"><i data-lucide="pencil"></i></a>
                    <button class="btn-icon" title="Candidats"><i data-lucide="users"></i></button>
                    <button type="button" onclick="confirmDelete(${o.id_offre}, '${titre.replace(/'/g, "\\\\'")}')" class="btn-icon btn-icon--danger" title="Supprimer"><i data-lucide="trash-2"></i></button>
                </div>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    // Re-appliquer le mode d'affichage
    if (localStorage.getItem('hr_posts_view_mode') === 'list') {
        container.classList.add('view-list');
    }
    if (window.lucide) lucide.createIcons();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}


document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('hr_posts_view_mode');
    if (savedMode === 'list') {
        setViewMode('list');
    }
});

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
