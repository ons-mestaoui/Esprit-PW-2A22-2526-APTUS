<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Prevent browser caching of the login page itself
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Security: If a user is already logged in and returns to the login page (e.g. via back button),
// we destroy the session to satisfy the requirement that 'Back = End Session'.
if (isset($_SESSION['id_utilisateur'])) {
    session_unset();
    session_destroy();
    
    // Explicitly clear the session cookie from the browser
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Restart session for the login page processing
    session_start();
}
include_once __DIR__ . '/../../controller/UtilisateurC.php';

$error = "";
$success_msg = "";
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_msg = "Inscription réussie ! Veuillez vérifier votre adresse email pour activer votre compte. (N'oubliez pas de vérifier vos courriers indésirables).";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if ($user && password_verify($password, $user['motDePasse'])) {
                // Check if account is verified
                if (isset($user['est_verifie']) && $user['est_verifie'] == 0) {
                    $error = "Veuillez vérifier votre adresse email avant de vous connecter. Un lien d'activation vous a été envoyé.";
                } else {
                    $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'] ?? '';
                $_SESSION['role'] = $user['role'];

                // Redirection basée sur le rôle (insensible à la casse par sécurité)
                $role = strtolower($user['role']);
                if ($role === 'admin') {
                    header("Location: ../backoffice/dashboard.php");
                } elseif ($role === 'candidat') {
                    header("Location: jobs_feed.php");
                } elseif ($role === 'entreprise') {
                    header("Location: hr_posts.php");
                } else {
                    // Par défaut si rôle inconnu
                    header("Location: landing.php");
                }
                exit();
                } // End est_verifie check
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
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
  <style>
    /* Immediate Fix for Sliding Panel */
    .auth-page {
      padding: 0 !important;
      background: var(--bg-body);
      overflow-x: hidden;
    }
    .auth-container {
      position: relative;
      overflow: hidden;
      width: 1000px;
      max-width: 95vw;
      min-height: 620px;
      margin: 20px;
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-2xl);
    }
    .form-container {
      position: absolute;
      top: 0;
      height: 100%;
      transition: all 0.6s ease-in-out;
    }
    .sign-in-container {
      left: 0;
      width: 50%;
      z-index: 2;
    }
    .auth-container.right-panel-active .sign-in-container {
      transform: translateX(100%);
    }
    .sign-up-container {
      left: 0;
      width: 50%;
      opacity: 0;
      z-index: 1;
    }
    .auth-container.right-panel-active .sign-up-container {
      transform: translateX(100%);
      opacity: 1;
      z-index: 5;
    }
    .overlay-container {
      position: absolute;
      top: 0;
      left: 50%;
      width: 50%;
      height: 100%;
      overflow: hidden;
      transition: transform 0.6s ease-in-out;
      z-index: 100;
    }
    .auth-container.right-panel-active .overlay-container {
      transform: translateX(-100%);
    }
    .overlay {
      background: var(--gradient-primary);
      background-repeat: no-repeat;
      background-size: cover;
      color: #FFFFFF;
      position: relative;
      left: -100%;
      height: 100%;
      width: 200%;
      transform: translateX(0);
      transition: transform 0.6s ease-in-out;
    }
    .auth-container.right-panel-active .overlay {
      transform: translateX(50%);
    }
    .overlay-panel {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 40px;
      text-align: center;
      top: 0;
      height: 100%;
      width: 50%;
      transition: transform 0.6s ease-in-out;
    }
    .overlay-left { transform: translateX(-20%); }
    .auth-container.right-panel-active .overlay-left { transform: translateX(0); }
    .overlay-right { right: 0; transform: translateX(0); }
    .auth-container.right-panel-active .overlay-right { transform: translateX(20%); }

    .auth-split-form {
      background-color: var(--bg-card);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 40px;
      height: 100%;
      text-align: center;
      gap: var(--space-5); /* Overall vertical spacing */
    }

    .role-grid-mini {
      display: flex;
      flex-direction: column;
      gap: var(--space-4); /* Specific space between cards */
      width: 100%;
    }

    /* Enhanced Button Hovers */
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
      filter: brightness(1.1);
    }
    
    .btn-outline-white {
      transition: all 0.3s ease;
    }
    .btn-outline-white:hover {
      background-color: #FFFFFF;
      color: var(--accent-primary) !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255,255,255,0.3);
    }

    /* More space specifically between email and password input groups */
    .sign-in-container .form-group + .form-group {
      margin-top: var(--space-3);
    }

    /* Clean & Premium Role Cards */
    .role-card-mini {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      width: 100%;
      padding: var(--space-4);
      display: flex;
      align-items: center;
      gap: var(--space-4);
      border-radius: var(--radius-lg);
      text-align: left;
    }
    
    .role-card-mini:hover {
      transform: translateY(-3px);
      border-color: var(--accent-primary);
      background: var(--bg-card);
      box-shadow: var(--shadow-xl);
    }
    
    .role-card-mini:hover .role-icon {
      color: var(--accent-primary);
      transform: scale(1.1);
    }

    .role-card-mini:nth-child(2):hover {
      border-color: var(--accent-secondary);
    }
    .role-card-mini:nth-child(2):hover .role-icon {
      color: var(--accent-secondary);
    }

    .role-card-mini:nth-child(3):hover {
      border-color: var(--accent-tertiary);
    }
    .role-card-mini:nth-child(3):hover .role-icon {
      color: var(--accent-tertiary);
    }
    
    .role-info h4 {
      margin: 0;
      font-size: var(--fs-base);
    }
    
    .role-info p {
      margin: 0;
      font-size: var(--fs-xs);
    }

    .role-card-mini:nth-child(3):hover {
      border-color: var(--accent-tertiary);
    }
    .role-card-mini:nth-child(3):hover .role-info h4 {
      color: var(--accent-tertiary);
    }
    .role-card-mini:nth-child(3)::after {
      background: var(--accent-tertiary);
    }

    @media (max-width: 768px) {
      .auth-container {
        min-height: auto;
        display: flex;
        flex-direction: column;
        width: 100%;
        margin: 0;
        border-radius: 0;
      }
      .form-container {
        position: relative;
        width: 100%;
        height: auto;
        transform: none !important;
      }
      .overlay-container { display: none; }
      .sign-up-container { display: none; }
      .auth-container.right-panel-active .sign-up-container { display: block; opacity: 1; transform: none !important; }
      .auth-container.right-panel-active .sign-in-container { display: none; }
    }
  </style>

  <script>
    /**
     * Security: Force reload if page is loaded from cache (Back/Forward button fix)
     * This ensures the PHP session revalidation logic is executed on every navigation.
     */
    window.addEventListener('pageshow', function(event) {
      if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
      }
    });
  </script>
</head>
<body>

  <div class="auth-page">
    <div class="auth-container" id="auth-container">
      
      <!-- Sign Up Container (Choices) -->
      <div class="form-container sign-up-container">
        <div class="auth-split-form">
          <h1>Rejoignez Aptus</h1>
          <p style="margin-bottom: var(--space-6); color: var(--text-secondary);">Choisissez votre profil pour commencer</p>
          
          <div class="role-grid-mini">
            <a href="signup_candidat.php" class="role-card-mini">
              <div class="role-icon" style="color: var(--accent-primary);">
                <i data-lucide="user"></i>
              </div>
              <div class="role-info">
                <h4>Candidat</h4>
                <p>Trouvez le job idéal avec l'IA.</p>
              </div>
            </a>

            <a href="signup_entreprise.php" class="role-card-mini">
              <div class="role-icon" style="color: var(--accent-secondary);">
                <i data-lucide="building-2"></i>
              </div>
              <div class="role-info">
                <h4>Entreprise</h4>
                <p>Recrutez les meilleurs talents.</p>
              </div>
            </a>

            <a href="signup_admin.php" class="role-card-mini">
              <div class="role-icon" style="color: var(--accent-tertiary);">
                <i data-lucide="shield"></i>
              </div>
              <div class="role-info">
                <h4>Admin</h4>
                <p>Gérez la plateforme Aptus.</p>
              </div>
            </a>
          </div>

          <a href="landing.php" class="back-to-site" style="margin-top: var(--space-8);">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Retour au site
          </a>
        </div>
      </div>

      <!-- Sign In Container (Login) -->
      <div class="form-container sign-in-container">
        <form class="auth-split-form" id="login-form" method="POST" action="" data-validate>
          <h1>Connexion</h1>
          
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="color:red; background-color:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); padding:10px; border-radius:5px; text-align:center; margin-bottom: 15px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success" style="color:#2ecc71; background-color:rgba(46,204,113,0.1); border:1px solid rgba(46,204,113,0.2); padding:10px; border-radius:5px; text-align:center; margin-bottom: 15px; font-size: 14px;">
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
          <?php endif; ?>

          <div class="form-group w-full">
            <div class="input-icon-wrapper">
              <i data-lucide="mail" style="width:18px;height:18px;"></i>
              <input type="text" class="input" id="login-email" name="email" placeholder="Email" data-required="true" data-type="email">
            </div>
          </div>

          <div class="form-group w-full">
            <div class="input-icon-wrapper">
              <i data-lucide="lock" style="width:18px;height:18px;"></i>
              <input type="password" class="input" id="login-password" name="password" placeholder="Mot de passe" data-required="true">
            </div>
          </div>



          <a href="forgot_password.php" class="text-xs text-secondary" style="margin: 10px 0;">Mot de passe oublié ?</a>
          
          <button type="submit" class="btn btn-primary btn-lg w-full" style="margin-top:var(--space-2);">Se connecter</button>
          
          <div class="auth-footer" style="margin-top: var(--space-6);">
             <a href="landing.php" class="back-to-site">
              <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
              Retour au site
            </a>
          </div>
        </form>
      </div>

      <!-- Overlay Container -->
      <div class="overlay-container">
        <div class="overlay">
          <div class="overlay-panel overlay-left">
            <h1>Bon retour parmi nous !</h1>
            <p>Pour rester connecté avec nous, veuillez vous connecter avec vos informations personnelles</p>
            <button class="btn btn-outline-white btn-lg" id="signIn">Se connecter</button>
          </div>
          <div class="overlay-panel overlay-right">
            <h1>Bonjour l'ami !</h1>
            <p>Inscrivez-vous et commencez votre aventure avec Aptus</p>
            <button class="btn btn-outline-white btn-lg" id="signUp">S'inscrire</button>
          </div>
        </div>
      </div>

      <!-- Theme toggle -->
      <div style="position:absolute;top:var(--space-4);right:var(--space-4);z-index:1000;">
        <button class="theme-toggle" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>
      </div>

    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="/aptus_first_official_version/view/assets/js/forms.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/auth_slider.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/password-toggle.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
