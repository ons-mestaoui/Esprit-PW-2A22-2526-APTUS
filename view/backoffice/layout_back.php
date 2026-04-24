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
  <link rel="stylesheet" href="../assets/css/variables.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/layout_back.css">
  <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="../assets/css/<?php echo $pageCSS; ?>">
  <?php endif; ?>
  <link rel="stylesheet" href="../assets/css/cv_premium.css">

  <!-- Theme Toggle (load early to avoid flash) -->
  <script src="../assets/js/theme-toggle.js"></script>
</head>
<body>

  <div class="backoffice">

    <!-- ═══════════════════════════════════════════
         LEFT SIDEBAR
         ═══════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">
      <!-- Logo -->
      <div class="sidebar__header">
        <img src="../assets/img/logo.png" alt="Aptus" class="sidebar__logo-icon" style="background:none;padding:2px;">
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
  <script src="../assets/js/nav.js"></script>
  <script src="../assets/js/forms.js"></script>
  <script src="../assets/js/charts.js"></script>
  <?php if (isset($pageJS)): ?>
    <script src="../assets/js/<?php echo $pageJS; ?>"></script>
  <?php endif; ?>
  <script>lucide.createIcons();</script>
  <!-- Modal Confirmation Suppression -->
  <div class="aptus-modal-overlay" id="confirm-modal">
      <div class="aptus-modal-content" style="max-width:450px; text-align:center; padding: 40px 32px; position:relative;">
          <button class="modal-close-btn" onclick="document.getElementById('confirm-modal').classList.remove('active')" style="position:absolute; top:20px; right:20px; background:none; border:none; color:var(--text-tertiary); cursor:pointer;">
            <i data-lucide="x" style="width:24px;height:24px;"></i>
          </button>

          <div style="width:64px; height:64px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
              <i data-lucide="alert-triangle" style="width:32px;height:32px;"></i>
          </div>

          <h3 id="confirm-title" style="margin-bottom:12px; color:var(--text-primary); font-size:1.5rem; font-weight:800;">Confirmation de suppression</h3>
          <p id="confirm-text" style="color:var(--text-secondary); margin-bottom:24px; line-height:1.6;">Êtes-vous sûr de vouloir continuer ? Cette action est irréversible.</p>

          <div style="display:flex; gap:12px; justify-content:center;">
              <button type="button" class="btn btn-secondary" style="flex:1; border-radius:12px; font-weight:700;" onclick="document.getElementById('confirm-modal').classList.remove('active')">Annuler</button>
              <button type="button" id="confirm-ok" class="btn btn-primary" style="flex:1; background:#ef4444; border-color:#ef4444; color:white; border-radius:12px; font-weight:700; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);">Oui, Supprimer</button>
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

            if(title) titleEl.textContent = title;
            if(text) textEl.textContent = text;
            
            modal.classList.add('active');
            if(window.lucide) lucide.createIcons();

            const cleanup = (val) => {
                modal.classList.remove('active');
                okBtn.onclick = null;
                resolve(val);
            };

            okBtn.onclick = () => cleanup(true);
            modal.onclick = (e) => { if(e.target === modal) cleanup(false); };
        });
    };
  </script>
</body>
</html>
