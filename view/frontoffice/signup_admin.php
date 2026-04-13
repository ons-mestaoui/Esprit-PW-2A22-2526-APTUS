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
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/AdminC.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '1');
    $telephone = trim($_POST['telephone'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? '');
    $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;

    if ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (empty($nom) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'adresse email est invalide.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        $utilisateurC = new UtilisateurC();
        if ($utilisateurC->emailExists($email)) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            $utilisateur = new Utilisateur(0, $nom, '', $email, $password, 'Admin', $telephone);
            $last_id = $utilisateurC->addUtilisateur($utilisateur);

            if ($last_id) {
                try {
                    $adminC = new AdminC();
                    $adminModel = new Admin($last_id, $niveau);
                    $adminC->addAdmin($adminModel);

                    include_once __DIR__ . '/../../controller/ProfilC.php';
                    $profilC = new ProfilC();
                    $p = new Profil(null, $last_id, null, null, $adresse, $ville, $pays, $date_naissance, $linkedin, null);
                    $profilC->addProfil($p);

                    $_SESSION['id_utilisateur'] = $last_id;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['role'] = 'Admin';
                    
                    header("Location: ../backoffice/dashboard.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Erreur de création admin : " . $e->getMessage();
                }
            } else {
                $error = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>
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

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="color:red; text-align:center; margin-bottom: 15px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form class="auth-form" id="signup-admin-form" method="POST" action="" data-validate>

        <div class="form-group">
          <label class="form-label" for="admin-nom">Nom complet</label>
          <div class="input-icon-wrapper">
            <i data-lucide="user" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="admin-nom" name="nom" placeholder="Nom complet" data-required="true">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-email">Email Administrateur</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="admin-email" name="email" placeholder="admin@aptus.com" data-required="true" data-type="email">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password">Mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="admin-password" name="password" placeholder="Min. 12 caractères" data-required="true">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password2">Confirmer le mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="admin-password2" name="password_confirm" placeholder="Confirmez" data-required="true" data-match="admin-password">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="admin-tel">Téléphone</label>
            <div class="input-icon-wrapper">
              <i data-lucide="phone" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-tel" name="telephone" placeholder="+216 XX XXX XXX">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="admin-linkedin">LinkedIn</label>
            <div class="input-icon-wrapper">
              <i data-lucide="linkedin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." data-type="url">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-date">Date de naissance</label>
          <div class="input-icon-wrapper">
            <i data-lucide="calendar" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-date" name="date_naissance" placeholder="AAAA-MM-JJ">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-pays">Pays</label>
          <div class="input-icon-wrapper">
            <i data-lucide="globe" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-pays" name="pays" placeholder="Ex: Tunisie">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-ville">Ville</label>
          <div class="input-icon-wrapper">
            <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-ville" name="ville" placeholder="Ex: Tunis">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-adresse">Adresse Complète</label>
          <input type="text" class="input" id="admin-adresse" name="adresse" placeholder="Ex: 12 Rue Principale">
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-niveau">Niveau (ex: 1, 2, SuperAdmin)</label>
          <div class="input-icon-wrapper">
            <i data-lucide="award" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="admin-niveau" name="niveau" placeholder="ex: SuperAdmin">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" id="signup-admin-submit">
          <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
          Créer le compte admin
        </button>
      </form>

      <div class="auth-footer">
        <div style="margin-bottom: var(--space-2);">Compte déjà existant ? <a href="login.php">Se connecter</a></div>
        <a href="landing.php" class="back-to-site">
          <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
          Retour au site
        </a>
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
