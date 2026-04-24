<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/SMTP.php';

require_once __DIR__ . '/../../.env.php';

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
            $message = "Si cette adresse est associée à un compte, un email contenant les instructions a été envoyé.";
            
            // Build the URL for the reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $uri = "/aptus_first_official_version/view/frontoffice/reset_password.php?token=" . urlencode($token);
            $resetLink = $protocol . $host . $uri;

            // Send actual email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Paramètres du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;

                // Destinataires
                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                $mail->addAddress($email);

                // Contenu
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Réinitialisation de votre mot de passe — Aptus';
                $mail->Body    = "
                <html>
                <head>
                  <title>Réinitialisation de mot de passe</title>
                </head>
                <body>
                  <p>Bonjour,</p>
                  <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Aptus.</p>
                  <p>Pour choisir un nouveau mot de passe, veuillez cliquer sur le lien ci-dessous :</p>
                  <p><a href='" . htmlspecialchars($resetLink) . "'>" . htmlspecialchars($resetLink) . "</a></p>
                  <p>Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité. Le lien expirera dans une heure.</p>
                  <p>Cordialement,<br>L'équipe Aptus</p>
                </body>
                </html>
                ";

                $mail->send();
            } catch (Exception $e) {
                // En cas d'erreur de configuration SMTP locale, on cache l'erreur à l'utilisateur final 
                // mais on peut l'afficher dans les logs PHP (désactivé ici par sécurité)
                // error_log(Message could not be sent. Mailer Error: {$mail->ErrorInfo});
            }

        } else {
            $messageType = "error"; 
            $message = "Cette adresse email n'existe pas dans notre base de données. Veuillez vérifier votre saisie ou créer un compte.";
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
    /* Add any custom styles specific to this page here */
  </style>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-card__header">
          <div style="color: var(--accent-primary); margin-bottom: var(--space-4);">
             <i data-lucide="key" style="width: 48px; height: 48px; margin: 0 auto;"></i>
          </div>
          <h1>Mot de passe oublié ?</h1>
          <p>Entrez votre adresse email ci-dessous. Si elle correspond à un compte actif, vous recevrez un lien de réinitialisation.</p>
      </div>

      <?php if (!empty($message)): ?>
          <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" style="font-size: 14px; margin-bottom: 20px; padding: 10px; border-radius: var(--radius-sm); text-align: left; background-color: <?php echo $messageType === 'success' ? 'rgba(46, 204, 113, 0.1)' : 'rgba(231, 76, 60, 0.1)'; ?>; color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
              <?php echo $message; ?>
          </div>
      <?php endif; ?>



      <form method="POST" action="" class="auth-form">
        <div class="form-group w-full">
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" name="email" placeholder="Votre email" required>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-full" style="margin-top:var(--space-2);">Envoyer le lien</button>
      </form>

      <div class="auth-footer" style="margin-top: var(--space-6);">
          <a href="login.php" class="back-to-site" style="margin-top: 0;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Retour à la connexion
          </a>
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

  <script src="/aptus_first_official_version/view/assets/js/alert-dismiss.js"></script>
  <script>
      lucide.createIcons();
  </script>
</body>
</html>
