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

<!-- ECharts for Maps -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>

<!-- NoUiSlider for Interactive Range Inputs -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>

<style>
*, *::before, *::after { box-sizing: border-box; }

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
.input.is-invalid, .textarea.is-invalid, .tag-input-container.is-invalid-tags { border-color: #ef4444 !important; background-color: rgba(239, 68, 68, 0.05) !important; }
/* Sector Filter Buttons */
.sector-filter-btn {
    padding: 5px 14px; font-size: 12px; font-weight: 600; border-radius: 20px;
    border: 1px solid var(--border-color); background: var(--bg-main);
    color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease;
    white-space: nowrap;
}
.sector-filter-btn:hover { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(99,102,241,0.05); }
.sector-filter-btn.active { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); box-shadow: 0 2px 8px rgba(99,102,241,0.3); }
/* Quill Overrides */
.ql-editor { min-height: 200px; color: var(--text-primary); font-size: 15px; }
.ql-toolbar { background: #f8fafc; border-top-left-radius: 8px; border-top-right-radius: 8px; }
.ql-container { border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; background: var(--input-bg); }
[data-theme="dark"] .ql-toolbar { background: #1e293b; border-color: var(--border-color); }
[data-theme="dark"] .ql-container { border-color: var(--border-color); }
[data-theme="dark"] .ql-stroke { stroke: #cbd5e1; }

/* Stepper Styles */
.stepper-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; position: relative; }
.stepper-header::before { content: ''; position: absolute; top: 16px; left: 10%; right: 10%; height: 2px; background: var(--border-color); z-index: 1; transition: background 0.3s; }
.stepper-step { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; }
.step-circle { width: 34px; height: 34px; border-radius: 50%; background: var(--bg-main); border: 2px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; color: var(--text-secondary); transition: all 0.3s; }
.stepper-step.active .step-circle { background: var(--accent-primary); border-color: var(--accent-primary); color: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
.stepper-step.completed .step-circle { background: var(--accent-primary); border-color: var(--accent-primary); color: #fff; }
.step-label { font-size: 12px; font-weight: 600; color: var(--text-secondary); text-align: center; }
.stepper-step.active .step-label { color: var(--accent-primary); }
.step-content { display: none; animation: fadeIn 0.3s ease; }
.step-content.active { display: block; }
.stepper-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid var(--border-color); }

/* Stepper Layout */
#modal-rapport .modal-content { width: 95vw; max-width: 1100px !important; display:flex; flex-direction:column; max-height: 95vh; padding: 0; overflow:hidden;}
#modal-rapport .modal-close { position:absolute; top:24px; right:24px; z-index:100; }
#rapport-modal-title { padding: 24px 32px 0; margin-bottom: 24px; }
#rapport-stepper-header { padding: 0 32px; margin-bottom: 24px; }
#form-rapport { display: flex; flex-direction: column; overflow: hidden; flex: 1; border-top: 1px solid var(--border-color); padding-top: 24px;}
.stepper-layout { display: flex; gap: 32px; flex: 1; overflow-y: auto; padding: 0 32px 32px; }
.stepper-left-panel { flex: 1.3; min-width: 0; }
.stepper-right-panel { flex: 1; min-width: 0; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color); padding: 16px; display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }

/* Responsive Overrides for Opera GX / Narrow Windows */
@media (max-width: 1024px) {
    .stepper-layout { flex-direction: column; }
    .stepper-left-panel, .stepper-right-panel { flex: none; width: 100%; }
    .stepper-right-panel { min-height: 400px; }
    #modal-rapport .modal-content { max-height: 98vh; width: 98vw; }
}

.stepper-footer { display: flex; justify-content: space-between; align-items: center; padding: 16px 32px; border-top: 1px solid var(--border-color); background: var(--bg-main); z-index: 10; margin-top: 0; }

.viz-panel { display: none; width: 100%; height: 100%; animation: fadeIn 0.4s ease; flex-direction: column; flex: 1; position: relative; justify-content: flex-start; }
.viz-panel.active { display: flex; }

/* Custom Gauges/Visuals */
.viz-title { font-size: 13px; font-weight: 700; color: var(--text-secondary); text-align: center; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; padding-bottom:8px; border-bottom: 1px solid var(--border-color); }

/* Article View for Step 2 */
.article-preview { width:100%; flex:1; overflow-y:auto; border-radius:8px; }
.article-preview-title { font-size:22px; font-weight:700; color:var(--text-primary); margin-bottom:12px; line-height:1.3; }
.article-preview-meta { display:flex; gap:16px; font-size:12px; color:var(--text-secondary); margin-bottom:20px; }
.article-preview-img { width:100%; height:180px; object-fit:cover; border-radius:8px; margin-bottom:20px; display:none; background:#e2e8f0; }
[data-theme="dark"] .article-preview-img { background: #334155; }
.article-preview-content { color:var(--text-primary); font-size:14px; line-height:1.6; padding-bottom:20px; }

/* Demand Blocks */
.demand-blocks { display: flex; gap: 6px; margin-top: 8px; }
.demand-block { flex: 1; height: 36px; background: #e2e8f0; border-radius: 6px; cursor: pointer; transition: all 0.2s; position:relative; overflow:hidden;}
[data-theme="dark"] .demand-block { background: #334155; }
.demand-block:hover { background: #cbd5e1; }
[data-theme="dark"] .demand-block:hover { background: #475569; }
.demand-block.active { background: var(--accent-primary); box-shadow: 0 0 12px rgba(99,102,241,0.3); }

/* NoUiSlider Overrides */
.noUi-target { background: #e2e8f0; border: none; box-shadow: none; height: 8px; }
[data-theme="dark"] .noUi-target { background: #334155; }
.noUi-connect { background: var(--accent-primary); }
.noUi-handle { border: 2px solid var(--accent-primary); border-radius: 50%; background: #ffffff; cursor: grab; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 22px !important; height: 22px !important; right:-11px !important; top:-7px !important;}
.noUi-handle::after, .noUi-handle::before { display: none; }
.noUi-tooltip { font-size: 11px; font-weight: 600; padding: 4px 8px; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-main); border-radius: 6px; }

/* Custom Summary Box Level 4 */
.summary-counter { width: 80px; height: 80px; border-radius: 50%; border: 4px solid #e2e8f0; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:var(--text-secondary); margin: 0 auto 24px; transition:all 0.3s;}
.summary-counter.valid { border-color: #10b981; color: #10b981; }

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

/* Tag Input Styles */
.tag-input-wrapper { position: relative; width: 100%; margin-top: 8px; }
.tag-input-container {
    display: flex; flex-wrap: wrap; gap: 8px; padding: 8px 12px;
    background: var(--input-bg); border: 1px solid var(--border-color);
    border-radius: var(--radius-md); transition: border-color 0.3s;
    min-height: 48px; align-items: center; cursor: text;
}
.tag-input-container:focus-within { border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.tag-input-container input {
    border: none; background: transparent; outline: none;
    color: var(--text-primary); font-size: 14px; flex: 1; min-width: 120px;
    padding: 4px 0;
}
.tag-chip {
    display: flex; align-items: center; gap: 6px; padding: 4px 10px;
    background: rgba(99, 102, 241, 0.1); color: var(--accent-primary);
    border-radius: 20px; font-size: 12px; font-weight: 600;
    animation: fadeInTag 0.2s ease;
}
@keyframes fadeInTag { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.tag-chip .remove-tag {
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; width: 14px; height: 14px;
    border-radius: 50%; opacity: 0.6; transition: opacity 0.2s;
    font-size: 16px; line-height: 1;
}
.tag-chip .remove-tag:hover { opacity: 1; background: rgba(99, 102, 241, 0.2); }

.tag-suggestions {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    max-height: 200px; overflow-y: auto; background: var(--bg-main);
    border: 1px solid var(--border-color); border-radius: var(--radius-md);
    z-index: 1000; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none;
    backdrop-filter: blur(10px);
}
.tag-suggestion-item {
    padding: 10px 16px; cursor: pointer; font-size: 14px;
    color: var(--text-secondary); transition: all 0.2s;
}
.tag-suggestion-item:hover {
    background: rgba(99, 102, 241, 0.05); color: var(--accent-primary);
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

    <!-- Sector Filter Bar -->
    <div id="sector-filter-bar" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; align-items:center;">
        <span style="font-size:13px; font-weight:600; color:var(--text-secondary); margin-right:4px;">Filtrer :</span>
        <button class="sector-filter-btn active" data-sector="all" onclick="filterBySector('all', this)">
            Tous
        </button>
        <?php 
        $allSecteurs = [];
        foreach ($listeRapportsDb as $p) {
            if (!empty($p['secteur_principal'])) {
                foreach (explode(',', $p['secteur_principal']) as $s) {
                    $s = trim($s);
                    if ($s && !in_array($s, $allSecteurs)) $allSecteurs[] = $s;
                }
            }
        }
        foreach ($allSecteurs as $sec): ?>
        <button class="sector-filter-btn" data-sector="<?php echo htmlspecialchars($sec); ?>" onclick="filterBySector('<?php echo htmlspecialchars(addslashes($sec)); ?>', this)">
            <?php echo htmlspecialchars($sec); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="published-list" style="margin-top:0;" id="rapports-list">
      <?php foreach ($listeRapportsDb as $p): 
        $secteursData = htmlspecialchars($p['secteur_principal'] ?? '');
      ?>
      <div class="published-item rapport-item" data-secteurs="<?php echo $secteursData; ?>" style="display:flex; justify-content:space-between; align-items:center; padding:16px;">
        <div>
          <div class="published-item__title" style="font-size:16px; font-weight:600; margin-bottom:4px;"><?php echo htmlspecialchars($p['titre']); ?></div>
          <div class="published-item__meta" style="color:var(--text-secondary); font-size:13px;">
            <span><i data-lucide="calendar" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> <?php echo date('d M', strtotime($p['date_publication'])); ?></span>
            <span style="margin-left:12px;"><i data-lucide="link" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> <?php echo $p['nombre_donnees']; ?> données liées</span>
            <span class="badge badge-sm" style="margin-left:12px; background:rgba(99,102,241,0.1); color:var(--accent-primary);"><i data-lucide="eye" style="width:10px;height:10px;display:inline;"></i> <?php echo $p['vues']; ?> vues</span>
            <?php if (!empty($p['secteur_principal'])): 
              foreach(explode(',', $p['secteur_principal']) as $tag): $tag = trim($tag); if (!$tag) continue; ?>
              <span class="badge badge-sm" style="margin-left:6px; background:rgba(99,102,241,0.08); color:var(--accent-primary); border:1px solid rgba(99,102,241,0.2);"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; endif; ?>
          </div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-sm btn-ghost" onclick='openRapportModal("edit", <?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8"); ?>)'><i data-lucide="edit" style="width:16px;height:16px;"></i></button>
            <button type="button" class="btn btn-sm btn-ghost text-danger" style="color:#ef4444;" onclick="openDeleteModal('delete_rapport', 'id_rapport_marche', <?php echo $p['id_rapport_marche']; ?>, 'Êtes-vous sûr de vouloir supprimer le rapport &quot;<?php echo addslashes($p['titre']); ?>&quot; ?')"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></button>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

    <!-- AI Forecast Dashboard Section -->
    <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; margin-top: 32px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;">
            <div>
                <h3 style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="line-chart" style="color: var(--accent-primary);"></i>
                    🔮 Intelligent Market Forecast
                </h3>
                <p style="color: var(--text-secondary); font-size: 14px; max-width: 600px;">
                    Notre IA analyse vos rapports historiques par secteur pour prédire les tendances futures des salaires et de la demande sur les 6 prochains mois.
                </p>
                <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                    <label for="forecast-category" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Secteur :</label>
                    <select id="forecast-category" class="select" style="width: 250px; padding: 6px 12px; font-size: 13px;" onchange="loadAIForecast(false)">
                        <option value="">Tous les secteurs</option>
                        <?php foreach ($allSecteurs as $sec): ?>
                            <option value="<?php echo htmlspecialchars($sec); ?>"><?php echo htmlspecialchars($sec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-ai-sparkle" onclick="loadAIForecast(true)" id="btn-refresh-forecast">
                <i data-lucide="refresh-cw" style="width:14px;height:14px;margin-right:6px;"></i> Update Predictions
            </button>
        </div>

        <div id="forecast-chart-container" style="width: 100%; height: 300px; margin-top: 24px; background: rgba(255,255,255,0.02); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <div id="forecast-placeholder" style="text-align: center; color: var(--text-tertiary);">
                <i data-lucide="sparkles" style="width:48px; height:48px; margin-bottom: 12px; opacity: 0.3;"></i>
                <p>Sélectionnez un secteur ou cliquez sur "Update Predictions".</p>
            </div>
            <div id="echarts-forecast" style="width: 100%; height: 100%; display: none;"></div>
        </div>
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
                            <button class="btn btn-sm btn-ghost" onclick='openDonneeModal("edit", <?php echo htmlspecialchars(json_encode($d), ENT_QUOTES, "UTF-8"); ?>)'><i data-lucide="edit" style="width:14px;height:14px;"></i></button>
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
        
        <!-- Stepper Header -->
        <div class="stepper-header" id="rapport-stepper-header">
            <div class="stepper-step active" data-step-indicator="1">
                <div class="step-circle">1</div>
                <div class="step-label">Identité</div>
            </div>
            <div class="stepper-step" data-step-indicator="2">
                <div class="step-circle">2</div>
                <div class="step-label">Contenu</div>
            </div>
            <div class="stepper-step" data-step-indicator="3">
                <div class="step-circle">3</div>
                <div class="step-label">Analyse</div>
            </div>
            <div class="stepper-step" data-step-indicator="4">
                <div class="step-circle">4</div>
                <div class="step-label">Liaison</div>
            </div>
        </div>

        <form action="veille_admin.php" method="POST" id="form-rapport" novalidate onsubmit="return syncQuill();">
            <input type="hidden" name="action" id="rapport-action" value="add_rapport">
            <input type="hidden" name="id_rapport_marche" id="rapport-id" value="">
            <input type="hidden" name="date_publication" id="rapport-date" value="">

            <div class="stepper-layout">
                <div class="stepper-left-panel">

                    <!-- STEP 1: Basic Information -->
                    <div class="step-content active" id="step-rapport-1">
                <div class="form-group mb-4">
                    <label class="form-label">Titre <span class="text-danger" style="color:#ef4444;">*</span></label>
                    <input type="text" class="input <?php echo isset($fieldErrors['titre']) ? 'is-invalid' : ''; ?>" name="titre" id="rapport-titre" required>
                    <?php if(isset($fieldErrors['titre'])): ?><span class="error-text"><?php echo $fieldErrors['titre']; ?></span><?php endif; ?>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Auteur <span class="text-danger" style="color:#ef4444;">*</span></label>
                    <input type="text" class="input <?php echo isset($fieldErrors['auteur']) ? 'is-invalid' : ''; ?>" name="auteur" id="rapport-auteur" required>
                    <?php if(isset($fieldErrors['auteur'])): ?><span class="error-text"><?php echo $fieldErrors['auteur']; ?></span><?php endif; ?>
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
                    <div class="form-group">
                        <label class="form-label">Secteur Principal <span class="text-danger" style="color:#ef4444;">*</span></label>
                        <div class="tag-input-wrapper" id="secteur-tag-wrapper">
                            <div class="tag-input-container" id="tag-container">
                                <!-- Tags will be injected here -->
                                <input type="text" id="tag-input" placeholder="Ajouter un secteur..." autocomplete="off">
                            </div>
                            <div class="tag-suggestions" id="tag-suggestions"></div>
                        </div>
                        <input type="hidden" name="secteur_principal" id="rapport-secteur" required>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Content & Visuals -->
            <div class="step-content" id="step-rapport-2">
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
                    <label class="form-label">Description Globale (Optionnel)</label>
                    <textarea class="textarea <?php echo isset($fieldErrors['description']) ? 'is-invalid' : ''; ?>" name="description" id="rapport-desc" rows="2"></textarea>
                    <?php if(isset($fieldErrors['description'])): ?><span class="error-text"><?php echo $fieldErrors['description']; ?></span><?php endif; ?>
                </div>

                <div class="form-group mb-4">
                    <div style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--accent-primary); font-size: 14px;"><i data-lucide="sparkles" style="width:14px;height:14px;display:inline;"></i> Génération Assistée par IA</strong>
                            <p style="font-size: 12px; color: var(--text-secondary); margin: 4px 0 0 0;">Générez automatiquement une analyse de marché approfondie et en français basée sur vos critères.</p>
                        </div>
                        <button type="button" class="btn btn-ai-sparkle" onclick="generateAIDraft()" id="btn-ai-draft">
                            Générer le rapport IA
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="margin-bottom: 0;">Corps du Rapport <span class="text-danger" style="color:#ef4444;">*</span></label>
                    </div>
                    <div id="editor-container" style="height: 300px;" class="<?php echo isset($fieldErrors['contenu_detaille']) ? 'is-invalid' : ''; ?>"></div>
                    <input type="hidden" name="contenu_detaille" id="rapport-contenu">
                    <?php if(isset($fieldErrors['contenu_detaille'])): ?><span class="error-text"><?php echo $fieldErrors['contenu_detaille']; ?></span><?php endif; ?>
                    <input type="hidden" name="vues" id="rapport-vues-field">
                </div>
            </div>

            <!-- STEP 3: Market Indicators -->
            <div class="step-content" id="step-rapport-3">
                <p style="color:var(--text-secondary); font-size:14px; margin-bottom:24px; padding:12px; background:var(--bg-main); border-radius:8px; border:1px solid var(--border-color);">
                    <i data-lucide="info" style="width:16px;height:16px;display:inline;vertical-align:-3px;color:var(--accent-primary);"></i>
                    Utilisez le panneau interactif à droite pour configurer l'<strong>Échelle Salariale</strong> et le <strong>Niveau de Demande</strong>.
                </p>
                
                <!-- Hidden inputs synced with right panel sliders -->
                <input type="hidden" name="salaire_min_global" id="rapport-smin" value="1000">
                <input type="hidden" name="salaire_max_global" id="rapport-smax" value="4000">
                <input type="hidden" name="niveau_demande_global" id="rapport-demande" value="3">
                
                <?php if(isset($fieldErrors['salaire_min_global'])): ?><div class="error-text mb-2"><?php echo $fieldErrors['salaire_min_global']; ?></div><?php endif; ?>
                <?php if(isset($fieldErrors['salaire_max_global'])): ?><div class="error-text mb-2"><?php echo $fieldErrors['salaire_max_global']; ?></div><?php endif; ?>

                <div class="form-group mb-4 mt-4">
                    <label class="form-label">Sal. Moyen Global (Optionnel) <span style='font-size:11px;color:var(--text-secondary);'>(calculé auto)</span></label>
                    <input type="number" class="input <?php echo isset($fieldErrors['salaire_moyen_global']) ? 'is-invalid' : ''; ?>" name="salaire_moyen_global" id="rapport-smoy" placeholder="Rempli auto depuis l'échelle">
                    <?php if(isset($fieldErrors['salaire_moyen_global'])): ?><span class="error-text"><?php echo $fieldErrors['salaire_moyen_global']; ?></span><?php endif; ?>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Tendance Globale (Optionnel)</label>
                    <select class="select" name="tendance_generale" id="rapport-tendance">
                        <option value="Hausse">En hausse</option>
                        <option value="Stable">Stable</option>
                        <option value="Baisse">En baisse</option>
                    </select>
                </div>
            </div>

            <!-- STEP 4: Association -->
            <div class="step-content" id="step-rapport-4">
                <h4 style="margin-bottom:16px; font-size:16px;">Associer des données à ce rapport <span class="text-danger" style="color:#ef4444;">*</span></h4>
                <p style="color:var(--text-secondary); font-size:13px; margin-bottom:16px;">Sélectionnez toutes les données brutes existantes que vous souhaitez associer à ce rapport. <strong>Au moins une est requise.</strong></p>
                <div id="assoc-error" class="error-text" style="display:none; margin-bottom:12px;">Vous devez sélectionner au moins une donnée.</div>
                <?php if(isset($fieldErrors['linked_donnees'])): ?><div class="error-text mb-2" style="margin-bottom:12px;"><?php echo $fieldErrors['linked_donnees']; ?></div><?php endif; ?>
                
                <div id="donnees-association-list" style="max-height:280px; overflow-y:auto; border:1px solid var(--border-color); border-radius:12px; padding:8px; background:var(--bg-main); display:flex; flex-direction:column; gap:8px;">
                    <?php foreach($listeDonneesDb as $d): 
                        $monTableauLiaison = isset($mapLiaisons[$d['id_donnee']]) ? $mapLiaisons[$d['id_donnee']] : [];
                    ?>
                        <label class="data-checkbox-card" id="label-assoc-<?php echo $d['id_donnee']; ?>" data-rapport-ids='<?php echo htmlspecialchars(json_encode($monTableauLiaison), ENT_QUOTES, "UTF-8"); ?>'>
                            <input type="checkbox" name="linked_donnees[]" value="<?php echo $d['id_donnee']; ?>" id="chk-assoc-<?php echo $d['id_donnee']; ?>" onchange="validateDataSelection(this)">
                            <div>
                                <div style="font-weight:600;font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($d['domaine'] . ' - ' . $d['competence']); ?></div>
                                <div style="font-size:12px;color:var(--text-secondary);">Moy: <?php echo $d['salaire_moyen']; ?> | <?php echo $d['date_collecte']; ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                    <div id="no-data-assoc" style="display:none; color:var(--text-secondary); font-size:14px; text-align:center; padding:12px;">Aucune donnée disponible.</div>
                </div>
                    </div>

                </div> <!-- End Left Panel -->
                
                <div class="stepper-right-panel custom-scrollbar">
                    <div id="viz-step-1" class="viz-panel active" style="align-items:center; padding: 4px 0;">
                        <div class="viz-title">Couverture Géographique</div>
                        <!-- Map container — aspect ratio controlled by CSS -->
                        <div id="map-wrapper" style="width:100%; position:relative; display:flex; align-items:center; justify-content:center;">
                            <div id="echarts-map" style="width:100%; height:200px; border-radius:8px; overflow:hidden;"></div>
                        </div>
                        <div id="intl-country-container" style="display:none; width:100%; margin-top:8px;">
                            <label class="form-label" style="text-align:left; font-size:12px; margin-bottom:4px;">Sélectionner un pays <span class="text-danger">*</span></label>
                            <select class="select" id="rapport-pays" name="pays">
                                <option value="">Choisir un pays...</option>
                                <option value="France">France</option>
                                <option value="Canada">Canada</option>
                                <option value="États-Unis">États-Unis</option>
                                <option value="Royaume-Uni">Royaume-Uni</option>
                                <option value="Allemagne">Allemagne</option>
                                <option value="Italie">Italie</option>
                                <option value="Espagne">Espagne</option>
                                <option value="Belgique">Belgique</option>
                                <option value="Suisse">Suisse</option>
                                <option value="Maroc">Maroc</option>
                                <option value="Algérie">Algérie</option>
                                <option value="Égypte">Égypte</option>
                                <option value="Arabie Saoudite">Arabie Saoudite</option>
                                <option value="Émirats Arabes Unis">Émirats Arabes Unis</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Chine">Chine</option>
                                <option value="Japon">Japon</option>
                                <option value="Brésil">Brésil</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="viz-step-2" class="viz-panel">
                        <div class="viz-title">Aperçu de l'Article</div>
                        <div class="article-preview custom-scrollbar">
                            <h1 class="article-preview-title" id="live-article-title">Titre du rapport...</h1>
                            <div class="article-preview-meta">
                                <span><i data-lucide="user" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> <span id="live-article-author">Auteur</span></span>
                                <span><i data-lucide="calendar" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> <?php echo date('d/m/Y'); ?></span>
                            </div>
                            <img id="live-article-img" class="article-preview-img" src="" alt="">
                            <div id="live-article-content" class="article-preview-content">
                                <p style="color:var(--text-secondary); font-style:italic;">Commencez à rédiger le corps du rapport pour voir l'aperçu ici...</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="viz-step-3" class="viz-panel" style="justify-content:center;">
                        <div class="viz-title" style="border:none; padding-bottom:0;">Indicateurs de Marché</div>
                        
                        <div style="width:90%; margin:0 auto; text-align:left;">
                            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:16px;">
                                <h4 style="font-size:14px; font-weight:700; color:var(--text-primary); margin:0;">Échelle Salariale</h4>
                                <span style="font-size:13px; font-weight:600; color:var(--accent-primary);" id="slider-salary-live">0 - 0 TND</span>
                            </div>
                            <!-- NoUiSlider container -->
                            <div id="salary-slider" style="margin-bottom: 48px; margin-top:24px;"></div>
                            
                            <h4 style="font-size:14px; font-weight:700; color:var(--text-primary); margin-top:24px;">Niveau de Demande</h4>
                            <!-- Clickable Demand Blocks -->
                            <div class="demand-blocks" id="interactive-demand-blocks">
                                <div class="demand-block" data-val="1"></div>
                                <div class="demand-block" data-val="2"></div>
                                <div class="demand-block" data-val="3"></div>
                                <div class="demand-block" data-val="4"></div>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:6px; color:var(--text-secondary);">
                                <span>Faible</span>
                                <span>Très Forte</span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="viz-step-4" class="viz-panel" style="justify-content:center; align-items:center;">
                        <div class="viz-title" style="position:static; border:none;">Validation de Publication</div>
                        <div class="summary-counter" id="final-summary-counter">
                            0
                        </div>
                        <div style="font-size:15px; color:var(--text-secondary); text-align:center;">
                            Données sources liées.<br>
                            <span style="font-size:13px; margin-top:8px; display:inline-block;">Une source minimum est requise.</span>
                        </div>
                    </div>
                </div> <!-- End right panel -->
            </div> <!-- End stepper-layout -->

            <!-- Stepper Footer -->
            <div class="stepper-footer">
                <button type="button" class="btn btn-secondary" id="btn-prev-step" onclick="prevStep()" style="display:none;">Précédent</button>
                <div style="flex:1;"></div>
                <button type="button" class="btn btn-primary" id="btn-next-step" onclick="nextStep()">Suivant</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-rapport" style="display:none;">Enregistrer le rapport</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Donnée -->
<div class="modal-overlay" id="modal-donnee">
    <div class="modal-content" style="max-width:600px;">
        <button class="modal-close" onclick="closeModals()"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
        
        <h3 id="donnee-modal-title" style="margin-bottom:24px;">Ajouter une donnée</h3>

        <!-- AI Scout Section -->
        <div style="background: rgba(99, 102, 241, 0.05); border: 1px dashed var(--accent-primary); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
            <label class="form-label" style="color: var(--accent-primary); font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="search" style="width:16px;height:16px;"></i> AI Scout & Research
            </label>
            <div style="display: flex; gap: 8px; margin-top: 8px;">
                <textarea id="ai-scout-query" class="textarea" placeholder="Décrivez ce que vous cherchez et/ou collez des URLs (ex: Salaire dev React Tunis https://...)" style="flex: 1;" rows="2"></textarea>
                <button type="button" class="btn btn-primary" onclick="scoutMarketData()" id="btn-scout" style="align-self: center;">
                    <i data-lucide="zap" style="width:16px;height:16px;margin-right:4px;"></i> Scout
                </button>
            </div>
            <p style="font-size: 11px; color: var(--text-secondary); margin-top: 8px;">
                <i data-lucide="info" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> L'IA peut chercher à partir d'une description simple ou explorer jusqu'à 3 liens fournis pour extraire précisément les informations du marché.
            </p>
        </div>

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
        lbl.classList.remove('is-selected');
        
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

    // Reset Stepper
    let firstErrorElement = document.querySelector('#form-rapport .is-invalid');
    if(firstErrorElement) {
        let stepParent = firstErrorElement.closest('.step-content');
        if(stepParent) {
            goToStep(parseInt(stepParent.id.replace('step-rapport-', '')));
        } else {
            goToStep(1);
        }
    } else {
        goToStep(1);
    }

    document.getElementById('modal-rapport').classList.add('active');
}

// Stepper Logic
let currentRapportStep = 1;
const totalRapportSteps = 4;

function goToStep(step) {
    currentRapportStep = step;
    
    // Update contents
    document.querySelectorAll('.step-content').forEach((el, index) => {
        el.classList.toggle('active', index + 1 === step);
    });
    
    // Update headers
    document.querySelectorAll('.stepper-step').forEach((el, index) => {
        const s = index + 1;
        el.classList.toggle('active', s === step);
        el.classList.toggle('completed', s < step);
    });
    
    // Update viz panels
    document.querySelectorAll('.viz-panel').forEach((el, index) => {
        el.classList.toggle('active', index + 1 === step);
    });

    if(step === 2 && typeof updateLivePreview === 'function') updateLivePreview();
    if(step === 3 && typeof updateSalaryGauges === 'function') updateSalaryGauges();
    if(step === 4 && typeof updateDataNodes === 'function') updateDataNodes();
    
    // Buttons
    document.getElementById('btn-prev-step').style.display = step > 1 ? 'block' : 'none';
    if(step === totalRapportSteps) {
        document.getElementById('btn-next-step').style.display = 'none';
        document.getElementById('btn-submit-rapport').style.display = 'block';
    } else {
        document.getElementById('btn-next-step').style.display = 'block';
        document.getElementById('btn-submit-rapport').style.display = 'none';
    }
}

function nextStep() {
    if(validateStep(currentRapportStep)) {
        goToStep(currentRapportStep + 1);
    }
}

function prevStep() {
    if(currentRapportStep > 1) {
        goToStep(currentRapportStep - 1);
    }
}

function validateStep(step) {
    let isValid = true;
    
    // Clear previous invalid UI
    document.querySelectorAll(`#step-rapport-${step} .input, #step-rapport-${step} .textarea`).forEach(el => {
        el.classList.remove('is-invalid');
    });

    if (step === 1) {
        const titre = document.getElementById('rapport-titre');
        const auteur = document.getElementById('rapport-auteur');
        const secteur = document.getElementById('rapport-secteur');
        const tagContainer = document.getElementById('tag-container');

        if(!titre.value.trim()) { titre.classList.add('is-invalid'); isValid = false; }
        if(!auteur.value.trim()) { auteur.classList.add('is-invalid'); isValid = false; }
        
        if(!secteur.value.trim()) { 
            tagContainer.classList.add('is-invalid-tags');
            isValid = false; 
        } else {
            tagContainer.classList.remove('is-invalid-tags');
        }
    } else if (step === 2) {
        const contenu = quill.root.innerHTML.trim();
        if(contenu === '<p><br></p>' || contenu === '') {
            document.getElementById('editor-container').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('editor-container').classList.remove('is-invalid');
        }
    } else if (step === 3) {
        const smin = document.getElementById('rapport-smin');
        const smax = document.getElementById('rapport-smax');
        if(!smin.value || isNaN(smin.value)) { smin.classList.add('is-invalid'); isValid = false; }
        if(!smax.value || isNaN(smax.value)) { smax.classList.add('is-invalid'); isValid = false; }
        if(isValid && Number(smin.value) > Number(smax.value)) {
            smax.classList.add('is-invalid'); 
            isValid = false;
        }
    }
    
    return isValid;
}

function validateDataSelection(checkbox) {
    checkbox.parentElement.classList.toggle('is-selected', checkbox.checked);
    document.getElementById('assoc-error').style.display = 'none';
}

function syncQuill() {
    document.getElementById('rapport-contenu').value = quill.root.innerHTML;
    // Step 4 final server-side pre-validation
    const checked = document.querySelectorAll('input[name="linked_donnees[]"]:checked').length;
    if (checked === 0) {
        document.getElementById('assoc-error').style.display = 'block';
        return false;
    }
    return true;
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
            if (typeof updateLivePreview === 'function') updateLivePreview();
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
    // Remove old inline onsubmit to use syncQuill() in form HTML
    // document.getElementById('form-rapport').onsubmit = function() ...

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
    
    // ==========================================
    // GEOGRAPHIC VISUALIZATION (V5 — High Fidelity)
    // Southern Tunisia Focus, Geo-Region Highlighting & Expanded Global Catalogue
    // ==========================================

    const regionSelect = document.getElementById('rapport-region');
    const intlContainer = document.getElementById('intl-country-container');
    const countrySelect = document.getElementById('rapport-pays');
    let mapChart = null;
    let tunisiaLoaded = false;
    let worldLoaded = false;
    let currentMapView = 'Tunisia'; 

    // ── Tunisia Region Centers & Zooms ──
    const TUNISIA_REGIONS_VIEW = {
        'Tunis':       { center: [10.25, 36.82], zoom: 4.5 },
        'Ariana':      { center: [10.12, 36.94], zoom: 4.5 },
        'Ben Arous':   { center: [10.26, 36.68], zoom: 4.2 },
        'Manouba':     { center: [9.91, 36.79],  zoom: 4.5 },
        'Nabeul':      { center: [10.75, 36.65], zoom: 3.2 },
        'Zaghouan':    { center: [10.14, 36.31], zoom: 3.8 },
        'Bizerte':     { center: [9.38, 37.15],  zoom: 3.5 },
        'Béja':        { center: [9.27, 36.73],  zoom: 3.8 },
        'Jendouba':    { center: [8.65, 36.68],  zoom: 3.8 },
        'Le Kef':      { center: [8.75, 36.06],  zoom: 3.8 },
        'Siliana':     { center: [9.36, 35.94],  zoom: 3.5 },
        'Sousse':      { center: [10.45, 35.84], zoom: 4.5 },
        'Monastir':    { center: [10.82, 35.63], zoom: 4.5 },
        'Mahdia':      { center: [10.75, 35.25], zoom: 3.5 },
        'Sfax':        { center: [10.42, 34.78], zoom: 2.8 },
        'Kairouan':    { center: [9.85, 35.69],  zoom: 3.0 },
        'Kasserine':   { center: [8.85, 35.30],  zoom: 3.0 },
        'Sidi Bouzid': { center: [9.52, 34.93],  zoom: 3.2 },
        'Gabès':       { center: [10.00, 33.79], zoom: 3.5 },
        'Médenine':    { center: [10.95, 33.15], zoom: 2.5 },
        'Tataouine':   { center: [10.55, 32.30], zoom: 1.8 },
        'Gafsa':       { center: [8.85, 34.34],  zoom: 3.2 },
        'Tozeur':      { center: [8.18, 33.95],  zoom: 3.8 },
        'Kebili':      { center: [9.05, 33.35],  zoom: 2.5 }
    };

    // ── Global Market Catalogue (Dropdown Val -> GeoJSON Name) ──
    const WORLD_NAME_MAP = {
        'France': 'France',
        'Canada': 'Canada',
        'États-Unis': 'United States',
        'Royaume-Uni': 'United Kingdom',
        'Allemagne': 'Germany',
        'Italie': 'Italy',
        'Espagne': 'Spain',
        'Belgique': 'Belgium',
        'Suisse': 'Switzerland',
        'Maroc': 'Morocco',
        'Algérie': 'Algeria',
        'Égypte': 'Egypt',
        'Arabie Saoudite': 'Saudi Arabia',
        'Émirats Arabes Unis': 'United Arab Emirates',
        'Qatar': 'Qatar',
        'Chine': 'China',
        'Japon': 'Japan',
        'Brésil': 'Brazil'
    };

    // ── Cinematic Camera Points [Center, Zoom] ──
    const COUNTRY_VIEW = {
        'France':       { center: [2.2, 46.2],     zoom: 5 },
        'Canada':       { center: [-96.8, 56.1],   zoom: 2.5 },
        'États-Unis':   { center: [-98.5, 39.5],   zoom: 3 },
        'Royaume-Uni':  { center: [-3.4, 55.4],    zoom: 5 },
        'Allemagne':    { center: [10.5, 51.2],    zoom: 5.5 },
        'Italie':       { center: [12.5, 41.8],    zoom: 5.5 },
        'Espagne':      { center: [-3.7, 40.4],    zoom: 5.5 },
        'Belgique':     { center: [4.4, 50.5],     zoom: 12 },
        'Suisse':       { center: [8.2, 46.8],     zoom: 10 },
        'Maroc':        { center: [-7.0, 31.7],    zoom: 4.5 },
        'Algérie':      { center: [3.0, 28.0],     zoom: 2.5 },
        'Égypte':       { center: [30.8, 26.8],    zoom: 3.5 },
        'Arabie Saoudite': { center: [45.0, 23.8],  zoom: 2.5 },
        'Émirats Arabes Unis': { center: [54.0, 24.0], zoom: 8 },
        'Qatar':        { center: [51.2, 25.3],    zoom: 15 },
        'Chine':        { center: [104.2, 35.9],   zoom: 2.5 },
        'Japon':        { center: [138.0, 36.5],   zoom: 4.5 },
        'Brésil':       { center: [-51.9, -14.2],  zoom: 2.5 }
    };

    function getOrInitChart() {
        if (!mapChart) {
            mapChart = echarts.init(document.getElementById('echarts-map'), null, { renderer: 'svg' });
        }
        return mapChart;
    }

    // Preload GeoJSONs
    fetch('../../assets/js/tunisia.json').then(r => r.json()).then(data => {
        echarts.registerMap('Tunisia', data);
        tunisiaLoaded = true;
        if (document.getElementById('viz-step-1').classList.contains('active')) {
            renderTunisiaMap(regionSelect.value || null);
        }
    });

    fetch('../../assets/js/world.json').then(r => r.json()).then(data => {
        echarts.registerMap('World', data);
        worldLoaded = true;
    });

    // ── RENDER: Tunisia Map (V5) ─────────────────────────────────────────────
    function renderTunisiaMap(regionSelected, customZoom = null) {
        if (!tunisiaLoaded) return;
        const chart = getOrInitChart();
        const isSwitching = currentMapView !== 'Tunisia';
        currentMapView = 'Tunisia';

        const hl = regionSelected || '';
        // FIX: Shifted center further south [34.5 instead of 35.5] to show the bottom tip
        const view = TUNISIA_REGIONS_VIEW[hl] || { center: [9.4, 34.2], zoom: 0.95 };
        const targetZoom = customZoom !== null ? customZoom : (hl ? view.zoom : 0.95);

        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const baseFill   = isDark ? '#1e293b' : '#e8edf5';
        const baseBorder = isDark ? '#334155' : '#b8c4d4';

        const option = {
            animation: true,
            animationDurationUpdate: 1200,
            animationEasing: 'cubicInOut',
            backgroundColor: 'transparent',
            series: [{
                type: 'map',
                map: 'Tunisia',
                roam: false,
                aspectScale: 0.72,
                center: view.center,
                zoom: targetZoom,
                label: { 
                    show: false,
                    color: '#fff',
                    textBorderColor: isDark ? '#1e293b' : '#334155',
                    textBorderWidth: 2
                },
                emphasis: {
                    label: { show: true },
                    itemStyle: { areaColor: '#818cf8', borderColor: '#4f46e5' }
                },
                itemStyle: { areaColor: baseFill, borderColor: baseBorder, borderWidth: 0.8 },
                data: hl ? [{
                    name: hl,
                    itemStyle: { areaColor: '#6366f1', borderColor: '#a5b4fc', borderWidth: 2, shadowBlur: 10, shadowColor: 'rgba(99,102,241,0.4)' },
                    label: { show: true, fontSize: 11, fontWeight: 700 }
                }] : []
            }]
        };

        // Use true for map switches to clear previous state, false for intra-map pans
        chart.setOption(option, isSwitching);
    }

    // ── RENDER: World Map (V5) ───────────────────────────────────────────────
    function renderWorldMap(countryVal) {
        if (!worldLoaded) return;
        const chart = getOrInitChart();
        const isSwitching = currentMapView !== 'World';
        currentMapView = 'World';

        const echName = WORLD_NAME_MAP[countryVal] || null;
        const view = echName ? (COUNTRY_VIEW[countryVal] || { center: [10, 20], zoom: 1.1 }) : { center: [10, 20], zoom: 1.1 };
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        const option = {
            animation: true,
            animationDurationUpdate: 1200,
            animationEasing: 'cubicInOut',
            backgroundColor: 'transparent',
            geo: {
                map: 'World',
                roam: false,
                center: view.center,
                zoom: view.zoom,
                label: { show: false },
                itemStyle: {
                    areaColor: isDark ? '#1e293b' : '#dde4ef',
                    borderColor: isDark ? '#334155' : '#b8c4d4',
                    borderWidth: 0.5
                },
                // Highlighting logic moved to Regions for Geo component reliability
                regions: echName ? [{
                    name: echName,
                    itemStyle: {
                        areaColor: '#6366f1',
                        borderColor: '#a5b4fc',
                        shadowBlur: 15,
                        shadowColor: 'rgba(99,102,241,0.5)'
                    },
                    label: { show: true, color: '#fff', fontWeight: 700, textBorderColor: '#4f46e5', textBorderWidth: 2 }
                }] : []
            },
            series: [] // No extra series needed when using geo.regions
        };

        chart.setOption(option, isSwitching);
    }

    // ── Event Handlers ───────────────────────────────────────────────────────
    regionSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        if (val === 'International') {
            intlContainer.style.display = 'block';
            if (currentMapView === 'Tunisia') {
                renderTunisiaMap(null, 0.3); // Zoom-out first
                setTimeout(() => renderWorldMap(countrySelect.value || null), 500);
            } else {
                renderWorldMap(countrySelect.value || null);
            }
        } else {
            intlContainer.style.display = 'none';
            countrySelect.value = '';
            renderTunisiaMap(val || null);
        }
    });

    countrySelect.addEventListener('change', (e) => {
        renderWorldMap(e.target.value || null);
    });

    window.addEventListener('resize', () => { if (mapChart) mapChart.resize(); });

    // 2. Step 2: Article Live Preview
    window.updateLivePreview = function() {
        const titleInput = document.getElementById('rapport-titre').value;
        const authorInput = document.getElementById('rapport-auteur').value;
        
        document.getElementById('live-article-title').innerText = titleInput || 'Titre du rapport...';
        document.getElementById('live-article-author').innerText = authorInput || 'Auteur';
        
        // Update content live from quill
        if (typeof quill !== 'undefined') {
            const content = quill.root.innerHTML;
            document.getElementById('live-article-content').innerHTML = content && content !== '<p><br></p>' ? content : '<p style="color:var(--text-secondary); font-style:italic;">Commencez à rédiger le corps du rapport pour voir l\'aperçu ici...</p>';
        }

        const base64 = document.getElementById('rapport-image-base64').value;
        const imgEl = document.getElementById('live-article-img');
        if (base64) {
            imgEl.src = base64;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
    };
    
    document.getElementById('rapport-titre').addEventListener('input', () => { if(currentRapportStep === 2) updateLivePreview(); });
    document.getElementById('rapport-auteur').addEventListener('input', () => { if(currentRapportStep === 2) updateLivePreview(); });
    if(typeof quill !== 'undefined') quill.on('text-change', function() { if(currentRapportStep === 2) updateLivePreview(); });

    // 3. Step 3: NoUiSlider & Interactive Demand Blocks
    const salarySlider = document.getElementById('salary-slider');
    const inputSmin = document.getElementById('rapport-smin');
    const inputSmax = document.getElementById('rapport-smax');
    const inputSmoy = document.getElementById('rapport-smoy');
    const liveSalaryText = document.getElementById('slider-salary-live');
    
    if (salarySlider && typeof noUiSlider !== 'undefined') {
        noUiSlider.create(salarySlider, {
            start: [parseInt(inputSmin.value) || 1200, parseInt(inputSmax.value) || 3500],
            connect: true,
            step: 50,
            range: { 'min': 500, 'max': 10000 },
            tooltips: [true, true],
            format: {
                to: function (value) { return Math.round(value); },
                from: function (value) { return Number(value); }
            }
        });

        salarySlider.noUiSlider.on('update', function (values, handle) {
            inputSmin.value = values[0];
            inputSmax.value = values[1];
            inputSmoy.value = Math.round((parseInt(values[0]) + parseInt(values[1])) / 2);
            liveSalaryText.innerText = `${values[0]} - ${values[1]} TND`;
        });
    }

    // Interactive Demand Blocks
    const demandBlocks = document.querySelectorAll('.demand-block');
    const inputDemande = document.getElementById('rapport-demande');

    function updateDemandBlocks(val) {
        demandBlocks.forEach(block => {
            if(parseInt(block.dataset.val) <= val) {
                block.classList.add('active');
            } else {
                block.classList.remove('active');
            }
        });
        inputDemande.value = val;
    }

    demandBlocks.forEach(block => {
        block.addEventListener('click', () => {
            updateDemandBlocks(parseInt(block.dataset.val));
        });
    });

    // Initialize Demand blocks default
    updateDemandBlocks(parseInt(inputDemande.value) || 3);
    
    // Override openRapportModal to trigger updates properly on modal open!
    const originalOpenRapport = window.openRapportModal;
    window.openRapportModal = function(type, data) {
        if (typeof originalOpenRapport === 'function') originalOpenRapport(type, data);
        setTimeout(() => {
            // Force a resize after modal expansion (standard transition is ~300ms)
            // Using 350ms to be safe for ECharts container calculation
            if (mapChart) {
                mapChart.resize();
            }
            
            // Render the correct map based on current region value
            const regionVal = document.getElementById('rapport-region').value;
            if (regionVal === 'International') {
                intlContainer.style.display = 'block';
                const countryVal = document.getElementById('rapport-pays').value;
                renderWorldMap(countryVal || null);
            } else {
                intlContainer.style.display = 'none';
                renderTunisiaMap(regionVal || null);
            }

            updateLivePreview();
            
            // Sync slider to hidden inputs loaded data
            if(salarySlider && salarySlider.noUiSlider) {
                salarySlider.noUiSlider.set([
                    parseInt(document.getElementById('rapport-smin').value) || 1000, 
                    parseInt(document.getElementById('rapport-smax').value) || 4000
                ]);
            }
            updateDemandBlocks(parseInt(document.getElementById('rapport-demande').value) || 3);
            updateDataNodes();

            // Populate tags from data
            const rawSecteurs = document.getElementById('rapport-secteur').value;
            tags = rawSecteurs ? rawSecteurs.split(',').filter(s => s.trim() !== '') : [];
            updateTagsDisplay();
        }, 350);
    };

    // --- HYBRID TAG SYSTEM LOGIC ---
    const SECTORS_LIST = [
        'Technologie', 'Informatique', 'Finance', 'Banque', 'Assurance', 'Santé', 
        'Pharmacie', 'Industrie', 'Énergie', 'Agriculture', 'Tourisme', 'Hôtellerie',
        'Commerce', 'Retail', 'Éducation', 'Formation', 'Services Publics', 
        'Marketing', 'Communication', 'Logistique', 'Transport', 'Immobilier',
        'Construction', 'Juridique', 'Ressources Humaines', 'E-commerce'
    ];

    const tagContainerEl = document.getElementById('tag-container');
    const tagInputEl = document.getElementById('tag-input');
    const tagSuggestionsEl = document.getElementById('tag-suggestions');
    const hiddenSecteurInput = document.getElementById('rapport-secteur');
    let tags = [];

    function updateTagsDisplay() {
        tagContainerEl.querySelectorAll('.tag-chip').forEach(el => el.remove());
        tags.forEach(tag => {
            const chip = document.createElement('div');
            chip.className = 'tag-chip';
            chip.innerHTML = `${tag} <span class="remove-tag">&times;</span>`;
            chip.querySelector('.remove-tag').onclick = () => removeTag(tag);
            tagContainerEl.insertBefore(chip, tagInputEl);
        });
        hiddenSecteurInput.value = tags.join(',');
    }

    window.addTag = function(tag) {
        tag = tag.trim();
        if (tag && !tags.includes(tag)) {
            tags.push(tag);
            updateTagsDisplay();
        }
        tagInputEl.value = '';
        tagSuggestionsEl.style.display = 'none';
        tagInputEl.focus();
    };

    window.removeTag = function(tag) {
        tags = tags.filter(t => t !== tag);
        updateTagsDisplay();
    };

    tagInputEl.addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase().trim();
        if (!val) {
            tagSuggestionsEl.style.display = 'none';
            return;
        }
        const matches = SECTORS_LIST.filter(s => s.toLowerCase().includes(val) && !tags.includes(s));
        if (matches.length > 0) {
            tagSuggestionsEl.innerHTML = matches.map(m => `<div class="tag-suggestion-item" onclick="addTag('${m}')">${m}</div>`).join('');
            tagSuggestionsEl.style.display = 'block';
        } else {
            tagSuggestionsEl.style.display = 'none';
        }
    });

    tagInputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(tagInputEl.value);
        }
        if (e.key === 'Backspace' && !tagInputEl.value && tags.length > 0) {
            removeTag(tags[tags.length - 1]);
        }
    });

    tagContainerEl.addEventListener('click', () => tagInputEl.focus());

    document.addEventListener('click', (e) => {
        if (!document.getElementById('secteur-tag-wrapper').contains(e.target)) {
            tagSuggestionsEl.style.display = 'none';
        }
    });


    // 4. Step 4: Simple Connection Counter
    window.updateDataNodes = function() {
        const checked = document.querySelectorAll('input[name="linked_donnees[]"]:checked').length;
        const counter = document.getElementById('final-summary-counter');
        counter.innerText = checked;
        
        if (checked > 0) {
            counter.classList.add('valid');
        } else {
            counter.classList.remove('valid');
        }
    };
    
    document.querySelectorAll('input[name="linked_donnees[]"]').forEach(chk => {
        chk.addEventListener('change', () => updateDataNodes());
    });
});

// --- AI ASSISTANT HANDLERS ---
async function generateAIDraft() {
    const btn = document.getElementById('btn-ai-draft');
    const metadata = {
        titre: document.getElementById('rapport-titre').value,
        auteur: document.getElementById('rapport-auteur').value,
        region: document.getElementById('rapport-region').value,
        secteur: document.getElementById('rapport-secteur').value,
        salaire: document.getElementById('rapport-smoy').value,
        demande: document.getElementById('rapport-demande').value,
        tendance: document.getElementById('rapport-tendance').value
    };

    if (!metadata.titre || !metadata.secteur) {
        alert("Veuillez remplir au moins le titre et le secteur pour générer un brouillon.");
        return;
    }

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width:14px;height:14px;margin-right:6px;"></i> Generating...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch('api_veille_ai.php?action=generate_draft', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(metadata)
        });
        const result = await response.json();
        if (result.success) {
            quill.root.innerHTML = result.draft;
            updateLivePreview();
        } else {
            alert("Erreur IA: " + result.error);
        }
    } catch (e) {
        console.error(e);
        alert("Impossible de contacter l'assistant IA.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

async function scoutMarketData() {
    const queryInput = document.getElementById('ai-scout-query');
    const btn = document.getElementById('btn-scout');
    const query = queryInput.value.trim();

    if (!query) {
        alert("Veuillez entrer une description ou des URLs.");
        return;
    }

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width:16px;height:16px;"></i>';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch('api_veille_ai.php?action=scout_data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        });
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            document.getElementById('donnee-domaine').value = data.domaine || '';
            document.getElementById('donnee-competence').value = data.competence || '';
            document.getElementById('donnee-smin').value = data.salaire_min || '';
            document.getElementById('donnee-smax').value = data.salaire_max || '';
            document.getElementById('donnee-smoy').value = data.salaire_moyen || '';
            document.getElementById('donnee-desc').value = data.source_summary || '';
            
            if (data.demande) {
                if (data.demande === 'Très forte' || data.demande === '4') document.getElementById('donnee-demande').value = '4';
                else if (data.demande === 'Forte' || data.demande === '3') document.getElementById('donnee-demande').value = '3';
                else if (data.demande === 'Modérée' || data.demande === '2') document.getElementById('donnee-demande').value = '2';
                else document.getElementById('donnee-demande').value = '1';
            }

            queryInput.value = '';
            alert("Données du marché générées et extraites avec succès !");
        } else {
            alert("Scout Error: " + (result.error || "Impossible d'extraire les données."));
        }
    } catch (e) {
        console.error(e);
        alert("Erreur de connexion avec le service AI Scout.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

let forecastChart = null;
let forecastCache = {}; // Object to store forecasts by category

async function loadAIForecast(forceRefresh = false) {
    const btn = document.getElementById('btn-refresh-forecast');
    const placeholder = document.getElementById('forecast-placeholder');
    const chartEl = document.getElementById('echarts-forecast');
    const categorySelect = document.getElementById('forecast-category');
    
    // Default to empty string if element not found, but it should exist
    const selectedCategory = categorySelect ? categorySelect.value : ''; 
    const cacheKey = selectedCategory || 'all';

    // If we have cached data and are NOT forcing a refresh, just render it
    if (!forceRefresh && forecastCache[cacheKey]) {
        renderForecastChart(forecastCache[cacheKey], chartEl, placeholder);
        return;
    }

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width:14px;height:14px;margin-right:6px;"></i> Predicting...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch(`api_veille_ai.php?action=get_forecast&secteur=${encodeURIComponent(selectedCategory)}`);
        const result = await response.json();

        if (result.success && result.forecast) {
            // Save to cache
            forecastCache[cacheKey] = result.forecast;
            renderForecastChart(result.forecast, chartEl, placeholder);
        } else {
            alert("Erreur de prédiction: " + (result.error || "Réponse invalide de l'API."));
        }
    } catch (e) {
        console.error(e);
        alert("Erreur lors de la génération des prévisions.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

function renderForecastChart(forecastData, chartEl, placeholder) {
    placeholder.style.display = 'none';
    chartEl.style.display = 'block';
    
    if (!forecastChart) {
        forecastChart = echarts.init(chartEl);
    }

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const months = forecastData.map(f => f.month);
    const salaries = forecastData.map(f => f.predicted_salary);
    const demands = forecastData.map(f => f.predicted_demand);

    const option = {
        tooltip: { trigger: 'axis' },
        legend: { data: ['Predicted Salary (TND)', 'Predicted Demand (1-10)'], bottom: 0, textStyle: { color: isDark ? '#fff' : '#333' } },
        grid: { top: 40, left: 50, right: 50, bottom: 60 },
        xAxis: { type: 'category', data: months, axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
        yAxis: [
            { type: 'value', name: 'Salary', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
            { type: 'value', name: 'Demand', max: 10, position: 'right', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } }
        ],
        series: [
            {
                name: 'Predicted Salary (TND)',
                type: 'line',
                smooth: true,
                data: salaries,
                itemStyle: { color: '#6366f1' },
                areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(99, 102, 241, 0.3)' }, { offset: 1, color: 'transparent' }]) }
            },
            {
                name: 'Predicted Demand (1-10)',
                type: 'bar',
                yAxisIndex: 1,
                data: demands,
                itemStyle: { color: '#a855f7', opacity: 0.6 }
            }
        ]
    };
    forecastChart.setOption(option);
    window.addEventListener('resize', () => forecastChart.resize());
}

function filterBySector(sector, btnEl) {
    // Toggle active state on filter buttons
    document.querySelectorAll('.sector-filter-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const items = document.querySelectorAll('.rapport-item');
    let visibleCount = 0;
    items.forEach(item => {
        if (sector === 'all') {
            item.style.display = '';
            visibleCount++;
        } else {
            const secteurs = item.dataset.secteurs || '';
            const tags = secteurs.split(',').map(s => s.trim().toLowerCase());
            if (tags.includes(sector.toLowerCase())) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        }
    });

    // Show empty state if no results
    const list = document.getElementById('rapports-list');
    let emptyState = document.getElementById('filter-empty-state');
    if (visibleCount === 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.id = 'filter-empty-state';
            emptyState.style.cssText = 'text-align:center; padding:40px; color:var(--text-secondary); font-size:14px;';
            emptyState.innerHTML = '<i data-lucide="search-x" style="width:32px;height:32px;margin-bottom:12px;opacity:0.4;display:block;margin-left:auto;margin-right:auto;"></i> Aucun rapport pour ce secteur.';
            list.appendChild(emptyState);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        emptyState.style.display = '';
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
}

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
