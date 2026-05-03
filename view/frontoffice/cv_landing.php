<?php $pageTitle = "CV Builder"; $pageCSS = "cv.css"; ?>
<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- This page is included inside layout_front.php -->

<!-- ═══════════════════════════════════════════
     CV LANDING HERO
     ═══════════════════════════════════════════ -->
<!-- ═══════════════════════════════════════════
     CV LANDING HERO (RÉVOLUTION IA)
     ═══════════════════════════════════════════ -->
<div class="cv-hero" style="padding: 5rem 2rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); overflow: hidden; position: relative; min-height: 600px; display: flex; align-items: center; border-radius: 0 0 50px 50px;">
  <!-- Background icon -->
  <div style="position:absolute; top:-50px; left:-50px; font-size:250px; color:rgba(255,255,255,0.05); transform:rotate(-15deg); pointer-events:none;"><i data-lucide="cpu"></i></div>
  
  <div class="container mx-auto flex flex-col md:flex-row items-center justify-between relative z-10" style="width: 100%; max-width: 1200px;">
    <!-- Left Content -->
    <div class="md:w-3/5 text-left">
      <div style="background: linear-gradient(90deg, #fbbf24, #f59e0b); color: #000; width: fit-content; padding: 5px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
          🚀 RÉVOLUTION IA INTÉGRÉE
      </div>
      
      <h1 style="font-size: 3.5rem; font-weight: 950; line-height: 1.1; margin-bottom: 20px; color: white; letter-spacing: -1.5px; text-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        Ne choisissez pas un design, <br><span style="color: #fbbf24;">choisissez la victoire.</span>
      </h1>
      
      <p style="color: white; opacity: 0.95; font-size: 1.15rem; margin-bottom: 2.5rem; max-width: 550px; line-height: 1.6; font-weight: 500;">
        Propulsez votre carrière avec notre suite d'outils de recrutement de nouvelle génération intégrée à chaque template.
      </p>

      <!-- Features Grid -->
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; margin-bottom: 3rem;">
          <div style="display:flex; align-items:flex-start; gap:12px; color:white;">
              <div style="background:rgba(255,255,255,0.2); padding:8px; border-radius:12px; backdrop-filter: blur(5px);"><i data-lucide="target" style="width:20px; height:20px;"></i></div>
              <div>
                  <div style="font-weight: 900; font-size: 1rem; margin-bottom: 2px;">CV Sur Mesure</div>
                  <div style="font-size: 0.8rem; opacity: 0.85; line-height: 1.3;">Réécriture automatique selon l'offre.</div>
              </div>
          </div>
          <div style="display:flex; align-items:flex-start; gap:12px; color:white;">
              <div style="background:rgba(255,255,255,0.2); padding:8px; border-radius:12px; backdrop-filter: blur(5px);"><i data-lucide="shield-check" style="width:20px; height:20px;"></i></div>
              <div>
                  <div style="font-weight: 900; font-size: 1rem; margin-bottom: 2px;">Audit ATS High-Tech</div>
                  <div style="font-size: 0.8rem; opacity: 0.85; line-height: 1.3;">Optimisation pour les algorithmes.</div>
              </div>
          </div>
          <div style="display:flex; align-items:flex-start; gap:12px; color:white;">
              <div style="background:rgba(255,255,255,0.2); padding:8px; border-radius:12px; backdrop-filter: blur(5px);"><i data-lucide="trending-up" style="width:20px; height:20px;"></i></div>
              <div>
                  <div style="font-weight: 900; font-size: 1rem; margin-bottom: 2px;">Stratégie Salariale</div>
                  <div style="font-size: 0.8rem; opacity: 0.85; line-height: 1.3;">Maximisez votre rémunération.</div>
              </div>
          </div>
          <div style="display:flex; align-items:flex-start; gap:12px; color:white;">
              <div style="background:rgba(255,255,255,0.2); padding:8px; border-radius:12px; backdrop-filter: blur(5px);"><i data-lucide="brain" style="width:20px; height:20px;"></i></div>
              <div>
                  <div style="font-weight: 900; font-size: 1rem; margin-bottom: 2px;">Psychologie d'Entretien</div>
                  <div style="font-size: 0.8rem; opacity: 0.85; line-height: 1.3;">Convainquez chaque recruteur.</div>
              </div>
          </div>
      </div>

      <div style="display:flex; flex-wrap: gap-4; gap: 15px;">
        <a href="cv_templates.php" class="btn" style="background:white; color:#4f46e5; border:none; font-weight:900; padding: 15px 40px; border-radius:18px; box-shadow: 0 15px 30px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; font-size: 1.1rem;">
          <i data-lucide="zap" style="width:22px; height:22px;"></i> Commencer Maintenant
        </a>
        <a href="cv_my.php" class="btn" style="border: 1px solid rgba(255,255,255,0.3); color:white; background:rgba(255,255,255,0.1); backdrop-filter:blur(10px); font-weight:800; padding: 15px 40px; border-radius:18px; display: flex; align-items: center; gap: 10px; font-size: 1.1rem;">
          <i data-lucide="folder" style="width:22px; height:22px;"></i> Mes CVs
        </a>
      </div>
    </div>

    <!-- Right Visual (Floating CV) -->
    <div class="md:w-2/5 relative hidden md:flex justify-end">
      <div style="position:relative; width:450px; height:450px;">
          <!-- Main Floating CV -->
          <div style="position:absolute; right:0; top:20px; width:340px; height:420px; background:white; border-radius:15px; box-shadow: 20px 40px 80px rgba(0,0,0,0.4); transform: rotate(4deg); overflow:hidden; z-index:5; border: 1px solid rgba(255,255,255,0.8);">
            <img src="assets/images/template_mockup.png" alt="CV Template" style="width:100%; height:100%; object-fit:cover; opacity: 0.95;">
          </div>
          <!-- Secondary CV -->
          <div style="position:absolute; right:100px; top:60px; width:320px; height:400px; background:rgba(255,255,255,0.8); border-radius:15px; box-shadow: 10px 20px 40px rgba(0,0,0,0.2); transform: rotate(-6deg); z-index:4; backdrop-filter: blur(5px);"></div>
          
          <!-- Floating AI Badge -->
          <div style="position:absolute; left:20px; bottom:100px; background:#fbbf24; color:black; padding:15px 25px; border-radius:25px; font-weight:950; box-shadow:0 15px 40px rgba(251, 191, 36, 0.5); z-index:10; display:flex; align-items:center; gap:10px; animation: float 3s ease-in-out infinite;">
              <i data-lucide="sparkles" style="width:24px; height:24px;"></i>
              <span>IA ACTIVE</span>
          </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     VIDEO / GIF PLACEHOLDER
     ═══════════════════════════════════════════ -->
<div class="cv-video-placeholder" id="cv-video-section">
  <div class="cv-video-placeholder__play">
    <i data-lucide="play" style="width:28px;height:28px;margin-left:3px;"></i>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     3 STEPS
     ═══════════════════════════════════════════ -->
<div class="cv-steps stagger">
  <div class="cv-step animate-on-scroll">
    <div class="cv-step__number">1</div>
    <h3 class="cv-step__title">Choisissez un Template</h3>
    <p class="cv-step__text">Parcourez notre bibliothèque de templates professionnels classés par secteur et style.</p>
  </div>
  <div class="cv-step animate-on-scroll">
    <div class="cv-step__number">2</div>
    <h3 class="cv-step__title">Personnalisez le Contenu</h3>
    <p class="cv-step__text">L'IA vous aide à formuler vos expériences et compétences pour maximiser votre impact.</p>
  </div>
  <div class="cv-step animate-on-scroll">
    <div class="cv-step__number">3</div>
    <h3 class="cv-step__title">Téléchargez & Postulez</h3>
    <p class="cv-step__text">Exportez votre CV en PDF haute qualité et postulez directement depuis la plateforme.</p>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     CV TIPS & TRUST BADGES
     ═══════════════════════════════════════════ -->
<div class="cv-tips">
  <h3 style="display:flex;align-items:center;gap:var(--space-2);">
    <i data-lucide="lightbulb" style="width:20px;height:20px;color:var(--accent-warning);"></i>
    Conseils pour un CV efficace
  </h3>
  <div class="cv-tips__grid">
    <div class="cv-tip">
      <div class="cv-tip__icon"><i data-lucide="target" style="width:18px;height:18px;"></i></div>
      <div>
        <div class="cv-tip__title">Adaptez votre CV</div>
        <div class="cv-tip__text">Personnalisez votre CV pour chaque offre d'emploi en mettant en avant les compétences pertinentes.</div>
      </div>
    </div>
    <div class="cv-tip">
      <div class="cv-tip__icon"><i data-lucide="trending-up" style="width:18px;height:18px;"></i></div>
      <div>
        <div class="cv-tip__title">Quantifiez vos résultats</div>
        <div class="cv-tip__text">Utilisez des chiffres concrets pour illustrer vos réalisations et votre impact.</div>
      </div>
    </div>
    <div class="cv-tip">
      <div class="cv-tip__icon"><i data-lucide="check-circle" style="width:18px;height:18px;"></i></div>
      <div>
        <div class="cv-tip__title">Restez concis</div>
        <div class="cv-tip__text">Un CV d'une à deux pages maximum. Privilégiez la qualité à la quantité.</div>
      </div>
    </div>
    <div class="cv-tip">
      <div class="cv-tip__icon"><i data-lucide="search" style="width:18px;height:18px;"></i></div>
      <div>
        <div class="cv-tip__title">Mots-clés ATS</div>
        <div class="cv-tip__text">Intégrez les mots-clés de l'offre d'emploi pour passer les filtres automatiques.</div>
      </div>
    </div>
  </div>

  <div class="trust-badges">
    <div class="trust-badge">
      <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
      <span>100% Gratuit</span>
    </div>
    <div class="trust-badge">
      <i data-lucide="lock" style="width:18px;height:18px;"></i>
      <span>Données sécurisées</span>
    </div>
    <div class="trust-badge">
      <i data-lucide="download" style="width:18px;height:18px;"></i>
      <span>Export PDF HD</span>
    </div>
    <div class="trust-badge">
      <i data-lucide="sparkles" style="width:18px;height:18px;"></i>
      <span>Optimisé par l'IA</span>
    </div>
  </div>
</div>
