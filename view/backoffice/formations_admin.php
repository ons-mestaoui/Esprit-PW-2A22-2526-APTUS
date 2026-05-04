<?php
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';

$formationC = new FormationController();

// 🛠️ MVC COMPLIANCE : On délègue toute la logique au Controller
$data = $formationC->getAdminPageData();

$listeFormations = $data['listeFormations'];
$totalFormations = $data['totalFormations'];
$domaines = $data['domaines'];
$tuteurs = $data['tuteurs'];
$tuteursList = $data['tuteursList'];
$stats = $data['stats'];

$calendarEventsJSON = json_encode($data['calendarEvents']);
$tuteurColorsJSON = json_encode($data['tuteurColors']);

$totalInscrits = $stats['total_inscrits'];
$certificats = $stats['certificats'];
$tauxCompletion = $stats['taux_completion'];

// sort($domaines); // SUPPRIMÉ : Déplacé dans FormationController::getAdminFormationsData() pour respecter le MVC

$pageTitle = "Formations";
$pageCSS = "formations.css";
?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>

<div class="back-page-header">
    <div class="back-page-header__row">
        <div>
            <h1>Gestion des Formations</h1>
            <p>Catalogue actuel : <strong>
                    <?php echo $totalFormations; ?>
                </strong> formations publiées</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'has_inscrits'): ?>
            <div
                style="color: var(--accent-tertiary); margin: 0 15px; font-weight: bold; background: var(--accent-tertiary-light); padding: 8px 15px; border-radius: 8px; border: 1px solid var(--accent-tertiary);">
                Erreur: Impossible de supprimer une formation qui a des étudiants inscrits.
            </div>
        <?php endif; ?>


        <button class="btn btn-primary" data-modal="add-formation-modal" id="add-formation-btn">
            <i data-lucide="plus" style="width:18px;height:18px;"></i>
            Ajouter une formation
        </button>
    </div>
</div>

<div class="grid grid-4 gap-6 mb-8 stagger">
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Total Formations</div>
            <div class="stat-card__value">
                <?php echo $totalFormations; ?>
            </div>
        </div>
        <div class="stat-card__icon purple"><i data-lucide="graduation-cap" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Étudiants inscrits</div>
            <div class="stat-card__value">
                <?php echo number_format($totalInscrits); ?>
            </div>
        </div>
        <div class="stat-card__icon teal"><i data-lucide="users" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Certificats délivrés</div>
            <div class="stat-card__value">
                <?php echo $certificats; ?>
            </div>
        </div>
        <div class="stat-card__icon blue"><i data-lucide="award" style="width:22px;height:22px;"></i></div>
    </div>
    <div class="stat-card animate-on-scroll">
        <div>
            <div class="stat-card__label">Taux de complétion</div>
            <div class="stat-card__value"><?php echo $tauxCompletion; ?>%</div>
        </div>
        <div class="stat-card__icon orange"><i data-lucide="target" style="width:22px;height:22px;"></i></div>
    </div>
</div>

<div class="card-flat" style="overflow:hidden;">
    <!-- Tabs pour basculer entre Liste et Planning Global -->
    <div class="view-tabs-container"
        style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-card);">
        <div class="view-tabs">
            <button class="view-tab active" data-view="list" onclick="switchView('list')">
                <i data-lucide="list" style="width:16px;height:16px;"></i>
                Liste
            </button>
            <button class="view-tab" data-view="calendar" onclick="switchView('calendar')">
                <i data-lucide="calendar" style="width:16px;height:16px;"></i>
                Planning Global
            </button>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         VUE 1 : LISTE DU CATALOGUE
         ════════════════════════════════════════════ -->
    <div id="view-list" class="active">
        <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
            <h3 class="text-md fw-semibold">Liste du catalogue</h3>
            <div class="flex items-center gap-3">
                <select id="filter-domaine" class="input"
                    style="padding:0.4rem 0.8rem; border-radius:8px; font-size:0.8rem; width:140px;">
                    <option value="">Tous Domaines</option>
                    <?php foreach ($domaines as $d): ?>
                        <option value="<?php echo htmlspecialchars($d); ?>"><?php echo htmlspecialchars($d); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="filter-niveau" class="input"
                    style="padding:0.4rem 0.8rem; border-radius:8px; font-size:0.8rem; width:130px;">
                    <option value="">Tous Niveaux</option>
                    <option value="Débutant">Débutant</option>
                    <option value="Intermédiaire">Intermédiaire</option>
                    <option value="Avancé">Avancé</option>
                    <option value="Expert">Expert</option>
                </select>

                <div class="search-bar" style="max-width:240px;">
                    <i data-lucide="search" style="width:16px;height:16px;"></i>
                    <input type="text" class="input" placeholder="Rechercher..." id="admin-formation-search">
                </div>

                <button id="reset-filters" class="btn btn-sm btn-ghost"
                    style="display:none; color:var(--accent-tertiary); padding: 0.5rem;" title="Effacer les filtres">
                    <i data-lucide="filter-x" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Formation</th>
                    <th>Domaine</th>
                    <th>Niveau</th>
                    <th>Lieu</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="formations-table-body">
                <?php include 'admin_table_rows_partial.php'; ?>
            </tbody>
        </table>
    </div>

    <!-- ════════════════════════════════════════════
         VUE 2 : PLANNING TUTEURS (CALENDRIER INTERACTIF)
         ════════════════════════════════════════════ -->
    <div id="view-calendar" style="display:none; padding-bottom: 2rem;">
        <div style="display:flex; justify-content: flex-end; margin-bottom: 1rem; padding: 0 1rem;">
            <button onclick="openAddModal()" class="btn btn-primary"
                style="padding:.5rem 1rem;border-radius:8px;font-size:.9rem;font-weight:600;">
                ➕ Placer Indisponibilité / Réunion
            </button>
        </div>
        <!-- Conteneur flex colonne pour que le div du calendrier prenne 100% de la hauteur dispo -->
        <div style="height:700px; padding: 0 1rem; display:flex; flex-direction:column;">
            <div
                style="flex:1; background:var(--bg-card); border-radius:12px; padding:1rem; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); border:1px solid var(--border-color);">
                <div id="calendar" style="height:100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ MODAL : Ajouter/modifier un créneau ════════════ -->
<div id="modal-creneau"
    style="display:none;position:fixed;inset:0;background:var(--bg-overlay, rgba(15, 23, 42, 0.6));backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:1rem;transition: all 0.3s ease;">

    <div
        style="background:var(--bg-card, #ffffff); color:var(--text-primary, #1e293b); border-radius:12px; width:100%; max-width:550px; box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); position:relative; display:flex; flex-direction:column; max-height: 90vh;">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:1.5rem 1.5rem 1rem;">
            <h3 style="margin:0; font-size:1.25rem; font-weight:700; color:var(--text-primary);">Indisponibilité /
                Réunion</h3>
            <button onclick="closeModal()"
                style="background:none; border:none; cursor:pointer; color:var(--text-secondary, #64748b); padding:4px; display:flex; align-items:center; justify-content:center; border-radius:6px; transition:background 0.2s;"
                onmouseover="this.style.background='var(--bg-surface, #f1f5f9)'"
                onmouseout="this.style.background='none'">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        <!-- Body -->
        <div style="padding:0 1.5rem 1.5rem; overflow-y:auto; display:flex; flex-direction:column; gap:1.25rem;">

            <p style="margin:0 0 0.5rem; font-size:0.9rem; color:var(--text-secondary, #64748b);">Configurez un blocage
                horaire ou une réunion interne pour le planning d'un tuteur.</p>
            <input type="hidden" id="creneau-id" value="">

            <!-- Tuteur -->
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.5rem;">
                <label class="form-label" style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Tuteur
                    <span style="color:#ef4444;">*</span></label>
                <div class="input-validated-wrap" style="position:relative;">
                    <select id="creneau-tuteur" class="select iv-field"
                        style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border-color, #cbd5e1); border-radius:8px; background-color:transparent; appearance:auto; outline:none; transition:border-color 0.2s;"
                        data-min="1" data-label="Tuteur">
                        <option value="">Sélectionnez un tuteur...</option>
                        <?php foreach ($tuteursList as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Ajustement ici aussi pour ne pas superposer la flèche du select -->
                    <span class="iv-status"
                        style="position:absolute;right:32px;top:50%;transform:translateY(-50%);display:none;"></span>
                </div>
            </div>

            <!-- Motif -->
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.5rem;">
                <label class="form-label" style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Motif
                    de l'événement</label>
                <div class="input-validated-wrap" style="position:relative;">
                    <input type="text" id="creneau-titre" class="input" placeholder="Ex: Congés, Réunion d'équipe..."
                        style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border-color, #cbd5e1); border-radius:8px; outline:none; background-color:transparent; transition:border-color 0.2s;">
                </div>
            </div>

            <!-- Dates Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group" style="display:flex; flex-direction:column; gap:0.5rem;">
                    <label class="form-label"
                        style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Début <span
                            style="color:#ef4444;">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <input type="datetime-local" id="creneau-debut" class="input iv-field"
                            style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border-color, #cbd5e1); border-radius:8px; outline:none; background-color:transparent; transition:border-color 0.2s;"
                            data-min="1" data-label="Date de début">
                        <!-- FIX: right: 40px pour laisser la place à l'icône native du calendrier -->
                        <span class="iv-status"
                            style="position:absolute;right:40px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                </div>
                <div class="form-group" style="display:flex; flex-direction:column; gap:0.5rem;">
                    <label class="form-label"
                        style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Fin <span
                            style="color:#ef4444;">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <input type="datetime-local" id="creneau-fin" class="input iv-field"
                            style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border-color, #cbd5e1); border-radius:8px; outline:none; background-color:transparent; transition:border-color 0.2s;"
                            data-min="1" data-label="Date de fin">
                        <!-- FIX: right: 40px pour laisser la place à l'icône native du calendrier -->
                        <span class="iv-status"
                            style="position:absolute;right:40px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                </div>
            </div>

            <!-- Options (Couleur et Récurrence) -->
            <div style="display:flex; align-items:center; gap:2rem; margin-top:0.5rem;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <label style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Couleur</label>
                    <div
                        style="position:relative; width:32px; height:32px; border-radius:6px; overflow:hidden; border:1px solid var(--border-color, #cbd5e1); box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                        <input type="color" id="creneau-couleur" value="#8b5cf6"
                            style="position:absolute; top:-10px; left:-10px; width:60px; height:60px; cursor:pointer; border:none; padding:0; background:none;">
                    </div>
                </div>
                <label
                    style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; font-weight:500; cursor:pointer; color:var(--text-primary);">
                    <input type="checkbox" id="creneau-recurrent"
                        style="width:16px; height:16px; accent-color:var(--accent-primary, #4f46e5); cursor:pointer; border-radius:4px;">
                    Événement récurrent
                </label>
            </div>
        </div>

        <!-- Footer -->
        <div
            style="padding:1rem 1.5rem; border-top:1px solid var(--border-color, #f1f5f9); display:flex; justify-content:flex-end; gap:0.75rem; background:var(--bg-surface-light, #f8fafc); border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
            <button onclick="closeModal()" class="btn"
                style="padding:0.625rem 1.25rem; border-radius:8px; background:white; color:var(--text-primary); border:1px solid var(--border-color, #cbd5e1); cursor:pointer; font-weight:600; font-size:0.875rem; transition:all 0.2s;"
                onmouseover="this.style.background='var(--bg-surface, #f1f5f9)';"
                onmouseout="this.style.background='white';">
                Annuler
            </button>
            <button onclick="submitCreneau()" id="btn-save-creneau" class="btn btn-primary"
                style="padding:0.625rem 1.25rem; border-radius:8px; background:linear-gradient(to right, #1a8ed9, #8a2594); color:white; border:none; cursor:pointer; font-weight:600; font-size:0.875rem; display:flex; align-items:center; gap:0.5rem; box-shadow: 0 4px 12px rgba(138, 37, 148, 0.3);">
                Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- ════════════ Modal : Choix action créneau ════════════ -->
<div id="modal-creneau-action"
    style="display:none;position:fixed;inset:0;background:var(--bg-overlay, rgba(15, 23, 42, 0.6));backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:1rem;transition: all 0.3s ease;">

    <div
        style="background:var(--bg-card, #ffffff); color:var(--text-primary, #1e293b); border-radius:12px; width:100%; max-width:400px; box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.1); position:relative; display:flex; flex-direction:column;">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:1.5rem 1.5rem 0.5rem;">
            <div>
                <h3 style="margin:0 0 0.25rem; font-size:1.15rem; font-weight:700; color:var(--text-primary);">Gérer le
                    créneau</h3>
                <p id="creneau-action-desc"
                    style="margin:0; font-size:0.85rem; color:var(--text-secondary, #64748b); line-height:1.4;"></p>
            </div>
            <button onclick="document.getElementById('modal-creneau-action').style.display='none'"
                style="background:none; border:none; cursor:pointer; color:var(--text-secondary, #64748b); padding:4px; margin-top:-4px; margin-right:-4px; border-radius:6px;"
                onmouseover="this.style.background='var(--bg-surface, #f1f5f9)'"
                onmouseout="this.style.background='none'">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        <!-- Body / Actions -->
        <div style="padding:1.5rem; display:flex; flex-direction:column; gap:0.75rem;">
            <button id="btn-creneau-modifier" class="btn btn-primary"
                style="width:100%; padding:0.75rem; border-radius:8px; background:var(--bg-surface, #f8fafc); color:var(--text-primary); border:1px solid var(--border-color, #e2e8f0); cursor:pointer; font-weight:600; font-size:0.9rem; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:background 0.2s;"
                onmouseover="this.style.background='#e2e8f0'"
                onmouseout="this.style.background='var(--bg-surface, #f8fafc)'">
                <i data-lucide="edit-2" style="width:16px; height:16px;"></i>
                Modifier les détails
            </button>

            <button id="btn-creneau-supprimer" class="btn btn-danger"
                style="width:100%; padding:0.75rem; border-radius:8px; background:#fef2f2; color:#ef4444; border:1px solid #fecaca; cursor:pointer; font-weight:600; font-size:0.9rem; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:background 0.2s;"
                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                Supprimer le créneau
            </button>
        </div>
    </div>
</div>

<!-- Modal : Détail formation -->
<div id="modal-formation-detail"
    style="display:none;position:fixed;inset:0;background:var(--bg-overlay);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div
        style="background:var(--bg-card); color:var(--text-primary); border:1px solid rgba(255,255,255,0.1); border-radius:24px; padding:2.5rem; width:100%; max-width:440px; box-shadow:var(--shadow-2xl); position:relative; text-align:center;">
        <div style="margin-bottom:1.5rem;">
            <div
                style="width:48px; height:48px; border-radius:14px; background:var(--accent-secondary-light); display:flex; align-items:center; justify-content:center; color:var(--accent-secondary); margin:0 auto 1rem;">
                <i data-lucide="graduation-cap" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="margin:0; font-size:1.3rem; font-weight:800; letter-spacing:-0.01em;" id="mfd-title">Détail
                Formation</h3>
        </div>
        <div id="mfd-body"
            style="font-size:0.95rem; line-height:1.8; color:var(--text-primary); background:var(--bg-surface); padding:1.25rem; border-radius:16px; border:1px solid var(--border-color); text-align:left; margin-bottom:2rem;">
        </div>
        <div style="display:flex; justify-content:center;">
            <button onclick="document.getElementById('modal-formation-detail').style.display='none'"
                class="btn btn-secondary"
                style="height:44px; padding:0 2rem; border-radius:14px; font-weight:700;">Fermer</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="add-formation-modal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                <h3>Nouvelle Formation</h3>
                <button type="button" id="btn-course-factory" onclick="openCourseFactory()" style="padding:0.4rem 1rem; border-radius:10px; border:none; cursor:pointer; font-size:0.82rem; font-weight:700;
                               background: var(--gradient-primary); color:white;
                               box-shadow:0 4px 12px rgba(107,52,163,0.35); display:flex; align-items:center; gap:0.4rem;
                               transition:transform 0.2s, box-shadow 0.2s;"
                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(107,52,163,0.45)';"
                    onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 12px rgba(107,52,163,0.35)';">
                    ✨ Générer avec Aptus AI
                </button>
            </div>
            <button class="modal-close btn-icon" type="button" onclick="closeModals()"><i data-lucide="x"
                    style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <form action="../../controller/traitement_add.php" method="POST" enctype="multipart/form-data"
                id="add-formation-form" class="auth-form" novalidate>

                <!-- Titre -->
                <div class="form-group">
                    <label class="form-label">Titre de la formation <span class="required-star">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <span class="iv-icon"
                            style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);pointer-events:none;"><i
                                data-lucide="book" style="width:16px;height:16px;"></i></span>
                        <input type="text" class="input iv-field" name="titre" id="af-titre"
                            placeholder="Ex: Masterclass IA" style="padding-left:36px;" data-min="3" data-label="Titre">
                        <span class="iv-status"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="af-titre-msg"
                        style="display:none;font-size:.78rem;color:var(--accent-tertiary);margin-top:4px;display:block;"></span>
                </div>

                <div class="form-group" style="padding-bottom: 25px;">
                    <label class="form-label">Description (Contenu Riche) <span class="required-star">*</span></label>
                    <textarea class="textarea" name="description" id="hidden-description"
                        style="display:none;"></textarea>
                    <div id="quill-editor" style="height: 150px; background: var(--bg-surface);"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <!-- Domaine -->
                    <div class="form-group">
                        <label class="form-label">Domaine <span class="required-star">*</span></label>
                        <div class="input-validated-wrap" style="position:relative;">
                            <input type="text" class="input iv-field" name="domaine" id="af-domaine"
                                placeholder="Ex: Développement Web" data-min="2" data-label="Domaine">
                            <span class="iv-status"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                        </div>
                        <span class="iv-msg" id="af-domaine-msg"
                            style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Niveau <span class="required-star">*</span></label>
                        <select class="select" name="niveau">
                            <option>Débutant</option>
                            <option>Intermédiaire</option>
                            <option>Avancé</option>
                            <option>Expert</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <!-- Dates -->
                    <div class="form-group">
                        <label class="form-label">Date de début <span class="required-star">*</span></label>
                        <div class="input-validated-wrap" style="position:relative;">
                            <input type="date" class="input iv-field" name="date_formation" id="af-date"
                                data-min-date="<?php echo date('Y-m-d'); ?>" data-min="1" data-label="Date de début">
                            <span class="iv-status"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                        </div>
                        <span class="iv-msg" id="af-date-msg"
                            style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date de fin (Optionnel)</label>
                        <div class="input-validated-wrap" style="position:relative;">
                            <input type="date" class="input" name="date_fin" id="af-date-fin">
                        </div>
                        <p style="font-size: 0.7rem; color: #64748b; margin-top: 4px;">Utilisée pour masquer le cours
                            48h après.</p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Durée (ex: 10h)</label>
                        <input type="text" class="input" name="duree">
                    </div>
                    <div class="form-group">
                        <!-- Space for layout -->
                    </div>
                </div>

                <!-- Tuteur -->
                <div class="form-group">
                    <label class="form-label">Tuteur <span class="required-star">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <select class="select iv-field" name="id_tuteur" id="af-tuteur" data-min="1" data-label="Tuteur"
                            style="appearance:auto;">
                            <option value="">Sélectionnez un tuteur...</option>
                            <?php foreach ($tuteurs as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="iv-status"
                            style="position:absolute;right:32px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="af-tuteur-msg"
                        style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Prérequis (Optionnel)</label>
                    <select class="select" name="prerequis_id">
                        <option value="">Aucun prérequis</option>
                        <?php foreach ($listeFormations as $f_pre): ?>
                            <option value="<?php echo $f_pre['id_formation']; ?>">
                                <?php echo htmlspecialchars($f_pre['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Image (16:9)</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Format</label>
                    <select class="select" name="is_online" id="lieu-select">
                        <option value="0">📍 Présentiel</option>
                        <option value="1">🌐 En ligne</option>
                    </select>
                </div>

                <div class="form-group" id="url-field" style="display:none;">
                    <label class="form-label">URL Room (Laissez vide pour Jitsi auto)</label>
                    <input type="url" class="input" name="online_url">
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" onclick="closeModals()">Annuler</button>
            <button class="btn btn-primary" id="btn-create-formation">Créer la formation</button>
        </div>
    </div>
</div>

<style>
    /* ── Sidebar hover ── */
    .tuteur-filter-item:hover {
        background: rgba(99, 102, 241, .07) !important;
    }

    .tuteur-filter-item.active {
        background: rgba(99, 102, 241, .12) !important;
    }

    /* ── FullCalendar overrides ── */
    #calendar .fc-event {
        border-radius: 6px !important;
        font-size: .8rem !important;
        cursor: pointer;
    }

    #calendar .fc-toolbar-title {
        font-size: 1rem !important;
        font-weight: 700;
    }

    #calendar .fc-button {
        border-radius: 8px !important;
        font-size: .8rem !important;
    }

    /* ── Validation champs ── */
    .iv-field.is-valid {
        border-color: var(--accent-secondary) !important;
        background: var(--accent-secondary-light);
    }

    .iv-field.is-invalid {
        border-color: var(--accent-tertiary) !important;
        background: var(--accent-tertiary-light);
    }

    .iv-status.valid {
        color: var(--accent-secondary);
        display: inline-flex !important;
    }

    .iv-status.invalid {
        color: var(--accent-tertiary);
        display: inline-flex !important;
    }

    .iv-msg {
        display: none;
    }

    .iv-msg.show {
        display: block !important;
    }

    /* ── Course Factory Modal ── */
    #modal-course-factory {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .factory-modal-body {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
    }

    .factory-prompt-area {
        width: 100%;
        padding: 0.9rem 1.1rem;
        border-radius: 12px;
        border: 2px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-primary);
        font-size: 0.95rem;
        outline: none;
        resize: vertical;
        min-height: 80px;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .factory-prompt-area:focus {
        border-color: #f59e0b;
    }

    .factory-quick-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.15s;
    }

    .factory-quick-tag:hover {
        border-color: #f59e0b;
        color: #f59e0b;
        background: rgba(245, 158, 11, 0.07);
    }
</style>

<!-- ═══════════════════════════════════════════════════════════
     COURSE FACTORY MODAL
     ═══════════════════════════════════════════════════════════ -->
<div id="modal-course-factory"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); z-index:10000; align-items:center; justify-content:center; padding:1rem;">
    <div class="factory-modal-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <div>
                <div
                    style="font-size:0.7rem; font-weight:800; letter-spacing:0.1em; color:#f59e0b; text-transform:uppercase; margin-bottom:0.25rem;">
                    ✨ Course Factory &mdash; Aptus AI</div>
                <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:var(--text-primary);">Générer une
                    formation en 1 clic</h3>
            </div>
            <button onclick="closeCourseFactory()"
                style="background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:1.5rem;line-height:1;">×</button>
        </div>

        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0 0 1rem; line-height:1.6;">
            Décrivez la formation souhaitée. L'IA va générer et <strong>pré-remplir automatiquement</strong> tous les
            champs du formulaire.
        </p>

        <textarea id="factory-prompt" class="factory-prompt-area"
            placeholder="Ex: Crée une formation intermédiaire sur le Cloud Computing avec 4 modules..."></textarea>

        <div style="margin:0.75rem 0; display:flex; flex-wrap:wrap; gap:0.4rem;">
            <span class="factory-quick-tag"
                onclick="setFactoryPrompt('Formation débutant Python avec 3 modules pratiques')">🐍 Python
                Débutant</span>
            <span class="factory-quick-tag"
                onclick="setFactoryPrompt('Formation avancée Cybersécurité avec 5 modules')">🔒 Cybersécurité</span>
            <span class="factory-quick-tag"
                onclick="setFactoryPrompt('Formation intermédiaire Cloud Computing 4 modules')">☁️ Cloud AWS</span>
            <span class="factory-quick-tag"
                onclick="setFactoryPrompt('Formation Marketing Digital pour entrepreneurs, 6 modules')">📈 Marketing
                Digital</span>
            <span class="factory-quick-tag"
                onclick="setFactoryPrompt('Formation IA & Machine Learning niveau expert, 5 modules')">🤖 IA & ML</span>
        </div>

        <div id="factory-status"
            style="display:none; font-size:0.82rem; color:var(--text-secondary); text-align:center; padding:0.5rem;">
        </div>

        <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.25rem;">
            <button onclick="closeCourseFactory()" class="btn btn-secondary"
                style="padding:0.65rem 1.4rem; border-radius:12px;">Annuler</button>
            <button id="btn-factory-generate" onclick="runCourseFactory()"
                style="padding:0.65rem 1.6rem; border-radius:12px; border:none; cursor:pointer; font-weight:700; font-size:0.9rem;
                    background: var(--gradient-primary); color:white; box-shadow: var(--shadow-glow); display:flex; align-items:center; gap:0.5rem;">
                <span>✨</span> <span id="factory-btn-text">Générer avec Llama-3</span>
            </button>
        </div>
    </div>
</div>

<script>
    // ── Quill Editor ────────────────────────────────────────────
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Saisissez le corps du rapport ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });
    var form = document.querySelector('#add-formation-form');
    form.onsubmit = function () {
        var hiddenDesc = document.querySelector('#hidden-description');
        hiddenDesc.value = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) hiddenDesc.value = '';
    };
    document.getElementById('lieu-select').addEventListener('change', function () {
        document.getElementById('url-field').style.display = (this.value == '1') ? 'block' : 'none';
    });

    // ── VIEW SWITCHER ─────────────────────────────────────────────
    function switchView(viewName) {
        const listView = document.getElementById('view-list');
        const calendarView = document.getElementById('view-calendar');
        document.querySelectorAll('.view-tab').forEach(t => t.classList.remove('active'));
        document.querySelector(`[data-view="${viewName}"]`).classList.add('active');
        if (viewName === 'list') {
            listView.style.display = 'block';
            calendarView.style.display = 'none';
        } else {
            listView.style.display = 'none';
            calendarView.style.display = 'block';
            setTimeout(() => {
                if (window.planningCalendar) {
                    window.planningCalendar.updateSize();
                }
            }, 80);
        }
    }

    // ── DONNÉES PHP → JS ──────────────────────────────────────────
    // ── DONNÉES INJECTÉES (MVC) ──────────────────────────────────
    const calendarEvents = <?php echo $calendarEventsJSON; ?>;
    const tuteurColors = <?php echo $tuteurColorsJSON; ?>;

    // ── VARIABLES CLÉS ──────────────────────────────────────────

    // ── MODAL CRÉNEAU ─────────────────────────────────────────────
    let modalStartStr = '';
    let modalEndStr = '';

    function openAddModal(startStr, endStr) {
        // Réinitialiser le formulaire
        document.getElementById('creneau-id').value = '';
        document.getElementById('creneau-titre').value = '';
        document.getElementById('creneau-recurrent').checked = false;
        document.getElementById('creneau-couleur').value = '#6366f1';
        // Pré-remplir avec l'heure cliquée
        if (startStr) {
            const toLocal = iso => iso ? iso.slice(0, 16) : '';
            document.getElementById('creneau-debut').value = toLocal(startStr);
            document.getElementById('creneau-fin').value = toLocal(endStr || startStr);
        } else {
            document.getElementById('creneau-debut').value = '';
            document.getElementById('creneau-fin').value = '';
        }
        document.getElementById('modal-creneau').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('modal-creneau').style.display = 'none';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // ── SOUMETTRE UN CRÉNEAU ──────────────────────────────────────
    function submitCreneau() {
        const id = document.getElementById('creneau-id').value;
        const tuteurId = document.getElementById('creneau-tuteur').value;
        const titre = document.getElementById('creneau-titre').value.trim() || 'Disponible';
        const debut = document.getElementById('creneau-debut').value;
        const fin = document.getElementById('creneau-fin').value;
        const couleur = document.getElementById('creneau-couleur').value;
        const recurrent = document.getElementById('creneau-recurrent').checked ? '1' : '';

        // Validation JS (iv-system)
        const fields = document.querySelectorAll('#modal-creneau .iv-field');
        let allOk = true;
        fields.forEach(f => { if (!ivValidate(f)) allOk = false; });

        if (!allOk) {
            aptusAlert('Veuillez remplir correctement tous les champs obligatoires.', 'error');
            return;
        }

        if (new Date(debut) >= new Date(fin)) {
            aptusAlert('La date de fin doit être strictement après le début.', 'error');
            const finField = document.getElementById('creneau-fin');
            finField.classList.add('is-invalid');
            return;
        }

        const fd = new FormData();
        fd.append('action', id ? 'update_creneau' : 'add_creneau');
        fd.append('id', id);
        fd.append('id_tuteur', tuteurId);
        fd.append('titre', titre);
        fd.append('debut', debut.replace('T', ' ') + ':00');
        fd.append('fin', fin.replace('T', ' ') + ':00');
        fd.append('couleur', couleur);
        if (recurrent) fd.append('recurrent', '1');

        fetch('ajax_handler_back.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                closeModal();
                if (data.success) {
                    location.reload();
                } else {
                    aptusAlert(data.message, 'error');
                }
            })
            .catch(err => aptusAlert('Erreur : ' + err.message, 'error'));
    }

    // ── SUPPRESSION D'UN CRÉNEAU ──────────────────────────────────
    function deleteCreneau(id, titre) {
        aptusConfirmDelete(() => {
            const fd = new FormData();
            fd.append('action', 'delete_creneau');
            fd.append('id', id);
            fetch('../../controller/ajax_tuteur.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        location.reload();
                    } else {
                        aptusAlert(d.message, 'error');
                    }
                });
        }, `Supprimer le créneau « ${titre} » ?`);
    }

    // ── INITIALISATION FULLCALENDAR ───────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        window.planningCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'fr',
            height: '100%',
            slotDuration: '00:30:00',
            slotLabelInterval: '01:00',
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            nowIndicator: true,
            editable: true,   // Drag & drop activé
            selectable: true,   // Cliquer-glisser pour créer un créneau
            selectMirror: true,

            // Source unique consolidée via le contrôleur
            events: calendarEvents.map(e => ({
                ...e,
                editable: e.extendedProps && e.extendedProps.type !== 'formation'
            })),

            // Cliquer-glisser sur une zone vide → ouvrir le modal d'ajout
            select: function (info) {
                openAddModal(info.startStr, info.endStr);
            },

            // Clic sur un événement → fiche détail
            eventClick: function (info) {
                const e = info.event;
                const props = e.extendedProps;

                // ── Formation (read-only) ──────────────────────────
                if (props.type === 'formation') {
                    document.getElementById('mfd-title').textContent = '📚 ' + props.titre;
                    document.getElementById('mfd-body').innerHTML =
                        `<p style="margin:.3rem 0;"><b>👨‍🏫 Tuteur :</b> ${props.tuteur_nom}</p>
                         <p style="margin:.3rem 0;"><b>📅 Date :</b> ${e.start.toLocaleDateString('fr-FR')}</p>
                         <p style="margin:.3rem 0;"><b>🎓 Niveau :</b> ${props.niveau}</p>
                         <p style="margin:.3rem 0;"><b>🏷️ Domaine :</b> ${props.domaine}</p>
                         <p style="margin:.3rem 0;"><b>📍 Format :</b> ${props.lieu}</p>`;
                    document.getElementById('modal-formation-detail').style.display = 'flex';
                    return;
                }

                // ── Créneau tuteur (éditable) ─────────────────────
                // On affiche les infos + deux boutons Modifier / Supprimer via custom modal
                const toLocal = d => d ? d.toISOString().slice(0, 16) : '';
                const info_txt = `<b>Tuteur :</b> ${props.tuteur_nom}<br><b>Début :</b> ${e.start.toLocaleString('fr-FR')}<br><b>Fin :</b> ${e.end ? e.end.toLocaleString('fr-FR') : '—'}${props.recurrent ? '<br>🔁 Récurrent' : ''}`;

                document.getElementById('creneau-action-desc').innerHTML = `<strong style="font-size:1.05rem;">${props.titre}</strong><br><br>${info_txt}`;
                document.getElementById('modal-creneau-action').style.display = 'flex';

                document.getElementById('btn-creneau-modifier').onclick = () => {
                    document.getElementById('modal-creneau-action').style.display = 'none';
                    document.getElementById('creneau-id').value = e.id;
                    document.getElementById('creneau-tuteur').value = props.id_tuteur;
                    document.getElementById('creneau-titre').value = props.titre;
                    document.getElementById('creneau-debut').value = toLocal(e.start);
                    document.getElementById('creneau-fin').value = toLocal(e.end || e.start);
                    document.getElementById('creneau-couleur').value = e.backgroundColor || '#6366f1';
                    document.getElementById('creneau-recurrent').checked = props.recurrent;
                    document.getElementById('modal-creneau').style.display = 'flex';
                };

                document.getElementById('btn-creneau-supprimer').onclick = () => {
                    document.getElementById('modal-creneau-action').style.display = 'none';
                    deleteCreneau(e.id, props.titre);
                };
            },

            // Drag & drop → mise à jour des dates en BDD
            eventDrop: function (info) {
                const e = info.event;
                saveDateChange(e.id, e.start, e.end, info.revert);
            },
            // Resize → mise à jour de la fin
            eventResize: function (info) {
                const e = info.event;
                saveDateChange(e.id, e.start, e.end, info.revert);
            },

            eventDisplay: 'block',
        });

        window.planningCalendar.render();
        // Masquer vue calendrier par défaut
        document.getElementById('view-calendar').style.display = 'none';
    });

    // ── SAUVEGARDER DRAG/RESIZE ───────────────────────────────────
    function saveDateChange(id, start, end, revert) {
        const toSQL = d => d ? d.toISOString().replace('T', ' ').slice(0, 19) : '';
        const fd = new FormData();
        fd.append('action', 'update_creneau');
        fd.append('id', id);
        fd.append('debut', toSQL(start));
        fd.append('fin', toSQL(end || start));
        fetch('ajax_handler_back.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    revert();
                    alert('❌ ' + d.message);
                }
            })
            .catch(() => revert());
    }

    // ── VUE PAR DÉFAUT ───────────────────────────────────────────
    document.querySelector('[data-view="list"]').classList.add('active');

    // ── VALIDATION CHAMPS EN TEMPS RÉEL (iv = inline-validation) ──
    function ivValidate(input) {
        const wrap = input.closest('.input-validated-wrap');
        const statusEl = wrap ? wrap.querySelector('.iv-status') : null;
        const msgEl = document.getElementById(input.id + '-msg');
        const min = parseInt(input.dataset.min || 0);
        const label = input.dataset.label || 'Ce champ';
        const val = input.value.trim();
        let valid;

        if (input.tagName === 'SELECT') {
            valid = val !== '';
        } else if (input.type === 'date' || input.type === 'datetime-local') {
            valid = val !== '' && !isNaN(Date.parse(val));
            if (valid && input.dataset.minDate) {
                valid = val >= input.dataset.minDate;
            }
        } else {
            valid = val.length >= min;
        }

        input.classList.toggle('is-valid', valid);
        input.classList.toggle('is-invalid', !valid);

        if (statusEl) {
            const hasValue = val !== '';
            const isDirty = input.classList.contains('is-dirty');

            if (hasValue || isDirty) {
                statusEl.className = 'iv-status ' + (valid ? 'valid' : 'invalid');
                statusEl.style.display = 'inline-flex';
                statusEl.innerHTML = valid
                    ? '<i data-lucide="check" style="width:14px;height:14px;"></i>'
                    : '<i data-lucide="alert-circle" style="width:14px;height:14px;"></i>';
                if (window.lucide) lucide.createIcons();
            } else {
                statusEl.style.display = 'none';
            }
        }

        if (msgEl) {
            if (!valid) {
                if (input.type === 'date' || input.type === 'datetime-local') {
                    const valDate = new Date(val);
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);
                    if (val === '') {
                        msgEl.textContent = `${label} est requis.`;
                    } else if (valDate < now && input.dataset.minDate) {
                        msgEl.textContent = `La date ne peut pas être dans le passé.`;
                    } else {
                        msgEl.textContent = `Date invalide.`;
                    }
                } else {
                    msgEl.textContent = (val.length === 0)
                        ? `${label} est requis.`
                        : `Trop court (min. ${min} caractères).`;
                }
                msgEl.style.display = 'block';
            } else {
                msgEl.textContent = '';
                msgEl.style.display = 'none';
            }
        }
        return valid;
    }

    // Attacher les écouteurs sur TOUS les champs iv-field de la page
    document.querySelectorAll('.iv-field').forEach(input => {
        ['input', 'blur', 'change'].forEach(ev => {
            input.addEventListener(ev, () => {
                if (ev === 'blur' || ev === 'change') input.classList.add('is-dirty');
                ivValidate(input);
            });
        });
        // Valider au chargement pour l'état initial (si déjà rempli)
        if (input.value.trim() !== '') {
            input.classList.add('is-dirty');
            ivValidate(input);
        }
    });

    // Bouton de validation final "Créer"
    const btnCreate = document.getElementById('btn-create-formation');
    if (btnCreate) {
        btnCreate.addEventListener('click', function (e) {
            e.preventDefault();
            const fields = document.querySelectorAll('#add-formation-form .iv-field');
            let allOk = true;
            fields.forEach(f => { if (!ivValidate(f)) allOk = false; });

            const hiddenDesc = document.querySelector('#hidden-description');
            hiddenDesc.value = quill.root.innerHTML.trim();
            if (quill.getText().trim().length < 10) {
                showModalError('Veuillez saisir une description plus détaillée (min. 10 caractères).');
                allOk = false;
            }
            if (!allOk) return;

            // AJAX — modal reste ouvert jusqu'au résultat
            btnCreate.disabled = true;
            btnCreate.innerHTML = '⏳ Création en cours...';

            const formData = new FormData(document.getElementById('add-formation-form'));
            formData.append('action', 'add_formation');
            fetch('ajax_handler_back.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    btnCreate.disabled = false;
                    btnCreate.innerHTML = 'Créer la formation';
                    if (data.type === 'success') {
                        closeModals();
                        aptusAlert(data.message || 'Formation créée avec succès !', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showModalError(data.message || 'Erreur lors de la création.');
                    }
                })
                .catch(err => {
                    btnCreate.disabled = false;
                    btnCreate.innerHTML = 'Créer la formation';
                    showModalError('Erreur réseau : ' + err.message);
                });
        });
    }

    function showModalError(msg) {
        let banner = document.getElementById('modal-error-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'modal-error-banner';
            banner.style.cssText = 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:10px 14px;border-radius:10px;font-size:0.85rem;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:8px;';
            banner.innerHTML = '⚠️ <span id="modal-error-text"></span>';
            const modalBody = document.querySelector('#add-formation-modal .modal-body');
            modalBody.insertBefore(banner, modalBody.firstChild);
        }
        document.getElementById('modal-error-text').textContent = msg;
        banner.style.display = 'flex';
        document.querySelector('#add-formation-modal .modal-body').scrollTop = 0;
    }

    // Fix: Trigger modal with transition
    const addFormationBtn = document.getElementById('add-formation-btn');
    const addFormationModal = document.getElementById('add-formation-modal');
    if (addFormationBtn && addFormationModal) {
        addFormationBtn.addEventListener('click', function () {
            addFormationModal.style.display = 'flex';
            setTimeout(() => {
                addFormationModal.classList.add('active');
            }, 10);
        });
    }

    // Déblocage Audio pour les notifications
    document.addEventListener('click', function () {
        if (window.AudioContext || window.webkitAudioContext) {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            if (ctx.state === 'suspended') ctx.resume();
        }
    }, { once: true });
    // --- LOGIQUE DE RECHERCHE DYNAMIQUE (AJAX) ---
    const searchInput = document.getElementById('admin-formation-search');
    const filterDomaine = document.getElementById('filter-domaine');
    const filterNiveau = document.getElementById('filter-niveau');
    const resetBtn = document.getElementById('reset-filters');
    const tableBody = document.getElementById('formations-table-body');

    function deleteFormation(id, title) {
        aptusConfirmDelete(() => {
            const fd = new FormData();
            fd.append('action', 'delete_formation');
            fd.append('id', id);
            fetch('ajax_handler_back.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        aptusAlert(d.message || 'Formation supprimée.', 'success');
                        performSearch(); // Rafraîchir la liste sans recharger la page
                    } else {
                        aptusAlert(d.message || 'Erreur lors de la suppression.', 'error');
                    }
                })
                .catch(err => aptusAlert('Erreur réseau : ' + err.message, 'error'));
        }, `Supprimer définitivement la formation « ${title} » ?`);
    }

    function performSearch() {
        const s = searchInput.value;
        const d = filterDomaine.value;
        const n = filterNiveau.value;

        // Afficher/Masquer le bouton reset
        if (s || d || n) {
            resetBtn.style.display = 'flex';
        } else {
            resetBtn.style.display = 'none';
        }

        // On ajoute un effet de chargement visuel léger
        tableBody.style.opacity = '0.5';

        fetch(`ajax_handler_back.php?action=search_formations&s=${encodeURIComponent(s)}&d=${encodeURIComponent(d)}&n=${encodeURIComponent(n)}`)
            .then(response => response.text())
            .then(html => {
                tableBody.innerHTML = html;
                tableBody.style.opacity = '1';
                // Réinitialiser les icônes Lucide pour les nouveaux éléments
                if (window.lucide) lucide.createIcons();
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                tableBody.style.opacity = '1';
            });
    }

    function resetFilters() {
        searchInput.value = '';
        filterDomaine.value = '';
        filterNiveau.value = '';
        performSearch();
    }

    if (searchInput) searchInput.addEventListener('input', performSearch);
    if (filterDomaine) filterDomaine.addEventListener('change', performSearch);
    if (filterNiveau) filterNiveau.addEventListener('change', performSearch);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);

    // ══════════════════════════════════════════════════════════════
    // COURSE FACTORY — Génération IA en 1 clic
    // ══════════════════════════════════════════════════════════════
    function openCourseFactory() {
        document.getElementById('modal-course-factory').style.display = 'flex';
        setTimeout(() => document.getElementById('factory-prompt').focus(), 100);
    }
    function closeCourseFactory() {
        document.getElementById('modal-course-factory').style.display = 'none';
        document.getElementById('factory-status').style.display = 'none';
    }
    function setFactoryPrompt(text) {
        document.getElementById('factory-prompt').value = text;
        document.getElementById('factory-prompt').focus();
    }

    function runCourseFactory() {
        const prompt = document.getElementById('factory-prompt').value.trim();
        if (!prompt) {
            document.getElementById('factory-status').textContent = '⚠️ Veuillez décrire la formation souhaitée.';
            document.getElementById('factory-status').style.display = 'block';
            return;
        }

        const btnText = document.getElementById('factory-btn-text');
        const btn = document.getElementById('btn-factory-generate');
        const status = document.getElementById('factory-status');

        btn.disabled = true;
        btnText.textContent = 'Génération en cours...';
        status.textContent = '🤖 Llama-3 crée votre formation...';
        status.style.display = 'block';
        status.style.color = 'var(--accent-primary)';

        const fd = new FormData();
        fd.append('action', 'generate_course_factory');
        fd.append('prompt', prompt);

        fetch('../frontoffice/ajax_handler.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btnText.textContent = 'Générer avec Llama-3';

                if (!data.success || !data.data) {
                    status.textContent = '⚠️ ' + (data.message || 'Erreur lors de la génération.');
                    status.style.color = '#ef4444';
                    return;
                }

                const d = data.data;

                // ── PRÉ-REMPLISSAGE DU FORMULAIRE PRINCIPAL ──
                // Titre
                const titreField = document.getElementById('af-titre');
                if (titreField && d.titre) {
                    titreField.value = d.titre;
                    titreField.dispatchEvent(new Event('input'));
                }

                // Domaine
                const domaineField = document.getElementById('af-domaine');
                if (domaineField && d.domaine) {
                    domaineField.value = d.domaine;
                    domaineField.dispatchEvent(new Event('input'));
                }

                // Niveau
                const niveauField = document.querySelector('#add-formation-form select[name="niveau"]');
                if (niveauField && d.niveau) {
                    const opts = Array.from(niveauField.options);
                    const match = opts.find(o => o.value.toLowerCase() === d.niveau.toLowerCase());
                    if (match) niveauField.value = match.value;
                }

                // Durée
                const dureeField = document.querySelector('#add-formation-form input[name="duree"]');
                if (dureeField && d.duree) dureeField.value = d.duree;

                // Description riche dans Quill.js
                if (window.quill && d.description_riche) {
                    // Construire le contenu complet : description + modules
                    let richContent = d.description_riche || '';

                    if (d.prerequis) {
                        richContent += `<h2>Prérequis</h2><p>${d.prerequis}</p>`;
                    }

                    if (d.modules && d.modules.length > 0) {
                        richContent += '<h2>📚 Plan du cours</h2><ol>';
                        d.modules.forEach(m => {
                            richContent += `<li><strong>${m.titre || 'Module'}</strong> (${m.duree || ''})<br>${m.description || ''}</li>`;
                        });
                        richContent += '</ol>';
                    }

                    if (d.tags && d.tags.length > 0) {
                        richContent += `<p><em>🏷️ Tags : ${d.tags.join(', ')}</em></p>`;
                    }

                    quill.root.innerHTML = richContent;
                    document.getElementById('hidden-description').value = richContent;
                }

                // Fermer le modal factory et ouvrir le formulaire principal
                closeCourseFactory();

                // Ouvrir le modal principal si pas déjà ouvert
                const mainModal = document.getElementById('add-formation-modal');
                if (mainModal) {
                    mainModal.style.display = 'flex';
                    setTimeout(() => mainModal.classList.add('active'), 10);
                    mainModal.scrollTop = 0;
                }

                // Focus sur le titre pour une continuité parfaite
                if (titreField) titreField.focus();
            })
            .catch(err => {
                btn.disabled = false;
                btnText.textContent = 'Générer avec Llama-3';
                status.textContent = '⚠️ Erreur réseau : ' + err.message;
                status.style.color = '#ef4444';
            });
    }

    // Fermer le modal factory avec Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeCourseFactory();
        }
    });
</script>