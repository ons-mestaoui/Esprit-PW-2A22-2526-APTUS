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
      <div class="stat-card__value">1,284</div>
      <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +8% ce mois</div>
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
<div class="grid grid-2 gap-6 mb-8">
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Activité des Posts (Mensuel)</h3>
    <div id="posts-monthly-chart" style="height:250px;"></div>
  </div>
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Répartition par catégorie</h3>
    <div class="flex items-center justify-center" id="category-donut-chart"></div>
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
      <?php
      $posts = [
        ['title' => 'Senior Full Stack Dev - TechSphere', 'author' => 'TechSphere Inc.', 'cat' => 'Offre', 'views' => '2.4k', 'engagement' => '89%', 'date' => '08 Avr.', 'cat_badge' => 'badge-info'],
        ['title' => 'Tendances IT Q1 2026', 'author' => 'Admin Aptus', 'cat' => 'Rapport', 'views' => '1.8k', 'engagement' => '92%', 'date' => '05 Avr.', 'cat_badge' => 'badge-primary'],
        ['title' => 'React.js Avancé - Formation', 'author' => 'Ahmed Ben Ali', 'cat' => 'Formation', 'views' => '967', 'engagement' => '76%', 'date' => '01 Avr.', 'cat_badge' => 'badge-success'],
        ['title' => 'Data Engineer - DataFlow', 'author' => 'DataFlow Analytics', 'cat' => 'Offre', 'views' => '1.2k', 'engagement' => '81%', 'date' => '28 Mar.', 'cat_badge' => 'badge-info'],
        ['title' => 'Compétences clés 2026', 'author' => 'Admin Aptus', 'cat' => 'Rapport', 'views' => '3.1k', 'engagement' => '94%', 'date' => '25 Mar.', 'cat_badge' => 'badge-primary'],
      ];
      foreach ($posts as $p):
      ?>
      <tr>
        <td class="fw-medium"><?php echo $p['title']; ?></td>
        <td class="text-sm text-secondary"><?php echo $p['author']; ?></td>
        <td><span class="badge <?php echo $p['cat_badge']; ?>"><?php echo $p['cat']; ?></span></td>
        <td class="fw-medium"><?php echo $p['views']; ?></td>
        <td>
          <div class="flex items-center gap-2">
            <div class="progress-bar" style="width:60px;height:6px;">
              <div class="progress-bar__fill" style="width:<?php echo $p['engagement']; ?>;"></div>
            </div>
            <span class="text-xs fw-medium"><?php echo $p['engagement']; ?></span>
          </div>
        </td>
        <td class="text-sm text-secondary"><?php echo $p['date']; ?></td>
        <td>
          <div class="flex gap-1">
            <button class="btn btn-sm btn-ghost" title="Voir"><i data-lucide="eye" style="width:14px;height:14px;"></i></button>
            <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Posts Monthly Chart
  AptusCharts.bar('posts-monthly-chart', [
    { label: 'Jan', value: 156 },
    { label: 'Fév', value: 198 },
    { label: 'Mar', value: 234 },
    { label: 'Avr', value: 189 },
    { label: 'Mai', value: 267 },
    { label: 'Jun', value: 240 },
  ], { barColor: 'var(--chart-1)', height: 250 });

  // Category Donut
  AptusCharts.donut('category-donut-chart', [
    { label: 'Offres d\'emploi', value: 680 },
    { label: 'Rapports marché', value: 234 },
    { label: 'Formations', value: 189 },
    { label: 'Autres', value: 181 },
  ], {
    size: 180,
    strokeWidth: 28,
    centerValue: '1,284',
    centerLabel: 'Total'
  });
});
</script>
