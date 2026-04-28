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
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/layout_front.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/landing_dynamic.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>

  <!-- Theme Toggle (load early to avoid flash) -->
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
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
      <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="landing-nav__logo-icon" style="background:none;">
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

      <?php if ($currentRole !== 'Entreprise'): ?>
      <!-- Notification Bell -->
      <?php
        if (!class_exists('candidatureC')) {
            require_once __DIR__ . '/../../controller/candidatureC.php';
        }
        $notifController = new candidatureC();
        $id_candidat_notif = 1; // Même ID par défaut que lors de la soumission
        $notifications = $notifController->getNotificationsByCandidat($id_candidat_notif);
        $unreadCount = 0;
        foreach ($notifications as $n) { if (!$n['is_read']) $unreadCount++; }
      ?>
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
                  <button class="delete-notif" onclick="deleteNotif(event, <?php echo $notif['id_notif']; ?>)" style="background:none; border:none; color:var(--text-tertiary); cursor:pointer; padding:0.2rem; opacity:0; transition:all 0.2s;" title="Supprimer">
                    <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <style>
            .notif-item:hover { background: var(--bg-hover) !important; }
            .notif-item:hover .delete-notif { opacity: 1 !important; }
            .delete-notif:hover { color: #ef4444 !important; }
            .notif-list::-webkit-scrollbar { width: 6px; }
            .notif-list::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
          </style>
        </div>
      </div>
      <script>
      function toggleNotifDropdown() {
          var menu = document.getElementById('notif-menu');
          menu.classList.toggle('active');
          // Marquer comme lues via AJAX
          var badge = document.getElementById('notif-badge');
          if (badge) {
              fetch('?mark_read=1').then(function(){
                  badge.style.display = 'none';
              });
          }
      }

      function deleteNotif(event, id) {
          event.stopPropagation();
          const item = event.target.closest('.notif-item');
          item.style.opacity = '0.5';
          item.style.pointerEvents = 'none';
          
          fetch('?delete_notif=' + id).then(function(res) {
              if (res.ok) {
                  item.style.transform = 'translateX(20px)';
                  item.style.opacity = '0';
                  setTimeout(() => item.remove(), 300);
              } else {
                  item.style.opacity = '1';
                  item.style.pointerEvents = 'auto';
              }
          });
      }

      document.addEventListener('click', function(e) {
          var dd = document.getElementById('notif-dropdown');
          if (dd && !dd.contains(e.target)) {
              document.getElementById('notif-menu').classList.remove('active');
          }
      });
      </script>
      <?php endif; ?>

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
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="topnav__logo-icon" style="background:none;">
          <span>Aptus</span>
        </a>
        <p>Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.</p>
      </div>
      <div>
        <h4 class="front-footer__heading">Plateforme</h4>
        <div class="front-footer__links">
          <a>Browse Jobs</a>
          <a>Formations</a>
          <a>CV Builder</a>
          <a>Leaderboard</a>
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
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="/aptus_first_official_version/view/assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script>lucide.createIcons();</script>
  <!-- Notification Toasts Container -->
  <div id="toast-container" style="position: fixed; top: 85px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; pointer-events: none;"></div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      // Afficher les notifications non lues sous forme de toasts
      <?php if (isset($unreadCount) && $unreadCount > 0): ?>
          <?php 
            $newNotifs = array_filter($notifications, function($n) { return !$n['is_read']; });
            $newNotifs = array_slice($newNotifs, 0, 3); // Max 3 toasts
            foreach($newNotifs as $notif): 
          ?>
              setTimeout(() => {
                  showToast("<?php echo addslashes(htmlspecialchars($notif['message'])); ?>");
              }, 500);
          <?php endforeach; ?>
      <?php endif; ?>
  });

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
      const isAccepted = (msgLower.includes('f\u00E9licitations') || msgLower.includes('\u00E9t\u00E9 retenue')) && !msgLower.includes('pas \u00E9t\u00E9 retenue');
      const iconColor = isAccepted ? '#10b981' : '#ef4444';
      const iconName = isAccepted ? 'check-circle' : 'x-circle';

      toast.innerHTML = `
          <div style="width: 40px; height: 40px; border-radius: 12px; background: ${iconColor}15; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="${iconName}" style="width: 22px; height: 22px; color: ${iconColor};"></i>
          </div>
          <div style="flex: 1; font-weight: 500;">${message}</div>
          <button onclick="this.parentElement.remove()" style="background:none; border:none; color:var(--text-tertiary); cursor:pointer; padding:0.2rem; display:flex; align-items:center;">
            <i data-lucide="x" style="width:16px;height:16px;"></i>
          </button>
      `;
      
      container.appendChild(toast);
      if (window.lucide) lucide.createIcons();

      // Slide in
      requestAnimationFrame(() => {
          setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 50);
      });

      // Auto remove after 4s
      setTimeout(() => {
          toast.style.transform = 'translateX(130%)';
          toast.style.opacity = '0';
          setTimeout(() => { if(toast.parentElement) toast.remove(); }, 600);
      }, 4000);
  }
  </script>
</body>
</html>
