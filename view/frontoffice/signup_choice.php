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
    
    .auth-card h1 {
      font-size: var(--fs-xl);
      font-weight: 800;
      margin-bottom: var(--space-1);
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-align: center;
    }
    .auth-card p {
      margin-bottom: var(--space-6);
      color: var(--text-secondary);
      font-size: var(--fs-sm);
      text-align: center;
    }
    .social-auth-container {
      display: flex;
      justify-content: center;
      gap: var(--space-4);
      width: 100%;
      margin-top: var(--space-4);
    }
    .btn-social {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-3);
      padding: var(--space-3) var(--space-4);
      border-radius: var(--radius-full);
      border: 1px solid var(--border-color);
      background: var(--bg-secondary);
      color: var(--text-primary);
      font-weight: 600;
      font-size: var(--fs-sm);
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      text-decoration: none;
    }
    
    [data-theme="dark"] .btn-social {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.1);
    }

    .btn-social:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: var(--shadow-lg);
      border-color: var(--accent-primary);
      background: var(--bg-card);
    }
    
    [data-theme="dark"] .btn-social:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--accent-primary);
    }

    .btn-social.google:hover {
      background: rgba(234, 67, 53, 0.08);
      border-color: rgba(234, 67, 53, 0.4);
      color: #EA4335 !important;
    }
    .btn-social.github:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--text-primary);
      color: var(--text-primary) !important;
    }
    .social-icon {
      width: 20px;
      height: 20px;
      transition: transform 0.3s ease;
      fill: currentColor;
    }
    .btn-social:hover .social-icon {
      transform: rotate(10deg);
    }

    .divider {
      display: flex;
      align-items: center;
      gap: var(--space-4);
      width: 100%;
      margin-top: var(--space-8);
      color: var(--text-tertiary);
      font-size: var(--fs-xs);
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
      opacity: 0.6;
    }
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border-color);
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

        <a href="signup_tuteur.php" class="role-card">
          <div class="role-icon" style="color: var(--accent-tertiary, #10B981);">
            <i data-lucide="graduation-cap" style="width:24px;height:24px;"></i>
          </div>
          <div class="role-title">Tuteur</div>
          <div class="role-desc">Encadrez les étudiants et suivez leur progression académique.</div>
        </a>

      </div>


      <!-- Footer -->
      <div class="auth-footer">
        <div style="margin-bottom: var(--space-2);">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></div>
        <a href="landing.php" class="back-to-site">
          <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
          Retour au site
        </a>
      </div>

      <!-- Theme toggle -->
      <div style="position:absolute;top:var(--space-4);right:var(--space-4);">
        <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
