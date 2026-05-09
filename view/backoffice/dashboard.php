<?php $pageTitle = "Tableau de Bord"; ?>

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
      <h1>Tableau de Bord</h1>
      <p>Aperçu global de l'activité sur la plateforme Aptus.</p>
    </div>
  </div>
</div>

<!-- ═══ Stat Cards (Reference: 4 cards) ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll" id="stat-hunters">
    <div>
      <div class="stat-card__label">Job Hunters Inscrits</div>
      <div class="stat-card__value" id="counter-hunters">12,450</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +12% ce mois
      </div>
    </div>
    <div class="stat-card__icon purple">
      <i data-lucide="users" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-enterprises">
    <div>
      <div class="stat-card__label">Entreprises Partenaires</div>
      <div class="stat-card__value" id="counter-enterprises">845</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +5.2% ce mois
      </div>
    </div>
    <div class="stat-card__icon teal">
      <i data-lucide="building-2" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-cvs">
    <div>
      <div class="stat-card__label">CV Analysés par l'IA</div>
      <div class="stat-card__value" id="counter-cvs">34,102</div>
      <div class="stat-card__trend up">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +24% ce mois
      </div>
    </div>
    <div class="stat-card__icon blue">
      <i data-lucide="file-scan" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-formations">
    <div>
      <div class="stat-card__label">Formations Suivies</div>
      <div class="stat-card__value" id="counter-formations">4,520</div>
      <div class="stat-card__trend down">
        <i data-lucide="trending-down" style="width:14px;height:14px;"></i> -2.1% ce mois
      </div>
    </div>
    <div class="stat-card__icon orange">
      <i data-lucide="graduation-cap" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ Charts Row (Reference: Bar Chart + Recent Table) ═══ -->
<div class="grid" style="grid-template-columns: 1fr 380px; gap: var(--space-6); margin-bottom: var(--space-8);">

  <!-- Bar Chart: Candidatures vs Formations -->
  <div class="card">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-md fw-semibold">Candidatures vs Formations (Mensuel)</h3>
      <select class="select" style="max-width:120px;font-size:var(--fs-xs);" id="chart-filter">
        <option>6 mois</option>
        <option>12 mois</option>
        <option>Cette année</option>
      </select>
    </div>
    <div id="dashboard-bar-chart" style="height:280px;"></div>
    <div class="flex items-center justify-center gap-6 mt-4" style="font-size:var(--fs-xs);color:var(--text-secondary);">
      <span style="display:flex;align-items:center;gap:6px;">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--chart-1);"></span>
        Candidatures
      </span>
      <span style="display:flex;align-items:center;gap:6px;">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--chart-2);"></span>
        Formations
      </span>
    </div>
  </div>

  <!-- Recent Inscriptions Table -->
  <div class="card" style="overflow:hidden;padding:0;">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
      <h3 class="text-sm fw-semibold">Inscriptions Récentes</h3>
      <a href="#" class="text-sm" style="color:var(--accent-primary);font-weight:500;">Voir tout</a>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Type</th>
          <th>Date</th>
          <th>Statut IA</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:10px;">AJ</div>
              <span class="text-sm fw-medium">Alex Jenkins</span>
            </div>
          </td>
          <td><span class="badge badge-info">Job Hunter</span></td>
          <td class="text-xs text-secondary">Aujourd'hui</td>
          <td><span class="badge badge-success">● Analysé</span></td>
        </tr>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:10px;background:var(--accent-secondary);">TS</div>
              <span class="text-sm fw-medium">TechSphere Inc.</span>
            </div>
          </td>
          <td><span class="badge badge-primary">Entreprise</span></td>
          <td class="text-xs text-secondary">Hier</td>
          <td><span class="badge badge-warning">● En attente</span></td>
        </tr>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:10px;">LD</div>
              <span class="text-sm fw-medium">Laura Dubois</span>
            </div>
          </td>
          <td><span class="badge badge-info">Job Hunter</span></td>
          <td class="text-xs text-secondary">Hier</td>
          <td><span class="badge badge-success">● Analysé</span></td>
        </tr>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:10px;background:var(--accent-secondary);">DG</div>
              <span class="text-sm fw-medium">DevGroup SA</span>
            </div>
          </td>
          <td><span class="badge badge-primary">Entreprise</span></td>
          <td class="text-xs text-secondary">04 Avril</td>
          <td><span class="badge badge-success">● Vérifié</span></td>
        </tr>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:10px;">MR</div>
              <span class="text-sm fw-medium">Marie Riahi</span>
            </div>
          </td>
          <td><span class="badge badge-info">Job Hunter</span></td>
          <td class="text-xs text-secondary">03 Avril</td>
          <td><span class="badge badge-success">● Analysé</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══ Additional Charts Row ═══ -->
<div class="grid grid-2 gap-6">
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Répartition par rôle</h3>
    <div class="flex items-center justify-center" id="role-donut-chart"></div>
  </div>
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Activité hebdomadaire</h3>
    <div id="weekly-chart"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Bar Chart: Candidatures vs Formations
  AptusCharts.bar('dashboard-bar-chart', [
    { label: 'Jan', value1: 240, value2: 180, label1: 'Candidatures', label2: 'Formations' },
    { label: 'Fév', value1: 310, value2: 220, label1: 'Candidatures', label2: 'Formations' },
    { label: 'Mar', value1: 420, value2: 280, label1: 'Candidatures', label2: 'Formations' },
    { label: 'Avr', value1: 380, value2: 340, label1: 'Candidatures', label2: 'Formations' },
    { label: 'Mai', value1: 290, value2: 190, label1: 'Candidatures', label2: 'Formations' },
    { label: 'Jun', value1: 350, value2: 260, label1: 'Candidatures', label2: 'Formations' },
  ], {
    dualBars: true,
    barColor: 'var(--chart-1)',
    barColor2: 'var(--chart-2)',
    height: 280
  });

  // Donut Chart: Roles
  AptusCharts.donut('role-donut-chart', [
    { label: 'Candidats', value: 12450 },
    { label: 'Entreprises', value: 845 },
    { label: 'Admins', value: 12 },
  ], {
    size: 180,
    strokeWidth: 30,
    centerValue: '13.3k',
    centerLabel: 'Total'
  });

  // Weekly Activity
  AptusCharts.bar('weekly-chart', [
    { label: 'Lun', value: 45 },
    { label: 'Mar', value: 62 },
    { label: 'Mer', value: 78 },
    { label: 'Jeu', value: 55 },
    { label: 'Ven', value: 90 },
    { label: 'Sam', value: 34 },
    { label: 'Dim', value: 22 },
  ], { barColor: 'var(--chart-3)', height: 200 });

  // Animate counters
  AptusCharts.counter('counter-hunters', 12450);
  AptusCharts.counter('counter-enterprises', 845);
  AptusCharts.counter('counter-cvs', 34102);
  AptusCharts.counter('counter-formations', 4520);
});
</script>
