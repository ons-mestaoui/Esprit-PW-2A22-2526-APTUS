<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Mon Profil"; $pageCSS = "cv.css"; 

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../controller/UtilisateurC.php';
$utilisateurC = new UtilisateurC();
$id = $_SESSION['id_utilisateur'];
$user = $utilisateurC->getUtilisateurById($id);

// Récupérer données candidat
$db = config::getConnexion();
$stmt = $db->prepare("SELECT * FROM candidat WHERE id_candidat = :id");
$stmt->execute(['id' => $id]);
$candidat = $stmt->fetch();
$competences = $candidat ? htmlspecialchars($candidat['competences'] ?? '') : '';

// Récupérer données profil
$stmtP = $db->prepare("SELECT * FROM profil WHERE id_utilisateur = :id");
$stmtP->execute(['id' => $id]);
$profil = $stmtP->fetch();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $competences_update = $_POST['competences'] ?? '';
    $niveauEtudes = $_POST['niveauEtudes'] ?? null;
    $niveau = $_POST['niveau'] ?? null;

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
                    // Si l'upload échoue à cause du poids (souvent > 2Mo sur XAMPP), on lance l'erreur pour ne pas ignorer
                    throw new Exception("L'image n'a pas pu être chargée. Code d'erreur PHP : " . $_FILES['photo']['error'] . " (1 = Fichier trop lourd)");
                }
            }

            // Mettre à jour utilisateur
            // ...
            $query = $db->prepare("UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone WHERE id_utilisateur = :id");
            $query->execute([
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone
            ]);

            // Mettre à jour profil
            if ($profil) {
                $queryP = $db->prepare("UPDATE profil SET photo = :photo, dateMiseAJour = NOW() WHERE id_utilisateur = :id");
                $queryP->execute(['id' => $id, 'photo' => $photo_base64]);
            } else {
                $queryP = $db->prepare("INSERT INTO profil (id_utilisateur, photo, dateCreation, dateMiseAJour) VALUES (:id, :photo, NOW(), NOW())");
                $queryP->execute(['id' => $id, 'photo' => $photo_base64]);
            }
            
            // Mettre à jour candidat 
            if ($candidat) {
                $queryC = $db->prepare("UPDATE candidat SET competences = :competences, niveauEtudes = :niveauEtudes, niveau = :niveau WHERE id_candidat = :id");
                $queryC->execute([
                    'id' => $id, 
                    'competences' => $competences_update,
                    'niveauEtudes' => $niveauEtudes,
                    'niveau' => $niveau
                ]);
            } else {
                $queryC = $db->prepare("INSERT INTO candidat (id_candidat, competences, niveauEtudes, niveau) VALUES (:id, :competences, :niveauEtudes, :niveau)");
                $queryC->execute([
                    'id' => $id, 
                    'competences' => $competences_update,
                    'niveauEtudes' => $niveauEtudes,
                    'niveau' => $niveau
                ]);
            }

            // Rafraîchir les données
            $user = $utilisateurC->getUtilisateurById($id);
            $stmtP->execute(['id' => $id]);
            $profil = $stmtP->fetch();
            
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $competences = htmlspecialchars($competences_update);
            
            $success = "Profil mis à jour avec succès.";
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
<!-- Included inside layout_front.php -->

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="user-circle" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mon Profil
      </h1>
      <p class="page-header__subtitle">Gérez vos informations personnelles et professionnelles</p>
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

<form method="POST" action="" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Photo & Quick Info -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    
    <div style="position:relative;width:120px;height:120px;margin:0 auto var(--space-4);">
      <div id="avatar-container" style="width:100%;height:100%;">
        <?php if (!empty($profil['photo'])): ?>
          <?php 
            $raw_photo = trim($profil['photo']);
            // Sécurité et Résilience : Vérifier si c'est un long texte (base64) ou un nom de fichier court
            if (strlen($raw_photo) > 50) {
                $photo_src = strpos($raw_photo, 'data:') === 0 ? $raw_photo : 'data:image/jpeg;base64,' . $raw_photo;
            } else {
                $photo_src = '/aptus_first_official_version/view/assets/uploads/profiles/' . htmlspecialchars($raw_photo);
            }
          ?>
          <img src="<?php echo $photo_src; ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--accent-primary);" title="Format sauvegardé">
          
          <?php if (strlen($raw_photo) > 50 && strlen($raw_photo) < 2000): ?>
            <div style="position:absolute;bottom:-40px;left:-20px;right:-20px;background:red;color:white;font-size:10px;padding:5px;border-radius:4px;z-index:100;">
                Taille B64: <?php echo strlen($raw_photo); ?> (Tronqué ! PHPMyAdmin: "photo" est toujours VARCHAR au lieu de LONGTEXT)
            </div>
          <?php endif; ?>
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
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);"><?php echo htmlspecialchars($user['role']); ?></p>
    <span class="badge badge-success" style="margin-bottom:var(--space-5);">Optionnel</span>

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
        Informations Personnelles
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="input" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input type="text" name="prenom" class="input" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" name="email" class="input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
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
          <label class="form-label">Niveau d'études</label>
          <select name="niveauEtudes" class="select">
              <option value="">Non spécifié</option>
              <option value="Bac" <?php echo (isset($candidat['niveauEtudes']) && $candidat['niveauEtudes'] == 'Bac')?'selected':'';?>>Bac</option>
              <option value="Bac+2" <?php echo (isset($candidat['niveauEtudes']) && $candidat['niveauEtudes'] == 'Bac+2')?'selected':'';?>>Bac+2 (BTS, DUT)</option>
              <option value="Bac+3" <?php echo (isset($candidat['niveauEtudes']) && $candidat['niveauEtudes'] == 'Bac+3')?'selected':'';?>>Bac+3 (Licence)</option>
              <option value="Bac+5" <?php echo (isset($candidat['niveauEtudes']) && $candidat['niveauEtudes'] == 'Bac+5')?'selected':'';?>>Bac+5 (Master, Ingénieur)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Années d'expérience</label>
          <select name="niveau" class="select">
              <option value="">Non spécifié</option>
              <option value="Junior" <?php echo (isset($candidat['niveau']) && $candidat['niveau'] == 'Junior')?'selected':'';?>>Junior (0-2 ans)</option>
              <option value="Confirmé" <?php echo (isset($candidat['niveau']) && $candidat['niveau'] == 'Confirmé')?'selected':'';?>>Confirmé (3-5 ans)</option>
              <option value="Senior" <?php echo (isset($candidat['niveau']) && $candidat['niveau'] == 'Senior')?'selected':'';?>>Senior (5+ ans)</option>
              <option value="Expert" <?php echo (isset($candidat['niveau']) && $candidat['niveau'] == 'Expert')?'selected':'';?>>Expert (10+ ans)</option>
          </select>
        </div>

        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Compétences</label>
          <div class="tag-input" id="skills-tag-input">
            <div class="tag-input__tags"></div>
            <input type="text" class="tag-input__field" list="skills-list" placeholder="Ajoutez vos compétences...">
            <input type="hidden" class="tag-input__hidden" name="competences" value="<?php echo $competences; ?>">
          </div>
          <datalist id="skills-list">
             <option value="React">
             <option value="Node.js">
             <option value="PHP">
             <option value="MySQL">
             <option value="Python">
             <option value="Java">
             <option value="Design UX/UI">
             <option value="Marketing SEO">
             <option value="Gestion de Projet">
          </datalist>
          
          <div class="suggested-skills" style="margin-top:var(--space-3);">
            <div style="font-size:var(--fs-sm);font-weight:var(--fw-medium);margin-bottom:var(--space-2);color:var(--text-secondary);">Compétences suggérées</div>
            <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);">
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> React
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Node.js
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> MySQL
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> PHP
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Python
                </button>
                <button type="button" class="btn btn-ghost btn-sm suggested-skill-btn" style="border:1px solid var(--border-color);border-radius:50px;padding:4px 12px;font-weight:normal;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Java
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save button -->
    <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
      <a href="profil_candidat.php" class="btn btn-ghost">Annuler</a>
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
</script>
