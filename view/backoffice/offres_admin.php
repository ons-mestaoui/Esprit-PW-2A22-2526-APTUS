<?php 
$pageTitle = "Offres Disponibles"; 
$pageCSS = "feeds.css"; 

require_once '../../controller/offreC.php';
require_once '../../model/offre.php';

$offreC = new offreC();
$action = $_GET['action'] ?? 'list';

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
        header("Location: offres_admin.php");
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
        header("Location: offres_admin.php");
        exit();
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
    $listeOffres = $offreC->afficherOffres();
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
  <div class="filter-bar mb-6">
    <div class="search-bar" style="flex:1;max-width:350px;">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      <input type="text" class="input" placeholder="Rechercher une offre..." id="admin-offers-search">
    </div>
    <select class="select" style="max-width:160px;" id="admin-offers-category">
      <option value="">Toutes domaines</option>
      <option>IT & Dev</option>
      <option>Data & IA</option>
      <option>Design</option>
      <option>Marketing</option>
    </select>
    <select class="select" style="max-width:140px;" id="admin-offers-status">
      <option value="">Tous statuts</option>
      <option>Actif</option>
      <option>En pause</option>
    </select>
    <select class="select" style="max-width:140px;" id="admin-offers-sort">
      <option>Plus récent</option>
      <option>Plus ancien</option>
    </select>
  </div>

  <!-- ═══ Offers Data Table ═══ -->
  <div class="card-flat" style="overflow:hidden;">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Entreprise</th>
          <th>Offre</th>
          <th>Domaine</th>
          <th>Salaire</th>
          <th>Candidats</th>
          <th>Date Expir.</th>
          <th>Date Publ.</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($listeOffres as $o): ?>
        <tr>
          <td class="text-secondary text-sm">#<?php echo htmlspecialchars($o['id_offre'] ?? ''); ?></td>
          <td class="fw-medium">Ent. <?php echo htmlspecialchars($o['id_entreprise'] ?? '1'); ?></td>
          <td class="fw-medium"><?php echo htmlspecialchars($o['titre'] ?? ''); ?></td>
          <td class="text-secondary"><?php echo htmlspecialchars($o['domaine'] ?? ''); ?></td>
          <td><span class="badge badge-neutral"><?php echo htmlspecialchars($o['salaire'] ?? ''); ?> TND</span></td>
          <td class="fw-medium text-secondary">N/A</td>
          <td><span class="badge badge-warning"><?php echo htmlspecialchars($o['date_expir'] ?? ''); ?></span></td>
          <td class="text-sm text-secondary"><?php echo htmlspecialchars($o['date_publication'] ?? ''); ?></td>
          <td>
            <div class="flex gap-1">
              <a href="offres_admin.php?action=edit&id=<?php echo $o['id_offre']; ?>" class="btn btn-sm btn-ghost" title="Éditer"><i data-lucide="pencil" style="width:14px;height:14px;"></i></a>
              <button type="button" onclick="confirmDelete(<?php echo $o['id_offre']; ?>, '<?php echo htmlspecialchars(addslashes($o['titre'] ?? '')); ?>')" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
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
    if ($action === 'edit' && isset($_GET['id'])) {
        $offreEdit = $offreC->getOffreById($_GET['id']);
    }
  ?>
  <div class="card" style="max-width: 800px; margin: 0 auto; background: var(--surface-1);">
    <h2 class="mb-6 text-xl fw-bold d-flex align-items-center gap-2">
        <i data-lucide="<?php echo $action === 'edit' ? 'pencil' : 'plus-circle'; ?>" style="width:24px;height:24px;color:var(--accent-primary);"></i>
        <?php echo $action === 'edit' ? 'Modifier l\'offre' : 'Nouvelle offre'; ?>
    </h2>
    
    <form method="POST" action="offres_admin.php?<?php echo $action === 'edit' ? 'action=edit&id='.$_GET['id'] : 'action=add'; ?>" class="auth-form">
        <?php if ($action === 'edit'): ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);" class="mb-4">
            <div class="form-group">
                <label class="form-label">ID de l'offre</label>
                <input type="text" class="input" value="#<?php echo htmlspecialchars($offreEdit['id_offre'] ?? ''); ?>" disabled readonly style="background: var(--bg-body); cursor: not-allowed; color: var(--text-secondary);">
            </div>
            <div class="form-group">
                <label class="form-label">Entreprise</label>
                <input type="text" class="input" value="Ent. <?php echo htmlspecialchars($offreEdit['id_entreprise'] ?? '1'); ?>" disabled readonly style="background: var(--bg-body); cursor: not-allowed; color: var(--text-secondary);">
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group mb-4">
            <label class="form-label" for="titre">Titre du poste</label>
            <input type="text" class="input" name="titre" id="titre" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['titre']) : ''; ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);" class="mb-4">
            <div class="form-group">
                <label class="form-label" for="domaine">Domaine</label>
                <input type="text" class="input" name="domaine" id="domaine" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['domaine']) : ''; ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="salaire">Salaire (TND)</label>
                <input type="number" step="0.01" class="input" name="salaire" id="salaire" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['salaire']) : ''; ?>">
            </div>
        </div>

        <div class="form-group mb-4">
            <label class="form-label" for="competences_requises">Compétences Requises</label>
            <input type="text" class="input" name="competences_requises" id="competences_requises" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['competences_requises']) : ''; ?>">
        </div>

        <div class="form-group mb-4">
            <label class="form-label" for="experience_requise">Expérience Requise</label>
            <input type="text" class="input" name="experience_requise" id="experience_requise" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['experience_requise']) : ''; ?>">
        </div>

        <div class="form-group mb-4">
            <label class="form-label" for="question">Question Personnalisée pour les candidats</label>
            <input type="text" class="input" name="question" id="question" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['question']) : ''; ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);" class="mb-4">
            <div class="form-group">
                <label class="form-label" for="date_publication">Date de Publication</label>
                <input type="date" class="input" name="date_publication" id="date_publication" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['date_publication']) : date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="date_expir">Date d'Expiration</label>
                <input type="date" class="input" name="date_expir" id="date_expir" required value="<?php echo $offreEdit ? htmlspecialchars($offreEdit['date_expir']) : ''; ?>">
            </div>
        </div>

        <div class="form-group mb-6">
            <label class="form-label" for="description">Description complète</label>
            <textarea class="textarea" name="description" id="description" rows="5" required><?php echo $offreEdit ? htmlspecialchars($offreEdit['description']) : ''; ?></textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" name="<?php echo $action === 'edit' ? 'submit_update' : 'submit_add'; ?>" class="btn btn-primary d-flex align-items-center gap-2">
                <i data-lucide="check" style="width:16px;height:16px;"></i>
                <?php echo $action === 'edit' ? 'Mettre à jour' : 'Publier l\'offre'; ?>
            </button>
            <a href="offres_admin.php" class="btn btn-secondary text-decoration-none">Annuler</a>
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

function confirmDelete(id, titre) {
    var titleEl = document.getElementById('delete-offer-title');
    if(titleEl) titleEl.innerText = titre;
    
    var btnEl = document.getElementById('confirm-delete-btn');
    if(btnEl) btnEl.href = "offres_admin.php?action=delete&id=" + id;
    
    var overlay = document.getElementById('delete-confirm-modal');
    if (overlay) {
        overlay.classList.add('active');
        var modal = overlay.querySelector('.modal');
        if (modal) modal.classList.add('active');
    }
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
        <a href="#" id="confirm-delete-btn" class="btn btn-primary" style="flex:1; background: #dc2626; border-color: #dc2626; display:flex; justify-content:center;">
          Oui, Supprimer
        </a>
      </div>
    </div>
  </div>
</div>
