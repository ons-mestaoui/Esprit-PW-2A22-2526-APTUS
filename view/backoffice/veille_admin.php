<?php 
$pageTitle = "Veille Marché — Publisher"; 
$pageCSS = "veille.css"; 

require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';
$vc = new VeilleC();

$fieldErrors = [];

// --- POST HANDLING WITH PHP VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ===================== RAPPORTS =====================
    if ($action === 'add_rapport' || $action === 'update_rapport') {

        // --- Validation côté serveur (PHP) ---
        if (empty(trim($_POST['titre'] ?? ''))) {
            $fieldErrors['titre'] = "Le titre du rapport est obligatoire.";
        }
        if (empty(trim($_POST['contenu_detaille'] ?? '')) || trim($_POST['contenu_detaille']) === '<p><br></p>') {
            $fieldErrors['contenu_detaille'] = "Le corps du rapport (contenu riche) est obligatoire.";
        }
        if (empty(trim($_POST['auteur'] ?? ''))) {
            $fieldErrors['auteur'] = "L'auteur du rapport est obligatoire.";
        }
        
        // Salaire Min/Max required and numeric
        if (empty($_POST['salaire_min_global'])) {
            $fieldErrors['salaire_min_global'] = "Le salaire minimum global est obligatoire.";
        } elseif (!is_numeric($_POST['salaire_min_global'])) {
            $fieldErrors['salaire_min_global'] = "Le salaire minimum doit être un nombre.";
        }
        
        if (empty($_POST['salaire_max_global'])) {
            $fieldErrors['salaire_max_global'] = "Le salaire maximum global est obligatoire.";
        } elseif (!is_numeric($_POST['salaire_max_global'])) {
            $fieldErrors['salaire_max_global'] = "Le salaire maximum doit être un nombre.";
        }

        if (!empty($_POST['salaire_moyen_global']) && !is_numeric($_POST['salaire_moyen_global'])) {
            $fieldErrors['salaire_moyen_global'] = "Le salaire moyen global doit être un nombre.";
        }
        
        if (!empty($_POST['salaire_min_global']) && !empty($_POST['salaire_max_global'])
            && is_numeric($_POST['salaire_min_global']) && is_numeric($_POST['salaire_max_global'])
            && floatval($_POST['salaire_min_global']) > floatval($_POST['salaire_max_global'])) {
            $fieldErrors['salaire_max_global'] = "Le salaire minimum ne peut pas être supérieur au salaire maximum.";
        }

        // Association minimal requirement: At least one data point
        if (!isset($_POST['linked_donnees']) || !is_array($_POST['linked_donnees']) || count($_POST['linked_donnees']) === 0) {
            $fieldErrors['linked_donnees'] = "Le rapport doit être associé à au moins une donnée du marché.";
        }

        // --- Auto-calcul du salaire moyen si min et max sont fournis ---
        $salaire_min_g = !empty($_POST['salaire_min_global']) && is_numeric($_POST['salaire_min_global']) ? floatval($_POST['salaire_min_global']) : null;
        $salaire_max_g = !empty($_POST['salaire_max_global']) && is_numeric($_POST['salaire_max_global']) ? floatval($_POST['salaire_max_global']) : null;
        $salaire_moyen_g = !empty($_POST['salaire_moyen_global']) && is_numeric($_POST['salaire_moyen_global']) ? floatval($_POST['salaire_moyen_global']) : null;

        if ($salaire_min_g !== null && $salaire_max_g !== null && $salaire_moyen_g === null) {
            $salaire_moyen_g = round(($salaire_min_g + $salaire_max_g) / 2, 2);
        }

        if (empty($fieldErrors)) {
            $date_pub = ($action === 'update_rapport') ? $_POST['date_publication'] : date('Y-m-d');
            $id_rm = ($action === 'update_rapport') ? $_POST['id_rapport_marche'] : null;

            $rapport = new RapportMarche(
                1,
                trim($_POST['titre']),
                trim($_POST['description']),
                $date_pub,
                $_POST['region'] ?? '',
                $_POST['secteur_principal'] ?? '',
                $salaire_moyen_g,
                $salaire_min_g,
                $salaire_max_g,
                $_POST['tendance_generale'] ?? '',
                $_POST['niveau_demande_global'] ?? null,
                isset($_POST['linked_donnees']) ? count($_POST['linked_donnees']) : 0,
                $_POST['auteur'] ?? '',
                $_POST['contenu_detaille'] ?? '',
                $_POST['image_couverture'] ?? '',
                $action === 'update_rapport' ? ($_POST['vues'] ?? 0) : 0,
                $id_rm
            );

            if ($action === 'add_rapport') {
                $id_rapport = $vc->ajouterRapport($rapport);
                if (isset($_POST['linked_donnees']) && is_array($_POST['linked_donnees'])) {
                    $vc->lierDonneesAuRapport($_POST['linked_donnees'], $id_rapport);
                }
                header('Location: veille_admin.php?success=1&tab=rapports');
                exit;
            } else {
                $vc->modifierRapport($rapport);
                $vc->delierToutesDonneesDUnRapport($_POST['id_rapport_marche']);
                if (isset($_POST['linked_donnees']) && is_array($_POST['linked_donnees'])) {
                    $vc->lierDonneesAuRapport($_POST['linked_donnees'], $_POST['id_rapport_marche']);
                }
                header('Location: veille_admin.php?success=2&tab=rapports');
                exit;
            }
        }
    }
    elseif ($action === 'delete_rapport') {
        $vc->supprimerRapport($_POST['id_rapport_marche']);
        header('Location: veille_admin.php?success=3&tab=rapports');
        exit;
    }

    // ===================== DONNEES =====================
    elseif ($action === 'add_donnee' || $action === 'update_donnee') {

        // --- Validation côté serveur (PHP) ---
        if (empty(trim($_POST['domaine'] ?? ''))) {
            $fieldErrors['domaine'] = "Le domaine est obligatoire.";
        }
        if (empty(trim($_POST['competence'] ?? ''))) {
            $fieldErrors['competence'] = "La compétence est obligatoire.";
        }
        if (!empty($_POST['salaire_min']) && !is_numeric($_POST['salaire_min'])) {
            $fieldErrors['salaire_min'] = "Le salaire minimum doit être un nombre.";
        }
        if (!empty($_POST['salaire_max']) && !is_numeric($_POST['salaire_max'])) {
            $fieldErrors['salaire_max'] = "Le salaire maximum doit être un nombre.";
        }
        if (!empty($_POST['salaire_moyen']) && !is_numeric($_POST['salaire_moyen'])) {
            $fieldErrors['salaire_moyen'] = "Le salaire moyen doit être un nombre.";
        }
        if (!empty($_POST['salaire_min']) && !empty($_POST['salaire_max'])
            && is_numeric($_POST['salaire_min']) && is_numeric($_POST['salaire_max'])
            && floatval($_POST['salaire_min']) > floatval($_POST['salaire_max'])) {
            $fieldErrors['salaire_max'] = "Le salaire minimum ne peut pas être supérieur au salaire maximum.";
        }

        // --- Auto-calcul du salaire moyen si min et max sont fournis ---
        $salaire_min_d = !empty($_POST['salaire_min']) && is_numeric($_POST['salaire_min']) ? floatval($_POST['salaire_min']) : null;
        $salaire_max_d = !empty($_POST['salaire_max']) && is_numeric($_POST['salaire_max']) ? floatval($_POST['salaire_max']) : null;
        $salaire_moyen_d = !empty($_POST['salaire_moyen']) && is_numeric($_POST['salaire_moyen']) ? floatval($_POST['salaire_moyen']) : null;

        if ($salaire_min_d !== null && $salaire_max_d !== null && $salaire_moyen_d === null) {
            $salaire_moyen_d = round(($salaire_min_d + $salaire_max_d) / 2, 2);
        }

        if (empty($fieldErrors)) {
            $donnee = new DonneeMarche(
                ($action === 'update_donnee' && !empty($_POST['id_rapport_marche'])) ? $_POST['id_rapport_marche'] : null,
                trim($_POST['domaine']),
                trim($_POST['competence']),
                $salaire_min_d,
                $salaire_max_d,
                $salaire_moyen_d,
                $_POST['demande'] ?? null,
                !empty($_POST['date_collecte']) ? $_POST['date_collecte'] : date('Y-m-d'),
                trim($_POST['description_donnee'] ?? ''),
                ($action === 'update_donnee') ? $_POST['id_donnee'] : null
            );

            if ($action === 'add_donnee') {
                $vc->ajouterDonnee($donnee);
                header('Location: veille_admin.php?success=4&tab=donnees');
                exit;
            } else {
                $vc->modifierDonnee($donnee);
                header('Location: veille_admin.php?success=5&tab=donnees');
                exit;
            }
        }
    }
    elseif ($action === 'delete_donnee') {
        $vc->supprimerDonnee($_POST['id_donnee']);
        header('Location: veille_admin.php?success=6&tab=donnees');
        exit;
    }

    // If we reach here with errors, override the active tab based on the action
    if (!empty($fieldErrors)) {
        if (strpos($action, 'rapport') !== false) {
            $activeTabOverride = 'rapports';
        } else {
            $activeTabOverride = 'donnees';
        }
    }
}

// --- FETCHING DATA ---
$listeRapportsDb = $vc->afficherRapports();
$listeDonneesDb = $vc->afficherToutesDonnees();
$mapLiaisons = $vc->getMapLiaisons(); // M2M links $map[id_donnee] = [id1, id2...]

$activeTab = $activeTabOverride ?? ($_GET['tab'] ?? 'rapports');

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
.tabs-container { margin-bottom: 24px; border-bottom: 2px solid var(--border-color); display: flex; gap: 16px; }
.tab-btn { padding: 12px 24px; font-weight: 600; font-size: 14px; color: var(--text-secondary); background: transparent; border: none; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
.tab-btn.active { color: var(--accent-primary); border-bottom-color: var(--accent-primary); }
.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

/* Modals */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
.modal-overlay.active { display: flex; animation: fadeInOverlay 0.2s ease; }
.modal-content { color: var(--text-primary); border: 1px solid var(--border-color); width: 100%; max-width: 800px; max-height: 90vh; border-radius: var(--radius-lg); padding: 32px; overflow-y: auto; position:relative; z-index: 1001; transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.modal-overlay.active .modal-content { transform: scale(1); }
@keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }

/* Light & Dark Contexts for Modals */
[data-theme="light"] .modal-overlay { background: rgba(255,255,255,0.6); }
[data-theme="light"] .modal-content { background: #ffffff; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }

[data-theme="dark"] .modal-overlay { background: rgba(0,0,0,0.6); }
[data-theme="dark"] .modal-content { background: #1a1d2d; box-shadow: 0 25px 50px rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.05); }
.modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; cursor: pointer; color: var(--text-secondary); padding:4px; }
.modal-close:hover { color: var(--text-primary); }

.data-checkbox-card { border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-md); display: flex; align-items: flex-start; gap: 12px; transition: 0.2s; cursor: pointer; background: var(--bg-main); }
.data-checkbox-card:hover { border-color: var(--accent-primary); background: rgba(99,102,241,0.03); }
.data-checkbox-card.is-selected { border-color: var(--accent-primary); background: rgba(99,102,241,0.08); box-shadow: 0 0 0 1px var(--accent-primary); }
.error-text { color: #ef4444; font-size: 12px; margin-top: 4px; display: block; font-weight: 500; }
.input.is-invalid, .textarea.is-invalid { border-color: #ef4444 !important; background-color: rgba(239, 68, 68, 0.05) !important; }
/* Quill Overrides */
.ql-editor { min-height: 200px; color: var(--text-primary); font-size: 15px; }
.ql-toolbar { background: #f8fafc; border-top-left-radius: 8px; border-top-right-radius: 8px; }
.ql-container { border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; background: var(--input-bg); }
[data-theme="dark"] .ql-toolbar { background: #1e293b; border-color: var(--border-color); }
[data-theme="dark"] .ql-container { border-color: var(--border-color); }
[data-theme="dark"] .ql-stroke { stroke: #cbd5e1; }

/* ── Image Upload Zone (Dropzone) ──────────────── */
.image-upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-md);
  padding: 32px 16px;
  text-align: center;
  background: var(--bg-main);
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-top: 8px;
}
.image-upload-zone:hover, .image-upload-zone.dragover {
  border-color: var(--accent-primary);
  background: rgba(99, 102, 241, 0.05);
}
.image-upload-zone__icon {
  width: 48px;
  height: 48px;
  color: var(--text-tertiary);
  transition: color 0.3s ease;
}
.image-upload-zone:hover .image-upload-zone__icon {
  color: var(--accent-primary);
}
.image-upload-zone__text {
  font-size: 14px;
  color: var(--text-secondary);
}
.image-upload-zone__text strong {
  color: var(--accent-primary);
}
.image-upload-zone__hint {
  font-size: 12px;
  color: var(--text-tertiary);
}
.image-upload-preview {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: none;
  z-index: 10;
}
.image-upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 20;
  opacity: 0;
  transition: opacity 0.3s ease;
  color: white;
  gap: 8px;
}
.image-upload-zone:hover .image-upload-overlay {
  opacity: 1;
}
.image-upload-zone.has-image {
  border-style: solid;
}
.image-upload-zone.has-image .image-upload-preview,
.image-upload-zone.has-image .image-upload-overlay {
  display: flex;
}
</style>

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Gestion de la Veille</h1>
      <p>Séparation des responsabilités : gérez les rapports et les points de données brutes indépendamment.</p>
    </div>
  </div>
</div>

<?php 
$msgs = [
    1 => "Rapport ajouté avec succès.", 
    2 => "Rapport modifié avec succès.", 
    3 => "Rapport supprimé.",
    4 => "Donnée brute ajoutée avec succès.",
    5 => "Donnée globale modifiée.",
    6 => "Donnée supprimée."
];
if(isset($_GET['success']) && isset($msgs[$_GET['success']])): 
?>
<div style="background:#10b981; color:#fff; padding:12px 20px; border-radius:8px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
    <?php echo $msgs[$_GET['success']]; ?>
</div>
<?php endif; ?>

<?php if(!empty($fieldErrors)): ?>
<div style="background:#ef4444; color:#fff; padding:12px 20px; border-radius:8px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
    <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
    Des erreurs ont été détectées dans le formulaire. Veuillez vérifier les champs ci-dessous.
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs-container">
    <button class="tab-btn <?php echo $activeTab === 'rapports' ? 'active' : ''; ?>" onclick="switchTab('rapports')"><i data-lucide="file-text" style="width:16px;height:16px;margin-right:6px;display:inline;"></i> Rapports de Marché</button>
    <button class="tab-btn <?php echo $activeTab === 'donnees' ? 'active' : ''; ?>" onclick="switchTab('donnees')"><i data-lucide="database" style="width:16px;height:16px;margin-right:6px;display:inline;"></i> Explorateur de Données Brutes</button>
</div>

<!-- ============================================== -->
<!-- TAB: RAPPORTS -->
<!-- ============================================== -->
<div id="tab-rapports" class="tab-content <?php echo $activeTab === 'rapports' ? 'active' : ''; ?>">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-6);">
        <h3>Vos Rapports</h3>
        <button class="btn btn-primary" onclick="openRapportModal('add')">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Nouveau Rapport
        </button>
    </div>

    <div class="published-list" style="margin-top:20px;">
      <?php foreach ($listeRapportsDb as $p): ?>
      <div class="published-item" style="display:flex; justify-content:space-between; align-items:center; padding:16px;">
        <div>
          <div class="published-item__title" style="font-size:16px; font-weight:600; margin-bottom:4px;"><?php echo htmlspecialchars($p['titre']); ?></div>
          <div class="published-item__meta" style="color:var(--text-secondary); font-size:13px;">
            <span><i data-lucide="calendar" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> <?php echo date('d M', strtotime($p['date_publication'])); ?></span>
            <span style="margin-left:12px;"><i data-lucide="link" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> <?php echo $p['nombre_donnees']; ?> données liées</span>
            <span class="badge badge-sm" style="margin-left:12px; background:rgba(99,102,241,0.1); color:var(--accent-primary);"><i data-lucide="eye" style="width:10px;height:10px;display:inline;"></i> <?php echo $p['vues']; ?> vues</span>
          </div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-sm btn-ghost" onclick='openRapportModal("edit", <?php echo json_encode($p); ?>)'><i data-lucide="edit" style="width:16px;height:16px;"></i></button>
            <button type="button" class="btn btn-sm btn-ghost text-danger" style="color:#ef4444;" onclick="openDeleteModal('delete_rapport', 'id_rapport_marche', <?php echo $p['id_rapport_marche']; ?>, 'Êtes-vous sûr de vouloir supprimer le rapport &quot;<?php echo addslashes($p['titre']); ?>&quot; ?')"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- TAB: DONNEES -->
<!-- ============================================== -->
<div id="tab-donnees" class="tab-content <?php echo $activeTab === 'donnees' ? 'active' : ''; ?>">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-6);">
        <h3>Données Brutes</h3>
        <button class="btn btn-primary" onclick="openDonneeModal('add')">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajouter une donnée
        </button>
    </div>

    <!-- Data List -->
    <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
        <table style="width:100%; border-collapse: collapse;">
            <thead style="background:rgba(255,255,255,0.02); text-align:left; border-bottom:2px solid var(--border-color);">
                <tr>
                    <th style="padding:16px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Domaine & Comp.</th>
                    <th style="padding:16px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Salaire Moyen</th>
                    <th style="padding:16px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Demande</th>
                    <th style="padding:16px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Statut Liaison</th>
                    <th style="padding:16px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; font-size:12px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $demandeMap = [4 => 'Très forte', 3 => 'Forte', 2 => 'Modérée', 1 => 'Faible'];
                foreach ($listeDonneesDb as $d): 
                    $demandeTxt = $demandeMap[$d['demande']] ?? 'N/A';
                    $liaisonsCounts = isset($mapLiaisons[$d['id_donnee']]) ? count($mapLiaisons[$d['id_donnee']]) : 0;
                ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:16px;">
                        <strong style="color:var(--text-primary);"><?php echo htmlspecialchars($d['domaine']); ?></strong><br>
                        <span style="color:var(--text-secondary); font-size:13px;"><?php echo htmlspecialchars($d['competence']); ?></span>
                    </td>
                    <td style="padding:16px; font-family:monospace;"><?php echo $d['salaire_moyen'] ? number_format($d['salaire_moyen'],0,',',' ').' TND' : '-'; ?></td>
                    <td style="padding:16px;"><span class="badge badge-info"><?php echo $demandeTxt; ?></span></td>
                    <td style="padding:16px;">
                        <?php if($liaisonsCounts > 0): ?>
                            <span class="badge badge-primary"><i data-lucide="link" style="width:10px;height:10px;"></i> <?php echo $liaisonsCounts; ?> Rapport(s)</span>
                        <?php else: ?>
                            <span class="badge" style="background:#4b5563;color:#fff;">Libre</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:16px; text-align:right;">
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <button class="btn btn-sm btn-ghost" onclick='openDonneeModal("edit", <?php echo json_encode($d); ?>)'><i data-lucide="edit" style="width:14px;height:14px;"></i></button>
                            <button type="button" class="btn btn-sm btn-ghost text-danger" style="color:#ef4444;" onclick="openDeleteModal('delete_donnee', 'id_donnee', <?php echo $d['id_donnee']; ?>, 'Êtes-vous sûr de vouloir supprimer la donnée &quot;<?php echo addslashes($d['domaine'] . ' - ' . $d['competence']); ?>&quot; ?')"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($listeDonneesDb)): ?>
                    <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--text-secondary);">Aucune donnée pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>


<!-- ============================================== -->
<!-- MODALS -->
<!-- ============================================== -->

<!-- 1. Modal Rapport -->
<div class="modal-overlay" id="modal-rapport">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModals()"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
        
        <h3 id="rapport-modal-title" style="margin-bottom:24px;">Nouveau Rapport</h3>
        <form action="veille_admin.php" method="POST" id="form-rapport">
            <input type="hidden" name="action" id="rapport-action" value="add_rapport">
            <input type="hidden" name="id_rapport_marche" id="rapport-id" value="">
            <input type="hidden" name="date_publication" id="rapport-date" value="">

            <div class="form-group mb-4">
                <label class="form-label">Titre</label>
                <input type="text" class="input <?php echo isset($fieldErrors['titre']) ? 'is-invalid' : ''; ?>" name="titre" id="rapport-titre">
                <?php if(isset($fieldErrors['titre'])): ?><span class="error-text"><?php echo $fieldErrors['titre']; ?></span><?php endif; ?>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Description Globale (Optionnel)</label>
                <textarea class="textarea <?php echo isset($fieldErrors['description']) ? 'is-invalid' : ''; ?>" name="description" id="rapport-desc" rows="4"></textarea>
                <?php if(isset($fieldErrors['description'])): ?><span class="error-text"><?php echo $fieldErrors['description']; ?></span><?php endif; ?>
            </div>

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Région (Optionnel)</label>
                    <select class="select" name="region" id="rapport-region">
                        <option value="">Sélectionner une région...</option>
                        <option value="Tunis">Tunis</option>
                        <option value="Ariana">Ariana</option>
                        <option value="Ben Arous">Ben Arous</option>
                        <option value="Manouba">Manouba</option>
                        <option value="Nabeul">Nabeul</option>
                        <option value="Zaghouan">Zaghouan</option>
                        <option value="Bizerte">Bizerte</option>
                        <option value="Béja">Béja</option>
                        <option value="Jendouba">Jendouba</option>
                        <option value="Le Kef">Le Kef</option>
                        <option value="Siliana">Siliana</option>
                        <option value="Sousse">Sousse</option>
                        <option value="Monastir">Monastir</option>
                        <option value="Mahdia">Mahdia</option>
                        <option value="Sfax">Sfax</option>
                        <option value="Kairouan">Kairouan</option>
                        <option value="Kasserine">Kasserine</option>
                        <option value="Sidi Bouzid">Sidi Bouzid</option>
                        <option value="Gabès">Gabès</option>
                        <option value="Médenine">Médenine</option>
                        <option value="Tataouine">Tataouine</option>
                        <option value="Gafsa">Gafsa</option>
                        <option value="Tozeur">Tozeur</option>
                        <option value="Kebili">Kebili</option>
                        <option value="International">International</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Secteur Principal (Optionnel)</label><input type="text" class="input" name="secteur_principal" id="rapport-secteur"></div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Image de Couverture (Optionnel)</label>
                <div class="image-upload-zone" id="rapport-upload-zone" onclick="document.getElementById('rapport-image-input').click()">
                    <div class="image-upload-zone__icon">
                        <i data-lucide="image-plus" style="width:100%; height:100%;"></i>
                    </div>
                    <div class="image-upload-zone__text">
                        <strong>Cliquez pour télécharger</strong> ou glissez-déposez
                    </div>
                    <div class="image-upload-zone__hint">
                        PNG, JPG ou WEBP (Max. 5 Mo)
                    </div>
                    
                    <input type="file" id="rapport-image-input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    <input type="hidden" name="image_couverture" id="rapport-image-base64">
                    
                    <img id="rapport-image-preview" src="" class="image-upload-preview">
                    
                    <div class="image-upload-overlay" id="rapport-upload-overlay">
                        <i data-lucide="refresh-cw" style="width:24px;height:24px;"></i>
                        <span style="font-size:13px; font-weight:600;">Changer l'image</span>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Corps du Rapport (Contenu Riche)</label>
                <div id="editor-container" style="height: 300px;" class="<?php echo isset($fieldErrors['contenu_detaille']) ? 'is-invalid' : ''; ?>"></div>
                <input type="hidden" name="contenu_detaille" id="rapport-contenu">
                <?php if(isset($fieldErrors['contenu_detaille'])): ?><span class="error-text"><?php echo $fieldErrors['contenu_detaille']; ?></span><?php endif; ?>
                <input type="hidden" name="vues" id="rapport-vues-field">
            </div>

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Sal. Min. Global</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['salaire_min_global']) ? 'is-invalid' : ''; ?>" name="salaire_min_global" id="rapport-smin" placeholder="ex: 1200">
                    <?php if(isset($fieldErrors['salaire_min_global'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_min_global']; ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Sal. Max. Global</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['salaire_max_global']) ? 'is-invalid' : ''; ?>" name="salaire_max_global" id="rapport-smax" placeholder="ex: 3500">
                    <?php if(isset($fieldErrors['salaire_max_global'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_max_global']; ?></span><?php endif; ?>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Sal. Moyen Global (Optionnel) <span style='font-size:11px;color:var(--text-secondary);'>(auto-calculé)</span></label>
                <input type="text" class="input <?php echo isset($fieldErrors['salaire_moyen_global']) ? 'is-invalid' : ''; ?>" name="salaire_moyen_global" id="rapport-smoy" placeholder="Rempli auto si min+max">
                <?php if(isset($fieldErrors['salaire_moyen_global'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_moyen_global']; ?></span><?php endif; ?>
            </div>

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group"><label class="form-label">Tendance (Optionnel)</label><select class="select" name="tendance_generale" id="rapport-tendance"><option value="Hausse">En hausse</option><option value="Stable">Stable</option><option value="Baisse">En baisse</option></select></div>
                <div class="form-group"><label class="form-label">Demande (Optionnel)</label><select class="select" name="niveau_demande_global" id="rapport-demande"><option value="4">Très forte</option><option value="3">Forte</option><option value="2">Modérée</option><option value="1">Faible</option></select></div>
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">Auteur</label>
                <input type="text" class="input <?php echo isset($fieldErrors['auteur']) ? 'is-invalid' : ''; ?>" name="auteur" id="rapport-auteur">
                <?php if(isset($fieldErrors['auteur'])): ?><span class="error-text"><?php echo $fieldErrors['auteur']; ?></span><?php endif; ?>
            </div>

            <!-- Liaison Section inside Modal -->
            <hr style="margin:24px 0; border:none; border-top:1px solid var(--border-color);">
            <h4 style="margin-bottom:16px; font-size:16px;">Associer des données à ce rapport</h4>
            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:16px;">Sélectionnez toutes les données brutes existantes que vous souhaitez associer à ce rapport. <strong>Au moins une est requise.</strong></p>
            <?php if(isset($fieldErrors['linked_donnees'])): ?><span class="error-text mb-2" style="margin-bottom:12px;"><?php echo $fieldErrors['linked_donnees']; ?></span><?php endif; ?>
            
            <div id="donnees-association-list" style="max-height:280px; overflow-y:auto; border:1px solid var(--border-color); border-radius:12px; padding:8px; background:var(--bg-main); display:flex; flex-direction:column; gap:8px;">
                <?php foreach($listeDonneesDb as $d): 
                    $monTableauLiaison = isset($mapLiaisons[$d['id_donnee']]) ? $mapLiaisons[$d['id_donnee']] : [];
                ?>
                    <label class="data-checkbox-card" id="label-assoc-<?php echo $d['id_donnee']; ?>" data-rapport-ids='<?php echo json_encode($monTableauLiaison); ?>'>
                        <input type="checkbox" name="linked_donnees[]" value="<?php echo $d['id_donnee']; ?>" id="chk-assoc-<?php echo $d['id_donnee']; ?>" onchange="this.parentElement.classList.toggle('is-selected', this.checked)">
                        <div>
                            <div style="font-weight:600;font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($d['domaine'] . ' - ' . $d['competence']); ?></div>
                            <div style="font-size:12px;color:var(--text-secondary);">Moy: <?php echo $d['salaire_moyen']; ?> | <?php echo $d['date_collecte']; ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
                <div id="no-data-assoc" style="display:none; color:var(--text-secondary); font-size:14px; text-align:center; padding:12px;">Aucune donnée disponible.</div>
            </div>

            <div style="text-align:right; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn btn-primary" style="margin-left:8px;">Enregistrer le rapport</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Donnée -->
<div class="modal-overlay" id="modal-donnee">
    <div class="modal-content" style="max-width:600px;">
        <button class="modal-close" onclick="closeModals()"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
        
        <h3 id="donnee-modal-title" style="margin-bottom:24px;">Ajouter une donnée</h3>
        <form action="veille_admin.php" method="POST" id="form-donnee">
            <input type="hidden" name="action" id="donnee-action" value="add_donnee">
            <input type="hidden" name="id_donnee" id="donnee-id" value="">
            <input type="hidden" name="id_rapport_marche" id="donnee-id-rapport" value="">

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Domaine</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['domaine']) ? 'is-invalid' : ''; ?>" name="domaine" id="donnee-domaine">
                    <?php if(isset($fieldErrors['domaine'])): ?><span class="error-text"><?php echo $fieldErrors['domaine']; ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Compétence</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['competence']) ? 'is-invalid' : ''; ?>" name="competence" id="donnee-competence">
                    <?php if(isset($fieldErrors['competence'])): ?><span class="error-text"><?php echo $fieldErrors['competence']; ?></span><?php endif; ?>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Description / Contexte de la donnée</label>
                <textarea class="textarea" name="description_donnee" id="donnee-desc" rows="3" placeholder="Source, spécificités, etc."></textarea>
            </div>

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Salaire Min</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['salaire_min']) ? 'is-invalid' : ''; ?>" name="salaire_min" id="donnee-smin" placeholder="ex: 800">
                    <?php if(isset($fieldErrors['salaire_min'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_min']; ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Salaire Max</label>
                    <input type="text" class="input <?php echo isset($fieldErrors['salaire_max']) ? 'is-invalid' : ''; ?>" name="salaire_max" id="donnee-smax" placeholder="ex: 2500">
                    <?php if(isset($fieldErrors['salaire_max'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_max']; ?></span><?php endif; ?>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Salaire Moyen <span style='font-size:11px;color:var(--text-secondary);'>(auto-calculé)</span></label>
                <input type="text" class="input <?php echo isset($fieldErrors['salaire_moyen']) ? 'is-invalid' : ''; ?>" name="salaire_moyen" id="donnee-smoy" placeholder="Rempli auto si min+max">
                <?php if(isset($fieldErrors['salaire_moyen'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_moyen']; ?></span><?php endif; ?>
            </div>

            <div class="grid grid-2 gap-4 mb-4" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group"><label class="form-label">Demande Nv.</label><select class="select" name="demande" id="donnee-demande"><option value="4">Très forte</option><option value="3">Forte</option><option value="2">Modérée</option><option value="1">Faible</option></select></div>
                <div class="form-group"><label class="form-label">Date Collecte</label><input type="date" class="input" name="date_collecte" id="donnee-date"></div>
            </div>

            <div style="text-align:right; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn btn-primary" style="margin-left:8px;">Sauvegarder la donnée</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Confirmation Suppression -->
<div class="modal-overlay" id="modal-delete">
    <div class="modal-content" style="max-width:450px; text-align:center; padding: 40px 32px;">
        <button class="modal-close" onclick="closeModals()"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
        
        <div style="width:64px; height:64px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <i data-lucide="alert-triangle" style="width:32px;height:32px;"></i>
        </div>
        
        <h3 style="margin-bottom:12px; color:var(--text-primary);">Confirmation de suppression</h3>
        <p id="delete-modal-msg" style="color:var(--text-secondary); margin-bottom:24px; line-height:1.6;">Êtes-vous sûr de vouloir continuer ? Cette action est irréversible.</p>
        
        <form action="veille_admin.php" method="POST" id="form-delete">
            <input type="hidden" name="action" id="delete-action" value="">
            <input type="hidden" name="" id="delete-id-field" value="">
            
            <div style="display:flex; gap:12px; justify-content:center;">
                <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:1; background:#ef4444; border-color:#ef4444; color:white;">Oui, Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    document.querySelector(`button[onclick="switchTab('${tabId}')"]`).classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function closeModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
}

function openRapportModal(type, data = null) {
    const isEdit = (type === 'edit');
    document.getElementById('rapport-modal-title').innerText = isEdit ? 'Modifier le Rapport' : 'Nouveau Rapport';
    document.getElementById('rapport-action').value = isEdit ? 'update_rapport' : 'add_rapport';
    
    // Fill basic fields
    document.getElementById('rapport-id').value = data?.id_rapport_marche || '';
    document.getElementById('rapport-date').value = data?.date_publication || '';
    document.getElementById('rapport-titre').value = data?.titre || '';
    document.getElementById('rapport-desc').value = data?.description || '';
    document.getElementById('rapport-region').value = data?.region || '';
    document.getElementById('rapport-secteur').value = data?.secteur_principal || '';
    document.getElementById('rapport-smin').value = data?.salaire_min_global || '';
    document.getElementById('rapport-smoy').value = data?.salaire_moyen_global || '';
    document.getElementById('rapport-smax').value = data?.salaire_max_global || '';
    document.getElementById('rapport-tendance').value = data?.tendance_generale || 'Hausse';
    document.getElementById('rapport-demande').value = data?.niveau_demande_global || '3';
    document.getElementById('rapport-auteur').value = data?.auteur || '';
    document.getElementById('rapport-vues-field').value = data?.vues || 0;

    // Rich Text & Image handling
    const uploadZone = document.getElementById('rapport-upload-zone');
    if (isEdit) {
        quill.root.innerHTML = data?.contenu_detaille || '';
        if (data?.image_couverture) {
            document.getElementById('rapport-image-preview').src = data.image_couverture;
            document.getElementById('rapport-image-preview').style.display = 'block';
            document.getElementById('rapport-image-base64').value = data.image_couverture;
            if (uploadZone) uploadZone.classList.add('has-image');
        } else {
            document.getElementById('rapport-image-preview').style.display = 'none';
            if (uploadZone) uploadZone.classList.remove('has-image');
        }
    } else {
        quill.root.innerHTML = '';
        document.getElementById('rapport-image-preview').style.display = 'none';
        document.getElementById('rapport-image-base64').value = '';
        if (uploadZone) uploadZone.classList.remove('has-image');
    }

    // Handle Association Checkboxes (M2M)
    let countVis = 0;
    document.querySelectorAll('.data-checkbox-card').forEach(lbl => {
        let chk = lbl.querySelector('input[type="checkbox"]');
        chk.checked = false; 
        
        if (isEdit) {
            let idsStr = lbl.getAttribute('data-rapport-ids');
            if(idsStr) {
                let ids = JSON.parse(idsStr);
                if (ids.includes(parseInt(data.id_rapport_marche)) || ids.includes(data.id_rapport_marche)) {
                    chk.checked = true;
                    lbl.classList.add('is-selected');
                }
            }
        }
        
        lbl.style.display = 'flex';
        countVis++;
    });

    document.getElementById('no-data-assoc').style.display = countVis === 0 ? 'block' : 'none';

    document.getElementById('modal-rapport').classList.add('active');
}

function openDonneeModal(type, data = null) {
    const isEdit = (type === 'edit');
    document.getElementById('donnee-modal-title').innerText = isEdit ? 'Modifier la donnée' : 'Ajouter une donnée';
    document.getElementById('donnee-action').value = isEdit ? 'update_donnee' : 'add_donnee';
    
    document.getElementById('donnee-id').value = data?.id_donnee || '';
    document.getElementById('donnee-domaine').value = data?.domaine || '';
    document.getElementById('donnee-competence').value = data?.competence || '';
    document.getElementById('donnee-smin').value = data?.salaire_min || '';
    document.getElementById('donnee-smoy').value = data?.salaire_moyen || '';
    document.getElementById('donnee-smax').value = data?.salaire_max || '';
    document.getElementById('donnee-demande').value = data?.demande || '3';
    document.getElementById('donnee-date').value = data?.date_collecte || '';
    document.getElementById('donnee-desc').value = data?.description || '';
    
    document.getElementById('modal-donnee').classList.add('active');
}

// Preview image and convert to base64
function previewImage(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 5 * 1024 * 1024) {
            alert("L'image est trop lourde (max 5 Mo)");
            input.value = "";
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('rapport-image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('rapport-image-base64').value = e.target.result;
            document.getElementById('rapport-upload-zone').classList.add('has-image');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Initializing Quill
var quill;
document.addEventListener('DOMContentLoaded', function() {
    quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Rédigez le contenu détaillé du rapport ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    document.getElementById('form-rapport').onsubmit = function() {
        document.getElementById('rapport-contenu').value = quill.root.innerHTML;
    };

    // Drag and Drop Logic
    const dropZone = document.getElementById('rapport-upload-zone');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('rapport-image-input');
            input.files = files;
            previewImage(input);
        }, false);
    }
});

function openDeleteModal(actionName, idFieldName, idValue, message) {
    document.getElementById('delete-action').value = actionName;
    const idField = document.getElementById('delete-id-field');
    idField.name = idFieldName;
    idField.value = idValue;
    
    document.getElementById('delete-modal-msg').innerText = message;
    
    document.getElementById('modal-delete').classList.add('active');
}
</script>

<?php if (!empty($fieldErrors)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($activeTab === 'rapports'): ?>
            openRapportModal('<?php echo strpos($action, 'update') !== false ? 'edit' : 'add'; ?>', <?php echo json_encode($_POST); ?>);
        <?php else: ?>
            openDonneeModal('<?php echo strpos($action, 'update') !== false ? 'edit' : 'add'; ?>', <?php echo json_encode($_POST); ?>);
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<?php 
// Add a helper script to re-lucide icons after any DOM changes if needed, 
// though here they are rendered server-side so they should be fine on load.
?>
