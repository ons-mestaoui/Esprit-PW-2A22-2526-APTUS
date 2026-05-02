<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../controller/SettingsAdminC.php';

$settingsC = new SettingsAdminC();
$pageTitle = "Paramètres"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        $settingsC->saveSettings($_POST);
        $successMsg = "Les paramètres ont été mis à jour avec succès.";
    } elseif ($_POST['action'] === 'upload_logo') {
        if (isset($_FILES['admin_logo']) && $_FILES['admin_logo']['error'] === UPLOAD_ERR_OK) {
            $result = $settingsC->uploadLogo($_FILES['admin_logo']);
            $successMsg = $result['success'] ? "Logo mis à jour avec succès." : $result['message'];
        }
    } elseif ($_POST['action'] === 'remove_logo') {
        $settingsC->removeLogo();
        $successMsg = "Logo supprimé avec succès.";
    } elseif ($_POST['action'] === 'maintenance_action') {
        if ($_POST['type'] === 'Vider le cache') {
            $settingsC->clearCache();
            $successMsg = "Le cache a été vidé avec succès.";
        } elseif ($_POST['type'] === 'Sauvegarder BDD') {
            $settingsC->backupDB();
            $successMsg = "La base de données a été sauvegardée avec succès.";
        } elseif ($_POST['type'] === 'Réinitialiser') {
            $settingsC->resetSettings();
            $successMsg = "Les paramètres ont été réinitialisés aux valeurs par défaut.";
        }
    }
}

$settings = $settingsC->getSettings();
?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>

<style>
  .settings-nav { display:flex; gap:var(--space-1); background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:var(--space-1); margin-bottom:var(--space-6); overflow-x:auto; }
  .settings-nav__item { padding:var(--space-3) var(--space-5); border-radius:var(--radius-md); font-size:var(--fs-sm); font-weight:500; color:var(--text-secondary); cursor:pointer; transition:all 0.2s; white-space:nowrap; display:flex; align-items:center; gap:var(--space-2); border:none; background:none; }
  .settings-nav__item:hover { color:var(--text-primary); background:var(--bg-secondary); }
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

<?php if(isset($successMsg)): ?>
<div class="alert alert-success" style="margin-bottom:var(--space-4); padding:var(--space-3); background:#d1fae5; color:#065f46; border-radius:var(--radius-md); border:1px solid #10b981;">
  <i data-lucide="check-circle" style="width:18px;height:18px;vertical-align:-4px;"></i> <?= $successMsg ?>
</div>
<?php endif; ?>

<!-- Settings Navigation Tabs -->
<div class="settings-nav" id="settings-nav">
  <button class="settings-nav__item active" data-tab="appearance">
    <i data-lucide="palette" style="width:16px;height:16px;"></i> Apparence
  </button>
  <button class="settings-nav__item" data-tab="general">
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

<!-- ═══ APPARENCE ═══ -->
<div class="settings-section active" id="tab-appearance">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">

    <!-- Colors -->
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="palette" style="width:20px;height:20px;color:var(--accent-primary);"></i> Couleurs</div>
      <div class="settings-card__desc">Personnalisez les couleurs de la plateforme</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-5);">
        <div class="form-group">
          <label class="form-label">Couleur principale</label>
          <div style="display:flex;align-items:center;gap:var(--space-3);">
            <input type="color" name="primary_color" id="inp-primary-color" value="<?= htmlspecialchars($settings['primary_color']) ?>" style="width:48px;height:40px;border:2px solid var(--border-color);border-radius:var(--radius-sm);cursor:pointer;padding:2px;background:var(--bg-input);">
            <input type="text" class="input" id="inp-primary-hex" value="<?= htmlspecialchars($settings['primary_color']) ?>" style="flex:1;font-family:monospace;" maxlength="7">
          </div>
          <div style="display:flex;gap:var(--space-2);margin-top:var(--space-2);" id="primary-presets">
            <?php foreach(['#6B34A3','#6366F1','#3B82F6','#0EA5E9','#10B981','#F59E0B','#EF4444','#EC4899'] as $c): ?>
            <button type="button" class="color-preset" data-color="<?= $c ?>" data-target="primary" style="width:28px;height:28px;border-radius:50%;border:2px solid <?= $settings['primary_color']===$c ? 'var(--text-primary)' : 'transparent' ?>;background:<?= $c ?>;cursor:pointer;transition:all 0.2s;" title="<?= $c ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Couleur d'accent</label>
          <div style="display:flex;align-items:center;gap:var(--space-3);">
            <input type="color" name="accent_color" id="inp-accent-color" value="<?= htmlspecialchars($settings['accent_color']) ?>" style="width:48px;height:40px;border:2px solid var(--border-color);border-radius:var(--radius-sm);cursor:pointer;padding:2px;background:var(--bg-input);">
            <input type="text" class="input" id="inp-accent-hex" value="<?= htmlspecialchars($settings['accent_color']) ?>" style="flex:1;font-family:monospace;" maxlength="7">
          </div>
          <div style="display:flex;gap:var(--space-2);margin-top:var(--space-2);" id="accent-presets">
            <?php foreach(['#00A3DA','#6366F1','#8B5CF6','#EC4899','#14B8A6','#F97316','#84CC16','#06B6D4'] as $c): ?>
            <button type="button" class="color-preset" data-color="<?= $c ?>" data-target="accent" style="width:28px;height:28px;border-radius:50%;border:2px solid <?= $settings['accent_color']===$c ? 'var(--text-primary)' : 'transparent' ?>;background:<?= $c ?>;cursor:pointer;transition:all 0.2s;" title="<?= $c ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- Live Preview -->
      <div style="margin-top:var(--space-5);padding:var(--space-4);border-radius:var(--radius-md);border:1px dashed var(--border-color);background:var(--bg-secondary);">
        <div style="font-size:var(--fs-xs);color:var(--text-tertiary);margin-bottom:var(--space-2);text-transform:uppercase;letter-spacing:0.5px;">Aperçu en direct</div>
        <div style="display:flex;gap:var(--space-3);align-items:center;flex-wrap:wrap;">
          <button type="button" class="btn btn-primary" id="preview-btn-primary" style="pointer-events:none;">Bouton Principal</button>
          <button type="button" class="btn btn-secondary" id="preview-btn-accent" style="pointer-events:none;">Bouton Accent</button>
          <span id="preview-link" style="color:var(--accent-primary);font-weight:500;font-size:var(--fs-sm);cursor:default;">Lien exemple</span>
          <div id="preview-badge" style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:var(--radius-full);font-size:var(--fs-xs);font-weight:600;">Badge</div>
        </div>
      </div>
    </div>

    <!-- Typography -->
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="type" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Typographie</div>
      <div class="settings-card__desc">Police de caractères utilisée sur la plateforme</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Police</label>
          <select class="select" name="font_family" id="inp-font-family">
            <?php foreach(['Inter','Roboto','Outfit','Poppins','DM Sans','Plus Jakarta Sans'] as $f): ?>
            <option value="<?= $f ?>" <?= $settings['font_family']===$f ? 'selected' : '' ?> style="font-family:'<?= $f ?>',sans-serif;"><?= $f ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Aperçu</label>
          <div id="font-preview" style="padding:var(--space-3);background:var(--bg-input);border-radius:var(--radius-md);border:1px solid var(--border-color);font-family:'<?= $settings['font_family'] ?>',sans-serif;">
            <div style="font-weight:700;font-size:var(--fs-md);margin-bottom:4px;">Aptus Platform</div>
            <div style="font-size:var(--fs-sm);color:var(--text-secondary);">La plateforme intelligente de recrutement 1234567890</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Shape & Theme -->
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="square" style="width:20px;height:20px;color:var(--stat-orange);"></i> Forme & Thème</div>
      <div class="settings-card__desc">Arrondis des éléments et thème par défaut</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Arrondi des coins</label>
          <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;" id="radius-options">
            <?php
            $radiusOptions = [
              'none' => ['label' => 'Aucun', 'preview' => '0px'],
              'small' => ['label' => 'Petit', 'preview' => '4px'],
              'medium' => ['label' => 'Moyen', 'preview' => '12px'],
              'large' => ['label' => 'Grand', 'preview' => '20px'],
              'full' => ['label' => 'Complet', 'preview' => '28px'],
            ];
            foreach ($radiusOptions as $val => $opt): ?>
            <button type="button" class="radius-option <?= $settings['border_radius']===$val ? 'active' : '' ?>" data-value="<?= $val ?>" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:var(--space-2) var(--space-3);border:2px solid <?= $settings['border_radius']===$val ? 'var(--accent-primary)' : 'var(--border-color)' ?>;border-radius:var(--radius-md);background:var(--bg-input);cursor:pointer;transition:all 0.2s;min-width:60px;">
              <div style="width:32px;height:22px;border:2px solid var(--text-secondary);border-radius:<?= $opt['preview'] ?>;"></div>
              <span style="font-size:11px;color:var(--text-secondary);"><?= $opt['label'] ?></span>
            </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="border_radius" id="inp-border-radius" value="<?= htmlspecialchars($settings['border_radius']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Thème par défaut</label>
          <div style="display:flex;gap:var(--space-2);" id="theme-options">
            <?php
            $themeOptions = [
              'light' => ['label' => 'Clair', 'icon' => 'sun'],
              'dark' => ['label' => 'Sombre', 'icon' => 'moon'],
              'system' => ['label' => 'Système', 'icon' => 'monitor'],
            ];
            foreach ($themeOptions as $val => $opt): ?>
            <button type="button" class="theme-option <?= $settings['default_theme']===$val ? 'active' : '' ?>" data-value="<?= $val ?>" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;padding:var(--space-3);border:2px solid <?= $settings['default_theme']===$val ? 'var(--accent-primary)' : 'var(--border-color)' ?>;border-radius:var(--radius-md);background:var(--bg-input);cursor:pointer;transition:all 0.2s;">
              <i data-lucide="<?= $opt['icon'] ?>" style="width:20px;height:20px;"></i>
              <span style="font-size:12px;"><?= $opt['label'] ?></span>
            </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="default_theme" id="inp-default-theme" value="<?= htmlspecialchars($settings['default_theme']) ?>">
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer l'apparence</button>
    </div>
  </form>

  <!-- Logo Upload (separate form with enctype) -->
  <div class="settings-card" style="margin-top:var(--space-5);">
    <div class="settings-card__title"><i data-lucide="image" style="width:20px;height:20px;color:var(--accent-primary);"></i> Logo de la plateforme</div>
    <div class="settings-card__desc">Téléchargez un logo personnalisé (PNG, JPG, SVG, WebP — max 2 Mo)</div>
    <div style="display:flex;align-items:center;gap:var(--space-5);flex-wrap:wrap;">
      <div style="width:80px;height:80px;border-radius:var(--radius-md);border:2px dashed var(--border-color);display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--bg-input);">
        <?php if (!empty($settings['admin_logo'])): ?>
          <img src="<?= htmlspecialchars($settings['admin_logo']) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
        <?php else: ?>
          <i data-lucide="image-plus" style="width:28px;height:28px;color:var(--text-tertiary);"></i>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:var(--space-2);flex-direction:column;">
        <form method="POST" action="" enctype="multipart/form-data" style="display:flex;gap:var(--space-2);align-items:center;">
          <input type="hidden" name="action" value="upload_logo">
          <input type="file" name="admin_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="input" style="max-width:260px;font-size:var(--fs-xs);">
          <button type="submit" class="btn btn-secondary" style="white-space:nowrap;"><i data-lucide="upload" style="width:14px;height:14px;"></i> Télécharger</button>
        </form>
        <?php if (!empty($settings['admin_logo'])): ?>
        <form method="POST" action="" style="margin:0;">
          <input type="hidden" name="action" value="remove_logo">
          <button type="submit" class="btn btn-ghost" style="color:var(--accent-tertiary);font-size:var(--fs-xs);padding:4px 8px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i> Supprimer le logo</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ═══ GENERAL ═══ -->
<div class="settings-section" id="tab-general">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="globe" style="width:20px;height:20px;color:var(--accent-primary);"></i> Site Web</div>
      <div class="settings-card__desc">Paramètres généraux du site</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom du site</label>
          <input type="text" name="site_name" class="input" value="<?= htmlspecialchars($settings['site_name']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">URL du site</label>
          <input type="url" name="site_url" class="input" value="<?= htmlspecialchars($settings['site_url']) ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Description</label>
          <textarea class="textarea" name="site_desc" rows="2"><?= htmlspecialchars($settings['site_desc']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Langue par défaut</label>
          <select class="select" name="language">
            <option <?= $settings['language'] == 'Français' ? 'selected' : '' ?>>Français</option>
            <option <?= $settings['language'] == 'English' ? 'selected' : '' ?>>English</option>
            <option <?= $settings['language'] == 'العربية' ? 'selected' : '' ?>>العربية</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Fuseau horaire</label>
          <select class="select" name="timezone">
            <option <?= $settings['timezone'] == 'Africa/Tunis (GMT+1)' ? 'selected' : '' ?>>Africa/Tunis (GMT+1)</option>
            <option <?= $settings['timezone'] == 'Europe/Paris (GMT+1)' ? 'selected' : '' ?>>Europe/Paris (GMT+1)</option>
          </select>
        </div>
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer</button>
    </div>
  </form>
</div>

<!-- ═══ PLATFORM ═══ -->
<div class="settings-section" id="tab-platform">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="settings" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Fonctionnalités</div>
      <div class="settings-card__desc">Activez ou désactivez les modules de la plateforme</div>
      
      <?php
      $platformToggles = [
          'reg_candidat' => ['label' => 'Inscription Candidats', 'hint' => 'Permettre aux candidats de s\'inscrire sur la plateforme'],
          'reg_entreprise' => ['label' => 'Inscription Entreprises', 'hint' => 'Permettre aux entreprises de créer un compte'],
          'mod_cv' => ['label' => 'Module CV Builder', 'hint' => 'Activer le générateur de CV avec l\'IA'],
          'mod_formations' => ['label' => 'Module Formations', 'hint' => 'Activer le catalogue de formations'],
          'mod_veille' => ['label' => 'Veille du Marché', 'hint' => 'Afficher les tendances et statistiques du marché'],
          'mod_matching' => ['label' => 'Matching IA', 'hint' => 'Activer le matching intelligent entre candidats et offres']
      ];
      foreach ($platformToggles as $key => $data):
      ?>
      <div class="toggle-row">
        <div class="toggle-row__info">
            <div class="toggle-row__label"><?= $data['label'] ?></div>
            <div class="toggle-row__hint"><?= $data['hint'] ?></div>
        </div>
        <div class="toggle-sw <?= $settings[$key] ? 'active' : '' ?>" onclick="this.classList.toggle('active'); document.getElementById('input_<?= $key ?>').value = this.classList.contains('active') ? 'true' : 'false';"></div>
        <input type="hidden" name="<?= $key ?>" id="input_<?= $key ?>" value="<?= $settings[$key] ? 'true' : 'false' ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer</button>
    </div>
  </form>
</div>

<!-- ═══ EMAIL ═══ -->
<div class="settings-section" id="tab-email">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="mail" style="width:20px;height:20px;color:var(--accent-primary);"></i> Serveur SMTP</div>
      <div class="settings-card__desc">Configuration de l'envoi d'emails</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Serveur SMTP</label>
          <input type="text" name="smtp_server" class="input" value="<?= htmlspecialchars($settings['smtp_server']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Port</label>
          <input type="number" name="smtp_port" class="input" value="<?= htmlspecialchars($settings['smtp_port']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email d'expédition</label>
          <input type="email" name="smtp_email" class="input" value="<?= htmlspecialchars($settings['smtp_email']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Nom d'expédition</label>
          <input type="text" name="smtp_name" class="input" value="<?= htmlspecialchars($settings['smtp_name']) ?>">
        </div>
      </div>
    </div>
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="bell" style="width:20px;height:20px;color:var(--stat-orange);"></i> Notifications Admin</div>
      <div class="settings-card__desc">Choisissez quand recevoir des notifications</div>
      
      <?php
      $emailToggles = [
          'notif_new_user' => ['label' => 'Nouvelle inscription', 'hint' => 'Email quand un nouvel utilisateur s\'inscrit'],
          'notif_report' => ['label' => 'Signalement', 'hint' => 'Email quand un contenu est signalé'],
          'notif_weekly' => ['label' => 'Rapport hebdomadaire', 'hint' => 'Résumé des statistiques chaque lundi']
      ];
      foreach ($emailToggles as $key => $data):
      ?>
      <div class="toggle-row">
        <div class="toggle-row__info">
            <div class="toggle-row__label"><?= $data['label'] ?></div>
            <div class="toggle-row__hint"><?= $data['hint'] ?></div>
        </div>
        <div class="toggle-sw <?= $settings[$key] ? 'active' : '' ?>" onclick="this.classList.toggle('active'); document.getElementById('input_<?= $key ?>').value = this.classList.contains('active') ? 'true' : 'false';"></div>
        <input type="hidden" name="<?= $key ?>" id="input_<?= $key ?>" value="<?= $settings[$key] ? 'true' : 'false' ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer Emails</button>
    </div>
  </form>
</div>

<!-- ═══ SECURITY ═══ -->
<div class="settings-section" id="tab-security">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="shield" style="width:20px;height:20px;color:var(--accent-primary);"></i> Politique de sécurité</div>
      <div class="settings-card__desc">Règles de sécurité appliquées aux comptes</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Longueur min. mot de passe</label>
          <input type="number" name="min_pass_length" class="input" value="<?= htmlspecialchars($settings['min_pass_length']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Expiration session (minutes)</label>
          <input type="number" name="session_expiry" class="input" value="<?= htmlspecialchars($settings['session_expiry']) ?>">
        </div>
      </div>
      <div class="toggle-row" style="margin-top:var(--space-4);">
        <div class="toggle-row__info"><div class="toggle-row__label">Forcer la 2FA pour les admins</div><div class="toggle-row__hint">Exiger l'authentification à deux facteurs</div></div>
        <div class="toggle-sw <?= $settings['force_2fa'] ? 'active' : '' ?>" onclick="this.classList.toggle('active'); document.getElementById('input_force_2fa').value = this.classList.contains('active') ? 'true' : 'false';"></div>
        <input type="hidden" name="force_2fa" id="input_force_2fa" value="<?= $settings['force_2fa'] ? 'true' : 'false' ?>">
      </div>
      <div class="toggle-row">
        <div class="toggle-row__info"><div class="toggle-row__label">Bloquer après 5 tentatives</div><div class="toggle-row__hint">Verrouiller le compte après 5 tentatives échouées</div></div>
        <div class="toggle-sw <?= $settings['block_after_5'] ? 'active' : '' ?>" onclick="this.classList.toggle('active'); document.getElementById('input_block_after_5').value = this.classList.contains('active') ? 'true' : 'false';"></div>
        <input type="hidden" name="block_after_5" id="input_block_after_5" value="<?= $settings['block_after_5'] ? 'true' : 'false' ?>">
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:var(--space-4);">
        <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer Sécurité</button>
      </div>
    </div>
  </form>

  <!-- ═══ FACE ID ENROLLMENT ═══ -->
  <div class="settings-card" id="faceid-card">
    <div class="settings-card__title"><i data-lucide="scan-face" style="width:20px;height:20px;color:var(--accent-primary);"></i> Face ID — Reconnaissance Faciale</div>
    <div class="settings-card__desc">Connectez-vous avec votre visage. La vérification de vivacité garantit qu'une vraie personne est présente.</div>
    
    <div id="faceid-status" class="toggle-row" style="border-bottom:none; padding-bottom:0;">
      <div class="toggle-row__info">
        <div class="toggle-row__label" id="faceid-status-label">Chargement...</div>
        <div class="toggle-row__hint" id="faceid-status-hint">Vérification du statut Face ID</div>
      </div>
      <div id="faceid-actions" style="display:flex;gap:var(--space-2);"></div>
    </div>
  </div>
</div>

<!-- Face ID Enrollment Modal -->
<div id="faceid-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:9999; backdrop-filter:blur(6px);">
  <div style="background:var(--bg-card); border-radius:var(--radius-xl); padding:var(--space-8); text-align:center; max-width:560px; width:95%; position:relative; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
    <button type="button" id="faceid-modal-close" style="position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; color:var(--text-secondary); padding:4px;">
      <i data-lucide="x" style="width:22px;height:22px;"></i>
    </button>
    
    <div style="width:64px; height:64px; background:rgba(99,102,241,0.12); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-4) auto;">
      <i data-lucide="scan-face" style="width:32px;height:32px;color:var(--accent-primary);"></i>
    </div>
    
    <h3 style="font-size:20px; font-weight:700; margin-bottom:var(--space-2);">Enregistrement Face ID</h3>
    <p id="faceid-instruction" style="font-size:14px; color:var(--text-secondary); margin-bottom:var(--space-5);">Suivez les instructions pour enregistrer votre visage.</p>
    
    <!-- Progress Steps -->
    <div id="faceid-progress" style="display:flex; justify-content:center; gap:var(--space-2); margin-bottom:var(--space-5);">
      <div class="faceid-step" data-step="1" style="width:40px; height:4px; border-radius:2px; background:var(--border-color); transition:background 0.3s;"></div>
      <div class="faceid-step" data-step="2" style="width:40px; height:4px; border-radius:2px; background:var(--border-color); transition:background 0.3s;"></div>
      <div class="faceid-step" data-step="3" style="width:40px; height:4px; border-radius:2px; background:var(--border-color); transition:background 0.3s;"></div>
      <div class="faceid-step" data-step="4" style="width:40px; height:4px; border-radius:2px; background:var(--border-color); transition:background 0.3s;"></div>
    </div>
    
    <!-- Camera Container -->
    <div id="faceid-camera-box" style="position:relative; width:100%; max-width:420px; margin:0 auto var(--space-5); border-radius:var(--radius-lg); overflow:hidden; background:#000; aspect-ratio:4/3;">
      <video id="faceid-video" autoplay muted playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
      <canvas id="faceid-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1); pointer-events:none;"></canvas>
      <div id="faceid-scan-ring" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:200px; height:200px; border-radius:50%; border:3px dashed rgba(99,102,241,0.5); animation:faceid-pulse 2s infinite;"></div>
    </div>
    
    <div id="faceid-result" style="display:none; padding:var(--space-3); border-radius:var(--radius-md); margin-bottom:var(--space-4); font-size:14px; font-weight:500;"></div>
    
    <button type="button" id="faceid-start-btn" class="btn btn-primary btn-lg" style="width:100%;">
      <i data-lucide="camera" style="width:18px;height:18px;"></i> Démarrer l'enregistrement
    </button>
  </div>
</div>

<style>
  @keyframes faceid-pulse {
    0%, 100% { opacity:0.4; transform:translate(-50%,-50%) scale(1); }
    50% { opacity:1; transform:translate(-50%,-50%) scale(1.05); }
  }
</style>

<!-- ═══ MAINTENANCE ═══ -->
<div class="settings-section" id="tab-maintenance">
  <form method="POST" action="">
    <input type="hidden" name="action" value="save_settings">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="wrench" style="width:20px;height:20px;color:var(--stat-orange);"></i> Mode Maintenance</div>
      <div class="settings-card__desc">Contrôle de l'accessibilité du site</div>
      <div class="toggle-row">
        <div class="toggle-row__info"><div class="toggle-row__label">Activer le mode maintenance</div><div class="toggle-row__hint">Les visiteurs verront un message de maintenance</div></div>
        <div class="toggle-sw <?= $settings['maintenance_mode'] ? 'active' : '' ?>" onclick="this.classList.toggle('active'); document.getElementById('input_maintenance_mode').value = this.classList.contains('active') ? 'true' : 'false';"></div>
        <input type="hidden" name="maintenance_mode" id="input_maintenance_mode" value="<?= $settings['maintenance_mode'] ? 'true' : 'false' ?>">
      </div>
      <div class="form-group" style="margin-top:var(--space-4);">
        <label class="form-label">Message de maintenance</label>
        <textarea class="textarea" name="maintenance_msg" rows="3"><?= htmlspecialchars($settings['maintenance_msg']) ?></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:var(--space-4);">
        <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer Maintenance</button>
      </div>
    </div>
  </form>
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="database" style="width:20px;height:20px;color:var(--accent-primary);"></i> Base de données</div>
    <div class="settings-card__desc">Actions de maintenance de la base de données</div>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
      <form method="POST" action="" style="margin:0;">
        <input type="hidden" name="action" value="maintenance_action">
        <input type="hidden" name="type" value="Sauvegarder BDD">
        <button type="submit" class="btn btn-secondary"><i data-lucide="download" style="width:16px;height:16px;"></i> Sauvegarder la BDD</button>
      </form>
      <form method="POST" action="" style="margin:0;">
        <input type="hidden" name="action" value="maintenance_action">
        <input type="hidden" name="type" value="Vider le cache">
        <button type="submit" class="btn btn-secondary"><i data-lucide="refresh-ccw" style="width:16px;height:16px;"></i> Vider le cache</button>
      </form>
      <form method="POST" action="" style="margin:0;">
        <input type="hidden" name="action" value="maintenance_action">
        <input type="hidden" name="type" value="Réinitialiser">
        <button type="submit" class="btn btn-ghost" style="color:var(--accent-tertiary);border-color:var(--accent-tertiary);" onclick="return confirm('Attention ! Êtes-vous sûr de vouloir réinitialiser ?');"><i data-lucide="alert-triangle" style="width:16px;height:16px;"></i> Réinitialiser</button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var navItems = document.querySelectorAll('.settings-nav__item');
  
  const lastActiveTab = localStorage.getItem('activeSettingsTab') || 'appearance';
  
  function activateTab(tabId) {
      navItems.forEach(function(n) { n.classList.remove('active'); });
      document.querySelectorAll('.settings-section').forEach(function(s) { s.classList.remove('active'); });
      
      const targetBtn = document.querySelector('.settings-nav__item[data-tab="' + tabId + '"]');
      if (targetBtn) targetBtn.classList.add('active');
      
      const targetSection = document.getElementById('tab-' + tabId);
      if (targetSection) targetSection.classList.add('active');
      
      localStorage.setItem('activeSettingsTab', tabId);
  }

  activateTab(lastActiveTab);

  navItems.forEach(function(item) {
    item.addEventListener('click', function() {
      activateTab(item.getAttribute('data-tab'));
    });
  });

  // ═══ APPEARANCE TAB INTERACTIVITY ═══

  // --- Color picker <-> hex text sync ---
  function syncColorInputs(colorId, hexId, nameAttr) {
    var colorInp = document.getElementById(colorId);
    var hexInp = document.getElementById(hexId);
    if (!colorInp || !hexInp) return;
    
    colorInp.addEventListener('input', function() {
      hexInp.value = colorInp.value.toUpperCase();
      colorInp.setAttribute('name', nameAttr); // ensure name stays
      updatePreview();
    });
    hexInp.addEventListener('input', function() {
      var val = hexInp.value.trim();
      if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        colorInp.value = val;
        updatePreview();
      }
    });
    hexInp.addEventListener('blur', function() {
      var val = hexInp.value.trim();
      if (!/^#[0-9A-Fa-f]{6}$/.test(val)) {
        hexInp.value = colorInp.value.toUpperCase();
      }
    });
  }
  syncColorInputs('inp-primary-color', 'inp-primary-hex', 'primary_color');
  syncColorInputs('inp-accent-color', 'inp-accent-hex', 'accent_color');

  // --- Color preset buttons ---
  document.querySelectorAll('.color-preset').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var color = btn.getAttribute('data-color');
      var target = btn.getAttribute('data-target');
      var colorInp = document.getElementById('inp-' + target + '-color');
      var hexInp = document.getElementById('inp-' + target + '-hex');
      if (colorInp) colorInp.value = color;
      if (hexInp) hexInp.value = color;
      // Update border on preset buttons
      btn.closest('div').querySelectorAll('.color-preset').forEach(function(b) {
        b.style.borderColor = 'transparent';
      });
      btn.style.borderColor = 'var(--text-primary)';
      updatePreview();
    });
  });

  // --- Radius options ---
  document.querySelectorAll('.radius-option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.radius-option').forEach(function(b) {
        b.style.borderColor = 'var(--border-color)';
        b.classList.remove('active');
      });
      btn.style.borderColor = 'var(--accent-primary)';
      btn.classList.add('active');
      document.getElementById('inp-border-radius').value = btn.getAttribute('data-value');
    });
  });

  // --- Theme options ---
  document.querySelectorAll('.theme-option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.theme-option').forEach(function(b) {
        b.style.borderColor = 'var(--border-color)';
        b.classList.remove('active');
      });
      btn.style.borderColor = 'var(--accent-primary)';
      btn.classList.add('active');
      document.getElementById('inp-default-theme').value = btn.getAttribute('data-value');
    });
  });

  // --- Font preview ---
  var fontSelect = document.getElementById('inp-font-family');
  var fontPreview = document.getElementById('font-preview');
  if (fontSelect && fontPreview) {
    fontSelect.addEventListener('change', function() {
      fontPreview.style.fontFamily = "'" + fontSelect.value + "', sans-serif";
    });
  }

  // --- Live preview update ---
  function updatePreview() {
    var primary = document.getElementById('inp-primary-color');
    var accent = document.getElementById('inp-accent-color');
    var btnPrimary = document.getElementById('preview-btn-primary');
    var btnAccent = document.getElementById('preview-btn-accent');
    var link = document.getElementById('preview-link');
    var badge = document.getElementById('preview-badge');
    
    if (primary && btnPrimary) {
      btnPrimary.style.background = primary.value;
      btnPrimary.style.borderColor = primary.value;
    }
    if (accent && btnAccent) {
      btnAccent.style.borderColor = accent.value;
      btnAccent.style.color = accent.value;
    }
    if (primary && link) {
      link.style.color = primary.value;
    }
    if (primary && badge) {
      badge.style.background = primary.value + '1a';
      badge.style.color = primary.value;
    }
  }
  // Initial preview
  updatePreview();
});
</script>

<!-- Face ID Scripts -->
<script src="/aptus_first_official_version/view/assets/js/face-api.min.js"></script>
<script src="/aptus_first_official_version/view/assets/js/face-recognition.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
  const statusLabel = document.getElementById('faceid-status-label');
  const statusHint = document.getElementById('faceid-status-hint');
  const actionsDiv = document.getElementById('faceid-actions');
  const modal = document.getElementById('faceid-modal');
  const modalClose = document.getElementById('faceid-modal-close');
  const instruction = document.getElementById('faceid-instruction');
  const progressSteps = document.querySelectorAll('.faceid-step');
  const videoEl = document.getElementById('faceid-video');
  const overlayCanvas = document.getElementById('faceid-overlay');
  const resultDiv = document.getElementById('faceid-result');
  const startBtn = document.getElementById('faceid-start-btn');

  let cancelled = false;
  let enrolling = false;

  async function refreshStatus() {
    if (!statusLabel) return;
    try {
      const res = await FaceAuth.getFaceStatus();
      if (res.enrolled) {
        statusLabel.textContent = 'Face ID activé';
        statusLabel.style.color = 'var(--accent-primary)';
        statusHint.textContent = 'Votre visage est enregistré. Vous pouvez l\'utiliser pour vous connecter.';
        actionsDiv.innerHTML = `
          <button type="button" class="btn btn-sm btn-secondary" id="faceid-reenroll-btn" style="padding: 6px 12px; font-size: 13px; border-radius: 6px;"><i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Réenregistrer</button>
          <button type="button" class="btn btn-sm btn-ghost" id="faceid-remove-btn" style="color:var(--accent-tertiary); padding: 6px 12px; font-size: 13px; border-radius: 6px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i> Supprimer</button>
        `;
      } else {
        statusLabel.textContent = 'Face ID non configuré';
        statusLabel.style.color = 'var(--text-secondary)';
        statusHint.textContent = 'Enregistrez votre visage pour une connexion rapide et sécurisée.';
        actionsDiv.innerHTML = `
          <button type="button" class="btn btn-sm btn-primary" id="faceid-enroll-btn" style="padding: 6px 12px; font-size: 13px; border-radius: 6px;"><i data-lucide="scan-face" style="width:14px;height:14px;"></i> Configurer</button>
        `;
      }
      if (typeof lucide !== 'undefined') lucide.createIcons();
      bindActions();
    } catch (e) {
      statusLabel.textContent = 'Erreur de vérification';
      statusHint.textContent = 'Impossible de vérifier le statut Face ID.';
    }
  }

  function bindActions() {
    const enrollBtn = document.getElementById('faceid-enroll-btn');
    const reenrollBtn = document.getElementById('faceid-reenroll-btn');
    const removeBtn = document.getElementById('faceid-remove-btn');

    if (enrollBtn) enrollBtn.addEventListener('click', openEnrollModal);
    if (reenrollBtn) reenrollBtn.addEventListener('click', openEnrollModal);
    if (removeBtn) removeBtn.addEventListener('click', async function() {
      if (confirm('Supprimer votre Face ID ?')) {
        const res = await FaceAuth.removeFace();
        if (res.success) refreshStatus();
      }
    });
  }

  async function openEnrollModal() {
    cancelled = false;
    enrolling = false;
    modal.style.display = 'flex';
    resultDiv.style.display = 'none';
    instruction.textContent = 'Chargement des modèles d\'IA...';
    startBtn.style.display = 'none';
    progressSteps.forEach(s => s.style.background = 'var(--border-color)');

    try {
      await FaceAuth.loadModels();
      await FaceAuth.startCamera(videoEl);
      instruction.textContent = 'Caméra prête. Placez votre visage dans le cercle.';
      startBtn.style.display = '';
      startBtn.disabled = false;
      startBtn.innerHTML = '<i data-lucide="camera" style="width:18px;height:18px;"></i> Démarrer l\'enregistrement';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
      instruction.textContent = 'Erreur : impossible d\'accéder à la caméra. Vérifiez les permissions.';
    }
  }

  if (modalClose) {
    modalClose.addEventListener('click', function() {
      cancelled = true;
      FaceAuth.stopCamera();
      modal.style.display = 'none';
    });
  }

  if (startBtn) {
    startBtn.addEventListener('click', async function() {
      if (enrolling) return;
      enrolling = true;
      startBtn.disabled = true;
      startBtn.innerHTML = '<i data-lucide="loader" style="width:18px;height:18px;animation:spin 1s linear infinite;"></i> En cours...';
      resultDiv.style.display = 'none';

      const descriptor = await FaceAuth.runLivenessCheck(
        videoEl,
        function(text, step, total) {
          instruction.textContent = text;
          progressSteps.forEach(function(s, i) {
            s.style.background = i < step ? 'var(--accent-primary)' : 'var(--border-color)';
          });
        },
        function() { return cancelled; },
        overlayCanvas
      );

      if (cancelled) return;

      if (descriptor) {
        instruction.textContent = 'Enregistrement en cours...';
        const res = await FaceAuth.enrollFace(descriptor);

        resultDiv.style.display = 'block';
        if (res.success) {
          resultDiv.style.background = 'rgba(16,185,129,0.1)';
          resultDiv.style.color = '#10b981';
          resultDiv.style.border = '1px solid rgba(16,185,129,0.3)';
          resultDiv.textContent = '✅ ' + res.message;
          setTimeout(function() {
            FaceAuth.stopCamera();
            modal.style.display = 'none';
            refreshStatus();
          }, 1500);
        } else {
          resultDiv.style.background = 'rgba(239,68,68,0.1)';
          resultDiv.style.color = '#ef4444';
          resultDiv.style.border = '1px solid rgba(239,68,68,0.3)';
          resultDiv.textContent = '❌ ' + res.message;
          enrolling = false;
          startBtn.disabled = false;
          startBtn.innerHTML = '<i data-lucide="refresh-cw" style="width:18px;height:18px;"></i> Réessayer';
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      } else {
        resultDiv.style.display = 'block';
        resultDiv.style.background = 'rgba(245,158,11,0.1)';
        resultDiv.style.color = '#f59e0b';
        resultDiv.style.border = '1px solid rgba(245,158,11,0.3)';
        resultDiv.textContent = '⚠️ Vérification échouée. Assurez-vous d\'être bien éclairé et suivez les instructions.';
        enrolling = false;
        startBtn.disabled = false;
        startBtn.innerHTML = '<i data-lucide="refresh-cw" style="width:18px;height:18px;"></i> Réessayer';
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }
    });
  }

  // Init
  refreshStatus();
});
</script>
