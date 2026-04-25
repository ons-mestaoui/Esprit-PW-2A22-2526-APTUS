<?php
$pageTitle = "Détails de la Formation";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';

    // Simulate fetching the formation by ID
    $id = $_GET['id'] ?? 1;
    $formationC = new FormationController();
    $formation = $formationC->getFormationById($id);

    // Simuler l'utilisateur (Candidat par défaut ID 10, Tuteur ID 1)
    // Idéalement, cela viendrait de $_SESSION['user_id']
    $id_user = (isset($_GET['role']) && $_GET['role'] == 'tuteur') ? 1 : 10;
    $userRole = ($id_user == 1) ? 'Tuteur' : 'Candidat';

    // === CONTRAINTE: VERIFICATION DU PREREQUIS ===
    $is_unlocked = true;
    $prereq_titre = "";
    if (!empty($formation['prerequis_id'])) {
        $prereq = $formationC->getFormationWithPrerequisite($formation['prerequis_id'], $id_user);
        if ($prereq && $prereq['ma_progression'] < 100) {
            $is_unlocked = false;
            $prereq_titre = $prereq['titre'];
        }
    }

    // Si on a été redirigé avec une erreur (ex: prérequis) ou depuis le contrôleur d'inscription
    if (isset($_SESSION['flash_error'])) {
        $errorMsg = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
    } elseif (isset($_SESSION['flash_success'])) {
        $successMsg = $_SESSION['flash_success'];
        unset($_SESSION['flash_success']);
    }

    if (!$is_unlocked && !isset($errorMsg)) {
        // Optionnel : un message d'avertissement
    } elseif (strtotime($formation['date_formation']) < strtotime(date('Y-m-d')) && !isset($errorMsg)) {
        // Optionnel
    }
    // Check if already inscribed
    $db = config::getConnexion();
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = ? AND id_user = ?");
        $stmt->execute([$id, $id_user]);
        $isInscribed = $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM Inscription WHERE id_formation = ? AND id_user = ?");
            $stmt->execute([$id, $id_user]);
            $isInscribed = $stmt->fetchColumn() > 0;
        } catch (Exception $e2) {
            $isInscribed = false;
        }
    }

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
                    style="font-size:0.75rem;"><?php echo htmlspecialchars($formation['domaine'] ?? 'Général'); ?></span>
                <span class="badge badge-primary"
                    style="font-size:0.75rem;"><?php echo htmlspecialchars($formation['niveau']); ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0;"><?php echo htmlspecialchars($formation['titre']); ?></h1>
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
                    <div style="font-weight: 600;"><?php echo date('d M Y', strtotime($formation['date_formation'])); ?>
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
                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align:center;">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            <?php if ($userRole === 'Tuteur' && $formation['id_tuteur'] == $id_user): ?>
                <div style="background: rgba(52, 152, 219, 0.1); color: #3498db; padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid rgba(52, 152, 219, 0.3);">
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">👨‍🏫 Vue Tuteur</div>
                    <p style="font-size: 0.9rem; margin-bottom: 1.5rem;">Vous êtes le formateur assigné à ce module.</p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="tuteur_dashboard.php" class="btn" style="flex:1; background: var(--bg-card); border: 1px solid #3498db; color: #3498db; text-decoration: none; padding: 0.75rem; border-radius: 8px; font-weight: 600;">Mon Dashboard</a>
                        <?php if($formation['is_online']): ?>
                            <a href="<?php echo htmlspecialchars($formation['lien_api_room'] ?? '#'); ?>" target="_blank" class="btn" style="flex:1; background: #3498db; color: white; text-decoration: none; padding: 0.75rem; border-radius: 8px; font-weight: 600;">Lancer la Room</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (isset($isInscribed) && $isInscribed): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid rgba(16, 185, 129, 0.3); margin-bottom: 1rem;">
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
                <form action="../../controller/traitement_inscription.php" method="post" style="margin-top: auto;">
                    <input type="hidden" name="id_formation" value="<?php echo $formation['id_formation']; ?>">
                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 12px; border: none; cursor: pointer;">
                        S'inscrire maintenant
                    </button>
                </form>
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