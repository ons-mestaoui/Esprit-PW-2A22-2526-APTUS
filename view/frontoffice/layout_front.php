<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Prevent browser caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
include_once __DIR__ . '/../../controller/ProfilC.php';

$userId = $_SESSION['id_utilisateur'] ?? null;
$userRole = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : null;

// Access Control: Only Candidats, Entreprises, and Tuteurs can access frontoffice protected pages
if (!$userId || ($userRole !== 'candidat' && $userRole !== 'entreprise' && $userRole !== 'tuteur')) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['nom'] ?? 'Utilisateur';
$currentRole = $_SESSION['role'] ?? 'Candidat';

$userPhoto = null;
$userPrefs = null;
if ($userId) {
    $profilC = new ProfilC();
    $userProfil = $profilC->getProfilByIdUtilisateur($userId);
    if ($userProfil && !empty($userProfil['photo'])) {
        $userPhoto = $userProfil['photo'];
    }
    
    include_once __DIR__ . '/../../controller/UtilisateurC.php';
    $utC = new UtilisateurC();
    $userPrefs = $utC->getPreferences($userId);
}

// Initialize theme from preferences early
$currentTheme = $userPrefs['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $currentTheme; ?>">
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
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/layout_front.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/landing_dynamic.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>

  <!-- Theme Toggle (load early to avoid flash) -->
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>

  <?php
  // Load admin appearance overrides (colors, font, radius)
  require_once __DIR__ . '/../../controller/SettingsAdminC.php';
  $platformSettingsC = new SettingsAdminC();
  echo $platformSettingsC->getAppearanceCSS();
  
  // Load user personal preferences overrides (accent color, font size)
  if ($userPrefs) {
      $userCSS = '';
      if (!empty($userPrefs['accent_color'])) {
          $hex = $userPrefs['accent_color'];
          $userCSS .= "  --accent-primary: {$hex} !important;\n";
          $userCSS .= "  --accent-primary-dark: {$hex} !important;\n";
          $userCSS .= "  --accent-primary-light: {$hex}1a !important;\n";
      }
      if (!empty($userCSS)) {
          echo "<style id=\"user-appearance-overrides\">\n:root {\n{$userCSS}}\n</style>\n";
      }
      
      if (!empty($userPrefs['font_size'])) {
          echo "<style>html { font-size: " . intval($userPrefs['font_size']) . "px !important; }</style>\n";
      }

      if (!empty($userPrefs['font_family'])) {
          $ff = $userPrefs['font_family'];
          echo "<style>body, h1, h2, h3, h4, h5, h6, .btn, .input { font-family: '{$ff}', sans-serif !important; }</style>\n";
          echo "<link href=\"https://fonts.googleapis.com/css2?family=" . str_replace(' ', '+', $ff) . ":wght@300;400;500;600;700;800&display=swap\" rel=\"stylesheet\">\n";
      }

      if (!empty($userPrefs['border_radius'])) {
          $radiusMap = [
              'none' => '0px',
              'small' => '4px',
              'medium' => '12px',
              'large' => '20px',
              'full' => '9999px'
          ];
          $rv = $radiusMap[$userPrefs['border_radius']] ?? '12px';
          echo "<style>:root { --radius-lg: {$rv} !important; --radius-md: " . (intval($rv)*0.75) . "px !important; --radius-sm: " . (intval($rv)*0.5) . "px !important; }</style>\n";
      }
  }
  ?>

  <script>
    window.addEventListener('pageshow', function(event) {
      if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
      }
    });
  </script>
</head>
<body>

  <div class="hero-bg-animated" style="opacity: 0.4;">
      <div class="blob blob-1" style="filter: blur(120px);"></div>
      <div class="blob blob-2" style="filter: blur(150px);"></div>
      <div class="blob blob-3" style="filter: blur(140px);"></div>
      <div class="grid-overlay" style="opacity: 0.05;"></div>
  </div>

  <nav class="landing-nav glass-nav" id="landing-nav">
    <a href="<?php 
      if ($currentRole === 'Entreprise') echo 'hr_posts.php';
      elseif ($currentRole === 'Tuteur') echo 'dashboard_tuteur.php';
      else echo 'jobs_feed.php'; 
    ?>" class="landing-nav__logo nav-anchor text-decoration-none d-flex align-items-center gap-2">
      <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="landing-nav__logo-icon" style="background:none;">
      <span class="gradient-text accent-font h4 m-0">Aptus</span>
    </a>

    <button class="hamburger-landing" id="hamburger-landing" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>

    <div class="landing-nav__links" id="nav-links">
      <?php if ($currentRole === 'Entreprise'): ?>
        <a href="hr_posts.php" class="nav-anchor" id="nav-hr-posts"><i data-lucide="briefcase"></i><span>Mes Postes</span></a>
        <a href="hr_candidatures.php" class="nav-anchor" id="nav-hr-candidatures"><i data-lucide="users"></i><span>Candidatures</span></a>
        <a href="profil_entreprise.php" class="nav-anchor" id="nav-hr-profile"><i data-lucide="building"></i><span>Profil Entreprise</span></a>
        <a href="veille_feed_ent.php" class="nav-anchor" id="nav-hr-veille"><i data-lucide="line-chart"></i><span>Veille Marché</span></a>
      <?php elseif ($currentRole === 'Tuteur'): ?>
        <a href="dashboard_tuteur.php" class="nav-anchor" id="nav-tuteur-dashboard"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
        <a href="espace_tuteur.php" class="nav-anchor" id="nav-tuteur-espace"><i data-lucide="graduation-cap"></i><span>Mon Espace</span></a>
      <?php else: ?>
        <a href="jobs_feed.php" class="nav-anchor" id="nav-jobs"><i data-lucide="briefcase"></i><span>Offres d'emploi</span></a>
        <a href="cv_templates.php" class="nav-anchor" id="nav-cv"><i data-lucide="file-badge"></i><span>Générer CV</span></a>
        <a href="formations_catalog.php" class="nav-anchor" id="nav-formations"><i data-lucide="graduation-cap"></i><span>Formations</span></a>
        <a href="veille_feed.php" class="nav-anchor" id="nav-veille"><i data-lucide="line-chart"></i><span>Veille Marché</span></a>
        <a href="cv_my.php" class="nav-anchor" id="nav-cv-my"><i data-lucide="file-text"></i><span>Mes CVs</span></a>
      <?php endif; ?>
    </div>

    <div class="landing-nav__actions" style="display: flex; align-items: center; gap: 1rem;">
      <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>

      <!-- Notifications -->
      <div class="dropdown" id="notifications-dropdown">
        <button class="nav-icon-btn dropdown-trigger" aria-label="Notifications" style="position:relative; background:none; border:none; color:var(--text-primary); cursor:pointer; padding:8px; display:flex; align-items:center; justify-content:center; transition:all 0.3s ease;">
          <i data-lucide="bell" style="width:20px;height:20px;"></i>
          <span class="notification-dot" style="position:absolute; top:8px; right:8px; width:8px; height:8px; background:var(--accent-tertiary, #ef4444); border-radius:50%; border:2px solid var(--bg-card); animation: pulse-red 2s infinite;"></span>
        </button>
        <div class="dropdown-menu dropdown-menu--right" style="width:300px; padding:var(--space-4);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-4);">
            <h4 class="m-0" style="font-size:var(--fs-base);">Notifications</h4>
            <span style="font-size:var(--fs-xs); color:var(--accent-primary); cursor:pointer;">Tout marquer comme lu</span>
          </div>
          <div style="display:flex; flex-direction:column; gap:var(--space-3); max-height:300px; overflow-y:auto;">
            <!-- Placeholder Notifications -->
            <div style="display:flex; gap:var(--space-3); padding:var(--space-3); background:var(--bg-secondary); border-radius:var(--radius-md); font-size:var(--fs-sm);">
                <div style="color:var(--accent-primary);"><i data-lucide="info" style="width:16px;height:16px;"></i></div>
                <div>
                    <div style="font-weight:var(--fw-medium);">Bienvenue sur Aptus !</div>
                    <div style="font-size:var(--fs-xs); color:var(--text-secondary);">Complétez votre profil pour plus de visibilité.</div>
                </div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="#" style="display:block; text-align:center; font-size:var(--fs-xs); color:var(--text-tertiary); text-decoration:none; padding-top:var(--space-2);">Voir toutes les notifications</a>
        </div>
      </div>

      <style>
      @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
      }
      .nav-icon-btn:hover {
        background: var(--bg-secondary) !important;
        border-radius: var(--radius-md);
        transform: translateY(-1px);
      }
      </style>

      <div class="dropdown" id="profile-dropdown">
        <div class="dropdown-trigger topnav__profile">
          <div class="topnav__profile-info">
            <span class="topnav__profile-name"><?php echo isset($userName) ? $userName : 'Utilisateur'; ?></span>
            <span class="topnav__profile-role"><?php echo isset($userRole) ? $userRole : 'Candidat'; ?></span>
          </div>
          <div class="avatar" style="width:36px;height:36px;font-size:13px;overflow:hidden;background:var(--bg-glass);display:flex;align-items:center;justify-content:center;border-radius:50%;border:1px solid var(--border-color);">
            <?php if ($userPhoto): ?>
              <img src="<?php echo $userPhoto; ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <span class="avatar-initials">
                <?php echo isset($userName) ? strtoupper(substr($userName, 0, 2)) : 'US'; ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
        <div class="dropdown-menu">
          <?php if ($currentRole === 'Candidat'): ?>
          <a href="profil_candidat.php" class="dropdown-item" id="dropdown-profile">
            <i data-lucide="user" style="width:16px;height:16px;"></i>
            Mon Profil
          </a>
          <?php endif; ?>
          <a href="settings.php<?php 
            if ($currentRole === 'Entreprise') echo '?role=entreprise';
            elseif ($currentRole === 'Tuteur') echo '?role=tuteur';
          ?>" class="dropdown-item" id="dropdown-settings">
            <i data-lucide="settings" style="width:16px;height:16px;"></i>
            Paramètres
          </a>
          <?php if ($currentRole === 'Tuteur'): ?>
          <a href="profil_tuteur.php" class="dropdown-item" id="dropdown-profile-tuteur">
            <i data-lucide="user" style="width:16px;height:16px;"></i>
            Mon Profil
          </a>
          <?php endif; ?>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item" id="dropdown-logout" style="color:var(--accent-tertiary);">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i>
            Déconnexion
          </a>
        </div>
      </div>
    </div>
  </nav>

  <div class="mobile-menu-landing" id="mobile-menu-landing">
    <?php if ($currentRole === 'Entreprise'): ?>
      <a href="hr_posts.php" class="nav-anchor"><i data-lucide="briefcase"></i> Mes Postes</a>
      <a href="hr_candidatures.php" class="nav-anchor"><i data-lucide="users"></i> Candidatures</a>
      <a href="profil_entreprise.php" class="nav-anchor"><i data-lucide="building"></i> Profil Entreprise</a>
      <a href="veille_feed_ent.php" class="nav-anchor"><i data-lucide="line-chart"></i> Veille Marché</a>
      <a href="settings.php?role=entreprise" class="nav-anchor"><i data-lucide="settings"></i> Paramètres</a>
    <?php elseif ($currentRole === 'Tuteur'): ?>
      <a href="dashboard_tuteur.php" class="nav-anchor"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="espace_tuteur.php" class="nav-anchor"><i data-lucide="graduation-cap"></i> Mon Espace</a>
      <a href="settings.php?role=tuteur" class="nav-anchor"><i data-lucide="settings"></i> Paramètres</a>
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

  <main class="front-main">
    <div class="front-content">
      <?php
        if (isset($content)) {
          include $content;
        }
      ?>
    </div>
  </main>

  <footer class="front-footer">
    <div class="front-footer__grid">
      <div class="front-footer__brand">
        <a href="/" class="topnav__logo">
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="background:none;">
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

  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js?v=2"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="/aptus_first_official_version/view/assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  
  <div class="a11y-cursor" id="a11y-cursor"></div>
  <div class="a11y-video-container" id="a11y-video-container">
    <video id="a11y-webcam" autoplay playsinline></video>
    <canvas id="a11y-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
  </div>
  <button class="a11y-toggle" id="a11y-toggle" aria-label="Activer la navigation gestuelle" title="Navigation Hand Tracking">
    <i data-lucide="hand"></i>
  </button>
  <script type="module" src="/aptus_first_official_version/view/assets/js/a11y-hand-control.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
