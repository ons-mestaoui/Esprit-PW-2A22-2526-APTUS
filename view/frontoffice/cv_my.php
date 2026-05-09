<?php $pageTitle = "Mes CVs"; $pageCSS = "cv.css"; ?>

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
        <i data-lucide="file-text" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mes CVs
      </h1>
      <p class="page-header__subtitle">Gérez et téléchargez vos CVs générés</p>
    </div>
    <a href="cv_templates.php" class="btn btn-primary">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Créer un nouveau CV
    </a>
  </div>
</div>

<!-- ═══ Stats Summary ═══ -->
<div class="grid grid-3 gap-6 mb-8">
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Total CVs</div>
      <div class="stat-card__value">4</div>
    </div>
    <div class="stat-card__icon purple">
      <i data-lucide="file-text" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Téléchargements</div>
      <div class="stat-card__value">12</div>
    </div>
    <div class="stat-card__icon teal">
      <i data-lucide="download" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Dernier modifié</div>
      <div class="stat-card__value text-sm fw-semibold" style="font-size:var(--fs-sm);">Aujourd'hui</div>
    </div>
    <div class="stat-card__icon blue">
      <i data-lucide="clock" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ CV Cards Grid ═══ -->
<div class="my-cvs-grid stagger">
  <?php
  $cvs = [
    ['title' => 'CV Développeur Full Stack', 'template' => 'Tech Stack', 'date' => '10 Avr. 2026', 'color' => '#3B82F6'],
    ['title' => 'CV Data Analyst', 'template' => 'Data Analyst', 'date' => '08 Avr. 2026', 'color' => '#8B5CF6'],
    ['title' => 'CV Chef de Projet', 'template' => 'Executive Pro', 'date' => '01 Avr. 2026', 'color' => '#6C5CE7'],
    ['title' => 'CV Stage Marketing', 'template' => 'Marketing Pro', 'date' => '25 Mar. 2026', 'color' => '#EC4899'],
  ];
  foreach ($cvs as $i => $cv):
  ?>
  <div class="my-cv-card animate-on-scroll" id="my-cv-<?php echo $i; ?>">
    <div class="my-cv-card__preview">
      <div class="template-card__preview-inner" style="width:60%;height:75%;">
        <div class="template-card__preview-line accent" style="background:<?php echo $cv['color']; ?>;"></div>
        <div class="template-card__preview-line medium"></div>
        <div class="template-card__preview-line short"></div>
        <div class="template-card__preview-block"></div>
        <div class="template-card__preview-line" style="margin-top:auto;"></div>
        <div class="template-card__preview-line medium"></div>
      </div>
    </div>
    <div class="my-cv-card__body">
      <div class="my-cv-card__title"><?php echo $cv['title']; ?></div>
      <div class="my-cv-card__date">
        <i data-lucide="clock" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i>
        Modifié le <?php echo $cv['date']; ?> — Template: <?php echo $cv['template']; ?>
      </div>
      <div class="my-cv-card__actions">
        <button class="btn btn-sm btn-secondary">
          <i data-lucide="pencil" style="width:14px;height:14px;"></i> Éditer
        </button>
        <button class="btn btn-sm btn-primary">
          <i data-lucide="download" style="width:14px;height:14px;"></i> PDF
        </button>
        <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);">
          <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ═══ Empty State (hidden when CVs exist) ═══ -->
<!--
<div class="empty-state">
  <div class="empty-state__icon">
    <i data-lucide="file-plus" style="width:36px;height:36px;"></i>
  </div>
  <h3 class="empty-state__title">Aucun CV créé</h3>
  <p class="empty-state__text">Commencez par choisir un template et créez votre premier CV professionnel.</p>
  <a href="cv_templates.php" class="btn btn-primary">
    <i data-lucide="plus" style="width:18px;height:18px;"></i>
    Créer mon premier CV
  </a>
</div>
-->
