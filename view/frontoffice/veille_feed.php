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
    <div class="empty-state" style="padding: var(--space-20); text-align: center; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); opacity: 0.8;">
      <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
          <i data-lucide="line-chart" style="width: 40px; height: 40px; color: var(--text-tertiary);"></i>
      </div>
      <h3 style="margin-bottom: var(--space-2);">Aucun rapport disponible</h3>
      <p style="color: var(--text-secondary);">Les analyses du marché seront publiées prochainement.</p>
    </div>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="veille-sidebar">
    <!-- Quick Stats -->
    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Offres ce mois</span>
        <span class="badge badge-neutral">0%</span>
      </div>
      <div class="data-card-mini__value">0</div>
      <div class="data-card-mini__chart" id="sparkline-offres"></div>
    </div>

    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Salaire moyen (IT)</span>
        <span class="badge badge-info">TND</span>
      </div>
      <div class="data-card-mini__value">—</div>
      <div class="data-card-mini__chart" id="sparkline-salary"></div>
    </div>

    <!-- Top Sectors Chart -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-4);">Top Secteurs</div>
      <div class="empty-state-mini" style="text-align:center; padding:var(--space-8); opacity:0.6;">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Données en attente</p>
      </div>
    </div>

    <!-- Trending Topics -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-3);">Sujets tendance</div>
      <div class="empty-state-mini" style="text-align:center; padding:var(--space-8); opacity:0.6;">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Aucune tendance détectée</p>
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
