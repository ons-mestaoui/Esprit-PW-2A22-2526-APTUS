<?php 
// Session nécessaire pour les messages flash (succès/erreur)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Mes Formations - Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/InscriptionController.php';
    
    $inscriptionC = new InscriptionController();
    $id_user = 10; // Demo User
    
    // Si le candidat clique sur "Se désinscrire" -> traitement POST
    // Le contrôleur appelle le Model qui vérifie les contraintes (date, statut)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
        $inscriptionC->desinscrire();
    }

    // Terminer une formation : on vérifie en PHP que la date n'est pas dans le futur
    if (isset($_GET['finish_id'])) {
        try {
            $inscriptionC->terminerFormation((int)$_GET['finish_id'], $id_user);
            $_SESSION['flash_success'] = "Bravo, formation terminée !";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        header("Location: formations_my.php");
        exit();
    }
    
    $mesCours = $inscriptionC->listerMesFormations($id_user);
    
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="page-header" style="text-align: left; margin-bottom: 2rem;">
  <h1 class="page-header__title">Mon Parcours d'Apprentissage</h1>
</div>



<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    <?php if (!empty($mesCours)): foreach($mesCours as $cours): ?>
    <div class="card-flat card-formation-hover" style="padding: 1.25rem; background: var(--bg-card); color: var(--text-primary); border-radius: 12px; border: 1px solid var(--border-color);">
        <?php if (!empty($cours['image_base64'])): ?>
            <div style="width:100%; height:150px; background: url('<?php echo $cours['image_base64']; ?>') center/cover; border-radius:8px; margin-bottom:1rem;"></div>
        <?php else: ?>
            <div style="width:100%; height:150px; background: linear-gradient(135deg, var(--primary-cyan), var(--accent-primary)); opacity: 0.1; border-radius:8px; margin-bottom:1rem;"></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span class="badge badge-info" style="font-size: 0.7rem;"><?php echo ($cours['is_online']) ? '🌐 En ligne' : '📍 Présentiel'; ?></span>
            <span class="badge <?php echo ($cours['statut'] == 'annulée') ? 'badge-danger' : 'badge-neutral'; ?>" style="font-size: 0.7rem;">
                <?php echo htmlspecialchars($cours['statut']); ?>
            </span>
        </div>

        <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($cours['titre']); ?></h2>
        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
            Tuteur : <b><?php echo htmlspecialchars($cours['tuteur_nom'] ?? 'Aptus'); ?></b>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem; font-weight: 500;">
                <label style="color: var(--text-secondary);">Progression</label>
                <span style="color: var(--primary-cyan);"><?php echo $cours['progression']; ?>%</span>
            </div>
            <div style="background: var(--bg-tertiary); border-radius: 8px; width: 100%; height: 8px; overflow: hidden; position: relative;">
                <div style="width: <?php echo $cours['progression']; ?>%; height: 100%; background: var(--gradient-primary); border-radius: 8px; transition: width 1s ease;"></div>
            </div>
        </div>

        <!-- Si la formation est annulée par l'admin, on bloque toutes les actions -->
        <?php if ($cours['statut'] === 'annulée'): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; text-align: center; font-weight: bold;">
                Formation annulée
            </div>
        <?php elseif ($cours['progression'] == 100 || $cours['statut'] === 'Terminée'): ?>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 1rem; border-radius: 12px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 600;">
                    <span style="font-size: 1.5rem;">🎓</span> Badge "Expert" Acquis !
                </div>
                <a href="certificate.php?f_id=<?php echo $cours['id_formation']; ?>" target="_blank" class="btn btn-primary" style="text-align:center;">
                    Générer mon Certificat
                </a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php if ($cours['is_online']): ?>
                    <a href="<?php echo htmlspecialchars($cours['lien_api_room'] ?? '#'); ?>" target="_blank" class="btn" style="background: #3b82f6; color: white; text-align:center; padding: 0.5rem; text-decoration:none; border-radius:6px;">📹 Rejoindre la Room</a>
                <?php endif; ?>
                
                <!-- Contrainte : le bouton "Terminer" n'apparaît que si la date est passée -->
                <?php if ($cours['date_formation'] <= date('Y-m-d')): ?>
                    <a href="formations_my.php?finish_id=<?php echo $cours['id_formation']; ?>" class="btn btn-primary" style="text-align:center;">Terminer la formation</a>
                <?php else: ?>
                    <button class="btn" style="background: #e2e8f0; color: #94a3b8; cursor: not-allowed; width:100%; border:none; padding:0.5rem; border-radius:6px;" disabled>Disponible le <?php echo date('d/m', strtotime($cours['date_formation'])); ?></button>
                    
                    <!-- Bouton pour se désinscrire avec SweetAlert2 -->
                    <form action="formations_my.php" method="POST" style="margin: 0;" onsubmit="return confirmDesinscription(this, event, '<?php echo addslashes(htmlspecialchars($cours['titre'])); ?>');">>
                        <input type="hidden" name="id_formation" value="<?php echo $cours['id_formation']; ?>">
                        <button type="submit" class="btn" style="width: 100%; background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 0.5rem; border-radius: 6px;">Se désinscrire</button>
                    </form>
                <?php endif; ?>

                <!-- CONCEPT 1 : Bouton "Demander de l'aide" — Peer Learning -->
                <!-- Visible uniquement si la progression est < 100% -->
                <button
                    class="btn btn-primary btn-peer-help"
                    id="peer-btn-<?php echo $cours['id_formation']; ?>"
                    onclick="demanderAide(<?php echo $cours['id_formation']; ?>, this)"
                    style="width:100%; border:none; padding:0.5rem; border-radius:6px; cursor:pointer; font-weight:600; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                    🤝 Demander de l'aide
                </button>
            </div>
        <?php endif; ?>

        <?php if ($cours['progression'] == 100 || $cours['statut'] === 'Terminée'): /* Déjà terminé : lien vers Soft-Skills */ ?>
            <!-- CONCEPT 3 : Évaluateur Soft-Skills (accès depuis la carte terminée) -->
            <div style="margin-top:0.75rem;">
                <a href="softskills_evaluator.php?id=<?php echo $cours['id_formation']; ?>"
                   style="display:flex; align-items:center; justify-content:center; gap:0.5rem;
                          padding:0.5rem; border-radius:6px; font-size:0.82rem; text-decoration:none;
                          background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(139,92,246,0.1));
                          color:#6366f1; border:1px solid rgba(99,102,241,0.25); font-weight:600;
                          transition:all .2s;" 
                   onmouseover="this.style.background='linear-gradient(135deg,#6366f1,#8b5cf6)';this.style.color='white';"
                   onmouseout="this.style.background='linear-gradient(135deg,rgba(99,102,241,0.1),rgba(139,92,246,0.1))';this.style.color='#6366f1';">
                    🧠 Valider via Soft-Skills
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; else: ?>
        <div style="grid-column: 1/-1;" class="empty-state">
            <div class="empty-state__icon">
                <i data-lucide="book-x" style="width: 40px; height: 40px;"></i>
            </div>
            <h3 class="empty-state__title">Vous n'avez aucune inscription</h3>
            <p class="empty-state__text">Vous n'êtes actuellement inscrit à aucune formation. Découvrez notre catalogue pour commencer votre parcours d'apprentissage.</p>
            <a href="formations_catalog.php" class="btn btn-primary" style="margin-top: 1rem;">Explorer le catalogue</a>
        </div>
    <?php endif; ?>
</div>

<script>
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
                        window.open(data.jitsi_link, '_blank', 'noopener');
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

    // Animation spin pour le bouton de chargement
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }';
    document.head.appendChild(style);

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
            cancelButtonText: '<span style="color:#0f172a">Annuler</span>',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>
