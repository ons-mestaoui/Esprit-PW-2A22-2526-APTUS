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
    <div class="card-flat" style="padding: 1.25rem; background: white; border-radius: 12px; border: 1px solid var(--border-color);">
        <?php if (!empty($cours['image_base64'])): ?>
            <div style="width:100%; height:150px; background: url('<?php echo $cours['image_base64']; ?>') center/cover; border-radius:8px; margin-bottom:1rem;"></div>
        <?php else: ?>
            <div style="width:100%; height:150px; background: linear-gradient(135deg, var(--primary-cyan), var(--accent-primary)); opacity: 0.1; border-radius:8px; margin-bottom:1rem;"></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span class="badge" style="font-size: 0.7rem; background: rgba(0,0,0,0.05);"><?php echo ($cours['is_online']) ? '🌐 En ligne' : '📍 Présentiel'; ?></span>
            <span class="badge" style="font-size: 0.7rem; background: <?php echo ($cours['statut'] == 'annulée') ? '#fca5a5' : 'rgba(0,0,0,0.05)'; ?>;">
                <?php echo htmlspecialchars($cours['statut']); ?>
            </span>
        </div>

        <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($cours['titre']); ?></h2>
        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
            Tuteur : <b><?php echo htmlspecialchars($cours['tuteur_nom'] ?? 'Aptus'); ?></b>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem;">
                <label>Progression</label>
                <span><?php echo $cours['progression']; ?>%</span>
            </div>
            <progress value="<?php echo $cours['progression']; ?>" max="100" style="width: 100%; height: 8px; border-radius: 4px; overflow: hidden;"></progress>
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
                <a href="certificate.php?f_id=<?php echo $cours['id_formation']; ?>" target="_blank" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); text-align:center;">
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
                    <form action="formations_my.php" method="POST" style="margin: 0;" onsubmit="return confirmDesinscription(this, event, '<?php echo addslashes(htmlspecialchars($cours['titre'])); ?>');">
                        <input type="hidden" name="id_formation" value="<?php echo $cours['id_formation']; ?>">
                        <button type="submit" class="btn" style="width: 100%; background: white; color: #ef4444; border: 1px solid #ef4444; padding: 0.5rem; border-radius: 6px;">Se désinscrire</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 5rem; opacity: 0.4;">
            <p>Vous n'avez pas encore d'inscriptions.</p>
            <a href="formations_catalog.php" class="btn btn-primary" style="margin-top: 1rem;">Explorer le catalogue</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Confirmation de désinscription avec SweetAlert2
    // On bloque le submit par défaut et on attend la confirmation de l'utilisateur
    function confirmDesinscription(form, event, formationTitre) {
        event.preventDefault(); // Empêcher l'envoi immédiat
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
                form.submit(); // Envoi du formulaire si on clique sur "Oui"
            }
        });
        return false;
    }
</script>
