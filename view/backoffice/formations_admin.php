<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurController.php';
require_once __DIR__ . '/../../model/Formation.php';

$formationC = new FormationController();
$tuteurC    = new TuteurController();

// Suppression d'une formation
if (isset($_GET['delete_id'])) {
    try {
        $formationC->deleteFormation($_GET['delete_id']);
        $_SESSION['flash_success'] = "Formation supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    header('Location: formations_admin.php');
    exit();
}

$listeFormations = $formationC->listerFormations()->fetchAll();
$tuteurs         = $formationC->getTuteurs();   // Pour le select du modal formation
$tuteursList     = $tuteurC->listerTuteurs();   // Pour filtre planning + modal créneau
$totalFormations = count($listeFormations);

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
            <div class="search-bar" style="max-width:280px;">
                <i data-lucide="search" style="width:16px;height:16px;"></i>
                <input type="text" class="input" placeholder="Rechercher..." id="admin-formation-search">
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
            <tbody>
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
                                <a href="formations_admin.php?delete_id=<?php echo $f['id_formation']; ?>"
                                    class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"
                                    onclick="return confirm('Supprimer définitivement cette formation ?');">
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </a>
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

<div class="modal-overlay" id="add-formation-modal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3>Nouvelle Formation</h3>
            <button class="modal-close btn-icon"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <form action="traitement_add.php" method="POST" enctype="multipart/form-data" id="add-formation-form"
                class="auth-form">

                <div class="form-group">
                    <label class="form-label">Titre de la formation <span class="required-star">*</span></label>
                    <input type="text" class="input" name="titre" placeholder="Ex: Masterclass IA">
                </div>

                <div class="form-group" style="padding-bottom: 25px;">
                    <label class="form-label">Description (Contenu Riche) <span class="required-star">*</span></label>
            <!-- Textarea caché qui contient le HTML de Quill -->
            <!-- C'est ce champ qui est envoyé dans le POST, pas le div Quill -->
                    <textarea class="textarea" name="description" id="hidden-description" style="display:none;"></textarea>
                    <div id="quill-editor" style="height: 150px; background: var(--bg-surface);"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Domaine <span class="required-star">*</span></label>
                        <input type="text" class="input" name="domaine">
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
                    <div class="form-group">
                        <label class="form-label">Date de début <span class="required-star">*</span></label>
                        <input type="date" class="input" name="date_formation">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Durée (ex: 10h)</label>
                        <input type="text" class="input" name="duree">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tuteur <span class="required-star">*</span></label>
                    <select class="select" name="id_tuteur">
                        <option value="">Sélectionnez un tuteur...</option>
                        <?php foreach ($tuteurs as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
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

                <!-- Si online et pas d'URL fournie, le contrôleur génère un lien Jitsi auto -->
                <div class="form-group" id="url-field" style="display:none;">
                    <label class="form-label">URL Room (Laissez vide pour Jitsi auto)</label>
                    <input type="url" class="input" name="online_url">
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Annuler</button>
            <button class="btn btn-primary" type="submit" form="add-formation-form">Créer la formation</button>
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

        if (!tuteurId) { Toast.fire({ icon:'error', title:'Sélectionnez un tuteur.' }); return; }
        if (!debut || !fin) { Toast.fire({ icon:'error', title:'Remplissez les dates.' }); return; }
        if (debut >= fin) { Toast.fire({ icon:'error', title:'Fin doit être après début.' }); return; }

        const fd = new FormData();
        fd.append('action',    id ? 'update_creneau' : 'add_creneau');
        fd.append('id',         id);
        fd.append('id_tuteur',  tuteurId);
        fd.append('titre',      titre);
        fd.append('debut',      debut.replace('T',' ') + ':00');
        fd.append('fin',        fin.replace('T',' ')   + ':00');
        fd.append('couleur',    couleur);
        if (recurrent) fd.append('recurrent', '1');

        fetch('ajax_tuteur.php', { method:'POST', body:fd })
            .then(r => r.json())
            .then(data => {
                closeModal();
                if (data.success) {
                    Toast.fire({ icon:'success', title: data.message });
                    setTimeout(() => location.reload(), 900);
                } else {
                    Toast.fire({ icon:'error', title: data.message });
                }
            })
            .catch(err => Toast.fire({ icon:'error', title: err.message }));
    }

    // ── SUPPRESSION D'UN CRÉNEAU ──────────────────────────────────
    function deleteCreneau(id, titre) {
        Swal.fire({
            title: 'Supprimer ce créneau ?',
            html: `<b>${titre}</b>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '🗑 Supprimer',
            cancelButtonText: 'Annuler'
        }).then(r => {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('action', 'delete_creneau');
            fd.append('id', id);
            fetch('ajax_tuteur.php', { method:'POST', body:fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Toast.fire({ icon:'success', title: d.message });
                        setTimeout(() => location.reload(), 900);
                    } else {
                        Toast.fire({ icon:'error', title: d.message });
                    }
                });
        });
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
                    Swal.fire({
                        title: '📚 ' + props.titre,
                        html: `
                            <div style="text-align:left;font-size:.88rem;">
                                <p style="margin:.3rem 0;"><b>👨‍🏫 Tuteur :</b> ${props.tuteur_nom}</p>
                                <p style="margin:.3rem 0;"><b>📅 Date :</b> ${e.start.toLocaleDateString('fr-FR')}</p>
                                <p style="margin:.3rem 0;"><b>🎓 Niveau :</b> ${props.niveau}</p>
                                <p style="margin:.3rem 0;"><b>🏷️ Domaine :</b> ${props.domaine}</p>
                                <p style="margin:.3rem 0;"><b>📍 Format :</b> ${props.lieu}</p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#6366f1',
                    });
                    return;
                }

                // ── Créneau tuteur (éditable) ─────────────────────
                Swal.fire({
                    title: '📅 ' + props.titre,
                    html: `
                        <div style="text-align:left;font-size:.88rem;">
                            <p style="margin:.3rem 0;"><b>👨‍🏫 Tuteur :</b> ${props.tuteur_nom}</p>
                            <p style="margin:.3rem 0;"><b>🕐 Début :</b> ${e.start.toLocaleString('fr-FR')}</p>
                            <p style="margin:.3rem 0;"><b>🕔 Fin :</b> ${e.end ? e.end.toLocaleString('fr-FR') : '—'}</p>
                            ${props.recurrent ? '<p style="margin:.5rem 0;"><span style="background:rgba(99,102,241,.1);color:#6366f1;padding:.2rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700;">🔁 Récurrent</span></p>' : ''}
                        </div>
                    `,
                    showDenyButton:   true,
                    showCancelButton: true,
                    confirmButtonText: '✏️ Modifier',
                    denyButtonText:   '🗑 Supprimer',
                    cancelButtonText: 'Fermer',
                    confirmButtonColor: '#6366f1',
                    denyButtonColor:    '#ef4444',
                }).then(result => {
                    if (result.isConfirmed) {
                        document.getElementById('creneau-id').value    = e.id;
                        document.getElementById('creneau-tuteur').value= props.id_tuteur;
                        document.getElementById('creneau-titre').value = props.titre;
                        const toLocal = d => d ? d.toISOString().slice(0,16) : '';
                        document.getElementById('creneau-debut').value = toLocal(e.start);
                        document.getElementById('creneau-fin').value   = toLocal(e.end || e.start);
                        document.getElementById('creneau-couleur').value = e.backgroundColor || '#6366f1';
                        document.getElementById('creneau-recurrent').checked = props.recurrent;
                        document.getElementById('modal-creneau').style.display = 'flex';
                    } else if (result.isDenied) {
                        deleteCreneau(e.id, props.titre);
                    }
                });
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
        fetch('ajax_tuteur.php', { method:'POST', body:fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Toast.fire({ icon:'success', title:'Créneau déplacé ✓', timer:1500 });
                } else {
                    revert();
                    Toast.fire({ icon:'error', title: d.message });
                }
            })
            .catch(() => revert());
    }

    // ── VUE PAR DÉFAUT ───────────────────────────────────────────
    document.querySelector('[data-view="list"]').classList.add('active');
</script>