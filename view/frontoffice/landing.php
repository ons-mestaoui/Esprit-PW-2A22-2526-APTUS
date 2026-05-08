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

        <div class="hero-visual">
          <div class="hero-nodes-container flex-column" data-tilt data-tilt-max="5" data-tilt-speed="400">
              <canvas id="neurons-canvas" class="neurons-canvas"></canvas>
              <div class="central-logo-node glow-btn">
                  <img src="/aptus_first_official_version/view/assets/img/logo sans bg.png" alt="Aptus" style="width: 70px; height: auto; z-index: 2;">
              </div>
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
             <div class="skeleton-box w-30 h-10 mb-4 rounded"></div>
             <div class="d-flex gap-3 mb-4">
                 <div class="skeleton-box w-20 h-20 rounded-circle"></div>
                 <div class="w-100">
                     <div class="skeleton-box w-50 h-4 mb-2 rounded"></div>
                     <div class="skeleton-box w-70 h-3 rounded"></div>
                 </div>
             </div>
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
          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5">
            <div class="resource-icon bg-blue-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="book-open" class="text-blue" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Documentation</h3>
            <p class="text-muted text-sm mb-4">Découvrez toutes les astuces et méthodes pour maîtriser la plateforme.</p>
            <a href="documentation.php" class="resource-link text-blue fw-bold d-inline-flex align-items-center gap-1 hover-arrow">Lire la Doc <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>
          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5">
            <div class="resource-icon bg-purple-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="edit-3" class="text-purple" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Le Blog Insider</h3>
            <p class="text-muted text-sm mb-4">Tendances du recrutement IA et articles de veille exclusifs.</p>
            <a href="blog.php" class="resource-link text-purple fw-bold d-inline-flex align-items-center gap-1 hover-arrow">Découvrir le blog <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>
          <div class="resource-card glass-panel p-4 rounded-2xl hover-extend-glow hover-zoom text-high-contrast" data-tilt data-tilt-max="5">
            <div class="resource-icon bg-green-light rounded-circle d-inline-flex p-3 mb-4"><i data-lucide="life-buoy" class="text-green" style="width:28px;height:28px;"></i></div>
            <h3 class="accent-font mb-2">Centre de Support</h3>
            <p class="text-muted text-sm mb-4">Notre équipe dévouée est là pour vous assister à tout moment.</p>
            <a href="mailto:support@aptus.com" class="resource-link text-green fw-bold d-inline-flex align-items-center gap-1 hover-arrow">Contacter l'équipe <i data-lucide="arrow-right" style="width:16px;"></i></a>
          </div>
        </div>
    </div>
  </section>

  <!-- ==========================================
       FOOTER
       ========================================== -->
  <footer class="front-footer landing-footer py-5 mt-0 section-dark">
    <div class="container text-center">
        <div class="front-footer__brand mb-4">
          <a href="#hero" class="topnav__logo nav-anchor d-inline-flex align-items-center gap-2 mb-3 text-decoration-none">
            <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="width:32px;">
            <span class="gradient-text accent-font h4 m-0">Aptus</span>
          </a>
          <p class="text-muted text-sm">Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.</p>
        </div>
        <div class="front-footer__bottom border-top border-dark-subtle mt-4 pt-4 text-muted text-sm">
          <span>&copy; <?php echo date('Y'); ?> Aptus. Tous droits réservés.</span>
          <span class="ms-3">Fait avec ✨ en Tunisie</span>
        </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  
  <script>
    lucide.createIcons();
    
    // Smooth Typewriter Effect
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
        if (this.isDeleting) { this.txt = fullTxt.substring(0, this.txt.length - 1); }
        else { this.txt = fullTxt.substring(0, this.txt.length + 1); }
        this.el.innerHTML = '<span class="wrap">'+this.txt+'</span>';
        var that = this;
        var delta = 150 - Math.random() * 50;
        if (this.isDeleting) { delta /= 2; }
        if (!this.isDeleting && this.txt === fullTxt) { delta = this.period; this.isDeleting = true; }
        else if (this.isDeleting && this.txt === '') { this.isDeleting = false; this.loopNum++; delta = 500; }
        setTimeout(function() { that.tick(); }, delta);
    };

    window.onload = function() {
        var elements = document.getElementsByClassName('typewrite');
        for (var i=0; i<elements.length; i++) {
            var toRotate = elements[i].getAttribute('data-type');
            var period = elements[i].getAttribute('data-period');
            if (toRotate) { new TxtType(elements[i], JSON.parse(toRotate), period); }
        }
        var css = document.createElement("style");
        css.type = "text/css";
        css.innerHTML = ".typewrite > .wrap { border-right: 0.08em solid var(--accent-primary);}";
        document.body.appendChild(css);
    };
  </script>
</body>
</html>
