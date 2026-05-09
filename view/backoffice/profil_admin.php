<?php $pageTitle = "Profil Admin"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Included inside layout_back.php -->

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Mon Profil</h1>
      <p>Gérez vos informations d'administrateur</p>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Admin Photo & Quick Info -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-4);font-size:2.5rem;font-weight:700;color:#fff;">
      AD
    </div>
    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);">Administrateur</h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);">Super Admin</p>
    <span class="badge badge-primary" style="margin-bottom:var(--space-5);">Super Admin</span>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">admin@aptus.com</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="shield-check" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Accès complet</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Depuis le 01 Jan. 2026</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="clock" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Dernière connexion : Aujourd'hui</span>
      </div>
    </div>

    <button class="btn btn-secondary w-full" style="margin-top:var(--space-5);">
      <i data-lucide="camera" style="width:16px;height:16px;"></i>
      Changer la photo
    </button>
  </div>

  <!-- Right: Edit Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <!-- Personal Info -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="user" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        Informations Personnelles
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom complet</label>
          <input type="text" class="input" value="Administrateur">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" value="admin@aptus.com">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <div class="input-icon-wrapper">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
            <input type="tel" class="input" value="+216 70 000 000">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Rôle</label>
          <input type="text" class="input" value="Super Admin" disabled style="opacity:0.7;">
        </div>
      </div>
    </div>

    <!-- Security -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="lock" style="width:20px;height:20px;color:var(--accent-secondary);"></i>
        Changer le mot de passe
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Mot de passe actuel</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" placeholder="••••••••">
          </div>
        </div>
        <div></div>
        <div class="form-group">
          <label class="form-label">Nouveau mot de passe</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" placeholder="Min. 12 caractères">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirmer</label>
          <div class="input-icon-wrapper">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            <input type="password" class="input" placeholder="Confirmez">
          </div>
        </div>
      </div>
    </div>

    <!-- Save -->
    <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
      <button class="btn btn-ghost">Annuler</button>
      <button class="btn btn-primary">
        <i data-lucide="save" style="width:18px;height:18px;"></i>
        Enregistrer
      </button>
    </div>
  </div>
</div>
