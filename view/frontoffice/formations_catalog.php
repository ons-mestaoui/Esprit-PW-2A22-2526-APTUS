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
      <strong>0</strong> formations disponibles
    </div>

    <div class="courses-grid stagger">
      <div class="empty-state" style="grid-column: 1 / -1; padding: var(--space-20); text-align: center; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); opacity: 0.8;">
        <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6);">
            <i data-lucide="graduation-cap" style="width: 40px; height: 40px; color: var(--text-tertiary);"></i>
        </div>
        <h3 style="margin-bottom: var(--space-2);">Aucune formation</h3>
        <p style="color: var(--text-secondary);">Notre catalogue de formations est en cours de mise à jour.</p>
      </div>
    </div>
  </div>
</div>
