<?php $pageTitle = "Candidatures"; $pageCSS = "feeds.css"; $userRole = "Entreprise"; ?>

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
    <i data-lucide="users" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Candidatures Reçues
  </h1>
  <p class="page-header__subtitle">Examinez et triez les candidatures pour vos postes</p>
</div>

<div class="hr-layout">
  <!-- ═══ CANDIDATE CARDS ═══ -->
  <div>
    <div class="results-info mb-4">
      <strong>18</strong> candidatures pour "Senior Full Stack Developer"
    </div>

    <div class="candidate-cards-grid stagger">
      <?php
      $candidates = [
        ['name' => 'Amine Belloumi', 'role' => 'Full Stack Developer', 'match' => 92, 'skills' => ['React', 'Node.js', 'TypeScript', 'PostgreSQL'], 'initials' => 'AB', 'applied' => 'Il y a 2h'],
        ['name' => 'Sara Khediri', 'role' => 'Frontend Developer', 'match' => 87, 'skills' => ['Vue.js', 'CSS', 'Figma', 'JavaScript'], 'initials' => 'SK', 'applied' => 'Il y a 5h'],
        ['name' => 'Mohamed Dridi', 'role' => 'Backend Developer', 'match' => 84, 'skills' => ['Python', 'Django', 'PostgreSQL', 'Docker'], 'initials' => 'MD', 'applied' => 'Il y a 1j'],
        ['name' => 'Fatma Jelassi', 'role' => 'Software Engineer', 'match' => 81, 'skills' => ['Java', 'Spring Boot', 'Microservices'], 'initials' => 'FJ', 'applied' => 'Il y a 1j'],
        ['name' => 'Youssef Hamdi', 'role' => 'Full Stack Developer', 'match' => 78, 'skills' => ['React', 'Express', 'MongoDB', 'AWS'], 'initials' => 'YH', 'applied' => 'Il y a 2j'],
        ['name' => 'Nour Maalej', 'role' => 'DevOps & Full Stack', 'match' => 75, 'skills' => ['Node.js', 'Docker', 'Kubernetes', 'CI/CD'], 'initials' => 'NM', 'applied' => 'Il y a 3j'],
      ];
      foreach ($candidates as $i => $c):
      ?>
      <div class="candidate-card animate-on-scroll" id="candidate-<?php echo $i; ?>">
        <div class="candidate-card__header">
          <div class="avatar avatar-lg avatar-initials"><?php echo $c['initials']; ?></div>
          <div class="candidate-card__info">
            <div class="candidate-card__name"><?php echo $c['name']; ?></div>
            <div class="candidate-card__role"><?php echo $c['role']; ?></div>
            <span class="text-xs text-tertiary"><?php echo $c['applied']; ?></span>
          </div>
          <div class="candidate-card__match">
            <?php echo $c['match']; ?>%
            <span>match</span>
          </div>
        </div>

        <div class="candidate-card__skills">
          <?php foreach ($c['skills'] as $skill): ?>
            <span class="badge badge-primary"><?php echo $skill; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="candidate-card__actions">
          <button class="btn btn-sm btn-primary"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Voir CV</button>
          <button class="btn btn-sm btn-success"><i data-lucide="check" style="width:14px;height:14px;"></i> Shortlist</button>
          <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"><i data-lucide="x" style="width:14px;height:14px;"></i> Refuser</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="hr-sidebar">
    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Filtrer pour le poste</h4>
      <select class="select w-full mb-3" id="filter-post">
        <option>Senior Full Stack Developer</option>
        <option>Data Engineer</option>
        <option>UI/UX Designer</option>
        <option>Product Manager</option>
      </select>
    </div>

    <div class="hr-sidebar__section">
      <div class="search-bar" style="max-width:100%;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un candidat..." id="candidate-search">
      </div>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Trier par</h4>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand" checked> Match % (desc)</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand"> Date (récent)</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand"> Nom (A-Z)</label>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Statut</h4>
      <label class="cv-sidebar__option"><input type="checkbox" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Shortlisté</label>
      <label class="cv-sidebar__option"><input type="checkbox"> En attente</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Refusé</label>
    </div>
  </aside>
</div>
