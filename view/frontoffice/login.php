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

            $passwordOk = false;
            if ($user) {
                if (password_verify($password, $user['motDePasse'])) {
                    $passwordOk = true;
                } elseif ($password === $user['motDePasse']) {
                    // Plaintext match (e.g. admin manually inserted via database)
                    $passwordOk = true;
                    // Auto-hash and update the password for future logins
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateQuery = $db->prepare("UPDATE utilisateur SET motDePasse = :hash WHERE id_utilisateur = :id");
                    $updateQuery->execute(['hash' => $newHash, 'id' => $user['id_utilisateur']]);
                }
            }

            if ($passwordOk) {
                // Connexion réussie : on simplifie et on connecte directement l'utilisateur
                $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'] ?? '';
                $_SESSION['role'] = $user['role'];

                // Redirection simple basée sur le rôle
                $roleRoutes = [
                    'admin' => '../backoffice/dashboard.php',
                    'candidat' => 'jobs_feed.php',
                    'entreprise' => 'hr_posts.php',
                    'tuteur' => 'dashboard_tuteur.php'
                ];
                $roleKey = strtolower($user['role']);
                
                header("Location: " . ($roleRoutes[$roleKey] ?? 'landing.php'));
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

// Handle social login errors from URL
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'no_account') {
        $error = "Aucun compte associé à cet email. Veuillez vous inscrire d'abord.";
    } elseif ($_GET['error'] == 'social_error') {
        $error = "Erreur lors de la connexion sociale. Veuillez réessayer.";
    } elseif ($_GET['error'] == 'auth_failed') {
        $error = "Identifiants invalides ou compte non vérifié.";
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
      min-height: 780px;
      margin: 20px auto;
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
      padding: var(--space-8) 40px;
      height: 100%;
      text-align: center;
      gap: var(--space-4);
      overflow-y: auto;
    }
    
    .auth-split-form h1 {
      font-size: var(--fs-xl);
      font-weight: 800;
      margin-bottom: var(--space-1);
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .auth-split-form p {
      margin-bottom: var(--space-4);
      color: var(--text-secondary);
      font-size: var(--fs-sm);
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
    /* ── Face ID Login Panel ── */
    .faceid-login-panel {
      display: none;
      flex-direction: column;
      align-items: center;
      gap: var(--space-4);
      width: 100%;
      animation: fadeSlideIn 0.4s ease;
    }
    .faceid-login-panel.active { display: flex; }
    @keyframes fadeSlideIn {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .faceid-camera-container {
      position: relative;
      width: 100%;
      max-width: 320px;
      border-radius: var(--radius-lg);
      overflow: hidden;
      background: #000;
      aspect-ratio: 4/3;
    }
    .faceid-camera-container video {
      width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);
    }
    .faceid-camera-container canvas {
      position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); pointer-events: none;
    }
    .faceid-scan-ring {
      position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
      width: 160px; height: 160px; border-radius: 50%;
      border: 3px dashed rgba(99,102,241,0.5);
      animation: faceid-pulse-login 2s infinite;
    }
    @keyframes faceid-pulse-login {
      0%, 100% { opacity: 0.4; transform: translate(-50%, -50%) scale(1); }
      50% { opacity: 1; transform: translate(-50%, -50%) scale(1.05); }
    }
    .faceid-progress-bar {
      display: flex; gap: var(--space-1); width: 100%; max-width: 320px;
    }
    .faceid-progress-bar .step {
      flex: 1; height: 4px; border-radius: 2px; background: var(--border-color, #e2e8f0);
      transition: background 0.3s;
    }
    .faceid-progress-bar .step.done { background: var(--accent-primary, #6366f1); }
    .faceid-status-text {
      font-size: var(--fs-sm, 14px); color: var(--text-secondary); text-align: center;
      min-height: 20px;
    }
    .faceid-result-msg {
      font-size: var(--fs-sm, 14px); padding: var(--space-2) var(--space-3);
      border-radius: var(--radius-md); text-align: center; font-weight: 500;
      display: none; width: 100%; max-width: 320px;
    }
    .faceid-divider {
      display: flex; align-items: center; gap: var(--space-3); width: 100%; margin: var(--space-2) 0;
      color: var(--text-tertiary, #94a3b8); font-size: var(--fs-xs, 12px);
    }
    .faceid-divider::before, .faceid-divider::after {
      content: ''; flex: 1; height: 1px; background: var(--border-color, #e2e8f0);
    }
    .btn-faceid {
      display: flex; align-items: center; justify-content: center; gap: var(--space-2);
      width: 100%; padding: var(--space-3); border-radius: var(--radius-md);
      border: 1.5px solid var(--border-color, #e2e8f0); background: var(--bg-secondary, #f8fafc);
      color: var(--text-primary); font-weight: 500; font-size: var(--fs-sm, 14px);
      cursor: pointer; transition: all 0.3s ease;
    }
    .btn-faceid:hover {
      border-color: var(--accent-primary, #6366f1); background: rgba(99,102,241,0.06);
      transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.15);
    }
    .btn-faceid svg, .btn-faceid i { color: var(--accent-primary, #6366f1); }
    /* Social Login Buttons - Glassmorphism & Premium Style */
    .social-auth-container {
      display: flex;
      justify-content: center;
      gap: var(--space-4);
      width: 100%;
      margin-top: var(--space-2);
    }
    .btn-social {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-3);
      padding: var(--space-3) var(--space-4);
      border-radius: var(--radius-full);
      border: 1px solid var(--border-color);
      background: var(--bg-secondary);
      color: var(--text-primary);
      font-weight: 600;
      font-size: var(--fs-sm);
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      text-decoration: none;
    }
    
    [data-theme="dark"] .btn-social {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.1);
    }

    .btn-social:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: var(--shadow-lg);
      border-color: var(--accent-primary);
      background: var(--bg-card);
    }
    
    [data-theme="dark"] .btn-social:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--accent-primary);
    }

    .btn-social.google:hover {
      background: rgba(234, 67, 53, 0.08);
      border-color: rgba(234, 67, 53, 0.4);
      color: #EA4335 !important;
    }
    .btn-social.github:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--text-primary);
      color: var(--text-primary) !important;
    }
    .social-icon {
      width: 20px;
      height: 20px;
      transition: transform 0.3s ease;
      fill: currentColor;
    }
    .btn-social:hover .social-icon {
      transform: rotate(10deg);
    }
    
    .faceid-divider {
      display: flex;
      align-items: center;
      gap: var(--space-4);
      width: 100%;
      font-size: var(--fs-xs);
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.5;
      margin: var(--space-1) 0;
      font-weight: 600;
    }
    .faceid-divider::before, .faceid-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border-color);
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

            <a href="signup_tuteur.php" class="role-card-mini">
              <div class="role-icon" style="color: var(--accent-tertiary, #10B981);">
                <i data-lucide="graduation-cap"></i>
              </div>
              <div class="role-info">
                <h4>Tuteur</h4>
                <p>Encadrez et suivez vos étudiants.</p>
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

          <!-- Face ID Divider & Button -->
          <div class="faceid-divider">ou</div>
          <button type="button" class="btn-faceid" id="faceid-login-toggle">
            <i data-lucide="scan-face" style="width:20px;height:20px;"></i>
            Se connecter avec Face ID
          </button>

          <!-- Face ID Login Panel (hidden by default) -->
          <div class="faceid-login-panel" id="faceid-login-panel">
            <div class="faceid-camera-container">
              <video id="login-faceid-video" autoplay muted playsinline></video>
              <canvas id="login-faceid-overlay"></canvas>
              <div class="faceid-scan-ring"></div>
            </div>
            <div class="faceid-progress-bar">
              <div class="step" data-step="1"></div>
              <div class="step" data-step="2"></div>
              <div class="step" data-step="3"></div>
              <div class="step" data-step="4"></div>
            </div>
            <div class="faceid-status-text" id="login-faceid-status">Chargement...</div>
            <div class="faceid-result-msg" id="login-faceid-result"></div>
            <button type="button" class="btn btn-primary w-full" id="login-faceid-start" style="display:none;">Vérifier mon identité</button>
            <button type="button" class="text-xs text-secondary" id="login-faceid-back" style="cursor:pointer; background:none; border:none; margin-top:var(--space-2);">
              ← Retour à la connexion classique
            </button>
          </div>
          
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
        <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-sun"></i>
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
  <script src="/aptus_first_official_version/view/assets/js/face-api.min.js"></script>
  <script src="/aptus_first_official_version/view/assets/js/face-recognition.js"></script>
  <script>lucide.createIcons();</script>

  <script>
  (function() {
    const toggleBtn = document.getElementById('faceid-login-toggle');
    const panel = document.getElementById('faceid-login-panel');
    const backBtn = document.getElementById('login-faceid-back');
    const videoEl = document.getElementById('login-faceid-video');
    const overlayCanvas = document.getElementById('login-faceid-overlay');
    const statusText = document.getElementById('login-faceid-status');
    const resultMsg = document.getElementById('login-faceid-result');
    const startBtn = document.getElementById('login-faceid-start');
    const progressSteps = document.querySelectorAll('#faceid-login-panel .step');
    const emailInput = document.getElementById('login-email');
    const passwordFields = document.querySelectorAll('.sign-in-container .form-group');
    const submitBtn = document.querySelector('.sign-in-container .btn-primary[type="submit"]');
    const forgotLink = document.querySelector('.sign-in-container .text-xs.text-secondary[href]');

    let cancelled = false;
    let verifying = false;

    if (!toggleBtn || !panel) return;

    // ── Toggle Face ID panel ──
    toggleBtn.addEventListener('click', async function() {
      // Require email first
      const email = emailInput ? emailInput.value.trim() : '';
      if (!email) {
        emailInput.focus();
        emailInput.style.borderColor = 'var(--accent-tertiary, #ef4444)';
        setTimeout(() => emailInput.style.borderColor = '', 2000);
        return;
      }

      cancelled = false;
      panel.classList.add('active');
      toggleBtn.style.display = 'none';
      // Hide password field and submit button
      passwordFields[1].style.display = 'none';
      submitBtn.style.display = 'none';
      if (forgotLink) forgotLink.style.display = 'none';
      resultMsg.style.display = 'none';
      statusText.textContent = 'Chargement des modèles d\'IA...';
      startBtn.style.display = 'none';
      progressSteps.forEach(s => s.classList.remove('done'));

      try {
        await FaceAuth.loadModels();
        await FaceAuth.startCamera(videoEl);
        statusText.textContent = 'Caméra prête. Placez votre visage devant la caméra.';
        startBtn.style.display = '';
        startBtn.disabled = false;
        startBtn.textContent = 'Vérifier mon identité';
      } catch (e) {
        statusText.textContent = 'Erreur : impossible d\'accéder à la caméra.';
      }
    });

    // ── Back to classic login ──
    backBtn.addEventListener('click', function() {
      cancelled = true;
      FaceAuth.stopCamera();
      panel.classList.remove('active');
      toggleBtn.style.display = '';
      passwordFields[1].style.display = '';
      submitBtn.style.display = '';
      if (forgotLink) forgotLink.style.display = '';
    });

    // ── Start Face ID verification ──
    startBtn.addEventListener('click', async function() {
      if (verifying) return;
      verifying = true;
      startBtn.disabled = true;
      startBtn.textContent = 'Vérification en cours...';
      resultMsg.style.display = 'none';

      const descriptor = await FaceAuth.runLivenessCheck(
        videoEl,
        function(text, step) {
          statusText.textContent = text;
          progressSteps.forEach(function(s, i) {
            if (i < step) s.classList.add('done');
            else s.classList.remove('done');
          });
        },
        function() { return cancelled; },
        overlayCanvas
      );

      if (cancelled) { verifying = false; return; }

      if (descriptor) {
        statusText.textContent = 'Comparaison du visage...';
        const email = emailInput ? emailInput.value.trim() : '';
        const res = await FaceAuth.verifyFace(email, descriptor);

        resultMsg.style.display = 'block';
        if (res.success) {
          resultMsg.style.background = 'rgba(16,185,129,0.1)';
          resultMsg.style.color = '#10b981';
          resultMsg.style.border = '1px solid rgba(16,185,129,0.3)';
          resultMsg.textContent = '✅ ' + res.message;
          statusText.textContent = 'Redirection...';
          FaceAuth.stopCamera();
          setTimeout(function() {
            window.location.href = res.redirect;
          }, 1000);
        } else {
          resultMsg.style.background = 'rgba(239,68,68,0.1)';
          resultMsg.style.color = '#ef4444';
          resultMsg.style.border = '1px solid rgba(239,68,68,0.3)';
          resultMsg.textContent = '❌ ' + res.message;
          verifying = false;
          startBtn.disabled = false;
          startBtn.textContent = 'Réessayer';
        }
      } else {
        resultMsg.style.display = 'block';
        resultMsg.style.background = 'rgba(245,158,11,0.1)';
        resultMsg.style.color = '#f59e0b';
        resultMsg.style.border = '1px solid rgba(245,158,11,0.3)';
        resultMsg.textContent = '⚠️ Vérification de vivacité échouée. Réessayez.';
        verifying = false;
        startBtn.disabled = false;
        startBtn.textContent = 'Réessayer';
      }
    });
  })();
  </script>
</body>
</html>
