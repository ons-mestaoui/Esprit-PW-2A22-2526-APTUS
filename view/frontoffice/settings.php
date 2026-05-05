<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/ProfilC.php';

$utilisateurC = new UtilisateurC();
$id = $_SESSION['id_utilisateur'];
$prefs = $utilisateurC->getPreferences($id);
$activeTab = 'general';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'delete_account') {
        $utilisateurC->deleteUtilisateur($id);
        session_destroy();
        header("Location: login.php");
        exit();

    } elseif ($_POST['action'] === 'change_password') {
        $activeTab = 'security';
        $user = $utilisateurC->getUtilisateurById($id);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (password_verify($current_password, $user['motDePasse'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $utilisateurC->resetPassword($id, $new_password);
                    $successMsg = "Mot de passe mis à jour avec succès.";
                } else {
                    $errorMsg = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                }
            } else {
                $errorMsg = "Les nouveaux mots de passe ne correspondent pas.";
            }
        } else {
            $errorMsg = "Le mot de passe actuel est incorrect.";
        }

    } elseif ($_POST['action'] === 'update_general') {
        $activeTab = 'general';
        $lang = $_POST['language'] ?? 'fr';
        $tz = $_POST['timezone'] ?? 'Africa/Tunis';
        $utilisateurC->updatePreferences($id, ['language' => $lang, 'timezone' => $tz]);
        $prefs = $utilisateurC->getPreferences($id);
        $successMsg = "Paramètres généraux enregistrés.";

    } elseif ($_POST['action'] === 'update_appearance') {
        $activeTab = 'appearance';
        $theme = $_POST['theme'] ?? 'dark';
        $color = $_POST['accent_color'] ?? '#6B34A3';
        $fontSize = intval($_POST['font_size'] ?? 14);
        $fontFamily = $_POST['font_family'] ?? 'Inter';
        $borderRadius = $_POST['border_radius'] ?? 'medium';
        $utilisateurC->updatePreferences($id, [
            'theme' => $theme, 
            'accent_color' => $color, 
            'font_size' => $fontSize,
            'font_family' => $fontFamily,
            'border_radius' => $borderRadius
        ]);
        $prefs = $utilisateurC->getPreferences($id);
        $successMsg = "Apparence mise à jour.";

    } elseif ($_POST['action'] === 'update_privacy') {
        $activeTab = 'privacy';
        $utilisateurC->updatePreferences($id, [
            'privacy_public' => isset($_POST['privacy_public']),
            'privacy_email'  => isset($_POST['privacy_email']),
            'privacy_phone'  => isset($_POST['privacy_phone']),
            'privacy_search' => isset($_POST['privacy_search']),
        ]);
        $prefs = $utilisateurC->getPreferences($id);
        $successMsg = "Paramètres de confidentialité enregistrés.";

    } elseif ($_POST['action'] === 'export_data') {
        require_once __DIR__ . '/../../libs/FPDF/fpdf.php';

        $user = $utilisateurC->getUtilisateurById($id);
        $profilC = new ProfilC();
        $profil = $profilC->getProfilByIdUtilisateur($id);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // ── Header ──
        $pdf->SetFillColor(99, 102, 241);
        $pdf->Rect(0, 0, 210, 40, 'F');
        $pdf->SetFont('Helvetica', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 20, 'Aptus', 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->Cell(0, 8, 'Export de vos donnees personnelles', 0, 1, 'C');
        $pdf->Ln(15);

        // ── Section: Compte ──
        $pdf->SetTextColor(99, 102, 241);
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Informations du compte', 0, 1);
        $pdf->SetDrawColor(99, 102, 241);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetFont('Helvetica', '', 11);

        $fields = [
            'Nom' => $user['nom'] ?? '-',
            'Prenom' => $user['prenom'] ?? '-',
            'Email' => $user['email'] ?? '-',
            'Role' => $user['role'] ?? '-',
            'Telephone' => $user['telephone'] ?? '-',
        ];
        foreach ($fields as $label => $val) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(50, 8, $label . ' :', 0, 0);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Cell(0, 8, $val, 0, 1);
        }

        // ── Section: Profil ──
        if ($profil && is_array($profil)) {
            $pdf->Ln(6);
            $pdf->SetTextColor(99, 102, 241);
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Profil', 0, 1);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(4);
            $pdf->SetTextColor(60, 60, 60);

            $profilFields = [
                'Bio' => $profil['bio'] ?? '-',
                'Adresse' => $profil['adresse'] ?? '-',
                'Ville' => $profil['ville'] ?? '-',
                'Pays' => $profil['pays'] ?? '-',
                'Date de naissance' => $profil['dateNaissance'] ?? '-',
                'LinkedIn' => $profil['linkedin'] ?? '-',
                'Site Web' => $profil['siteWeb'] ?? '-',
            ];
            foreach ($profilFields as $label => $val) {
                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->Cell(50, 8, $label . ' :', 0, 0);
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->Cell(0, 8, $val, 0, 1);
            }
        }

        // ── Section: Preferences ──
        $pdf->Ln(6);
        $pdf->SetTextColor(99, 102, 241);
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Preferences', 0, 1);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);
        $pdf->SetTextColor(60, 60, 60);

        $prefLabels = [
            'language' => 'Langue',
            'timezone' => 'Fuseau horaire',
            'theme' => 'Theme',
            'accent_color' => 'Couleur accent',
            'font_size' => 'Taille du texte',
            'privacy_public' => 'Profil public',
            'privacy_email' => 'Email visible',
            'privacy_phone' => 'Telephone visible',
            'privacy_search' => 'Recherche recruteurs',
        ];
        foreach ($prefLabels as $key => $label) {
            $val = $prefs[$key] ?? '-';
            if (is_bool($val)) $val = $val ? 'Oui' : 'Non';
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(50, 8, $label . ' :', 0, 0);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Cell(0, 8, (string)$val, 0, 1);
        }

        // ── Footer ──
        $pdf->Ln(10);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->Cell(0, 8, 'Exporte le ' . date('d/m/Y a H:i') . ' depuis Aptus', 0, 1, 'C');

        $pdf->Output('D', 'aptus_export_' . date('Ymd') . '.pdf');
        exit();
    }
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

<?php if(isset($successMsg)): ?>
<div class="alert alert-success" style="margin-bottom:var(--space-4); padding:var(--space-3); background:#d1fae5; color:#065f46; border-radius:var(--radius-md); border:1px solid #10b981;">
  <i data-lucide="check-circle" style="width:18px;height:18px;vertical-align:-4px;"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>
<?php if(isset($errorMsg)): ?>
<div class="alert alert-danger" style="margin-bottom:var(--space-4); padding:var(--space-3); background:#fee2e2; color:#b91c1c; border-radius:var(--radius-md); border:1px solid #ef4444;">
  <i data-lucide="alert-circle" style="width:18px;height:18px;vertical-align:-4px;"></i> <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

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
    <form method="POST" action="">
      <input type="hidden" name="action" value="update_general">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Langue</label>
          <select class="select" name="language">
            <option value="fr" <?= ($prefs['language']??'fr')==='fr'?'selected':'' ?>>Français</option>
            <option value="en" <?= ($prefs['language']??'')==='en'?'selected':'' ?>>English</option>
            <option value="ar" <?= ($prefs['language']??'')==='ar'?'selected':'' ?>>العربية</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Fuseau horaire</label>
          <select class="select" name="timezone">
            <option value="Africa/Tunis" <?= ($prefs['timezone']??'')==='Africa/Tunis'?'selected':'' ?>>Africa/Tunis (GMT+1)</option>
            <option value="Europe/Paris" <?= ($prefs['timezone']??'')==='Europe/Paris'?'selected':'' ?>>Europe/Paris (GMT+1)</option>
            <option value="Europe/London" <?= ($prefs['timezone']??'')==='Europe/London'?'selected':'' ?>>Europe/London (GMT+0)</option>
          </select>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <button type="submit" class="btn btn-primary" style="width:fit-content;"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer</button>
        </div>
      </div>
    </form>
  </div>

  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="download" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Données & Export</div>
    <div class="settings-card__desc">Téléchargez vos données ou supprimez votre compte</div>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
      <form method="POST" action="" style="margin:0;"><input type="hidden" name="action" value="export_data"><button type="submit" class="btn btn-secondary"><i data-lucide="download" style="width:16px;height:16px;"></i> Exporter mes données</button></form>
      <button type="button" class="btn btn-ghost" onclick="document.getElementById('deleteModal').style.display='flex';" style="color:var(--accent-tertiary);border-color:var(--accent-tertiary);"><i data-lucide="trash-2" style="width:16px;height:16px;"></i> Supprimer mon compte</button>
      
      <!-- Delete Confirmation Modal -->
      <div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999; backdrop-filter: blur(4px);">
        <div style="background:var(--bg-card, #ffffff); border-radius:16px; padding:32px 24px; text-align:center; max-width:400px; width:90%; position:relative; box-shadow:0 10px 25px rgba(0,0,0,0.1); display:flex; flex-direction:column; align-items:center;">
          <button type="button" onclick="document.getElementById('deleteModal').style.display='none';" style="position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; color:var(--text-secondary); padding:4px;">
            <i data-lucide="x" style="width:20px;height:20px;"></i>
          </button>
          
          <div style="width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
              <i data-lucide="alert-triangle" style="width:32px;height:32px;color:#ef4444;"></i>
          </div>
          
          <h3 style="font-size:24px; font-weight:700; color:var(--text-primary, #1e293b); margin-bottom:12px; font-family:'Inter', sans-serif;">Confirmation de suppression</h3>
          
          <p style="font-size:16px; color:var(--text-secondary, #64748b); margin-bottom:32px; line-height:1.5;">Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.</p>
          
          <div style="display:flex; gap:16px; width:100%;">
            <button type="button" onclick="document.getElementById('deleteModal').style.display='none';" style="flex:1; padding:12px; border-radius:8px; border:1px solid var(--border-color, #e2e8f0); background:transparent; font-weight:600; color:var(--text-primary, #1e293b); cursor:pointer; font-size:15px; transition:all 0.2s;">Annuler</button>
            <form method="POST" action="" style="flex:1; margin:0; display:flex;">
              <input type="hidden" name="action" value="delete_account">
              <button type="submit" style="flex:1; padding:12px; border-radius:8px; border:none; background:#ef4444; font-weight:600; color:#ffffff; cursor:pointer; font-size:15px; transition:all 0.2s;">Oui, Supprimer</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ APPEARANCE ═══ -->
<div class="settings-section" id="tab-appearance">
  <form method="POST" action="" id="appearance-form">
    <input type="hidden" name="action" value="update_appearance">
    <input type="hidden" name="theme" id="inp-theme" value="<?= htmlspecialchars($prefs['theme'] ?? 'dark') ?>">
    <input type="hidden" name="border_radius" id="inp-border-radius" value="<?= htmlspecialchars($prefs['border_radius'] ?? 'medium') ?>">

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-5);">
      <!-- Column 1: Theme & Color -->
      <div style="display:flex; flex-direction:column; gap:var(--space-5);">
        <div class="settings-card" style="height:100%;">
          <div class="settings-card__title"><i data-lucide="sun" style="width:20px;height:20px;color:var(--stat-orange);"></i> Thème</div>
          <div class="settings-card__desc">Choisissez le mode d'affichage de l'interface</div>
          <div style="display:flex;gap:var(--space-4);" id="theme-options">
            <?php
            $themeOptions = [
              'light' => ['label' => 'Clair', 'icon' => 'sun'],
              'dark' => ['label' => 'Sombre', 'icon' => 'moon'],
            ];
            foreach ($themeOptions as $val => $opt): ?>
            <button type="button" class="theme-option <?= ($prefs['theme'] ?? 'dark')===$val ? 'active' : '' ?>" data-value="<?= $val ?>" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;padding:var(--space-5);border:2px solid <?= ($prefs['theme'] ?? 'dark')===$val ? 'var(--accent-primary)' : 'var(--border-color)' ?>;border-radius:var(--radius-lg);background:var(--bg-body);cursor:pointer;transition:all 0.2s;">
              <i data-lucide="<?= $opt['icon'] ?>" style="width:28px;height:28px;"></i>
              <span class="text-sm fw-semibold"><?= $opt['label'] ?></span>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="settings-card">
          <div class="settings-card__title"><i data-lucide="palette" style="width:20px;height:20px;color:var(--accent-primary);"></i> Couleur d'accent</div>
          <div class="settings-card__desc">Personnalisez la couleur principale de l'interface</div>
          <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;margin-bottom:var(--space-4);">
            <?php 
            $colors = ['#6B34A3','#00A3DA','#6366F1','#8B5CF6','#EC4899','#10B981','#F59E0B','#EF4444'];
            foreach($colors as $c): ?>
            <button type="button" class="color-preset" data-color="<?= $c ?>" style="width:32px;height:32px;border-radius:50%;border:2px solid <?= ($prefs['accent_color'] ?? '#6B34A3')===$c ? 'var(--text-primary)' : 'transparent' ?>;background:<?= $c ?>;cursor:pointer;transition:all 0.2s;" title="<?= $c ?>"></button>
            <?php endforeach; ?>
          </div>
          <div style="display:flex; align-items:center; gap:var(--space-3); padding:var(--space-3); background:var(--bg-secondary); border-radius:var(--radius-md);">
            <label class="text-xs fw-medium" style="color:var(--text-secondary);">Personnalisée :</label>
            <input type="color" name="accent_color" id="inp-accent-color" value="<?= htmlspecialchars($prefs['accent_color'] ?? '#6B34A3') ?>" style="width:30px;height:30px;border:none;background:none;cursor:pointer;">
            <input type="text" id="inp-accent-hex" value="<?= htmlspecialchars($prefs['accent_color'] ?? '#6B34A3') ?>" style="flex:1; font-family:monospace; font-size:12px; border:none; background:none; color:var(--text-primary);" maxlength="7">
          </div>
        </div>
      </div>

      <!-- Column 2: Typography & Shape -->
      <div style="display:flex; flex-direction:column; gap:var(--space-5);">
        <div class="settings-card">
          <div class="settings-card__title"><i data-lucide="type" style="width:20px;height:20px;color:var(--accent-secondary);"></i> Typographie</div>
          <div class="settings-card__desc">Choisissez votre police et taille de texte</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
            <div class="form-group">
              <label class="form-label" style="font-size:12px;">Police de caractères</label>
              <select class="select" name="font_family" id="inp-font-family" style="width:100%;">
                <?php foreach(['Inter','Roboto','Outfit','Poppins','DM Sans','Plus Jakarta Sans'] as $f): ?>
                <option value="<?= $f ?>" <?= ($prefs['font_family'] ?? 'Inter')===$f ? 'selected' : '' ?> style="font-family:'<?= $f ?>',sans-serif;"><?= $f ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:12px;">Aperçu</label>
              <div id="font-preview" style="padding:var(--space-3);background:var(--bg-input);border-radius:var(--radius-md);border:1px solid var(--border-color);font-family:'<?= $prefs['font_family'] ?? 'Inter' ?>',sans-serif;">
                <div style="font-weight:700;font-size:var(--fs-md);margin-bottom:4px;">Aptus Platform</div>
                <div style="font-size:var(--fs-sm);color:var(--text-secondary);">La plateforme intelligente 1234567890</div>
              </div>
            </div>
          </div>
          <div class="form-group" style="margin-top:var(--space-4);">
            <label class="form-label" id="fontsize-label" style="font-size:12px;">Taille du texte (<?= intval($prefs['font_size'] ?? 14) ?>px)</label>
            <div style="display:flex;align-items:center;gap:var(--space-4);">
              <span class="text-xs">A</span>
              <input type="range" name="font_size" id="inp-font-size" style="flex:1;accent-color:var(--accent-primary);" min="12" max="20" value="<?= intval($prefs['font_size'] ?? 14) ?>">
              <span style="font-size:1.25rem;font-weight:600;">A</span>
            </div>
          </div>
        </div>

        <div class="settings-card">
          <div class="settings-card__title"><i data-lucide="square" style="width:20px;height:20px;color:var(--stat-orange);"></i> Arrondi des coins</div>
          <div class="settings-card__desc">Modifiez le style des boutons et des cartes</div>
          <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;" id="radius-options">
            <?php
            $radiusOptions = [
              'none' => ['label' => 'Droit', 'preview' => '0px'],
              'small' => ['label' => 'Petit', 'preview' => '4px'],
              'medium' => ['label' => 'Moyen', 'preview' => '12px'],
              'large' => ['label' => 'Grand', 'preview' => '20px'],
              'full' => ['label' => 'Rond', 'preview' => '28px'],
            ];
            foreach ($radiusOptions as $val => $opt): ?>
            <button type="button" class="radius-option <?= ($prefs['border_radius'] ?? 'medium')===$val ? 'active' : '' ?>" data-value="<?= $val ?>" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:var(--space-2) var(--space-3);border:2px solid <?= ($prefs['border_radius'] ?? 'medium')===$val ? 'var(--accent-primary)' : 'var(--border-color)' ?>;border-radius:var(--radius-md);background:var(--bg-body);cursor:pointer;transition:all 0.2s;min-width:60px;">
              <div style="width:30px;height:18px;border:2px solid var(--text-secondary);border-radius:<?= $opt['preview'] ?>;"></div>
              <span style="font-size:10px;font-weight:500;color:var(--text-secondary);"><?= $opt['label'] ?></span>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Preview (same as admin) -->
    <div class="settings-card" style="margin-top:var(--space-5); padding:var(--space-4); border:1px dashed var(--border-color); background:var(--bg-secondary);">
      <div style="font-size:var(--fs-xs);color:var(--text-tertiary);margin-bottom:var(--space-2);text-transform:uppercase;letter-spacing:0.5px;">Aperçu en direct</div>
      <div style="display:flex;gap:var(--space-3);align-items:center;flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" id="preview-btn-primary" style="pointer-events:none;">Bouton Principal</button>
        <button type="button" class="btn btn-secondary" id="preview-btn-secondary" style="pointer-events:none;">Bouton Secondaire</button>
        <span id="preview-link" style="color:var(--accent-primary);font-weight:500;font-size:var(--fs-sm);cursor:default;">Lien exemple</span>
        <div id="preview-badge" style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:var(--radius-full);font-size:var(--fs-xs);font-weight:600;">Badge</div>
      </div>
    </div>

    <div style="margin-top:var(--space-6); display:flex; justify-content:flex-end;">
      <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer l'apparence</button>
    </div>
  </form>
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
    <p style="font-size:var(--fs-xs);color:var(--text-tertiary);margin-top:var(--space-4);"><i data-lucide="info" style="width:14px;height:14px;vertical-align:-2px;"></i> Les notifications ne sont pas encore disponibles.</p>
  </div>
</div>

<!-- ═══ PRIVACY ═══ -->
<div class="settings-section" id="tab-privacy">
  <form method="POST" action="">
    <input type="hidden" name="action" value="update_privacy">
    <div class="settings-card">
      <div class="settings-card__title"><i data-lucide="eye" style="width:20px;height:20px;color:var(--accent-primary);"></i> Visibilité du profil</div>
      <div class="settings-card__desc">Contrôlez qui peut voir vos informations</div>
      <div class="setting-row">
        <div class="setting-row__info"><div class="setting-row__label">Profil public</div><div class="setting-row__hint">Votre profil est visible par les recruteurs</div></div>
        <label class="toggle-switch <?= !empty($prefs['privacy_public']) ? 'active' : '' ?>">
          <input type="checkbox" name="privacy_public" value="1" <?= !empty($prefs['privacy_public']) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)">
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-row__info"><div class="setting-row__label">Afficher l'email</div><div class="setting-row__hint">Montrer votre adresse email sur votre profil</div></div>
        <label class="toggle-switch <?= !empty($prefs['privacy_email']) ? 'active' : '' ?>">
          <input type="checkbox" name="privacy_email" value="1" <?= !empty($prefs['privacy_email']) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)">
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-row__info"><div class="setting-row__label">Afficher le téléphone</div><div class="setting-row__hint">Montrer votre numéro de téléphone sur votre profil</div></div>
        <label class="toggle-switch <?= !empty($prefs['privacy_phone']) ? 'active' : '' ?>">
          <input type="checkbox" name="privacy_phone" value="1" <?= !empty($prefs['privacy_phone']) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)">
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-row__info"><div class="setting-row__label">Recherche par les recruteurs</div><div class="setting-row__hint">Permettre aux entreprises de vous trouver par recherche</div></div>
        <label class="toggle-switch <?= !empty($prefs['privacy_search']) ? 'active' : '' ?>">
          <input type="checkbox" name="privacy_search" value="1" <?= !empty($prefs['privacy_search']) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)">
        </label>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="width:fit-content;margin-top:var(--space-3);"><i data-lucide="save" style="width:16px;height:16px;"></i> Enregistrer la confidentialité</button>
  </form>
</div>

<!-- ═══ SECURITY ═══ -->
<div class="settings-section" id="tab-security">
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="key" style="width:20px;height:20px;color:var(--stat-orange);"></i> Mot de passe</div>
    <div class="settings-card__desc">Modifiez votre mot de passe de connexion</div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="change_password">
      <div style="display:grid;grid-template-columns:1fr;gap:var(--space-4);max-width:400px;">
        <div class="form-group">
          <label class="form-label">Mot de passe actuel</label>
          <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" name="current_password" class="input" placeholder="••••••••" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">Nouveau mot de passe</label>
          <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" name="new_password" class="input" placeholder="Min. 8 caractères" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirmer le nouveau mot de passe</label>
          <div class="input-icon-wrapper"><i data-lucide="lock" style="width:18px;height:18px;"></i><input type="password" name="confirm_password" class="input" placeholder="Confirmez" required></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:fit-content;"><i data-lucide="check" style="width:16px;height:16px;"></i> Mettre à jour</button>
      </div>
    </form>
  </div>

  <!-- ═══ FACE ID ENROLLMENT ═══ -->
  <div class="settings-card" id="faceid-card">
    <div class="settings-card__title"><i data-lucide="scan-face" style="width:20px;height:20px;color:var(--accent-primary);"></i> Face ID — Reconnaissance Faciale</div>
    <div class="settings-card__desc">Connectez-vous avec votre visage. La vérification de vivacité garantit qu'une vraie personne est présente.</div>
    
    <div id="faceid-status" class="setting-row">
      <div class="setting-row__info">
        <div class="setting-row__label" id="faceid-status-label">Chargement...</div>
        <div class="setting-row__hint" id="faceid-status-hint">Vérification du statut Face ID</div>
      </div>
      <div id="faceid-actions" style="display:flex;gap:var(--space-2);"></div>
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
        <!-- Scanning animation ring -->
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

  <!-- ═══ 2FA SECTION ═══ -->
  <div class="settings-card">
    <div class="settings-card__title"><i data-lucide="smartphone" style="width:20px;height:20px;color:var(--accent-primary);"></i> Authentification à deux facteurs (2FA)</div>
    <div class="settings-card__desc">Ajoutez une couche de sécurité supplémentaire en utilisant une application d'authentification (Google Authenticator, Authy, etc.).</div>
    
    <div class="toggle-row" id="2fa-status-row" style="border-bottom:none; padding-bottom:0; display: flex; align-items: center; justify-content: space-between;">
      <div class="toggle-row__info">
        <div class="toggle-row__label" id="2fa-status-label" style="font-weight: 600; font-size: var(--fs-md);">Chargement...</div>
        <div class="toggle-row__hint" id="2fa-status-hint">Vérification de l'état de la 2FA</div>
      </div>
      <div id="2fa-actions">
        <!-- Buttons will be injected here by JS -->
      </div>
    </div>
  </div>

  <style>
    /* Premium 2FA Modal Styles */
    .modal-2fa {
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      background-color: rgba(255, 255, 255, 0.7);
    }
    [data-theme="dark"] .modal-2fa {
      background-color: rgba(15, 23, 42, 0.8);
    }
    .modal-2fa-content {
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      overflow: hidden;
      background: var(--bg-card);
      position: relative;
    }
    .modal-2fa-header {
      background: var(--gradient-primary);
      padding: var(--space-8) var(--space-6);
      color: white;
      text-align: center;
      position: relative;
    }
    .modal-2fa-header h3 { color: white; margin: 0; font-weight: 800; font-size: 24px; }
    .modal-2fa-header p { color: rgba(255,255,255,0.8); margin-top: 8px; font-size: 14px; }
    
    .modal-2fa-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255,255,255,0.1);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .modal-2fa-close:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }

    .step-indicator {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-bottom: var(--space-6);
    }
    .step-dot {
      width: 40px;
      height: 6px;
      border-radius: 3px;
      background: var(--border-color);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .step-dot.active {
      background: var(--accent-primary);
      width: 60px;
      box-shadow: 0 0 15px var(--accent-primary-light);
    }

    .qr-container {
      background: white;
      padding: 16px;
      border-radius: var(--radius-lg);
      display: inline-block;
      margin-bottom: var(--space-6);
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-md);
      transition: all 0.3s ease;
    }
    .qr-container:hover {
      transform: scale(1.02);
      box-shadow: var(--shadow-lg);
    }

    .otp-display {
      background: var(--bg-secondary);
      padding: 12px 20px;
      border-radius: var(--radius-md);
      font-family: 'JetBrains Mono', monospace;
      letter-spacing: 2px;
      font-weight: 700;
      color: var(--accent-primary);
      border: 1px dashed var(--border-color);
      cursor: pointer;
      transition: all 0.2s;
    }
    .otp-display:hover { background: var(--bg-input); }

    .verify-input-wrapper {
      position: relative;
      margin: var(--space-8) 0;
    }
    .verify-input {
      width: 100%;
      height: 70px;
      text-align: center;
      font-size: 32px !important;
      letter-spacing: 12px;
      font-weight: 800;
      border-radius: var(--radius-lg);
      border: 2px solid var(--border-color);
      background: var(--bg-input);
      transition: all 0.3s;
    }
    .verify-input:focus {
      border-color: var(--accent-primary);
      box-shadow: 0 0 0 6px var(--accent-primary-light);
      background: var(--bg-card);
    }

    @keyframes success-pop {
      0% { transform: scale(0.8); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
    .success-icon-anim {
      width: 80px;
      height: 80px;
      background: #10b9811a;
      color: #10b981;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto var(--space-6);
      animation: success-pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
  </style>

  <!-- ═══ 2FA SETUP MODAL (Improved) ═══ -->
  <div id="2fa-modal" class="modal-overlay modal-2fa" style="z-index: 10000;">
    <div class="modal-content modal-2fa-content" style="max-width: 500px; border-radius: var(--radius-xl);">
      
      <div class="modal-2fa-header">
        <button type="button" class="modal-2fa-close" onclick="document.getElementById('2fa-modal').classList.remove('active')">&times;</button>
        <div style="margin-bottom: var(--space-4);">
          <i data-lucide="shield-check" style="width: 48px; height: 48px;"></i>
        </div>
        <h3 id="2fa-modal-title">Sécurité Renforcée</h3>
        <p id="2fa-modal-desc">Protégez votre compte avec l'authentification 2FA</p>
      </div>

      <div class="modal-body" style="padding: var(--space-8); text-align: center; display: flex; flex-direction: column; align-items: center;">
        
        <!-- Step Indicators -->
        <div class="step-indicator" id="2fa-step-indicator" style="width: 100%; display: flex; justify-content: center;">
          <div class="step-dot active" id="dot-1"></div>
          <div class="step-dot" id="dot-2"></div>
        </div>

        <!-- STEP 1: SCAN QR -->
        <div id="2fa-step-1" style="width: 100%;">
          <div class="qr-container" style="margin: 0 auto var(--space-6) auto;">
            <img id="2fa-qr-code" src="" alt="QR Code" style="display: block; width: 200px; height: 200px; image-rendering: pixelated;">
          </div>
          
          <div style="margin-bottom: var(--space-6);">
            <p style="font-size: 15px; font-weight: 500; margin-bottom: 8px;">Scannez ce code QR</p>
            <p style="font-size: 13px; color: var(--text-secondary);">Utilisez Google Authenticator ou Authy pour scanner ce code.</p>
          </div>

          <div style="margin-bottom: var(--space-8);">
            <p style="font-size: 12px; color: var(--text-tertiary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">Ou entrez le code manuellement</p>
            <div class="otp-display" id="2fa-secret-text" style="display: inline-block; margin: 0 auto;" onclick="navigator.clipboard.writeText(this.innerText); alert('Copié !')"></div>
          </div>

          <button type="button" class="btn btn-primary w-full btn-lg" onclick="TwoFactor.goToStep(2)">
            Continuer <i data-lucide="arrow-right" style="width:18px;height:18px;margin-left:8px;"></i>
          </button>
        </div>

        <!-- STEP 2: VERIFY -->
        <div id="2fa-step-2" style="display: none; width: 100%;">
          <div class="verify-input-wrapper">
            <input type="text" id="2fa-verify-code" class="verify-input" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
          </div>
          
          <div style="margin-bottom: var(--space-8);">
            <p style="font-size: 15px; font-weight: 500; margin-bottom: 8px;">Vérification du code</p>
            <p style="font-size: 13px; color: var(--text-secondary);">Saisissez le code à 6 chiffres affiché dans votre application.</p>
          </div>

          <div id="2fa-verify-error" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: var(--space-6); display: none; border: 1px solid rgba(239, 68, 68, 0.2);"></div>

          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
            <button type="button" class="btn btn-secondary" onclick="TwoFactor.goToStep(1)">
              <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
            </button>
            <button type="button" id="2fa-confirm-btn" class="btn btn-primary btn-lg">
              Activer la 2FA
            </button>
          </div>
        </div>

        <!-- STEP 3: SUCCESS (Animated) -->
        <div id="2fa-step-success" style="display: none; padding: var(--space-4) 0; width: 100%;">
            <div class="success-icon-anim">
                <i data-lucide="check" style="width: 40px; height: 40px;"></i>
            </div>
            <h3 style="font-weight: 800; font-size: 20px; margin-bottom: 8px;">Configuration Réussie !</h3>
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: var(--space-8);">Votre compte est désormais protégé par l'authentification à deux facteurs.</p>
            <button type="button" class="btn btn-primary w-full" onclick="document.getElementById('2fa-modal').classList.remove('active')">Génial !</button>
        </div>
      </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // ═══ TAB NAVIGATION (same as admin: localStorage persistence) ═══
  var navItems = document.querySelectorAll('.settings-nav__item');
  var lastActiveTab = localStorage.getItem('aptus-settings-tab') || '<?= $activeTab ?>';
  if (lastActiveTab === 'general' && '<?= $activeTab ?>' !== 'general') {
    lastActiveTab = '<?= $activeTab ?>';
  }

  function activateTab(tabId) {
    navItems.forEach(function(n) { n.classList.remove('active'); });
    document.querySelectorAll('.settings-section').forEach(function(s) { s.classList.remove('active'); });
    var targetBtn = document.querySelector('.settings-nav__item[data-tab="' + tabId + '"]');
    if (targetBtn) targetBtn.classList.add('active');
    var targetSection = document.getElementById('tab-' + tabId);
    if (targetSection) targetSection.classList.add('active');
    localStorage.setItem('aptus-settings-tab', tabId);
  }

  activateTab(lastActiveTab);

  navItems.forEach(function(item) {
    item.addEventListener('click', function() {
      activateTab(item.getAttribute('data-tab'));
    });
  });

  // ═══ APPEARANCE TAB INTERACTIVITY (same pattern as admin) ═══

  // --- Color picker <-> hex text sync (same as admin syncColorInputs) ---
  var colorInp = document.getElementById('inp-accent-color');
  var hexInp = document.getElementById('inp-accent-hex');
  if (colorInp && hexInp) {
    colorInp.addEventListener('input', function() {
      hexInp.value = colorInp.value.toUpperCase();
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

  // --- Color preset buttons (same as admin) ---
  document.querySelectorAll('.color-preset').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var color = btn.getAttribute('data-color');
      if (colorInp) colorInp.value = color;
      if (hexInp) hexInp.value = color;
      btn.closest('div').querySelectorAll('.color-preset').forEach(function(b) {
        b.style.borderColor = 'transparent';
      });
      btn.style.borderColor = 'var(--text-primary)';
      updatePreview();
    });
  });

  // --- Theme options (same as admin) ---
  document.querySelectorAll('.theme-option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.theme-option').forEach(function(b) {
        b.style.borderColor = 'var(--border-color)';
        b.classList.remove('active');
      });
      btn.style.borderColor = 'var(--accent-primary)';
      btn.classList.add('active');
      var val = btn.getAttribute('data-value');
      document.getElementById('inp-theme').value = val;
      // Apply theme live
      document.documentElement.setAttribute('data-theme', val);
      localStorage.setItem('aptus-theme', val);
    });
  });

  // --- Radius options (same as admin) ---
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

  // --- Font preview (same as admin) ---
  var fontSelect = document.getElementById('inp-font-family');
  var fontPreview = document.getElementById('font-preview');
  if (fontSelect && fontPreview) {
    fontSelect.addEventListener('change', function() {
      fontPreview.style.fontFamily = "'" + fontSelect.value + "', sans-serif";
    });
  }

  // --- Font size range ---
  var fontSizeRange = document.getElementById('inp-font-size');
  var fontSizeLabel = document.getElementById('fontsize-label');
  if (fontSizeRange && fontSizeLabel) {
    fontSizeRange.addEventListener('input', function() {
      fontSizeLabel.textContent = 'Taille du texte (' + fontSizeRange.value + 'px)';
    });
  }

  // --- Live preview update (same as admin) ---
  function updatePreview() {
    var accent = document.getElementById('inp-accent-color');
    var btnPrimary = document.getElementById('preview-btn-primary');
    var btnSecondary = document.getElementById('preview-btn-secondary');
    var link = document.getElementById('preview-link');
    var badge = document.getElementById('preview-badge');

    if (accent && btnPrimary) {
      btnPrimary.style.background = accent.value;
      btnPrimary.style.borderColor = accent.value;
    }
    if (accent && btnSecondary) {
      btnSecondary.style.borderColor = accent.value;
      btnSecondary.style.color = accent.value;
    }
    if (accent && link) {
      link.style.color = accent.value;
    }
    if (accent && badge) {
      badge.style.background = accent.value + '1a';
      badge.style.color = accent.value;
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

  // ── Check Face ID status ──
  async function refreshStatus() {
    try {
      const res = await FaceAuth.getFaceStatus();
      if (res.enrolled) {
        statusLabel.textContent = 'Face ID activé';
        statusLabel.style.color = 'var(--accent-primary)';
        statusHint.textContent = 'Votre visage est enregistré. Vous pouvez l\'utiliser pour vous connecter.';
        actionsDiv.innerHTML = `
          <button class="btn btn-sm btn-secondary" id="faceid-reenroll-btn"><i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Réenregistrer</button>
          <button class="btn btn-sm btn-ghost" id="faceid-remove-btn" style="color:var(--accent-tertiary);"><i data-lucide="trash-2" style="width:14px;height:14px;"></i> Supprimer</button>
        `;
      } else {
        statusLabel.textContent = 'Face ID non configuré';
        statusLabel.style.color = 'var(--text-secondary)';
        statusHint.textContent = 'Enregistrez votre visage pour une connexion rapide et sécurisée.';
        actionsDiv.innerHTML = `
          <button class="btn btn-sm btn-primary" id="faceid-enroll-btn"><i data-lucide="scan-face" style="width:14px;height:14px;"></i> Configurer</button>
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

  // ── Open enrollment modal ──
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

  // ── Close modal ──
  modalClose.addEventListener('click', function() {
    cancelled = true;
    FaceAuth.stopCamera();
    modal.style.display = 'none';
  });

  // ── Start enrollment with liveness ──
  startBtn.addEventListener('click', async function() {
    if (enrolling) return;
    enrolling = true;
    startBtn.disabled = true;
    startBtn.innerHTML = '<i data-lucide="loader" style="width:18px;height:18px;animation:spin 1s linear infinite;"></i> En cours...';
    resultDiv.style.display = 'none';

    const descriptor = await FaceAuth.runLivenessCheck(
      videoEl,
      // onStatus callback
      function(text, step, total) {
        instruction.textContent = text;
        progressSteps.forEach(function(s, i) {
          s.style.background = i < step ? 'var(--accent-primary)' : 'var(--border-color)';
        });
      },
      // onCancel callback
      function() { return cancelled; },
      // overlay canvas
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

  // ── Initial status check ──
  refreshStatus();
});
</script>

<script>
  const TwoFactor = {
    apiUrl: '/aptus_first_official_version/controller/TwoFactorC.php',
    
    async apiCall(data) {
      const res = await fetch(this.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      return res.json();
    },

    async refreshStatus() {
      const label = document.getElementById('2fa-status-label');
      const hint = document.getElementById('2fa-status-hint');
      const actions = document.getElementById('2fa-actions');
      if (!label) return;

      try {
        const res = await this.apiCall({ action: 'status' });
        if (res.enabled) {
          label.textContent = 'Protection Activée';
          label.style.color = '#10b981';
          hint.textContent = 'Votre compte bénéficie d\'une sécurité maximale.';
          actions.innerHTML = '<button type="button" class="btn btn-ghost" onclick="TwoFactor.disable()" style="color: #ef4444; font-size: 13px; padding: 6px 12px; border: 1px solid rgba(239, 68, 68, 0.2);"><i data-lucide="trash-2" style="width:14px;height:14px;vertical-align:-2px;"></i> Désactiver</button>';
        } else {
          label.textContent = 'Sécurité Non Configurée';
          label.style.color = 'var(--text-secondary)';
          hint.textContent = 'Activez la 2FA pour bloquer les tentatives de piratage.';
          actions.innerHTML = '<button type="button" class="btn btn-primary" onclick="TwoFactor.setup()" style="padding: 10px 24px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px var(--accent-primary-light);">Configurer la 2FA</button>';
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
      } catch (e) {
        console.error(e);
      }
    },

    async setup() {
      const modal = document.getElementById('2fa-modal');
      const qrImg = document.getElementById('2fa-qr-code');
      const secretText = document.getElementById('2fa-secret-text');
      
      this.goToStep(1);
      
      try {
        const res = await this.apiCall({ action: 'setup' });
        if (res.success) {
          qrImg.src = res.qrCodeUrl;
          secretText.textContent = res.secret;
          modal.classList.add('active');
        }
      } catch (e) {
        alert('Erreur lors de la configuration.');
      }
    },

    goToStep(step) {
      document.getElementById('2fa-step-1').style.display = (step === 1) ? 'block' : 'none';
      document.getElementById('2fa-step-2').style.display = (step === 2) ? 'block' : 'none';
      document.getElementById('2fa-step-success').style.display = (step === 'success') ? 'block' : 'none';
      document.getElementById('2fa-step-indicator').style.display = (step === 'success') ? 'none' : 'flex';
      
      const dot1 = document.getElementById('dot-1');
      const dot2 = document.getElementById('dot-2');
      if (dot1 && dot2) {
        dot1.className = (step === 1) ? 'step-dot active' : 'step-dot';
        dot2.className = (step === 2) ? 'step-dot active' : 'step-dot';
      }

      if (step === 2) {
        setTimeout(() => document.getElementById('2fa-verify-code').focus(), 100);
      }
    },

    async disable() {
      if (confirm('Voulez-vous vraiment désactiver la 2FA ? Votre compte sera moins protégé.')) {
        const res = await this.apiCall({ action: 'disable' });
        if (res.success) this.refreshStatus();
      }
    }
  };

  document.getElementById('2fa-confirm-btn').addEventListener('click', async () => {
    const code = document.getElementById('2fa-verify-code').value;
    const errorEl = document.getElementById('2fa-verify-error');
    const confirmBtn = document.getElementById('2fa-confirm-btn');
    
    if (code.length !== 6) {
      errorEl.textContent = 'Veuillez entrer le code à 6 chiffres.';
      errorEl.style.display = 'block';
      return;
    }

    errorEl.style.display = 'none';
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:18px;height:18px;"></i> Vérification...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
      const res = await TwoFactor.apiCall({ action: 'confirm', code });
      if (res.success) {
        TwoFactor.goToStep('success');
        TwoFactor.refreshStatus();
        if (typeof lucide !== 'undefined') lucide.createIcons();
      } else {
        errorEl.textContent = res.message;
        errorEl.style.display = 'block';
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Activer la 2FA';
      }
    } catch (e) {
      errorEl.textContent = 'Erreur lors de la vérification.';
      errorEl.style.display = 'block';
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Activer la 2FA';
    }
  });

  // Re-init status on page load
  window.addEventListener('load', () => {
    TwoFactor.refreshStatus();
  });
</script>
