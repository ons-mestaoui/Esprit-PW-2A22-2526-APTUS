<?php 
$pageTitle = "Browse Jobs"; 
$pageCSS = "feeds.css"; 

require_once '../../controller/offreC.php';
$offreC = new offreC();

$criteres = [];
if (!empty($_GET['sort_salaire'])) {
    $criteres['sort_salaire'] = $_GET['sort_salaire'];
}
if (!empty($_GET['sort_date'])) {
    $criteres['sort_date'] = $_GET['sort_date'];
}

// Toujours filtrer sur les offres actives pour les candidats
$listeOffres = !empty($criteres)
    ? $offreC->filtrerOffres(array_merge($criteres, ['statut' => 'Actif']))
    : $offreC->afficherOffres(true);
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
  <form method="GET" action="jobs_feed.php" style="display:flex; gap: 0.5rem; flex-shrink: 0;">
    <select class="select" id="job-sort-date" name="sort_date" style="width: 140px;" onchange="this.form.submit()">
      <option value="">Date de pub.</option>
      <option value="DESC" <?php echo (isset($_GET['sort_date']) && $_GET['sort_date'] === 'DESC') ? 'selected' : ''; ?>>Plus récent ↓</option>
      <option value="ASC" <?php echo (isset($_GET['sort_date']) && $_GET['sort_date'] === 'ASC') ? 'selected' : ''; ?>>Plus ancien ↑</option>
    </select>
    
    <select class="select" id="job-sort-salary" name="sort_salaire" style="width: 140px;" onchange="this.form.submit()">
      <option value="">Salaire</option>
      <option value="ASC" <?php echo (isset($_GET['sort_salaire']) && $_GET['sort_salaire'] === 'ASC') ? 'selected' : ''; ?>>Croissant ↑</option>
      <option value="DESC" <?php echo (isset($_GET['sort_salaire']) && $_GET['sort_salaire'] === 'DESC') ? 'selected' : ''; ?>>Décroissant ↓</option>
    </select>
  </form>
</div>

<!-- Results Info -->
<div class="results-info mb-4">
  <strong><?php echo $count; ?></strong> results found
</div>

<!-- ═══ JOB CARDS GRID ═══ -->
<div class="job-cards-grid stagger">
  <?php foreach ($listeOffres as $offreItem): ?>
    <div class="job-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
      <?php if (!empty($offreItem['img_post'])): ?>
        <div style="height: 160px; background-image: url('<?php echo htmlspecialchars($offreItem['img_post']); ?>'); background-size: cover; background-position: center; border-bottom: 1px solid var(--border-color);"></div>
      <?php else: ?>
        <div style="height: 6px; background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));"></div>
      <?php endif; ?>
      <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
      <div class="job-card__header">
        <div class="job-card__company-logo">
        <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
      </div>
      <div class="job-card__title-group">
        <h3 class="job-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
        <span class="job-card__company"><?php echo htmlspecialchars($offreItem['nom_entreprise'] ?? 'Entreprise Inconnue'); ?> • <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?></span>
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
      <button type="button" class="btn btn-sm" style="background: linear-gradient(90deg, #4fb5ff 0%, #a864e4 50%, #d85ab2 100%); border: none; color: white; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';" onclick="openOfferModal(<?php echo htmlspecialchars(json_encode([
          'id' => $offreItem['id_offre'],
          'titre' => $offreItem['titre'],
          'nom_entreprise' => $offreItem['nom_entreprise'] ?? 'Entreprise Inconnue',
          'domaine' => $offreItem['domaine'],
          'description' => $offreItem['description'],
          'competences' => $offreItem['competences_requises'],
          'experience' => $offreItem['experience_requise'],
          'salaire' => $offreItem['salaire'],
          'date_pub' => $offreItem['date_publication'],
          'img_post' => $offreItem['img_post'] ?? ''
      ])); ?>)">
        <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
      </button>
    </div>
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

function openOfferModal(data) {
    document.getElementById('modal-title').innerText = data.titre || 'Titre inconnu';
    document.getElementById('modal-company').innerText = (data.nom_entreprise || 'Entreprise Inconnue') + ' • ' + (data.domaine || '');
    document.getElementById('modal-desc').innerText = data.description || 'Aucune description fournie.';
    document.getElementById('modal-skills').innerText = data.competences || 'N/A';
    document.getElementById('modal-exp').innerText = data.experience || 'N/A';
    document.getElementById('modal-salary').innerText = data.salaire || 'N/A';
    
    var coverEl = document.getElementById('modal-cover-img');
    if (data.img_post) {
        coverEl.style.backgroundImage = "url('" + data.img_post + "')";
    } else {
        coverEl.style.backgroundImage = "linear-gradient(90deg, var(--accent-primary), var(--accent-secondary))";
    }

    var overlay = document.getElementById('offer-details-modal');
    if (overlay) {
        overlay.classList.add('active');
        var modal = overlay.querySelector('.modal');
        if (modal) modal.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var closeBtns = document.querySelectorAll('.modal-close-btn');
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var overlay = document.getElementById('offer-details-modal');
            if (overlay) {
                overlay.classList.remove('active');
                var modal = overlay.querySelector('.modal');
                if (modal) modal.classList.remove('active');
            }
        });
    });
});
</script>

<!-- ═══ Modal Détails de l'Offre ═══ -->
<div class="modal-overlay" id="offer-details-modal">
  <div class="modal" style="max-width:650px; padding:0; overflow:hidden; border-radius:16px; display:flex; flex-direction:column; max-height:90vh;">
    <div id="modal-cover-img" style="height:150px; background:linear-gradient(90deg, var(--accent-primary), var(--accent-secondary)); background-size:cover; background-position:center;"></div>
    <div class="modal-body" style="padding: 2.5rem; overflow-y:auto; flex:1;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 1.5rem;">
        <div>
          <h2 id="modal-title" style="font-size:1.75rem; font-weight:700; margin-bottom:0.35rem; color:var(--text-primary);">Titre</h2>
          <p id="modal-company" style="color:var(--text-secondary); font-size:1rem;">Entreprise • Domaine</p>
        </div>
        <span class="badge badge-success">Actif</span>
      </div>
      
      <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; padding: 1rem; background: var(--bg-body); border-radius: 8px;">
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="award" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-skills" style="margin-left:4px;"></strong></span>
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="clock" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-exp" style="margin-left:4px;"></strong></span>
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="banknote" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-salary" style="margin-left:4px;"></strong> TND</span>
      </div>
      
      <div style="margin-bottom: 2.5rem; max-height: 45vh; overflow-y: auto; padding-right: 0.5rem;">
        <h4 style="font-size:1.1rem; font-weight:600; margin-bottom:0.75rem; color:var(--text-primary);">Description du poste</h4>
        <p id="modal-desc" style="color:var(--text-secondary); line-height:1.7; white-space:pre-wrap; word-break: break-word; overflow-wrap: anywhere;"></p>
      </div>

      <div class="flex gap-3 justify-end" style="border-top:1px solid var(--border-color); padding-top:1.5rem;">
        <button type="button" class="btn btn-secondary modal-close-btn" style="padding: 0.5rem 1.5rem;">Fermer</button>
        <button type="button" class="btn btn-primary" onclick="alert('Fonctionnalité de candidature à venir !')" style="padding: 0.5rem 1.5rem;">
          <i data-lucide="send" style="width:16px;height:16px;"></i> Postuler
        </button>
      </div>
    </div>
  </div>
</div>
