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
      <span class="text-sm text-secondary">0 templates disponibles</span>
      <div class="search-bar" style="max-width:280px;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un template..." id="template-search">
      </div>
    </div>

    <div class="cv-templates-grid stagger">
      <div class="empty-state" style="grid-column: 1 / -1; padding: var(--space-20); text-align: center; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); opacity: 0.8; width: 100%;">
        <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
            <i data-lucide="layout-template" style="width: 40px; height: 40px; color: var(--text-tertiary);"></i>
        </div>
        <h3 style="margin-bottom: var(--space-2);">Aucun template</h3>
        <p style="color: var(--text-secondary);">De nouveaux templates de CV seront ajoutés très bientôt.</p>
      </div>
    </div>
  </div>
</div>
