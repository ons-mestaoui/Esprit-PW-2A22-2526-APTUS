<?php
$pageTitle = "Dashboard Tuteur — Aptus";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/UserC.php';

$formationC = new FormationController();
$userC = new UserC();

// 1. Gérer l'action AJAX pour le calendrier
if (isset($_GET['action']) && $_GET['action'] == 'getCalendarEvents') {
    $id_tuteur = $_GET['id_tuteur'] ?? 0;
    $formationC->getCalendarEventsJSON($id_tuteur);
    exit();
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Logique flexible : on prend l'ID dans l'URL (pour tester) OU l'ID en session (pour l'intégration finale)
$id_tuteur = $_GET['tuteur_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 1;

require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
$dashC = new TuteurDashboardController();
$stats = $dashC->getGlobalStats($id_tuteur);

$tuteur = $userC->getUserById($id_tuteur);
$formations = $formationC->getFormationsByTuteur($id_tuteur);

if (!isset($content)) {
    $content = __FILE__;
    $userName = $tuteur['nom'] ?? 'Tuteur';
    $userRole = 'Tuteur';
    include 'layout_front.php';
    exit();
}
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<!-- Tippy.js for Premium Tooltips -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/shift-away.css" />
<style>
    .dashboard-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
        margin-top: 2rem;
    }

    @media (max-width: 1024px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }

    .calendar-container {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: var(--border-color);
    }

    .fc-header-toolbar {
        margin-bottom: 2rem !important;
    }

    .fc-button-primary {
        background-color: var(--accent-primary) !important;
        border-color: var(--accent-primary) !important;
        text-transform: capitalize;
        font-weight: 500;
        border-radius: 8px !important;
    }

    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        border-radius: 4px;
        border: none;
    }

    .sidebar-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .side-card {
        background: var(--bg-card);
        padding: 1.25rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .side-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .side-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .side-card__title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .side-card__meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stats-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .mini-stat {
        background: var(--bg-surface);
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    .mini-stat__value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent-primary);
    }

    .mini-stat__label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Advanced Tooltip Styling */
    .tippy-box[data-theme~='aptus-dark'] {
        background-color: #1e293b;
        color: white;
        border-radius: 12px;
        padding: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tooltip-content {
        padding: 8px;
    }

    .tooltip-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tooltip-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .tooltip-stats {
        font-size: 0.8rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Color Bar for Sidebar Cards */
    .side-card {
        position: relative;
        overflow: hidden;
        padding-left: 1.5rem;
    }

    .side-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
    }

    .side-card.online::before {
        background: #3498db;
    }

    .side-card.offline::before {
        background: #2ecc71;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.urgent {
        background: #fee2e2;
        color: #ef4444;
    }

    .status-badge.upcoming {
        background: #f3e8ff;
        color: #a855f7;
    }
</style>

<div class="tuteur-dashboard-header">
    <div class="welcome-section" style="margin-bottom: 2.5rem;">
        <h1 class="gradient-text" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Bienvenue,
            <?php echo htmlspecialchars($tuteur['nom'] ?? 'Mme Dupont'); ?> 👋</h1>
        <p class="text-secondary">Voici l'aperçu de vos formations et de votre calendrier d'enseignement.</p>
    </div>

    <div class="stats-summary">
        <div class="mini-stat">
            <div class="mini-stat__value"><?php echo $stats['total_students']; ?></div>
            <div class="mini-stat__label">Étudiants Total</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__value"><?php echo $stats['completed']; ?></div>
            <div class="mini-stat__label">Terminés</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat__value">
                <?php echo $stats['taux']; ?>%
            </div>
            <div class="mini-stat__label">Taux de Complétion</div>
        </div>
    </div>
</div>

<div class="dashboard-layout">
    <!-- CALENDRIER -->
    <div class="calendar-container">
        <h3 class="mb-4 d-flex align-items-center gap-2">
            <i data-lucide="calendar" style="color:var(--accent-primary);"></i>
            Planning des Formations
        </h3>
        <div id='calendar'></div>
    </div>

    <!-- LISTE LATERALE -->
    <div class="sidebar-list">
        <h3 class="mb-2 d-flex align-items-center gap-2">
            <i data-lucide="list-checks" style="color:var(--accent-primary);"></i>
            Mes Assignations
        </h3>

        <?php if (empty($formations)): ?>
            <div class="side-card" style="text-align: center; opacity: 0.6;">
                <p>Aucune formation assignée.</p>
            </div>
        <?php else: ?>
            <?php foreach ($formations as $f):
                $dateDiff = (strtotime($f['date_formation']) - strtotime(date('Y-m-d'))) / 86400;
                $statusText = "";
                $statusClass = "";
                if ($dateDiff == 0) {
                    $statusText = "Aujourd'hui";
                    $statusClass = "urgent";
                } elseif ($dateDiff == 1) {
                    $statusText = "Demain !";
                    $statusClass = "urgent";
                } elseif ($dateDiff > 0) {
                    $statusText = "Dans " . round($dateDiff) . "j";
                    $statusClass = "upcoming";
                } else {
                    $statusText = "Passé";
                    $statusClass = "";
                }
                ?>
                <div class="side-card <?php echo $f['is_online'] ? 'online' : 'offline'; ?>">
                    <div class="side-card__header">
                        <div>
                            <div class="side-card__title"><?php echo htmlspecialchars($f['titre']); ?></div>
                            <div class="side-card__meta">
                                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                <?php echo date('H:i', strtotime($f['date_formation'])); ?> (7 jours)
                            </div>
                        </div>
                        <?php if ($statusText): ?>
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="side-card__body">
                        <div class="side-card__meta" style="margin-top: 5px;">
                            <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                            <?php echo date('d M Y', strtotime($f['date_formation'])); ?>
                        </div>
                    </div>
                    <div class="side-card__footer"
                        style="display:flex; justify-content: space-between; align-items: center; margin-top: 1rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1rem;">
                        <span style="font-size: 0.8rem; font-weight: 500; opacity: 0.7;">
                            <i data-lucide="users" style="width:14px;height:14px; vertical-align:middle; margin-right:4px;"></i>
                            <?php echo $f['nb_inscrits']; ?> inscrit(s)
                        </span>

                        <div style="display: flex; gap: 8px;">
                            <?php if ($f['is_online']): ?>
                                <a href="jitsi_room.php?id_formation=<?php echo $f['id_formation']; ?>&url=<?php echo urlencode($f['lien_api_room'] ?? '#'); ?>&role=tuteur" target="_blank"
                                    class="btn btn-sm"
                                    style="background: #3498db; color: white; display: flex; align-items: center; gap: 4px; padding: 4px 12px; font-size: 0.75rem; border-radius: 6px; text-decoration: none;">
                                    <i data-lucide="video" style="width:14px;height:14px;"></i>
                                    Room
                                </a>
                            <?php endif; ?>
                            <a href="tuteur_formation_manage.php?id=<?php echo $f['id_formation']; ?>"
                                class="text-sm fw-medium"
                                style="color:var(--text-primary); text-decoration:none; display: flex; align-items: center; gap: 2px;">
                                Gérer &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            firstDay: 1, // Commencer par Lundi
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine'
            },
            events: 'tuteur_dashboard.php?action=getCalendarEvents&id_tuteur=<?php echo $id_tuteur; ?>',

            eventDidMount: function (info) {
                const props = info.event.extendedProps;
                const typeLabel = props.is_online ? '🌐 EN LIGNE' : '📍 PRÉSENTIEL';
                const badgeColor = props.is_online ? '#3498db' : '#2ecc71';

                tippy(info.el, {
                    content: `
                    <div class="tooltip-content">
                        <div class="tooltip-header" style="color:${badgeColor}">
                            ${typeLabel}
                        </div>
                        <div class="tooltip-title">${info.event.title}</div>
                        <div class="tooltip-stats">
                            <i data-lucide="users" style="width:12px;height:12px;"></i>
                            ${props.nb_inscrits} participants inscrits
                        </div>
                        <div style="margin-top:8px; font-size:0.75rem; opacity:0.6; font-style:italic;">
                            ${props.description || 'Pas de description'}
                        </div>
                    </div>
                `,
                    allowHTML: true,
                    theme: 'aptus-dark',
                    placement: 'top',
                    animation: 'shift-away',
                    onShown() { lucide.createIcons(); }
                });
            },

            eventClick: function (info) {
                // Optionnel : redirection vers détails
            }
        });
        calendar.render();
    });
</script>