<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurController.php';
require_once __DIR__ . '/../../model/Formation.php';

$formationC = new FormationController();
$tuteurC    = new TuteurController();



$listeFormations = $formationC->listerFormations()->fetchAll();
$tuteurs         = $formationC->getTuteurs();   // Pour le select du modal formation
$tuteursList     = $tuteurC->listerTuteurs();   // Pour filtre planning + modal créneau
$totalFormations = count($listeFormations);
$domaines = array_unique(array_map(function($f) { return $f['domaine']; }, $listeFormations));
sort($domaines);

// Events : formations (background) + créneaux tuteurs (foreground)
$formationEvents = $formationC->getFormationsForCalendar();
$planningEvents  = $tuteurC->getPlanning();  // Tous les tuteurs
$calendarEvents  = json_encode(array_merge($formationEvents, $planningEvents));
$planningJSON    = json_encode($planningEvents);
$tuteurColors    = [];
$palette = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6','#ef4444'];
foreach ($tuteursList as $idx => $t) {
    $tuteurColors[$t['id']] = $palette[$idx % count($palette)];
}
$tuteurColorsJSON = json_encode($tuteurColors);

$statsGlobales  = $formationC->getStatsGlobales();
$totalInscrits  = $statsGlobales['total_inscrits'];
$certificats    = $statsGlobales['certificats'];
$tauxCompletion = $statsGlobales['taux_completion'];

$pageTitle = "Formations";
$pageCSS   = "formations.css";
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
            <div style="color: red; margin: 0 15px; font-weight: bold;">
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
    <div class="view-tabs-container" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-card);">
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
                <select id="filter-domaine" class="input" style="padding:0.4rem 0.8rem; border-radius:8px; font-size:0.8rem; width:140px;">
                    <option value="">Tous Domaines</option>
                    <?php foreach($domaines as $d): ?>
                        <option value="<?php echo htmlspecialchars($d); ?>"><?php echo htmlspecialchars($d); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="filter-niveau" class="input" style="padding:0.4rem 0.8rem; border-radius:8px; font-size:0.8rem; width:130px;">
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

                <button id="reset-filters" class="btn btn-sm btn-ghost" style="display:none; color:var(--accent-tertiary); padding: 0.5rem;" title="Effacer les filtres">
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
                <?php foreach ($listeFormations as $f):
                    $levelClass = 'badge-neutral';
                    switch ($f['niveau']) {
                        case 'Débutant':
                            $levelClass = 'badge-success';
                            break;
                        case 'Intermédiaire':
                            $levelClass = 'badge-warning';
                            break;
                        case 'Avancé':
                            $levelClass = 'badge-danger';
                            break;
                        case 'Expert':
                            $levelClass = 'badge-primary';
                            break;
                    }
                    ?>
                    <tr>
                        <td class="fw-medium">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="<?php echo $f['image_base64']; ?>" alt=""
                                    style="width:45px; height:25px; object-fit:cover; border-radius:4px; background:#eee;">
                                <?php echo htmlspecialchars($f['titre']); ?>
                            </div>
                        </td>
                        <td><span class="badge badge-info">
                                <?php echo htmlspecialchars($f['domaine']); ?>
                            </span></td>
                        <td><span class="badge <?php echo $levelClass; ?>">
                                <?php echo $f['niveau']; ?>
                            </span></td>
                        <td class="text-sm">
                            <i data-lucide="<?php echo $f['is_online'] ? 'video' : 'map-pin'; ?>"
                                style="width:14px; height:14px; vertical-align:middle; margin-right:5px;"></i>
                            <?php echo $f['is_online'] ? 'En ligne' : 'Présentiel'; ?>
                        </td>
                        <td class="text-sm text-secondary">
                            <?php echo date('d M. Y', strtotime($f['date_formation'])); ?>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="edit_formation.php?id=<?php echo $f['id_formation']; ?>" class="btn btn-sm btn-ghost"
                                    title="Éditer">
                                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"
                                    onclick='aptusConfirmDelete("../../controller/traitement_delete.php?delete_id=<?php echo $f["id_formation"]; ?>", <?php echo htmlspecialchars(json_encode("Supprimer définitivement la formation « " . $f["titre"] . " » ?"), ENT_QUOTES, "UTF-8"); ?>);'>
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ════════════════════════════════════════════
         VUE 2 : PLANNING TUTEURS (CALENDRIER INTERACTIF)
         ════════════════════════════════════════════ -->
    <div id="view-calendar" style="display:none; padding-bottom: 2rem;">
        <div style="display:flex; justify-content: flex-end; margin-bottom: 1rem; padding: 0 1rem;">
            <button onclick="openAddModal()" class="btn btn-primary" style="padding:.5rem 1rem;border-radius:8px;font-size:.9rem;font-weight:600;">
                ➕ Placer Indisponibilité / Réunion
            </button>
        </div>
        <!-- Conteneur flex colonne pour que le div du calendrier prenne 100% de la hauteur dispo -->
        <div style="height:700px; padding: 0 1rem; display:flex; flex-direction:column;">
            <div style="flex:1; background:var(--bg-card); border-radius:12px; padding:1rem; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); border:1px solid var(--border-color);">
                <div id="calendar" style="height:100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ MODAL : Ajouter/modifier un créneau ════════════ -->
<div id="modal-creneau" style="display:none;position:fixed;inset:0;background:var(--bg-overlay);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-radius:20px;padding:2rem;width:100%;max-width:460px;box-shadow:var(--shadow-xl);">
        <h3 style="margin:0 0 .3rem;font-size:1.15rem;font-weight:800;">📅 Indisponibilité / Réunion</h3>
        <p style="margin:0 0 1.5rem;font-size:.82rem;color:var(--text-secondary);">Bloquez un horaire (congés, réunion interne) pour un tuteur.</p>

        <input type="hidden" id="creneau-id" value="">

        <div style="margin-bottom:1rem;">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.4rem;">Tuteur <span style="color:var(--accent-warning);">*</span></label>
            <select id="creneau-tuteur" class="input" style="width:100%;padding:.6rem .9rem;border-radius:10px;font-size:.88rem;outline:none;">
                <option value="">Sélectionnez un tuteur...</option>
                <?php foreach ($tuteursList as $t): ?>
                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.4rem;">Motif / Type d'événement</label>
            <input type="text" id="creneau-titre" class="input" placeholder="Ex: Congés annuels, Réunion d'équipe..." style="width:100%;padding:.6rem .9rem;border-radius:10px;font-size:.88rem;outline:none;box-sizing:border-box;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
            <div>
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.4rem;">Début <span style="color:var(--accent-warning);">*</span></label>
                <input type="datetime-local" id="creneau-debut" class="input" style="width:100%;padding:.6rem .7rem;border-radius:10px;font-size:.82rem;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.4rem;">Fin <span style="color:var(--accent-warning);">*</span></label>
                <input type="datetime-local" id="creneau-fin" class="input" style="width:100%;padding:.6rem .7rem;border-radius:10px;font-size:.82rem;outline:none;box-sizing:border-box;">
            </div>
        </div>

        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;">
            <label style="font-size:.8rem;font-weight:600;">Couleur de repère</label>
            <input type="color" id="creneau-couleur" value="#f59e0b" style="width:40px;height:36px;border:1px solid var(--border-color);border-radius:8px;cursor:pointer;padding:2px;background:var(--bg-input);">
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;margin-left:.5rem;">
                <input type="checkbox" id="creneau-recurrent"> Récurrent (hebdomadaire)
            </label>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button onclick="closeModal()" class="btn" style="padding:.6rem 1.4rem;border-radius:10px;background:var(--bg-tertiary);color:var(--text-primary);border:1px solid var(--border-color);cursor:pointer;font-weight:600;font-size:.88rem;">Annuler</button>
            <button onclick="submitCreneau()" id="btn-save-creneau" class="btn btn-primary" style="padding:.6rem 1.6rem;border-radius:10px;border:none;cursor:pointer;font-weight:700;font-size:.88rem;box-shadow:var(--shadow-glow);">✅ Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modal : Choix action créneau (Remplace prompt du navigateur) -->
<div id="modal-creneau-action" style="display:none;position:fixed;inset:0;background:var(--bg-overlay);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-radius:20px;padding:2rem;width:100%;max-width:400px;box-shadow:var(--shadow-xl);text-align:center;">
        <h3 style="margin:0 0 1rem;font-size:1.15rem;font-weight:800;">📅 Options du Créneau</h3>
        <p id="creneau-action-desc" style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.5;"></p>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <button id="btn-creneau-modifier" class="btn btn-primary" style="padding:0.75rem; border-radius:10px;">✏️ Modifier ce créneau</button>
            <button id="btn-creneau-supprimer" class="btn btn-secondary" style="padding:0.75rem; color:#ef4444; border-color:rgba(239,68,68,0.3); border-radius:10px;">🗑️ Supprimer</button>
            <button onclick="document.getElementById('modal-creneau-action').style.display='none'" class="btn btn-ghost" style="padding:0.75rem; border-radius:10px;">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal : Détail formation (read-only, remplace Swal formation) -->
<div id="modal-formation-detail" style="display:none;position:fixed;inset:0;background:var(--bg-overlay);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-radius:20px;padding:2rem;width:100%;max-width:420px;box-shadow:var(--shadow-xl);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:800;" id="mfd-title">📚 Formation</h3>
            <button onclick="document.getElementById('modal-formation-detail').style.display='none'" style="background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:1.4rem;line-height:1;">×</button>
        </div>
        <div id="mfd-body" style="font-size:.88rem;line-height:1.7;"></div>
        <div style="margin-top:1.5rem;text-align:right;">
            <button onclick="document.getElementById('modal-formation-detail').style.display='none'" class="btn btn-secondary" style="padding:.5rem 1.4rem;">Fermer</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="add-formation-modal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3>Nouvelle Formation</h3>
            <button class="modal-close btn-icon" type="button" onclick="closeModals()"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <form action="../../controller/traitement_add.php" method="POST" enctype="multipart/form-data" id="add-formation-form"
                class="auth-form" novalidate>

                <!-- Titre -->
                <div class="form-group">
                    <label class="form-label">Titre de la formation <span class="required-star">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <span class="iv-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);pointer-events:none;"><i data-lucide="book" style="width:16px;height:16px;"></i></span>
                        <input type="text" class="input iv-field" name="titre" id="af-titre" placeholder="Ex: Masterclass IA"
                               style="padding-left:36px;" data-min="3" data-label="Titre">
                        <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="af-titre-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                </div>

                <div class="form-group" style="padding-bottom: 25px;">
                    <label class="form-label">Description (Contenu Riche) <span class="required-star">*</span></label>
                    <textarea class="textarea" name="description" id="hidden-description" style="display:none;"></textarea>
                    <div id="quill-editor" style="height: 150px; background: var(--bg-surface);"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <!-- Domaine -->
                    <div class="form-group">
                        <label class="form-label">Domaine <span class="required-star">*</span></label>
                        <div class="input-validated-wrap" style="position:relative;">
                            <input type="text" class="input iv-field" name="domaine" id="af-domaine" placeholder="Ex: Développement Web"
                                   data-min="2" data-label="Domaine">
                            <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                        </div>
                        <span class="iv-msg" id="af-domaine-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
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
                                   min="<?php echo date('Y-m-d'); ?>" data-min="1" data-label="Date de début">
                            <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                        </div>
                        <span class="iv-msg" id="af-date-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date de fin (Optionnel)</label>
                        <div class="input-validated-wrap" style="position:relative;">
                            <input type="date" class="input" name="date_fin" id="af-date-fin">
                        </div>
                        <p style="font-size: 0.7rem; color: #64748b; margin-top: 4px;">Utilisée pour masquer le cours 48h après.</p>
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
                        <select class="select iv-field" name="id_tuteur" id="af-tuteur" data-min="1" data-label="Tuteur" style="appearance:auto;">
                            <option value="">Sélectionnez un tuteur...</option>
                            <?php foreach ($tuteurs as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="iv-status" style="position:absolute;right:32px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="af-tuteur-msg" style="display:none;font-size:.78rem;color:#ef4444;margin-top:4px;display:block;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Prérequis (Optionnel)</label>
                    <select class="select" name="prerequis_id">
                        <option value="">Aucun prérequis</option>
                        <?php foreach ($listeFormations as $f_pre): ?>
                            <option value="<?php echo $f_pre['id_formation']; ?>"><?php echo htmlspecialchars($f_pre['titre']); ?></option>
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
.tuteur-filter-item:hover { background: rgba(99,102,241,.07) !important; }
.tuteur-filter-item.active { background: rgba(99,102,241,.12) !important; }
/* ── FullCalendar overrides ── */
#calendar .fc-event { border-radius:6px !important; font-size:.8rem !important; cursor:pointer; }
#calendar .fc-toolbar-title { font-size:1rem !important; font-weight:700; }
#calendar .fc-button { border-radius:8px !important; font-size:.8rem !important; }

/* ── Validation champs ── */
.iv-field.is-valid   { border-color: #10b981 !important; background: rgba(16,185,129,0.04); }
.iv-field.is-invalid { border-color: #ef4444 !important; background: rgba(239,68,68,0.04); }
.iv-status.valid  { color: #10b981; display:inline-flex !important; }
.iv-status.invalid{ color: #ef4444; display:inline-flex !important; }
.iv-msg           { display:none; }
.iv-msg.show      { display:block !important; }
</style>

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
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });
    var form = document.querySelector('#add-formation-form');
    form.onsubmit = function() {
        var hiddenDesc = document.querySelector('#hidden-description');
        hiddenDesc.value = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) hiddenDesc.value = '';
    };
    document.getElementById('lieu-select').addEventListener('change', function () {
        document.getElementById('url-field').style.display = (this.value == '1') ? 'block' : 'none';
    });

    // ── VIEW SWITCHER ─────────────────────────────────────────────
    function switchView(viewName) {
        const listView     = document.getElementById('view-list');
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
    // Toutes les formations (background reference)
    const formationEvents = <?php echo json_encode($formationEvents); ?>;
    // Créneaux tuteurs (peuvent être filtrés dynamiquement)
    let allPlanningEvents = <?php echo $planningJSON; ?>;
    // Couleurs par tuteur
    const tuteurColors = <?php echo $tuteurColorsJSON; ?>;

    // ── VARIABLES CLÉS ──────────────────────────────────────────

    // ── MODAL CRÉNEAU ─────────────────────────────────────────────
    let modalStartStr = '';
    let modalEndStr   = '';

    function openAddModal(startStr, endStr) {
        // Réinitialiser le formulaire
        document.getElementById('creneau-id').value    = '';
        document.getElementById('creneau-titre').value = '';
        document.getElementById('creneau-recurrent').checked = false;
        document.getElementById('creneau-couleur').value = '#6366f1';
        // Pré-remplir avec l'heure cliquée
        if (startStr) {
            const toLocal = iso => iso ? iso.slice(0,16) : '';
            document.getElementById('creneau-debut').value = toLocal(startStr);
            document.getElementById('creneau-fin').value   = toLocal(endStr || startStr);
        } else {
            document.getElementById('creneau-debut').value = '';
            document.getElementById('creneau-fin').value   = '';
        }
        document.getElementById('modal-creneau').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('modal-creneau').style.display = 'none';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // ── SOUMETTRE UN CRÉNEAU ──────────────────────────────────────
    function submitCreneau() {
        const id       = document.getElementById('creneau-id').value;
        const tuteurId = document.getElementById('creneau-tuteur').value;
        const titre    = document.getElementById('creneau-titre').value.trim() || 'Disponible';
        const debut    = document.getElementById('creneau-debut').value;
        const fin      = document.getElementById('creneau-fin').value;
        const couleur  = document.getElementById('creneau-couleur').value;
        const recurrent= document.getElementById('creneau-recurrent').checked ? '1' : '';

        if (!tuteurId) { aptusAlert('Sélectionnez un tuteur.', 'error'); return; }
        if (!debut || !fin) { aptusAlert('Remplissez les dates de début et de fin.', 'error'); return; }
        if (debut >= fin) { aptusAlert('La date de fin doit être après le début.', 'error'); return; }

        const fd = new FormData();
        fd.append('action',    id ? 'update_creneau' : 'add_creneau');
        fd.append('id',         id);
        fd.append('id_tuteur',  tuteurId);
        fd.append('titre',      titre);
        fd.append('debut',      debut.replace('T',' ') + ':00');
        fd.append('fin',        fin.replace('T',' ')   + ':00');
        fd.append('couleur',    couleur);
        if (recurrent) fd.append('recurrent', '1');

        fetch('../../controller/ajax_tuteur.php', { method:'POST', body:fd })
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
            fetch('../../controller/ajax_tuteur.php', { method:'POST', body:fd })
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
            initialView:  'timeGridWeek',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'fr',
            height: '100%',
            slotDuration:       '00:30:00',
            slotLabelInterval:  '01:00',
            slotLabelFormat:    { hour:'2-digit', minute:'2-digit', hour12:false },
            nowIndicator:       true,
            editable:           true,   // Drag & drop activé
            selectable:         true,   // Cliquer-glisser pour créer un créneau
            selectMirror:       true,

            // Source 1 : Formations — vraies séances de tuteurs, colorées par tuteur
            eventSources: [
                {
                    id: 'formations',
                    events: formationEvents.map(e => ({ ...e, editable: false }))
                },
                {
                    id: 'planning',
                    events: allPlanningEvents
                }
            ],

            // Cliquer-glisser sur une zone vide → ouvrir le modal d'ajout
            select: function (info) {
                openAddModal(info.startStr, info.endStr);
            },

            // Clic sur un événement → fiche détail
            eventClick: function (info) {
                const e     = info.event;
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
                const toLocal = d => d ? d.toISOString().slice(0,16) : '';
                const info_txt = `<b>Tuteur :</b> ${props.tuteur_nom}<br><b>Début :</b> ${e.start.toLocaleString('fr-FR')}<br><b>Fin :</b> ${e.end ? e.end.toLocaleString('fr-FR') : '—'}${props.recurrent ? '<br>🔁 Récurrent' : ''}`;
                
                document.getElementById('creneau-action-desc').innerHTML = `<strong style="font-size:1.05rem;">${props.titre}</strong><br><br>${info_txt}`;
                document.getElementById('modal-creneau-action').style.display = 'flex';

                document.getElementById('btn-creneau-modifier').onclick = () => {
                    document.getElementById('modal-creneau-action').style.display = 'none';
                    document.getElementById('creneau-id').value    = e.id;
                    document.getElementById('creneau-tuteur').value= props.id_tuteur;
                    document.getElementById('creneau-titre').value = props.titre;
                    document.getElementById('creneau-debut').value = toLocal(e.start);
                    document.getElementById('creneau-fin').value   = toLocal(e.end || e.start);
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
        const toSQL = d => d ? d.toISOString().replace('T',' ').slice(0,19) : '';
        const fd = new FormData();
        fd.append('action', 'update_creneau');
        fd.append('id',    id);
        fd.append('debut', toSQL(start));
        fd.append('fin',   toSQL(end || start));
        fetch('../../controller/ajax_tuteur.php', { method:'POST', body:fd })
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
        const wrap     = input.closest('.input-validated-wrap');
        const statusEl = wrap ? wrap.querySelector('.iv-status') : null;
        const msgEl    = document.getElementById(input.id + '-msg');
        const min      = parseInt(input.dataset.min || 0);
        const label    = input.dataset.label || 'Ce champ';
        const val      = input.value.trim();
        let valid;

        if (input.tagName === 'SELECT') {
            valid = val !== '';
        } else if (input.type === 'date') {
            valid = val !== '' && !isNaN(Date.parse(val));
            if (valid && input.hasAttribute('min')) {
                valid = val >= input.getAttribute('min');
            }
        } else {
            valid = val.length >= min;
        }

        input.classList.toggle('is-valid',   valid);
        input.classList.toggle('is-invalid', !valid);

        if (statusEl) {
            statusEl.className = 'iv-status ' + (valid ? 'valid' : 'invalid');
            statusEl.style.display = 'inline-flex';
            statusEl.innerHTML = valid
                ? '<i data-lucide="check" style="width:14px;height:14px;"></i>'
                : '<i data-lucide="alert-circle" style="width:14px;height:14px;"></i>';
            lucide.createIcons();
        }

        if (msgEl) {
            if (!valid) {
                if (input.type === 'date') {
                    const valDate = new Date(val);
                    const now = new Date();
                    now.setHours(0,0,0,0);
                    if (val === '') {
                        msgEl.textContent = `${label} est requis.`;
                    } else if (valDate < now) {
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

    // Attacher les écouteurs sur les champs id-field du formulaire
    document.querySelectorAll('#add-formation-form .iv-field').forEach(input => {
        ['input', 'blur', 'change'].forEach(ev => {
            input.addEventListener(ev, () => ivValidate(input));
        });
    });

    // Bouton de validation final "Créer"
    const btnCreate = document.getElementById('btn-create-formation');
    if (btnCreate) {
        btnCreate.addEventListener('click', function(e) {
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
            fetch('../../controller/traitement_add.php', { method: 'POST', body: formData })
            .then(() => fetch('../../controller/check_flash.php'))
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
        addFormationBtn.addEventListener('click', function() {
            addFormationModal.style.display = 'flex';
            setTimeout(() => {
                addFormationModal.classList.add('active');
            }, 10);
        });
    }

    // Déblocage Audio pour les notifications
    document.addEventListener('click', function() {
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

        fetch(`ajax_search_formations.php?s=${encodeURIComponent(s)}&d=${encodeURIComponent(d)}&n=${encodeURIComponent(n)}`)
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
</script>