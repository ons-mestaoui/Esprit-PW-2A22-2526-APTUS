<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <!-- Logo -->
      <div class="auth-card__logo">
        <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon" style="background:none;padding:4px;">
        <span class="auth-card__logo-text">Aptus</span>
      </div>

      <!-- Header -->
      <div class="auth-card__header">
        <h1>Bienvenue !</h1>
        <p>Connectez-vous à votre compte pour continuer</p>
      </div>

      <!-- Login Form -->
      <div class="auth-form" id="login-form">
        <div class="form-group">
          <label class="form-label" for="login-email">Adresse Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" id="login-email" name="email" placeholder="votre@email.com">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-password">Mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="login-password" name="password" placeholder="••••••••">
          </div>
        </div>

        <div class="flex items-center justify-between" style="margin: -4px 0;">
          <label class="flex items-center gap-2" style="cursor:pointer;">
            <input type="checkbox" id="remember-me" style="accent-color:var(--accent-primary);">
            <span class="text-sm text-secondary">Se souvenir de moi</span>
          </label>
        </div>
      </div>

      <!-- Footer -->
      <div class="auth-footer">
        Pas de compte ? <a href="signup_choice.php">S'inscrire gratuitement</a>
        <a href="landing.php" class="back-to-site">
          <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
          Retour au site
        </a>
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
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>


