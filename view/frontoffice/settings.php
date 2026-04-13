<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    $utilisateurC = new UtilisateurC();
    $id = $_SESSION['id_utilisateur'];
    $utilisateurC->deleteUtilisateur($id);
    session_destroy();
    header("Location: login.php");
    exit();
}

$pageTitle = "Paramètres"; 
$pageCSS = "cv.css"; 
$userRole = $_SESSION['role'] ?? 'Candidat'; 
?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<style>
  .settings-nav { display:flex; gap:var(--space-1); background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:var(--space-1); margin-bottom:var(--space-6); overflow-x:auto; }
  .settings-nav__item { padding:var(--space-3) var(--space-5); border-radius:var(--radius-md); font-size:var(--fs-sm); font-weight:500; color:var(--text-secondary); cursor:pointer; transition:all var(--transition-fast); white-space:nowrap; display:flex; align-items:center; gap:var(--space-2); border:none; background:none; }
  .settings-nav__item:hover { color:var(--text-primary); background:var(--bg-hover); }
  .settings-nav__item.active { background:var(--accent-primary); color:#fff; box-shadow:0 2px 8px rgba(99,102,241,0.3); }
  .settings-section { display:none; }
  .settings-section.active { display:block; }
  .settings-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:var(--space-6); margin-bottom:var(--space-5); }
  .settings-card__title { font-size:var(--fs-lg); font-weight:600; margin-bottom:var(--space-1); display:flex; align-items:center; gap:var(--space-2); }
  .settings-card__desc { font-size:var(--fs-sm); color:var(--text-secondary); margin-bottom:var(--space-5); }
  .toggle-switch { position:relative; width:44px; height:24px; background:var(--border-color); border-radius:24px; cursor:pointer; transition:background var(--transition-fast); flex-shrink:0; }
  .toggle-switch.active { background:var(--accent-primary); }
  .toggle-switch::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:transform var(--transition-fast); box-shadow:0 1px 3px rgba(0,0,0,0.2); }
  .toggle-switch.active::after { transform:translateX(20px); }
  .setting-row { display:flex; align-items:center; justify-content:space-between; padding:var(--space-4) 0; border-bottom:1px solid var(--border-color); }
  .setting-row:last-child { border-bottom:none; }
  .setting-row__info { flex:1; }
  .setting-row__label { font-weight:500; font-size:var(--fs-sm); margin-bottom:2px; }
  .setting-row__hint { font-size:var(--fs-xs); color:var(--text-tertiary); }
  .color-swatch { width:32px; height:32px; border-radius:var(--radius-sm); cursor:pointer; border:2px solid transparent; transition:all var(--transition-fast); }
  .color-swatch:hover, .color-swatch.active { border-color:var(--text-primary); transform:scale(1.15); }
</style>

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="settings" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Paramètres
  </h1>
  <p class="page-header__subtitle">Personnalisez votre expérience sur Aptus</p>
</div>

<!-- Settings Navigation Tabs -->
<div class="settings-nav" id="settings-nav">
  <button class="settings-nav__item active" data-tab="general">
    <i data-lucide="sliders-horizontal" style="width:16px;height:16px;"></i> Général
  </button>
  <button class="settings-nav__item" data-tab="appearance">
    <i data-lucide="palette" style="width:16px;height:16px;"></i> Apparence
  </button>
  <button class="settings-nav__item" data-tab="notifications">
    <i data-lucide="bell" style="width:16px;height:16px;"></i> Notifications
  </button>
  <button class="settings-nav__item" data-tab="privacy">
    <i data-lucide="shield" style="width:16px;height:16px;"></i> Confidentialité
  </button>
  <button class="settings-nav__item" data-tab="security">
    <i data-lucide="lock" style="width:16px;height:16px;"></i> Sécurité
  </button>
</div>

<!-- ═══ GENERAL ═══ -->
<div class="settings-section active" id="tab-general">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="globe" style="width:20px;height:20px;color:var(--accent-primary);"></i> Langue & Région</div>
    <div class="settings-card__desc">Choisissez la langue d'affichage et votre fuseau horaire</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="form-group">
        <label class="form-label">Langue</label>
        <select class="select">
          <option selected>Français</option>
          <option>English</option>
          <option>العربية</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Fuseau horaire</label>
        <select class="select">
          <option selected>Africa/Tunis (GMT+1)</option>
          <option>Europe/Paris (GMT+1)</option>
          <option>Europe/London (GMT+0)</option>
        </select>
      </div>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="download" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Données & Export</div>
    <div class="settings-card__desc">Téléchargez vos données ou supprimez votre compte</div>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
      <button class="btn btn-secondary"><i data-lucide="download" style="width:16px;height:16px;"></i> Exporter mes données</button>
      <form method="POST" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.');" style="margin:0;">
        <input type="hidden" name="action" value="delete_account">
        <button type="submit" class="btn btn-ghost" style="color:var(--accent-tertiary);border-color:var(--accent-tertiary);"><i data-lucide="trash-2" style="width:16px;height:16px;"></i> Supprimer mon compte</button>
      </form>
    </div>
  </div>
</div>

<!-- ═══ APPEARANCE ═══ -->
<div class="settings-section" id="tab-appearance">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="sun" style="width:20px;height:20px;color:var(--stat-orange);"></i> Thème</div>
    <div class="settings-card__desc">Choisissez le mode d'affichage de l'interface</div>
    <div style="display:flex;gap:var(--space-4);">
      <div style="flex:1;padding:var(--space-5);border:2px solid var(--accent-primary);border-radius:var(--radius-lg);text-align:center;cursor:pointer;background:var(--bg-body);">
        <i data-lucide="sun" style="width:28px;height:28px;color:var(--accent-primary);margin-bottom:var(--space-2);"></i>
        <div class="text-sm fw-semibold">Clair</div>
      </div>
      <div style="flex:1;padding:var(--space-5);border:2px solid var(--border-color);border-radius:var(--radius-lg);text-align:center;cursor:pointer;background:var(--bg-body);">
        <i data-lucide="moon" style="width:28px;height:28px;color:var(--text-secondary);margin-bottom:var(--space-2);"></i>
        <div class="text-sm fw-semibold">Sombre</div>
      </div>
      <div style="flex:1;padding:var(--space-5);border:2px solid var(--border-color);border-radius:var(--radius-lg);text-align:center;cursor:pointer;background:var(--bg-body);">
        <i data-lucide="monitor" style="width:28px;height:28px;color:var(--text-secondary);margin-bottom:var(--space-2);"></i>
        <div class="text-sm fw-semibold">Système</div>
      </div>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="palette" style="width:20px;height:20px;color:var(--accent-primary);"></i> Couleur d'accent</div>
    <div class="settings-card__desc">Personnalisez la couleur principale de l'interface</div>
    <div style="display:flex;gap:var(--space-3);">
      <div class="color-swatch active" style="background:#6366f1;"></div>
      <div class="color-swatch" style="background:#3B82F6;"></div>
      <div class="color-swatch" style="background:#8B5CF6;"></div>
      <div class="color-swatch" style="background:#EC4899;"></div>
      <div class="color-swatch" style="background:#10B981;"></div>
      <div class="color-swatch" style="background:#F59E0B;"></div>
      <div class="color-swatch" style="background:#EF4444;"></div>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="type" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Taille du texte</div>
    <div class="settings-card__desc">Ajustez la taille du texte de l'interface</div>
    <div style="display:flex;align-items:center;gap:var(--space-4);max-width:300px;">
      <span class="text-xs">A</span>
      <input type="range" style="flex:1;accent-color:var(--accent-primary);" min="12" max="20" value="14">
      <span style="font-size:1.25rem;font-weight:600;">A</span>
    </div>
  </div>
</div>

<!-- ═══ NOTIFICATIONS ═══ -->
<div class="settings-section" id="tab-notifications">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="mail" style="width:20px;height:20px;color:var(--accent-primary);"></i> Notifications par email</div>
    <div class="settings-card__desc">Gérez les emails que vous recevez d'Aptus</div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Nouveaux postes correspondants</div><div class="setting-row__hint">Recevez un email quand un poste correspond à votre profil</div></div>
      <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Mises à jour des candidatures</div><div class="setting-row__hint">Statut de vos candidatures et retours des entreprises</div></div>
      <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Nouvelles formations</div><div class="setting-row__hint">Soyez informé des nouvelles formations et certifications</div></div>
      <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Newsletter Aptus</div><div class="setting-row__hint">Actualités, conseils carrière et tendances du marché</div></div>
      <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- ═══ PRIVACY ═══ -->
<div class="settings-section" id="tab-privacy">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="eye" style="width:20px;height:20px;color:var(--accent-primary);"></i> Visibilité du profil</div>
    <div class="settings-card__desc">Contrôlez qui peut voir vos informations</div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Profil public</div><div class="setting-row__hint">Votre profil est visible par les recruteurs</div></div>
      <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Afficher l'email</div><div class="setting-row__hint">Montrer votre adresse email sur votre profil</div></div>
      <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Afficher le téléphone</div><div class="setting-row__hint">Montrer votre numéro de téléphone sur votre profil</div></div>
      <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Recherche par les recruteurs</div><div class="setting-row__hint">Permettre aux entreprises de vous trouver par recherche</div></div>
      <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- ═══ SECURITY ═══ -->
<div class="settings-section" id="tab-security">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="key" style="width:20px;height:20px;color:var(--stat-orange);"></i> Mot de passe</div>
    <div class="settings-card__desc">Modifiez votre mot de passe de connexion</div>
    <div style="display:grid;grid-template-columns:1fr;gap:var(--space-4);max-width:400px;">
      <div class="form-group">
        <label class="form-label">Mot de passe actuel</label>
        <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" class="input" placeholder="••••••••"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Nouveau mot de passe</label>
        <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" class="input" placeholder="Min. 8 caractères"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Confirmer le nouveau mot de passe</label>
        <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" class="input" placeholder="Confirmez"></div>
      </div>
      <button class="btn btn-primary" style="width:fit-content;"><i data-lucide="check" style="width:16px;height:16px;"></i> Mettre à jour</button>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="smartphone" style="width:20px;height:20px;color:var(--accent-primary);"></i> Authentification à deux facteurs</div>
    <div class="settings-card__desc">Ajoutez une couche de sécurité supplémentaire</div>
    <div class="setting-row">
      <div class="setting-row__info"><div class="setting-row__label">Activer la 2FA</div><div class="setting-row__hint">Utilisez une application d'authentification pour sécuriser votre compte</div></div>
      <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="monitor" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Sessions actives</div>
    <div class="settings-card__desc">Gérez les appareils connectés à votre compte</div>
    <div class="setting-row">
      <div class="setting-row__info" style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="monitor" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        <div><div class="setting-row__label">Chrome — Windows 11</div><div class="setting-row__hint">Tunis, Tunisie • Actif maintenant</div></div>
      </div>
      <span class="badge badge-success">Actuel</span>
    </div>
    <div class="setting-row">
      <div class="setting-row__info" style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="smartphone" style="width:20px;height:20px;color:var(--text-tertiary);"></i>
        <div><div class="setting-row__label">Safari — iPhone 15</div><div class="setting-row__hint">Tunis, Tunisie • Il y a 3 heures</div></div>
      </div>
      <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);">Révoquer</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var navItems = document.querySelectorAll('.settings-nav__item');
  navItems.forEach(function(item) {
    item.addEventListener('click', function() {
      navItems.forEach(function(n) { n.classList.remove('active'); });
      document.querySelectorAll('.settings-section').forEach(function(s) { s.classList.remove('active'); });
      item.classList.add('active');
      var tab = document.getElementById('tab-' + item.getAttribute('data-tab'));
      if (tab) tab.classList.add('active');
    });
  });
});
</script>
