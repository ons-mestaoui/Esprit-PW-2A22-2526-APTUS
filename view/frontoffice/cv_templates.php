<?php $pageTitle = "Templates CV"; $pageCSS = "cv.css"; ?>

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
    <i data-lucide="layout-template" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Templates CV
  </h1>
  <p class="page-header__subtitle">Choisissez votre template et créez un CV professionnel en quelques clics</p>
</div>

<div class="cv-gallery-layout">
  <!-- ═══ SIDEBAR FILTERS ═══ -->
  <aside class="cv-sidebar">
    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="filter" style="width:16px;height:16px;"></i>
        Catégorie
      </div>
      <label class="cv-sidebar__option"><input type="checkbox" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Technologie</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Design</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Business</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Marketing</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Santé</label>
    </div>

    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="palette" style="width:16px;height:16px;"></i>
        Style
      </div>
      <label class="cv-sidebar__option"><input type="radio" name="style" checked> Tous les styles</label>
      <label class="cv-sidebar__option"><input type="radio" name="style"> Classique</label>
      <label class="cv-sidebar__option"><input type="radio" name="style"> Moderne</label>
      <label class="cv-sidebar__option"><input type="radio" name="style"> Créatif</label>
      <label class="cv-sidebar__option"><input type="radio" name="style"> Minimaliste</label>
    </div>

    <div class="cv-sidebar__section">
      <div class="cv-sidebar__title">
        <i data-lucide="arrow-up-down" style="width:16px;height:16px;"></i>
        Trier par
      </div>
      <label class="cv-sidebar__option"><input type="radio" name="sort" checked> Plus populaires</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort"> Plus récents</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort"> Nom (A-Z)</label>
    </div>
  </aside>

  <!-- ═══ TEMPLATE GRID ═══ -->
  <div>
    <div class="flex items-center justify-between mb-6">
      <span class="text-sm text-secondary">12 templates disponibles</span>
      <div class="search-bar" style="max-width:280px;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un template..." id="template-search">
      </div>
    </div>

    <div class="cv-templates-grid stagger">
      <?php
      $templates = [
        ['name' => 'Executive Pro', 'category' => 'Business', 'color' => '#6C5CE7'],
        ['name' => 'Tech Stack', 'category' => 'Technologie', 'color' => '#3B82F6'],
        ['name' => 'Créatif Bold', 'category' => 'Design', 'color' => '#F59E0B'],
        ['name' => 'Minimaliste', 'category' => 'Minimaliste', 'color' => '#6B7280'],
        ['name' => 'Modern Flow', 'category' => 'Moderne', 'color' => '#00B894'],
        ['name' => 'Data Analyst', 'category' => 'Technologie', 'color' => '#8B5CF6'],
        ['name' => 'Marketing Pro', 'category' => 'Marketing', 'color' => '#EC4899'],
        ['name' => 'Classique Elite', 'category' => 'Classique', 'color' => '#1F2937'],
        ['name' => 'Santé Expert', 'category' => 'Santé', 'color' => '#059669'],
        ['name' => 'Freelancer', 'category' => 'Créatif', 'color' => '#F97316'],
        ['name' => 'Corporate', 'category' => 'Business', 'color' => '#4338CA'],
        ['name' => 'Designer UX', 'category' => 'Design', 'color' => '#D946EF'],
      ];
      foreach ($templates as $i => $t):
      ?>
      <div class="template-card animate-on-scroll" data-category="<?php echo $t['category']; ?>" id="template-<?php echo $i; ?>">
        <div class="template-card__preview">
          <div class="template-card__preview-inner">
            <div class="template-card__preview-line accent" style="background:<?php echo $t['color']; ?>;"></div>
            <div class="template-card__preview-line medium"></div>
            <div class="template-card__preview-line short"></div>
            <div class="template-card__preview-block"></div>
            <div class="template-card__preview-line" style="margin-top:auto;"></div>
            <div class="template-card__preview-line medium"></div>
            <div class="template-card__preview-block"></div>
            <div class="template-card__preview-line short"></div>
          </div>
          <div class="template-card__overlay">
            <button class="btn btn-sm">
              <i data-lucide="eye" style="width:14px;height:14px;"></i>
              Utiliser ce Template
            </button>
          </div>
        </div>
        <div class="template-card__info">
          <div>
            <div class="template-card__name"><?php echo $t['name']; ?></div>
            <div class="template-card__category"><?php echo $t['category']; ?></div>
          </div>
          <span class="badge badge-neutral"><?php echo $t['category']; ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
