<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Aptus — Plateforme intelligente de recrutement et d'apprentissage. Trouvez votre prochaine opportunité avec l'IA.">
  <title><?php echo isset($pageTitle) ? $pageTitle . ' — Aptus' : 'Aptus'; ?></title>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../assets/css/variables.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/layout_front.css">
  <link rel="stylesheet" href="../assets/css/landing_dynamic.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="../assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>

  <!-- Theme Toggle (load early to avoid flash) -->
  <script src="../assets/js/theme-toggle.js"></script>
</head>
<body>

  <!-- Cursor aura removed for internal pages -->

  <!-- Hero Background Animated (Global) - Smoothed for internal pages -->
  <div class="hero-bg-animated" style="opacity: 0.4;">
      <div class="blob blob-1" style="filter: blur(120px);"></div>
      <div class="blob blob-2" style="filter: blur(150px);"></div>
      <div class="blob blob-3" style="filter: blur(140px);"></div>
      <div class="grid-overlay" style="opacity: 0.05;"></div>
  </div>

  <!-- ═══════════════════════════════════════════
       TOP NAVIGATION BAR
       ═══════════════════════════════════════════ -->
  <nav class="landing-nav glass-nav" id="landing-nav">
    <?php $currentRole = isset($userRole) ? $userRole : 'Candidat'; ?>
    <!-- Logo -->
    <a href="<?php echo ($currentRole === 'Entreprise') ? 'hr_posts.php' : 'jobs_feed.php'; ?>" class="landing-nav__logo nav-anchor text-decoration-none d-flex align-items-center gap-2">
      <img src="../assets/img/logo.png" alt="Aptus" class="landing-nav__logo-icon" style="background:none;">
      <span class="gradient-text accent-font h4 m-0">Aptus</span>
    </a>

    <!-- Hamburger (Mobile) -->
    <button class="hamburger-landing" id="hamburger-landing" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>

    <!-- Navigation Links -->
    <div class="landing-nav__links" id="nav-links">
      <?php if ($currentRole === 'Entreprise'): ?>
        <a href="hr_posts.php" class="nav-anchor" id="nav-hr-posts"><i data-lucide="briefcase"></i><span>Mes Postes</span></a>
        <a href="hr_candidatures.php" class="nav-anchor" id="nav-hr-candidatures"><i data-lucide="users"></i><span>Candidatures</span></a>
        <a href="profil_entreprise.php" class="nav-anchor" id="nav-hr-profile"><i data-lucide="building"></i><span>Profil Entreprise</span></a>
        <a href="veille_feed_ent.php" class="nav-anchor" id="nav-hr-veille"><i data-lucide="line-chart"></i><span>Veille Marché</span></a>
      <?php else: ?>
        <a href="jobs_feed.php" class="nav-anchor" id="nav-jobs"><i data-lucide="briefcase"></i><span>Offres d'emploi</span></a>
        <a href="cv_templates.php" class="nav-anchor" id="nav-cv"><i data-lucide="file-badge"></i><span>Générer CV</span></a>
        <a href="formations_catalog.php" class="nav-anchor" id="nav-formations"><i data-lucide="graduation-cap"></i><span>Formations</span></a>
        <a href="veille_feed.php" class="nav-anchor" id="nav-veille"><i data-lucide="line-chart"></i><span>Veille Marché</span></a>
        <a href="cv_my.php" class="nav-anchor" id="nav-cv-my"><i data-lucide="file-text"></i><span>Mes CVs</span></a>
      <?php endif; ?>
    </div>

    <!-- Right Actions -->
    <div class="landing-nav__actions" style="display: flex; align-items: center; gap: 1rem;">
      <!-- Theme Toggle -->
      <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>

      <!-- Profile Dropdown -->
      <div class="dropdown" id="profile-dropdown">
        <div class="dropdown-trigger topnav__profile">
          <div class="topnav__profile-info">
            <span class="topnav__profile-name"><?php echo isset($userName) ? $userName : 'Utilisateur'; ?></span>
            <span class="topnav__profile-role"><?php echo isset($userRole) ? $userRole : 'Candidat'; ?></span>
          </div>
          <div class="avatar avatar-initials" style="width:36px;height:36px;font-size:13px;">
            <?php echo isset($userName) ? strtoupper(substr($userName, 0, 2)) : 'US'; ?>
          </div>
        </div>
        <div class="dropdown-menu">
          <?php if ($currentRole !== 'Entreprise'): ?>
          <a href="profil_candidat.php" class="dropdown-item" id="dropdown-profile">
            <i data-lucide="user" style="width:16px;height:16px;"></i>
            Mon Profil
          </a>
          <?php endif; ?>
          <a href="settings.php<?php echo ($currentRole === 'Entreprise') ? '?role=entreprise' : ''; ?>" class="dropdown-item" id="dropdown-settings">
            <i data-lucide="settings" style="width:16px;height:16px;"></i>
            Paramètres
          </a>
          <div class="dropdown-divider"></div>
          <a href="login.php" class="dropdown-item" id="dropdown-logout" style="color:var(--accent-tertiary);">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i>
            Déconnexion
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Navigation Menu -->
  <div class="mobile-menu-landing" id="mobile-menu-landing">
    <?php if ($currentRole === 'Entreprise'): ?>
      <a href="hr_posts.php" class="nav-anchor"><i data-lucide="briefcase"></i> Mes Postes</a>
      <a href="hr_candidatures.php" class="nav-anchor"><i data-lucide="users"></i> Candidatures</a>
      <a href="profil_entreprise.php" class="nav-anchor"><i data-lucide="building"></i> Profil Entreprise</a>
      <a href="veille_feed_ent.php" class="nav-anchor"><i data-lucide="line-chart"></i> Veille Marché</a>
      <a href="settings.php?role=entreprise" class="nav-anchor"><i data-lucide="settings"></i> Paramètres</a>
    <?php else: ?>
      <a href="jobs_feed.php" class="nav-anchor"><i data-lucide="briefcase"></i> Offres d'emploi</a>
      <a href="cv_templates.php" class="nav-anchor"><i data-lucide="file-badge"></i> Générer CV</a>
      <a href="formations_catalog.php" class="nav-anchor"><i data-lucide="graduation-cap"></i> Formations</a>
      <a href="veille_feed.php" class="nav-anchor"><i data-lucide="line-chart"></i> Veille Marché</a>
      <a href="cv_my.php" class="nav-anchor"><i data-lucide="file-text"></i> Mes CVs</a>
      <a href="profil_candidat.php" class="nav-anchor"><i data-lucide="user"></i> Mon Profil</a>
      <a href="settings.php" class="nav-anchor"><i data-lucide="settings"></i> Paramètres</a>
    <?php endif; ?>
  </div>

  <!-- ═══════════════════════════════════════════
       MAIN CONTENT
       ═══════════════════════════════════════════ -->
  <main class="front-main">
    <div class="front-content">
      <?php
        if (isset($content)) {
          include $content;
        }
      ?>
    </div>
  </main>

  <!-- ═══════════════════════════════════════════
       FOOTER
       ═══════════════════════════════════════════ -->
  <footer class="front-footer">
    <div class="front-footer__grid">
      <div class="front-footer__brand">
        <a href="/" class="topnav__logo">
          <img src="../assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="background:none;">
          <span>Aptus</span>
        </a>
        <p>Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.</p>
      </div>
      <div>
        <h4 class="front-footer__heading">Plateforme</h4>
        <div class="front-footer__links">
          <a href="jobs_feed.php">Browse Jobs</a>
          <a href="formations_catalog.php">Formations</a>
          <a href="cv_landing.php">CV Builder</a>
          <a href="veille_feed.php">Leaderboard</a>
        </div>
      </div>
      <div>
        <h4 class="front-footer__heading">Ressources</h4>
        <div class="front-footer__links">
          <a href="#">Documentation</a>
          <a href="#">Blog</a>
          <a href="#">Support</a>
          <a href="#">API</a>
        </div>
      </div>
      <div>
        <h4 class="front-footer__heading">Légal</h4>
        <div class="front-footer__links">
          <a href="#">Conditions</a>
          <a href="#">Confidentialité</a>
          <a href="#">Cookies</a>
        </div>
      </div>
    </div>
    <div class="front-footer__bottom">
      <span>&copy; <?php echo date('Y'); ?> Aptus. Tous droits réservés.</span>
      <span>Fait avec ❤️ en Tunisie</span>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="../assets/js/nav.js"></script>
  <script src="../assets/js/forms.js"></script>
  <script src="../assets/js/landing-animations.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="../assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script>lucide.createIcons();</script>
  <div class="aptus-modal-overlay" id="confirm-modal">
    <div class="aptus-modal-content" style="position:relative;">
      <!-- Close Button -->
      <button class="modal-close-btn" id="confirm-close" style="position:absolute; top:20px; right:20px; color:var(--text-tertiary); cursor:pointer;">
        <i data-lucide="x" style="width:24px;height:24px;"></i>
      </button>

      <div class="modal-icon-circle" style="background:#FFF0F0; color:#E11D48; width:70px; height:70px; margin-bottom:1.5rem;">
        <i data-lucide="alert-triangle" style="width:32px;height:32px;"></i>
      </div>

      <h3 class="aptus-modal-title" id="confirm-title" style="font-size:1.8rem; font-weight:800; color:#1e293b; margin-bottom:1rem;">Confirmation de suppression</h3>
      <p class="aptus-modal-text" id="confirm-text" style="font-size:1rem; color:#64748b; margin-bottom:2rem; line-height:1.6;">Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
      
      <div class="aptus-modal-footer" style="display:flex; gap:1rem;">
        <button class="btn-modal-cancel" id="confirm-cancel" style="flex:1; padding:0.8rem; border:1px solid #e2e8f0; border-radius:12px; font-weight:700; background:white; color:#1e293b;">Annuler</button>
        <button class="btn-modal-confirm" id="confirm-ok" style="flex:1; padding:0.8rem; background:#ef4444; border:none; border-radius:12px; font-weight:700; color:white; box-shadow:0 4px 12px rgba(239, 68, 68, 0.25);">Oui, Supprimer</button>
      </div>
    </div>
  </div>

  <script>
    window.aptusConfirm = function(title, text) {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirm-modal');
            const titleEl = document.getElementById('confirm-title');
            const textEl = document.getElementById('confirm-text');
            const okBtn = document.getElementById('confirm-ok');
            const cancelBtn = document.getElementById('confirm-cancel');
            const closeBtn = document.getElementById('confirm-close');

            if(title) titleEl.textContent = title;
            if(text) textEl.textContent = text;
            
            modal.classList.add('active');
            if(window.lucide) lucide.createIcons();

            const cleanup = (val) => {
                modal.classList.remove('active');
                okBtn.onclick = null;
                cancelBtn.onclick = null;
                closeBtn.onclick = null;
                resolve(val);
            };

            okBtn.onclick = () => cleanup(true);
            cancelBtn.onclick = () => cleanup(false);
            closeBtn.onclick = () => cleanup(false);
            modal.onclick = (e) => { if(e.target === modal) cleanup(false); };
        });
    };
  </script>
</body>
</html>
