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
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../controller/UtilisateurC.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $competences = $_POST['competences'] ?? '';
    $niveauEtudes = $_POST['niveauEtudes'] ?? null;
    $niveau = $_POST['niveau'] ?? null;
    
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? '');
    $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;

    if ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'adresse email est invalide.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        $utilisateurC = new UtilisateurC();
        if ($utilisateurC->emailExists($email)) {
            $error = "Cette adresse email est déjà utilisée par un autre compte.";
        } else {
            $utilisateur = new Utilisateur(0, $nom, $prenom, $email, $password, 'Candidat', $telephone);
            $last_id = $utilisateurC->addUtilisateur($utilisateur);

            if ($last_id) {
                // Add Candidat details
                try {
                    include_once __DIR__ . '/../../controller/CandidatC.php';
                    include_once __DIR__ . '/../../controller/ProfilC.php';
                    $candidatC = new CandidatC();
                    $profilC = new ProfilC();
                    
                    $c = new Candidat($last_id, $competences, $niveauEtudes, $niveau);
                    $candidatC->addCandidat($c);

                    $p = new Profil(null, $last_id, null, null, $adresse, $ville, $pays, $date_naissance, $linkedin, null);
    $profilC->addProfil($p);

                    // Set Session for Auto-Login
                    $_SESSION['id_utilisateur'] = $last_id;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['role'] = 'Candidat';

                    // Redirect directly to the feed
                    header("Location: jobs_feed.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Erreur d'inscription du candidat: " . $e->getMessage();
                }
            } else {
                $error = "Erreur lors de la création de l'utilisateur.";
            }
        }
    }
}
?>

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
      <?php if (!empty($error)): ?>
        <!-- 
           Note Académique : Conformément aux consignes, nous n'utilisons aucun attribut HTML5 (required, type="email", etc.).
           La validation est gérée intégralement par le moteur custom dans assets/js/forms.js via les attributs 'data-*'.
        -->
        <div class="alert alert-danger" style="color:red; margin-bottom:15px; padding:10px; border:1px solid red; background:#ffeaea; border-radius:5px;">
            <?php echo $error; ?>
        </div>
      <?php endif; ?>
      <form class="auth-form" id="signup-candidat-form" method="POST" action="" data-validate>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-nom">Nom</label>
            <input type="text" class="input" id="candidat-nom" name="nom" placeholder="Votre nom" data-required="true">
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-prenom">Prénom</label>
            <input type="text" class="input" id="candidat-prenom" name="prenom" placeholder="Votre prénom" data-required="true">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="candidat-email">Adresse Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="candidat-email" name="email" placeholder="votre@email.com" data-required="true" data-type="email">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-password">Mot de passe</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="candidat-password" name="password" placeholder="Min. 8 caractères" data-required="true" data-minlength="8">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-password2">Confirmer</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="candidat-password2" name="password_confirm" placeholder="Confirmez" data-required="true" data-minlength="8" data-match="candidat-password">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-tel">Téléphone</label>
            <div class="input-icon-wrapper">
              <i data-lucide="phone" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="candidat-tel" name="telephone" placeholder="+216 XX XXX XXX" data-type="tel">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-linkedin">LinkedIn</label>
            <div class="input-icon-wrapper">
              <i data-lucide="linkedin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="candidat-linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." data-type="url">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-date">Date de naissance</label>
            <div class="input-icon-wrapper">
              <i data-lucide="calendar" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="candidat-date" name="date_naissance" placeholder="AAAA-MM-JJ">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-pays">Pays</label>
            <div class="input-icon-wrapper">
              <i data-lucide="globe" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="candidat-pays" name="pays" placeholder="Ex: Tunisie">
            </div>
          </div>
        </div>
        
        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="candidat-ville">Ville</label>
            <div class="input-icon-wrapper">
              <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="candidat-ville" name="ville" placeholder="Ex: Tunis">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="candidat-adresse">Adresse Complète</label>
            <input type="text" class="input" id="candidat-adresse" name="adresse" placeholder="Ex: 12 Rue des Oliviers">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label">Niveau d'études</label>
            <select name="niveauEtudes" class="select">
                <option value="">Non spécifié</option>
                <option value="Bac">Bac</option>
                <option value="Bac+2">Bac+2 (BTS, DUT)</option>
                <option value="Bac+3">Bac+3 (Licence)</option>
                <option value="Bac+5">Bac+5 (Master, Ingénieur)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Années d'expérience</label>
            <select name="niveau" class="select">
                <option value="">Non spécifié</option>
                <option value="Junior">Junior (0-2 ans)</option>
                <option value="Confirmé">Confirmé (3-5 ans)</option>
                <option value="Senior">Senior (5+ ans)</option>
                <option value="Expert">Expert (10+ ans)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Compétences</label>
          <div class="tag-input" id="skills-tag-input">
            <div class="tag-input__tags"></div>
            <input type="text" class="tag-input__field" list="skills-list" placeholder="Ajoutez vos compétences...">
            <input type="hidden" class="tag-input__hidden" name="competences">
          </div>
          <datalist id="skills-list">
             <option value="React">
             <option value="Node.js">
             <option value="PHP">
             <option value="MySQL">
             <option value="Python">
             <option value="Java">
             <option value="Design UX/UI">
             <option value="Marketing SEO">
             <option value="Gestion de Projet">
          </datalist>
          <span class="form-hint" style="display:block;margin-bottom:var(--space-3);">Appuyez sur Entrée ou virgule pour ajouter</span>
          
          <div class="suggested-skills">
            <div style="font-size:var(--fs-sm);font-weight:var(--fw-medium);margin-bottom:var(--space-2);color:var(--text-secondary);">Compétences suggérées</div>
            <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);">
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> React
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Node.js
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> MySQL
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> PHP
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Python
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Java
                </button>
            </div>
          </div>
        </div>


        <button type="submit" class="btn btn-primary btn-lg w-full" id="signup-candidat-submit">
          <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
          Créer mon compte
        </button>
      </form>

      <div class="auth-footer">
        <div style="margin-bottom: var(--space-2);">Déjà inscrit ? <a href="login.php">Se connecter</a></div>
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
  <script src="/aptus_first_official_version/view/assets/js/forms.js?v=2"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
