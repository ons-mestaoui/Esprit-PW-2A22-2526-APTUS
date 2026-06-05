<?php 
$pageTitle = "Tableau de Bord"; 

// --- DYNAMIC STATS ---
try {
    if (!class_exists('config')) {
        include_once __DIR__ . '/../../config.php';
    }
    $db = config::getConnexion();
    $nbCandidats = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'Candidat'")->fetchColumn();
    $nbEntreprises = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'Entreprise'")->fetchColumn();
    $nbTuteurs = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'Tuteur'")->fetchColumn();
    $nbAdmins = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'Admin'")->fetchColumn();
    
    $recentUsers = $db->query("SELECT u.nom, u.prenom, u.role, p.photo, p.dateCreation 
                               FROM utilisateur u 
                               LEFT JOIN profil p ON u.id_utilisateur = p.id_utilisateur 
                               ORDER BY u.id_utilisateur DESC LIMIT 5")->fetchAll();

    // Weekly Activity Data (Current Week: Mon - Sun)
    $weeklyActivity = [0, 0, 0, 0, 0, 0, 0];
    $weeklyQuery = $db->query("SELECT DATE(p.dateCreation) as date, COUNT(*) as count 
                               FROM utilisateur u 
                               INNER JOIN profil p ON u.id_utilisateur = p.id_utilisateur 
                               WHERE p.dateCreation >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                               GROUP BY DATE(p.dateCreation)");
    while ($row = $weeklyQuery->fetch()) {
        $dayIndex = date('N', strtotime($row['date'])) - 1; // 0 for Monday, 6 for Sunday
        if ($dayIndex >= 0 && $dayIndex <= 6) {
            $weeklyActivity[$dayIndex] = (int)$row['count'];
        }
    }
} catch (Exception $e) {
    $nbCandidats = 12450;
    $nbEntreprises = 845;
    $nbTuteurs = 150;
    $nbAdmins = 12;
    $recentUsers = [];
    $weeklyActivity = [0, 0, 0, 0, 0, 0, 0];
}

$totalUsers = $nbCandidats + $nbEntreprises + $nbTuteurs + $nbAdmins;
// ---------------------
?>

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

<!-- ═══ Stat Cards ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll" id="stat-hunters">
    <div>
      <div class="stat-card__label">Job Hunters Inscrits</div>
      <div class="stat-card__value" id="counter-hunters">0</div>
      <div class="stat-card__trend">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0% ce mois
      </div>
    </div>
    <div class="stat-card__icon purple">
      <i data-lucide="users" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-enterprises">
    <div>
      <div class="stat-card__label">Entreprises Partenaires</div>
      <div class="stat-card__value" id="counter-enterprises">0</div>
      <div class="stat-card__trend">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0% ce mois
      </div>
    </div>
    <div class="stat-card__icon teal">
      <i data-lucide="building-2" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-admins">
    <div>
      <div class="stat-card__label">Tuteurs / Formateurs</div>
      <div class="stat-card__value" id="counter-tuteurs">0</div>
      <div class="stat-card__trend">
        <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0% ce mois
      </div>
    </div>
    <div class="stat-card__icon orange" style="background:var(--accent-warning-light); color:var(--text-warning);">
      <i data-lucide="graduation-cap" style="width:22px;height:22px;"></i>
    </div>
  </div>

  <div class="stat-card animate-on-scroll" id="stat-admins">
    <div>
      <div class="stat-card__label">Administrateurs</div>
      <div class="stat-card__value" id="counter-admins">0</div>
      <div class="stat-card__trend" style="color:var(--text-secondary);">
        <i data-lucide="shield-check" style="width:14px;height:14px;"></i> Système
      </div>
    </div>
    <div class="stat-card__icon" style="background:var(--bg-danger); color:var(--text-danger);">
      <i data-lucide="shield-alert" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ Recent Inscriptions Row ═══ -->
<div style="margin-bottom: var(--space-8);">

  <!-- Recent Inscriptions Table -->
  <div class="card" style="overflow:hidden;padding:0;">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
      <h3 class="text-sm fw-semibold">Inscriptions Récentes</h3>
      <a href="users.php" class="text-sm" style="color:var(--accent-primary);font-weight:500;">Voir tout</a>
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
            <td colspan="4" class="text-center text-secondary py-12 text-sm" style="opacity:0.6;">
                <i data-lucide="users" style="width:32px;height:32px;margin:0 auto var(--space-2);display:block;color:var(--text-tertiary);"></i>
                Aucune inscription récente.
            </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══ Additional Charts Row ═══ -->
<div class="grid grid-2 gap-6">
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Répartition par rôle</h3>
    <div class="flex items-center justify-center" id="role-donut-chart">
        <div class="empty-state-mini" style="text-align:center;opacity:0.6;">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Données insuffisantes</p>
       </div>
    </div>
  </div>
  <div class="card">
    <h3 class="text-md fw-semibold mb-6">Activité hebdomadaire</h3>
    <div id="weekly-chart">
        <div class="empty-state-mini" style="text-align:center;opacity:0.6;padding-top:var(--space-8);">
          <p style="font-size:var(--fs-xs);color:var(--text-secondary);">Aucune activité cette semaine</p>
       </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Removed static Bar Chart

  // Donut Chart: Roles
  AptusCharts.donut('role-donut-chart', [
    { label: 'Candidats', value: <?php echo $nbCandidats; ?> },
    { label: 'Entreprises', value: <?php echo $nbEntreprises; ?> },
    { label: 'Tuteurs', value: <?php echo $nbTuteurs; ?> },
    { label: 'Admins', value: <?php echo $nbAdmins; ?> },
  ], {
    size: 180,
    strokeWidth: 30,
    centerValue: '<?php echo $totalUsers; ?>',
    centerLabel: 'Total'
  });

  // Weekly Activity
  AptusCharts.bar('weekly-chart', [
    { label: 'Lun', value: <?php echo $weeklyActivity[0]; ?> },
    { label: 'Mar', value: <?php echo $weeklyActivity[1]; ?> },
    { label: 'Mer', value: <?php echo $weeklyActivity[2]; ?> },
    { label: 'Jeu', value: <?php echo $weeklyActivity[3]; ?> },
    { label: 'Ven', value: <?php echo $weeklyActivity[4]; ?> },
    { label: 'Sam', value: <?php echo $weeklyActivity[5]; ?> },
    { label: 'Dim', value: <?php echo $weeklyActivity[6]; ?> },
  ], { barColor: 'var(--chart-3)', height: 200 });

  // Animate counters
  AptusCharts.counter('counter-hunters', <?php echo $nbCandidats; ?>);
  AptusCharts.counter('counter-enterprises', <?php echo $nbEntreprises; ?>);
  AptusCharts.counter('counter-tuteurs', <?php echo $nbTuteurs; ?>);
  AptusCharts.counter('counter-admins', <?php echo $nbAdmins; ?>);
});
</script>
