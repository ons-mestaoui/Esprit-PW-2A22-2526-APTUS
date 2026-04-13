<?php 
$pageTitle = "Mes Postes"; 
$pageCSS = "feeds.css"; 
$userRole = "Entreprise"; 

// --- CONTROLLER INCLUSION ---
require_once '../../controller/offreC.php';
require_once '../../model/offre.php';

$offreC = new offreC();
$action = $_GET['action'] ?? 'list';

// --- LOGIQUE CRUD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_add'])) {
        $offre = new offre(
            $_POST['titre'], 
            $_POST['description'], 
            $_POST['domaine'], 
            $_POST['competences_requises'], 
            $_POST['experience_requise'], 
            (float)$_POST['salaire'], 
            $_POST['question'], 
            $_POST['date_publication'], 
            $_POST['date_expir']
        );
        $offreC->ajouterOffre($offre);
        header("Location: hr_posts.php");
        exit();
    }
    if (isset($_POST['submit_update']) && isset($_GET['id'])) {
        $offre = new offre(
            $_POST['titre'], 
            $_POST['description'], 
            $_POST['domaine'], 
            $_POST['competences_requises'], 
            $_POST['experience_requise'], 
            (float)$_POST['salaire'], 
            $_POST['question'], 
            $_POST['date_publication'], 
            $_POST['date_expir']
        );
        $offreC->modifierOffre($offre, $_GET['id']);
        header("Location: hr_posts.php");
        exit();
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
        $listeOffres = $offreC->afficherOffres();
        $count = $listeOffres->rowCount();
      ?>
      <div class="results-info mb-4">
        <strong><?php echo $count; ?></strong> postes publiés
      </div>

      <div class="hr-posts-grid stagger">
        <?php foreach ($listeOffres as $offreItem): ?>
        <div class="hr-post-card animate-on-scroll">
          <div class="hr-post-card__header">
            <span class="badge badge-success">Actif</span>
          </div>
          <h3 class="hr-post-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
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
          <div class="hr-post-card__actions" style="margin-top: 1rem;">
            <a href="hr_posts.php?action=edit&id=<?php echo $offreItem['id_offre']; ?>" class="btn btn-sm btn-secondary text-decoration-none d-flex align-items-center gap-1"><i data-lucide="pencil" style="width:14px;height:14px;"></i> Éditer</a>
            <button class="btn btn-sm btn-ghost d-flex align-items-center gap-1"><i data-lucide="users" style="width:14px;height:14px;"></i> Candidats</button>
            <a href="hr_posts.php?action=delete&id=<?php echo $offreItem['id_offre']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?');" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>
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
        if ($action === 'edit' && isset($_GET['id'])) {
            $offreEdit = $offreC->getOffreById($_GET['id']);
        }
      ?>
      <div class="form-container" style="background:var(--surface-1); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color);">
        <h2 class="mb-4 text-xl fw-bold d-flex align-items-center gap-2">
            <i data-lucide="<?php echo $action === 'edit' ? 'pencil' : 'plus-circle'; ?>" style="width:24px;height:24px;color:var(--accent-primary);"></i>
            <?php echo $action === 'edit' ? 'Modifier l\'offre' : 'Nouvelle offre'; ?>
        </h2>
        
        <form method="POST" action="hr_posts.php?<?php echo $action === 'edit' ? 'action=edit&id='.$_GET['id'] : 'action=add'; ?>">
            
            <div class="form-group mb-3">
                <label class="form-label" for="titre">Titre de l'offre</label>
                <input type="text" class="input" name="titre" id="titre" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['titre']) : ''; ?>">
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="input" name="description" id="description" rows="4" required><?php echo $offreEdit ? htmlspecialchars($offreEdit['description']) : ''; ?></textarea>
            </div>

            <div style="display:flex; gap: 1rem;" class="mb-3">
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="domaine">Domaine</label>
                    <input type="text" class="input" name="domaine" id="domaine" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['domaine']) : ''; ?>">
                </div>
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="salaire">Salaire proposé</label>
                    <input type="number" step="0.01" class="input" name="salaire" id="salaire" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['salaire']) : ''; ?>">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="competences_requises">Compétences Requises</label>
                <input type="text" class="input" name="competences_requises" id="competences_requises" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['competences_requises']) : ''; ?>" placeholder="ex: PHP, HTML, CSS...">
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="experience_requise">Expérience Requise</label>
                <input type="text" class="input" name="experience_requise" id="experience_requise" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['experience_requise']) : ''; ?>" placeholder="ex: 3 à 5 ans">
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="question">Question Personnalisée pour les candidats</label>
                <input type="text" class="input" name="question" id="question" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['question']) : ''; ?>">
            </div>

            <div style="display:flex; gap: 1rem;" class="mb-3">
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="date_publication">Date de Publication</label>
                    <input type="date" class="input" name="date_publication" id="date_publication" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['date_publication']) : date('Y-m-d'); ?>">
                </div>
                <div class="form-group flex-1" style="flex:1;">
                    <label class="form-label" for="date_expir">Date d'Expiration</label>
                    <input type="date" class="input" name="date_expir" id="date_expir" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['date_expir']) : ''; ?>">
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
      <div class="search-bar" style="max-width:100%;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher..." id="hr-search">
      </div>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Filtrer par statut</h4>
      <label class="cv-sidebar__option"><input type="radio" name="status" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> Actif</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> En pause</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> Clôturé</label>
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
