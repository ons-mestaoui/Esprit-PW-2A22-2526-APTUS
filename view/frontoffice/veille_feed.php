<?php 
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';
$vc = new VeilleC();
$dbReports = $vc->afficherRapports();

$pageTitle = "Veille du Marché"; 
$pageCSS = "veille.css"; 

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="line-chart" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Veille du Marché
  </h1>
  <p class="page-header__subtitle">Rapports, analyses et données du marché de l'emploi</p>
</div>

<div class="veille-layout">
  <!-- ═══ MAIN FEED ═══ -->
  <div class="report-feed stagger">

    <!-- Featured Report -->
    <?php if (count($dbReports) > 0): $featured = $dbReports[0]; ?>
    <article class="report-card report-card--featured animate-on-scroll" id="report-featured">
      <div class="report-card__header">
        <div class="report-card__header-content">
          <h2 class="report-card__title"><?php echo htmlspecialchars($featured['titre']); ?></h2>
          <p class="report-card__excerpt">
            <?php echo htmlspecialchars(substr($featured['description'], 0, 300)) . '...'; ?>
          </p>
        </div>
      </div>
      <?php if (!empty($featured['image_couverture'])): ?>
        <img src="<?php echo $featured['image_couverture']; ?>" alt="Cover" class="report-card__image">
      <?php endif; ?>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($featured['auteur']); ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo date('d M. Y', strtotime($featured['date_publication'])); ?>
        </span>
        <span class="report-card__meta-item report-card__meta-item--views">
          <i data-lucide="eye" style="width:12px;height:12px;"></i> <?php echo $featured['vues']; ?> vues
        </span>
        <span class="badge badge-info"><?php echo htmlspecialchars($featured['secteur_principal']); ?></span>
      </div>
      <div class="report-card__footer">
        <a href="veille_details.php?id=<?php echo $featured['id_rapport_marche']; ?>" class="btn btn-sm btn-primary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>
    <?php endif; ?>

    <!-- Regular Reports -->
    <?php
    for ($i = 1; $i < count($dbReports); $i++):
      $r = $dbReports[$i];
    ?>
    <article class="report-card animate-on-scroll" id="report-<?php echo $i; ?>">
      <div class="report-card__header">
        <div class="report-card__header-content">
          <h3 class="report-card__title"><?php echo htmlspecialchars($r['titre']); ?></h3>
          <p class="report-card__excerpt"><?php echo htmlspecialchars(substr($r['description'], 0, 150)) . '...'; ?></p>
        </div>
        <span class="badge badge-primary report-card__category"><?php echo htmlspecialchars($r['secteur_principal']); ?></span>
      </div>
      <?php if (!empty($r['image_couverture'])): ?>
        <img src="<?php echo $r['image_couverture']; ?>" alt="Cover" class="report-card__image">
      <?php endif; ?>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($r['auteur']); ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo date('d M. Y', strtotime($r['date_publication'])); ?>
        </span>
        <span class="report-card__meta-item report-card__meta-item--views">
          <i data-lucide="eye" style="width:12px;height:12px;"></i> <?php echo $r['vues']; ?> vues
        </span>
      </div>
      <div class="report-card__footer">
        <a href="veille_details.php?id=<?php echo $r['id_rapport_marche']; ?>" class="btn btn-sm btn-secondary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>
    <?php endfor; ?>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="veille-sidebar">
    <!-- Quick Stats -->
    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Offres ce mois</span>
        <span class="badge badge-success">+12%</span>
      </div>
      <div class="data-card-mini__value">2,845</div>
      <div class="data-card-mini__chart" id="sparkline-offres"></div>
    </div>

    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Salaire moyen (IT)</span>
        <span class="badge badge-info">TND</span>
      </div>
      <div class="data-card-mini__value">3,200</div>
      <div class="data-card-mini__chart" id="sparkline-salary"></div>
    </div>

    <!-- Top Sectors Chart -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-4);">Top Secteurs</div>
      <div class="simple-bar-chart">
        <div class="simple-bar-chart__row">
          <span class="simple-bar-chart__label">IT</span>
          <div class="simple-bar-chart__bar"><div class="simple-bar-chart__fill" style="width:85%;background:var(--chart-1);"></div></div>
          <span class="simple-bar-chart__value">85%</span>
        </div>
        <div class="simple-bar-chart__row">
          <span class="simple-bar-chart__label">Finance</span>
          <div class="simple-bar-chart__bar"><div class="simple-bar-chart__fill" style="width:62%;background:var(--chart-2);"></div></div>
          <span class="simple-bar-chart__value">62%</span>
        </div>
        <div class="simple-bar-chart__row">
          <span class="simple-bar-chart__label">Santé</span>
          <div class="simple-bar-chart__bar"><div class="simple-bar-chart__fill" style="width:45%;background:var(--chart-3);"></div></div>
          <span class="simple-bar-chart__value">45%</span>
        </div>
        <div class="simple-bar-chart__row">
          <span class="simple-bar-chart__label">Commerce</span>
          <div class="simple-bar-chart__bar"><div class="simple-bar-chart__fill" style="width:38%;background:var(--chart-4);"></div></div>
          <span class="simple-bar-chart__value">38%</span>
        </div>
      </div>
    </div>

    <!-- Trending Topics -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-3);">Sujets tendance</div>
      <div class="trending-list">
        <div class="trending-item"><span class="trending-item__rank">1</span><span class="trending-item__text">Intelligence Artificielle</span><span class="trending-item__count">324</span></div>
        <div class="trending-item"><span class="trending-item__rank">2</span><span class="trending-item__text">Cybersécurité</span><span class="trending-item__count">218</span></div>
        <div class="trending-item"><span class="trending-item__rank">3</span><span class="trending-item__text">Cloud Computing</span><span class="trending-item__count">195</span></div>
        <div class="trending-item"><span class="trending-item__rank">4</span><span class="trending-item__text">Remote Work</span><span class="trending-item__count">167</span></div>
        <div class="trending-item"><span class="trending-item__rank">5</span><span class="trending-item__text">Green Tech</span><span class="trending-item__count">134</span></div>
      </div>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  AptusCharts.sparkline('sparkline-offres', [42, 55, 48, 62, 70, 65, 78, 85, 72, 90, 88, 95], 'var(--chart-2)');
  AptusCharts.sparkline('sparkline-salary', [28, 30, 29, 31, 32, 30, 33, 34, 32, 35, 33, 36], 'var(--chart-3)');
});
</script>
