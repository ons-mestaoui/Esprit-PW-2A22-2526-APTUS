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
include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/candidatureC.php';

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
    
    $utC = new UtilisateurC();
    $userPrefs = $utC->getPreferences($userId);
}

// Initialize theme from preferences early
$currentTheme = $userPrefs['theme'] ?? 'light';

// Handle notifications logic
$notifController = new candidatureC();
$notifications = [];
$unreadCount = 0;
if ($userRole === 'candidat') {
    // Note: Assuming ID 1 for test or extracting actual ID if available in session
    // In a real app, we'd use $userId linked to candidate
    $notifications = $notifController->getNotificationsByCandidat(1); 
    foreach ($notifications as $n) { if (!$n['is_read']) $unreadCount++; }
}

if (isset($_GET['mark_read'])) {
    // Logic to mark as read
    exit();
}
if (isset($_GET['delete_notif'])) {
    // Logic to delete
    exit();
}
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
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/ai_agent.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>

  <!-- Scripts -->
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/ai_agent.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/ai_agent_ext.js"></script>

  <?php
  // Load admin appearance overrides
  require_once __DIR__ . '/../../controller/SettingsAdminC.php';
  $platformSettingsC = new SettingsAdminC();
  echo $platformSettingsC->getAppearanceCSS();
  
  // User appearance overrides
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

      <!-- Notification Bell -->
      <div class="dropdown" id="notif-dropdown" style="position: relative;">
        <button class="dropdown-trigger" onclick="toggleNotifDropdown()" style="background: none; border: none; cursor: pointer; position: relative; padding: 0.4rem; display:flex; align-items:center;">
          <i data-lucide="bell" style="width:20px;height:20px;color:var(--text-secondary);"></i>
          <?php if ($unreadCount > 0): ?>
          <span id="notif-badge" style="position:absolute; top:-2px; right:-4px; background:#ef4444; color:white; font-size:0.65rem; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><?php echo $unreadCount; ?></span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu" id="notif-menu" style="width: 380px; max-height: 480px; overflow-y: auto; right: 0; left: auto; padding: 0; border: 1px solid var(--border-color); box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
          <div style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-card);">
            <h3 style="font-weight: 700; color: var(--text-primary); font-size: 1rem; margin: 0;">Notifications</h3>
            <?php if ($unreadCount > 0): ?>
              <span style="font-size: 0.75rem; background: var(--accent-primary); color: white; padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 600;"><?php echo $unreadCount; ?> nouvelles</span>
            <?php endif; ?>
          </div>
          <div class="notif-list">
            <?php if (empty($notifications)): ?>
              <div style="padding: 3rem 2rem; text-align: center; color: var(--text-tertiary);">
                <i data-lucide="bell-off" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.3;"></i>
                <div style="font-size: 0.9rem;">Aucune notification pour le moment</div>
              </div>
            <?php else: ?>
              <?php foreach ($notifications as $notif): 
                  $msgLower = strtolower($notif['message']);
                  $isAccepted = (strpos($msgLower, 'félicitations') !== false || strpos($msgLower, 'été retenue') !== false) && strpos($msgLower, 'pas été retenue') === false;
              ?>
                <div class="notif-item" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; transition: all 0.2s; display: flex; gap: 1rem; <?php echo !$notif['is_read'] ? 'background: rgba(79, 181, 255, 0.04); border-left: 3px solid var(--accent-primary);' : 'border-left: 3px solid transparent;'; ?>">
                  <div style="width: 36px; height: 36px; border-radius: 12px; background: <?php echo $isAccepted ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="<?php echo $isAccepted ? 'check-circle' : 'x-circle'; ?>" style="width: 20px; height: 20px; color: <?php echo $isAccepted ? '#10b981' : '#ef4444'; ?>;"></i>
                  </div>
                  <div style="flex: 1;">
                    <div style="margin-bottom: 0.4rem; color: var(--text-primary); font-weight: <?php echo !$notif['is_read'] ? '600' : '400'; ?>;">
                      <?php echo htmlspecialchars($notif['message']); ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-tertiary); display: flex; align-items: center; gap: 0.4rem;">
                      <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                      <?php echo date('d/m/Y H:i', strtotime($notif['date_notif'])); ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="dropdown" id="profile-dropdown">
        <div class="dropdown-trigger topnav__profile">
          <div class="topnav__profile-info">
            <span class="topnav__profile-name"><?php echo htmlspecialchars($userName); ?></span>
            <span class="topnav__profile-role"><?php echo htmlspecialchars($currentRole); ?></span>
          </div>
          <div class="avatar" style="width:36px;height:36px;overflow:hidden;background:var(--bg-glass);display:flex;align-items:center;justify-content:center;border-radius:50%;border:1px solid var(--border-color);">
            <?php if ($userPhoto): ?>
              <img src="<?php echo $userPhoto; ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <span class="avatar-initials"><?php echo strtoupper(substr($userName, 0, 2)); ?></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="dropdown-menu">
          <?php if ($currentRole !== 'Entreprise'): ?>
          <a href="profil_candidat.php" class="dropdown-item">
            <i data-lucide="user" style="width:16px;height:16px;"></i> Mon Profil
          </a>
          <?php endif; ?>
          <a href="settings.php" class="dropdown-item">
            <i data-lucide="settings" style="width:16px;height:16px;"></i> Paramètres
          </a>
          <div class="dropdown-divider"></div>
          <a href="login.php" class="dropdown-item" style="color:var(--accent-tertiary);">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i> Déconnexion
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
    <?php elseif ($currentRole === 'Tuteur'): ?>
      <a href="dashboard_tuteur.php" class="nav-anchor"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="espace_tuteur.php" class="nav-anchor"><i data-lucide="graduation-cap"></i> Mon Espace</a>
    <?php else: ?>
      <a href="jobs_feed.php" class="nav-anchor"><i data-lucide="briefcase"></i> Offres d'emploi</a>
      <a href="cv_templates.php" class="nav-anchor"><i data-lucide="file-badge"></i> Générer CV</a>
      <a href="formations_catalog.php" class="nav-anchor"><i data-lucide="graduation-cap"></i> Formations</a>
      <a href="veille_feed.php" class="nav-anchor"><i data-lucide="line-chart"></i> Veille Marché</a>
    <?php endif; ?>
  </div>

  <main class="front-main">
    <div class="front-content">
      <?php if (isset($content)) include $content; ?>
    </div>
  </main>

  <footer class="front-footer">
    <div class="front-footer__grid">
      <div class="front-footer__brand">
        <div class="topnav__logo">
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="background:none;">
          <span>Aptus</span>
        </div>
        <p>Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.</p>
      </div>
      <div>
        <h4 class="front-footer__heading">Plateforme</h4>
        <div class="front-footer__links">
          <a href="jobs_feed.php">Browse Jobs</a>
          <a href="formations_catalog.php">Formations</a>
          <a href="cv_landing.php">CV Builder</a>
        </div>
      </div>
    </div>
    <div class="front-footer__bottom">
      <span>&copy; <?php echo date('Y'); ?> Aptus. Tous droits réservés.</span>
      <span>Fait avec ✨ en Tunisie</span>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script>lucide.createIcons();</script>

  <!-- Notification Toasts Container -->
  <div id="toast-container" style="position: fixed; top: 85px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; pointer-events: none;"></div>

  <script>
  function toggleNotifDropdown() {
      var menu = document.getElementById('notif-menu');
      menu.classList.toggle('active');
  }

  function showToast(message) {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.style.cssText = `
          background: var(--bg-card);
          color: var(--text-primary);
          padding: 1rem 1.5rem;
          border-radius: 16px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.2);
          border-left: 4px solid var(--accent-primary);
          display: flex;
          align-items: center;
          gap: 1.25rem;
          min-width: 320px;
          max-width: 420px;
          transform: translateX(130%);
          transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
          font-size: 0.92rem;
          line-height: 1.5;
          pointer-events: auto;
          backdrop-filter: blur(10px);
          background: var(--bg-card-glass, var(--bg-card));
      `;
      
      const msgLower = message.toLowerCase();
      const isAccepted = msgLower.includes('retenue');
      const iconColor = isAccepted ? '#10b981' : '#ef4444';
      const iconName = isAccepted ? 'check-circle' : 'x-circle';

      toast.innerHTML = `
          <div style="width: 40px; height: 40px; border-radius: 12px; background: ${iconColor}15; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="${iconName}" style="width: 22px; height: 22px; color: ${iconColor};"></i>
          </div>
          <div style="flex: 1; font-weight: 500;">${message}</div>
      `;
      
      container.appendChild(toast);
      if (window.lucide) lucide.createIcons();

      requestAnimationFrame(() => {
          setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 50);
      });

      setTimeout(() => {
          toast.style.transform = 'translateX(130%)';
          toast.style.opacity = '0';
          setTimeout(() => { if(toast.parentElement) toast.remove(); }, 600);
      }, 4000);
  }

  document.addEventListener('DOMContentLoaded', function() {
      <?php if ($unreadCount > 0): ?>
          showToast("Vous avez <?php echo $unreadCount; ?> nouvelles notifications.");
      <?php endif; ?>
  });
  </script>
</body>
</html>
