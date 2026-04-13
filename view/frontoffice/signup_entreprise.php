<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription Entreprise — Aptus</title>
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
      <div class="auth-card__logo">
        <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="auth-card__logo-icon" style="background:none;padding:4px;">
        <span class="auth-card__logo-text">Aptus</span>
      </div>

      <div class="auth-card__header">
        <span class="auth-card__type-badge entreprise">
          <i data-lucide="building-2" style="width:14px;height:14px;"></i>
          Entreprise
        </span>
        <h1>Inscrivez votre entreprise</h1>
        <p>Accédez à un vivier de talents qualifiés et boostez vos recrutements</p>
      </div>

      <div class="steps">
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
      </div>

      <div class="auth-form" id="signup-entreprise-form">

        <div class="form-group">
          <label class="form-label" for="ent-nom">Nom de l'entreprise</label>
          <div class="input-icon-wrapper">
            <i data-lucide="building-2" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="ent-nom" name="nom_entreprise" placeholder="Ex: TechSphere Inc.">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-secteur">Secteur d'activité</label>
            <select class="select" id="ent-secteur" name="secteur">
              <option value="">Sélectionnez...</option>
              <option value="tech">Technologie</option>
              <option value="finance">Finance & Banque</option>
              <option value="sante">Santé</option>
              <option value="education">Éducation</option>
              <option value="commerce">Commerce & Retail</option>
              <option value="industrie">Industrie</option>
              <option value="services">Services</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-taille">Taille</label>
            <select class="select" id="ent-taille" name="taille">
              <option value="">Sélectionnez...</option>
              <option value="1-10">1-10 employés</option>
              <option value="11-50">11-50 employés</option>
              <option value="51-200">51-200 employés</option>
              <option value="201-500">201-500 employés</option>
              <option value="500+">500+ employés</option>
            </select>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-email">Email Professionnel</label>
            <div class="input-icon-wrapper">
              <i data-lucide="mail" style="width:18px;height:18px;"></i>
              <input type="email" class="input" id="ent-email" name="email" placeholder="contact@entreprise.com">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-site">Site Web</label>
            <div class="input-icon-wrapper">
              <i data-lucide="globe" style="width:18px;height:18px;"></i>
              <input type="url" class="input" id="ent-site" name="site_web" placeholder="https://www.entreprise.com">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-password">Mot de passe</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="ent-password" name="password" placeholder="Min. 8 caractères">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-password2">Confirmer</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="ent-password2" name="password_confirm" placeholder="Confirmez">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="ent-description">Description de l'entreprise</label>
          <textarea class="textarea" id="ent-description" name="description" rows="3" placeholder="Décrivez brièvement votre entreprise et sa mission..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Logo de l'entreprise</label>
          <div class="drop-zone" id="logo-drop-zone">
            <input type="file" class="drop-zone__input" name="logo" accept="image/*">
            <div class="drop-zone__prompt">
              <i data-lucide="image" style="width:32px;height:32px;"></i>
              <span>Déposez votre logo ici ou <span class="text-accent">parcourir</span></span>
              <span class="text-xs text-tertiary">PNG, JPG, SVG — Max. 2MB</span>
            </div>
            <div class="drop-zone__preview"></div>
          </div>
        </div>

        <a href="hr_posts.php" class="btn btn-primary btn-lg w-full" style="text-decoration:none;" id="signup-entreprise-submit">
          <i data-lucide="building-2" style="width:18px;height:18px;"></i>
          Inscrire l'entreprise
        </a>
      </div>

      <div class="auth-footer">
        Déjà inscrit ? <a href="login.php">Se connecter</a>
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
