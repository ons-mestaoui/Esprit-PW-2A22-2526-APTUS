<?php $pageTitle = "Mes Postes"; $pageCSS = "feeds.css"; $userRole = "Entreprise"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php (Enterprise view) -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="briefcase" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Mes Postes
  </h1>
  <p class="page-header__subtitle">Gérez vos offres d'emploi publiées</p>
</div>

<div class="hr-layout">
  <!-- ═══ MAIN GRID ═══ -->
  <div>
    <div class="results-info mb-4">
      <strong>6</strong> postes publiés
    </div>

    <div class="hr-posts-grid stagger">
      <?php
      $posts = [
        ['title' => 'Senior Full Stack Developer', 'status' => 'Actif', 'applicants' => 18, 'views' => 245, 'date' => '08 Avr.', 'badge' => 'badge-success'],
        ['title' => 'Data Engineer', 'status' => 'Actif', 'applicants' => 12, 'views' => 180, 'date' => '05 Avr.', 'badge' => 'badge-success'],
        ['title' => 'UI/UX Designer', 'status' => 'Actif', 'applicants' => 8, 'views' => 134, 'date' => '01 Avr.', 'badge' => 'badge-success'],
        ['title' => 'DevOps Engineer', 'status' => 'En pause', 'applicants' => 5, 'views' => 92, 'date' => '28 Mar.', 'badge' => 'badge-warning'],
        ['title' => 'Product Manager', 'status' => 'Actif', 'applicants' => 22, 'views' => 310, 'date' => '25 Mar.', 'badge' => 'badge-success'],
        ['title' => 'Marketing Intern', 'status' => 'Clôturé', 'applicants' => 45, 'views' => 520, 'date' => '15 Mar.', 'badge' => 'badge-neutral'],
      ];
      foreach ($posts as $i => $p):
      ?>
      <div class="hr-post-card animate-on-scroll" id="hr-post-<?php echo $i; ?>">
        <div class="hr-post-card__header">
          <span class="badge <?php echo $p['badge']; ?>"><?php echo $p['status']; ?></span>
          <button class="btn btn-sm btn-ghost"><i data-lucide="more-vertical" style="width:16px;height:16px;"></i></button>
        </div>
        <h3 class="hr-post-card__title"><?php echo $p['title']; ?></h3>
        <div class="hr-post-card__stats">
          <span class="hr-post-card__stat">
            <i data-lucide="users" style="width:14px;height:14px;"></i> <?php echo $p['applicants']; ?> candidats
          </span>
          <span class="hr-post-card__stat">
            <i data-lucide="eye" style="width:14px;height:14px;"></i> <?php echo $p['views']; ?> vues
          </span>
          <span class="hr-post-card__stat">
            <i data-lucide="calendar" style="width:14px;height:14px;"></i> <?php echo $p['date']; ?>
          </span>
        </div>
        <div class="hr-post-card__actions">
          <button class="btn btn-sm btn-secondary"><i data-lucide="pencil" style="width:14px;height:14px;"></i> Éditer</button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="users" style="width:14px;height:14px;"></i> Candidats</button>
          <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="hr-sidebar">
    <div class="hr-sidebar__section" style="text-align:center;">
      <button class="btn btn-primary btn-lg w-full" id="post-offer-btn">
        <i data-lucide="plus" style="width:18px;height:18px;"></i> Poster une offre
      </button>
    </div>

    <div class="hr-sidebar__section">
      <div class="search-bar" style="max-width:100%;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher..." id="hr-search">
      </div>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Filtrer par statut</h4>
      <label class="cv-sidebar__option"><input type="radio" name="status" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> Actif</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> En pause</label>
      <label class="cv-sidebar__option"><input type="radio" name="status"> Clôturé</label>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Résumé</h4>
      <div style="display:flex;flex-direction:column;gap:var(--space-3);">
        <div class="flex items-center justify-between">
          <span class="text-sm text-secondary">Postes actifs</span>
          <span class="fw-semibold text-sm">4</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-secondary">Total candidats</span>
          <span class="fw-semibold text-sm">110</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-secondary">Vues ce mois</span>
          <span class="fw-semibold text-sm">1,481</span>
        </div>
      </div>
    </div>
  </aside>
</div>
