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
    <div class="cv-hero__dots"></div>
    <div class="cv-hero__glow"></div>
  </div>
  <div class="cv-hero__content">
    <span class="cv-hero__badge">
      <i data-lucide="sparkles" style="width:14px;height:14px;"></i>
      Aptus Intelligence Évolution
    </span>
    <h1 class="cv-hero__title">
      Propulsez votre Carrière avec<br>l'IA Haute Précision</span>
    </h1>
    <p class="cv-hero__subtitle">
      Optimisez chaque mot avec notre <strong>AI Polish</strong>, adaptez votre profil au poste visé avec le <strong>CV IA Sur Mesure</strong>, et garantissez votre succès avec l'<strong>Audit ATS</strong> en temps réel.
    </p>
    <div class="cv-hero__actions">
      <a href="#templates-section" class="cv-hero__cta" onclick="document.getElementById('templates-section').scrollIntoView({behavior:'smooth'});return false;">
        <i data-lucide="rocket" style="width:18px;height:18px;"></i>
        Démarrer mon Audit IA
      </a>
      <a href="cv_my.php" class="cv-hero__cta cv-hero__cta--ghost">
        <i data-lucide="folder-open" style="width:18px;height:18px;"></i>
        Consulter mes CVs
      </a>
    </div>

    <!-- Quick Features -->
    <div class="cv-hero__stats">
      <div class="cv-hero__stat">
        <div class="cv-hero__stat-icon"><i data-lucide="target" style="width:16px;height:16px;"></i></div>
        <div>
          <span class="cv-hero__stat-num">Sur Mesure</span>
          <span class="cv-hero__stat-label">Analyse de Poste</span>
        </div>
      </div>
      <div class="cv-hero__stat-divider"></div>
      <div class="cv-hero__stat">
        <div class="cv-hero__stat-icon"><i data-lucide="wand-2" style="width:16px;height:16px;"></i></div>
        <div>
          <span class="cv-hero__stat-num">AI Polish</span>
          <span class="cv-hero__stat-label">Style & Impact</span>
        </div>
      </div>
      <div class="cv-hero__stat-divider"></div>
      <div class="cv-hero__stat">
        <div class="cv-hero__stat-icon"><i data-lucide="shield-check" style="width:16px;height:16px;"></i></div>
        <div>
          <span class="cv-hero__stat-num">ATS Ready</span>
          <span class="cv-hero__stat-label">Score de Matching</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating preview mockup -->
  <div class="cv-hero__mockup">
    <div class="cv-hero__mockup-card cv-hero__mockup-card--1">
      <div style="width:100%;height:10px;background:linear-gradient(90deg, #00A3DA, #6B34A3);border-radius:6px 6px 0 0;"></div>
      <div style="padding:15px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
           <div style="width:30px;height:30px;border-radius:50%;background:#f0f0f0;flex-shrink:0;"></div>
           <div style="flex:1;">
             <div style="width:60%;height:6px;background:#ddd;border-radius:3px;margin-bottom:4px;"></div>
             <div style="width:40%;height:4px;background:#eee;border-radius:2px;"></div>
           </div>
        </div>
        <!-- Profile summary block -->
        <div style="margin-bottom:15px;">
           <div style="width:100%;height:3px;background:#f5f5f5;border-radius:2px;margin-bottom:4px;"></div>
           <div style="width:90%;height:3px;background:#f5f5f5;border-radius:2px;margin-bottom:4px;"></div>
           <div style="width:75%;height:3px;background:#f5f5f5;border-radius:2px;"></div>
        </div>
        <!-- AI Polish highlight flash -->
        <div style="position:relative; padding:8px; background:rgba(192, 132, 252, 0.08); border:1px dashed rgba(192, 132, 252, 0.3); border-radius:6px; margin-bottom:15px;">
           <div style="width:60%;height:4px;background:#C084FC;opacity:0.6;border-radius:2px;margin-bottom:6px;"></div>
           <div style="width:40%;height:3px;background:#C084FC;opacity:0.3;border-radius:2px;"></div>
           <i data-lucide="sparkle" style="position:absolute; top:4px; right:4px; width:10px; height:10px; color:#C084FC;"></i>
        </div>
        <!-- Skills pills -->
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
           <div style="width:25%;height:12px;background:#f0f0f0;border-radius:99px;"></div>
           <div style="width:35%;height:12px;background:#f0f0f0;border-radius:99px;"></div>
           <div style="width:20%;height:12px;background:#f0f0f0;border-radius:99px;"></div>
        </div>
      </div>
    </div>
    <div class="cv-hero__mockup-card cv-hero__mockup-card--2">
      <div style="display:flex;height:100%;">
        <div style="width:35%;background:#f8fafc;padding:12px;display:flex;flex-direction:column;gap:10px;border-right:1px solid #f1f5f9;">
          <div style="width:100%;aspect-ratio:1;background:#e2e8f0;border-radius:6px;"></div>
          <div style="width:100%;height:4px;background:#e2e8f0;border-radius:2px;"></div>
          <div style="width:80%;height:4px;background:#e2e8f0;border-radius:2px;"></div>
          <div style="margin-top:auto;">
             <div style="width:100%;height:3px;background:#e2e8f0;border-radius:2px;margin-bottom:4px;"></div>
             <div style="width:60%;height:3px;background:#e2e8f0;border-radius:2px;"></div>
          </div>
        </div>
        <div style="flex:1;padding:15px;background:#fff;display:flex;flex-direction:column;">
          <div style="width:60%;height:8px;background:#1e293b;opacity:0.1;border-radius:4px;margin-bottom:10px;"></div>
          <div style="width:100%;height:4px;background:#f1f5f9;border-radius:2px;margin-bottom:6px;"></div>
          <div style="width:100%;height:4px;background:#f1f5f9;border-radius:2px;margin-bottom:6px;"></div>
          <div style="width:100%;height:4px;background:#f1f5f9;border-radius:2px;margin-bottom:15px;"></div>
          
          <!-- Experience card flash -->
          <div style="padding:10px; border-radius:8px; border:1px solid #f1f5f9; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
             <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
               <div style="width:40%;height:5px;background:#cbd5e1;border-radius:2px;"></div>
               <div style="width:20%;height:5px;background:#e2e8f0;border-radius:2px;"></div>
             </div>
             <div style="width:90%;height:3px;background:#f1f5f9;border-radius:2px;"></div>
          </div>
          
          <!-- ATS Score Badge floating -->
          <div style="margin-top:auto; align-self:flex-end; padding:6px 10px; background:linear-gradient(135deg, #f59e0b, #d946ef); border-radius:99px; font-size:9px; color:#fff; font-weight:800; box-shadow:0 4px 10px rgba(245,158,11,0.3); display:flex; align-items:center; gap:4px;">
             <i data-lucide="shield-check" style="width:10px;height:10px;"></i>
             ATS: 92% Match
          </div>
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
  <!-- ═══════════════════════════════════════════
       CV LANDING HERO (ULTIMATE IA EXPERIENCE)
       ═══════════════════════════════════════════ -->

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
          $tagsString = strtolower($t['description']);
        ?>
        <div class="template-card animate-on-scroll" data-tags="<?php echo htmlspecialchars($tagsString); ?>" data-name="<?php echo htmlspecialchars(strtolower($t['nom'])); ?>" data-id="<?php echo $t['id_template']; ?>" id="template-<?php echo $t['id_template']; ?>">
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
                  <span class="badge" style="background: linear-gradient(135deg, #f59e0b, #d946ef); color: #fff; border: none; box-shadow: 0 2px 10px rgba(245,158,11,0.4); text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">Premium</span>
              <?php endif; ?>
              <span class="badge badge-neutral" style="background: var(--bg-tertiary); color: var(--text-secondary); border: 1px solid var(--border-color);"><?php echo htmlspecialchars($mainTag); ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        
        <div id="no-template-msg" style="grid-column: 1 / -1; text-align:center; padding: 40px; color: var(--text-tertiary); display: <?php echo ($totalAvailable === 0) ? 'block' : 'none'; ?>;">
            <i data-lucide="layout-template" style="width:48px;height:48px;margin-bottom:16px;opacity:0.5;"></i>
            <h3>Aucun template disponible</h3>
            <p>Essayez de modifier vos critères de recherche.</p>
        </div>
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
  background: linear-gradient(135deg, #00A3DA 0%, #6B34A3 50%, #8D2587 100%);
  border: none;
  border-radius: var(--radius-2xl);
  padding: 5rem 4rem;
  margin-bottom: 3rem;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 4rem;
  min-height: 420px;
  box-shadow: var(--shadow-xl);
  color: #fff;
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
  background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 24px 24px;
}

.cv-hero__glow {
  position: absolute;
  top: -20%;
  right: -10%;
  width: 60%;
  height: 140%;
  background: radial-gradient(circle, rgba(192, 132, 252, 0.2) 0%, transparent 70%);
  filter: blur(60px);
}

.cv-hero__content {
  position: relative;
  z-index: 2;
  flex: 1;
}

.cv-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0.5rem 1.25rem;
  background: rgba(251, 219, 92, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(251, 219, 92, 0.2);
  border-radius: 999px;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
}

.cv-hero__title {
  font-size: 3rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.1;
  margin-bottom: 1.5rem;
  letter-spacing: -0.03em;
}

.cv-hero__subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.7;
  margin-bottom: 2.5rem;
  max-width: 600px;
}

.cv-hero__subtitle strong {
  color: #fff;
  font-weight: 700;
}

.cv-hero__actions {
  display: flex;
  gap: 1.25rem;
  margin-bottom: 3rem;
}

.cv-hero__cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 1rem 2rem;
  background: #fff;
  color: #6B34A3;
  border-radius: 16px;
  font-weight: 700;
  font-size: 1rem;
  text-decoration: none;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.cv-hero__cta:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  color: #6B34A3;
}

.cv-hero__cta--ghost {
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(5px);
}

.cv-hero__cta--ghost:hover {
  background: rgba(255,255,255,0.2);
  border-color: #fff;
}

[data-theme="dark"] .cv-hero {
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), linear-gradient(135deg, #00A3DA 0%, #6B34A3 50%, #8D2587 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}

[data-theme="dark"] .cv-hero__dots {
  opacity: 0.15;
}

[data-theme="dark"] .cv-hero__glow {
  opacity: 0.4;
}

[data-theme="dark"] .cv-hero__cta--ghost {
  background: rgba(255, 255, 255, 0.03);
}

/* Feature row */
.cv-hero__stats {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.cv-hero__stat {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cv-hero__stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(255,255,255,0.12);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cv-hero__stat-num {
  display: block;
  font-size: 0.9rem;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
}

.cv-hero__stat-label {
  display: block;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ── Mockup Dark Mode Refinement ── */
[data-theme="dark"] .cv-hero__mockup-card {
  background: #1e2235 !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

[data-theme="dark"] .cv-hero__mockup-card div {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

[data-theme="dark"] .cv-hero__mockup-card--2 > div > div:first-child {
  background: #161a2b !important;
}

[data-theme="dark"] .cv-hero__mockup-card--2 > div > div:last-child {
  background: #1e2235 !important;
}

[data-theme="dark"] .cv-hero__mockup-card [style*="background:#f0f0f0"],
[data-theme="dark"] .cv-hero__mockup-card [style*="background:#eee"],
[data-theme="dark"] .cv-hero__mockup-card [style*="background:#ddd"],
[data-theme="dark"] .cv-hero__mockup-card [style*="background:#e2e8f0"],
[data-theme="dark"] .cv-hero__mockup-card [style*="background:#f1f5f9"],
[data-theme="dark"] .cv-hero__mockup-card [style*="background:#cbd5e1"] {
  background: rgba(255, 255, 255, 0.1) !important;
}

[data-theme="dark"] .cv-hero__mockup-card [style*="background:#fff"] {
  background: #1e2235 !important;
}

[data-theme="dark"] .cv-hero__mockup-card [style*="color:#1e293b"] {
  color: #fff !important;
}


.cv-hero__stat-divider {
  width: 1px;
  height: 32px;
  background: rgba(255,255,255,0.1);
}

/* Floating mockup cards */
.cv-hero__mockup {
  position: relative;
  z-index: 2;
  width: 300px;
  min-width: 300px;
  height: 280px;
}

.cv-hero__mockup-card {
  position: absolute;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: var(--shadow-2xl);
  overflow: hidden;
  transition: all 0.3s ease;
}

.cv-hero__mockup-card--1 {
  width: 200px;
  height: 240px;
  top: 0;
  left: 0;
  animation: floatUp 6s ease-in-out infinite;
  z-index: 2;
}

.cv-hero__mockup-card--2 {
  width: 190px;
  height: 210px;
  bottom: 0;
  right: 0;
  animation: floatUpAlt 7s ease-in-out infinite;
  z-index: 1;
}

/* Responsive hero */
@media (max-width: 1100px) {
  .cv-hero { gap: 2rem; padding: 4rem 3rem; }
  .cv-hero__title { font-size: 2.2rem; }
}

@media (max-width: 950px) {
  .cv-hero { flex-direction: column; text-align: center; padding: 4rem 2rem; }
  .cv-hero__subtitle { margin-left: auto; margin-right: auto; }
  .cv-hero__actions { justify-content: center; }
  .cv-hero__stats { justify-content: center; flex-wrap: wrap; }
  .cv-hero__mockup { display: none; }
}

@media (max-width: 480px) {
  .cv-hero__actions { flex-direction: column; width: 100%; }
  .cv-hero__cta { width: 100%; justify-content: center; }
  .cv-hero__stats { gap: 1rem; }
  .cv-hero__stat-divider { display: none; }
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
    const templateCards = Array.from(document.querySelectorAll('.template-card'));
    const categoryCheckboxes = document.querySelectorAll('.cv-sidebar__section:nth-child(1) input[type="checkbox"]');
    const styleRadios = document.querySelectorAll('.cv-sidebar__section:nth-child(2) input[type="radio"]');
    const sortRadios = document.querySelectorAll('.cv-sidebar__section:nth-child(3) input[type="radio"]');
    const grid = document.querySelector('.cv-templates-grid');
    const noTemplateMsg = document.getElementById('no-template-msg');

    // Handle "Tous" logic for categories
    const chkTous = categoryCheckboxes[0];
    categoryCheckboxes.forEach((cb, idx) => {
        cb.addEventListener('change', function() {
            if (idx === 0 && this.checked) {
                categoryCheckboxes.forEach((c, i) => { if (i !== 0) c.checked = false; });
            } else if (idx !== 0 && this.checked) {
                chkTous.checked = false;
            } else if (idx !== 0 && !this.checked) {
                let anyChecked = false;
                categoryCheckboxes.forEach((c, i) => { if (i !== 0 && c.checked) anyChecked = true; });
                if (!anyChecked) chkTous.checked = true;
            }
            filterTemplates();
        });
    });

    function filterTemplates() {
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        
        const activeCategories = [];
        if (!chkTous.checked) {
            categoryCheckboxes.forEach((cb, idx) => {
                if (cb.checked && idx > 0) {
                    activeCategories.push(cb.closest('label').textContent.trim().toLowerCase());
                }
            });
        }

        let activeStyle = 'tous les styles';
        styleRadios.forEach(radio => {
            if (radio.checked) {
                activeStyle = radio.closest('label').textContent.trim().toLowerCase();
            }
        });

        let activeSort = 'plus populaires';
        sortRadios.forEach(radio => {
            if (radio.checked) {
                activeSort = radio.closest('label').textContent.trim().toLowerCase();
            }
        });

        let visibleCount = 0;

        templateCards.forEach(card => {
            const name = card.getAttribute('data-name');
            const tags = card.getAttribute('data-tags');
            
            const matchesSearch = query === '' || name.includes(query) || tags.includes(query);
            
            let matchesCategory = chkTous.checked || activeCategories.length === 0;
            if (!matchesCategory) {
                matchesCategory = activeCategories.some(cat => tags.includes(cat));
            }

            let matchesStyle = activeStyle === 'tous les styles' || tags.includes(activeStyle);

            if (matchesSearch && matchesCategory && matchesStyle) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.3s ease forwards';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Sorting
        const visibleCards = templateCards.filter(card => card.style.display === 'block');
        visibleCards.sort((a, b) => {
            if (activeSort === 'nom (a-z)') {
                return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
            } else {
                // Plus récents / Plus populaires (Use ID descending by default)
                return parseInt(b.getAttribute('data-id')) - parseInt(a.getAttribute('data-id'));
            }
        });

        // Reorder DOM
        visibleCards.forEach(card => grid.appendChild(card));
        if(noTemplateMsg) noTemplateMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    if(searchInput) searchInput.addEventListener('input', filterTemplates);
    styleRadios.forEach(rd => rd.addEventListener('change', filterTemplates));
    sortRadios.forEach(rd => rd.addEventListener('change', filterTemplates));
});
</script>
