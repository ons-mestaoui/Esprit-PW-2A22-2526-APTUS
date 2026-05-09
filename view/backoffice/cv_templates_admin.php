<?php $pageTitle = "Templates CV"; $pageCSS = "cv.css"; ?>

<?php
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
      <h1>Templates CV</h1>
      <p>Gérez les templates disponibles pour les utilisateurs</p>
    </div>
    <button class="btn btn-primary" data-modal="add-template-modal" id="add-template-btn">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Ajouter un Template
    </button>
  </div>
</div>

<!-- ═══ Stats Cards ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Total Templates</div>
      <div class="stat-card__value">24</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +3 ce mois
      </div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="layout-template" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Plus utilisé</div>
      <div class="stat-card__value" style="font-size:var(--fs-md);">Tech Stack</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> 1,240 utilisations
      </div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="star" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">CVs générés (total)</div>
      <div class="stat-card__value">8,432</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +18% ce mois
      </div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="file-check" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Ajouts récents</div>
      <div class="stat-card__value">3</div>
      <div class="stat-card__trend">
        <span class="text-tertiary">Cette semaine</span>
      </div>
    </div>
    <div class="stat-card__icon orange"><i data-lucide="clock" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<!-- ═══ Top Used Templates Chart + Table ═══ -->
<div class="grid" style="grid-template-columns: 1fr 300px; gap: var(--space-6);">
  <!-- Templates Table -->
  <div class="card-flat" style="overflow:hidden;">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
      <h3 class="text-md fw-semibold">Tous les Templates</h3>
      <div class="search-bar" style="max-width:240px;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher..." id="admin-template-search">
      </div>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Template</th>
          <th>Catégorie</th>
          <th>Utilisations</th>
          <th>Date d'ajout</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $adminTemplates = [
          ['name' => 'Tech Stack', 'cat' => 'Technologie', 'uses' => 1240, 'date' => '15 Jan 2026'],
          ['name' => 'Executive Pro', 'cat' => 'Business', 'uses' => 980, 'date' => '20 Jan 2026'],
          ['name' => 'Créatif Bold', 'cat' => 'Design', 'uses' => 856, 'date' => '02 Fév 2026'],
          ['name' => 'Modern Flow', 'cat' => 'Moderne', 'uses' => 723, 'date' => '10 Fév 2026'],
          ['name' => 'Data Analyst', 'cat' => 'Technologie', 'uses' => 654, 'date' => '18 Mar 2026'],
          ['name' => 'Marketing Pro', 'cat' => 'Marketing', 'uses' => 512, 'date' => '25 Mar 2026'],
          ['name' => 'Minimaliste', 'cat' => 'Minimaliste', 'uses' => 489, 'date' => '01 Avr 2026'],
        ];
        foreach ($adminTemplates as $i => $t):
        ?>
        <tr>
          <td>
            <div class="flex items-center gap-3">
              <div style="width:32px;height:40px;border-radius:4px;background:var(--gradient-card);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;">
                <i data-lucide="file-text" style="width:14px;height:14px;color:var(--text-tertiary);"></i>
              </div>
              <span class="fw-medium"><?php echo $t['name']; ?></span>
            </div>
          </td>
          <td><span class="badge badge-primary"><?php echo $t['cat']; ?></span></td>
          <td class="fw-medium"><?php echo number_format($t['uses']); ?></td>
          <td class="text-secondary text-sm"><?php echo $t['date']; ?></td>
          <td>
            <div class="flex gap-1">
              <button class="btn btn-sm btn-ghost" title="Éditer"><i data-lucide="pencil" style="width:14px;height:14px;"></i></button>
              <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="pagination" style="padding:var(--space-4);">
      <button class="pagination__btn">&laquo;</button>
      <button class="pagination__btn active">1</button>
      <button class="pagination__btn">2</button>
      <button class="pagination__btn">3</button>
      <button class="pagination__btn">&raquo;</button>
    </div>
  </div>

  <!-- Top Used Chart -->
  <div class="card">
    <h4 class="text-sm fw-semibold mb-6">Templates les plus utilisés</h4>
    <div id="template-usage-chart"></div>
  </div>
</div>

<!-- ═══ Add Template Modal ═══ -->
<div class="modal-overlay" id="add-template-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>Ajouter un Template</h3>
      <button class="modal-close btn-icon" aria-label="Fermer">
        <i data-lucide="x" style="width:20px;height:20px;"></i>
      </button>
    </div>
    <div class="modal-body">
      <form class="auth-form" data-validate id="add-template-form">
        <div class="form-group">
          <label class="form-label" for="tpl-name">Nom du template</label>
          <input type="text" class="input" id="tpl-name" name="name" placeholder="Ex: Tech Stack" required>
          <span class="form-error"></span>
        </div>
        <div class="form-group">
          <label class="form-label" for="tpl-category">Catégorie</label>
          <select class="select" id="tpl-category" name="category" required>
            <option value="">Sélectionnez...</option>
            <option>Technologie</option>
            <option>Business</option>
            <option>Design</option>
            <option>Marketing</option>
            <option>Santé</option>
            <option>Minimaliste</option>
            <option>Moderne</option>
            <option>Classique</option>
            <option>Créatif</option>
          </select>
          <span class="form-error"></span>
        </div>
        <div class="form-group">
          <label class="form-label">Fichier template (HTML/CSS)</label>
          <div class="drop-zone">
            <input type="file" class="drop-zone__input" name="template_file" accept=".html,.css,.zip">
            <div class="drop-zone__prompt">
              <i data-lucide="upload-cloud" style="width:28px;height:28px;"></i>
              <span>Déposez le fichier ici ou <span class="text-accent">parcourir</span></span>
              <span class="text-xs text-tertiary">HTML, CSS, ZIP</span>
            </div>
            <div class="drop-zone__preview"></div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="tpl-description">Description</label>
          <textarea class="textarea" id="tpl-description" name="description" rows="3" placeholder="Décrivez ce template..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary modal-close">Annuler</button>
      <button class="btn btn-primary" type="submit" form="add-template-form">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Ajouter
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Template usage chart
  AptusCharts.bar('template-usage-chart', [
    { label: 'Tech Stack', value: 1240 },
    { label: 'Exec Pro', value: 980 },
    { label: 'Créatif', value: 856 },
    { label: 'Modern', value: 723 },
    { label: 'Data', value: 654 },
  ], { barColor: 'var(--chart-1)', height: 220 });
});
</script>
