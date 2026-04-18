<?php
session_start();
include_once __DIR__ . '/../../controller/UtilisateurC.php';

$message = "";
$messageType = "";
$tokenValid = false;
$id_utilisateur = false;
$uc = new UtilisateurC();

// Verification du jeton (token) depuis l'url
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    $id_utilisateur = $uc->validateResetToken($token);
    
    if ($id_utilisateur !== false) {
        $tokenValid = true;
    } else {
        $messageType = "error";
        $message = "Ce lien de réinitialisation est invalide ou a expiré.";
    }
} else {
    $messageType = "error";
    $message = "Aucun jeton de réinitialisation fourni.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        
        if (strlen($password) < 8) {
            $messageType = "error";
            $message = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif ($password !== $confirm) {
            $messageType = "error";
            $message = "Les mots de passe ne correspondent pas.";
        } else {
            // Success: Reset password
            if ($uc->resetPassword($id_utilisateur, $password)) {
                $messageType = "success";
                $message = "Votre mot de passe a été réinitialisé avec succès. Vous allez être redirigé vers la page de connexion...";
                // Rediriger dans 3 secondes
                header("refresh:3;url=login.php");
                $tokenValid = false; // Hide form
            } else {
                $messageType = "error";
                $message = "Une erreur est survenue lors de la réinitialisation.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau mot de passe — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .auth-page {
      padding: 0 !important;
      background: var(--bg-body);
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .fp-card {
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-2xl);
      padding: var(--space-8);
      width: 100%;
      max-width: 450px;
      text-align: center;
      border: 1px solid var(--border-color);
    }
    .fp-card h1 {
        margin-bottom: var(--space-2);
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .fp-card p {
        color: var(--text-secondary);
        margin-bottom: var(--space-6);
        font-size: var(--fs-sm);
    }
  </style>
</head>
<body>

  <div class="auth-page">
    <div style="position:absolute;top:var(--space-4);right:var(--space-4);z-index:1000;">
      <button class="theme-toggle" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>
    </div>

    <div class="fp-card">
      <div style="margin-bottom: 20px; color: var(--accent-primary);">
          <i data-lucide="<?php echo $messageType === 'success' && !$tokenValid ? 'check-circle' : 'lock-keyhole'; ?>" style="width: 48px; height: 48px;"></i>
      </div>
      
      <?php if ($messageType === 'success' && !$tokenValid): ?>
          <h1>Félicitations !</h1>
          <p>Votre mot de passe a été mis à jour.</p>
      <?php else: ?>
          <h1>Nouveau mot de passe</h1>
          <p>Saisissez votre nouveau mot de passe sécurisé pour retrouver l'accès à votre compte.</p>
      <?php endif; ?>

      <?php if (!empty($message)): ?>
          <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" style="font-size: 14px; margin-bottom: 20px; padding: 10px; border-radius: var(--radius-sm); text-align: left; background-color: <?php echo $messageType === 'success' ? 'rgba(46, 204, 113, 0.1)' : 'rgba(231, 76, 60, 0.1)'; ?>; color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
              <?php echo $message; ?>
          </div>
      <?php endif; ?>

      <?php if ($tokenValid): ?>
      <form method="POST" action="" style="display: flex; flex-direction: column; gap: var(--space-4);">
        <div class="form-group w-full">
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" name="password" placeholder="Nouveau mot de passe" required minlength="8">
          </div>
        </div>
        
        <div class="form-group w-full">
          <div class="input-icon-wrapper">
            <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
            <input type="password" class="input" name="confirm_password" placeholder="Confirmer le mot de passe" required minlength="8">
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-full">Enregistrer</button>
      </form>
      <?php endif; ?>
      
      <?php if (!$tokenValid && $messageType === 'error'): ?>
      <div style="margin-top:20px;">
          <a href="forgot_password.php" class="btn btn-primary btn-lg w-full" style="display:block; text-decoration:none;">Générer un nouveau lien</a>
      </div>
      <?php endif; ?>

      <div class="auth-footer" style="margin-top: var(--space-6);">
          <a href="login.php" class="back-to-site" style="display: inline-flex; align-items: center; gap: 5px; color: var(--text-secondary); text-decoration: none; font-weight: 500;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Retour à la connexion
          </a>
      </div>
    </div>
  </div>

  <script>
      lucide.createIcons();
  </script>
</body>
</html>
