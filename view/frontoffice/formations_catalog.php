<?php $pageTitle = "Formations"; $pageCSS = "formations.css"; ?>

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
    <i data-lucide="graduation-cap" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Catalogue des Formations
  </h1>
  <p class="page-header__subtitle">Développez vos compétences avec nos formations certifiantes</p>
</div>

<!-- Top Search -->
<div class="filter-bar mb-6">
  <div class="search-bar" style="flex:1;max-width:400px;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" placeholder="Rechercher une formation..." id="formation-search">
  </div>
  <select class="select" style="max-width:160px;">
    <option>Tous les domaines</option>
    <option>Développement</option>
    <option>Data Science</option>
    <option>Design</option>
    <option>Marketing</option>
    <option>Cybersécurité</option>
  </select>
  <select class="select" style="max-width:140px;">
    <option>Plus récent</option>
    <option>Plus populaire</option>
    <option>Nom (A-Z)</option>
  </select>
</div>

<div class="formations-layout">
  <!-- ═══ SIDEBAR FILTERS ═══ -->
  <aside class="cv-sidebar" style="position:sticky;top:calc(var(--topbar-height) + var(--space-4));align-self:start;">
    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="layers" style="width:16px;height:16px;"></i>
        Domaine
      </div>
      <label class="cv-sidebar__option"><input type="checkbox" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Développement Web</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Data Science</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Design UI/UX</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Cybersécurité</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Marketing Digital</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Cloud & DevOps</label>
    </div>

    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="signal" style="width:16px;height:16px;"></i>
        Niveau
      </div>
      <label class="cv-sidebar__option"><input type="checkbox" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Débutant</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Intermédiaire</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Avancé</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Expert</label>
    </div>

    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
        Lieu
      </div>
      <label class="cv-sidebar__option"><input type="radio" name="lieu" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="radio" name="lieu"> En ligne</label>
      <label class="cv-sidebar__option"><input type="radio" name="lieu"> Présentiel</label>
    </div>
  </aside>

  <!-- ═══ COURSE CARDS GRID ═══ -->
  <div>
    <div class="results-info mb-4">
      <strong>12</strong> formations disponibles
    </div>

    <div class="courses-grid stagger">
      <?php
      $courses = [
        ['title' => 'React.js Avancé : Hooks, Context & Performance', 'level' => 'Avancé', 'level_class' => 'advanced', 'tutor' => 'Ahmed Ben Ali', 'domain' => 'Développement', 'students' => 245, 'duration' => '24h'],
        ['title' => 'Introduction à Python & Data Science', 'level' => 'Débutant', 'level_class' => 'beginner', 'tutor' => 'Sara Khediri', 'domain' => 'Data Science', 'students' => 890, 'duration' => '32h'],
        ['title' => 'UI/UX Design : De Figma au Prototype', 'level' => 'Intermédiaire', 'level_class' => 'intermediate', 'tutor' => 'Nour Maalej', 'domain' => 'Design', 'students' => 312, 'duration' => '18h'],
        ['title' => 'Cybersécurité Fondamentale', 'level' => 'Débutant', 'level_class' => 'beginner', 'tutor' => 'Youssef Hamdi', 'domain' => 'Cybersécurité', 'students' => 178, 'duration' => '20h'],
        ['title' => 'Machine Learning avec TensorFlow', 'level' => 'Expert', 'level_class' => 'expert', 'tutor' => 'Mohamed Dridi', 'domain' => 'Data Science', 'students' => 156, 'duration' => '40h'],
        ['title' => 'Marketing Digital & SEO', 'level' => 'Intermédiaire', 'level_class' => 'intermediate', 'tutor' => 'Fatma Jelassi', 'domain' => 'Marketing', 'students' => 423, 'duration' => '16h'],
        ['title' => 'Docker & Kubernetes en Production', 'level' => 'Avancé', 'level_class' => 'advanced', 'tutor' => 'Ahmed Ben Ali', 'domain' => 'Cloud & DevOps', 'students' => 198, 'duration' => '28h'],
        ['title' => 'JavaScript ES2025 Masterclass', 'level' => 'Intermédiaire', 'level_class' => 'intermediate', 'tutor' => 'Sara Khediri', 'domain' => 'Développement', 'students' => 567, 'duration' => '22h'],
        ['title' => 'Node.js Backend Development', 'level' => 'Avancé', 'level_class' => 'advanced', 'tutor' => 'Youssef Hamdi', 'domain' => 'Développement', 'students' => 334, 'duration' => '30h'],
      ];
      foreach ($courses as $i => $c):
      ?>
      <div class="course-card animate-on-scroll" id="course-<?php echo $i; ?>">
        <div class="course-card__image">
          <div class="course-card__image-placeholder" style="animation-delay:<?php echo $i * 0.5; ?>s;">
            <i data-lucide="graduation-cap" style="width:40px;height:40px;"></i>
          </div>
          <span class="course-card__level <?php echo $c['level_class']; ?>"><?php echo $c['level']; ?></span>
        </div>
        <div class="course-card__body">
          <h3 class="course-card__title"><?php echo $c['title']; ?></h3>
          <div class="course-card__meta">
            <span class="course-card__meta-item"><i data-lucide="users" style="width:12px;height:12px;"></i> <?php echo $c['students']; ?></span>
            <span class="course-card__meta-item"><i data-lucide="clock" style="width:12px;height:12px;"></i> <?php echo $c['duration']; ?></span>
            <span class="badge badge-neutral"><?php echo $c['domain']; ?></span>
          </div>
          <div class="course-card__tutor">
            <div class="avatar avatar-sm avatar-initials" style="width:24px;height:24px;font-size:10px;"><?php echo strtoupper(substr($c['tutor'], 0, 1) . substr(strstr($c['tutor'], ' '), 1, 1)); ?></div>
            <?php echo $c['tutor']; ?>
          </div>
          <div class="course-card__footer">
            <span class="badge badge-primary"><?php echo $c['domain']; ?></span>
            <button class="btn btn-sm btn-primary">
              <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
