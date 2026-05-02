<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription Tuteur — Aptus</title>
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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../.env.php';

include_once __DIR__ . '/../../controller/UtilisateurC.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    $specialite = trim($_POST['specialite'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $biographie = trim($_POST['biographie'] ?? '');
    
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? '');

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
            $utilisateur = new Utilisateur(0, $nom, $prenom, $email, $password, 'Tuteur', $telephone);
            $result = $utilisateurC->addUtilisateur($utilisateur);
            $last_id = $result['id'];
            $token_verification = $result['token'];

            if ($last_id) {
                try {
                    include_once __DIR__ . '/../../controller/TuteurC.php';
                    include_once __DIR__ . '/../../controller/ProfilC.php';
                    $tuteurC = new TuteurC();
                    $profilC = new ProfilC();
                    
                    $t = new Tuteur($last_id, $specialite, $experience, $biographie);
                    $tuteurC->addTuteur($t);

                    // Set Profil Attributes
                    $p = new Profil(null, $last_id, null, null, $adresse, $ville, $pays, null, null, null);
                    $profilC->addProfil($p);

                    // Send Verification Email
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $host = $_SERVER['HTTP_HOST'];
                    $verificationLink = $protocol . $host . "/aptus_first_official_version/view/frontoffice/verify_email.php?token=" . urlencode($token_verification);

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = SMTP_HOST;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = SMTP_USER;
                        $mail->Password   = SMTP_PASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = SMTP_PORT;

                        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                        $mail->addAddress($email, $nom . ' ' . $prenom);

                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = 'Activez votre compte Tuteur - Aptus';
                        $mail->Body    = "
                        <html><body>
                          <h2>Bienvenue sur Aptus !</h2>
                          <p>Merci pour votre inscription en tant que Tuteur. Avant de pouvoir accéder à votre tableau de bord, vous devez vérifier votre adresse email.</p>
                          <p>Cliquez sur le lien ci-dessous pour activer votre compte :</p>
                          <p><a href='" . htmlspecialchars($verificationLink) . "'><strong>Activer mon compte</strong></a></p>
                          <br><p>L'équipe Aptus</p>
                        </body></html>";
                        $mail->send();
                    } catch (Exception $e) {}

                    header("Location: login.php?registered=1");
                    exit();
                } catch (Exception $e) {
                    $error = "Erreur d'inscription du tuteur: " . $e->getMessage();
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
        <span class="auth-card__type-badge" style="background: var(--accent-tertiary, #10B981); color: #fff;">
          <i data-lucide="graduation-cap" style="width:14px;height:14px;"></i>
          Tuteur
        </span>
        <h1>Créer votre compte Tuteur</h1>
        <p>Partagez votre expertise et accompagnez les futurs talents</p>
      </div>

      <!-- Signup Form -->
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="color:red; margin-bottom:15px; padding:10px; border:1px solid red; background:#ffeaea; border-radius:5px;">
            <?php echo $error; ?>
        </div>
      <?php endif; ?>
      <form class="auth-form" id="signup-tuteur-form" method="POST" action="" data-validate>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="tuteur-nom">Nom</label>
            <input type="text" class="input" id="tuteur-nom" name="nom" placeholder="Votre nom" data-required="true">
          </div>
          <div class="form-group">
            <label class="form-label" for="tuteur-prenom">Prénom</label>
            <input type="text" class="input" id="tuteur-prenom" name="prenom" placeholder="Votre prénom" data-required="true">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="tuteur-email">Adresse Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="tuteur-email" name="email" placeholder="votre@email.com" data-required="true" data-type="email">
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="tuteur-password">Mot de passe</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="tuteur-password" name="password" placeholder="Min. 8 caractères" data-required="true" data-minlength="8">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="tuteur-password2">Confirmer</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="tuteur-password2" name="password_confirm" placeholder="Confirmez" data-required="true" data-minlength="8" data-match="tuteur-password">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="tuteur-tel">Téléphone</label>
            <div class="input-icon-wrapper">
              <i data-lucide="phone" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="tuteur-tel" name="telephone" placeholder="+216 XX XXX XXX" data-type="tel">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="tuteur-specialite">Spécialité / Domaine</label>
            <div class="input-icon-wrapper">
              <i data-lucide="award" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="tuteur-specialite" name="specialite" placeholder="Ex: Développement Web, IA..." data-required="true">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="tuteur-exp">Expérience (Années)</label>
          <input type="text" class="input" id="tuteur-exp" name="experience" placeholder="Ex: 5 ans">
        </div>

        <div class="form-group">
          <label class="form-label" for="tuteur-bio">Biographie / Présentation</label>
          <textarea class="input" id="tuteur-bio" name="biographie" placeholder="Parlez-nous de votre parcours..." style="min-height: 100px; padding: 12px;"></textarea>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="tuteur-pays">Pays</label>
            <input type="text" class="input" id="tuteur-pays" name="pays" placeholder="Ex: Tunisie">
          </div>
          <div class="form-group">
            <label class="form-label" for="tuteur-ville">Ville</label>
            <input type="text" class="input" id="tuteur-ville" name="ville" placeholder="Ex: Tunis">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" id="signup-tuteur-submit" style="background: var(--accent-tertiary, #10B981); border-color: var(--accent-tertiary, #10B981);">
          <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
          Créer mon compte Tuteur
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
        <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/password-toggle.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
