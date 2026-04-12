<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription Candidat — Aptus</title>
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
    <div class="auth-card auth-card--wide">
      <!-- Logo -->
      <div class="auth-card__logo">
        <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon" style="background:none;padding:4px;">
        <span class="auth-card__logo-text">Aptus</span>
      </div>

      <!-- Header -->
      <div class="auth-card__header">
        <span class="auth-card__type-badge candidat">
          <i data-lucide="user" style="width:14px;height:14px;"></i>
          Candidat
        </span>
        <h1>Créer votre compte</h1>
        <p>Rejoignez la communauté et trouvez votre emploi idéal</p>
      </div>

      <!-- Progress Steps -->
      <div class="steps">
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
      </div>

      <!-- Signup Form -->
      <div class="auth-form" id="signup-candidat-form">

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-nom">Nom</label>
            <input type="text" class="input" id="candidat-nom" name="nom" placeholder="Votre nom">
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-prenom">Prénom</label>
            <input type="text" class="input" id="candidat-prenom" name="prenom" placeholder="Votre prénom">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="candidat-email">Adresse Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" id="candidat-email" name="email" placeholder="votre@email.com">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-password">Mot de passe</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="candidat-password" name="password" placeholder="Min. 8 caractères">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-password2">Confirmer</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="candidat-password2" name="password_confirm" placeholder="Confirmez">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="candidat-tel">Téléphone</label>
          <div class="input-icon-wrapper">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
            <input type="tel" class="input" id="candidat-tel" name="telephone" placeholder="+216 XX XXX XXX">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Compétences</label>
          <div class="tag-input" id="skills-tag-input">
            <div class="tag-input__tags"></div>
            <input type="text" class="tag-input__field" placeholder="Tapez une compétence puis Entrée...">
            <input type="hidden" class="tag-input__hidden" name="competences">
          </div>
          <span class="form-hint">Appuyez sur Entrée ou virgule pour ajouter</span>
        </div>

        <div class="form-group">
          <label class="form-label">CV (PDF)</label>
          <div class="drop-zone" id="cv-drop-zone">
            <input type="file" class="drop-zone__input" name="cv" accept=".pdf,.doc,.docx">
            <div class="drop-zone__prompt">
              <i data-lucide="upload-cloud" style="width:32px;height:32px;"></i>
              <span>Déposez votre CV ici ou <span class="text-accent">parcourir</span></span>
              <span class="text-xs text-tertiary">PDF, DOC, DOCX — Max. 5MB</span>
            </div>
            <div class="drop-zone__preview"></div>
          </div>
        </div>

        <a href="jobs_feed.php" class="btn btn-primary btn-lg w-full" style="text-decoration:none;" id="signup-candidat-submit">
          <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
          Créer mon compte
        </a>
      </div>

      <div class="auth-footer">
        Déjà inscrit ? <a href="login.php">Se connecter</a>
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
