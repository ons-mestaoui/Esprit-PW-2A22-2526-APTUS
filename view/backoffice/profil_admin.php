<?php $pageTitle = "Profil Admin"; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontoffice/login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/AdminC.php';
include_once __DIR__ . '/../../controller/ProfilC.php';

$utilisateurC = new UtilisateurC();
$adminC = new AdminC();
$profilC = new ProfilC();

$id = $_SESSION['id_utilisateur'];
$user = $utilisateurC->getUtilisateurById($id);
$admin = $adminC->getAdminById($id);
$profil = $profilC->getProfilByIdUtilisateur($id);

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    
    $adresse = $_POST['adresse'] ?? null;
    $ville = $_POST['ville'] ?? null;
    $pays = $_POST['pays'] ?? null;
    $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;

    if (empty($nom) || empty($email)) {
        $error = "Le nom entier et l'email sont obligatoires.";
    } else {
        try {
            // --- Traitement de la photo (Base64) ---
            $photo_base64 = $profil ? ($profil['photo'] ?? null) : null; 
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $imageData = file_get_contents($_FILES['photo']['tmp_name']);
                $mimeType = !empty($_FILES['photo']['type']) ? $_FILES['photo']['type'] : 'image/jpeg'; 
                $photo_base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }

            $utilisateur_model = new Utilisateur($id, $nom, $user['prenom'], $email, $user['motDePasse'], $user['role'], $telephone, $user['photo']??null);
            $utilisateurC->updateUtilisateur($utilisateur_model, $id);

            $p = new Profil(null, $id, $photo_base64, $profil['bio']??null, $adresse, $ville, $pays, $date_naissance, $profil['linkedin']??null, $profil['siteWeb']??null);
            if ($profil) {
                $profilC->updateProfil($p, $id);
            } else {
                $profilC->addProfil($p);
            }

            $user = $utilisateurC->getUtilisateurById($id);
            $profil = $profilC->getProfilByIdUtilisateur($id);
            $success = "Profil mis à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

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
    
    <div style="position:relative;width:120px;height:120px;margin:0 auto var(--space-4);">
      <div id="avatar-container" style="width:100%;height:100%;">
        <?php if (!empty($profil['photo'])): ?>
          <img src="<?php echo $profil['photo']; ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--accent-primary);">
        <?php else: ?>
          <div style="width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#fff;">
            <?php echo strtoupper(substr($user['nom'] ?? 'A', 0, 1) . substr($user['prenom'] ?? 'D', 0, 1)); ?>
          </div>
        <?php endif; ?>
      </div>
      <label for="photo_upload" style="position:absolute;bottom:0;right:0;background:var(--accent-primary);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid var(--bg-card);transition:transform 0.2s;" title="Changer de photo">
         <i data-lucide="camera" style="width:16px;height:16px;"></i>
      </label>
    </div>

    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);"><?php echo htmlspecialchars($user['nom'] ?? 'Administrateur'); ?></h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);">Super Admin</p>
    <span class="badge badge-primary" style="margin-bottom:var(--space-5);">Super Admin</span>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm"><?php echo htmlspecialchars($user['email'] ?? 'admin@aptus.com'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="shield-check" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Accès complet</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm"><?php echo htmlspecialchars($profil['ville'] ?? 'Ville non définie'); ?>, <?php echo htmlspecialchars($profil['pays'] ?? 'Pays non défini'); ?></span>
      </div>
      <?php if (!empty($profil['dateCreation'])): ?>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Inscrit en <?php echo htmlspecialchars(substr($profil['dateCreation'], 0, 4)); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: Edit Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="color:var(--accent-tertiary); text-align:center; padding: 12px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2);">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="color:#10b981; text-align:center; padding: 12px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.2);">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="file" id="photo_upload" name="photo" accept="image/png, image/jpeg, image/jpg" style="display:none;">
        
        <!-- Personal Info -->
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);margin-bottom:var(--space-6);">
          <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
            <i data-lucide="user" style="width:20px;height:20px;color:var(--accent-primary);"></i>
            Informations Personnelles
          </h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
            <div class="form-group">
              <label class="form-label">Nom complet</label>
              <input type="text" name="nom" class="input" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <div class="input-icon-wrapper">
                <i data-lucide="mail" style="width:18px;height:18px;"></i>
                <input type="email" name="email" class="input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Téléphone</label>
              <div class="input-icon-wrapper">
                <i data-lucide="phone" style="width:18px;height:18px;"></i>
                <input type="tel" name="telephone" class="input" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Date de naissance</label>
              <div class="input-icon-wrapper">
                <i data-lucide="calendar" style="width:18px;height:18px;"></i>
                <input type="text" name="date_naissance" class="input" value="<?php echo htmlspecialchars($profil['dateNaissance'] ?? ''); ?>" placeholder="AAAA-MM-JJ">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Pays</label>
              <input type="text" name="pays" class="input" value="<?php echo htmlspecialchars($profil['pays'] ?? ''); ?>" placeholder="Ex: Tunisie">
            </div>
            <div class="form-group">
              <label class="form-label">Ville</label>
              <input type="text" name="ville" class="input" value="<?php echo htmlspecialchars($profil['ville'] ?? ''); ?>" placeholder="Ex: Tunis">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label">Adresse Complète</label>
              <input type="text" name="adresse" class="input" value="<?php echo htmlspecialchars($profil['adresse'] ?? ''); ?>" placeholder="Ex: 12 Rue des Oliviers">
            </div>
          </div>
        </div>

        <!-- Security -->
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);margin-bottom:var(--space-6);">
          <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
            <i data-lucide="lock" style="width:20px;height:20px;color:var(--accent-secondary);"></i>
            Changer le mot de passe
          </h3>
          <p class="text-sm text-secondary" style="margin-bottom:10px;">La gestion du mot de passe se fait via les paramètres de sécurité globaux.</p>
        </div>

        <!-- Save -->
        <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:18px;height:18px;"></i>
            Enregistrer les modifications
          </button>
        </div>
    </form>
  </div>

</div>

<script>
// Prévisualisation de la photo sélectionnée
document.getElementById('photo_upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imgContainer = document.getElementById('avatar-container');
            imgContainer.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--accent-primary);">';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

