<?php 
$pageTitle = "Browse Jobs"; 
$pageCSS = "feeds.css"; 

require_once '../../controller/offreC.php';
$offreC = new offreC();
$listeOffres = $offreC->afficherOffres(true);
$count = $listeOffres->rowCount();
?><?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="search" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Browse Jobs &amp; Tasks
  </h1>
  <p class="page-header__subtitle">Trouvez l'offre qui correspond à votre profil</p>
</div>

<!-- ═══ FILTER BAR ═══ -->
<div class="job-filter-bar mb-6" id="job-filters" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
  <!-- Group 1: Search -->
  <div style="display:flex; gap: 0.5rem; flex: 1; min-width: 300px;">
    <div class="input-icon-wrapper search-input" style="flex:1;">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      <input type="text" class="input" id="job-search" placeholder="Mot-clé, poste...">
    </div>
    <button class="btn btn-primary" id="job-search-btn">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      Rechercher
    </button>
  </div>

  <!-- Group 2: Location & Mode -->
  <div class="input-icon-wrapper" style="width: 180px;">
    <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-location" placeholder="Localisation...">
  </div>

  <div class="mode-toggle" id="mode-toggle" style="flex-shrink: 0;">
    <button class="mode-toggle__option active" data-mode="all">Tout</button>
    <button class="mode-toggle__option" data-mode="remote">À distance</button>
    <button class="mode-toggle__option" data-mode="onsite">Sur site</button>
    <button class="mode-toggle__option" data-mode="hybrid">Hybride</button>
  </div>

  <!-- Group 3: Sorting Options -->
  <div style="display:flex; gap: 0.5rem; flex-shrink: 0;">
    <select class="select" id="job-sort-date" style="width: 140px;">
      <option value="" disabled selected>Date de pub.</option>
      <option value="newest">Plus récent</option>
      <option value="oldest">Plus ancien</option>
    </select>
    
    <select class="select" id="job-sort-salary" style="width: 140px;">
      <option value="" disabled selected>Salaire</option>
      <option value="asc">Croissant ↑</option>
      <option value="desc">Décroissant ↓</option>
    </select>
  </div>
</div>

<!-- Results Info -->
<div class="results-info mb-4">
  <strong><?php echo $count; ?></strong> results found
</div>

<!-- ═══ JOB CARDS GRID ═══ -->
<div class="job-cards-grid stagger">
  <?php foreach ($listeOffres as $offreItem): ?>
  <div class="job-card animate-on-scroll">
    <div class="job-card__header">
      <div class="job-card__company-logo">
        <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
      </div>
      <div class="job-card__title-group">
        <h3 class="job-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
        <span class="job-card__company">Domaine: <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?></span>
      </div>
      <span class="badge badge-info job-card__type-badge">Job</span>
    </div>
    <p class="job-card__description" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($offreItem['description'] ?? ''); ?></p>
    <div class="job-card__tags">
      <span class="job-card__tag" title="Compétences"><i data-lucide="award" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['competences_requises'] ?? ''); ?></span>
      <span class="job-card__tag" title="Expérience"><i data-lucide="clock" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['experience_requise'] ?? ''); ?></span>
      <span class="job-card__tag" title="Salaire"><i data-lucide="banknote" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['salaire'] ?? ''); ?> TND</span>
    </div>
    <div class="job-card__footer">
      <span class="job-card__date">
        <i data-lucide="calendar" style="width:12px;height:12px;"></i> Publié: <?php echo htmlspecialchars($offreItem['date_publication'] ?? ''); ?>
      </span>
      <button class="btn btn-sm btn-primary">
        <i data-lucide="send" style="width:14px;height:14px;"></i> Postuler
      </button>
    </div>
  </div>
  <?php endforeach; ?>
  
  <?php if ($count == 0): ?>
    <div class="empty-state text-center" style="padding: 3rem; background: var(--surface-1); border-radius: 12px; grid-column: 1 / -1;">
        <p>Aucune offre trouvée pour le moment.</p>
    </div>
  <?php endif; ?>
</div>

<!-- Pagination -->
<div class="pagination">
  <button class="pagination__btn">&laquo;</button>
  <button class="pagination__btn active">1</button>
  <button class="pagination__btn">2</button>
  <button class="pagination__btn">3</button>
  <button class="pagination__btn">&raquo;</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mode toggle interaction
  document.querySelectorAll('.mode-toggle__option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.mode-toggle__option').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
    });
  });
});
</script>
