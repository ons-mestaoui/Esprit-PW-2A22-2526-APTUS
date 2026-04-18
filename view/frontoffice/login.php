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
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="color:red; text-align:center; margin-bottom: 15px;">
            <?php echo $error; ?>
        </div>
      <?php endif; ?>
      <form class="auth-form" id="login-form" method="POST" action="" data-validate>
        <div class="form-group">
          <label class="form-label" for="login-email">Adresse Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="text" class="input" id="login-email" name="email" placeholder="votre@email.com" data-required="true" data-type="email">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-password">Mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" id="login-password" name="password" placeholder="••••••••" data-required="true" data-minlength="8">
          </div>
        </div>

        <div class="flex items-center justify-between" style="margin: -4px 0;">
          <label class="flex items-center gap-2" style="cursor:pointer;">
            <input type="checkbox" id="remember-me" style="accent-color:var(--accent-primary);">
            <span class="text-sm text-secondary">Se souvenir de moi</span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-full" style="margin-top:var(--space-3);">Se connecter</button>
      </form>

      <!-- Footer -->
      <div class="auth-footer">
        <div style="margin-bottom: var(--space-2);">Pas de compte ? <a href="signup_choice.php">S'inscrire gratuitement</a></div>
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
