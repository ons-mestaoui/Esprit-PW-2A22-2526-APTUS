<?php
require_once 'layout_front.php';
$pageTitle = "Le Blog Insider - Aptus";
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
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body class="landing-page-body">
  
  <div id="cursor-aura"></div>

  <!-- NAVIGATION -->
  <?php echo getNavElements(); ?>

  <main class="page-content" style="padding-top: 100px;">
      
      <!-- HERO BLOG -->
      <section class="section-hero text-center py-6 reveal-up">
          <div class="container">
              <div class="section-tag accent-teal mx-auto mb-3"><i data-lucide="newspaper" class="me-2"></i>Le Blog Insider</div>
              <h1 class="accent-font text-high-contrast" style="font-size: 3.5rem; font-weight: 900;">
                  Intelligence & <span class="text-teal text-gradient-teal">Tendances</span>.
              </h1>
              <p class="text-muted max-w-2xl mx-auto text-lg mt-3">
                  Articles de fond, décryptage des tendances du recrutement par l'IA et analyses exclusives de la communauté Aptus. Plongez dans la veille stratégique.
              </p>
          </div>
      </section>

      <!-- FEATURED ARTICLE -->
      <section class="section-featured pb-5 reveal-up delay-200">
          <div class="container">
              <a href="#" class="text-decoration-none">
                  <div class="glass-panel p-3 p-md-4 rounded-2xl mx-auto hover-extend-glow transition-all d-flex flex-column flex-md-row gap-4 align-items-center text-high-contrast" style="max-width: 1000px;" data-tilt data-tilt-max="2" data-tilt-glare="true" data-tilt-max-glare="0.1">
                      <div class="featured-img rounded-xl bg-teal overflow-hidden flex-shrink-0" style="width: 100%; max-width: 450px; height: 300px; position:relative;">
                           <div class="position-absolute w-100 h-100 bg-teal-light opacity-50 blur-sm z-1"></div>
                           <div class="position-absolute top-50 start-50 translate-middle z-2">
                               <i data-lucide="bot" class="text-white opacity-75" style="width: 80px; height: 80px;"></i>
                           </div>
                      </div>
                      <div class="featured-content p-3">
                          <span class="badge bg-teal-light text-teal fw-bold mb-3">Tendance 2026</span>
                          <h2 class="accent-font mb-3">Comment l'IA prédictive transforme le tri des CV en moins de 3 secondes</h2>
                          <p class="text-muted mb-4 d-none d-md-block">Les ATS traditionnels cherchent des mots-clés, l'IA Aptus cherche du sens. Découvrez comment les modèles de langage redéfinissent la sélection des talents et éliminent les biais cognitifs.</p>
                          <div class="d-flex align-items-center gap-3 mt-auto">
                              <div class="avatar bg-light-subtle rounded-circle border border-muted" style="width:40px;height:40px;"></div>
                              <div>
                                  <div class="fw-bold text-sm">Équipe R&D Aptus</div>
                                  <div class="text-xs text-muted">Il y a 2 jours • 5 min de lecture</div>
                              </div>
                          </div>
                      </div>
                  </div>
              </a>
          </div>
      </section>

      <!-- ARTICLES GRID -->
      <section class="section-articles py-5">
          <div class="container">
              <div class="d-flex justify-content-between align-items-center mb-5 reveal-up">
                  <h3 class="accent-font text-high-contrast m-0">Dernières publications</h3>
                  <div class="d-flex gap-2">
                      <select class="form-select bg-transparent text-muted border-muted rounded-pill text-sm" style="width: auto;">
                          <option>Tous les sujets</option>
                          <option>Astuces Candidat</option>
                          <option>Stratégies RH</option>
                          <option>Tech & IA</option>
                      </select>
                  </div>
              </div>

              <div class="grid-3 gap-4">
                  <!-- Article 1 -->
                  <div class="article-card reveal-up delay-100">
                      <a href="#" class="text-decoration-none">
                          <div class="glass-panel p-3 rounded-xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast">
                              <div class="article-img rounded-lg bg-purple-light mb-3 position-relative" style="height:200px;">
                                   <i data-lucide="trending-up" class="position-absolute top-50 start-50 translate-middle text-purple opacity-50" style="width: 48px; height: 48px;"></i>
                              </div>
                              <div class="article-meta d-flex justify-content-between mb-2">
                                  <span class="text-xs text-purple fw-bold text-uppercase">Stratégies RH</span>
                                  <span class="text-xs text-muted">12 Avril 2026</span>
                              </div>
                              <h5 class="accent-font mb-2">Les 5 KPIs du recrutement qui comptent vraiment cette année</h5>
                              <p class="text-sm text-muted mb-4 flex-grow-1">Oubliez le Time-to-Fill. Concentrez-vous sur la Qualité d'Embauche et le Taux de Rétention prédictif.</p>
                              <div class="text-purple text-sm fw-bold d-flex align-items-center gap-1 mt-auto">Lire l'article <i data-lucide="arrow-right" style="width:14px;"></i></div>
                          </div>
                      </a>
                  </div>

                  <!-- Article 2 -->
                  <div class="article-card reveal-up delay-200">
                      <a href="#" class="text-decoration-none">
                          <div class="glass-panel p-3 rounded-xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast">
                              <div class="article-img rounded-lg bg-orange-light mb-3 position-relative" style="height:200px;">
                                   <i data-lucide="pen-tool" class="position-absolute top-50 start-50 translate-middle text-orange opacity-50" style="width: 48px; height: 48px;"></i>
                              </div>
                              <div class="article-meta d-flex justify-content-between mb-2">
                                  <span class="text-xs text-orange fw-bold text-uppercase">Astuces Candidat</span>
                                  <span class="text-xs text-muted">08 Avril 2026</span>
                              </div>
                              <h5 class="accent-font mb-2">Comment optimiser votre portfolio pour les algorithmes sans perdre votre âme</h5>
                              <p class="text-sm text-muted mb-4 flex-grow-1">Le guide ultime pour marier l'aspect créatif d'un portfolio au balisage SEO technique attendu par les recruteurs 2.0.</p>
                              <div class="text-orange text-sm fw-bold d-flex align-items-center gap-1 mt-auto">Lire l'article <i data-lucide="arrow-right" style="width:14px;"></i></div>
                          </div>
                      </a>
                  </div>

                  <!-- Article 3 -->
                  <div class="article-card reveal-up delay-300">
                      <a href="#" class="text-decoration-none">
                          <div class="glass-panel p-3 rounded-xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast">
                              <div class="article-img rounded-lg bg-blue-light mb-3 position-relative" style="height:200px;">
                                   <i data-lucide="network" class="position-absolute top-50 start-50 translate-middle text-blue opacity-50" style="width: 48px; height: 48px;"></i>
                              </div>
                              <div class="article-meta d-flex justify-content-between mb-2">
                                  <span class="text-xs text-blue fw-bold text-uppercase">Tech & IA</span>
                                  <span class="text-xs text-muted">05 Avril 2026</span>
                              </div>
                              <h5 class="accent-font mb-2">Sous le capot : l'architecture de matching Aptus révélée</h5>
                              <p class="text-sm text-muted mb-4 flex-grow-1">Une plongée technique dans l'infrastructure qui permet d'analyser des dizaines de milliers de points de données instantanément.</p>
                              <div class="text-blue text-sm fw-bold d-flex align-items-center gap-1 mt-auto">Lire l'article <i data-lucide="arrow-right" style="width:14px;"></i></div>
                          </div>
                      </a>
                  </div>
              </div>

              <div class="text-center mt-5 reveal-up delay-400">
                  <a href="#" class="btn btn-ghost btn-lg magnetic-btn">Charger plus d'articles</a>
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
  </script>

</body>
</html>
