<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription Admin — Aptus</title>
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
      <div class="auth-card__logo">
        <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon" style="background:none;padding:4px;">
        <span class="auth-card__logo-text">Aptus</span>
      </div>

      <div class="auth-card__header">
        <span class="auth-card__type-badge admin">
          <i data-lucide="shield-check" style="width:14px;height:14px;"></i>
          Administrateur
        </span>
        <h1>Accès Administrateur</h1>
        <p>Inscription réservée au personnel autorisé</p>
      </div>

      <!-- Security Notice Removed -->

      <div class="auth-form" id="signup-admin-form">

        <div class="form-group">
          <label class="form-label" for="admin-nom">Nom complet</label>
          <div class="input-icon-wrapper">
            <i data-lucide="user" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="admin-nom" name="nom" placeholder="Nom complet">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-email">Email Administrateur</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" id="admin-email" name="email" placeholder="admin@aptus.com">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password">Mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="admin-password" name="password" placeholder="Min. 12 caractères">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password2">Confirmer le mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="admin-password2" name="password_confirm" placeholder="Confirmez">
          </div>
        </div>

        <a href="../backoffice/dashboard.php" class="btn btn-primary btn-lg w-full" style="text-decoration:none;" id="signup-admin-submit">
          <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
          Créer le compte admin
        </a>
      </div>

      <div class="auth-footer">
        <a href="login.php">← Retour à la connexion</a>
      </div>

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


