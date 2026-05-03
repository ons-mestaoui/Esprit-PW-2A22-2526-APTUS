<?php
$pageTitle = "Détails de la Formation";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';
    require_once __DIR__ . '/../../controller/SessionManager.php';
    SessionManager::start();

    // Fetch the formation detail data (MVC COMPLIANCE)
    $id = $_GET['id'] ?? 1;
    $id_user     = SessionManager::getUserId();
    $userRole    = $_SESSION['role'] ?? 'Etudiant';
    $formationC  = new FormationController();
    
    $detailData = $formationC->getFormationDetailData($id, $id_user);
    if (!$detailData) {
        header('Location: formations_catalog.php');
        exit();
    }

    $formation   = $detailData['formation'];
    $is_unlocked = $detailData['is_unlocked'];
    $prereq_titre= $detailData['prereq_titre'];
    $isInscribed = $detailData['isInscribed'];

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div style="max-width: 900px; margin: 0 auto; margin-top:2rem;">
    <a href="formations_catalog.php"
        style="text-decoration: none; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem; display: inline-block;">←
        Retour au catalogue</a>

    <div class="card-flat"
        style="padding: 0; overflow: hidden; display: flex; flex-direction: column; border-radius:12px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
        <?php if (!empty($formation['image_base64'])): ?>
            <div
                style="background: url('<?php echo $formation['image_base64']; ?>') center/cover; width: 100%; height: 350px;">
            </div>
        <?php else: ?>
            <div
                style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-purple)); opacity: 0.2; width: 100%; height: 350px;">
            </div>
        <?php endif; ?>

        <div style="padding: 3rem; display: flex; flex-direction: column;">
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <span class="badge badge-info"
                    style="font-size:0.75rem;"><?php echo $formation['domaine_safe']; ?></span>
                <span class="badge <?php echo $formation['niveau_class']; ?>"
                    style="font-size:0.75rem;"><?php echo $formation['niveau']; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0;"><?php echo $formation['titre_safe']; ?></h1>
            </div>

            <div
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <div>
                    <label style="opacity: 0.6; font-size: 0.75rem; text-transform: uppercase;">Tuteur</label>
                    <div style="font-weight: 600;">


                        <?php echo htmlspecialchars($formation['tuteur_nom'] ?? 'Équipe Aptus'); ?>
                    </div>
                </div>
                <div>
                    <label style="opacity: 0.6; font-size: 0.75rem; text-transform: uppercase;">Date</label>
                    <div style="font-weight: 600;"><?php echo $formation['date_format']; ?>
                    </div>
                </div>
                <div>
                    <label style="opacity: 0.6; fon
                        t-size: 0.75rem; text-transform: uppercase;">Durée</label>

                    <div style="font-weight: 600;">
                        <?php echo htmlspecialchars($formation['duree'] ?? 'Non spécifiée'); ?>
                    </div>
                </div>
                <div>


                    <label style="opacity: 0.6; font-size: 0.75rem; text-transform: uppercase;">Format</label>
                    <div style="font-weight: 600;">
                        <?php echo ($formation['is_online']) ? '🌐 En ligne' : '📍 Présentiel'; ?>
                    </div>
                </div>
            </div>
            <?php if (isset($errorMsg)): ?>
                <div
                    style="background: var(--accent-tertiary-light); color: var(--accent-tertiary); padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align:center; border: 1px solid var(--accent-tertiary);">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            <?php if ($userRole === 'Tuteur' && $formation['id_tuteur'] == $id_user): ?>
                <div style="background: var(--accent-info-light); color: var(--accent-info); padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid var(--accent-info);">
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">👨‍🏫 Vue Tuteur</div>
                    <p style="font-size: 0.9rem; margin-bottom: 1.5rem;">Vous êtes le formateur assigné à ce module.</p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="tuteur_dashboard.php" class="btn" style="flex:1; background: var(--bg-card); border: 1px solid var(--accent-info); color: var(--accent-info); text-decoration: none; padding: 0.75rem; border-radius: 8px; font-weight: 600;">Mon Dashboard</a>
                        <?php if($formation['is_online']): ?>
                            <a href="<?php echo htmlspecialchars($formation['lien_api_room'] ?? '#'); ?>" target="_blank" class="btn" style="flex:1; background: var(--accent-info); color: white; text-decoration: none; padding: 0.75rem; border-radius: 8px; font-weight: 600;">Lancer la Room</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (isset($isInscribed) && $isInscribed): ?>
                <div
                    style="background: var(--accent-secondary-light); color: var(--accent-secondary); padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid var(--accent-secondary); margin-bottom: 1rem;">
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">✅ Vous êtes déjà inscrit !
                    </div>
                    <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 1.5rem;">Vous pouvez retrouver cette formation
                        dans votre espace personnel.</p>
                    <a href="formations_my.php" class="btn btn-primary" style="width: 100%;">Accéder à Mes Cours</a>
                </div>
            <?php elseif (strtotime($formation['date_formation']) < strtotime(date('Y-m-d'))): ?>
                <div
                    style="background: rgba(156, 163, 175, 0.1); color: #6b7280; padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid rgba(156, 163, 175, 0.3);">
                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Inscriptions Closes</div>
                    <p style="font-size: 0.9rem;">Cette formation a déjà commencé ou est terminée.</p>
                </div>
            <?php elseif (!$is_unlocked): ?>
                <div style="background: var(--bg-tertiary); color: var(--text-tertiary); padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px dashed var(--border-color); margin-bottom: 1rem;">
                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);"><i data-lucide="lock" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"></i> Formation verrouillée</div>
                    <p style="font-size: 0.9rem; margin-bottom: 1.25rem;">Cette étape est soumise à condition. Vous devez d'abord compléter à 100% le prérequis suivant pour y accéder :</p>
                    <div style="background: var(--bg-card); padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; color: var(--primary-cyan); border: 1px solid var(--border-color); margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($prereq_titre); ?>
                    </div>
                    <a href="skill_tree.php" class="btn btn-secondary" style="font-size: 0.85rem;"><i data-lucide="git-branch" style="width: 14px; height: 14px;"></i> Voir mon Skill Tree</a>
                </div>
            <?php else: ?>
                <form id="inscription-form" style="margin-top: auto;">
                    <input type="hidden" name="action" value="inscrire">
                    <input type="hidden" name="id_formation" value="<?php echo $formation['id_formation']; ?>">
                    <button type="submit" class="btn btn-primary" id="btn-submit-inscri"
                        style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 12px; border: none; cursor: pointer;">
                        S'inscrire maintenant
                    </button>
                </form>

                <script>
                    document.getElementById('inscription-form').onsubmit = function(e) {
                        e.preventDefault();
                        const btn = document.getElementById('btn-submit-inscri');
                        btn.disabled = true;
                        btn.textContent = 'Traitement...';

                        const formData = new FormData(this);
                        fetch('ajax_handler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                Swal.fire({
                                    title: 'Félicitations !',
                                    text: d.message,
                                    icon: 'success',
                                    confirmButtonText: 'Voir mes cours'
                                }).then(() => {
                                    window.location.href = 'formations_my.php';
                                });
                            } else {
                                Swal.fire('Erreur', d.message, 'error');
                                btn.disabled = false;
                                btn.textContent = "S'inscrire maintenant";
                            }
                        })
                        .catch(err => {
                            Swal.fire('Erreur', 'Erreur réseau : ' + err.message, 'error');
                            btn.disabled = false;
                            btn.textContent = "S'inscrire maintenant";
                        });
                    };
                </script>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description Card -->
    <div class="card-flat" style="margin-top: 2rem; padding: 3rem; border-radius:12px; background: var(--bg-card); border: 1px solid var(--border-color); position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--text-primary);">À propos de cette formation</h2>
            <button onclick="TTS.readElement('formation-description', this)" class="btn-icon-circular" title="Écouter la description" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--primary-cyan); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <i data-lucide="volume-2" style="width: 18px; height: 18px;"></i>
            </button>
        </div>
        <div id="formation-description" style="color: var(--text-secondary); line-height: 1.8;">
            <?php echo $formation['description']; ?>
        </div>
    </div>
</div>
