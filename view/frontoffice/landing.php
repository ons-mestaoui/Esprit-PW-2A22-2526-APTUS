<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Aptus — Plateforme intelligente de recrutement et d'apprentissage propulsée par l'IA.">
  <title>Aptus — Trouvez Votre Prochaine Opportunité</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;700;800;900&display=swap" rel="stylesheet">
  
  <!-- CSS -->
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/layout_front.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/landing_dynamic.css">
  
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body class="landing-page-body">
  
  <!-- Interactive Cursor Aura -->
  <div id="cursor-aura"></div>

  <!-- ==========================================
       LANDING NAVIGATION
       ========================================== -->
  <nav class="landing-nav glass-nav" id="landing-nav">
    <a href="#hero" class="landing-nav__logo nav-anchor">
      <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="landing-nav__logo-icon">
      <span class="gradient-text accent-font">Aptus</span>
    </a>
    <div class="landing-nav__links">
      <a href="#showcase-jobs" class="nav-anchor">Matching IA</a>
      <a href="#showcase-cv" class="nav-anchor">CV Builder</a>
      <a href="#showcase-formations" class="nav-anchor">Formations & XP</a>
      <a href="#resources" class="nav-anchor">Ressources</a>
    </div>
    <div class="landing-nav__actions">
      <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>
      <a href="login.php" class="btn btn-ghost nav-btn-login">Se connecter</a>
      <a href="signup_choice.php" class="btn btn-primary glow-btn">S'inscrire</a>
    </div>
    <button class="hamburger-landing" id="hamburger-landing" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Menu -->
  <div class="mobile-menu-landing" id="mobile-menu-landing">
      <a href="#showcase-jobs" class="nav-anchor">Matching IA</a>
      <a href="#showcase-cv" class="nav-anchor">CV Builder</a>
      <a href="#showcase-formations" class="nav-anchor">Formations & XP</a>
      <a href="#resources" class="nav-anchor">Ressources</a>
      <div class="mobile-menu-actions mt-4">
        <a href="login.php" class="btn btn-ghost w-100 mb-2">Se connecter</a>
        <a href="signup_choice.php" class="btn btn-primary w-100">S'inscrire</a>
      </div>
  </div>

  <!-- ==========================================
       HERO SECTION 
       ========================================== -->
  <section class="hero-dynamic" id="hero">
    <div class="hero-bg-animated">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="grid-overlay"></div>
    </div>
    
    <div class="hero-container">
        <div class="hero__content reveal-on-scroll">
          <div class="hero-badge pulse-badge">
            <i data-lucide="sparkles"></i>
            Propulsé par l'Intelligence Artificielle
          </div>
          <h1 class="hero__title accent-font text-high-contrast">
            Défiez les règles du <br>
            <span class="text-gradient-animate typewrite text-high-contrast" data-period="2000" data-type='[ "Recrutement", "Succès", "Talent" ]'>Recrutement</span>
          </h1>
          <p class="hero__subtitle text-high-contrast opacity-75">
            Aptus connecte instantanément les meilleurs talents et les entreprises visionnaires grâce à un matching prédictif, un générateur de CV intelligent et un parcours d'apprentissage gamifié.
          </p>
          <div class="hero__ctas mt-5">
            <a href="signup_choice.php" class="btn btn-primary btn-lg glow-btn splash-hover magnetic-btn">
              <i data-lucide="rocket"></i> S'inscrire
            </a>
            <a href="#showcase-jobs" class="btn btn-ghost btn-lg nav-anchor">
              Découvrir <i data-lucide="arrow-down" class="bounce ms-2"></i>
            </a>
          </div>

          <!-- Dynamic Stats -->
          <div class="hero__stats hero-stats-glass mt-5 text-high-contrast mb-4">
            <div class="hero__stat"><div class="hero__stat-value counter accent-font" data-target="12450">0</div><div class="hero__stat-label">Offres</div></div>
            <div class="hero__stat"><div class="hero__stat-value counter accent-font" data-target="845">0</div><div class="hero__stat-label">Entreprises</div></div>
            <div class="hero__stat"><div class="hero__stat-value counter accent-font" data-target="34102">0</div><div class="hero__stat-label">CVs créés</div></div>
            <div class="hero__stat"><div class="hero__stat-value accent-font" id="stat-rate">94%</div><div class="hero__stat-label">Satisfaction</div></div>
          </div>
        </div>

        <!-- Hero Visual: Neurons / Nodes Concept -->
        <div class="hero-visual">
          <div class="hero-nodes-container flex-column" data-tilt data-tilt-max="5" data-tilt-speed="400">
              <canvas id="neurons-canvas" class="neurons-canvas"></canvas>
              
              <div class="central-logo-node glow-btn">
                  <img src="/aptus_first_official_version/view/assets/img/logo sans bg.png" alt="Aptus" style="width: 70px; height: auto; z-index: 2;">
              </div>

              <!-- Floating UI Cards -->
              <div class="float-card sleek-card pos-top-left floating-anim-1">
                  <div class="sleek-card-icon"><i data-lucide="sparkles"></i></div>
                  <div class="sleek-card-content">
                      <div class="sc-title text-high-contrast">Analyse IA Terminée</div>
                      <div class="sc-desc text-high-contrast">Profil optimal à 98%</div>
                  </div>
              </div>
              
              <div class="float-card sleek-card pos-bottom-right floating-anim-2" style="animation-delay: -3s;">
                  <div class="sleek-card-icon badge-primary"><i data-lucide="briefcase"></i></div>
                  <div class="sleek-card-content">
                      <div class="sc-title text-high-contrast">Nouvelle Opportunité</div>
                      <div class="sc-desc text-high-contrast">5 offres correspondantes</div>
                  </div>
              </div>
          </div>
        </div>
    </div>
  </section>

  <!-- ==========================================
       SHOWCASE : MATCHING IA (JOBS)
       ========================================== -->
  <section class="section-showcase" id="showcase-jobs">
    <div class="showcase-container">
      <div class="showcase-text reveal-left">
        <div class="section-tag accent-teal">MatchMaking Intelligent</div>
        <h2 class="section-title accent-font">Ne cherchez plus. <br>Laissez l'IA vous <span class="text-teal text-gradient-teal">trouver</span>.</h2>
        <p class="section-desc text-muted">Notre algorithme exclusif analyse vos compétences en profondeur et les confronte à des milliers d'offres en temps réel. Finies les candidatures à l'aveugle, place à la précision chirurgicale.</p>
        <ul class="feature-list mt-4">
          <li><div class="feature-icon bg-teal-light"><i data-lucide="scan-line" class="text-teal"></i></div> <div><strong class="text-high-contrast">Analyse sémantique</strong><br><span class="text-sm text-muted">Compréhension profonde des compétences</span></div></li>
          <li><div class="feature-icon bg-teal-light"><i data-lucide="percent" class="text-teal"></i></div> <div><strong class="text-high-contrast">Score de compatibilité</strong><br><span class="text-sm text-muted">Match affiché instantanément</span></div></li>
        </ul>
        <a href="signup_choice.php" class="btn btn-outline-teal mt-4 rounded-pill">Explorer les offres</a>
      </div>
      <div class="showcase-visual reveal-right">
        <div class="mockup-card glass-panel tilt-card text-high-contrast" data-tilt data-tilt-perspective="1000" data-tilt-max="10" data-tilt-speed="400">
          <div class="mockup-header border-bottom">
            <div class="d-flex gap-2">
                <div class="mockup-dot red"></div><div class="mockup-dot yellow"></div><div class="mockup-dot green"></div>
            </div>
          </div>
          <div class="mockup-body mockup-jobs bg-texture">
            <div class="job-mini-card animate-slide-up bg-high-light" style="animation-delay: 0.1s;">
               <div class="jmc-logo bg-dark text-white"><i data-lucide="triangle"></i></div>
               <div class="jmc-info"><div class="jmc-title text-high-contrast">Lead Data Scientist</div><div class="jmc-company text-muted text-xs">TechSphere · Tunis</div></div>
               <div class="jmc-score badge-green">98% Match</div>
            </div>
            <div class="job-mini-card animate-slide-up bg-high-light" style="animation-delay: 0.3s;">
               <div class="jmc-logo bg-primary text-white"><i data-lucide="circle"></i></div>
               <div class="jmc-info"><div class="jmc-title text-high-contrast">Développeur React</div><div class="jmc-company text-muted text-xs">InnoLab · Remote</div></div>
               <div class="jmc-score badge-green">92% Match</div>
            </div>
            <div class="job-mini-card opacity-60 animate-slide-up bg-high-light" style="animation-delay: 0.5s;">
               <div class="jmc-logo bg-teal text-white"><i data-lucide="hexagon"></i></div>
               <div class="jmc-info"><div class="jmc-title text-high-contrast">UX/UI Designer</div><div class="jmc-company text-muted text-xs">Creative Studio</div></div>
               <div class="jmc-score badge-yellow">75% Match</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================
       SHOWCASE : CV BUILDER
       ========================================== -->
  <section class="section-showcase showcase-reverse bg-gradient-subtle" id="showcase-cv">
    <div class="showcase-container">
      <div class="showcase-text reveal-right">
        <div class="section-tag accent-purple">Générateur de CV Pro</div>
        <h2 class="section-title accent-font">Concevez un profil <span class="text-purple text-gradient-purple">exceptionnel</span>.</h2>
        <p class="section-desc text-muted">Créez un CV qui attire l'attention en quelques minutes. L'intelligence artificielle vous guide pas à pas pour mettre en valeur vos compétences de manière claire et professionnelle, et décrocher plus d'entretiens sans le moindre effort.</p>
        <ul class="feature-list mt-4">
          <li><div class="feature-icon bg-purple-light"><i data-lucide="wand-2" class="text-purple"></i></div> <div><strong class="text-high-contrast">Rédaction Assistée</strong><br><span class="text-sm text-muted">Phrases d'accroche générées par l'IA</span></div></li>
          <li><div class="feature-icon bg-purple-light"><i data-lucide="layout-template" class="text-purple"></i></div> <div><strong class="text-high-contrast">Templates Premium</strong><br><span class="text-sm text-muted">Designs modernes et percutants</span></div></li>
        </ul>
        <a href="signup_choice.php" class="btn btn-outline-purple mt-4 rounded-pill">Créer mon CV</a>
      </div>
      <div class="showcase-visual reveal-left relative">
        <div class="mockup-cv-wrapper floating-slow" data-tilt data-tilt-perspective="1000" data-tilt-max="10">
           <div class="cv-paper glass-card bg-white shadow-xl text-high-contrast">
             <div class="cv-header skeleton-box w-30 h-10 mb-4 rounded"></div>
             <div class="d-flex gap-3 mb-4">
                 <div class="skeleton-box w-20 h-20 rounded-circle"></div>
                 <div class="w-100">
                     <div class="skeleton-box w-50 h-4 mb-2 rounded"></div>
                     <div class="skeleton-box w-70 h-3 rounded"></div>
                 </div>
             </div>
             <div class="cv-lines">
                 <div class="skeleton-box w-100 h-3 mb-2 rounded"></div>
                 <div class="skeleton-box w-90 h-3 mb-2 rounded"></div>
                 <div class="skeleton-box w-80 h-3 mb-4 rounded"></div>
             </div>
             <!-- AI Hover popup -->
             <div class="ai-suggestion-popup bounce-subtle">
                 <i data-lucide="sparkles" class="text-purple"></i>
                 <span>L'IA a optimisé cette section !</span>
             </div>
           </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================
       SHOWCASE : FORMATIONS & LEADERBOARD
       ========================================== -->
  <section class="section-showcase" id="showcase-formations">
    <div class="showcase-container">
      <div class="showcase-text reveal-left">
        <div class="section-tag accent-blue">Gamification & Apprentissage</div>
        <h2 class="section-title accent-font">Évoluez, Gagnez de l'XP et <span class="text-blue text-gradient-blue">dominez</span>.</h2>
        <p class="section-desc text-muted">La formation continue réinventée. Suivez nos cours certifiants, montez en niveau, affrontez la communauté et dévoilez votre expertise aux recruteurs.</p>
        <div class="gamification-features mt-4 grid-2 offset-hover">
           <a href="signup_choice.php" class="text-decoration-none">
           <div class="gf-item glass-panel p-3 rounded-xl border border-blue-subtle hover-extend-glow hover-lift text-high-contrast">
             <div class="gf-icon mb-2"><i data-lucide="graduation-cap" class="text-blue" style="width:32px;height:32px;"></i></div>
             <div class="h5 mb-1 accent-font">Catalogue de Cours</div>
             <div class="text-sm text-muted">Découvrez nos formations.</div>
           </div>
           </a>
           <a href="signup_choice.php" class="text-decoration-none">
           <div class="gf-item glass-panel p-3 rounded-xl border border-blue-subtle hover-extend-glow hover-lift text-high-contrast" id="showcase-leaderboard">
             <div class="gf-icon mb-2"><i data-lucide="trophy" class="text-blue" style="width:32px;height:32px;"></i></div>
             <div class="h5 mb-1 accent-font">Leaderboard</div>
             <div class="text-sm text-muted">Classement des membres.</div>
           </div>
           </a>
        </div>
      </div>
      <div class="showcase-visual reveal-right">
         <div class="courses-grid-mockup">
            <div class="c-mockup-card glass-card p-3 mb-3 floating-anim-1 border-left-blue text-high-contrast hover-extend-glow" data-tilt data-tilt-max="10">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs text-muted fw-bold text-uppercase">Développement Web</span>
                    <span class="badge-xp bg-blue-light text-blue fw-bold rounded px-2 py-1 text-xs">+150 XP</span>
                </div>
                <h4 class="mb-1 text-md">Mastering React 18</h4>
                <div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-blue" style="width:75%; background:#2563eb;"></div></div>
            </div>
            
            <div class="leaderboard-aesthetic glass-panel p-4 mt-4 shadow-xl mx-auto rounded-xl hover-extend-glow hover-lift" style="max-width:85%;" data-tilt data-tilt-max="5">
                <div class="d-flex align-items-center gap-2 mb-4">
                     <div class="icon-glow bg-blue-light"><i data-lucide="award" class="text-blue"></i></div>
                     <h5 class="m-0 accent-font text-high-contrast">Classement XP</h5>
                </div>
                <div class="lb-item d-flex align-items-center justify-content-between p-2 rounded mb-2 hover-bg-subtle transition-all">
                    <div class="d-flex align-items-center gap-3">
                         <div class="rank-badge primary-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm">1</div>
                         <div class="text-sm fw-bold text-high-contrast">Développeur Fullstack</div>
                    </div>
                    <div class="xp-score text-blue fw-bolder">8400 <span class="text-xs opacity-50">XP</span></div>
                </div>
                <div class="lb-item d-flex align-items-center justify-content-between p-2 rounded hover-bg-subtle transition-all">
                    <div class="d-flex align-items-center gap-3">
                         <div class="rank-badge secondary-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm">2</div>
                         <div class="text-sm fw-bold text-high-contrast">Analyste de Données</div>
                    </div>
                    <div class="xp-score opacity-75 fw-bold text-high-contrast">7950 <span class="text-xs">XP</span></div>
                </div>
            </div>
         </div>
      </div>
    </div>
  </section>

  <!-- ==========================================
       RESOURCES SECTION
       ========================================== -->
  <section class="section-resources py-6" id="resources">
    <div class="resources-wrapper container">
        <div class="resources-header text-center reveal-up mb-5">
          <div class="section-tag accent-blue mx-auto mb-3">Écosystème</div>
          <h2 class="section-title accent-font">Une galaxie de <span class="text-blue text-gradient-blue">Ressources</span></h2>
          <p class="section-desc mx-auto text-muted max-w-lg">Tout ce dont vous avez besoin pour briller : documentation, actualités et support.</p>
        </div>
        
        <div class="resources-grid grid-3 reveal-up delay-200 gap-4">
          
          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5" data-tilt-glare="true" data-tilt-max-glare="0.2">
            <div class="resource-icon bg-blue-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="book-open" class="text-blue" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Documentation</h3>
            <p class="text-muted text-sm mb-4">Découvrez toutes les astuces et méthodes pour maîtriser la plateforme.</p>
            <a href="#doc-details" class="resource-link text-blue fw-bold d-inline-flex align-items-center gap-1 hover-arrow nav-anchor">Lire la Doc <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>

          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5" data-tilt-glare="true" data-tilt-max-glare="0.2">
            <div class="resource-icon bg-purple-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="edit-3" class="text-purple" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Le Blog Insider</h3>
            <p class="text-muted text-sm mb-4">Tendances du recrutement IA et articles de veille exclusifs de notre communauté.</p>
            <a href="#blog-details" class="resource-link text-purple fw-bold d-inline-flex align-items-center gap-1 hover-arrow nav-anchor">Découvrir le blog <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>

          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5" data-tilt-glare="true" data-tilt-max-glare="0.2">
            <div class="resource-icon bg-green-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="life-buoy" class="text-green" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Centre de Support</h3>
            <p class="text-muted text-sm mb-4">Notre équipe dévouée est là pour vous assister à tout moment.</p>
            <a href="#team-archipel" class="nav-anchor resource-link text-green fw-bold d-inline-flex align-items-center gap-1 hover-arrow">Contacter l'équipe <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>

        </div>
    </div>
  </section>

  <section class="section-doc-details py-6" id="doc-details" style="position:relative; z-index:10;">
    <div class="container">
      <!-- 1. Header -->
      <div class="text-center mb-10 reveal-up">
        <div class="section-tag accent-purple mx-auto mb-3">Guide & Découverte</div>
        <h2 class="section-title accent-font">Un univers de <span class="text-purple text-gradient-purple">Possibilités</span></h2>
        <p class="section-desc mx-auto text-muted max-w-lg">Plongez dans les détails techniques et fonctionnels qui font d'Aptus l'outil de recrutement le plus avancé du marché.</p>
      </div>

      <!-- 2. Video Section (Centralized) -->
      <div class="video-central-wrapper d-flex justify-content-center mb-10 pb-5 reveal-up">
          <div class="glass-panel p-2 rounded-2xl shadow-xl hover-extend-glow transition-all" style="width: 100%; max-width: 900px; border: 1px solid var(--glass-border); overflow: hidden;">
              <div class="video-inner rounded-xl bg-dark position-relative overflow-hidden" style="padding-top: 56.25%;">
                  <video class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;" controls muted loop>
                      <source src="/aptus_first_official_version/view/assets/video/IMG_E8245_1.mp4" type="video/mp4">
                      Votre navigateur ne supporte pas la balise vidéo.
                  </video>
              </div>
              <p class="text-center mt-3 text-sm text-muted accent-font text-uppercase tracking-widest opacity-75 mb-2">
                  <i data-lucide="play-circle" class="me-1" style="width:14px;"></i> Démo Interactive Aptus
              </p>
          </div>
      </div>

      <!-- 3. Key Features Row -->
      <div class="features-flex-container mb-10 pb-5 reveal-up">
          <div class="text-center mb-10">
            <h3 class="accent-font text-high-contrast mb-3">Fonctionnalités Clés</h3>
            <p class="text-muted text-sm">Des outils conçus pour l'efficacité et la clarté</p>
          </div>
          <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 35px;">
              <div class="func-item glass-panel p-5 rounded-2xl hover-lift shadow-lg position-relative" style="flex: 1; min-width: 300px; max-width: 350px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                <div class="icon-box-premium mb-4 d-inline-flex align-items-center justify-content-center rounded-xl" style="width: 60px; height: 60px; background: var(--grad-purple); box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);">
                    <i data-lucide="brain-circuit" class="text-white" style="width:30px;height:30px;"></i>
                </div>
                <h4 class="accent-font h5 fw-bold mb-3">Matching Prédictif</h4>
                <p class="text-sm text-muted lh-lg">Algorithme propriétaire qui analyse vos compétences pour dénicher les opportunités invisibles à l'œil nu.</p>
              </div>
              <div class="func-item glass-panel p-5 rounded-2xl hover-lift shadow-lg position-relative" style="flex: 1; min-width: 300px; max-width: 350px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                <div class="icon-box-premium mb-4 d-inline-flex align-items-center justify-content-center rounded-xl" style="width: 60px; height: 60px; background: var(--grad-teal); box-shadow: 0 10px 20px rgba(13, 148, 136, 0.3);">
                    <i data-lucide="file-json" class="text-white" style="width:30px;height:30px;"></i>
                </div>
                <h4 class="accent-font h5 fw-bold mb-3">Générateur de CV</h4>
                <p class="text-sm text-muted lh-lg">Optimisez chaque mot de votre parcours avec notre IA de rédaction pour passer tous les filtres ATS.</p>
              </div>
              <div class="func-item glass-panel p-5 rounded-2xl hover-lift shadow-lg position-relative" style="flex: 1; min-width: 300px; max-width: 350px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                <div class="icon-box-premium mb-4 d-inline-flex align-items-center justify-content-center rounded-xl" style="width: 60px; height: 60px; background: var(--grad-orange); box-shadow: 0 10px 20px rgba(234, 88, 12, 0.3);">
                    <i data-lucide="graduation-cap" class="text-white" style="width:30px;height:30px;"></i>
                </div>
                <h4 class="accent-font h5 fw-bold mb-3">Système Certifiant</h4>
                <p class="text-sm text-muted lh-lg">Validez vos acquis avec des badges officiels et une progression gamifiée reconnue par les recruteurs.</p>
              </div>
          </div>
      </div>

      <!-- 4. Deep Insights Row -->
      <div class="insights-flex-container py-5 reveal-up">
          <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 40px;">
              <div class="insight-card" style="flex: 1; min-width: 340px; max-width: 520px;">
                  <div class="glass-panel p-3 rounded-3xl mb-4 hover-extend-glow hover-lift transition-all position-relative overflow-hidden cursor-pointer" data-tilt data-tilt-max="3" style="border: 1px solid var(--glass-border);">
                      <div class="teaser-visual rounded-2xl bg-dark position-relative" style="height: 350px; overflow: hidden;">
                            <div class="position-absolute w-100 h-100 bg-purple-light opacity-30 blur-xl z-1"></div>
                            <div class="position-absolute top-50 start-50 translate-middle z-2 text-center w-100 px-4">
                                <div class="mb-4 d-inline-flex p-3 rounded-circle bg-white shadow-lg" style="background: rgba(255,255,255,0.1) !important; backdrop-filter: blur(10px);">
                                    <i data-lucide="wand-2" class="text-purple" style="width: 48px; height: 48px;"></i>
                                </div>
                                <h3 class="text-white accent-font fw-bold m-0 mb-3 h2">CV Builder IA</h3>
                                <span class="badge bg-purple text-white px-4 py-2 rounded-pill fs-xs text-uppercase tracking-tighter">Accès Anticipé</span>
                            </div>
                      </div>
                  </div>
                  <div class="px-2">
                    <h4 class="accent-font text-high-contrast h5 fw-bold mb-3">Conception Intelligente</h4>
                    <p class="text-muted small lh-relaxed">Notre assistant analyse le marché du travail en temps réel pour suggérer les compétences les plus recherchées dans votre domaine.</p>
                  </div>
              </div>

              <div class="insight-card" style="flex: 1; min-width: 340px; max-width: 520px;">
                  <div class="glass-panel p-3 rounded-3xl mb-4 hover-extend-glow hover-lift transition-all position-relative overflow-hidden cursor-pointer" data-tilt data-tilt-max="3" style="border: 1px solid var(--glass-border);">
                      <div class="teaser-visual rounded-2xl bg-dark position-relative" style="height: 350px; overflow: hidden;">
                            <div class="position-absolute w-100 h-100 bg-teal-light opacity-30 blur-xl z-1"></div>
                            <div class="position-absolute top-50 start-50 translate-middle z-2 text-center w-100 px-4">
                                <div class="mb-4 d-inline-flex p-3 rounded-circle bg-white shadow-lg" style="background: rgba(255,255,255,0.1) !important; backdrop-filter: blur(10px);">
                                    <i data-lucide="zap" class="text-teal" style="width: 48px; height: 48px;"></i>
                                </div>
                                <h3 class="text-white accent-font fw-bold m-0 mb-3 h2">Matching 2.0</h3>
                                <span class="badge bg-teal text-white px-4 py-2 rounded-pill fs-xs text-uppercase tracking-tighter">Propulsé par GPT-4</span>
                            </div>
                      </div>
                  </div>
                  <div class="px-2">
                    <h4 class="accent-font text-high-contrast h5 fw-bold mb-3">Matching Prédictif</h4>
                    <p class="text-muted small lh-relaxed">Ne perdez plus de temps avec des offres non pertinentes. L'IA apprend de vos préférences pour affiner ses suggestions chaque jour.</p>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </section>

  <section class="section-blog-details py-6" id="blog-details" style="position:relative; z-index:10;">
    <div class="container">
      <div class="text-center mb-10 reveal-up">
        <div class="section-tag accent-teal mx-auto mb-3">Laboratoire d'idées</div>
        <h2 class="section-title accent-font">Intelligence & <span class="text-teal text-gradient-teal">Exploration</span></h2>
        <p class="section-desc mx-auto text-muted max-w-lg">Plongez dans nos recherches sur le futur du travail et les algorithmes qui façonneront les talents de demain.</p>
      </div>

      <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 35px; align-items: stretch;" class="reveal-up">
          <!-- Article 1 -->
          <div class="article-card" style="flex: 1; min-width: 300px; max-width: 380px;">
              <div class="glass-panel p-4 rounded-3xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast" data-tilt data-tilt-max="5" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                  <div class="article-img rounded-2xl mb-4 position-relative overflow-hidden" style="height:220px; background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(192, 38, 211, 0.1));">
                       <div class="position-absolute top-50 start-50 translate-middle">
                            <i data-lucide="trending-up" class="text-purple opacity-40" style="width: 80px; height: 80px;"></i>
                       </div>
                       <span class="badge position-absolute top-3 end-3 bg-blur-dark text-white rounded-pill px-3 py-1" style="font-size: 10px; backdrop-filter: blur(5px);">TENDANCES</span>
                  </div>
                  <h4 class="accent-font h5 fw-bold mb-3">KPIs Recrutement 2024</h4>
                  <p class="text-sm text-muted lh-relaxed flex-grow-1">Découvrez les indicateurs de performance clés qui transformeront les stratégies RH cette année.</p>
                  <div class="mt-4 pt-4 border-top border-light-subtle d-flex justify-content-between align-items-center">
                      <span class="text-xs text-muted opacity-75"><i data-lucide="clock" class="me-1" style="width:12px;"></i> 5 min</span>
                      <div class="btn-read-more text-purple fw-bold text-xs" style="cursor: pointer;">LIRE L'ARTICLE <i data-lucide="chevron-right" class="ms-1" style="width:14px;"></i></div>
                  </div>
              </div>
          </div>

          <!-- Article 2 -->
          <div class="article-card" style="flex: 1; min-width: 300px; max-width: 380px;">
              <div class="glass-panel p-4 rounded-3xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast" data-tilt data-tilt-max="5" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                  <div class="article-img rounded-2xl mb-4 position-relative overflow-hidden" style="height:220px; background: linear-gradient(135deg, rgba(234, 88, 12, 0.1), rgba(249, 115, 22, 0.1));">
                       <div class="position-absolute top-50 start-50 translate-middle">
                            <i data-lucide="pen-tool" class="text-orange opacity-40" style="width: 80px; height: 80px;"></i>
                       </div>
                       <span class="badge position-absolute top-3 end-3 bg-blur-dark text-white rounded-pill px-3 py-1" style="font-size: 10px; backdrop-filter: blur(5px);">CONSEILS</span>
                  </div>
                  <h4 class="accent-font h5 fw-bold mb-3">Optimisation Portfolio</h4>
                  <p class="text-sm text-muted lh-relaxed flex-grow-1">Comment marier créativité visuelle et SEO technique pour captiver instantanément les recruteurs.</p>
                  <div class="mt-4 pt-4 border-top border-light-subtle d-flex justify-content-between align-items-center">
                      <span class="text-xs text-muted opacity-75"><i data-lucide="clock" class="me-1" style="width:12px;"></i> 8 min</span>
                      <div class="btn-read-more text-orange fw-bold text-xs" style="cursor: pointer;">LIRE L'ARTICLE <i data-lucide="chevron-right" class="ms-1" style="width:14px;"></i></div>
                  </div>
              </div>
          </div>

          <!-- Article 3 -->
          <div class="article-card" style="flex: 1; min-width: 300px; max-width: 380px;">
              <div class="glass-panel p-4 rounded-3xl hover-extend-glow hover-lift transition-all h-100 d-flex flex-column text-high-contrast" data-tilt data-tilt-max="5" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03);">
                  <div class="article-img rounded-2xl mb-4 position-relative overflow-hidden" style="height:220px; background: linear-gradient(135deg, rgba(13, 148, 136, 0.1), rgba(20, 184, 166, 0.1));">
                       <div class="position-absolute top-50 start-50 translate-middle">
                            <i data-lucide="network" class="text-teal opacity-40" style="width: 80px; height: 80px;"></i>
                       </div>
                       <span class="badge position-absolute top-3 end-3 bg-blur-dark text-white rounded-pill px-3 py-1" style="font-size: 10px; backdrop-filter: blur(5px);">TECH</span>
                  </div>
                  <h4 class="accent-font h5 fw-bold mb-3">Architecture Matching</h4>
                  <p class="text-sm text-muted lh-relaxed flex-grow-1">Plongez dans les coulisses de l'infrastructure IA neuronale qui propulse le futur d'Aptus.</p>
                  <div class="mt-4 pt-4 border-top border-light-subtle d-flex justify-content-between align-items-center">
                      <span class="text-xs text-muted opacity-75"><i data-lucide="clock" class="me-1" style="width:12px;"></i> 12 min</span>
                      <div class="btn-read-more text-teal fw-bold text-xs" style="cursor: pointer;">LIRE L'ARTICLE <i data-lucide="chevron-right" class="ms-1" style="width:14px;"></i></div>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="text-center mt-12 reveal-up">
          <a href="signup_choice.php" class="btn btn-primary glow-btn px-8 py-3 rounded-pill h5 mb-0">Découvrir tout le Blog</a>
      </div>
    </div>
  </section>

  <!-- ==========================================
       TEAM ARCHIPEL SECTION
       ========================================== -->
  <section class="section-team py-6" id="team-archipel">
    <div class="container reveal-up">
      <div class="text-center mb-5">
        <div class="section-tag accent-teal mx-auto mb-3">L'Équipe Dévouée</div>
        <h2 class="section-title accent-font">Rencontrez l'<span class="text-teal text-gradient-teal">Archipel</span></h2>
        <p class="section-desc mx-auto text-muted max-w-lg">Notre mission est de bâtir le futur du recrutement. Découvrez les talents passionnés qui se cachent derrière la plateforme Aptus.</p>
      </div>

      <div class="team-grid grid-4 gap-4">
        <!-- Team Member 1 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper mx-auto mb-3 position-relative" style="width:100px;height:100px;">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-teal-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/user_placeholder.png" alt="Membre H" class="rounded-circle img-fluid position-relative z-1" style="border: 3px solid var(--glass-border);">
          </div>
          <h4 class="accent-font mb-1 text-md">Créateur Visionnaire</h4>
          <p class="text-sm text-teal fw-bold mb-2">Product Lead</p>
          <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="linkedin" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="github" style="width:14px;"></i></a>
          </div>
        </div>
        
        <!-- Team Member 2 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper mx-auto mb-3 position-relative" style="width:100px;height:100px;">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-purple-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/user_placeholder.png" alt="Membre 2" class="rounded-circle img-fluid position-relative z-1" style="border: 3px solid var(--glass-border); filter: hue-rotate(45deg);">
          </div>
          <h4 class="accent-font mb-1 text-md">L'Architecte IA</h4>
          <p class="text-sm text-purple fw-bold mb-2">Lead Developer</p>
          <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="linkedin" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 3 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper mx-auto mb-3 position-relative" style="width:100px;height:100px;">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-blue-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/user_placeholder.png" alt="Membre 3" class="rounded-circle img-fluid position-relative z-1" style="border: 3px solid var(--glass-border); filter: hue-rotate(90deg);">
          </div>
          <h4 class="accent-font mb-1 text-md">Moteur de Succès</h4>
          <p class="text-sm text-blue fw-bold mb-2">Customer Success</p>
          <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="twitter" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="mail" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 4 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper mx-auto mb-3 position-relative" style="width:100px;height:100px;">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-orange-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/user_placeholder.png" alt="Membre 4" class="rounded-circle img-fluid position-relative z-1" style="border: 3px solid var(--glass-border); filter: hue-rotate(180deg);">
          </div>
          <h4 class="accent-font mb-1 text-md">Génie Visuel</h4>
          <p class="text-sm text-orange fw-bold mb-2">UI/UX Designer</p>
          <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary"><i data-lucide="dribbble" style="width:14px;"></i></a>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-5">
         <a href="mailto:contact@aptus.com" class="btn btn-lg btn-ghost magnetic-btn">
             <i data-lucide="mail" class="me-2"></i> Écrivez-nous
         </a>
      </div>
    </div>
  </section>

  <!-- ==========================================
       FOOTER
       ========================================== -->
  <footer class="front-footer landing-footer py-5 mt-0 section-dark">
    <div class="container">
        <div class="front-footer__grid grid-4 gap-4">
        
          <div class="front-footer__brand pe-4">
            <a href="#hero" class="topnav__logo nav-anchor d-flex align-items-center gap-2 mb-3 text-decoration-none">
              <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="width:32px;">
              <span class="gradient-text accent-font h4 m-0">Aptus</span>
            </a>
            <p class="text-muted text-sm lh-lg">Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle. Repensez votre carrière.</p>
            <div class="social-links d-flex gap-3 mt-4">
                <a href="#" class="text-muted hover-text-primary"><i data-lucide="twitter"></i></a>
                <a href="#" class="text-muted hover-text-primary"><i data-lucide="linkedin"></i></a>
                <a href="#" class="text-muted hover-text-primary"><i data-lucide="github"></i></a>
            </div>
          </div>
          
          <div>
            <h4 class="front-footer__heading accent-font mb-4">Plateforme</h4>
            <div class="front-footer__links d-flex flex-column gap-3">
              <a href="#showcase-jobs" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Le Matching IA</a>
              <a href="#showcase-formations" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Formations & Cours</a>
              <a href="#showcase-cv" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Générateur de CV Pro</a>
              <a href="#showcase-formations" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Leaderboard Communauté</a>
            </div>
          </div>
          
          <div>
            <h4 class="front-footer__heading accent-font mb-4">Ressources</h4>
            <div class="front-footer__links d-flex flex-column gap-3">
              <a href="#doc-details" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Documentation API</a>
              <a href="#blog-details" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Blog & Actualités</a>
              <a href="#team-archipel" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Centre de Support</a>
            </div>
          </div>
          
          <div>
            <h4 class="front-footer__heading accent-font mb-4">Légal</h4>
            <div class="front-footer__links d-flex flex-column gap-3">
              <a href="#" class="text-muted hover-text-primary text-decoration-none transition-all">Conditions d'utilisation</a>
              <a href="#" class="text-muted hover-text-primary text-decoration-none transition-all">Politique de Confidentialité</a>
              <a href="#" class="text-muted hover-text-primary text-decoration-none transition-all">Préférences Cookies</a>
            </div>
          </div>
          
        </div>
        
        <div class="front-footer__bottom border-top border-dark-subtle mt-5 pt-4 d-flex justify-content-between text-muted text-sm flex-wrap gap-3">
          <span>&copy; <?php echo date('Y'); ?> Aptus. Tous droits réservés.</span>
        </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- Vanilla-tilt JS for 3D card effects (TikTok/Reel style) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  
  <script>
    lucide.createIcons();
    
    // Smooth Typewriter Effect for Hero Title
    var TxtType = function(el, toRotate, period) {
        this.toRotate = toRotate;
        this.el = el;
        this.loopNum = 0;
        this.period = parseInt(period, 10) || 2000;
        this.txt = '';
        this.tick();
        this.isDeleting = false;
    };

    TxtType.prototype.tick = function() {
        var i = this.loopNum % this.toRotate.length;
        var fullTxt = this.toRotate[i];

        if (this.isDeleting) {
        this.txt = fullTxt.substring(0, this.txt.length - 1);
        } else {
        this.txt = fullTxt.substring(0, this.txt.length + 1);
        }

        this.el.innerHTML = '<span class="wrap">'+this.txt+'</span>';

        var that = this;
        var delta = 150 - Math.random() * 50; // Smooth typing

        if (this.isDeleting) { delta /= 2; }

        if (!this.isDeleting && this.txt === fullTxt) {
        delta = this.period; // Pause at end
        this.isDeleting = true;
        } else if (this.isDeleting && this.txt === '') {
        this.isDeleting = false;
        this.loopNum++;
        delta = 500; // Pause before typing new word
        }
        setTimeout(function() { that.tick(); }, delta);
    };

    // Initialize Typewriter
    var elements = document.getElementsByClassName('typewrite');
    for (var i=0; i<elements.length; i++) {
        var toRotate = elements[i].getAttribute('data-type');
        var period = elements[i].getAttribute('data-period');
        if (toRotate) {
          new TxtType(elements[i], JSON.parse(toRotate), period);
        }
    }
    // Inject CSS for typewriter cursor
    var css = document.createElement("style");
    css.type = "text/css";
    css.innerHTML = ".typewrite > .wrap { border-right: 0.08em solid var(--accent-primary);}";
    document.body.appendChild(css);

    // Counter Animation Logic (Smoothed)
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.counter');
        const countDuration = 2000; // 2 seconds to reach the number
        
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    let startTimestamp = null;
                    const step = (timestamp) => {
                        if (!startTimestamp) startTimestamp = timestamp;
                        const progress = Math.min((timestamp - startTimestamp) / countDuration, 1);
                        const easeOutQuart = 1 - Math.pow(1 - progress, 4); // Easing function
                        const currentCount = Math.floor(easeOutQuart * target);
                        
                        counter.innerText = currentCount.toLocaleString('fr-FR') + (target >= 1000 ? '+' : '');
                        
                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        }
                    };
                    window.requestAnimationFrame(step);
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        
        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    });
  </script>
</body>
</html>
.