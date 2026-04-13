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
<div class="cv-hero">
  <div class="cv-hero__content">
    <div class="hero__badge" style="display:inline-flex;">
      <i data-lucide="sparkles" style="width:16px;height:16px;"></i>
      Propulsé par l'IA
    </div>
    <h1>Créez votre CV Professionnel<br>en Quelques Minutes</h1>
    <p>Choisissez parmi nos templates premium, personnalisez avec l'assistance IA, et téléchargez votre CV prêt à l'emploi.</p>
    <a href="cv_templates.php" class="btn btn-primary btn-lg" style="background:#fff;color:var(--accent-primary);">
      <i data-lucide="rocket" style="width:18px;height:18px;"></i>
      Commencer Maintenant
    </a>
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
