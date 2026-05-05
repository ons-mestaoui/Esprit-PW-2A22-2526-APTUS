<?php $pageTitle = "Browse Jobs"; $pageCSS = "feeds.css"; ?>

<?php
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
<div class="job-filter-bar mb-6" id="job-filters">
  <div class="input-icon-wrapper search-input" style="flex:1;max-width:280px;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-search" placeholder="Search by keyword...">
  </div>
  <div class="input-icon-wrapper" style="max-width:180px;">
    <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-location" placeholder="Location...">
  </div>
  <select class="select" id="job-type" style="max-width:160px;">
    <option value="">Job Offer</option>
    <option>Job Offer</option>
    <option>Internship</option>
    <option>Freelance</option>
  </select>
  <select class="select" id="job-time" style="max-width:140px;">
    <option value="">Full-time</option>
    <option>Full-time</option>
    <option>Part-time</option>
    <option>Contract</option>
  </select>
  <div class="mode-toggle" id="mode-toggle">
    <button class="mode-toggle__option active" data-mode="all">All</button>
    <button class="mode-toggle__option" data-mode="remote">Remote</button>
    <button class="mode-toggle__option" data-mode="onsite">On-site</button>
    <button class="mode-toggle__option" data-mode="hybrid">Hybrid</button>
  </div>
  <select class="select" id="job-sort" style="max-width:130px;">
    <option>Newest</option>
    <option>Oldest</option>
    <option>Salary ↑</option>
    <option>Salary ↓</option>
  </select>
  <button class="btn btn-primary" id="job-search-btn">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    Search
  </button>
</div>

<!-- Results Info -->
<div class="results-info mb-4">
  <strong>0</strong> results found
</div>

<!-- ═══ JOB CARDS GRID ═══ -->
<div class="job-cards-grid stagger">
  <div class="empty-state" style="grid-column: 1 / -1; padding: var(--space-20); text-align: center; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); opacity: 0.8;">
    <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
        <i data-lucide="briefcase" style="width: 40px; height: 40px; color: var(--text-tertiary);"></i>
    </div>
    <h3 style="margin-bottom: var(--space-2);">Aucune offre d'emploi</h3>
    <p style="color: var(--text-secondary);">Revenez plus tard pour découvrir de nouvelles opportunités.</p>
  </div>
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
