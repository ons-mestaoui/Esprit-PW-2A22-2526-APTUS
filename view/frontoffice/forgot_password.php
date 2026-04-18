<?php
session_start();
include_once __DIR__ . '/../../controller/UtilisateurC.php';

$message = "";
$messageType = "";
$simulatedLink = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($email)) {
        $uc = new UtilisateurC();
        $token = $uc->createPasswordResetToken($email);
        
        if ($token) {
            $messageType = "success";
            $message = "Un email de réinitialisation a été préparé.";
            
            // Build the URL for the simulated email local testing
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $uri = "/aptus_first_official_version/view/frontoffice/reset_password.php?token=" . urlencode($token);
            $simulatedLink = $protocol . $host . $uri;
        } else {
            $messageType = "error";
            $message = "Si cet email existe, un lien vous sera envoyé."; // Generic message for security (don't reveal if email exists or not)
        }
    } else {
        $messageType = "error";
        $message = "Veuillez entrer une adresse email.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mot de passe oublié — Aptus</title>
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
    .simulated-email {
        background-color: var(--bg-accent-light);
        border: 1px dashed var(--accent-primary);
        padding: var(--space-4);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-6);
        text-align: left;
    }
    .simulated-email strong {
        color: var(--accent-primary);
    }
  </style>
</head>
<body>

  <div class="auth-page">
    <!-- Theme toggle -->
    <div style="position:absolute;top:var(--space-4);right:var(--space-4);z-index:1000;">
      <button class="theme-toggle" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>
    </div>

    <div class="fp-card">
      <div style="margin-bottom: 20px; color: var(--accent-primary);">
          <i data-lucide="key" style="width: 48px; height: 48px;"></i>
      </div>
      <h1>Mot de passe oublié ?</h1>
      <p>Entrez votre adresse email ci-dessous. Si elle correspond à un compte actif, vous recevrez un lien de réinitialisation.</p>

      <?php if (!empty($message)): ?>
          <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" style="font-size: 14px; margin-bottom: 20px; padding: 10px; border-radius: var(--radius-sm); text-align: left; background-color: <?php echo $messageType === 'success' ? 'rgba(46, 204, 113, 0.1)' : 'rgba(231, 76, 60, 0.1)'; ?>; color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
              <?php echo $message; ?>
          </div>
      <?php endif; ?>

      <?php if (!empty($simulatedLink)): ?>
          <div class="simulated-email">
              <strong>[Environnement Local] Simulation d'e-mail</strong><br>
              Dans le cadre du test local, voici le lien que l'utilisateur recevrait :<br><br>
              <a href="<?php echo htmlspecialchars($simulatedLink); ?>" style="color: var(--accent-primary); word-break: break-all; font-weight: bold; text-decoration: underline;">
                  Cliquez ici pour réinitialiser le mot de passe
              </a>
          </div>
      <?php endif; ?>

      <form method="POST" action="" style="display: flex; flex-direction: column; gap: var(--space-4);">
        <div class="form-group w-full">
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" name="email" placeholder="Votre email" required>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-full">Envoyer le lien</button>
      </form>

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
