<?php 
$pageTitle = "Générer CV"; 
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

<!-- ═══════════════════════════════════════════════
     HERO BANNER — Full-width gradient CTA
     ═══════════════════════════════════════════════ -->
<section class="cv-hero" id="cv-hero">
  <div class="cv-hero__bg">
    <!-- Animated dot grid -->
    <div class="cv-hero__dots"></div>
  </div>
  <div class="cv-hero__content">
    <span class="cv-hero__badge">
      <i data-lucide="sparkles" style="width:14px;height:14px;"></i>
      Propulsé par l'IA
    </span>
    <h1 class="cv-hero__title">
      Créez votre CV Professionnel<br>en Quelques Minutes
    </h1>
    <p class="cv-hero__subtitle">
      Choisissez parmi nos templates premium, personnalisez avec l'assistance IA,<br>
      et téléchargez votre CV prêt à l'emploi.
    </p>
    <div class="cv-hero__actions">
      <a href="#templates-section" class="cv-hero__cta" onclick="document.getElementById('templates-section').scrollIntoView({behavior:'smooth'});return false;">
        <i data-lucide="rocket" style="width:18px;height:18px;"></i>
        Commencer Maintenant
      </a>
      <a href="cv_my.php" class="cv-hero__cta cv-hero__cta--ghost">
        <i data-lucide="folder-open" style="width:18px;height:18px;"></i>
        Mes CVs
      </a>
    </div>

    <!-- Quick Stats -->
    <div class="cv-hero__stats">
      <div class="cv-hero__stat">
        <span class="cv-hero__stat-num"><?php echo $totalAvailable; ?></span>
        <span class="cv-hero__stat-label">Templates</span>
      </div>
      <div class="cv-hero__stat-divider"></div>
      <div class="cv-hero__stat">
        <span class="cv-hero__stat-num">100%</span>
        <span class="cv-hero__stat-label">Gratuit</span>
      </div>
      <div class="cv-hero__stat-divider"></div>
      <div class="cv-hero__stat">
        <span class="cv-hero__stat-num">PDF</span>
        <span class="cv-hero__stat-label">Export Instant</span>
      </div>
      <div class="cv-hero__stat-divider"></div>
      <div class="cv-hero__stat">
        <span class="cv-hero__stat-num">IA</span>
        <span class="cv-hero__stat-label">Polish Intégré</span>
      </div>
    </div>
  </div>

  <!-- Floating preview mockup -->
  <div class="cv-hero__mockup">
    <div class="cv-hero__mockup-card cv-hero__mockup-card--1">
      <div style="width:100%;height:8px;background:var(--accent-primary);border-radius:4px 4px 0 0;"></div>
      <div style="padding:12px;">
        <div style="width:50%;height:6px;background:#ddd;border-radius:3px;margin-bottom:8px;"></div>
        <div style="width:80%;height:4px;background:#eee;border-radius:2px;margin-bottom:5px;"></div>
        <div style="width:65%;height:4px;background:#eee;border-radius:2px;margin-bottom:10px;"></div>
        <div style="width:40%;height:5px;background:var(--accent-primary);opacity:0.3;border-radius:2px;margin-bottom:6px;"></div>
        <div style="width:90%;height:3px;background:#f0f0f0;border-radius:2px;margin-bottom:4px;"></div>
        <div style="width:70%;height:3px;background:#f0f0f0;border-radius:2px;"></div>
      </div>
    </div>
    <div class="cv-hero__mockup-card cv-hero__mockup-card--2">
      <div style="display:flex;height:100%;">
        <div style="width:35%;background:var(--accent-secondary);opacity:0.15;border-radius:4px 0 0 4px;padding:10px;">
          <div style="width:30px;height:30px;background:rgba(255,255,255,0.3);border-radius:50%;margin:0 auto 8px;"></div>
          <div style="width:80%;height:4px;background:rgba(255,255,255,0.2);border-radius:2px;margin:0 auto 4px;"></div>
          <div style="width:60%;height:3px;background:rgba(255,255,255,0.15);border-radius:2px;margin:0 auto;"></div>
        </div>
        <div style="flex:1;padding:10px;">
          <div style="width:70%;height:5px;background:#ddd;border-radius:3px;margin-bottom:8px;"></div>
          <div style="width:90%;height:3px;background:#eee;border-radius:2px;margin-bottom:4px;"></div>
          <div style="width:75%;height:3px;background:#eee;border-radius:2px;"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ Scroll-down indicator ═══ -->
<div style="text-align:center; margin: -1rem 0 2rem; opacity:0.5;">
  <i data-lucide="chevrons-down" style="width:28px;height:28px;color:var(--accent-primary);animation:bounceDown 2s infinite;"></i>
</div>

<!-- ═══════════════════════════════════════════════
     TEMPLATES SECTION
     ═══════════════════════════════════════════════ -->
<div id="templates-section">
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
</div>

<!-- ═══ HERO + TEMPLATE PAGE STYLES ═══ -->
<style>
/* Bounce animation for scroll indicator */
@keyframes bounceDown {
  0%, 100% { transform: translateY(0); opacity: 0.5; }
  50% { transform: translateY(8px); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes floatUp {
  0%, 100% { transform: translateY(0) rotate(-3deg); }
  50% { transform: translateY(-10px) rotate(-3deg); }
}

@keyframes floatUpAlt {
  0%, 100% { transform: translateY(0) rotate(2deg); }
  50% { transform: translateY(-8px) rotate(2deg); }
}

/* ── Hero Section ─────────────────────────────── */
.cv-hero {
  position: relative;
  background: var(--gradient-primary);
  border-radius: var(--radius-xl);
  padding: 4rem 3rem 3.5rem;
  margin-bottom: 2rem;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 3rem;
  min-height: 340px;
}

.cv-hero__bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.cv-hero__dots {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px);
  background-size: 20px 20px;
}

.cv-hero__content {
  position: relative;
  z-index: 2;
  flex: 1;
}

.cv-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0.4rem 1rem;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 999px;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
}

.cv-hero__title {
  font-size: 2.2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
  margin-bottom: 1rem;
  letter-spacing: -0.02em;
}

.cv-hero__subtitle {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.8);
  line-height: 1.6;
  margin-bottom: 1.75rem;
  max-width: 550px;
}

.cv-hero__actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.cv-hero__cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0.75rem 1.75rem;
  background: #fff;
  color: var(--accent-primary);
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  border: none;
  cursor: pointer;
}

.cv-hero__cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.cv-hero__cta--ghost {
  background: transparent;
  color: #fff;
  border: 1.5px solid rgba(255,255,255,0.4);
  box-shadow: none;
}

.cv-hero__cta--ghost:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.7);
}

/* Stats row */
.cv-hero__stats {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.cv-hero__stat {
  display: flex;
  flex-direction: column;
}

.cv-hero__stat-num {
  font-size: 1.3rem;
  font-weight: 800;
  color: #fff;
}

.cv-hero__stat-label {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.6);
  font-weight: 500;
}

.cv-hero__stat-divider {
  width: 1px;
  height: 30px;
  background: rgba(255,255,255,0.2);
}

/* Floating mockup cards */
.cv-hero__mockup {
  position: relative;
  z-index: 2;
  width: 250px;
  min-width: 250px;
  height: 220px;
}

.cv-hero__mockup-card {
  position: absolute;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  overflow: hidden;
}

.cv-hero__mockup-card--1 {
  width: 160px;
  height: 200px;
  top: 0;
  left: 0;
  animation: floatUp 4s ease-in-out infinite;
}

.cv-hero__mockup-card--2 {
  width: 145px;
  height: 170px;
  top: 30px;
  right: 0;
  animation: floatUpAlt 5s ease-in-out infinite;
}

/* Responsive hero */
@media (max-width: 900px) {
  .cv-hero { flex-direction: column; text-align: center; padding: 3rem 2rem; }
  .cv-hero__subtitle { margin-left: auto; margin-right: auto; }
  .cv-hero__actions { justify-content: center; }
  .cv-hero__stats { justify-content: center; }
  .cv-hero__mockup { display: none; }
  .cv-hero__title { font-size: 1.6rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set active nav
    const navCV = document.getElementById('nav-cv');
    if(navCV) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        navCV.classList.add('active');
    }

    const searchInput = document.getElementById('template-search');
    const templateCards = document.querySelectorAll('.template-card');
    const categoryCheckboxes = document.querySelectorAll('.cv-sidebar__section:nth-child(1) input[type="checkbox"]');
    const styleRadios = document.querySelectorAll('.cv-sidebar__section:nth-child(2) input[type="radio"]');
    
    function filterTemplates() {
        const query = searchInput.value.toLowerCase();
        
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

        let activeStyle = 'tous les styles';
        styleRadios.forEach(radio => {
            if (radio.checked) {
                activeStyle = radio.closest('.cv-sidebar__option').textContent.trim().toLowerCase();
            }
        });

        templateCards.forEach(card => {
            const name = card.querySelector('.template-card__name').textContent.toLowerCase();
            const category = card.getAttribute('data-category').toLowerCase();
            
            const matchesSearch = name.includes(query);
            const matchesCategory = activeCategories.includes('tous') || activeCategories.includes(category);

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
