<?php
require_once 'layout_front.php';
$pageTitle = "Guide Aptus - Explorez la magie";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/layout_front.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/landing_dynamic.css">
  <!-- Load theme early to prevent flash -->
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body class="landing-page-body">
  
  <!-- Interactive Cursor Aura -->
  <div id="cursor-aura"></div>

  <!-- NAVIGATION -->
  <?php echo getNavElements(); ?>

  <main class="page-content" style="padding-top: 100px;">
      
      <!-- HERO DOCUMENTATION -->
      <section class="section-hero text-center py-6 reveal-up">
          <div class="container">
              <div class="section-tag accent-purple mx-auto mb-3"><i data-lucide="sparkles" class="me-2"></i>Guide & Découverte</div>
              <h1 class="accent-font text-high-contrast" style="font-size: 3.5rem; font-weight: 900;">
                  Un aperçu du <span class="text-purple text-gradient-purple">Futur</span>.
              </h1>
              <p class="text-muted max-w-lg mx-auto text-lg mt-3">
                  Aptus est plus qu'une plateforme. C'est votre accélérateur de carrière propulsé par l'IA. 
                  Voici un avant-goût de ce qui vous attend à l'intérieur...
              </p>
          </div>
      </section>

      <!-- PROMO VIDEO PLACEHOLDER -->
      <section class="section-video py-4 reveal-up delay-200">
          <div class="container">
              <div class="glass-panel p-2 rounded-2xl mx-auto shadow-xl hover-extend-glow transition-all" style="max-width: 900px;" data-tilt data-tilt-max="2" data-tilt-glare="true" data-tilt-max-glare="0.1">
                  <!-- L'utilisateur insérera sa balise vidéo ici -->
                  <div class="video-placeholder rounded-xl bg-dark d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 500px;">
                      <!-- Faux bouton play pour le design -->
                      <div class="play-btn rounded-circle bg-white text-dark d-flex align-items-center justify-content-center shadow-lg" style="width: 80px; height: 80px; z-index: 2; cursor: pointer;">
                          <i data-lucide="play" style="width: 36px; height: 36px; margin-left: 5px;"></i>
                      </div>
                      
                      <!-- Overlay de suspense -->
                      <div class="position-absolute w-100 h-100" style="background: radial-gradient(circle, rgba(124, 58, 237, 0.4) 0%, rgba(0,0,0,0.8) 100%);"></div>
                      <h4 class="position-absolute bottom-0 start-50 translate-middle-x mb-4 text-white accent-font opacity-75 text-center w-100 text-uppercase tracking-wide" style="letter-spacing: 3px;">
                          [ Insérez votre vidéo promotionnelle ici ]
                      </h4>
                  </div>
              </div>
          </div>
      </section>

      <!-- TEASER GIFS / FLASHES (SUSPENSE) -->
      <section class="section-teasers py-6">
          <div class="container">
              <div class="text-center mb-5 reveal-up">
                  <h2 class="accent-font text-high-contrast mb-3">Aperçu Exclusif</h2>
                  <p class="text-muted">Découvrez en un flash la magie du CV Builder et du Matching IA. Inscrivez-vous pour tout débloquer.</p>
              </div>

              <div class="grid-2 gap-5">
                  <!-- Teaser 1: CV Builder -->
                  <div class="teaser-card reveal-left">
                      <div class="glass-panel p-2 rounded-2xl mb-4 hover-extend-glow hover-lift transition-all position-relative overflow-hidden cursor-pointer">
                          <!-- Image/Gif floutée pour le suspense -->
                          <div class="teaser-visual rounded-xl bg-dark position-relative" style="height: 280px; overflow: hidden;">
                                <div class="position-absolute w-100 h-100 bg-purple-light opacity-50 blur-md z-1"></div>
                                <div class="position-absolute top-50 start-50 translate-middle z-2 text-center w-100">
                                    <i data-lucide="wand-2" class="text-purple mb-2" style="width: 48px; height: 48px;"></i>
                                    <h5 class="text-white accent-font m-0">Builder IA en action</h5>
                                    <span class="badge bg-purple text-white mt-2">Classé Confidentiel</span>
                                </div>
                          </div>
                      </div>
                      <h4 class="accent-font text-high-contrast mb-2">Conception Intelligente</h4>
                      <p class="text-muted">Donnez vos expériences brutes, l'IA les sublime instantanément en phrases d'accroche percutantes. Rapide, sans effort, et visuellement parfait.</p>
                  </div>

                  <!-- Teaser 2: Matching -->
                  <div class="teaser-card reveal-right delay-200">
                      <div class="glass-panel p-2 rounded-2xl mb-4 hover-extend-glow hover-lift transition-all position-relative overflow-hidden cursor-pointer">
                          <!-- Image/Gif floutée pour le suspense -->
                          <div class="teaser-visual rounded-xl bg-dark position-relative" style="height: 280px; overflow: hidden;">
                                <div class="position-absolute w-100 h-100 bg-teal-light opacity-50 blur-md z-1"></div>
                                <div class="position-absolute top-50 start-50 translate-middle z-2 text-center w-100">
                                    <i data-lucide="zap" class="text-teal mb-2" style="width: 48px; height: 48px;"></i>
                                    <h5 class="text-white accent-font m-0">Le Matching Prédictif</h5>
                                    <span class="badge bg-teal text-white mt-2">Top Secret</span>
                                </div>
                          </div>
                      </div>
                      <h4 class="accent-font text-high-contrast mb-2">Adieu les recherches interminables</h4>
                      <p class="text-muted">Vous naviguez comme sur un réseau social. L'algorithme calcule vos probabilités d'embauche et vous sert uniquement les offres qui vous correspondent à 90%+.</p>
                  </div>
              </div>
              
              <div class="text-center mt-6 py-4 reveal-up delay-300">
                  <h3 class="accent-font text-high-contrast mb-4">La curiosité est un vilain défaut... sauf ici.</h3>
                  <a href="login.php?panel=signup" class="btn btn-lg btn-primary glow-btn splash-hover px-5">
                      Franchir le pas <i data-lucide="arrow-right" class="ms-2"></i>
                  </a>
              </div>
          </div>
      </section>

  </main>

  <!-- FOOTER -->
  <footer class="front-footer landing-footer py-5 mt-0 section-dark border-top border-dark-subtle">
    <div class="container text-center">
        <a href="landing.php" class="topnav__logo nav-anchor d-inline-flex align-items-center gap-2 mb-3 text-decoration-none">
            <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="width:32px;">
            <span class="gradient-text accent-font h4 m-0">Aptus</span>
        </a>
        <p class="text-muted text-sm d-block mx-auto max-w-sm mb-0">&copy; <?php echo date('Y'); ?> Aptus. Façonné avec passion.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  
  <script>
    lucide.createIcons();
    // Re-trigger intersection observers manually for this page if needed
  </script>

</body>
</html>
