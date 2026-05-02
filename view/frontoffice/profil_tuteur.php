<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Mon Profil Tuteur"; $pageCSS = "cv.css"; 

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
include_once __DIR__ . '/../../controller/TuteurC.php';
include_once __DIR__ . '/../../controller/ProfilC.php';

$utilisateurC = new UtilisateurC();
$tuteurC = new TuteurC();
$profilC = new ProfilC();

$id = $_SESSION['id_utilisateur'];
$user = $utilisateurC->getUtilisateurById($id);
$tuteur = $tuteurC->getTuteurById($id);
$profil = $profilC->getProfilByIdUtilisateur($id);

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $specialite = $_POST['specialite'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $bio_tuteur = $_POST['bio_tuteur'] ?? ''; // From tuteur table
    
    $adresse = $_POST['adresse'] ?? null;
    $ville = $_POST['ville'] ?? null;
    $pays = $_POST['pays'] ?? null;
    $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Veuillez remplir les champs obligatoires (Nom, Prénom, Email).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'adresse email est invalide.";
    } elseif ($utilisateurC->emailExists($email, $id)) {
        $error = "Cette adresse email est déjà utilisée par un autre compte.";
    } else {
        try {
            // --- Traitement de la photo (Base64) ---
            $photo_base64 = $profil ? ($profil['photo'] ?? null) : null; 
            if (isset($_FILES['photo'])) {
                if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $imageData = file_get_contents($_FILES['photo']['tmp_name']);
                    $mimeType = !empty($_FILES['photo']['type']) ? $_FILES['photo']['type'] : 'image/jpeg'; 
                    $photo_base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                } elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception("L'image n'a pas pu être chargée. Code d'erreur PHP : " . $_FILES['photo']['error']);
                }
            }

            // Mettre à jour utilisateur
            $utilisateur_model = new Utilisateur($id, $nom, $prenom, $email, $user['motDePasse'], $user['role'], $telephone, $user['photo'] ?? null);
            $utilisateurC->updateUtilisateur($utilisateur_model, $id);

            // Mettre à jour profil (table générique profil)
            $p = new Profil(null, $id, $photo_base64, $bio_tuteur, $adresse, $ville, $pays, $date_naissance, $profil['linkedin']??null, $profil['siteWeb']??null);
            if ($profil) {
                $profilC->updateProfil($p, $id);
            } else {
                $profilC->addProfil($p);
            }
            
            // Mettre à jour tuteur (table spécifique)
            // Assuming Tuteur model and controller updateTuteur exist or can be called
            $tuteurC->updateTuteurInfo($id, $specialite, $experience, $bio_tuteur);

            // Rafraîchir les données
            $user = $utilisateurC->getUtilisateurById($id);
            $tuteur = $tuteurC->getTuteurById($id);
            $profil = $profilC->getProfilByIdUtilisateur($id);
            
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            
            $success = "Profil tuteur mis à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur de mise à jour : " . $e->getMessage();
        }
    }
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="graduation-cap" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mon Profil Professionnel
      </h1>
      <p class="page-header__subtitle">Gérez votre expertise et vos informations de contact</p>
    </div>
  </div>
</div>

<!-- ═══ Profile Content ═══ -->
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

<form method="POST" action="" enctype="multipart/form-data" data-validate>
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Photo & Quick Info -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    
    <div style="position:relative;width:120px;height:120px;margin:0 auto var(--space-4);">
      <div id="avatar-container" style="width:100%;height:100%;">
        <?php if (!empty($profil['photo'])): ?>
          <?php 
            $raw_photo = trim($profil['photo']);
            $photo_src = (strpos($raw_photo, 'data:') === 0) ? $raw_photo : 'data:image/jpeg;base64,' . $raw_photo;
          ?>
          <img src="<?php echo $photo_src; ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--accent-primary);">
        <?php else: ?>
          <div style="width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#fff;">
            <?php echo htmlspecialchars(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
          </div>
        <?php endif; ?>
      </div>
      
      <label for="photo_upload" style="position:absolute;bottom:0;right:0;background:var(--accent-primary);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid var(--bg-card);transition:transform 0.2s;" title="Changer de photo">
         <i data-lucide="camera" style="width:16px;height:16px;"></i>
      </label>
      <input type="file" id="photo_upload" name="photo" accept="image/png, image/jpeg, image/jpg" style="display:none;">
    </div>
    
    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);"><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);"><?php echo htmlspecialchars($tuteur['specialite'] ?? 'Tuteur'); ?></p>
    <div style="display:flex;justify-content:center;gap:var(--space-2);margin-bottom:var(--space-5);">
      <span class="badge" style="background:var(--stat-blue-bg);color:var(--stat-blue);font-size:10px;padding:2px 8px;border-radius:50px;">Expert</span>
      <span class="badge" style="background:var(--stat-teal-bg);color:var(--stat-teal);font-size:10px;padding:2px 8px;border-radius:50px;">Vérifié</span>
    </div>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm"><?php echo htmlspecialchars($user['email']); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="phone" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm"><?php echo htmlspecialchars($user['telephone'] ?? 'Non renseigné'); ?></span>
      </div>
    </div>
  </div>

  <!-- Right: Editable Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="user" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        Informations Générales
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="input" value="<?php echo htmlspecialchars($user['nom']); ?>" data-required="true">
        </div>
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input type="text" name="prenom" class="input" value="<?php echo htmlspecialchars($user['prenom']); ?>" data-required="true">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="text" name="email" class="input" value="<?php echo htmlspecialchars($user['email']); ?>" data-required="true" data-type="email">
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="input" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
        </div>

        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Spécialité</label>
          <input type="text" name="specialite" class="input" value="<?php echo htmlspecialchars($tuteur['specialite'] ?? ''); ?>" placeholder="Ex: Développement Web Fullstack">
        </div>

        <div class="form-group">
          <label class="form-label">Expérience (années)</label>
          <input type="text" name="experience" class="input" value="<?php echo htmlspecialchars($tuteur['experience'] ?? ''); ?>" placeholder="Ex: 5 ans">
        </div>
        <div class="form-group">
          <label class="form-label">Pays</label>
          <input type="text" name="pays" class="input" value="<?php echo htmlspecialchars($profil['pays'] ?? ''); ?>" placeholder="Ex: Tunisie">
        </div>

        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Biographie Professionnelle</label>
          <textarea name="bio_tuteur" class="input" style="min-height:120px;"><?php echo htmlspecialchars($tuteur['biographie'] ?? ''); ?></textarea>
          <p class="text-xs text-secondary" style="margin-top:4px;">Décrivez votre parcours et ce que vous apportez aux étudiants.</p>
        </div>
      </div>
    </div>

    <!-- Save button -->
    <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
      <a href="dashboard_tuteur.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <i data-lucide="save" style="width:18px;height:18px;"></i>
        Enregistrer les modifications
      </button>
    </div>
  </div>
</div>
</form>

<script>
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
lucide.createIcons();
</script>
