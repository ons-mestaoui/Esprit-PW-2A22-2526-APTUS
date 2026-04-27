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

  <!-- UX Discoverability: Intro.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
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
        <a href="formations_my.php" class="nav-anchor" id="nav-my-formations"><i data-lucide="book-open"></i><span>Mes Formations</span></a>
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

      <!-- Notification Bell (Premium) + Intro.js Styles -->
      <style>
        /* ─── Intro.js Custom Aptus Branding ─── */
        .introjs-tooltip {
            border-radius: 12px;
            background-color: var(--bg-card);
            color: var(--text-primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 1px solid var(--accent-primary);
        }
        .introjs-button {
            background: var(--gradient-primary) !important;
            color: white !important;
            text-shadow: none !important;
            border: none !important;
            border-radius: 8px !important;
        }
        /* ─── Notification Panel (override dropdown-menu defaults) ─── */
        #notification-dropdown .notif-panel {
          width: 360px !important;
          min-width: 360px !important;
          padding: 0 !important;
          overflow: hidden;
          border-radius: 16px !important;
          box-shadow: 0 25px 60px rgba(0,0,0,0.18) !important;
          /* DO NOT set display:none — the global .dropdown.open system handles show/hide via opacity/visibility */
        }
        .notif-panel__head {
          padding: 1rem 1.25rem;
          background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
          display: flex; align-items: center; justify-content: space-between;
        }
        .notif-panel__head h5 {
          margin: 0; color: #fff; font-size: 1rem; font-weight: 700;
          display: flex; align-items: center; gap: 8px;
        }
        .notif-panel__head h5 .notif-count-pill {
          background: rgba(255,255,255,0.25); color: #fff;
          font-size: 11px; padding: 2px 8px; border-radius: 20px;
          font-weight: 600;
        }
        .notif-panel__mark-btn {
          background: rgba(255,255,255,0.15); border: none;
          color: #fff; font-size: 11px; padding: 5px 10px;
          border-radius: 8px; cursor: pointer; font-weight: 600;
          transition: background 0.2s;
        }
        .notif-panel__mark-btn:hover { background: rgba(255,255,255,0.3); }
        .notif-panel__body { max-height: 400px; overflow-y: auto; }
        .notif-panel__body::-webkit-scrollbar { width: 4px; }
        .notif-panel__body::-webkit-scrollbar-track { background: transparent; }
        .notif-panel__body::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
        .notif-item {
          display: flex; align-items: flex-start; gap: 12px;
          padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);
          text-decoration: none; color: inherit;
          transition: background 0.15s, transform 0.15s;
          position: relative;
        }
        .notif-item:hover { background: var(--bg-surface); transform: translateX(2px); }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread::before {
          content: ''; width: 7px; height: 7px; border-radius: 50%;
          background: #6366f1; position: absolute; top: 1.1rem; right: 1rem;
          flex-shrink: 0;
        }
        .notif-item__icon {
          width: 38px; height: 38px; border-radius: 10px;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0; margin-top: 2px;
        }
        .notif-item__icon.type-certif_ready  { background: rgba(16,185,129,0.12); color: #10b981; }
        .notif-item__icon.type-peer_request  { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .notif-item__icon.type-new_message   { background: rgba(59,130,246,0.12);  color: #3b82f6; }
        .notif-item__icon.type-ai_reply      { background: rgba(99,102,241,0.12);  color: #6366f1; }
        .notif-item__icon.type-default       { background: rgba(156,163,175,0.12); color: #9ca3af; }
        .notif-item__body { flex: 1; min-width: 0; }
        .notif-item__type {
          font-size: 10px; font-weight: 700; text-transform: uppercase;
          letter-spacing: 0.06em; margin-bottom: 3px;
          color: var(--text-secondary);
        }
        .notif-item__msg {
          font-size: 0.85rem; line-height: 1.45; color: var(--text-primary);
          white-space: normal; font-weight: 500;
        }
        .notif-item__time {
          font-size: 0.72rem; color: var(--text-secondary);
          margin-top: 4px; display: block;
        }
        .notif-empty {
          padding: 3rem 1.5rem; text-align: center; color: var(--text-secondary);
        }
        .notif-empty__icon { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.5; }
        .notif-empty p { font-size: 0.9rem; margin: 0; }
        .notif-panel__foot {
          padding: 0.6rem 1.25rem; background: var(--bg-surface);
          border-top: 1px solid var(--border-color);
          text-align: center; font-size: 11px; color: var(--text-secondary);
        }
        @keyframes bellRing {
          0%,100% { transform: rotate(0); }
          15%      { transform: rotate(12deg); }
          30%      { transform: rotate(-10deg); }
          45%      { transform: rotate(8deg); }
          60%      { transform: rotate(-6deg); }
          75%      { transform: rotate(4deg); }
        }
        @keyframes pulse-ring {
          0%   { transform: scale(0.8); opacity: 0.7; }
          100% { transform: scale(2);   opacity: 0; }
        }
        .bell-has-notif { animation: bellRing 1.5s ease 0.3s; }
        .bell-pulse-ring {
          position: absolute; top: 50%; left: 50%;
          width: 20px; height: 20px; border-radius: 50%;
          background: rgba(99,102,241,0.5);
          transform: translate(-50%,-50%);
          animation: pulse-ring 1.5s ease-out infinite;
          pointer-events: none;
        }
      </style>

      <div class="dropdown" id="notification-dropdown">
        <button class="landing-nav__btn-icon dropdown-trigger"
                id="notif-bell-btn"
                style="position:relative; background:none; border:none; cursor:pointer; color:var(--text-primary); width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <span class="bell-pulse-ring" id="bell-pulse" style="display:none;"></span>
            <i data-lucide="bell" id="notif-bell-icon" style="width:20px; height:20px;"></i>
            <span id="notif-badge" style="display:none; position:absolute; top:-4px; right:-4px;
                  background: linear-gradient(135deg,#ef4444,#dc2626); color:white;
                  font-size:10px; min-width:18px; height:18px; line-height:18px;
                  text-align:center; border-radius:9px; border:2px solid var(--bg-card);
                  font-weight:700; padding:0 4px;">0</span>
        </button>
        <div class="dropdown-menu notif-panel" id="notif-panel">
            <div class="notif-panel__head">
                <h5>
                    <i data-lucide="bell" style="width:16px;height:16px;"></i>
                    Notifications
                    <span class="notif-count-pill" id="notif-head-count">0</span>
                </h5>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <button class="notif-panel__mark-btn" onclick="markAllRead()" title="Tout marquer comme lu" style="padding: 6px 8px;">
                        <i data-lucide="check-check" style="width:14px; height:14px; vertical-align:middle;"></i>
                    </button>
                    <button class="notif-panel__mark-btn" id="dnd-toggle-btn" onclick="toggleDND()" style="background:rgba(0,0,0,0.2); padding: 6px 8px;" title="Mode Silence">
                        <i data-lucide="bell" id="dnd-icon-status" style="width:14px; height:14px; vertical-align:middle;"></i>
                    </button>
                    <button class="notif-panel__mark-btn" onclick="deleteAllNotifications()" style="background:rgba(0,0,0,0.2); color:#fff; padding: 6px 8px;" title="Vider l'historique">
                        <i data-lucide="trash-2" style="width:14px; height:14px; vertical-align:middle;"></i>
                    </button>
                </div>
            </div>
            <div id="dnd-banner" style="display:none; background: #fffbeb; border-bottom:1px solid #fef3c7; padding: 6px 15px; font-size: 11px; color: #92400e; font-weight: 600; text-align: center;">
                <i data-lucide="moon" style="width:12px; vertical-align:middle;"></i> Mode Silence actif (Seul l'urgent s'affiche)
            </div>
            <div class="notif-panel__body" id="notif-items">
                <div class="notif-empty">
                    <div class="notif-empty__icon">🔔</div>
                    <p>Chargement...</p>
                </div>
            </div>
            <div class="notif-panel__foot">
                ✨ Aptus Engagement Engine
            </div>
        </div>
      </div>


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
          <a href="skill_tree.php" class="dropdown-item" id="dropdown-skilltree">
            <i data-lucide="git-branch" style="width:16px;height:16px;"></i>
            Arbre de Compétences
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
      <a href="formations_my.php" class="nav-anchor"><i data-lucide="book-open"></i> Mes Formations</a>
      <a href="veille_feed.php" class="nav-anchor"><i data-lucide="line-chart"></i> Veille Marché</a>
      <a href="cv_my.php" class="nav-anchor"><i data-lucide="file-text"></i> Mes CVs</a>
      <a href="profil_candidat.php" class="nav-anchor"><i data-lucide="user"></i> Mon Profil</a>
      <a href="skill_tree.php" class="nav-anchor"><i data-lucide="git-branch"></i> Skill Tree</a>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/landing-animations.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="/aptus_first_official_version/view/assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script>
    lucide.createIcons();

    // SweetAlert2 Toast Global Configuration
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    <?php if (isset($_SESSION['flash_success'])): ?>
      Toast.fire({
        icon: 'success',
        title: <?php echo json_encode($_SESSION['flash_success']); ?>
      });
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
      Toast.fire({
        icon: 'error',
        title: <?php echo json_encode($_SESSION['flash_error']); ?>
      });
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    // --- NOTIFICATION SYSTEM (Premium) ---
    const NOTIF_ICONS = {
        certif_ready : 'award',
        peer_request : 'users',
        new_message  : 'message-circle',
        ai_reply     : 'cpu',
        default      : 'bell'
    };
    const NOTIF_LABELS = {
        certif_ready : '🏆 Certificat',
        peer_request : '🤝 Peer Learning',
        new_message  : '💬 Message',
        ai_reply     : '🤖 IA',
        default      : '🔔 Notification'
    };

    let _prevCount = 0;
    let _bellRung  = false;
    let _dndMode   = localStorage.getItem('aptus_dnd_mode') === 'true';

    function toggleDND() {
        _dndMode = !_dndMode;
        localStorage.setItem('aptus_dnd_mode', _dndMode);
        applyDNDUI();
        fetchNotifications();
    }

    function applyDNDUI() {
        const btn = document.getElementById('dnd-toggle-btn');
        const icon = document.getElementById('dnd-icon-status');
        const banner = document.getElementById('dnd-banner');
        if (_dndMode) {
            btn.style.background = '#ef4444';
            icon.setAttribute('data-lucide', 'bell-off');
            banner.style.display = 'block';
        } else {
            btn.style.background = 'rgba(0,0,0,0.2)';
            icon.setAttribute('data-lucide', 'bell');
            banner.style.display = 'none';
        }
        lucide.createIcons();
    }

    // Apply UI on load
    document.addEventListener('DOMContentLoaded', () => {
        applyDNDUI();
        requestPushPermission();
    });

    // ─── Native Push Notifications ───
    function requestPushPermission() {
        if (!("Notification" in window)) return;
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    function sendNativePush(title, options) {
        if (!("Notification" in window)) return;
        if (Notification.permission === "granted") {
            const n = new Notification(title, options);
            n.onclick = function() {
                window.focus();
                n.close();
            };
        }
    }

    // ─── Tick Sound via Web Audio API ───
    function playNotifTick() {
        if (_dndMode) return; // Silent in DND
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);           // A5 note
            osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.08);   // slight rise
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.25);
        } catch(e) {} // silent fail if browser blocks audio
    }

    function timeAgo(minutes) {
        if (minutes < 1)     return "À l'instant";
        if (minutes < 60)    return `il y a ${minutes} min`;
        const h = Math.floor(minutes / 60);
        if (h < 24)          return `il y a ${h}h`;
        return `il y a ${Math.floor(h/24)}j`;
    }

    function fetchNotifications() {
        fetch('/aptus_first_official_version/view/frontoffice/ajax_handler.php?action=get_notifications')
        .then(r => r.json())
        .then(data => { 
            if (data.success) {
                // Filter DND if active (keep only URGENT_)
                let notifs = data.notifications;
                if (_dndMode) {
                    notifs = notifs.filter(n => n.type.startsWith('URGENT_'));
                }
                updateNotifUI(notifs); 
            }
        })
        .catch(() => {}); // silent fail
    }

    function updateNotifUI(notifs) {
        const badge     = document.getElementById('notif-badge');
        const headCount = document.getElementById('notif-head-count');
        const list      = document.getElementById('notif-items');
        const bellIcon  = document.getElementById('notif-bell-icon');
        const pulse     = document.getElementById('bell-pulse');
        const count     = notifs.length;

        // Update badge
        if (count > 0) {
            badge.textContent     = count > 99 ? '99+' : count;
            badge.style.display   = 'block';
            headCount.textContent = count;

            // Check if there is any URGENT in the list for pulse
            const hasUrgent = notifs.some(n => n.type.startsWith('URGENT_'));

            // Ring bell + play tick sound when NEW notifs arrive
            if (!_bellRung || count > _prevCount) {
                bellIcon.classList.remove('bell-has-notif');
                void bellIcon.offsetWidth;
                bellIcon.classList.add('bell-has-notif');
                if (hasUrgent) pulse.style.display = 'block';
                _bellRung = true;
                // Play tick and send push only when count increases (new notification)
                if (count > _prevCount && _prevCount !== 0) {
                    playNotifTick();
                    
                    // Native Browser Push
                    if (notifs.length > 0 && !_dndMode) {
                        const newest = notifs[0];
                        sendNativePush("Nouvelle notification Aptus", {
                            body: newest.message,
                            icon: "/aptus_first_official_version/view/assets/img/logo.png"
                        });
                    }
                }
            }
        } else {
            badge.style.display   = 'none';
            headCount.textContent = 0;
            pulse.style.display   = 'none';
            bellIcon.classList.remove('bell-has-notif');
        }
        _prevCount = count;

        // Render items
        if (count === 0) {
            list.innerHTML = `
                <div class="notif-empty">
                    <div class="notif-empty__icon">${_dndMode ? '🌙' : '✅'}</div>
                    <p>${_dndMode ? 'Mode Silence : Seules les alertes critiques s\'afficheront.' : 'Vous êtes à jour !<br>Aucune nouvelle notification.'}</p>
                </div>`;
            return;
        }

        let html = '';
        notifs.forEach(n => {
            let typeKey = n.type.replace('URGENT_', '').replace('SILENT_', '');
            const isUrgent = n.type.startsWith('URGENT_');
            
            typeKey  = NOTIF_ICONS[typeKey] ? typeKey : 'default';
            const icon     = NOTIF_ICONS[typeKey] || 'bell';
            const label    = (isUrgent ? '⚡ URGENT : ' : '') + (NOTIF_LABELS[typeKey] || 'Notification');
            const time     = timeAgo(parseInt(n.age_minutes) || 0);
            let href = n.url_action ? n.url_action : '#';
            if (href !== '#' && !href.startsWith('http') && !href.startsWith('/')) {
                href = '/aptus_first_official_version/view/frontoffice/' + href;
            }
            
            const urgentStyle = isUrgent ? 'border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.02);' : '';

            html += `
            <a href="${href}" class="notif-item unread" onclick="markOneRead(${n.id}, this)" style="${urgentStyle}">
                <div class="notif-item__icon type-${typeKey}" ${isUrgent ? 'style="background:#ef4444; color:#fff;"' : ''}>
                    <i data-lucide="${icon}" style="width:18px;height:18px;"></i>
                </div>
                <div class="notif-item__body">
                    <div class="notif-item__type" ${isUrgent ? 'style="color:#ef4444; font-weight:900;"' : ''}>${label}</div>
                    <div class="notif-item__msg">${n.message}</div>
                    <span class="notif-item__time">🕐 ${time}</span>
                </div>
            </a>`;
        });
        list.innerHTML = html;
        lucide.createIcons();
    }

    function markOneRead(id, el) {
        el.classList.remove('unread');
        const formData = new FormData();
        formData.append('action', 'mark_notifications_read');
        formData.append('notif_id', id);
        fetch('/aptus_first_official_version/view/frontoffice/ajax_handler.php', { method: 'POST', body: formData });
    }

    function markAllRead() {
        const formData = new FormData();
        formData.append('action', 'mark_notifications_read');
        fetch('/aptus_first_official_version/view/frontoffice/ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) updateNotifUI([]); });
    }

    function deleteAllNotifications() {
        const formData = new FormData();
        formData.append('action', 'delete_all_notifications');
        fetch('/aptus_first_official_version/view/frontoffice/ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Force empty state update directly
                _prevCount = 0;
                document.getElementById('notif-badge').style.display = 'none';
                document.getElementById('notif-head-count').textContent = '0';
                document.getElementById('bell-pulse').style.display = 'none';
                document.getElementById('notif-bell-icon').classList.remove('bell-has-notif');
                document.getElementById('notif-items').innerHTML = `
                    <div class="notif-empty">
                        <div class="notif-empty__icon">${_dndMode ? '🌙' : '✅'}</div>
                        <p>${_dndMode ? "Mode Silence : Seules les alertes critiques s'afficheront." : "Vous êtes à jour !<br>Aucune nouvelle notification."}</p>
                    </div>`;
            }
        });
    }

    // Initial load + poll every 30s
    fetchNotifications();
    setInterval(fetchNotifications, 30000);

    // --- ACCESSIBILITY : TEXT-TO-SPEECH (TTS) SYSTEM ---
    const TTS = {
        synth: window.speechSynthesis,
        isSpeaking: false,
        isUniversalMode: false,
        
        toggleUniversal: function() {
            this.isUniversalMode = !this.isUniversalMode;
            const btn = document.getElementById('tts-universal-btn');
            if (this.isUniversalMode) {
                btn.style.background = 'var(--accent-primary)';
                btn.style.color = 'white';
                document.body.style.cursor = 'help';
                Toast.fire({ icon: 'info', title: 'Mode Lecture activé : Cliquez sur un texte pour l\'écouter.' });
                this.initUniversalEvents();
            } else {
                btn.style.background = 'var(--bg-card)';
                btn.style.color = 'var(--text-primary)';
                document.body.style.cursor = 'default';
                this.synth.cancel();
                Toast.fire({ icon: 'success', title: 'Mode Lecture désactivé.' });
            }
        },

        initUniversalEvents: function() {
            document.addEventListener('click', (e) => {
                if (!this.isUniversalMode) return;
                
                // On ignore les clics sur les boutons de navigation
                if (e.target.closest('nav') || e.target.closest('button')) return;

                const text = e.target.innerText || e.target.textContent;
                if (text && text.length > 3) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.speak(text);
                }
            }, { capture: true });
        },
        
        speak: function(text, btn = null) {
            this.synth.cancel(); // Stop previous speech
            if (text.length === 0) return;

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            utterance.rate = 1.0;

            utterance.onstart = () => {
                this.isSpeaking = true;
                if (btn) btn.innerHTML = '<i data-lucide="square" style="width:16px;"></i>';
                lucide.createIcons();
            };

            utterance.onend = () => {
                this.isSpeaking = false;
                if (btn) btn.innerHTML = '<i data-lucide="volume-2" style="width:16px;"></i>';
                lucide.createIcons();
            };

            this.synth.speak(utterance);
        },

        readElement: function(elementId, btn = null) {
            const el = document.getElementById(elementId);
            if (!el) return;
            this.speak(el.innerText || el.textContent, btn);
        }
    };
  </script>

  <!-- Floating Accessibility Tool -->
  <button id="tts-universal-btn" onclick="TTS.toggleUniversal()" 
          style="position: fixed; bottom: 2rem; left: 2rem; z-index: 9999; width: 45px; height: 45px; border-radius: 12px; background: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-md); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;"
          title="Activer le mode Lecture (TTS Universel)">
      <i data-lucide="headphones" style="width: 20px;"></i>
  </button>
</body>
</html>
