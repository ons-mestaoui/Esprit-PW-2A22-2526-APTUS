<?php 
$pageTitle = "Mes Formations - Aptus AI";

if (!isset($content)) {
    // Basic setup if executed directly
    require_once __DIR__ . '/../../config.php';
    
    $db = config::getConnexion();
    $id_user = 10; // User candidat ID pour démonstration
    $mesCours = [];
    
    try {
        $stmt = $db->prepare("
            SELECT f.*, i.statut, i.progression, 
                   COALESCE(u.nom, 'Aptus') as tuteur_nom
            FROM inscription i
            JOIN Formation f ON i.id_formation = f.id_formation
            LEFT JOIN utilisateur u ON f.id_tuteur = u.id
            WHERE i.id_user = ?
            ORDER BY i.date_inscription DESC
        ");
        $stmt->execute([$id_user]);
        $mesCours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        try {
            $stmt = $db->prepare("
                SELECT f.*, i.statut, i.progression, 
                       COALESCE(u.nom, 'Aptus') as tuteur_nom
                FROM Inscription i
                JOIN Formation f ON i.id_formation = f.id_formation
                LEFT JOIN User u ON f.id_tuteur = u.id
                WHERE i.id_user = ?
                ORDER BY i.date_inscription DESC
            ");
            $stmt->execute([$id_user]);
            $mesCours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e2) {
            $mesCours = [];
        }
    }
    
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
            <span class="badge" style="font-size: 0.7rem; background: rgba(0,0,0,0.05);"><?php echo htmlspecialchars($cours['statut']); ?></span>
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

        <?php if ($cours['progression'] == 100): ?>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 1rem; border-radius: 12px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 600;">
                    <span style="font-size: 1.5rem;">
                        <?php 
                            $dom = strtolower($cours['domaine'] ?? '');
                            if (strpos($dom, 'ia') !== false) echo '🤖';
                            elseif (strpos($dom, 'code') !== false || strpos($dom, 'dev') !== false) echo '💻';
                            else echo '🏅';
                        ?>
                    </span>
                    Badge "Expert" Acquis !
                </div>
                <a href="certificate.php?f_id=<?php echo $cours['id_formation']; ?>" target="_blank" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); text-align:center;">
                    🎓 Générer mon Certificat
                </a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php if ($cours['is_online']): ?>
                    <a href="<?php echo htmlspecialchars($cours['lien_api_room'] ?? '#'); ?>" target="_blank" class="btn" style="background: #3b82f6; color: white; text-align:center; padding: 0.5rem; text-decoration:none; border-radius:6px;">📹 Rejoindre la Room</a>
                <?php endif; ?>
                
                <?php if ($cours['date_formation'] <= date('Y-m-d')): ?>
                    <a href="#" class="btn btn-primary" style="text-align:center;">Terminer la formation</a>
                <?php else: ?>
                    <button class="btn" style="background: #e2e8f0; color: #94a3b8; cursor: not-allowed; width:100%; border:none; padding:0.5rem; border-radius:6px;" disabled>Disponible le <?php echo date('d/m', strtotime($cours['date_formation'])); ?></button>
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
