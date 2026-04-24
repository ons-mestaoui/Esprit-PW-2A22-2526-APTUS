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
    // Les champs
    $raison_sociale = trim($_POST['raison_sociale'] ?? '');
    $siret = trim($_POST['siret'] ?? '');
    $secteur = trim($_POST['secteur'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    
    // Champs optionnels
    $site_web = trim($_POST['site_web'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $taille = $_POST['taille'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $annee_fondation = !empty($_POST['annee_fondation']) ? $_POST['annee_fondation'] : null;
    
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? '');

    if ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (empty($raison_sociale) || empty($siret) || empty($email) || empty($password)) {
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
            // Création dans "utilisateur" - nom prend raisonSociale, prenom est vide pour une entreprise
            // Création dans "utilisateur" - nom prend raisonSociale, prenom est vide pour une entreprise
            $utilisateur = new Utilisateur(0, $raison_sociale, '', $email, $password, 'Entreprise', $telephone);
            $result = $utilisateurC->addUtilisateur($utilisateur);
            $last_id = $result['id'];
            $token_verification = $result['token'];

            if ($last_id) {
                try {
                    include_once __DIR__ . '/../../controller/EntrepriseC.php';
                    include_once __DIR__ . '/../../controller/ProfilC.php';
                    $entrepriseC = new EntrepriseC();
                    $profilC = new ProfilC();
                    
                    // 1. Profil
                    $photo_base64 = null;
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                        $imageData = file_get_contents($_FILES['logo']['tmp_name']);
                        $mimeType = !empty($_FILES['logo']['type']) ? $_FILES['logo']['type'] : 'image/jpeg'; 
                        $photo_base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    }
                    $profil = new Profil(null, $last_id, $photo_base64, $description, $adresse, $ville, $pays, null, $linkedin, $site_web);
                    $profilC->addProfil($profil);
                    
                    // 2. Entreprise
                    // 2. Entreprise
                    $ent = new Entreprise($last_id, $secteur, $siret, $raison_sociale, $taille, $annee_fondation);
                    $entrepriseC->addEntreprise($ent);

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
                        $mail->addAddress($email, $raison_sociale);

                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = 'Activez le compte de votre Entrerpise - Aptus';
                        $mail->Body    = "
                        <html><body>
                          <h2>Bienvenue sur Aptus !</h2>
                          <p>Vous avez inscrit l'entreprise <strong>$raison_sociale</strong>. Avant de pouvoir publier des offres, vous devez vérifier cette adresse email professionnelle.</p>
                          <p>Cliquez sur le lien ci-dessous pour activer le compte de l'entreprise :</p>
                          <p><a href='" . htmlspecialchars($verificationLink) . "'><strong>Activer le compte</strong></a></p>
                          <br><p>L'équipe Aptus</p>
                        </body></html>";
                        $mail->send();
                    } catch (Exception $e) {}

                    header("Location: login.php?registered=1");
                    exit();
                } catch (Exception $e) {
                    $error = "Erreur de création entreprise : " . $e->getMessage();
                }
            } else {
                $error = "Une erreur s'est produite lors de la création du compte.";
            }
        }
    }
}
?>

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

      <?php if (!empty($error)): ?>
        <div style="background-color:rgba(239,68,68,0.1);color:#ef4444;padding:12px;border-radius:8px;margin-bottom:var(--space-4);font-size:14px;border:1px solid rgba(239,68,68,0.2);">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data" class="auth-form" id="signup-entreprise-form" data-validate>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-nom">Raison sociale</label>
            <div class="input-icon-wrapper">
              <i data-lucide="building-2" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-nom" name="raison_sociale" placeholder="Ex: TechSphere" data-required="true">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-siret">N° SIRET</label>
            <div class="input-icon-wrapper">
              <i data-lucide="hash" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-siret" name="siret" placeholder="123 456 789 00012" data-required="true" data-type="tel">
            </div>
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
            <label class="form-label" for="ent-annee">Année de fondation</label>
            <div class="input-icon-wrapper">
              <i data-lucide="calendar" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-annee" name="annee_fondation" placeholder="Ex: 2012" data-type="tel" data-minlength="4">
            </div>
          </div>
          <!-- Le div vide pour balancer la grid ou alors l'email peut y aller. Je vais juste mettre un form-group vide pour aligner, ou je met l'année seule -->
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-pays">Pays</label>
            <div class="input-icon-wrapper">
              <i data-lucide="globe" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-pays" name="pays" placeholder="Ex: France">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-ville">Ville</label>
            <div class="input-icon-wrapper">
              <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-ville" name="ville" placeholder="Ex: Paris">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="ent-adresse">Adresse Complète</label>
          <input type="text" class="input" id="ent-adresse" name="adresse" placeholder="12 Avenue des Champs Elysées">
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-email">Email Professionnel</label>
            <div class="input-icon-wrapper">
              <i data-lucide="mail" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-email" name="email" placeholder="contact@entreprise.com" data-required="true" data-type="email">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-tel">Téléphone</label>
            <div class="input-icon-wrapper">
              <i data-lucide="phone" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-tel" name="telephone" placeholder="+216 XX XXX XXX" data-type="tel">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-site">Site Web</label>
            <div class="input-icon-wrapper">
              <i data-lucide="globe" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-site" name="site_web" placeholder="https://www.entreprise.com" data-type="url">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-linkedin">LinkedIn</label>
            <div class="input-icon-wrapper">
              <i data-lucide="linkedin" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="ent-linkedin" name="linkedin" placeholder="https://linkedin.com/company/..." data-type="url">
            </div>
          </div>
        </div>

        <div class="auth-form__row">
          <div class="form-group">
            <label class="form-label" for="ent-password">Mot de passe</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="ent-password" name="password" placeholder="Min. 8 caractères" data-required="true" data-minlength="8">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="ent-password2">Confirmer</label>
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="ent-password2" name="password_confirm" placeholder="Confirmez" data-required="true" data-minlength="8" data-match="ent-password">
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
              <span class="text-xs text-tertiary">PNG, JPG, SVG — Max. 200MB</span>
            </div>
            <div class="drop-zone__preview"></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" id="signup-entreprise-submit">
          <i data-lucide="building-2" style="width:18px;height:18px;"></i>
          Inscrire l'entreprise
        </button>
      </form>

      <div class="auth-footer">
        <div style="margin-bottom: var(--space-2);">Déjà inscrit ? <a href="login.php">Se connecter</a></div>
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
  <script src="/aptus_first_official_version/view/assets/js/password-toggle.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
