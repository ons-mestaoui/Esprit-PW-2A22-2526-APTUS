<?php $pageTitle = "Paramètres"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Included inside layout_back.php -->

<style>
  .settings-nav { display:flex; gap:var(--space-1); background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:var(--space-1); margin-bottom:var(--space-6); overflow-x:auto; }
  .settings-nav__item { padding:var(--space-3) var(--space-5); border-radius:var(--radius-md); font-size:var(--fs-sm); font-weight:500; color:var(--text-secondary); cursor:pointer; transition:all 0.2s; white-space:nowrap; display:flex; align-items:center; gap:var(--space-2); border:none; background:none; }
  .settings-nav__item:hover { color:var(--text-primary); background:var(--bg-hover); }
  .settings-nav__item.active { background:var(--accent-primary); color:#fff; box-shadow:0 2px 8px rgba(99,102,241,0.3); }
  .settings-section { display:none; }
  .settings-section.active { display:block; }
  .settings-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:var(--space-6); margin-bottom:var(--space-5); }
  .settings-card__title { font-size:var(--fs-lg); font-weight:600; margin-bottom:var(--space-1); display:flex; align-items:center; gap:var(--space-2); }
  .settings-card__desc { font-size:var(--fs-sm); color:var(--text-secondary); margin-bottom:var(--space-5); }
  .toggle-row { display:flex; align-items:center; justify-content:space-between; padding:var(--space-4) 0; border-bottom:1px solid var(--border-color); }
  .toggle-row:last-child { border-bottom:none; }
  .toggle-row__info { flex:1; }
  .toggle-row__label { font-weight:500; font-size:var(--fs-sm); margin-bottom:2px; }
  .toggle-row__hint { font-size:var(--fs-xs); color:var(--text-tertiary); }
  .toggle-sw { position:relative; width:44px; height:24px; background:var(--border-color); border-radius:24px; cursor:pointer; transition:background 0.2s; flex-shrink:0; }
  .toggle-sw.active { background:var(--accent-primary); }
  .toggle-sw::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:transform 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
  .toggle-sw.active::after { transform:translateX(20px); }
</style>

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Paramètres</h1>
      <p>Configuration générale de la plateforme Aptus</p>
    </div>
  </div>
</div>

<!-- Settings Navigation Tabs -->
<div class="settings-nav" id="settings-nav">
  <button class="settings-nav__item active" data-tab="general">
    <i data-lucide="sliders-horizontal" style="width:16px;height:16px;"></i> Général
  </button>
  <button class="settings-nav__item" data-tab="platform">
    <i data-lucide="globe" style="width:16px;height:16px;"></i> Plateforme
  </button>
  <button class="settings-nav__item" data-tab="email">
    <i data-lucide="mail" style="width:16px;height:16px;"></i> Emails
  </button>
  <button class="settings-nav__item" data-tab="security">
    <i data-lucide="shield" style="width:16px;height:16px;"></i> Sécurité
  </button>
  <button class="settings-nav__item" data-tab="maintenance">
    <i data-lucide="wrench" style="width:16px;height:16px;"></i> Maintenance
  </button>
</div>

<!-- ═══ GENERAL ═══ -->
<div class="settings-section active" id="tab-general">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="globe" style="width:20px;height:20px;color:var(--accent-primary);"></i> Site Web</div>
    <div class="settings-card__desc">Paramètres généraux du site</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="form-group">
        <label class="form-label">Nom du site</label>
        <input type="text" class="input" value="Aptus">
      </div>
      <div class="form-group">
        <label class="form-label">URL du site</label>
        <input type="url" class="input" value="https://aptus.tn">
      </div>
      <div class="form-group" style="grid-column:1/-1;">
        <label class="form-label">Description</label>
        <textarea class="textarea" rows="2">Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.</textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Langue par défaut</label>
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
        </select>
      </div>
    </div>
  </div>
  <div style="display:flex;justify-content:flex-end;">
    <button class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer</button>
  </div>
</div>

<!-- ═══ PLATFORM ═══ -->
<div class="settings-section" id="tab-platform">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="settings" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Fonctionnalités</div>
    <div class="settings-card__desc">Activez ou désactivez les modules de la plateforme</div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Inscription Candidats</div><div class="toggle-row__hint">Permettre aux candidats de s'inscrire sur la plateforme</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Inscription Entreprises</div><div class="toggle-row__hint">Permettre aux entreprises de créer un compte</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Module CV Builder</div><div class="toggle-row__hint">Activer le générateur de CV avec l'IA</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Module Formations</div><div class="toggle-row__hint">Activer le catalogue de formations</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Veille du Marché</div><div class="toggle-row__hint">Afficher les tendances et statistiques du marché</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Matching IA</div><div class="toggle-row__hint">Activer le matching intelligent entre candidats et offres</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- ═══ EMAIL ═══ -->
<div class="settings-section" id="tab-email">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="mail" style="width:20px;height:20px;color:var(--accent-primary);"></i> Serveur SMTP</div>
    <div class="settings-card__desc">Configuration de l'envoi d'emails</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="form-group">
        <label class="form-label">Serveur SMTP</label>
        <input type="text" class="input" value="smtp.aptus.tn">
      </div>
      <div class="form-group">
        <label class="form-label">Port</label>
        <input type="number" class="input" value="587">
      </div>
      <div class="form-group">
        <label class="form-label">Email d'expédition</label>
        <input type="email" class="input" value="noreply@aptus.tn">
      </div>
      <div class="form-group">
        <label class="form-label">Nom d'expédition</label>
        <input type="text" class="input" value="Aptus Platform">
      </div>
    </div>
  </div>
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="bell" style="width:20px;height:20px;color:var(--stat-orange);"></i> Notifications Admin</div>
    <div class="settings-card__desc">Choisissez quand recevoir des notifications</div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Nouvelle inscription</div><div class="toggle-row__hint">Email quand un nouvel utilisateur s'inscrit</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Signalement</div><div class="toggle-row__hint">Email quand un contenu est signalé</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Rapport hebdomadaire</div><div class="toggle-row__hint">Résumé des statistiques chaque lundi</div></div>
      <div class="toggle-sw" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- ═══ SECURITY ═══ -->
<div class="settings-section" id="tab-security">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="shield" style="width:20px;height:20px;color:var(--accent-primary);"></i> Politique de sécurité</div>
    <div class="settings-card__desc">Règles de sécurité appliquées aux comptes</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="form-group">
        <label class="form-label">Longueur min. mot de passe</label>
        <input type="number" class="input" value="8">
      </div>
      <div class="form-group">
        <label class="form-label">Expiration session (minutes)</label>
        <input type="number" class="input" value="120">
      </div>
    </div>
    <div class="toggle-row" style="margin-top:var(--space-4);">
      <div class="toggle-row__info"><div class="toggle-row__label">Forcer la 2FA pour les admins</div><div class="toggle-row__hint">Exiger l'authentification à deux facteurs</div></div>
      <div class="toggle-sw" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Bloquer après 5 tentatives</div><div class="toggle-row__hint">Verrouiller le compte après 5 tentatives échouées</div></div>
      <div class="toggle-sw active" onclick="this.classList.toggle('active')"></div>
    </div>
  </div>
</div>

<!-- ═══ MAINTENANCE ═══ -->
<div class="settings-section" id="tab-maintenance">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="wrench" style="width:20px;height:20px;color:var(--stat-orange);"></i> Mode Maintenance</div>
    <div class="settings-card__desc">Contrôle de l'accessibilité du site</div>
    <div class="toggle-row">
      <div class="toggle-row__info"><div class="toggle-row__label">Activer le mode maintenance</div><div class="toggle-row__hint">Les visiteurs verront un message de maintenance</div></div>
      <div class="toggle-sw" onclick="this.classList.toggle('active')"></div>
    </div>
    <div class="form-group" style="margin-top:var(--space-4);">
      <label class="form-label">Message de maintenance</label>
      <textarea class="textarea" rows="3">Le site est en cours de maintenance. Nous serons de retour très bientôt !</textarea>
    </div>
  </div>
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="database" style="width:20px;height:20px;color:var(--accent-primary);"></i> Base de données</div>
    <div class="settings-card__desc">Actions de maintenance de la base de données</div>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
      <button class="btn btn-secondary"><i data-lucide="download" style="width:16px;height:16px;"></i> Sauvegarder la BDD</button>
      <button class="btn btn-secondary"><i data-lucide="refresh-ccw" style="width:16px;height:16px;"></i> Vider le cache</button>
      <button class="btn btn-ghost" style="color:var(--accent-tertiary);border-color:var(--accent-tertiary);"><i data-lucide="alert-triangle" style="width:16px;height:16px;"></i> Réinitialiser</button>
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
