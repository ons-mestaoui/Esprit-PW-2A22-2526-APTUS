<?php 
$pageTitle = "Utilisateurs"; 
include_once __DIR__ . '/../../controller/UtilisateurC.php';
$utilisateurC = new UtilisateurC();

// Variables from URL or Form
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id_edit = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userToEdit = null;

// Handle Delete (GET)
if ($action === 'delete' && $id_edit > 0) {
    $utilisateurC->deleteUtilisateur($id_edit);
    header('Location: users.php');
    exit();
}

$error = "";
$success = "";

// Handle Add / Update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['action'] ?? '';
    
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['motDePasse'] ?? '';
    $role = $_POST['role'] ?? 'Candidat';
    $telephone = $_POST['telephone'] ?? null;
    $linkedin = $_POST['linkedin'] ?? '';
    $id_utilisateur = isset($_POST['id_utilisateur']) ? intval($_POST['id_utilisateur']) : 0;

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Nom, prénom et email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'email est invalide.";
    } elseif ($formAction === 'add' && empty($motDePasse)) {
        $error = "Le mot de passe est obligatoire pour la création.";
    } elseif (!empty($motDePasse) && strlen($motDePasse) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($utilisateurC->emailExists($email, $formAction === 'update' ? $id_utilisateur : 0)) {
        $error = "L'adresse email est déjà utilisée par un autre compte.";
    } else {
        $utilisateur = new Utilisateur($id_utilisateur, $nom, $prenom, $email, $motDePasse, $role, $telephone);

        if ($formAction === 'add') {
            $last_id = $utilisateurC->addUtilisateur($utilisateur);
            if ($last_id) {
                include_once __DIR__ . '/../../controller/ProfilC.php';
                $profilC = new ProfilC();
                $profil = new Profil(null, $last_id, null, null, null, null, null, null, $linkedin, null);
                $profilC->addProfil($profil);
            }
            header('Location: users.php');
            exit();
        } elseif ($formAction === 'update' && $id_utilisateur > 0) {
            $utilisateurC->updateUtilisateur($utilisateur, $id_utilisateur);
            
            include_once __DIR__ . '/../../controller/ProfilC.php';
            $profilC = new ProfilC();
            $existingProfil = $profilC->getProfilByIdUtilisateur($id_utilisateur);
            
            if ($existingProfil) {
                $profil = new Profil(
                    $existingProfil['id_profil'], $id_utilisateur, 
                    $existingProfil['photo'], $existingProfil['bio'], 
                    $existingProfil['adresse'], $existingProfil['ville'], $existingProfil['pays'], 
                    $existingProfil['dateNaissance'], $linkedin, $existingProfil['siteWeb']
                );
                $profilC->updateProfil($profil, $id_utilisateur);
            } else {
                $profil = new Profil(null, $id_utilisateur, null, null, null, null, null, null, $linkedin, null);
                $profilC->addProfil($profil);
            }
            
            header('Location: users.php');
            exit();
        }
    }
}

// If editing, fetch the user data with profile
if ($action === 'edit' && $id_edit > 0) {
    try {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT u.*, p.linkedin FROM utilisateur u LEFT JOIN profil p ON u.id_utilisateur = p.id_utilisateur WHERE u.id_utilisateur = :id");
        $query->execute(['id' => $id_edit]);
        $userToEdit = $query->fetch();
    } catch (Exception $e) {
        $userToEdit = $utilisateurC->getUtilisateurById($id_edit);
    }
}

// Récupération de tous les utilisateurs agrégés avec leurs données de profil.
// On utilise un LEFT JOIN pour obtenir les informations des deux tables (utilisateur et profil)
// même si l'entrée dans la table profil n'existe pas encore.
try {
    $db = config::getConnexion();
    $usersListe = $db->query("SELECT u.*, p.linkedin, p.dateCreation, p.dateMiseAJour 
                              FROM utilisateur u 
                              LEFT JOIN profil p ON u.id_utilisateur = p.id_utilisateur")->fetchAll();
} catch (Exception $e) {
    $usersListe = $utilisateurC->listerUtilisateurs(); // Fallback
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Utilisateurs</h1>
      <p>Gérez les comptes candidats, entreprises et administrateurs</p>
    </div>
    <div class="flex gap-3">
      <?php if ($action !== 'add' && $action !== 'edit'): ?>
      <a href="users.php?action=add" class="btn btn-primary" id="add-user-btn">
        <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
        Ajouter
      </a>
      <?php else: ?>
      <a href="users.php" class="btn btn-secondary">
        <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        Retour à la liste
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ═══ Formulaire (Ajout / Modification) ═══ -->
<div class="card-flat" style="padding:var(--space-6); max-width:800px; margin:auto;">
    <h2><?php echo $action === 'edit' ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur'; ?></h2>
    <!-- Les erreurs de validation serveur (PHP) sont affichées ici -->
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="color:red; margin-top:15px; margin-bottom:15px; padding:10px; border:1px solid red; background:#ffeaea; border-radius:5px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="users.php" class="mt-4" data-validate>
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id_utilisateur" value="<?php echo htmlspecialchars($userToEdit['id_utilisateur'] ?? ''); ?>">
        <?php endif; ?>
        
        <div class="grid grid-2 gap-4 mb-4">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="input" value="<?php echo htmlspecialchars($userToEdit['nom'] ?? ''); ?>" data-required="true">
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="input" value="<?php echo htmlspecialchars($userToEdit['prenom'] ?? ''); ?>" data-required="true">
            </div>
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Email</label>
            <input type="text" name="email" class="input" value="<?php echo htmlspecialchars($userToEdit['email'] ?? ''); ?>" data-required="true" data-type="email">
        </div>

        <div class="grid grid-2 gap-4 mb-4">
            <div class="form-group">
                <label class="form-label">Mot de passe <?php echo $action === 'edit' ? '(Laisser vide pour ne pas modifier)' : ''; ?></label>
                <input type="password" name="motDePasse" class="input">
            </div>
            <div class="form-group">
                <label class="form-label">Rôle</label>
                <select name="role" class="select">
                    <option value="Candidat" <?php echo (isset($userToEdit['role']) && $userToEdit['role'] === 'Candidat') ? 'selected' : ''; ?>>Candidat</option>
                    <option value="Entreprise" <?php echo (isset($userToEdit['role']) && $userToEdit['role'] === 'Entreprise') ? 'selected' : ''; ?>>Entreprise</option>
                    <option value="Admin" <?php echo (isset($userToEdit['role']) && $userToEdit['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" class="input" value="<?php echo htmlspecialchars($userToEdit['telephone'] ?? ''); ?>">
        </div>

        <div class="form-group mb-4">
            <label class="form-label">LinkedIn (URL)</label>
            <input type="text" name="linkedin" class="input" value="<?php echo htmlspecialchars($userToEdit['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/..." data-type="url">
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'edit' ? 'Sauvegarder les modifications' : 'Créer l\'utilisateur'; ?>
            </button>
            <a href="users.php" class="btn btn-ghost">Annuler</a>
        </div>
    </form>
</div>
<?php else: ?>
<!-- ═══ Users Table ═══ -->
<div class="card-flat" style="overflow:hidden;">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Utilisateur</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Téléphone / LinkedIn</th>
        <th>Création / Maj</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($usersListe)): ?>
          <?php foreach ($usersListe as $u): ?>
          <tr>
            <td class="text-secondary">#<?php echo htmlspecialchars($u['id_utilisateur']); ?></td>
            <td>
              <div class="flex items-center gap-3">
                <div class="avatar avatar-sm avatar-initials" style="width:32px;height:32px;font-size:11px;">
                    <?php echo htmlspecialchars(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1)); ?>
                </div>
                <span class="fw-medium"><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></span>
              </div>
            </td>
            <td class="text-sm text-secondary"><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
                <?php 
                $badge = 'badge-info';
                if ($u['role'] === 'Admin') $badge = 'badge-danger';
                elseif ($u['role'] === 'Entreprise') $badge = 'badge-primary';
                ?>
                <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($u['role']); ?></span>
            </td>
            <td class="text-sm">
                <div class="flex flex-column gap-1">
                    <span class="text-secondary"><?php echo htmlspecialchars($u['telephone'] ?? '-'); ?></span>
                    <?php if (!empty($u['linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($u['linkedin']); ?>" target="_blank" class="text-accent text-xs" style="text-decoration:none;">
                            <i data-lucide="linkedin" style="width:12px;height:12px;vertical-align:middle;"></i> Profile
                        </a>
                    <?php endif; ?>
                </div>
            </td>
            <td class="text-xs text-secondary">
                <div class="flex flex-column">
                    <span>Créé: <?php echo !empty($u['dateCreation']) ? date('d/m/Y', strtotime($u['dateCreation'])) : '-'; ?></span>
                    <span>Màj: <?php echo !empty($u['dateMiseAJour']) ? date('d/m/Y', strtotime($u['dateMiseAJour'])) : '-'; ?></span>
                </div>
            </td>
            <td>
              <div class="flex gap-1">
                <a href="users.php?action=edit&id=<?php echo $u['id_utilisateur']; ?>" class="btn btn-sm btn-ghost" title="Éditer">
                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                </a>
                <a href="users.php?action=delete&id=<?php echo $u['id_utilisateur']; ?>" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
      <?php else: ?>
          <tr>
              <td colspan="6" class="text-center" style="padding:var(--space-6);">Aucun utilisateur trouvé.</td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
