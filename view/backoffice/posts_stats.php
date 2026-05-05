<?php $pageTitle = "Posts & Stats"; ?>

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
      <h1>Posts &amp; Statistiques</h1>
      <p>Vue d'ensemble de l'activité, engagement et métriques de la plateforme</p>
    </div>
    <div class="flex gap-3">
      <select class="select" style="max-width:160px;" id="stats-period">
        <option>7 derniers jours</option>
        <option>30 derniers jours</option>
        <option>3 derniers mois</option>
        <option>Cette année</option>
      </select>
      <button class="btn btn-primary" id="export-stats-btn">
        <i data-lucide="download" style="width:18px;height:18px;"></i>
        Exporter
      </button>
    </div>
  </div>
</div>

<!-- ═══ Engagement Stats ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Posts publiés</div>
      <div class="stat-card__value">0</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> 0 ce mois</div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="file-text" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Engagement moyen</div>
      <div class="stat-card__value">0%</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0%</div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="heart" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Vues totales</div>
      <div class="stat-card__value">0</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0%</div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="eye" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Taux de conversion</div>
      <div class="stat-card__value">0%</div>
      <div class="stat-card__trend"><i data-lucide="trending-down" style="width:14px;height:14px;"></i> -0%</div>
    </div>
    <div class="stat-card__icon orange"><i data-lucide="target" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<!-- ═══ Charts Row ═══ -->
<div class="grid grid-2 gap-6 mb-8">
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Activité des Posts (Mensuel)</h3>
    <div id="posts-monthly-chart" style="height:250px;">
        <div class="empty-state-mini" style="padding:var(--space-8);text-align:center;opacity:0.6;">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Données insuffisantes</p>
       </div>
    </div>
  </div>
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Répartition par catégorie</h3>
    <div class="flex items-center justify-center" id="category-donut-chart">
        <div class="empty-state-mini" style="padding:var(--space-8);text-align:center;opacity:0.6;">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Aucune catégorie</p>
       </div>
    </div>
  </div>
</div>

<!-- ═══ Posts Table ═══ -->
<div class="card-flat" style="overflow:hidden;">
  <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
    <h3 class="text-md fw-semibold">Derniers Posts</h3>
    <div class="search-bar" style="max-width:280px;">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      <input type="text" class="input" placeholder="Rechercher un post..." id="posts-search">
    </div>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Post</th>
        <th>Auteur</th>
        <th>Catégorie</th>
        <th>Vues</th>
        <th>Engagement</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
       <tr>
        <td colspan="7">
          <div class="empty-state-mini" style="padding:var(--space-12);text-align:center;background:var(--bg-secondary);border-radius:var(--radius-lg);opacity:0.6;">
            <i data-lucide="file-text" style="width:40px;height:40px;margin:0 auto var(--space-3);display:block;color:var(--text-tertiary);"></i>
            <p style="color:var(--text-secondary);">Aucun post publié pour le moment</p>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Charts are only rendered if data exists, here they are empty
});
</script>
