<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Profil Entreprise"; $pageCSS = "cv.css"; $userRole = "Entreprise"; 

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'Entreprise') {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
$utilisateurC = new UtilisateurC();
$id = $_SESSION['id_utilisateur'];
$user = $utilisateurC->getUtilisateurById($id);

include_once __DIR__ . '/../../controller/EntrepriseC.php';
include_once __DIR__ . '/../../controller/ProfilC.php';
$entrepriseC = new EntrepriseC();
$profilC = new ProfilC();

$entreprise = $entrepriseC->getEntrepriseById($id);
$profil = $profilC->getProfilByIdUtilisateur($id);

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raison_sociale = $_POST['raison_sociale'] ?? '';
    $secteur = $_POST['secteur'] ?? '';
    $siret = $_POST['siret'] ?? '';
    $taille = $_POST['taille'] ?? '';
    $anneeFondation = !empty($_POST['annee_fondation']) ? $_POST['annee_fondation'] : null;
    
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $site_web = $_POST['site_web'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $bio = $_POST['bio'] ?? '';

    if (empty($raison_sociale) || empty($email)) {
        $error = "Veuillez remplir les champs obligatoires (Raison sociale, Email).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'adresse email est invalide.";
    } elseif ($utilisateurC->emailExists($email, $id)) {
        $error = "Cette adresse email est déjà utilisée.";
    } else {
        try {
            // Photo upload
            $photo_base64 = $profil ? ($profil['photo'] ?? null) : null; 
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $imageData = file_get_contents($_FILES['logo']['tmp_name']);
                $mimeType = !empty($_FILES['logo']['type']) ? $_FILES['logo']['type'] : 'image/jpeg'; 
                $photo_base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception("L'image n'a pas pu être chargée. Code d'erreur : " . $_FILES['logo']['error']);
            }

            // Update utilisateur
            $utilisateur_model = new Utilisateur($id, $raison_sociale, $user['prenom'], $email, $user['motDePasse'], $user['role'], $telephone, $user['photo'] ?? null);
            $utilisateurC->updateUtilisateur($utilisateur_model, $id);

            // Update entreprise 
            $ent = new Entreprise($id, $secteur, $siret, $raison_sociale, $taille, $anneeFondation);
            if ($entreprise) {
                $entrepriseC->updateEntreprise($ent, $id);
            } else {
                $entrepriseC->addEntreprise($ent);
            }

            // Update profil
            $p = new Profil(null, $id, $photo_base64, $bio, $adresse, $ville, $pays, null, $linkedin, $site_web);
            if ($profil) {
                $profilC->updateProfil($p, $id);
            } else {
                $profilC->addProfil($p);
            }

            // Refresh data
            $user = $utilisateurC->getUtilisateurById($id);
            $entreprise = $entrepriseC->getEntrepriseById($id);
            $profil = $profilC->getProfilByIdUtilisateur($id);
            $_SESSION['nom'] = $raison_sociale;

            $success = "Profil mis à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}

$rs = htmlspecialchars($entreprise['raisonSociale'] ?? $user['nom'] ?? '');
$lettre = mb_substr($rs, 0, 1) . mb_substr(explode(' ', $rs . ' ')[1], 0, 1);
if (strlen(trim($lettre)) == 0) $lettre = "ET";

// Format stats or fallbacks
$creationDate = $profil['dateCreation'] ?? date('Y-m-d');
$fmtDate = date('d M. Y', strtotime($creationDate));
?>
<!-- Included inside layout_front.php (Enterprise view) -->

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="building-2" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Profil Entreprise
      </h1>
      <p class="page-header__subtitle">Gérez la vitrine de votre entreprise sur Aptus</p>
    </div>
  </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="color:green; margin-bottom:15px; padding:10px; border:1px solid green; background:#eaffea; border-radius:5px;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="color:red; margin-bottom:15px; padding:10px; border:1px solid red; background:#ffeaea; border-radius:5px;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- ═══ Profile Content ═══ -->
<form method="POST" action="" enctype="multipart/form-data" data-validate>
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Company Card -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    
    <div style="position:relative;width:120px;height:120px;margin:0 auto var(--space-4);">
      <div id="logo-container" style="width:100%;height:100%;border-radius:var(--radius-lg);overflow:hidden;border:3px solid var(--border-color);">
        <?php if (!empty($profil['photo'])): ?>
          <?php 
            $raw_photo = trim($profil['photo']);
            if (strlen($raw_photo) > 50) {
                $photo_src = strpos($raw_photo, 'data:') === 0 ? $raw_photo : 'data:image/jpeg;base64,' . $raw_photo;
            } else {
                $photo_src = '/aptus_first_official_version/view/assets/uploads/profiles/' . htmlspecialchars($raw_photo);
            }
          ?>
          <img src="<?php echo $photo_src; ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--accent-secondary),var(--accent-primary));display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;">
            <?php echo htmlspecialchars(strtoupper($lettre)); ?>
          </div>
        <?php endif; ?>
      </div>
      
      <label for="logo_upload" style="position:absolute;bottom:-10px;right:-10px;background:var(--accent-primary);color:#fff;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:3px solid var(--bg-card);transition:transform 0.2s;" title="Changer de logo">
         <i data-lucide="camera" style="width:18px;height:18px;"></i>
      </label>
      <input type="file" id="logo_upload" name="logo" accept="image/png, image/jpeg, image/jpg" style="display:none;">
    </div>
    
    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);"><?php echo $rs; ?></h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);">
        <?php 
            $sec = htmlspecialchars($entreprise['secteur'] ?? 'Secteur non défini'); 
            $tai = htmlspecialchars($entreprise['taille'] ?? '');
            echo $sec . ($tai ? ' • ' . $tai : ''); 
        ?>
    </p>
    <span class="badge badge-success" style="margin-bottom:var(--space-5);">Compte vérifié</span>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm" title="<?php echo htmlspecialchars($user['email']); ?>">
            <?php echo strlen($user['email']) > 22 ? substr(htmlspecialchars($user['email']), 0, 20).'...' : htmlspecialchars($user['email']); ?>
        </span>
      </div>
      <?php if (!empty($profil['siteWeb'])): ?>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="globe" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <a href="<?php echo htmlspecialchars($profil['siteWeb']); ?>" target="_blank" class="text-sm text-accent" style="text-decoration:none;">Site Web</a>
      </div>
      <?php endif; ?>
      <?php if (!empty($profil['adresse'])): ?>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm" title="<?php echo htmlspecialchars($profil['adresse']); ?>">
            <?php echo strlen($profil['adresse']) > 22 ? substr(htmlspecialchars($profil['adresse']), 0, 20).'...' : htmlspecialchars($profil['adresse']); ?>
        </span>
      </div>
      <?php endif; ?>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Inscrit le <?php echo $fmtDate; ?></span>
      </div>
      <?php if (!empty($profil['dateMiseAJour'])): ?>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="refresh-cw" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Mis à jour le <?php echo date('d/m/Y', strtotime($profil['dateMiseAJour'])); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: Editable Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <!-- Company Info -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="building-2" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        Informations de l'Entreprise
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom de l'entreprise</label>
          <input type="text" name="raison_sociale" class="input" value="<?php echo $rs; ?>" data-required="true">
        </div>
        <div class="form-group">
          <label class="form-label">Secteur d'activité</label>
          <select name="secteur" class="select">
            <option value="">Sélectionnez...</option>
            <option value="tech" <?php echo ($entreprise['secteur']??'')=='tech'?'selected':'';?>>Technologie</option>
            <option value="finance" <?php echo ($entreprise['secteur']??'')=='finance'?'selected':'';?>>Finance & Banque</option>
            <option value="sante" <?php echo ($entreprise['secteur']??'')=='sante'?'selected':'';?>>Santé</option>
            <option value="education" <?php echo ($entreprise['secteur']??'')=='education'?'selected':'';?>>Éducation</option>
            <option value="commerce" <?php echo ($entreprise['secteur']??'')=='commerce'?'selected':'';?>>Commerce & Retail</option>
            <option value="industrie" <?php echo ($entreprise['secteur']??'')=='industrie'?'selected':'';?>>Industrie</option>
            <option value="services" <?php echo ($entreprise['secteur']??'')=='services'?'selected':'';?>>Services</option>
            <option value="autre" <?php echo ($entreprise['secteur']??'')=='autre'?'selected':'';?>>Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">N° SIRET</label>
          <input type="text" name="siret" class="input" value="<?php echo htmlspecialchars($entreprise['siret'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Taille de l'entreprise</label>
          <select name="taille" class="select">
            <option value="">Sélectionnez...</option>
            <option value="1-10" <?php echo ($entreprise['taille']??'')=='1-10'?'selected':'';?>>1-10 employés</option>
            <option value="11-50" <?php echo ($entreprise['taille']??'')=='11-50'?'selected':'';?>>11-50 employés</option>
            <option value="51-200" <?php echo ($entreprise['taille']??'')=='51-200'?'selected':'';?>>51-200 employés</option>
            <option value="201-500" <?php echo ($entreprise['taille']??'')=='201-500'?'selected':'';?>>201-500 employés</option>
            <option value="500+" <?php echo ($entreprise['taille']??'')=='500+'?'selected':'';?>>500+ employés</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Année de fondation</label>
          <input type="text" name="annee_fondation" class="input" value="<?php echo htmlspecialchars($entreprise['anneeFondation'] ?? ''); ?>">
        </div>
      </div>
    </div>

    <!-- Contact -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="phone" style="width:20px;height:20px;color:var(--accent-secondary);"></i>
        Contact & Liens
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Email professionnel</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="text" name="email" class="input" value="<?php echo htmlspecialchars($user['email']); ?>" data-required="true" data-type="email">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <div class="input-icon-wrapper">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
            <input type="text" name="telephone" class="input" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Site Web</label>
          <div class="input-icon-wrapper">
            <i data-lucide="globe" style="width:18px;height:18px;"></i>
            <input type="text" name="site_web" class="input" value="<?php echo htmlspecialchars($profil['siteWeb'] ?? ''); ?>" data-type="url">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">LinkedIn</label>
          <div class="input-icon-wrapper">
            <i data-lucide="linkedin" style="width:18px;height:18px;"></i>
            <input type="text" name="linkedin" class="input" value="<?php echo htmlspecialchars($profil['linkedin'] ?? ''); ?>" data-type="url">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Pays</label>
          <div class="input-icon-wrapper">
            <i data-lucide="globe" style="width:18px;height:18px;"></i>
            <input type="text" name="pays" class="input" value="<?php echo htmlspecialchars($profil['pays'] ?? ''); ?>" placeholder="Ex: France">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Ville</label>
          <div class="input-icon-wrapper">
            <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
            <input type="text" name="ville" class="input" value="<?php echo htmlspecialchars($profil['ville'] ?? ''); ?>" placeholder="Ex: Paris">
          </div>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Adresse Complète</label>
          <input type="text" name="adresse" class="input" value="<?php echo htmlspecialchars($profil['adresse'] ?? ''); ?>">
        </div>
      </div>
    </div>

    <!-- Description -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="file-text" style="width:20px;height:20px;color:var(--stat-orange);"></i>
        Description
      </h3>
      <div class="form-group">
        <textarea name="bio" class="textarea" rows="5" placeholder="Décrivez votre entreprise..."><?php echo htmlspecialchars($profil['bio'] ?? ''); ?></textarea>
      </div>
    </div>

    <!-- Save button -->
    <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
      <a href="profil_entreprise.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <i data-lucide="save" style="width:18px;height:18px;"></i>
        Enregistrer les modifications
      </button>
    </div>
  </div>
</div>
</form>

<script>
document.getElementById('logo_upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imgContainer = document.getElementById('logo-container');
            // Check if transparent or jpg, but general object-fit handles it
            imgContainer.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
