<?php 
$pageTitle = "Offres Disponibles"; 
$pageCSS = "feeds.css"; 

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
    
    // Forçage de la date de publication selon l'action
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
        $errors['question'] = "Veuillez poser une question pour filtrer les candidats.";
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
        
        header("Location: offres_admin.php");
        exit();
    } else {
        // Maintien de l'état du formulaire
        if (isset($_POST['submit_add'])) {
            $action = 'add';
        } else if (isset($_POST['submit_update'])) {
            $action = 'edit';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $offreC->supprimerOffre($_GET['id']);
    header("Location: offres_admin.php");
    exit();
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Included inside layout_back.php -->

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Offres Disponibles</h1>
      <p>Gestion complète des offres d'emploi publiées par les entreprises</p>
    </div>
    <?php if ($action === 'list'): ?>
    <a href="offres_admin.php?action=add" class="btn btn-primary text-decoration-none d-flex align-items-center gap-2">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Ajouter une offre
    </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($action === 'list'): ?>
  <?php 
    $criteres_admin = [];
    if (!empty($_GET['filter_status']) && $_GET['filter_status'] !== 'Tous statuts') {
        $criteres_admin['statut'] = $_GET['filter_status'];
    }

    if (!empty($criteres_admin)) {
        $listeOffres = $offreC->filtrerOffres($criteres_admin);
    } else {
        $listeOffres = $offreC->afficherOffres();
    }
    $count = $listeOffres->rowCount();
  ?>


  <!-- ═══ Engagement Stats (from posts_stats) ═══ -->
  <div class="grid grid-4 gap-6 mb-8 stagger">
    <div class="stat-card animate-on-scroll">
      <div>
        <div class="stat-card__label">Offres publiées</div>
        <div class="stat-card__value"><?php echo $count; ?></div>
        <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> à jour</div>
      </div>
      <div class="stat-card__icon purple"><i data-lucide="file-text" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
      <div>
        <div class="stat-card__label">Engagement moyen</div>
        <div class="stat-card__value">78%</div>
        <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +3.2%</div>
      </div>
      <div class="stat-card__icon teal"><i data-lucide="heart" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
      <div>
        <div class="stat-card__label">Vues totales</div>
        <div class="stat-card__value">89.4k</div>
        <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +15%</div>
      </div>
      <div class="stat-card__icon blue"><i data-lucide="eye" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
      <div>
        <div class="stat-card__label">Taux de conversion</div>
        <div class="stat-card__value">12.3%</div>
        <div class="stat-card__trend down"><i data-lucide="trending-down" style="width:14px;height:14px;"></i> -0.5%</div>
      </div>
      <div class="stat-card__icon orange"><i data-lucide="target" style="width:22px;height:22px;"></i></div>
    </div>
  </div>

  <!-- ═══ Charts Row ═══ -->
  <div class="grid grid-2 gap-6 mb-8" style="margin-top: 1rem;">
    <div class="card">
      <h3 class="text-md fw-semibold mb-6">Activité des Posts (Mensuel)</h3>
      <div id="posts-monthly-chart" style="height:250px;"></div>
    </div>
    <div class="card">
      <h3 class="text-md fw-semibold mb-6">Répartition par domaine</h3>
      <div class="flex items-center justify-center" id="category-donut-chart"></div>
    </div>
  </div>

  <!-- ═══ Search & Sort Toolbar ═══ -->
  <div class="filter-bar mb-6" id="admin-filter-toolbar" style="display: flex; gap: 1rem; align-items: center;">
    <div class="search-bar" style="flex:1;max-width:350px; position: relative; display: flex; align-items: center;">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      <input type="text" class="input" placeholder="Rechercher une offre..." id="admin-offers-search" name="q" autocomplete="off" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
      <div id="admin-search-spinner" style="position: absolute; right: 1rem; display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 18px; height: 18px; border: 2px solid rgba(168, 100, 228, 0.2); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
      </div>
    </div>
    <select class="select" style="max-width:160px;" id="admin-offers-category">
      <option value="">Toutes domaines</option>
      <option>IT & Dev</option>
      <option>Data & IA</option>
      <option>Design</option>
      <option>Marketing</option>
    </select>
    <select class="select" style="max-width:140px;" id="admin-offers-status" name="filter_status" onchange="fetchAdminSearch(document.getElementById('admin-offers-search').value);">
      <option value="">Tous statuts</option>
      <option value="Actif" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Actif') ? 'selected' : ''; ?>>Actif</option>
      <option value="Expiré" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'Expiré') ? 'selected' : ''; ?>>Expiré</option>
    </select>
  </div>
  <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
  </div>

  <!-- ═══ Offers Data Table ═══ -->
  <div class="card-flat" id="admin-table-container" style="overflow:hidden;">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Entreprise</th>
          <th>Offre</th>
          <th>Domaine</th>
          <th>Salaire</th>
          <th>Candidats</th>
          <th>Statut</th>
          <th>Date Expir.</th>
          <th>Date Publ.</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($listeOffres as $o): 
            $id = $o['id_offre'];
            $titre = htmlspecialchars($o['titre'] ?? '');
            $statut = $o['statut'] ?? 'Actif';
            $badgeClass = ($statut === 'Expiré') ? 'badge-danger' : 'badge-success';
        ?>
        <tr class="animate-on-scroll">
            <td style="font-weight:600; color:var(--text-tertiary);">#<?php echo $id; ?></td>
            <td>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center; color:var(--accent-primary);">
                        <i data-lucide="building" style="width:16px; height:16px;"></i>
                    </div>
                    <span style="font-weight:500;"><?php echo htmlspecialchars($o['nom_entreprise'] ?? 'Aptus'); ?></span>
                </div>
            </td>
            <td>
                <div style="font-weight:600; color:var(--text-primary);"><?php echo $titre; ?></div>
                <div style="font-size:0.8rem; color:var(--text-tertiary);"><?php echo htmlspecialchars($o['experience_requise'] ?? ''); ?> exp.</div>
            </td>
            <td><span class="tag-flat" style="background: var(--bg-secondary); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.85rem; color: var(--text-secondary);"><?php echo htmlspecialchars($o['domaine'] ?? ''); ?></span></td>
            <td style="font-weight:600; color:var(--accent-primary);"><?php echo number_format($o['salaire'] ?? 0, 0, '.', ' '); ?> TND</td>
            <td>
                <div style="display:flex; align-items:center; gap:0.4rem;">
                    <i data-lucide="users" style="width:14px; height:14px; color:var(--text-tertiary);"></i>
                    <span style="font-weight:500;"><?php echo $o['nb_candidats'] ?? 0; ?></span>
                </div>
            </td>
            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $statut; ?></span></td>
            <td style="color:var(--text-tertiary); font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($o['date_expir'])); ?></td>
            <td style="color:var(--text-tertiary); font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($o['date_publication'])); ?></td>
            <td>
                <div class="flex gap-2">
                    <a href="offres_admin.php?action=edit&id=<?php echo $id; ?>" class="btn-icon purple" title="Modifier" style="color: var(--accent-primary); background: rgba(168, 100, 228, 0.1); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i data-lucide="pencil" style="width:16px; height:16px;"></i>
                    </a>
                    <button type="button" class="btn-icon red" title="Supprimer" onclick="confirmDelete(<?php echo $id; ?>, '<?php echo addslashes($titre); ?>')" style="color: #ef4444; background: rgba(239, 68, 68, 0.1); width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                        <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if($count == 0): ?>
      <div style="padding: 2rem; text-align: center;" class="text-secondary">Aucune offre publiée.</div>
    <?php endif; ?>
  </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
  <?php 
    $offreEdit = null;
    if ($action === 'edit' && isset($_GET['id']) && empty($form_data)) {
        $offreEdit = $offreC->getOffreById($_GET['id']);
    }
    
    // Helper pour afficher les valeurs
    if (!function_exists('val')) {
        function val($field, $form_data, $offreEdit, $default = '') {
            if (isset($form_data[$field])) return $form_data[$field];
            if (isset($offreEdit[$field])) return $offreEdit[$field];
            return $default;
        }
    }
  ?>
  <div class="card" style="max-width: 1100px; margin: 0 auto; background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.06); padding: 2.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="<?php echo $action === 'edit' ? 'pencil' : 'plus-circle'; ?>" style="width:32px;height:32px;color:var(--accent-primary);"></i>
                <?php echo $action === 'edit' ? 'Modifier l\'offre' : 'Publier une nouvelle offre'; ?>
            </h2>
            <p style="color: var(--text-tertiary); font-size: 1rem;">Remplissez les informations ci-dessous pour <?php echo $action === 'edit' ? 'mettre à jour l\'offre' : 'diffuser votre annonce'; ?>.</p>
        </div>
        <a href="offres_admin.php" style="width: 44px; height: 44px; border-radius: 12px; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';" onmouseout="this.style.background='var(--bg-secondary)'; this.style.color='var(--text-secondary)';" title="Fermer">
            <i data-lucide="x" style="width: 24px; height: 24px;"></i>
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div style="background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 1.25rem; border-radius: 16px; margin-bottom: 2.5rem; display: flex; align-items: flex-start; gap: 1rem;">
        <i data-lucide="alert-circle" style="width: 24px; height: 24px; flex-shrink: 0; margin-top: 2px;"></i>
        <div>
            <strong style="display: block; margin-bottom: 0.25rem; font-size: 1.05rem;">Attention</strong>
            <p style="font-size: 0.95rem; line-height: 1.5; margin: 0;">Veuillez corriger les <?php echo count($errors); ?> erreur(s) signalée(s) ci-dessous pour pouvoir valider l'offre.</p>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="offres_admin.php?<?php echo ($action === 'edit' && isset($_GET['id'])) ? 'action=edit&id='.$_GET['id'] : 'action=add'; ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- ═══ COLONNE GAUCHE ═══ -->
            <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                <!-- Section 1: Informations Générales -->
                <section>
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--accent-primary); font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="info" style="width: 16px; height: 16px;"></i> Informations Générales
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Titre du poste <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="input <?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" name="titre" placeholder="ex: Senior React Developer" value="<?php echo htmlspecialchars(val('titre', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-secondary); font-size: 1rem;">
                            <?php if (isset($errors['titre'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['titre']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Domaine d'activité <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="input <?php echo isset($errors['domaine']) ? 'has-error' : ''; ?>" name="domaine" placeholder="ex: Informatique / IT" value="<?php echo htmlspecialchars(val('domaine', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-secondary);">
                            <?php if (isset($errors['domaine'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['domaine']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Détails du Poste -->
                <section>
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--accent-primary); font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="briefcase" style="width: 16px; height: 16px;"></i> Détails du Poste
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Salaire (TND) <span style="color: #ef4444;">*</span></label>
                                <div style="position: relative;">
                                    <input type="text" class="input <?php echo isset($errors['salaire']) ? 'has-error' : ''; ?>" name="salaire" value="<?php echo htmlspecialchars(val('salaire', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px; padding-left: 2.75rem;">
                                    <i data-lucide="banknote" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-tertiary);"></i>
                                </div>
                                <?php if (isset($errors['salaire'])): ?>
                                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['salaire']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Expérience <span style="color: #ef4444;">*</span></label>
                                <input type="text" class="input <?php echo isset($errors['experience_requise']) ? 'has-error' : ''; ?>" name="experience_requise" placeholder="ex: 2-3 ans" value="<?php echo htmlspecialchars(val('experience_requise', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px;">
                                <?php if (isset($errors['experience_requise'])): ?>
                                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['experience_requise']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Type de poste</label>
                            <?php $currentType = val('type', $form_data, $offreEdit, 'Sur site'); ?>
                            <select class="input" name="type" style="height: 52px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-secondary); cursor: pointer;">
                                <option value="Sur site" <?php echo $currentType === 'Sur site' ? 'selected' : ''; ?>>Sur site</option>
                                <option value="À distance" <?php echo $currentType === 'À distance' ? 'selected' : ''; ?>>À distance</option>
                                <option value="Hybride" <?php echo $currentType === 'Hybride' ? 'selected' : ''; ?>>Hybride</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Description du poste <span style="color: #ef4444;">*</span></label>
                            <textarea class="textarea <?php echo isset($errors['description']) ? 'has-error' : ''; ?>" name="description" rows="6" placeholder="Décrivez les missions et responsabilités..." style="border-radius: 16px; padding: 1.25rem; font-size: 1rem; background: var(--bg-secondary);"><?php echo htmlspecialchars(val('description', $form_data, $offreEdit)); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['description']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ═══ COLONNE DROITE ═══ -->
            <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                <!-- Section 3: Profil Recherché -->
                <section>
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--accent-primary); font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="user-check" style="width: 16px; height: 16px;"></i> Profil Recherché
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Compétences clés <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="input <?php echo isset($errors['competences_requises']) ? 'has-error' : ''; ?>" name="competences_requises" placeholder="ex: PHP, MySQL, React (séparées par des virgules)" value="<?php echo htmlspecialchars(val('competences_requises', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px;">
                            <?php if (isset($errors['competences_requises'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['competences_requises']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Question de filtrage <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="input <?php echo isset($errors['question']) ? 'has-error' : ''; ?>" name="question" placeholder="ex: Pourquoi devrions-nous vous choisir ?" value="<?php echo htmlspecialchars(val('question', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px;">
                            <p style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 0.5rem;">Cette question sera posée à chaque candidat lors de sa postulation.</p>
                            <?php if (isset($errors['question'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['question']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Médias et Calendrier -->
                <section>
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--accent-primary); font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Médias &amp; Calendrier
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Image de couverture (Max 1Mo)</label>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <input type="file" class="input <?php echo isset($errors['img_post']) ? 'has-error' : ''; ?>" name="img_post" accept="image/*" style="flex: 1; padding: 10px; height: auto;">
                                <?php if ($action === 'edit' && !empty($offreEdit['img_post'])): ?>
                                    <div style="width: 52px; height: 52px; border-radius: 10px; background-image: url('<?php echo htmlspecialchars($offreEdit['img_post']); ?>'); background-size: cover; background-position: center; border: 2px solid var(--border-color);"></div>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($errors['img_post'])): ?>
                                <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['img_post']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Date de publication</label>
                                <input type="date" class="input" name="date_publication" value="<?php echo htmlspecialchars(($action === 'edit' && $offreEdit) ? $offreEdit['date_publication'] : date('Y-m-d')); ?>" readonly style="background: var(--bg-secondary); color: var(--text-tertiary); cursor: not-allowed; opacity: 0.7;">
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">Date d'expiration <span style="color: #ef4444;">*</span></label>
                                <input type="date" class="input <?php echo isset($errors['date_expir']) ? 'has-error' : ''; ?>" name="date_expir" value="<?php echo htmlspecialchars(val('date_expir', $form_data, $offreEdit)); ?>" style="height: 52px; border-radius: 12px;">
                                <?php if (isset($errors['date_expir'])): ?>
                                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;"><i data-lucide="alert-circle" style="width:14px;height:14px;"></i> <?php echo $errors['date_expir']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div style="margin-top: 3.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 1.25rem;">
            <a href="offres_admin.php" class="btn btn-ghost" style="padding: 0.75rem 2rem; border-radius: 12px; font-weight: 600;">Annuler</a>
            <button type="submit" name="<?php echo $action === 'edit' ? 'submit_update' : 'submit_add'; ?>" class="btn btn-primary" style="padding: 0.75rem 2.5rem; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border: none; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(168, 100, 228, 0.4)';" onmouseout="this.style.transform='translateY(0)';">
                <i data-lucide="check" style="width: 20px; height: 20px; margin-right: 0.5rem;"></i>
                <?php echo $action === 'edit' ? 'Enregistrer les modifications' : 'Publier l\'offre officiellement'; ?>
            </button>
        </div>
    </form>
  </div>
<?php endif; ?>



































<script>
document.addEventListener('DOMContentLoaded', function() {
  if(typeof AptusCharts !== 'undefined' && document.getElementById('posts-monthly-chart')) {
      AptusCharts.bar('posts-monthly-chart', [
        { label: 'Jan', value: 156 },
        { label: 'Fév', value: 198 },
        { label: 'Mar', value: 234 },
        { label: 'Avr', value: 189 },
        { label: 'Mai', value: 267 },
        { label: 'Jun', value: 240 },
      ], { barColor: 'var(--chart-1)', height: 250 });

      AptusCharts.donut('category-donut-chart', [
        { label: 'Offres d\'emploi', value: <?php echo isset($count) ? $count : 0; ?> },
        { label: 'Rapports marché', value: 234 },
        { label: 'Formations', value: 189 },
        { label: 'Autres', value: 181 },
      ], {
        size: 180,
        strokeWidth: 28,
        centerValue: '<?php echo isset($count) ? $count : 0; ?>',
        centerLabel: 'Total'
      });
  }
});

var deleteUrl = '';

function confirmDelete(id, titre) {
    var titleEl = document.getElementById('delete-offer-title');
    if(titleEl) titleEl.innerText = titre;
    
    deleteUrl = "offres_admin.php?action=delete&id=" + id;
    
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

// ═══ DYNAMIC AJAX SEARCH (ADMIN - MVC) ═══
let searchTimeout;
const adminSearchInput = document.getElementById('admin-offers-search');
if (adminSearchInput) {
    adminSearchInput.addEventListener('input', function() {
        const query = this.value;
        const spinner = document.getElementById('admin-search-spinner');
        clearTimeout(searchTimeout);
        if (spinner) spinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            fetchAdminSearch(query);
        }, 300);
    });
}

function fetchAdminSearch(query) {
    const formData = new FormData();
    formData.append('action', 'search_offres');
    formData.append('query', query);
    
    const statusSelect = document.getElementById('admin-offers-status');
    if (statusSelect) {
        formData.append('filter_status', statusSelect.value);
    }
    
    fetch('ajax_offres.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateAdminTable(data.results);
        }
        const spinner = document.getElementById('admin-search-spinner');
        if (spinner) spinner.style.display = 'none';
    })
    .catch(err => {
        console.error('Erreur recherche:', err);
        const spinner = document.getElementById('admin-search-spinner');
        if (spinner) spinner.style.display = 'none';
    });
}

function updateAdminTable(offres) {
    const tableBody = document.querySelector('.data-table tbody');
    if (!tableBody) return;
    
    if (offres.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="10" style="padding: 3rem; text-align: center; color: var(--text-tertiary);">Aucun résultat trouvé</td></tr>';
        return;
    }
    
    let html = '';
    offres.forEach(o => {
        const statut = o.statut || 'Actif';
        const badgeClass = statut === 'Expiré' ? 'badge-danger' : 'badge-success';
        const salaire = Number(o.salaire || 0).toLocaleString('fr-FR');
        const dateExpir = new Date(o.date_expir).toLocaleDateString('fr-FR');
        const datePub = new Date(o.date_publication).toLocaleDateString('fr-FR');
        const titre = escapeHtml(o.titre || '');
        const entreprise = escapeHtml(o.nom_entreprise || 'Aptus');
        const experience = escapeHtml(o.experience_requise || '');
        const domaine = escapeHtml(o.domaine || '');
        
        html += `<tr class="animate-on-scroll">
            <td style="font-weight:600; color:var(--text-tertiary);">#${o.id_offre}</td>
            <td>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center; color:var(--accent-primary);">
                        <i data-lucide="building" style="width:16px; height:16px;"></i>
                    </div>
                    <span style="font-weight:500;">${entreprise}</span>
                </div>
            </td>
            <td>
                <div style="font-weight:600; color:var(--text-primary);">${titre}</div>
                <div style="font-size:0.8rem; color:var(--text-tertiary);">${experience}</div>
            </td>
            <td><span class="tag-flat" style="background:var(--bg-secondary); padding:0.25rem 0.6rem; border-radius:6px; font-size:0.85rem; color:var(--text-secondary);">${domaine}</span></td>
            <td style="font-weight:600; color:var(--accent-primary);">${salaire} TND</td>
            <td>
                <div style="display:flex; align-items:center; gap:0.4rem;">
                    <i data-lucide="users" style="width:14px; height:14px; color:var(--text-tertiary);"></i>
                    <span style="font-weight:500;">${o.nb_candidats || 0}</span>
                </div>
            </td>
            <td><span class="badge ${badgeClass}">${statut}</span></td>
            <td style="color:var(--text-tertiary); font-size:0.85rem;">${dateExpir}</td>
            <td style="color:var(--text-tertiary); font-size:0.85rem;">${datePub}</td>
            <td>
                <div class="flex gap-2">
                    <a href="offres_admin.php?action=edit&id=${o.id_offre}" class="btn-icon purple" title="Modifier" style="color:var(--accent-primary); background:rgba(168,100,228,0.1); width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i data-lucide="pencil" style="width:16px; height:16px;"></i>
                    </a>
                    <button type="button" class="btn-icon red" title="Supprimer" onclick="confirmDelete(${o.id_offre}, '${titre.replace(/'/g, "\\\\'")}')"
                        style="color:#ef4444; background:rgba(239,68,68,0.1); width:32px; height:32px; border-radius:8px; border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s;">
                        <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });
    
    tableBody.innerHTML = html;
    if (window.lucide) lucide.createIcons();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
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
        <button type="button" onclick="executeDelete()" id="confirm-delete-btn" class="btn btn-primary" style="flex:1; background: #dc2626; border-color: #dc2626;">
          Oui, Supprimer
        </button>
      </div>
    </div>
  </div>
</div>
