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
      <a href="#showcase-academy" class="nav-anchor">Formations & XP</a>
      <a href="#resources" class="nav-anchor">Ressources</a>
    </div>
    <div class="landing-nav__actions">
      <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>
      <a href="login.php" class="btn btn-ghost nav-btn-login">Se connecter</a>
      <a href="login.php?panel=signup" class="btn btn-primary glow-btn">S'inscrire</a>
    </div>
    <button class="hamburger-landing" id="hamburger-landing" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Menu -->
  <div class="mobile-menu-landing" id="mobile-menu-landing">
    <a href="#showcase-jobs" class="nav-anchor">Matching IA</a>
    <a href="#showcase-cv" class="nav-anchor">CV Builder</a>
    <a href="#showcase-academy" class="nav-anchor">Formations & XP</a>
    <a href="#resources" class="nav-anchor">Ressources</a>
    <div class="mobile-menu-actions mt-4">
      <a href="login.php" class="btn btn-ghost w-100 mb-2">Se connecter</a>
      <a href="login.php?panel=signup" class="btn btn-primary w-100">S'inscrire</a>
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
          <a href="login.php?panel=signup" class="btn btn-primary btn-lg glow-btn splash-hover magnetic-btn">
            <i data-lucide="rocket"></i> S'inscrire
          </a>
          <a href="#showcase-jobs" class="btn btn-ghost btn-lg nav-anchor">
            Découvrir <i data-lucide="arrow-down" class="bounce ms-2"></i>
          </a>
        </div>

        <!-- Dynamic Stats -->
        <div class="hero__stats hero-stats-glass mt-5 text-high-contrast mb-4">
          <div class="hero__stat">
            <div class="hero__stat-value counter accent-font" data-target="12450">0</div>
            <div class="hero__stat-label">Offres</div>
          </div>
          <div class="hero__stat">
            <div class="hero__stat-value counter accent-font" data-target="845">0</div>
            <div class="hero__stat-label">Entreprises</div>
          </div>
          <div class="hero__stat">
            <div class="hero__stat-value counter accent-font" data-target="34102">0</div>
            <div class="hero__stat-label">CVs créés</div>
          </div>
          <div class="hero__stat">
            <div class="hero__stat-value accent-font" id="stat-rate">94%</div>
            <div class="hero__stat-label">Satisfaction</div>
          </div>
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
    <style>
      #showcase-jobs .flex {
        display: flex !important;
      }

      #showcase-jobs .items-center {
        align-items: center !important;
      }

      #showcase-jobs .items-start {
        align-items: flex-start !important;
      }

      #showcase-jobs .justify-between {
        justify-content: space-between !important;
      }

      #showcase-jobs .justify-center {
        justify-content: center !important;
      }

      #showcase-jobs .gap-2 {
        gap: 0.5rem !important;
      }

      #showcase-jobs .gap-3 {
        gap: 0.75rem !important;
      }

      #showcase-jobs .flex-shrink-0 {
        flex-shrink: 0 !important;
      }
    </style>
    <div class="showcase-container">
      <div class="showcase-text reveal-left">
        <div class="section-tag accent-purple">RADAR DE CARRIÈRE & RECRUTEMENT</div>
        <h2 class="section-title accent-font">Une Expérience de <br>Recrutement <br><span class="text-purple text-gradient-purple">Révolutionnaire</span></h2>
        <p class="section-desc text-secondary">Une réinvention complète de la recherche d'emploi et du recrutement, articulée autour de données en temps réel, de décisions assistées par IA, et d'une expérience utilisateur fluide et agréable.</p>

        <div class="grid-2-custom mt-6" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          <!-- Carte Interactive -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="map" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Carte Interactive</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Marché géolocalisé en temps réel.</p>
            </div>
          </div>

          <!-- Assistant IA -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="bot" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Assistant IA</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Pré-sélection objective de profils.</p>
            </div>
          </div>

          <!-- Suivi en Direct -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="bell" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Suivi en Direct</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Status de candidature instantané.</p>
            </div>
          </div>

          <!-- Rapports IA -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Rapports IA</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Audits PDF en un clic.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="showcase-visual reveal-right flex justify-center">
        <div class="mockup-card glass-panel p-4 text-high-contrast" style="border: 1px solid var(--glass-border); width: 100%; max-width: 520px; border-radius: var(--radius-xl); box-shadow: var(--shadow-2xl);">
          <div class="flex items-center gap-2 mb-4" style="padding-bottom: var(--space-3); border-bottom: 1px solid var(--glass-border);">
            <i data-lucide="map-pin" style="color: #7c3aed; width: 18px; height: 18px;"></i>
            <span class="accent-font fw-bold" style="font-size: 0.9rem; letter-spacing: 0.5px; color: var(--landing-text-muted);">Radar d'Activité en Tunisie</span>
          </div>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Tech Hub Tunis -->
            <div class="glass-panel flex items-center justify-between p-3 hover-lift" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg);">
              <div class="flex items-center gap-3">
                <div class="flex items-center justify-center" style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.15); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
                  <i data-lucide="map-pin" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                  <div class="fw-bold text-high-contrast" style="font-size: 0.95rem;">Tech Hub Tunis</div>
                  <div class="text-secondary text-xs">14 offres actives · 8 entreprises</div>
                </div>
              </div>
              <span class="fw-bold" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: 8px; padding: 0.35rem 0.75rem; font-size: 0.75rem; letter-spacing: 0.3px; flex-shrink: 0;">Live Feed</span>
            </div>

            <!-- Analyse IA Pré-sélection -->
            <div class="glass-panel flex items-center justify-between p-3 hover-lift" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg);">
              <div class="flex items-center gap-3">
                <div class="flex items-center justify-center" style="width: 48px; height: 48px; background: rgba(79, 70, 229, 0.15); color: #4f46e5; border-radius: var(--radius-md); flex-shrink: 0;">
                  <i data-lucide="check-square" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                  <div class="fw-bold text-high-contrast" style="font-size: 0.95rem;">Analyse IA Pré-sélection</div>
                  <div class="text-secondary text-xs">Réponses évaluées avec succès</div>
                </div>
              </div>
              <span class="fw-bold" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 8px; padding: 0.35rem 0.75rem; font-size: 0.75rem; letter-spacing: 0.3px; flex-shrink: 0;">Top 5%</span>
            </div>

            <!-- Rapport PDF Généré -->
            <div class="glass-panel flex items-center justify-between p-3 hover-lift" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg);">
              <div class="flex items-center gap-3">
                <div class="flex items-center justify-center" style="width: 48px; height: 48px; background: rgba(13, 148, 136, 0.15); color: #0d9488; border-radius: var(--radius-md); flex-shrink: 0;">
                  <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                  <div class="fw-bold text-high-contrast" style="font-size: 0.95rem;">Rapport PDF Généré</div>
                  <div class="text-secondary text-xs">Exportation du vivier disponible</div>
                </div>
              </div>
              <span class="fw-bold" style="background: rgba(148, 163, 184, 0.15); color: #64748b; border-radius: 8px; padding: 0.35rem 0.75rem; font-size: 0.75rem; letter-spacing: 0.3px; flex-shrink: 0;">PDF</span>
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
    <style>
      #showcase-cv .flex {
        display: flex !important;
      }

      #showcase-cv .items-center {
        align-items: center !important;
      }

      #showcase-cv .items-start {
        align-items: flex-start !important;
      }

      #showcase-cv .justify-between {
        justify-content: space-between !important;
      }

      #showcase-cv .justify-center {
        justify-content: center !important;
      }

      #showcase-cv .gap-2 {
        gap: 0.5rem !important;
      }

      #showcase-cv .gap-3 {
        gap: 0.75rem !important;
      }

      #showcase-cv .flex-shrink-0 {
        flex-shrink: 0 !important;
      }
    </style>
    <div class="showcase-container">
      <div class="showcase-text reveal-right">
        <div class="section-tag accent-purple">CURRICULUM INTELLIGENT</div>
        <h2 class="section-title accent-font">Un CV Sur-Mesure <br>Conçu pour <span class="text-purple text-gradient-purple">l'Impact</span></h2>
        <p class="section-desc text-secondary">Créez un CV qui attire l'attention en quelques minutes. L'intelligence artificielle guide pas à pas pour mettre en valeur vos compétences de manière claire et professionnelle, et décrocher plus d'entretiens sans le moindre effort.</p>

        <div class="grid-2-custom mt-6" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          <!-- Rédaction Assistée -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="wand-2" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Rédaction Assistée</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Textes d'accroches rédigés par l'IA.</p>
            </div>
          </div>

          <!-- Templates Premium -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="layout-template" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Templates Premium</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Designs professionnels et impactants.</p>
            </div>
          </div>

          <!-- CV Adaptatif -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="link" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">CV Adaptatif</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Ajusté automatiquement selon l'offre.</p>
            </div>
          </div>

          <!-- Optimisation ATS -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Optimisation ATS</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Format optimisé pour les recruteurs.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="showcase-visual reveal-left relative">
        <div class="mockup-cv-wrapper floating-slow" data-tilt data-tilt-perspective="1000" data-tilt-max="10">
          <div class="cv-paper glass-card bg-white shadow-xl text-dark" style="background: #ffffff !important; color: #1e293b !important; border-radius: var(--radius-lg); border: 1px solid rgba(0,0,0,0.08); font-family: 'Inter', sans-serif; padding: 1.5rem; text-align: left;">
            <!-- CV Header -->
            <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px solid rgba(0,0,0,0.06);">
              <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); font-weight: 700; font-size: 1.2rem; flex-shrink: 0;">
                AD
              </div>
              <div>
                <h4 class="mb-0 text-dark" style="font-size: 1.1rem; font-weight: 700; letter-spacing: -0.3px;">Alexandre Dubois</h4>
                <p class="mb-0 text-purple" style="font-size: 0.8rem; font-weight: 600;">Développeur React Front-End</p>
              </div>
            </div>

            <!-- CV Details -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
              <!-- Section Expérience -->
              <div>
                <h5 style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.4rem; letter-spacing: 0.5px;">Expérience</h5>
                <div style="background: rgba(124, 58, 237, 0.03); border-left: 3px solid #7c3aed; padding: 0.5rem 0.75rem; border-radius: 0 8px 8px 0;">
                  <div class="d-flex justify-content-between" style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">
                    <span>Lead React Dev · TechSphere</span>
                    <span style="color: #64748b; font-size: 0.7rem;">2024 - Présent</span>
                  </div>
                  <p class="mb-0 text-muted" style="font-size: 0.7rem; line-height: 1.3; margin-top: 2px;">Migration réussie vers React 18, augmentant la performance globale de 40%.</p>
                </div>
              </div>

              <!-- Section Compétences -->
              <div>
                <h5 style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.4rem; letter-spacing: 0.5px;">Compétences Clés</h5>
                <div class="d-flex flex-wrap gap-1">
                  <span style="background: rgba(124, 58, 237, 0.08); color: #7c3aed; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px;">React 18</span>
                  <span style="background: rgba(124, 58, 237, 0.08); color: #7c3aed; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px;">TypeScript</span>
                  <span style="background: rgba(124, 58, 237, 0.08); color: #7c3aed; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px;">Tailwind CSS</span>
                  <span style="background: rgba(124, 58, 237, 0.08); color: #7c3aed; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px;">Next.js</span>
                </div>
              </div>
            </div>

            <!-- AI Hover popup -->
            <div class="ai-suggestion-popup bounce-subtle" style="right: -1.5rem;">
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
  <section class="section-showcase" id="market-analysis">
    <style>
      #market-analysis .flex {
        display: flex !important;
      }

      #market-analysis .items-center {
        align-items: center !important;
      }

      #market-analysis .items-start {
        align-items: flex-start !important;
      }

      #market-analysis .justify-between {
        justify-content: space-between !important;
      }

      #market-analysis .justify-center {
        justify-content: center !important;
      }

      #market-analysis .gap-2 {
        gap: 0.5rem !important;
      }

      #market-analysis .gap-3 {
        gap: 0.75rem !important;
      }

      #market-analysis .flex-shrink-0 {
        flex-shrink: 0 !important;
      }
    </style>
    <div class="showcase-container">
      <div class="showcase-text reveal-left">
        <div class="section-tag accent-blue">Analyses de Marché</div>
        <h2 class="section-title accent-font">Anticipez les Mutations du Secteur en <span class="text-blue text-gradient-blue">Temps Réel</span>.</h2>
        <p class="section-desc text-secondary">L'algorithme IA qui transforme les données brutes du marché du travail en opportunités de carrière concrètes.</p>

        <div class="grid-2-custom mt-6" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          <!-- Flux en Direct -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="activity" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Flux en Direct</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Mise à jour continue des salaires (12h).</p>
            </div>
          </div>

          <!-- Prévisions -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="trending-up" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Prévisions</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Modélisation prédictive de l'emploi à 6 mois.</p>
            </div>
          </div>

          <!-- Carte Thermique -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="map-pin" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Carte Thermique</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Visualisation de la densité d'offres.</p>
            </div>
          </div>

          <!-- Bureau Holographique -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="smartphone" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Bureau Holographique</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Projection 3D en réalité augmentée.</p>
            </div>
          </div>
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
            <div class="progress mt-2" style="height:6px;">
              <div class="progress-bar bg-blue" style="width:75%; background:#2563eb;"></div>
            </div>
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
       SHOWCASE : MODULE 4 — TRAINING MODULE
       ========================================== -->
  <section class="section-showcase" id="showcase-formations">
    <div class="showcase-container">
      <div class="showcase-text reveal-left">
        <div class="section-tag accent-blue">Académie d'Apprentissage</div>
        <h2 class="section-title accent-font">Comblez l'Écart entre Apprentissage et <span class="text-blue text-gradient-blue">Opportunités</span></h2>
        <p class="section-desc text-muted">Un écosystème éducatif complet conçu pour combler l'écart entre votre niveau actuel et les exigences réelles du marché de l'emploi.</p>
        <div class="grid-2-custom mt-6" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          <!-- Card 1 -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="git-branch" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Arbre de Graphes</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Parcours guidé vers le statut prêt à l'emploi.</p>
            </div>
          </div>
          <!-- Card 2 -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="bot" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Tuteur IA</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Réponses instantanées sur les cours complexes.</p>
            </div>
          </div>
          <!-- Card 3 -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="award" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Défis Gamifiés</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">XP et badges de compétences exclusifs.</p>
            </div>
          </div>
          <!-- Card 4 -->
          <div class="glass-panel p-4 hover-lift flex gap-3 items-start" style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.02); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; border-radius: var(--radius-md); flex-shrink: 0;">
              <i data-lucide="check-square" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <h4 class="accent-font text-high-contrast mb-1" style="font-size: 1rem; font-weight: 700; margin-top: 2px;">Certifications</h4>
              <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.4;">Diplômes validés par les recruteurs.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="showcase-visual reveal-right">
         <div class="courses-grid-mockup">
            <div class="c-mockup-card glass-card p-3 mb-3 floating-anim-1 border-left-blue text-high-contrast hover-extend-glow" data-tilt data-tilt-max="10">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs text-muted fw-bold text-uppercase">Développement Web</span>
                    <span class="badge-xp bg-blue-light text-blue fw-bold rounded px-2 py-1 text-xs">+150 XP</span>
                </div>
                <h4 class="mb-1 text-md">Maîtriser React 18</h4>
                <div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-blue" style="width:75%; background:#2563eb;"></div></div>
            </div>
            
            <div class="leaderboard-aesthetic glass-panel p-4 mt-4 shadow-xl mx-auto rounded-xl hover-extend-glow hover-lift" style="max-width:85%;" data-tilt data-tilt-max="5">
                <div class="d-flex align-items-center gap-2 mb-4">
                     <div class="icon-glow bg-blue-light"><i data-lucide="award" class="text-blue"></i></div>
                     <h5 class="m-0 accent-font text-high-contrast">Classement Général</h5>
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
       TEAM ARCHIPEL SECTION (5 Members Well-Structured Layout)
       ========================================== -->
  <section class="section-team py-6" id="team-archipel">
    <style>
      #team-archipel .container {
        max-width: 1400px !important;
      }

      #team-archipel .team-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.85rem;
        margin-top: 3rem;
      }

      #team-archipel .team-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 0.75rem;
        height: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
      }

      #team-archipel .team-avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 1.25rem auto;
      }

      #team-archipel .team-avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid var(--glass-border);
      }

      #team-archipel .team-contact-info {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 0.6rem 0.5rem;
        width: 100%;
        margin: 1.25rem 0;
        text-align: left;
      }

      #team-archipel .contact-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.68rem;
        color: var(--landing-text-muted, #94a3b8);
        margin-bottom: 0.5rem;
      }

      #team-archipel .contact-item span {
        white-space: nowrap;
      }

      #team-archipel .contact-item:last-child {
        margin-bottom: 0;
      }

      #team-archipel .contact-item i {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: var(--text-teal, #0d9488);
      }

      /* Light Mode overrides to match the light theme photo exactly */
      body[data-theme="light"] #team-archipel .team-card {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04) !important;
      }

      body[data-theme="light"] #team-archipel .team-contact-info {
        background: rgba(0, 0, 0, 0.015) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
      }

      body[data-theme="light"] #team-archipel .contact-item {
        color: #64748b !important;
      }

      @media (max-width: 1200px) {
        #team-archipel .team-grid {
          grid-template-columns: repeat(3, 1fr);
        }
      }

      @media (max-width: 768px) {
        #team-archipel .team-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }

      @media (max-width: 480px) {
        #team-archipel .team-grid {
          grid-template-columns: 1fr;
        }
      }
    </style>
    <div class="container reveal-up">
      <div class="text-center mb-5">
        <div class="section-tag accent-teal mx-auto mb-3">L'Équipe Dévouée</div>
        <h2 class="section-title accent-font">Rencontrez <span class="text-teal text-gradient-teal">Archipel</span></h2>
        <p class="section-desc mx-auto text-muted max-w-lg">Découvrez les profils passionnés qui se cachent derrière la conception de la plateforme Aptus.</p>
      </div>

      <div class="team-grid">
        <!-- Team Member 1 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-teal-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/rayen.jpg" onerror="this.src='/aptus_first_official_version/view/assets/img/user_placeholder.svg'" alt="Taiba Med Rayen" class="rounded-circle position-relative z-1">
          </div>
          <h4 class="accent-font mb-1 text-md">Taiba Med Rayen</h4>
          <p class="text-sm text-teal fw-bold mb-2">Fullstack Developer</p>
          <div class="team-contact-info">
            <div class="contact-item"><i data-lucide="mail"></i> <span>MohamedRayen.Taiba@esprit.tn</span></div>
            <div class="contact-item"><i data-lucide="phone"></i> <span>+216 50 287 694</span></div>
            <div class="contact-item"><i data-lucide="map-pin"></i> <span>Tunis, Tunisie</span></div>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-auto">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="LinkedIn"><i data-lucide="linkedin" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="Twitter"><i data-lucide="twitter" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 2 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-purple-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/ons.png" onerror="this.src='/aptus_first_official_version/view/assets/img/user_placeholder.svg'" alt="Mestaoui Ons" class="rounded-circle position-relative z-1">
          </div>
          <h4 class="accent-font mb-1 text-md">Mestaoui Ons</h4>
          <p class="text-sm text-blue fw-bold mb-2">Fullstack Developer</p>
          <div class="team-contact-info">
            <div class="contact-item"><i data-lucide="mail"></i> <span>Ons.Mestaoui@esprit.tn</span></div>
            <div class="contact-item"><i data-lucide="phone"></i> <span>+216 54 059 297</span></div>
            <div class="contact-item"><i data-lucide="map-pin"></i> <span>Tunis, Tunisie</span></div>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-auto">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="LinkedIn"><i data-lucide="linkedin" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="GitHub"><i data-lucide="github" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 3 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-blue-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/Imen.png" onerror="this.src='/aptus_first_official_version/view/assets/img/user_placeholder.svg'" alt="Imen Ben Jbara" class="rounded-circle position-relative z-1">
          </div>
          <h4 class="accent-font mb-1 text-md">Imen Ben Jbara</h4>
          <p class="text-sm text-purple fw-bold mb-2">Fullstack Developer</p>
          <div class="team-contact-info">
            <div class="contact-item"><i data-lucide="mail"></i> <span>Imen.BenJbara@esprit.tn</span></div>
            <div class="contact-item"><i data-lucide="phone"></i> <span>+216 98 320 420</span></div>
            <div class="contact-item"><i data-lucide="map-pin"></i> <span>Tunis, Tunisie</span></div>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-auto">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="LinkedIn"><i data-lucide="linkedin" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="Dribbble"><i data-lucide="dribbble" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 4 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-mauve-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/A413B287-2894-4753-9F8C-0AF491729144.jpg" onerror="this.src='/aptus_first_official_version/view/assets/img/user_placeholder.svg'" alt="Outheila Taamalli" class="rounded-circle position-relative z-1">
          </div>
          <h4 class="accent-font mb-1 text-md">Outheila Taamalli</h4>
          <p class="text-sm text-blue fw-bold mb-2">Fullstack Developer</p>
          <div class="team-contact-info">
            <div class="contact-item"><i data-lucide="mail"></i> <span>Outheila.Taamalli@esprit.tn</span></div>
            <div class="contact-item"><i data-lucide="phone"></i> <span>+216 95 994 616</span></div>
            <div class="contact-item"><i data-lucide="map-pin"></i> <span>Tunis, Tunisie</span></div>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-auto">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="LinkedIn"><i data-lucide="linkedin" style="width:14px;"></i></a>
          </div>
        </div>

        <!-- Team Member 5 -->
        <div class="team-card glass-panel rounded-2xl p-4 text-center hover-extend-glow hover-lift text-high-contrast transition-all">
          <div class="team-avatar-wrapper">
            <div class="avatar-glow position-absolute w-100 h-100 rounded-circle bg-green-light blur-md" style="top:5px; left:0; z-index:0;"></div>
            <img src="/aptus_first_official_version/view/assets/img/amine.png" onerror="this.src='/aptus_first_official_version/view/assets/img/user_placeholder.svg'" alt="Med Amine Belloumi" class="rounded-circle position-relative z-1">
          </div>
          <h4 class="accent-font mb-1 text-md">Med Amine Belloumi</h4>
          <p class="text-sm text-teal fw-bold mb-2">Fullstack Developer</p>
          <div class="team-contact-info">
            <div class="contact-item"><i data-lucide="mail"></i> <span>MohamedAmine.Belloumi@esprit.tn</span></div>
            <div class="contact-item"><i data-lucide="phone"></i> <span>+216 54 485 455</span></div>
            <div class="contact-item"><i data-lucide="map-pin"></i> <span>Tunis, Tunisie</span></div>
          </div>
          <div class="d-flex justify-content-center gap-2 mt-auto">
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="LinkedIn"><i data-lucide="linkedin" style="width:14px;"></i></a>
            <a href="#" class="btn btn-sm btn-icon bg-light-subtle rounded-circle text-muted hover-text-primary" aria-label="GitHub"><i data-lucide="github" style="width:14px;"></i></a>
          </div>
        </div>
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
            <a href="#showcase-jobs" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Radar & Matching IA</a>
            <a href="#showcase-cv" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Curriculum Intelligent</a>
            <a href="#showcase-academy" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Académie d'Apprentissage</a>
            <a href="#leaderboard-showcase" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Classement Général</a>
          </div>
        </div>

        <div id="resources">
          <h4 class="front-footer__heading accent-font mb-4">Ressources</h4>
          <div class="front-footer__links d-flex flex-column gap-3">
            <a href="javascript:void(0)" id="link-doc" class="text-muted hover-text-primary text-decoration-none transition-all">Documentation API</a>
            <a href="javascript:void(0)" id="link-blog" class="text-muted hover-text-primary text-decoration-none transition-all">Blog & Actualités</a>
            <a href="#team-archipel" class="nav-anchor text-muted hover-text-primary text-decoration-none transition-all">Centre de Support</a>
          </div>
        </div>

        <div>
          <h4 class="front-footer__heading accent-font mb-4">Légal</h4>
          <div class="front-footer__links d-flex flex-column gap-3">
            <a href="javascript:void(0)" id="link-terms" class="text-muted hover-text-primary text-decoration-none transition-all">Conditions d'utilisation</a>
            <a href="javascript:void(0)" id="link-privacy" class="text-muted hover-text-primary text-decoration-none transition-all">Politique de Confidentialité</a>
            <a href="javascript:void(0)" id="link-cookies" class="text-muted hover-text-primary text-decoration-none transition-all">Préférences Cookies</a>
          </div>
        </div>

      </div>

      <div class="front-footer__bottom border-top border-dark-subtle mt-5 pt-4 d-flex justify-content-between text-muted text-sm flex-wrap gap-3">
        <span>&copy; <?php echo date('Y'); ?> Aptus. Tous droits réservés.</span>
      </div>
    </div>
  </footer>

  <!-- ==========================================
       LEGAL MODALS & COOKIES PREFERENCES
       ========================================== -->
  <style>
    /* Glassmorphic Modals Styles */
    .aptus-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(12px) saturate(160%);
      -webkit-backdrop-filter: blur(12px) saturate(160%);
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    [data-theme="dark"] .aptus-modal-overlay {
      background: rgba(8, 8, 12, 0.75);
    }
    .aptus-modal-overlay.active {
      opacity: 1;
    }
    .aptus-modal {
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-xl), 0 0 40px rgba(124, 58, 237, 0.05);
      border-radius: 20px;
      display: flex;
      flex-direction: column;
      transform: scale(0.92) translateY(20px);
      transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
      overflow: hidden;
    }
    [data-theme="dark"] .aptus-modal {
      background: rgba(18, 18, 26, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(124, 58, 237, 0.08);
    }
    .aptus-modal-overlay.active .aptus-modal {
      transform: scale(1) translateY(0);
    }
    .aptus-modal-header {
      padding: 1.5rem 1.75rem;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    [data-theme="dark"] .aptus-modal-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .aptus-modal-close {
      background: none;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;
      transition: color 0.2s, transform 0.2s;
      padding: 0.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .aptus-modal-close:hover {
      color: var(--accent-primary);
      transform: scale(1.1);
    }
    .aptus-modal-body {
      padding: 1.75rem;
      overflow-y: auto;
      color: var(--text-secondary);
      font-size: 0.9rem;
      line-height: 1.6;
    }
    .aptus-modal-body h5 {
      color: var(--text-primary);
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      margin-top: 1.5rem;
      margin-bottom: 0.5rem;
    }
    .aptus-modal-body h5:first-of-type {
      margin-top: 0;
    }
    .aptus-modal-body p {
      margin-bottom: 1.25rem;
    }
    .aptus-modal-body p:last-child {
      margin-bottom: 0;
    }
    .aptus-modal-footer {
      padding: 1.25rem 1.75rem;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      background: var(--bg-secondary);
    }
    [data-theme="dark"] .aptus-modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(12, 12, 18, 0.4);
    }
    /* Custom Switches for Cookie Toggles */
    .custom-switch-wrapper {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
      flex-shrink: 0;
    }
    .custom-switch-checkbox {
      opacity: 0;
      width: 0;
      height: 0;
    }
    .custom-switch-label {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: var(--bg-tertiary);
      transition: .3s;
      border-radius: 24px;
      border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .custom-switch-label {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .custom-switch-label:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 2px;
      bottom: 2px;
      background-color: var(--text-secondary);
      transition: .3s;
      border-radius: 50%;
    }
    .custom-switch-checkbox:checked + .custom-switch-label {
      background-color: rgba(124, 58, 237, 0.2);
      border-color: rgba(124, 58, 237, 0.4);
    }
    .custom-switch-checkbox:checked + .custom-switch-label:before {
      transform: translateX(20px);
      background-color: var(--accent-primary);
      box-shadow: 0 0 10px rgba(167, 139, 250, 0.4);
    }
    .custom-switch-checkbox:disabled + .custom-switch-label {
      opacity: 0.6;
      cursor: not-allowed;
    }
    /* Toast styling */
    .aptus-toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      z-index: 11000;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-xl);
      border-radius: 12px;
      padding: 0.85rem 1.25rem;
      opacity: 0;
      transform: translateY(15px);
      transition: opacity 0.3s, transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    [data-theme="dark"] .aptus-toast {
      background: rgba(18, 18, 26, 0.9);
      border: 1px solid rgba(20, 184, 166, 0.3);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(20, 184, 166, 0.1);
    }
    .aptus-toast.show {
      opacity: 1;
      transform: translateY(0);
    }
    .toast-content {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: var(--text-primary);
      font-size: 0.88rem;
      font-weight: 500;
    }
    .toast-content i {
      width: 20px;
      height: 20px;
    }
  </style>

  <!-- Modal overlay container -->
  <div class="aptus-modal-overlay" id="modal-container" style="display: none;">
    <!-- Terms Modal -->
    <div class="aptus-modal" id="modal-terms" style="display: none;">
      <div class="aptus-modal-header">
        <div class="d-flex align-items-center gap-2">
          <i data-lucide="file-text" class="text-purple" style="width: 24px; height: 24px;"></i>
          <h3 class="accent-font m-0 text-high-contrast">Conditions d'utilisation</h3>
        </div>
        <button class="aptus-modal-close" aria-label="Fermer"><i data-lucide="x"></i></button>
      </div>
      <div class="aptus-modal-body">
        <h5>1. Acceptation des conditions</h5>
        <p>En accédant et en utilisant la plateforme Aptus, vous acceptez d'être lié par les présentes conditions d'utilisation. Si vous n'avez pas l'âge légal ou n'acceptez pas ces termes, veuillez ne pas utiliser nos services.</p>
        
        <h5>2. Utilisation des services et de l'API</h5>
        <p>Aptus accorde un accès limité et révocable aux fonctionnalités de recrutement guidé par IA et de matching. Vous vous engagez à ne pas tenter de copier, modifier, désassembler ou perturber les algorithmes exclusifs de matching de la plateforme.</p>
        
        <h5>3. Comptes utilisateurs et sécurité</h5>
        <p>Pour accéder à certaines fonctionnalités, vous devez vous inscrire en créant un compte. Vous êtes responsable du maintien de la confidentialité de vos informations de connexion et de toutes les activités qui se produisent sous votre compte.</p>
        
        <h5>4. Propriété intellectuelle</h5>
        <p>Le code source, le design d'interface, la marque Aptus, le logo, les graphiques animés en 3D et les algorithmes IA sont la propriété exclusive d'Aptus et de ses concédants de licence. Toute reproduction non autorisée est strictement interdite.</p>
        
        <h5>5. Modification des services</h5>
        <p>Aptus se réserve le droit de modifier, suspendre ou interrompre tout ou partie de ses services à tout moment, sans préavis.</p>
      </div>
      <div class="aptus-modal-footer">
        <button class="btn btn-primary px-4 modal-btn-close">Fermer</button>
      </div>
    </div>

    <!-- Privacy Modal -->
    <div class="aptus-modal" id="modal-privacy" style="display: none;">
      <div class="aptus-modal-header">
        <div class="d-flex align-items-center gap-2">
          <i data-lucide="shield" class="text-purple" style="width: 24px; height: 24px;"></i>
          <h3 class="accent-font m-0 text-high-contrast">Politique de Confidentialité</h3>
        </div>
        <button class="aptus-modal-close" aria-label="Fermer"><i data-lucide="x"></i></button>
      </div>
      <div class="aptus-modal-body">
        <h5>1. Collecte des données personnelles</h5>
        <p>Nous collectons les informations nécessaires à la personnalisation de votre expérience sur Aptus, notamment : vos nom, prénom, adresse e-mail, historique académique, compétences professionnelles, et les fichiers de CV importés.</p>
        
        <h5>2. Utilisation de l'intelligence artificielle</h5>
        <p>Vos CV et données de compétences sont analysés par nos modèles d'apprentissage automatique afin de calculer des scores de compatibilité avec les offres d'emploi en temps réel. Ces analyses sont strictement confidentielles et ne sont jamais vendues à des tiers.</p>
        
        <h5>3. Partage de vos informations</h5>
        <p>Vos données de candidature ne sont partagées avec les entreprises partenaires d'Aptus que si vous postulez explicitement à une offre d'emploi ou si vous activez l'option de visibilité publique dans votre profil candidat.</p>
        
        <h5>4. Droits des utilisateurs</h5>
        <p>Conformément aux réglementations sur la protection des données personnelles (RGPD), vous disposez d'un droit d'accès, de rectification, de portabilité et de suppression de vos données personnelles. Vous pouvez exercer ces droits depuis les paramètres de votre compte.</p>
        
        <h5>5. Conservation des données</h5>
        <p>Nous conservons vos informations aussi longtemps que votre compte reste actif. Si vous demandez la suppression de votre compte, toutes vos données personnelles et CV associés seront définitivement effacés de nos serveurs sous 30 jours.</p>
      </div>
      <div class="aptus-modal-footer">
        <button class="btn btn-primary px-4 modal-btn-close">Fermer</button>
      </div>
    </div>

    <!-- Cookies Modal -->
    <div class="aptus-modal" id="modal-cookies" style="display: none;">
      <div class="aptus-modal-header">
        <div class="d-flex align-items-center gap-2">
          <i data-lucide="cookie" class="text-purple" style="width: 24px; height: 24px;"></i>
          <h3 class="accent-font m-0 text-high-contrast">Préférences Cookies</h3>
        </div>
        <button class="aptus-modal-close" aria-label="Fermer"><i data-lucide="x"></i></button>
      </div>
      <div class="aptus-modal-body">
        <p class="text-muted text-sm mb-4">Gérez les cookies utilisés par la plateforme Aptus pour optimiser votre expérience, vos suggestions de formations, et les performances du site.</p>
        
        <!-- Toggle 1: Essential -->
        <div class="cookie-toggle-row d-flex justify-content-between align-items-center p-3 mb-3 rounded-lg border border-dark-subtle" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05);">
          <div class="pe-3">
            <div class="fw-bold text-high-contrast text-sm mb-1">Cookies Essentiels <span class="badge bg-purple text-white ms-2 text-xxs">Requis</span></div>
            <p class="text-muted text-xs mb-0">Nécessaires pour sécuriser la connexion, retenir votre thème graphique, et assurer le bon fonctionnement de la plateforme.</p>
          </div>
          <div class="custom-switch-wrapper">
            <input type="checkbox" id="cookie-essential" checked disabled class="custom-switch-checkbox">
            <label for="cookie-essential" class="custom-switch-label"></label>
          </div>
        </div>

        <!-- Toggle 2: Analytics -->
        <div class="cookie-toggle-row d-flex justify-content-between align-items-center p-3 mb-3 rounded-lg border border-dark-subtle" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05);">
          <div class="pe-3">
            <div class="fw-bold text-high-contrast text-sm mb-1">Mesure d'Audience & Analytics</div>
            <p class="text-muted text-xs mb-0">Nous permettent de suivre les statistiques d'utilisation anonymes afin d'améliorer la fluidité de nos parcours.</p>
          </div>
          <div class="custom-switch-wrapper">
            <input type="checkbox" id="cookie-analytics" checked class="custom-switch-checkbox">
            <label for="cookie-analytics" class="custom-switch-label"></label>
          </div>
        </div>

        <!-- Toggle 3: AI Personalization -->
        <div class="cookie-toggle-row d-flex justify-content-between align-items-center p-3 mb-3 rounded-lg border border-dark-subtle" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05);">
          <div class="pe-3">
            <div class="fw-bold text-high-contrast text-sm mb-1">Personnalisation IA</div>
            <p class="text-muted text-xs mb-0">Stocke temporairement vos préférences de recherche d'emploi locales pour accélérer les recommandations du moteur de Matching IA.</p>
          </div>
          <div class="custom-switch-wrapper">
            <input type="checkbox" id="cookie-ai" checked class="custom-switch-checkbox">
            <label for="cookie-ai" class="custom-switch-label"></label>
          </div>
        </div>
      </div>
      <div class="aptus-modal-footer d-flex justify-content-between align-items-center gap-3">
        <button class="btn btn-ghost modal-btn-close">Annuler</button>
        <button class="btn btn-primary px-4" id="btn-save-cookies">Enregistrer mes choix</button>
      </div>
    </div>

    <!-- Documentation API Modal -->
    <div class="aptus-modal" id="modal-doc" style="display: none;">
      <div class="aptus-modal-header">
        <div class="d-flex align-items-center gap-2">
          <i data-lucide="code" class="text-purple" style="width: 24px; height: 24px;"></i>
          <h3 class="accent-font m-0 text-high-contrast">Documentation API Aptus</h3>
        </div>
        <button class="aptus-modal-close" aria-label="Fermer"><i data-lucide="x"></i></button>
      </div>
      <div class="aptus-modal-body">
        <p class="text-muted text-sm mb-4">Intégrez les services d'intelligence artificielle Aptus directement dans vos outils RH et vos portails d'apprentissage.</p>
        
        <h5>1. Authentification</h5>
        <p>Toutes les requêtes d'API doivent inclure votre clé d'API Aptus dans l'en-tête de la requête :</p>
        <pre class="bg-dark text-white p-3 rounded" style="font-size: 0.8rem; overflow-x: auto;"><code>Authorization: Bearer YOUR_API_KEY</code></pre>

        <h5>2. Analyse Sémantique de CV</h5>
        <p>Soumettez un CV pour extraire automatiquement les compétences et générer un profil structuré.</p>
        <div class="text-xs fw-bold text-uppercase text-teal mb-1">POST /api/v1/cv/analyze</div>
        <pre class="bg-dark text-white p-3 rounded" style="font-size: 0.8rem; overflow-x: auto;"><code>{
  "cv_url": "https://example.com/cv.pdf",
  "language": "fr"
}</code></pre>

        <h5>3. Calcul de Match IA</h5>
        <p>Calculez le score de compatibilité entre un candidat et une offre d'emploi.</p>
        <div class="text-xs fw-bold text-uppercase text-teal mb-1">POST /api/v1/jobs/match</div>
        <pre class="bg-dark text-white p-3 rounded" style="font-size: 0.8rem; overflow-x: auto;"><code>{
  "candidate_id": "cand_98452",
  "job_id": "job_01429"
}</code></pre>

        <h5>4. Webhooks de Progression</h5>
        <p>Recevez des notifications en temps réel lorsqu'un membre de votre équipe valide un cours ou gagne des points d'XP dans l'académie.</p>
      </div>
      <div class="aptus-modal-footer">
        <button class="btn btn-primary px-4 modal-btn-close">Fermer</button>
      </div>
    </div>

    <!-- Blog & Actualités Modal -->
    <div class="aptus-modal" id="modal-blog" style="display: none;">
      <div class="aptus-modal-header">
        <div class="d-flex align-items-center gap-2">
          <i data-lucide="newspaper" class="text-purple" style="width: 24px; height: 24px;"></i>
          <h3 class="accent-font m-0 text-high-contrast">Blog & Tendances Aptus</h3>
        </div>
        <button class="aptus-modal-close" aria-label="Fermer"><i data-lucide="x"></i></button>
      </div>
      <div class="aptus-modal-body">
        <p class="text-muted text-sm mb-4">Suivez l'actualité de la tech, du recrutement assisté par IA, et les dernières innovations de la plateforme Aptus.</p>

        <!-- Article 1 -->
        <div class="blog-item mb-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05) !important;">
          <span class="badge bg-purple text-white mb-2 text-xxs">Tendance Recrutement</span>
          <h5 class="text-high-contrast mb-1" style="font-size: 0.95rem; font-weight: 700;">Comment l'IA prédictive élimine les biais dans la sélection des talents</h5>
          <p class="text-muted text-xs mb-0">Découvrez comment les algorithmes de matching basés sur le sens et non plus seulement les mots-clés permettent d'assurer une évaluation équitable et chirurgicale de chaque profil candidat.</p>
        </div>

        <!-- Article 2 -->
        <div class="blog-item mb-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05) !important;">
          <span class="badge bg-blue text-white mb-2 text-xxs">Mise à jour Produit</span>
          <h5 class="text-high-contrast mb-1" style="font-size: 0.95rem; font-weight: 700;">Nouveauté Aptus : L'arbre de graphes de compétences interactif</h5>
          <p class="text-muted text-xs mb-0">Nous venons de déployer un tout nouveau système de visualisation de compétences dans l'Académie. Suivez votre progression XP en temps réel et préparez-vous sereinement aux opportunités du marché.</p>
        </div>

        <!-- Article 3 -->
        <div class="blog-item">
          <span class="badge bg-teal text-white mb-2 text-xxs">Astuces de Carrière</span>
          <h5 class="text-high-contrast mb-1" style="font-size: 0.95rem; font-weight: 700;">5 conseils pour booster la rédaction de votre CV grâce à l'assistant IA</h5>
          <p class="text-muted text-xs mb-0">Maximisez vos chances auprès des recruteurs en utilisant notre générateur intelligent de phrases d'accroche et nos modèles premium optimisés pour l'impact visuel.</p>
        </div>
      </div>
      <div class="aptus-modal-footer">
        <button class="btn btn-primary px-4 modal-btn-close">Fermer</button>
      </div>
    </div>
  </div>

  <!-- Toast Notification for Cookie Preferences Save -->
  <div id="aptus-toast" class="aptus-toast" style="display: none;">
    <div class="toast-content">
      <i data-lucide="check-circle" class="text-teal"></i>
      <span>Préférences enregistrées avec succès !</span>
    </div>
  </div>

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

      this.el.innerHTML = '<span class="wrap">' + this.txt + '</span>';

      var that = this;
      var delta = 150 - Math.random() * 50; // Smooth typing

      if (this.isDeleting) {
        delta /= 2;
      }

      if (!this.isDeleting && this.txt === fullTxt) {
        delta = this.period; // Pause at end
        this.isDeleting = true;
      } else if (this.isDeleting && this.txt === '') {
        this.isDeleting = false;
        this.loopNum++;
        delta = 500; // Pause before typing new word
      }
      setTimeout(function() {
        that.tick();
      }, delta);
    };

    // Initialize Typewriter
    var elements = document.getElementsByClassName('typewrite');
    for (var i = 0; i < elements.length; i++) {
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

    // --- LEGAL & INFORMATION MODALS CONTROLLER ---
    (function() {
      const links = {
        terms: document.getElementById('link-terms'),
        privacy: document.getElementById('link-privacy'),
        cookies: document.getElementById('link-cookies'),
        doc: document.getElementById('link-doc'),
        blog: document.getElementById('link-blog')
      };

      const modals = {
        container: document.getElementById('modal-container'),
        terms: document.getElementById('modal-terms'),
        privacy: document.getElementById('modal-privacy'),
        cookies: document.getElementById('modal-cookies'),
        doc: document.getElementById('modal-doc'),
        blog: document.getElementById('modal-blog')
      };

      const toast = document.getElementById('aptus-toast');
      const saveCookiesBtn = document.getElementById('btn-save-cookies');

      function openModal(modalKey) {
        // Hide all modals first
        modals.terms.style.display = 'none';
        modals.privacy.style.display = 'none';
        modals.cookies.style.display = 'none';
        modals.doc.style.display = 'none';
        modals.blog.style.display = 'none';

        // Display container & matching modal
        modals.container.style.display = 'flex';
        modals.container.offsetHeight; // trigger reflow
        modals.container.classList.add('active');
        modals[modalKey].style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Lock page scroll
        
        // Force Lucide icons inside modal to render if needed
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }

      function closeModal() {
        modals.container.classList.remove('active');
        document.body.style.overflow = ''; // Restore page scroll
        setTimeout(() => {
          modals.container.style.display = 'none';
          modals.terms.style.display = 'none';
          modals.privacy.style.display = 'none';
          modals.cookies.style.display = 'none';
          modals.doc.style.display = 'none';
          modals.blog.style.display = 'none';
        }, 350);
      }

      // Bind open links
      if (links.terms) links.terms.addEventListener('click', () => openModal('terms'));
      if (links.privacy) links.privacy.addEventListener('click', () => openModal('privacy'));
      if (links.cookies) links.cookies.addEventListener('click', () => openModal('cookies'));
      if (links.doc) links.doc.addEventListener('click', () => openModal('doc'));
      if (links.blog) links.blog.addEventListener('click', () => openModal('blog'));

      // Bind close elements
      document.querySelectorAll('.aptus-modal-close, .modal-btn-close').forEach(btn => {
        btn.addEventListener('click', closeModal);
      });

      // Close when clicking outside modal content
      if (modals.container) {
        modals.container.addEventListener('click', (e) => {
          if (e.target === modals.container) {
            closeModal();
          }
        });
      }

      // Close on Escape key press
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modals.container.style.display === 'flex') {
          closeModal();
        }
      });

      // Cookie Preferences Save
      if (saveCookiesBtn) {
        saveCookiesBtn.addEventListener('click', () => {
          // Gather values (in a real app, save to localStorage/cookies)
          const essential = document.getElementById('cookie-essential').checked;
          const analytics = document.getElementById('cookie-analytics').checked;
          const ai = document.getElementById('cookie-ai').checked;

          localStorage.setItem('cookie_essential', essential);
          localStorage.setItem('cookie_analytics', analytics);
          localStorage.setItem('cookie_ai_personalization', ai);

          closeModal();

          // Show Toast Notification
          if (toast) {
            toast.style.display = 'block';
            toast.offsetHeight; // trigger reflow
            toast.classList.add('show');
            
            setTimeout(() => {
              toast.classList.remove('show');
              setTimeout(() => {
                toast.style.display = 'none';
              }, 300);
            }, 3000);
          }
        });
      }
    })();
  </script>
</body>

</html>