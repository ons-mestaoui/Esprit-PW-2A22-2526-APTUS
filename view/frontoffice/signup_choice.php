<!DOCTYPE html>
<html lang="fr" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rejoindre Aptus — Choisissez votre profil</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <style>
    :root {
      --card-bg: var(--bg-card);
      --card-hover-border: var(--accent-primary);
    }

    .role-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: var(--space-4);
      margin-top: var(--space-6);
      margin-bottom: var(--space-6);
    }

    @media (max-width: 580px) {
      .role-grid {
        grid-template-columns: 1fr;
      }
    }

    .role-card {
      position: relative;
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      padding: var(--space-5);
      text-align: left;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      gap: var(--space-3);
      overflow: hidden;
    }

    .role-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: linear-gradient(135deg, var(--accent-primary), transparent);
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 0;
    }

    .role-card:hover {
      border-color: var(--card-hover-border);
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    }

    .role-card:hover::before {
      opacity: 0.03;
    }

    .role-icon {
      position: relative;
      z-index: 1;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: var(--bg-tertiary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-primary);
      margin-bottom: var(--space-2);
      transition: transform 0.3s;
    }

    .role-card:hover .role-icon {
      transform: scale(1.1) rotate(-5deg);
      background: var(--accent-primary);
      color: white;
    }

    .role-title {
      position: relative;
      z-index: 1;
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
      color: var(--text-primary);
    }

    .role-desc {
      position: relative;
      z-index: 1;
      font-size: var(--fs-xs);
      color: var(--text-secondary);
      line-height: 1.5;
    }

    /* Demo Card specific styles */
    .role-card--demo {
      border: 2px dashed var(--border-color);
      background: rgba(var(--accent-primary-rgb), 0.02);
    }

    .role-card--demo:hover {
      border-style: solid;
      background: var(--bg-card);
    }

    .demo-badge {
      position: absolute;
      top: var(--space-3);
      right: var(--space-3);
      background: var(--gradient-primary);
      color: white;
      font-size: 10px;
      font-weight: 800;
      padding: 2px 8px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .auth-card__header h1 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .accent-font { font-family: 'Outfit', sans-serif; }
  </style>
</head>

<body>

  <div class="auth-page">
    <div class="auth-card" style="max-width: 680px;">
      <!-- Logo -->
      <div class="auth-card__logo">
        <a href="landing.php"
          style="display:flex;align-items:center;gap:var(--space-2);text-decoration:none;color:inherit;">
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon"
            style="background:none;padding:4px;">
          <span class="auth-card__logo-text accent-font">Aptus</span>
        </a>
      </div>

      <!-- Header -->
      <div class="auth-card__header">
        <h1>Bienvenue sur Aptus AI</h1>
        <p>Sélectionnez votre profil pour commencer l'aventure ou essayez la démo.</p>
      </div>

      <!-- Roles Selection -->
      <div class="role-grid">
        <!-- Candidat -->
        <a href="signup_candidat.php" class="role-card">
          <div class="role-icon">
            <i data-lucide="user" style="width:22px;height:22px;"></i>
          </div>
          <div class="role-title">Candidat</div>
          <div class="role-desc">Trouvez le job idéal et développez vos compétences avec l'IA.</div>
        </a>

        <!-- Entreprise -->
        <a href="signup_entreprise.php" class="role-card">
          <div class="role-icon" style="--accent-primary: var(--accent-secondary);">
            <i data-lucide="building-2" style="width:22px;height:22px;"></i>
          </div>
          <div class="role-title">Entreprise</div>
          <div class="role-desc">Recrutez les meilleurs talents et gérez vos offres d'emploi.</div>
        </a>

        <!-- Tuteur (New!) -->
        <a href="signup_candidat.php?role=tuteur" class="role-card">
          <div class="role-icon" style="--accent-primary: var(--accent-tertiary);">
            <i data-lucide="graduation-cap" style="width:22px;height:22px;"></i>
          </div>
          <div class="role-title">Tuteur / Expert</div>
          <div class="role-desc">Transmettez votre savoir et accompagnez les apprenants.</div>
        </a>

        <!-- Demo Mode (The missing piece!) -->
        <a href="formations_catalog.php?user_id=10" class="role-card role-card--demo">
          <span class="demo-badge">Accès Direct</span>
          <div class="role-icon" style="background: var(--bg-surface); color: var(--text-secondary);">
            <i data-lucide="play-circle" style="width:22px;height:22px;"></i>
          </div>
          <div class="role-title">Mode Démo</div>
          <div class="role-desc">Explorez le catalogue et les fonctionnalités sans créer de compte.</div>
        </a>
      </div>

      <!-- Footer -->
      <div class="auth-footer" style="text-align: center; margin-top: var(--space-2);">
        Vous avez déjà un compte ? <a href="login.php" style="font-weight: 600;">Se connecter</a>
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