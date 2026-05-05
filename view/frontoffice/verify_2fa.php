<?php
session_start();
if (!isset($_SESSION['pending_2fa_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vérification 2FA — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/auth.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .auth-card { max-width: 420px; }
    .otp-input-group { display: flex; gap: 10px; justify-content: center; margin: 20px 0; }
    .otp-field { width: 45px; height: 55px; border: 2px solid var(--border-color); border-radius: 8px; text-align: center; font-size: 24px; font-weight: 700; background: var(--bg-input); color: var(--text-primary); transition: all 0.2s; }
    .otp-field:focus { border-color: var(--accent-primary); outline: none; box-shadow: 0 0 0 4px var(--accent-primary-light); }
  </style>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-card__header">
          <div style="color: var(--accent-primary); margin-bottom: var(--space-4);">
             <i data-lucide="smartphone" style="width: 48px; height: 48px; margin: 0 auto;"></i>
          </div>
          <h1>Vérification 2FA</h1>
          <p>Entrez le code à 6 chiffres de votre application d'authentification.</p>
      </div>

      <div id="2fa-error" class="alert alert-danger" style="display:none; font-size: 14px; margin-bottom: 20px; padding: 10px; border-radius: var(--radius-sm); text-align: left; background-color: rgba(231, 76, 60, 0.1); color: #e74c3c;"></div>

      <form id="2fa-form" class="auth-form">
        <div class="otp-input-group">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
          <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
        </div>
        
        <button type="submit" id="submit-btn" class="btn btn-primary btn-lg w-full" style="margin-top:var(--space-2);">Vérifier</button>
      </form>

      <div class="auth-footer" style="margin-top: var(--space-6);">
          <a href="login.php" class="back-to-site" style="margin-top: 0;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Retour à la connexion
          </a>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();

    const fields = document.querySelectorAll('.otp-field');
    const form = document.getElementById('2fa-form');
    const errorEl = document.getElementById('2fa-error');
    const submitBtn = document.getElementById('submit-btn');

    fields[0].focus();

    fields.forEach((field, index) => {
      field.addEventListener('input', (e) => {
        if (e.target.value.length > 0 && index < fields.length - 1) {
          fields[index + 1].focus();
        }
      });

      field.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
          fields[index - 1].focus();
        }
      });
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorEl.style.display = 'none';
      
      const code = Array.from(fields).map(f => f.value).join('');
      if (code.length !== 6) {
        errorEl.textContent = 'Veuillez entrer le code complet.';
        errorEl.style.display = 'block';
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:18px;height:18px;"></i> Vérification...';
      lucide.createIcons();

      try {
        const res = await fetch('/aptus_first_official_version/controller/TwoFactorC.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'verify_login', code })
        });
        const data = await res.json();

        if (data.success) {
          window.location.href = data.redirect;
        } else {
          errorEl.textContent = data.message;
          errorEl.style.display = 'block';
          submitBtn.disabled = false;
          submitBtn.textContent = 'Vérifier';
        }
      } catch (err) {
        errorEl.textContent = 'Erreur de connexion au serveur.';
        errorEl.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Vérifier';
      }
    });
  </script>
</body>
</html>
