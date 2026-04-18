<?php
// Session et variables
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Visionneuse de Cours - Aptus AI";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurDashboardController.php';

$formationC = new FormationController();
$tuteurC = new TuteurDashboardController();

$id_formation = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation = $formationC->getFormationById($id_formation);

if (!$formation) {
    die("Formation introuvable.");
}

$resources = $tuteurC->getResources($id_formation);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div style="background: var(--bg-card); border-radius: 16px; padding: 2.5rem; margin-top: 2rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--text-primary);"><?php echo htmlspecialchars($formation['titre']); ?></h1>
            <p style="color: var(--text-secondary); margin:0;">
                Tuteur : <strong><?php echo htmlspecialchars($formation['tuteur_nom'] ?? 'Aptus'); ?></strong> | 
                Domaine : <strong><?php echo htmlspecialchars($formation['domaine']); ?></strong> | 
                Niveau : <strong><?php echo htmlspecialchars($formation['niveau']); ?></strong>
            </p>
        </div>
        <a href="formations_my.php" class="btn btn-secondary">Retour à mes cours</a>
    </div>

    <!-- Description de la formation (Contenu riche Quill.js) -->
    <div style="margin-bottom: 3rem; background: var(--bg-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Description du cours</h3>
        <div style="font-size: 1.05rem; line-height: 1.6; color: var(--text-primary);">
            <?php 
                $clean_desc = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $formation['description']);
                echo $clean_desc; // Affiché tel quel (HTML) 
            ?>
        </div>
    </div>

    <!-- Ressources pédagogiques (Vidéos, PDFs, Quizzes) -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--text-primary);">Ressources Pédagogiques</h2>
        
        <?php if (empty($resources)): ?>
            <div style="text-align: center; padding: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1px dashed var(--border-color); color: var(--text-secondary);">
                <i data-lucide="book-x" style="width: 48px; height: 48px; opacity: 0.5; margin-bottom: 1rem;"></i>
                <p>Aucune ressource pédagogique n'a encore été ajoutée par le tuteur.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($resources as $res): ?>
                    <a href="<?php echo htmlspecialchars($res['url']); ?>" 
                       <?php if($res['type'] === 'pdf' || strpos($res['url'], 'data:') === 0): ?> download="<?php echo htmlspecialchars($res['titre']); ?>.pdf" <?php endif; ?>
                       target="_blank" style="text-decoration: none;">
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; transition: all 0.2s; display: flex; align-items: center; gap: 1rem; cursor: pointer;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                            <div style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); flex-shrink: 0;">
                                <?php if ($res['type'] === 'video'): ?>
                                    <i data-lucide="video" style="width: 24px; height: 24px;"></i>
                                <?php elseif ($res['type'] === 'pdf'): ?>
                                    <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                                <?php elseif ($res['type'] === 'quiz'): ?>
                                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                                <?php else: ?>
                                    <i data-lucide="link" style="width: 24px; height: 24px;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="overflow: hidden;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary); font-size: 1.1rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?php echo htmlspecialchars($res['titre']); ?></h4>
                                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;"><?php echo htmlspecialchars($res['type']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
