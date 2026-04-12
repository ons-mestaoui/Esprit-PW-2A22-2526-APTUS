<?php 
$pageTitle = "Templates CV"; 
$pageCSS = "cv.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Template.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

$tc = new TemplateC();
$dbTemplates = $tc->listeTemplates();
$totalAvailable = count($dbTemplates);

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
      <span class="text-sm text-secondary"><?php echo $totalAvailable; ?> templates disponibles</span>
      <div class="search-bar" style="max-width:280px;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un template..." id="template-search">
      </div>
    </div>

    <div class="cv-templates-grid stagger">
      <?php foreach ($dbTemplates as $t): 
        $tags = array_filter(array_map('trim', explode(',', $t['description'])));
        $mainTag = !empty($tags) ? $tags[0] : 'Général';
      ?>
      <div class="template-card animate-on-scroll" data-category="<?php echo htmlspecialchars($mainTag); ?>" id="template-<?php echo $t['id_template']; ?>">
        <div class="template-card__preview">
          <?php if(!empty($t['urlMiniature'])): ?>
            <img src="<?php echo htmlspecialchars($t['urlMiniature']); ?>" alt="<?php echo htmlspecialchars($t['nom']); ?>" style="width:100%; height:100%; object-fit: cover; position:absolute; inset:0;">
          <?php else: ?>
            <div class="template-card__preview-inner">
                <div class="template-card__preview-line accent"></div>
                <div class="template-card__preview-line medium"></div>
                <div class="template-card__preview-line short"></div>
                <div class="template-card__preview-block"></div>
                <div class="template-card__preview-line" style="margin-top:auto;"></div>
                <div class="template-card__preview-line medium"></div>
                <div class="template-card__preview-block"></div>
                <div class="template-card__preview-line short"></div>
            </div>
          <?php endif; ?>
          <div class="template-card__overlay">
            <a href="cv_form.php?template_id=<?php echo $t['id_template']; ?>" class="btn btn-sm" style="text-decoration:none;">
              <i data-lucide="eye" style="width:14px;height:14px;"></i>
              Utiliser ce Template
            </a>
          </div>
        </div>
        <div class="template-card__info">
          <div>
            <div class="template-card__name"><?php echo htmlspecialchars($t['nom']); ?></div>
            <div class="template-card__category"><?php echo htmlspecialchars($mainTag); ?></div>
          </div>
          <div class="flex gap-1">
            <?php if($t['estPremium']): ?>
                <span class="badge" style="background:rgba(245,158,11,0.1); color:#f59e0b; border: 1px solid rgba(245,158,11,0.2);">Premium</span>
            <?php endif; ?>
            <span class="badge badge-neutral"><?php echo htmlspecialchars($mainTag); ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <?php if($totalAvailable === 0): ?>
        <div style="grid-column: 1 / -1; text-align:center; padding: 40px; color: var(--text-tertiary);">
            <i data-lucide="layout-template" style="width:48px;height:48px;margin-bottom:16px;opacity:0.5;"></i>
            <h3>Aucun template disponible</h3>
            <p>Revenez plus tard pour découvrir nos nouveaux modèles.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php 
// End of file
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('template-search');
    const templateCards = document.querySelectorAll('.template-card');
    const categoryCheckboxes = document.querySelectorAll('.cv-sidebar__section:nth-child(1) input[type="checkbox"]');
    const styleRadios = document.querySelectorAll('.cv-sidebar__section:nth-child(2) input[type="radio"]');
    
    function filterTemplates() {
        const query = searchInput.value.toLowerCase();
        
        // Get active categories
        const activeCategories = [];
        categoryCheckboxes.forEach((cb, idx) => {
            if (cb.checked) {
                if (idx === 0) activeCategories.push('tous');
                else {
                    const label = cb.closest('.cv-sidebar__option').textContent.trim().toLowerCase();
                    activeCategories.push(label);
                }
            }
        });

        // Get active style
        let activeStyle = 'tous les styles';
        styleRadios.forEach(radio => {
            if (radio.checked) {
                activeStyle = radio.closest('.cv-sidebar__option').textContent.trim().toLowerCase();
            }
        });

        templateCards.forEach(card => {
            const name = card.querySelector('.template-card__name').textContent.toLowerCase();
            const category = card.getAttribute('data-category').toLowerCase();
            
            // Search Match
            const matchesSearch = name.includes(query);
            
            // Category Match
            const matchesCategory = activeCategories.includes('tous') || activeCategories.includes(category);
            
            // Style/Type Match (Note: actually Style is often same as Category in these projects)
            // For now we just implement the toggle logic
            const matchesStyle = activeStyle === 'tous les styles' || activeStyle.includes(category);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.3s ease forwards';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if(searchInput) searchInput.addEventListener('input', filterTemplates);
    categoryCheckboxes.forEach(cb => cb.addEventListener('change', filterTemplates));
    styleRadios.forEach(rd => rd.addEventListener('change', filterTemplates));
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
