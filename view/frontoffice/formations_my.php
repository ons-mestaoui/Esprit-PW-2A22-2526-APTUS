<?php
// Session nécessaire pour les messages flash (succès/erreur)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Mes Formations - Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/InscriptionController.php';

    $inscriptionC = new InscriptionController();

    // On prend l'ID dans l'URL (test), sinon en Session (inté), sinon 10 (fallback)
    $id_user = $_GET['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;

    // Le contrôleur appelle le Model qui vérifie les contraintes (date, statut)
    // Le contrôleur appelle le Model qui vérifie les contraintes (date, statut)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
        $inscriptionC->desinscrire();
    }

    // Terminer une formation : on vérifie en PHP que la date n'est pas dans le futur
    if (isset($_GET['finish_id'])) {
        try {
            $inscriptionC->terminerFormation((int) $_GET['finish_id'], $id_user);
            $_SESSION['flash_success'] = "Bravo, formation terminée !";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        header("Location: formations_my.php");
        exit();
    }

    $mesCours = $inscriptionC->listerMesFormations($id_user);

    // Compute stats for the welcome banner
    $totalCours = count($mesCours);
    $completedCours = 0;
    $enCoursCours = 0;
    $annuleeCours = 0;
    foreach ($mesCours as $c) {
        if ($c['progression'] == 100 || $c['statut'] === 'Terminée') {
            $completedCours++;
        } elseif ($c['statut'] === 'annulée') {
            $annuleeCours++;
        } else {
            $enCoursCours++;
        }
    }

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    /* ── Welcome Banner ── */
    .welcome-banner {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        position: relative;
        overflow: hidden;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
    }

    .welcome-banner__info h1 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .welcome-banner__info p {
        color: var(--text-secondary);
        margin: 0;
    }

    .welcome-banner__stats {
        display: flex;
        gap: 1.5rem;
        flex-shrink: 0;
    }

    .mini-stat-card {
        text-align: center;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        min-width: 80px;
    }

    .mini-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .mini-stat-card__value.cyan {
        color: var(--primary-cyan);
    }

    .mini-stat-card__value.green {
        color: #10b981;
    }

    .mini-stat-card__value.purple {
        color: var(--accent-primary);
    }

    .mini-stat-card__label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
        font-weight: 600;
    }

    /* ── Global Progress Ring ── */
    .global-progress {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .global-progress__bar {
        flex: 1;
        height: 10px;
        background: var(--bg-tertiary);
        border-radius: 999px;
        overflow: hidden;
    }

    .global-progress__fill {
        height: 100%;
        border-radius: 999px;
        background: var(--gradient-primary);
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .global-progress__fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        from {
            transform: translateX(-100%);
        }

        to {
            transform: translateX(100%);
        }
    }

    .global-progress__text {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary-cyan);
        white-space: nowrap;
    }

    /* ── Tabs ── */
    .formations-tabs {
        display: flex;
        gap: 0.25rem;
        background: var(--bg-card);
        padding: 0.35rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
        width: fit-content;
    }

    .formations-tab {
        padding: 0.6rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .formations-tab:hover {
        color: var(--text-primary);
        background: var(--bg-secondary);
    }

    .formations-tab.active {
        background: var(--gradient-primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(107, 52, 163, 0.3);
    }

    .formations-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0 6px;
    }

    .formations-tab.active .formations-tab__count {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .formations-tab:not(.active) .formations-tab__count {
        background: var(--bg-tertiary);
        color: var(--text-secondary);
    }

    /* ── Formation Card ── */
    .formation-card {
        padding: 1.25rem;
        background: var(--bg-card);
        color: var(--text-primary);
        border-radius: 14px;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .formation-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        border-color: var(--primary-cyan);
    }

    .formation-card__image {
        width: 100%;
        height: 160px;
        border-radius: 10px;
        margin-bottom: 1rem;
        overflow: hidden;
        position: relative;
    }

    .formation-card__image img,
    .formation-card__image-bg {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .formation-card__image-bg {
        background: var(--gradient-primary);
        opacity: 0.15;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .welcome-banner {
            flex-direction: column;
            text-align: center;
        }

        .welcome-banner__stats {
            justify-content: center;
        }

        .formations-tabs {
            width: 100%;
            justify-content: stretch;
        }

        .formations-tab {
            flex: 1;
            justify-content: center;
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }
    }

    /* ── Feature 2 : Pulse Contextuel sur bouton Certificat ── */
    @keyframes certifPulse {
        0%   { box-shadow: 0 0 0 0   rgba(139,92,246,0.5); }
        70%  { box-shadow: 0 0 0 14px rgba(139,92,246,0);  }
        100% { box-shadow: 0 0 0 0   rgba(139,92,246,0);  }
    }
    .btn-certif-pulse {
        animation: certifPulse 2s ease-out infinite;
        background: var(--gradient-primary) !important;
        border-color: transparent !important;
    }
</style>

<!-- ═══════════════════════════════════════════
     WELCOME BANNER + STATS
     ═══════════════════════════════════════════ -->
<div class="welcome-banner">
    <div class="welcome-banner__info">
        <h1>Mon Parcours d'Apprentissage</h1>
        <?php if ($totalCours > 0): ?>
            <p>Vous avez complété <strong><?php echo $completedCours; ?></strong>
                formation<?php echo $completedCours > 1 ? 's' : ''; ?> sur <strong><?php echo $totalCours; ?></strong>.
                <?php echo $completedCours == $totalCours && $totalCours > 0 ? '🎉 Bravo, parcours complet !' : 'Continuez comme ça !'; ?>
            </p>
            <div class="global-progress">
                <div class="global-progress__bar">
                    <div class="global-progress__fill"
                        style="width: <?php echo $totalCours > 0 ? round(($completedCours / $totalCours) * 100) : 0; ?>%;">
                    </div>
                </div>
                <span
                    class="global-progress__text"><?php echo $totalCours > 0 ? round(($completedCours / $totalCours) * 100) : 0; ?>%</span>
            </div>
        <?php else: ?>
            <p>Commencez votre parcours en explorant notre catalogue de formations.</p>
        <?php endif; ?>
    </div>

    <?php if ($totalCours > 0): ?>
        <div class="welcome-banner__stats">
            <div class="mini-stat-card">
                <div class="mini-stat-card__value cyan"><?php echo $enCoursCours; ?></div>
                <div class="mini-stat-card__label">En cours</div>
            </div>
            <div class="mini-stat-card">
                <div class="mini-stat-card__value green"><?php echo $completedCours; ?></div>
                <div class="mini-stat-card__label">Terminées</div>
            </div>
            <div class="mini-stat-card">
                <div class="mini-stat-card__value purple"><?php echo $totalCours; ?></div>
                <div class="mini-stat-card__label">Total</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════
     TABS
     ═══════════════════════════════════════════ -->
<?php if ($totalCours > 0): ?>
    <div class="formations-tabs" id="formationsTabs">
        <button class="formations-tab active" data-filter="all">
            Toutes <span class="formations-tab__count"><?php echo $totalCours; ?></span>
        </button>
        <button class="formations-tab" data-filter="en-cours">
            En cours <span class="formations-tab__count"><?php echo $enCoursCours; ?></span>
        </button>
        <button class="formations-tab" data-filter="terminee">
            Terminées <span class="formations-tab__count"><?php echo $completedCours; ?></span>
        </button>
        <?php if ($annuleeCours > 0): ?>
            <button class="formations-tab" data-filter="annulee">
                Annulées <span class="formations-tab__count"><?php echo $annuleeCours; ?></span>
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     CARDS GRID
     ═══════════════════════════════════════════ -->
<div class="grid" id="formationsGrid"
    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    <?php if (!empty($mesCours)):
        foreach ($mesCours as $cours):
            // Determine filter category
            $filterCat = 'en-cours';
            if ($cours['progression'] == 100 || $cours['statut'] === 'Terminée')
                $filterCat = 'terminee';
            if ($cours['statut'] === 'annulée')
                $filterCat = 'annulee';
            ?>
            <div class="formation-card" data-category="<?php echo $filterCat; ?>">
                <!-- Image -->
                <div class="formation-card__image">
                    <?php if (!empty($cours['image_base64'])): ?>
                        <div class="formation-card__image-bg"
                            style="background: url('<?php echo $cours['image_base64']; ?>') center/cover; opacity: 1;"></div>
                    <?php else: ?>
                        <div class="formation-card__image-bg"></div>
                    <?php endif; ?>
                </div>

                <!-- Badges -->
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="badge badge-info"
                        style="font-size: 0.7rem;"><?php echo ($cours['is_online']) ? '🌐 En ligne' : '📍 Présentiel'; ?></span>
                    <span
                        class="badge <?php echo ($cours['statut'] == 'annulée') ? 'badge-danger' : ($filterCat === 'terminee' ? 'badge-success' : 'badge-neutral'); ?>"
                        style="font-size: 0.7rem;">
                        <?php echo htmlspecialchars($cours['statut']); ?>
                    </span>
                </div>

                <!-- Title & Tutor -->
                <h2 style="font-size: 1.2rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($cours['titre']); ?></h2>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">
                    Tuteur : <b><?php echo htmlspecialchars($cours['tuteur_nom'] ?? 'Aptus'); ?></b>
                </div>

                <!-- Progress Bar -->
                <div style="margin-bottom: 1.25rem;">
                    <div
                        style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem; font-weight: 500;">
                        <label style="color: var(--text-secondary);">Progression</label>
                        <span style="color: var(--primary-cyan); font-weight: 700;"><?php echo $cours['progression']; ?>%</span>
                    </div>
                    <div class="global-progress__bar" style="height: 8px;">
                        <div class="global-progress__fill" style="width: <?php echo $cours['progression']; ?>%; <?php if ($cours['progression'] == 100)
                               echo 'background: var(--gradient-primary);'; ?>">
                        </div>
                    </div>
                </div>

                <!-- Actions based on status -->
                <?php if ($cours['statut'] === 'annulée'): ?>
                    <div
                        style="background: var(--accent-tertiary-light); color: var(--accent-tertiary); padding: 0.75rem; border-radius: 10px; text-align: center; font-weight: 600; font-size: 0.85rem;">
                        ⚠️ Formation annulée
                    </div>
                <?php elseif ($cours['progression'] == 100 || $cours['statut'] === 'Terminée'): ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div
                            style="background: rgba(139, 92, 246, 0.1); color: var(--primary-purple); padding: 0.75rem; border-radius: 10px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 600;">
                            <span style="font-size: 1.3rem;">🎓</span> Badge "Expert" Acquis !
                        </div>
                        <a href="certificate.php?f_id=<?php echo $cours['id_formation']; ?>" target="_blank" 
                            class="btn btn-primary btn-certif-pulse"
                            style="text-align:center;">
                            🎓 Générer mon Certificat
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: auto;">
                        <?php if ($cours['is_online']): ?>
                            <a href="jitsi_room.php?id_formation=<?php echo $cours['id_formation']; ?>&url=<?php echo urlencode($cours['lien_api_room'] ?? '#'); ?>" target="_blank" class="btn"
                                style="background: var(--accent-info); color: white; text-align:center; padding: 0.5rem; text-decoration:none; border-radius:8px;">📹
                                Rejoindre la Room</a>
                        <?php endif; ?>

                        <!-- Contrainte : accès si date_formation <= aujourd'hui (comparaison DATE sans heure) -->
                        <?php
                            $dateFormation = date('Y-m-d', strtotime($cours['date_formation']));
                            $isAvailable = ($dateFormation <= date('Y-m-d'));
                        ?>
                        <?php if ($isAvailable): ?>
                            <a href="formation_viewer.php?id=<?php echo $cours['id_formation']; ?>" class="btn btn-primary"
                                style="text-align:center;">📖 Accéder au cours</a>
                        <?php else: ?>
                            <button class="btn"
                                style="background: var(--bg-tertiary); color: var(--text-tertiary); cursor: not-allowed; width:100%; border:none; padding:0.5rem; border-radius:8px;"
                                disabled>🔒 Disponible le <?php echo date('d/m/Y', strtotime($cours['date_formation'])); ?></button>
                            <form action="formations_my.php" method="POST" style="margin: 0;"
                                onsubmit="return confirmDesinscription(this, event, '<?php echo addslashes(htmlspecialchars($cours['titre'])); ?>');">
                                <input type="hidden" name="id_formation" value="<?php echo $cours['id_formation']; ?>">
                                <button type="submit" class="btn"
                                    style="width: 100%; background: transparent; color: var(--accent-tertiary); border: 1px solid var(--accent-tertiary); padding: 0.5rem; border-radius: 8px;">Se désinscrire</button>
                            </form>
                        <?php endif; ?>

                        <!-- CONCEPT 1 : Bouton "Demander de l'aide" — Peer Learning -->
                        <!-- Visible uniquement si la progression est < 100% -->
                        <button class="btn btn-primary btn-peer-help" id="peer-btn-<?php echo $cours['id_formation']; ?>"
                            onclick="demanderAide(<?php echo $cours['id_formation']; ?>, this)"
                            style="width:100%; border:none; padding:0.5rem; border-radius:8px; cursor:pointer; font-weight:600; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                            🤝 Demander de l'aide
                        </button>
                    </div>
                <?php endif; ?>


            </div>
        <?php endforeach; else: ?>
        <!-- Beautiful Empty State -->
        <div style="grid-column: 1/-1;" class="empty-state">
            <div class="empty-state__icon"
                style="width: 100px; height: 100px; background: var(--gradient-primary); opacity: 0.15; position: relative;">
                <i data-lucide="book-open" style="width: 44px; height: 44px; opacity: 1;"></i>
            </div>
            <h3 class="empty-state__title" style="font-size: 1.3rem;">Votre parcours commence ici</h3>
            <p class="empty-state__text">Vous n'êtes inscrit à aucune formation pour le moment. Explorez notre catalogue
                pour trouver le programme parfait pour vos objectifs.</p>
            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; justify-content: center;">
                <a href="formations_catalog.php" class="btn btn-primary">
                    <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    Explorer le catalogue
                </a>
                <a href="skill_tree.php" class="btn btn-secondary">
                    <i data-lucide="git-branch" style="width: 16px; height: 16px;"></i>
                    Voir le Skill Tree
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // ================================================================
    // TABS FILTERING
    // ================================================================
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.formations-tab');
        const cards = document.querySelectorAll('.formation-card');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');

                // Filter cards with animation
                cards.forEach(card => {
                    const cat = card.getAttribute('data-category');
                    if (filter === 'all' || cat === filter) {
                        card.style.display = 'flex';
                        card.style.animation = 'fadeSlideUp 0.3s ease forwards';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Animate progress bars on load
        document.querySelectorAll('.global-progress__fill').forEach(bar => {
            const target = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = target; }, 200);
        });
    });

    // Fade animation
    const styleSheet = document.createElement('style');
    styleSheet.textContent = `
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    `;
    document.head.appendChild(styleSheet);

    // ================================================================
    // CONCEPT 1 : Peer Learning — Demander de l'aide via AJAX
    // ================================================================

    /**
     * Envoie une requête AJAX au PeerLearningController pour trouver un mentor.
     * Si trouvé → affiche une modale SweetAlert2 avec le lien Jitsi.
     * Sinon   → affiche un toast d'information.
     *
     * @param {number} idFormation  L'ID de la formation pour laquelle on demande de l'aide.
     * @param {Element} btn         Le bouton qui a été cliqué (pour le feedback visuel).
     */
    function demanderAide(idFormation, btn) {
        // Feedback visuel immédiat : spinner sur le bouton
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;animation:spin .8s linear infinite;">⏳</span> Recherche d\'un expert...';

        // Requête AJAX POST vers ajax_handler.php
        const formData = new FormData();
        formData.append('id_formation', idFormation);

        fetch('ajax_handler.php?action=peer_help', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                // Vérification du Content-Type pour éviter les erreurs JSON sur erreurs PHP
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse serveur invalide (attendu JSON).');
                }
                return response.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHTML;

                if (data.success) {
                    // ✅ Mentor trouvé → Modale de succès avec le lien Jitsi
                    Swal.fire({
                        title: '🎉 Expert trouvé !',
                        html: `
                        <div style="text-align:left; font-family:var(--font-family);">
                            <p style="margin-bottom:1.25rem; color:var(--text-primary); font-size:1rem;">
                                <strong>${data.mentor.mentor_nom}</strong> est disponible pour vous aider sur cette formation !
                            </p>
                            <div style="background:var(--accent-secondary-light);
                                        border:1px solid var(--accent-secondary); border-radius:var(--radius-md);
                                        padding:1.25rem; margin-bottom:1.5rem; box-shadow:var(--shadow-sm);">
                                <p style="font-size:var(--fs-xs); color:var(--accent-secondary-dark); font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
                                    <i data-lucide="link" style="width:14px;height:14px;"></i> Votre salle de réunion privée
                                </p>
                                <code style="font-size:var(--fs-sm); word-break:break-all; color:var(--text-primary); background:rgba(255,255,255,0.5); padding:0.5rem; border-radius:var(--radius-xs); display:block; border:1px dashed var(--accent-secondary);">
                                    ${data.jitsi_link}
                                </code>
                            </div>
                            <p style="font-size:var(--fs-xs); color:var(--text-tertiary); display:flex; align-items:center; gap:0.5rem;">
                                <i data-lucide="info" style="width:14px;height:14px;"></i> Le lien est unique et sécurisé pour votre session.
                            </p>
                        </div>
                    `,
                        icon: 'success',
                        iconColor: 'var(--accent-secondary)',
                        showCancelButton: true,
                        confirmButtonText: '📹 Rejoindre la salle',
                        cancelButtonText: 'Copier le lien',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'card-flat animate-scale-in',
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-secondary',
                            actions: 'gap-3 mt-4'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Ouvrir Jitsi dans un nouvel onglet
                            window.open('jitsi_room.php?id_formation=' + idFormation + '&url=' + encodeURIComponent(data.jitsi_link), '_blank', 'noopener');
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Copier le lien dans le presse-papier
                            navigator.clipboard.writeText(data.jitsi_link).then(() => {
                                Toast.fire({ icon: 'success', title: 'Lien copié dans le presse-papier !' });
                            });
                        }
                    });
                } else {
                    // ❌ Aucun mentor disponible → Toast informatif
                    Toast.fire({
                        icon: 'info',
                        title: data.message || 'Aucun expert disponible pour le moment.'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur réseau : ' + err.message
                });
            });
    }

    // ================================================================
    // Confirmation de désinscription avec SweetAlert2 (inchangé)
    // ================================================================
    function confirmDesinscription(form, event, formationTitre) {
        event.preventDefault();
        Swal.fire({
            title: 'Se désinscrire ?',
            html: `Êtes-vous sûr de vouloir annuler votre inscription à la formation <br><b>"${formationTitre}"</b> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#e2e8f0',
            confirmButtonText: 'Oui, me désinscrire',
            cancelButtonText: '<span style="color:var(--text-primary)">Annuler</span>',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>