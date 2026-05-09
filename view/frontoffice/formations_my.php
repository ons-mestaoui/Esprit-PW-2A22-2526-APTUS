<?php $pageTitle = "Mes Cours"; $pageCSS = "formations.css"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="book-open" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mes Cours
      </h1>
      <p class="page-header__subtitle">Suivez votre progression et obtenez vos certificats</p>
    </div>
    <a href="formations_catalog.php" class="btn btn-primary">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Explorer le catalogue
    </a>
  </div>
</div>

<!-- ═══ Gamification Stats ═══ -->
<div class="gamification-stats stagger">
  <div class="gamification-stat animate-on-scroll">
    <div class="gamification-stat__icon" style="background:var(--stat-purple-bg);color:var(--stat-purple);">
      <i data-lucide="trophy" style="width:24px;height:24px;"></i>
    </div>
    <div class="gamification-stat__value">3</div>
    <div class="gamification-stat__label">Cours Complétés</div>
  </div>
  <div class="gamification-stat animate-on-scroll">
    <div class="gamification-stat__icon" style="background:var(--stat-teal-bg);color:var(--stat-teal);">
      <i data-lucide="zap" style="width:24px;height:24px;"></i>
    </div>
    <div class="gamification-stat__value">1,240</div>
    <div class="gamification-stat__label">XP Gagnés</div>
  </div>
  <div class="gamification-stat animate-on-scroll">
    <div class="gamification-stat__icon" style="background:var(--stat-orange-bg);color:var(--stat-orange);">
      <i data-lucide="flame" style="width:24px;height:24px;"></i>
    </div>
    <div class="gamification-stat__value">12</div>
    <div class="gamification-stat__label">Jours de Streak</div>
  </div>
</div>

<!-- ═══ My Courses Grid ═══ -->
<div class="my-courses-grid stagger">
  <?php
  $myCourses = [
    ['title' => 'React.js Avancé : Hooks, Context & Performance', 'tutor' => 'Ahmed Ben Ali', 'progress' => 100, 'mode' => 'online', 'url' => 'https://meet.aptus.ai/react-advanced'],
    ['title' => 'Introduction à Python & Data Science', 'tutor' => 'Sara Khediri', 'progress' => 72, 'mode' => 'online', 'url' => 'https://meet.aptus.ai/python-ds'],
    ['title' => 'UI/UX Design : De Figma au Prototype', 'tutor' => 'Nour Maalej', 'progress' => 45, 'mode' => 'presentiel', 'url' => null],
    ['title' => 'Cybersécurité Fondamentale', 'tutor' => 'Youssef Hamdi', 'progress' => 100, 'mode' => 'online', 'url' => 'https://meet.aptus.ai/cybersec'],
    ['title' => 'Docker & Kubernetes en Production', 'tutor' => 'Ahmed Ben Ali', 'progress' => 18, 'mode' => 'online', 'url' => 'https://meet.aptus.ai/docker-k8s'],
    ['title' => 'Machine Learning avec TensorFlow', 'tutor' => 'Mohamed Dridi', 'progress' => 100, 'mode' => 'presentiel', 'url' => null],
  ];
  foreach ($myCourses as $i => $mc):
    $progressColor = $mc['progress'] === 100 ? 'var(--accent-secondary)' : 'var(--accent-primary)';
  ?>
  <div class="my-course-card animate-on-scroll" id="my-course-<?php echo $i; ?>">
    <div class="my-course-card__header">
      <div class="my-course-card__thumb">
        <i data-lucide="book-open" style="width:20px;height:20px;color:rgba(255,255,255,0.6);"></i>
      </div>
      <div>
        <h3 class="my-course-card__title"><?php echo $mc['title']; ?></h3>
        <div class="my-course-card__tutor">
          <i data-lucide="user" style="width:12px;height:12px;display:inline;vertical-align:-1px;"></i> <?php echo $mc['tutor']; ?>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="course-progress">
      <div class="course-progress__info">
        <span class="course-progress__label">Progression</span>
        <span class="course-progress__value"><?php echo $mc['progress']; ?>%</span>
      </div>
      <div class="progress-bar">
        <div class="progress-bar__fill" style="width:<?php echo $mc['progress']; ?>%;background:<?php echo ($mc['progress'] === 100) ? 'var(--accent-secondary)' : 'var(--gradient-primary)'; ?>;"></div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2">
      <?php if ($mc['progress'] === 100): ?>
        <a href="certificate.php" class="btn btn-sm btn-success">
          <i data-lucide="award" style="width:14px;height:14px;"></i> Générer Certificat
        </a>
      <?php elseif ($mc['mode'] === 'online' && $mc['url']): ?>
        <a href="<?php echo $mc['url']; ?>" class="btn btn-sm btn-primary" target="_blank">
          <i data-lucide="video" style="width:14px;height:14px;"></i> Rejoindre la Room
        </a>
      <?php endif; ?>
      <button class="btn btn-sm btn-secondary">
        <i data-lucide="play" style="width:14px;height:14px;"></i> Continuer
      </button>
      <span class="badge <?php echo $mc['mode'] === 'online' ? 'badge-info' : 'badge-warning'; ?>" style="margin-left:auto;align-self:center;">
        <?php echo $mc['mode'] === 'online' ? 'En ligne' : 'Présentiel'; ?>
      </span>
    </div>
  </div>
  <?php endforeach; ?>
</div>
