<?php $pageTitle = "Veille du Marché"; $pageCSS = "veille.css"; ?>

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
    <i data-lucide="line-chart" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Veille du Marché
  </h1>
  <p class="page-header__subtitle">Rapports, analyses et données du marché de l'emploi</p>
</div>

<div class="veille-layout">
  <!-- ═══ MAIN FEED ═══ -->
  <div class="report-feed stagger">

    <!-- Featured Report -->
    <article class="report-card report-card--featured animate-on-scroll" id="report-featured">
      <div class="report-card__header">
        <div>
          <h2 class="report-card__title">Tendances du marché IT en Tunisie : Bilan Q1 2026</h2>
          <p class="report-card__excerpt">
            Le premier trimestre 2026 a marqué une croissance significative dans le secteur tech tunisien. Les postes de développeurs Full Stack et Data Engineers ont connu une augmentation de 34% par rapport à la même période de l'année précédente. L'émergence de l'IA générative a créé de nouveaux rôles comme les "AI Prompt Engineers" et les "ML Platform Engineers". Ce rapport couvre les secteurs clés, les fourchettes salariales et les compétences les plus demandées par les recruteurs.
          </p>
        </div>
      </div>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> Admin Aptus
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> 08 Avr. 2026
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="clock" style="width:12px;height:12px;"></i> 12 min de lecture
        </span>
        <span class="badge badge-info">Technologie</span>
      </div>
      <div class="report-card__footer">
        <a href="#" class="btn btn-sm btn-primary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport complet
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>

    <!-- Regular Reports -->
    <?php
    $reports = [
      [
        'title' => 'Les compétences les plus recherchées en 2026',
        'excerpt' => 'Python, React, Cloud AWS et la Data Science dominent les offres d\'emploi au premier trimestre. L\'analyse de 5000+ offres révèle les tendances majeures et les gaps de compétences sur le marché.',
        'author' => 'Admin Aptus',
        'date' => '02 Avr. 2026',
        'time' => '8 min',
        'category' => 'Compétences',
        'badge_class' => 'badge-primary'
      ],
      [
        'title' => 'Salaires du secteur digital : Comparatif régional',
        'excerpt' => 'Comparaison des fourchettes salariales entre Tunis, Sfax, Sousse et les offres remote internationales. Les écarts se réduisent grâce au télétravail mais des disparités persistent selon les domaines.',
        'author' => 'Admin Aptus',
        'date' => '28 Mar. 2026',
        'time' => '10 min',
        'category' => 'Salaires',
        'badge_class' => 'badge-success'
      ],
      [
        'title' => 'Impact de l\'IA sur le recrutement en 2026',
        'excerpt' => 'Comment l\'intelligence artificielle transforme les processus de recrutement. De la présélection automatisée au matching intelligent, découvrez les outils qui redéfinissent l\'industrie RH.',
        'author' => 'Admin Aptus',
        'date' => '20 Mar. 2026',
        'time' => '15 min',
        'category' => 'IA & HR Tech',
        'badge_class' => 'badge-warning'
      ],
      [
        'title' => 'Freelancing vs CDI : Analyse du marché tunisien',
        'excerpt' => 'Le nombre de freelancers tech en Tunisie a augmenté de 45% en un an. Cette étude analyse les avantages, les risques et les revenus moyens comparés aux postes salariés traditionnels.',
        'author' => 'Admin Aptus',
        'date' => '15 Mar. 2026',
        'time' => '7 min',
        'category' => 'Emploi',
        'badge_class' => 'badge-info'
      ],
    ];
    foreach ($reports as $i => $r):
    ?>
    <article class="report-card animate-on-scroll" id="report-<?php echo $i; ?>">
      <div class="report-card__header">
        <div>
          <h3 class="report-card__title"><?php echo $r['title']; ?></h3>
          <p class="report-card__excerpt"><?php echo $r['excerpt']; ?></p>
        </div>
        <span class="badge <?php echo $r['badge_class']; ?> report-card__category"><?php echo $r['category']; ?></span>
      </div>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> <?php echo $r['author']; ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo $r['date']; ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="clock" style="width:12px;height:12px;"></i> <?php echo $r['time']; ?>
        </span>
      </div>
      <div class="report-card__footer">
        <a href="#" class="btn btn-sm btn-secondary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
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
