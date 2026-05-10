<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Only accessible if we have a temporary user session from login.php
if (!isset($_SESSION['temp_2fa_user'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/TwoFactorC.php';

$error = "";
$userTemp = $_SESSION['temp_2fa_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    
    if (!empty($code)) {
        $utilisateurC = new UtilisateurC();
        $prefs = $utilisateurC->getPreferences($userTemp['id']);
        $secret = $prefs['two_factor_secret'] ?? '';

        if (TwoFactorC::verifyCode($secret, $code)) {
            // Success! Finalize Login
            $_SESSION['id_utilisateur'] = $userTemp['id'];
            $_SESSION['nom'] = $userTemp['nom'];
            $_SESSION['prenom'] = $userTemp['prenom'];
            $_SESSION['role'] = $userTemp['role'];
            
            // Clear temp session
            unset($_SESSION['temp_2fa_user']);

            // Redirect based on role
            $roleRoutes = [
                'admin' => '../backoffice/dashboard.php',
                'candidat' => 'jobs_feed.php',
                'entreprise' => 'hr_posts.php',
                'tuteur' => 'dashboard_tuteur.php'
            ];
            $roleKey = strtolower($userTemp['role']);
            header("Location: " . ($roleRoutes[$roleKey] ?? 'landing.php'));
            exit();
        } else {
            $error = "Code de vérification invalide.";
        }
    } else {
        $error = "Veuillez entrer le code de vérification.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vérification 2FA — Aptus</title>
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page" style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg-body);">

  <div class="auth-container" style="max-width:400px; min-height:auto; padding:var(--space-8); text-align:center;">
    <div style="width:64px; height:64px; background:rgba(99,102,241,0.12); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-4) auto;">
      <i data-lucide="shield-check" style="width:32px;height:32px;color:var(--accent-primary);"></i>
    </div>

    <h1 style="font-size:24px; font-weight:800; margin-bottom:var(--space-1); background:var(--gradient-primary); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Double vérification</h1>
    <p style="color:var(--text-secondary); font-size:14px; margin-bottom:var(--space-6);">Saisissez le code généré par votre application d'authentification pour continuer.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom:var(--space-4); font-size:14px; padding:var(--space-3); background:#fee2e2; color:#b91c1c; border-radius:var(--radius-md); border:1px solid #ef4444;">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group" style="margin-bottom:var(--space-6);">
        <input type="text" name="code" class="input" placeholder="000000" maxlength="6" pattern="\d{6}" required autofocus style="text-align:center; font-size:32px; letter-spacing:12px; height:64px; font-weight:700;">
      </div>
      
      <button type="submit" class="btn btn-primary btn-lg w-full">Vérifier et se connecter</button>
    </form>

    <div style="margin-top:var(--space-6);">
      <a href="login.php" style="color:var(--text-tertiary); font-size:13px; text-decoration:none;">
        <i data-lucide="arrow-left" style="width:14px; height:14px; vertical-align:-2px;"></i> Retour à la connexion
      </a>
    </div>
  </div>

  <script>lucide.createIcons();</script>
</body>
</html>
