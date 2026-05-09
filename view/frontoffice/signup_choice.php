<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>S'inscrire — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <style>
    .role-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: var(--space-4);
      margin-top: var(--space-6);
      margin-bottom: var(--space-6);
    }
    .role-card {
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      padding: var(--space-5);
      text-align: center;
      transition: all var(--transition-fast);
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--space-3);
    }
    .role-card:hover {
      border-color: var(--accent-primary);
      background: var(--bg-hover);
      transform: translateY(-2px);
    }
    .role-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: var(--bg-tertiary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-primary);
    }
    .role-title {
      font-weight: 600;
      font-size: var(--fs-md);
    }
    .role-desc {
      font-size: var(--fs-xs);
      color: var(--text-secondary);
    }
  </style>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card" style="max-width: 700px;">
      <!-- Logo -->
      <div class="auth-card__logo">
        <a href="landing.php" style="display:flex;align-items:center;gap:var(--space-2);text-decoration:none;color:inherit;">
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon" style="background:none;padding:4px;">
          <span class="auth-card__logo-text">Aptus</span>
        </a>
      </div>

      <!-- Header -->
      <div class="auth-card__header">
        <h1>Rejoignez Aptus</h1>
        <p>Sélectionnez votre profil pour commencer l'aventure</p>
      </div>

      <!-- Roles Selection -->
      <div class="role-grid">
        <a href="signup_candidat.php" class="role-card">
          <div class="role-icon">
            <i data-lucide="user" style="width:24px;height:24px;"></i>
          </div>
          <div class="role-title">Candidat</div>
          <div class="role-desc">Trouvez le job idéal et développez vos compétences avec l'IA.</div>
        </a>

        <a href="signup_entreprise.php" class="role-card">
          <div class="role-icon" style="color: var(--accent-secondary);">
            <i data-lucide="building-2" style="width:24px;height:24px;"></i>
          </div>
          <div class="role-title">Entreprise</div>
          <div class="role-desc">Recrutez les meilleurs talents et déposez vos offres.</div>
        </a>

        <a href="signup_admin.php" class="role-card">
          <div class="role-icon" style="color: var(--accent-tertiary);">
            <i data-lucide="shield" style="width:24px;height:24px;"></i>
          </div>
          <div class="role-title">Admin</div>
          <div class="role-desc">Gérez la plateforme, les utilisateurs et les statistiques.</div>
        </a>
      </div>

      <!-- Footer -->
      <div class="auth-footer" style="text-align: center;">
        Vous avez déjà un compte ? <a href="login.php">Se connecter</a>
      </div>

      <!-- Theme toggle -->
      <div style="position:absolute;top:var(--space-4);right:var(--space-4);">
        <button class="theme-toggle" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
