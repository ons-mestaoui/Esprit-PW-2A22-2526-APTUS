<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Aptus — Panneau d'administration. Gérez les utilisateurs, offres, formations et statistiques.">
  <title><?php echo isset($pageTitle) ? $pageTitle . ' — Aptus Admin' : 'Aptus Admin'; ?></title>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/layout_back.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>

  <!-- Quill JS for Rich Text Editor -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <!-- Theme Toggle (load early to avoid flash) -->
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body>

  <div class="backoffice">

    <!-- ═══════════════════════════════════════════
         LEFT SIDEBAR
         ═══════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">
      <!-- Logo -->
      <div class="sidebar__header">
        <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="sidebar__logo-icon" style="background:none;padding:2px;">
        <span class="sidebar__logo-text">Aptus</span>
      </div>

      <!-- Navigation -->
      <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
      <nav class="sidebar__nav">
        <div class="sidebar__section-label">Principal</div>

        <a href="dashboard.php" class="sidebar-link<?php echo ($currentPage==='dashboard.php')?' active':''; ?>" id="sidebar-dashboard">
          <i data-lucide="layout-dashboard"></i>
          <span>Dashboard</span>
        </a>
        <a href="users.php" class="sidebar-link<?php echo ($currentPage==='users.php')?' active':''; ?>" id="sidebar-users">
          <i data-lucide="users"></i>
          <span>Utilisateurs</span>
        </a>
        <a href="veille_admin.php" class="sidebar-link<?php echo ($currentPage==='veille_admin.php')?' active':''; ?>" id="sidebar-veille">
          <i data-lucide="line-chart"></i>
          <span>Veille Marché</span>
        </a>
        <a href="cv_templates_admin.php" class="sidebar-link<?php echo ($currentPage==='cv_templates_admin.php')?' active':''; ?>" id="sidebar-templates">
          <i data-lucide="file-badge"></i>
          <span>Templates CV</span>
        </a>
        <a href="formations_admin.php" class="sidebar-link<?php echo ($currentPage==='formations_admin.php')?' active':''; ?>" id="sidebar-formations">
          <i data-lucide="graduation-cap"></i>
          <span>Formations</span>
        </a>
    
        <a href="offres_admin.php" class="sidebar-link<?php echo ($currentPage==='offres_admin.php')?' active':''; ?>" id="sidebar-offres">
          <i data-lucide="briefcase"></i>
          <span>Offres Disponibles</span>
        </a>
        <a href="posts_stats.php" class="sidebar-link<?php echo ($currentPage==='posts_stats.php')?' active':''; ?>" id="sidebar-posts">
          <i data-lucide="bar-chart-3"></i>
          <span>Posts &amp; Stats</span>
        </a>
      </nav>

      <!-- Footer / Logout -->
      <div class="sidebar__footer">
        <a href="../frontoffice/login.php" class="sidebar__logout" id="sidebar-logout">
          <i data-lucide="log-out"></i>
          <span>Déconnexion</span>
        </a>
      </div>
    </aside>

    <!-- ═══════════════════════════════════════════
         TOP HEADER BAR
         ═══════════════════════════════════════════ -->
    <header class="back-topbar" id="back-topbar">
      <!-- Sidebar Toggle -->
      <button class="back-topbar__toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <i data-lucide="menu"></i>
      </button>

      <!-- Search -->
      <div class="back-topbar__search">
        <i data-lucide="search" style="width:18px;height:18px;"></i>
        <input type="text" class="input" id="admin-search" placeholder="Rechercher des candidats, entreprises, formations...">
      </div>

      <!-- Actions -->
      <div class="back-topbar__actions">
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="admin-theme-toggle" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>

        <!-- Notifications -->
        <button class="btn-icon" style="position:relative;" aria-label="Notifications">
          <i data-lucide="bell" style="width:20px;height:20px;color:var(--text-secondary);"></i>
          <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;background:var(--accent-tertiary);border-radius:50%;border:2px solid var(--bg-topbar);"></span>
        </button>

        <!-- Admin Profile Dropdown -->
        <div class="dropdown" id="admin-dropdown">
          <div class="dropdown-trigger back-topbar__admin">
            <div class="back-topbar__admin-info">
              <span class="back-topbar__admin-name"><?php echo isset($adminName) ? $adminName : 'Administrateur'; ?></span>
              <span class="back-topbar__admin-role">Super Admin</span>
            </div>
            <div class="avatar avatar-initials" style="width:36px;height:36px;font-size:13px;">
              <?php echo isset($adminName) ? strtoupper(substr($adminName, 0, 2)) : 'AD'; ?>
            </div>
          </div>
          <div class="dropdown-menu">
            <a href="profil_admin.php" class="dropdown-item">
              <i data-lucide="user" style="width:16px;height:16px;"></i>
              Mon Profil
            </a>
            <a href="settings_admin.php" class="dropdown-item">
              <i data-lucide="settings" style="width:16px;height:16px;"></i>
              Paramètres
            </a>
            <div class="dropdown-divider"></div>
            <a href="../frontoffice/login.php" class="dropdown-item" style="color:var(--accent-tertiary);">
              <i data-lucide="log-out" style="width:16px;height:16px;"></i>
              Déconnexion
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- 3. Modal Confirmation Suppression (Exact Design) -->
    <div class="modal-overlay" id="modal-delete" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:10000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:var(--bg-card); border-radius:24px; max-width:450px; text-align:center; padding: 40px 32px; position:relative; border:1px solid var(--border-color); box-shadow:var(--shadow-2xl);">
            <button class="modal-close" onclick="closeModals()" style="position:absolute; top:20px; right:20px; color:var(--text-tertiary); background:none; border:none; cursor:pointer;"><i data-lucide="x" style="width:24px;height:24px;"></i></button>

            <div style="width:64px; height:64px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i data-lucide="alert-triangle" style="width:32px;height:32px;"></i>
            </div>

            <h3 style="margin-bottom:12px; color:var(--text-primary); font-size:1.5rem; font-weight:700;">Confirmation de suppression</h3>
            <p id="delete-modal-msg" style="color:var(--text-secondary); margin-bottom:24px; line-height:1.6;">Êtes-vous sûr de vouloir continuer ? Cette action est irréversible.</p>

            <form action="" method="POST" id="form-delete">
                <input type="hidden" name="action" id="delete-action" value="delete">
                <input type="hidden" name="delete_id" id="delete-id-field" value="">

                <div style="display:flex; gap:12px; justify-content:center;">
                    <button type="button" class="btn btn-secondary" style="flex:1; border-radius:12px; padding:12px;" onclick="closeModals()">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; background:#ef4444; border-color:#ef4444; color:white; border-radius:12px; padding:12px; font-weight:600;">Oui, Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'Alerte Simple (Design Aptus) -->
    <div class="modal-overlay" id="modal-alert" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:10000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:var(--bg-card); border-radius:24px; max-width:400px; text-align:center; padding: 40px 32px; position:relative; border:1px solid var(--border-color); box-shadow:var(--shadow-2xl);">
            <div id="alert-icon-box" style="width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i id="alert-icon" data-lucide="info" style="width:28px;height:28px;"></i>
            </div>
            <h3 id="alert-title" style="margin-bottom:12px; color:var(--text-primary);">Message</h3>
            <p id="alert-msg" style="color:var(--text-secondary); margin-bottom:24px; line-height:1.6;"></p>
            <button onclick="closeModals()" class="btn btn-primary" style="padding:10px 30px; width:100%;">Continuer</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         MAIN CONTENT
         ═══════════════════════════════════════════ -->
    <main class="back-main" id="main-content">
      <div class="back-content">
        <?php
          if (isset($content)) {
            include $content;
          }
        ?>
      </div>
    </main>

  </div><!-- /.backoffice -->

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="/aptus_first_official_version/view/assets/js/nav.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/charts.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="/aptus_first_official_version/view/assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script>
    lucide.createIcons();

    // ── Gestion des Modals Aptus ──
    let deleteCallback = null;

    function closeModals() {
        // Liste de tous les IDs de modals possibles dans le backoffice
        const modalIds = ['modal-delete', 'modal-alert', 'add-formation-modal', 'modal-creneau', 'modal-creneau-action', 'modal-formation-detail'];
        
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('active');
                // On attend la fin de l'animation CSS (0.3s) avant de masquer
                setTimeout(() => { 
                    if (!modal.classList.contains('active')) {
                        modal.style.display = 'none'; 
                    }
                }, 300);
            }
        });
    }

    function aptusConfirmDelete(param, message = null) {
        const formDelete = document.getElementById('form-delete');
        const modalDelete = document.getElementById('modal-delete');
        if (!formDelete || !modalDelete) return;

        if (typeof param === 'function') {
            deleteCallback = param;
            formDelete.onsubmit = (e) => {
                e.preventDefault();
                deleteCallback();
                closeModals();
            };
        } else {
            deleteCallback = null;
            formDelete.onsubmit = null;
            
            // Extraction robuste de l'ID depuis l'URL ou passage direct d'ID
            let id = param;
            if (typeof param === 'string' && param.includes('delete_id=')) {
                id = param.split('delete_id=')[1].split('&')[0];
            }
            
            document.getElementById('delete-id-field').value = id;
            formDelete.action = window.location.pathname; // On reste sur la même page
        }

        if (message) document.getElementById('delete-modal-msg').textContent = message;
        
        modalDelete.style.display = 'flex';
        setTimeout(() => {
            modalDelete.classList.add('active');
        }, 10);
        
        if (window.lucide) lucide.createIcons();
    }

    function aptusAlert(message, type = 'success') {
        const modal = document.getElementById('modal-alert');
        const iconBox = document.getElementById('alert-icon-box');
        const icon = document.getElementById('alert-icon');
        const title = document.getElementById('alert-title');
        
        if (!modal || !iconBox || !icon || !title) return;

        document.getElementById('alert-msg').textContent = message;
        
        if (type === 'error') {
            iconBox.style.background = 'rgba(239,68,68,0.1)';
            iconBox.style.color = '#ef4444';
            icon.setAttribute('data-lucide', 'x-circle');
            title.textContent = 'Erreur';
        } else {
            iconBox.style.background = 'rgba(16,185,129,0.1)';
            iconBox.style.color = '#10b981';
            icon.setAttribute('data-lucide', 'check-circle');
            title.textContent = 'Succès';
        }
        
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        if (window.lucide) lucide.createIcons();
    }

    <?php if (isset($_SESSION['flash_success'])): ?>
      aptusAlert(<?php echo json_encode($_SESSION['flash_success']); ?>, 'success');
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
      aptusAlert(<?php echo json_encode($_SESSION['flash_error']); ?>, 'error');
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    // ── Système de Notifications Aptus ──
    let lastNotifCount = 0;

    function playNotifTick() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
            osc.start();
            osc.stop(ctx.currentTime + 0.1);
        } catch(e) { console.error("Audio block:", e); }
    }

    function checkNotifications() {
        fetch('../frontoffice/ajax_handler.php?action=get_notifications')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.notifications) {
                    const count = data.notifications.length;
                    if (count > lastNotifCount) {
                        playNotifTick(); // Joue le son "tick"
                    }
                    lastNotifCount = count;
                    // Mise à jour de la pastille rouge
                    const badge = document.querySelector('.btn-icon span');
                    if (badge) badge.style.display = count > 0 ? 'block' : 'none';
                }
            });
    }

    // Vérifier toutes les 30 secondes
    setInterval(checkNotifications, 30000);
    checkNotifications(); // Première vérification immédiate
  </script>
</body>
</html>
