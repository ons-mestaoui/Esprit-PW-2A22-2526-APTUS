<?php
session_start();
include_once __DIR__ . '/../../controller/UtilisateurC.php';

$message = "";
$messageType = "error";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    $uc = new UtilisateurC();
    
    if ($uc->verifyAccount($token)) {
        $messageType = "success";
        $message = "Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.";
    } else {
        $messageType = "error";
        $message = "Ce lien de vérification est invalide ou votre compte a déjà été activé.";
    }
} else {
    $messageType = "error";
    $message = "Aucun code de vérification fourni.";
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vérification Email — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-card__header">
          <div style="color: <?php echo $messageType === 'success' ? 'var(--accent-primary)' : '#e74c3c'; ?>; margin-bottom: var(--space-4);">
              <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'x-circle'; ?>" style="width: 48px; height: 48px; margin: 0 auto;"></i>
          </div>
          
          <?php if ($messageType === 'success'): ?>
              <h1>Félicitations !</h1>
              <p>Votre adresse email est officiellement vérifiée.</p>
          <?php else: ?>
              <h1>Erreur de vérification</h1>
              <p>Nous n'avons pas pu valider votre demande.</p>
          <?php endif; ?>
      </div>

      <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" style="font-size: 14px; margin-bottom: 20px; padding: 10px; border-radius: var(--radius-sm); text-align: center; background-color: <?php echo $messageType === 'success' ? 'rgba(46, 204, 113, 0.1)' : 'rgba(231, 76, 60, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)'; ?>; color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
          <?php echo $message; ?>
      </div>

      <a href="login.php" class="btn btn-primary btn-lg w-full" style="margin-top:var(--space-2); text-decoration:none; text-align:center;">
          Aller à la connexion
      </a>

      <div style="position:absolute;top:var(--space-4);right:var(--space-4);z-index:1000;">
        <button class="theme-toggle" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
          <i data-lucide="moon" class="icon-moon"></i>
        </button>
      </div>

    </div>
  </div>

  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  <script>
      lucide.createIcons();
  </script>
</body>
</html>
