<?php 
$pageTitle = "Utilisateurs"; 
include_once __DIR__ . '/../../controller/UtilisateurC.php';
$utilisateurC = new UtilisateurC();

// Variables from URL or Form
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id_edit = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$userToEdit = null;

// Handle Delete (GET)
if ($action === 'delete' && $id_edit > 0) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    try {
        $isDeletingSelf = (isset($_SESSION['id_utilisateur']) && $_SESSION['id_utilisateur'] == $id_edit);
        
        $utilisateurC->deleteUtilisateur($id_edit);
        
        if ($isDeletingSelf) {
            session_destroy();
            header('Location: ../frontoffice/login.php');
            exit();
        }
        
        header('Location: users.php');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (!isset($error)) $error = "";
if (!isset($success)) $success = "";

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
    $order_sql = "ORDER BY u.id_utilisateur ASC"; // Default
    if ($sort === 'role') {
        $order_sql = "ORDER BY u.role $order, u.id_utilisateur DESC";
    } elseif ($sort === 'nom') {
        $order_sql = "ORDER BY u.nom $order, u.id_utilisateur DESC";
    } elseif ($sort === 'id') {
        $order_sql = "ORDER BY u.id_utilisateur $order";
    } elseif ($sort === 'date') {
        $order_sql = "ORDER BY p.dateCreation $order, u.id_utilisateur DESC";
    } elseif ($sort === 'email') {
        $order_sql = "ORDER BY u.email $order, u.id_utilisateur DESC";
    }

    $db = config::getConnexion();
    $usersListe = $db->query("SELECT u.*, p.linkedin, p.photo, p.dateCreation, p.dateMiseAJour 
                              FROM utilisateur u 
                              LEFT JOIN profil p ON u.id_utilisateur = p.id_utilisateur 
                              $order_sql")->fetchAll();
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
    <div class="flex gap-3" style="align-items: center;">
      <?php if ($action !== 'add' && $action !== 'edit'): ?>
      <div class="search-box" style="position: relative;">
          <input type="text" id="userSearchInput" class="input" placeholder="Rechercher un utilisateur..." style="padding-left: 36px; border-radius: 8px; width: 250px;">
          <i data-lucide="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-secondary);"></i>
      </div>
      <div class="filter-box">
          <select id="roleFilterInput" class="select" style="border-radius: 8px; height: 100%;">
              <option value="">Tous les rôles</option>
              <option value="Admin">Admin</option>
              <option value="Entreprise">Entreprise</option>
              <option value="Candidat">Candidat</option>
          </select>
      </div>
      <div class="sort-box">
          <select onchange="if(this.value){ window.location.href='?'+this.value; } else { window.location.href='users.php'; }" class="select" style="border-radius: 8px; height: 100%;">
              <option value="">Trier par défaut</option>
              <optgroup label="Par date d'inscription">
                <option value="sort=id&order=desc" <?php echo ($sort === 'id' && $order === 'DESC') ? 'selected' : ''; ?>>Plus récents</option>
                <option value="sort=id&order=asc" <?php echo ($sort === 'id' && $order === 'ASC') ? 'selected' : ''; ?>>Plus anciens</option>
              </optgroup>
              <optgroup label="Par nom">
                <option value="sort=nom&order=asc" <?php echo ($sort === 'nom' && $order === 'ASC') ? 'selected' : ''; ?>>Nom (A-Z)</option>
                <option value="sort=nom&order=desc" <?php echo ($sort === 'nom' && $order === 'DESC') ? 'selected' : ''; ?>>Nom (Z-A)</option>
              </optgroup>
              <optgroup label="Par email">
                <option value="sort=email&order=asc" <?php echo ($sort === 'email' && $order === 'ASC') ? 'selected' : ''; ?>>Email (A-Z)</option>
                <option value="sort=email&order=desc" <?php echo ($sort === 'email' && $order === 'DESC') ? 'selected' : ''; ?>>Email (Z-A)</option>
              </optgroup>
              <optgroup label="Par rôle">
                <option value="sort=role&order=desc" <?php echo ($sort === 'role' && $order === 'DESC') ? 'selected' : ''; ?>>Rôle (A-Z)</option>
                <option value="sort=role&order=asc" <?php echo ($sort === 'role' && $order === 'ASC') ? 'selected' : ''; ?>>Rôle (Z-A)</option>
              </optgroup>
          </select>
      </div>
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
<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="color:red; margin-bottom:15px; padding:10px; border:1px solid red; background:#ffeaea; border-radius:5px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<div class="card-flat" style="overflow:hidden;">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Utilisateur</th>
        <th>Email</th>
        <th>
            <a href="?sort=role&order=<?php echo $sort === 'role' && $order === 'ASC' ? 'desc' : 'asc'; ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;" title="Trier par rôle">
                Rôle 
                <i data-lucide="arrow-up-down" style="width:14px;height:14px;"></i>
            </a>
        </th>
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
                <?php if (!empty($u['photo'])): ?>
                    <div class="avatar avatar-sm" style="width:32px;height:32px;border-radius:50%;overflow:hidden;">
                        <img src="<?php echo htmlspecialchars($u['photo']); ?>" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                <?php else: ?>
                    <div class="avatar avatar-sm avatar-initials" style="width:32px;height:32px;font-size:11px;">
                        <?php echo htmlspecialchars(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
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
                <a href="#" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer" onclick="return confirmDelete('users.php?action=delete&id=<?php echo $u['id_utilisateur']; ?>', 'cet utilisateur');">
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

<!-- Generic Delete Confirmation Modal -->
<div id="generalDeleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999; backdrop-filter: blur(4px);">
  <div style="background:var(--bg-card, #ffffff); border-radius:16px; padding:32px 24px; text-align:center; max-width:400px; width:90%; position:relative; box-shadow:0 10px 25px rgba(0,0,0,0.1); display:flex; flex-direction:column; align-items:center;">
    <button type="button" onclick="document.getElementById('generalDeleteModal').style.display='none';" style="position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; color:var(--text-secondary); padding:4px;">
      <i data-lucide="x" style="width:20px;height:20px;"></i>
    </button>
    
    <div style="width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
        <i data-lucide="alert-triangle" style="width:32px;height:32px;color:#ef4444;"></i>
    </div>
    
    <h3 style="font-size:24px; font-weight:700; color:var(--text-primary, #1e293b); margin-bottom:12px; font-family:'Inter', sans-serif;">Confirmation de suppression</h3>
    
    <p id="deleteModalText" style="font-size:16px; color:var(--text-secondary, #64748b); margin-bottom:32px; line-height:1.5;">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
    
    <div style="display:flex; gap:16px; width:100%;">
      <button type="button" onclick="document.getElementById('generalDeleteModal').style.display='none';" style="flex:1; padding:12px; border-radius:8px; border:1px solid var(--border-color, #e2e8f0); background:transparent; font-weight:600; color:var(--text-primary, #1e293b); cursor:pointer; font-size:15px; transition:all 0.2s;">Annuler</button>
      <button type="button" id="deleteModalConfirmBtn" style="flex:1; padding:12px; border-radius:8px; border:none; background:#ef4444; font-weight:600; color:#ffffff; cursor:pointer; font-size:15px; transition:all 0.2s; display:inline-flex; align-items:center; justify-content:center;">Oui, Supprimer</button>
    </div>
  </div>
</div>

<script>
function confirmDelete(url, itemName) {
    document.getElementById('deleteModalText').innerText = "Êtes-vous sûr de vouloir supprimer " + itemName + " ? Cette action est irréversible.";
    document.getElementById('deleteModalConfirmBtn').onclick = function() {
        window.location.href = url;
    };
    document.getElementById('generalDeleteModal').style.display = 'flex';
    return false; // prevent default link behavior
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    const roleFilterInput = document.getElementById('roleFilterInput');

    // Get current sort mode from PHP
    const currentSort = "<?php echo htmlspecialchars($sort); ?>";

    function filterTable() {
        const searchText = searchInput ? searchInput.value.toLowerCase() : '';
        const roleFilter = roleFilterInput ? roleFilterInput.value.toLowerCase() : '';
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        
        tableRows.forEach(row => {
            // Ignore empty state row
            if (row.cells.length === 1) return;
            
            // Extract and clean up text for accurate matching
            const idText = row.querySelector('td:nth-child(1)') ? row.querySelector('td:nth-child(1)').textContent.replace('#', '').trim().toLowerCase() : '';
            
            // For the name, extract specifically from the span to ignore the avatar initials
            const userNameSpan = row.querySelector('td:nth-child(2) span.fw-medium');
            const userNameText = userNameSpan ? userNameSpan.textContent.trim().toLowerCase() : '';
            
            const userEmailText = row.querySelector('td:nth-child(3)') ? row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase() : '';
            const roleCellText = row.querySelector('td:nth-child(4)') ? row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase() : '';
            
            // For date, extract just the creation date string part
            const dateSpan = row.querySelector('td:nth-child(6) span:first-child');
            const dateText = dateSpan ? dateSpan.textContent.replace('Créé:', '').replace('créé:', '').trim().toLowerCase() : '';
            
            let matchesSearch = false;
            
            if (searchText === '') {
                matchesSearch = true;
            } else if (currentSort === 'email') {
                matchesSearch = userEmailText.startsWith(searchText);
            } else if (currentSort === 'nom') {
                matchesSearch = userNameText.startsWith(searchText);
            } else if (currentSort === 'id') {
                matchesSearch = idText.startsWith(searchText);
            } else if (currentSort === 'date') {
                matchesSearch = dateText.startsWith(searchText);
            } else {
                // Default fallback (or if sort is 'role')
                matchesSearch = userNameText.startsWith(searchText) || userEmailText.startsWith(searchText);
            }
            
            const matchesRole = roleFilter === '' || roleCellText.includes(roleFilter);
            
            if (matchesSearch && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', filterTable);
    }
    if (roleFilterInput) {
        roleFilterInput.addEventListener('change', filterTable);
    }
});
</script>
