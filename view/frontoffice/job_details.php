<?php 
require_once '../../controller/offreC.php';
$offreC = new offreC();

if (!isset($_GET['id'])) {
    header('Location: jobs_feed.php');
    exit();
}

$id_offre = intval($_GET['id']);
$offre = $offreC->getOffreById($id_offre);

if (!$offre) {
    header('Location: jobs_feed.php');
    exit();
}

$pageTitle = "Détails de l'offre - " . $offre['titre']; 
$pageCSS = "feeds.css"; 
$userRole = "Candidat"; 

if (!isset($content)) {
    $content = __FILE__;
    require_once 'layout_front.php';
} else {
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem; max-width: 1000px; margin: 0 auto;">
    
    <!-- Bouton Retour -->
    <a href="jobs_feed.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-tertiary); text-decoration: none; margin-bottom: 2rem; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-primary)'" onmouseout="this.style.color='var(--text-tertiary)'">
        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux offres
    </a>

    <!-- Header de l'offre -->
    <div style="background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-bottom: 2rem;">
        
        <!-- Image de couverture -->
        <?php if (!empty($offre['img_post'])): ?>
            <div style="height: 300px; background-image: url('<?php echo htmlspecialchars($offre['img_post']); ?>'); background-size: cover; background-position: center;"></div>
        <?php else: ?>
            <div style="height: 200px; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="briefcase" style="width: 64px; height: 64px; color: white; opacity: 0.3;"></i>
            </div>
        <?php endif; ?>

        <div style="padding: 3rem; position: relative;">
            
            <!-- Logo Entreprise Flottant -->
            <div style="position: absolute; top: -40px; left: 3rem; width: 80px; height: 80px; background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 8px 20px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                <i data-lucide="building" style="width: 40px; height: 40px;"></i>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 1rem; flex-wrap: wrap; gap: 2rem;">
                <div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">
                        <?php echo htmlspecialchars($offre['titre']); ?>
                    </h1>
                    <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 1.1rem; font-weight: 500;">
                        <span><?php echo htmlspecialchars($offre['nom_entreprise'] ?? 'Entreprise Inconnue'); ?></span>
                        <span style="width: 4px; height: 4px; background: var(--text-tertiary); border-radius: 50%;"></span>
                        <span><?php echo htmlspecialchars($offre['domaine']); ?></span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; align-items: flex-end;">
                    <span class="badge badge-info" style="font-size: 1rem; padding: 0.6rem 1.2rem; border-radius: 12px;">
                        <?php echo htmlspecialchars($offre['type'] ?? 'Sur site'); ?>
                    </span>
                    <div style="color: var(--text-tertiary); font-size: 0.9rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i data-lucide="calendar" style="width: 14px; height: 14px;"></i> Publié le <?php echo htmlspecialchars($offre['date_publication']); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; background: rgba(79, 70, 229, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                        <i data-lucide="award" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase;">Compétences</div>
                        <div style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($offre['competences_requises']); ?></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase;">Expérience</div>
                        <div style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($offre['experience_requise']); ?></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                        <i data-lucide="banknote" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase;">Salaire</div>
                        <div style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($offre['salaire']); ?> TND</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: flex-start;">
        
        <!-- Description -->
        <div style="background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border-color); padding: 3rem; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="file-text" style="width: 24px; height: 24px; color: var(--accent-primary);"></i>
                Description du poste
            </h2>
            <div style="color: var(--text-secondary); line-height: 1.8; font-size: 1.1rem; white-space: pre-wrap;">
                <?php echo htmlspecialchars($offre['description']); ?>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div style="position: sticky; top: 2rem;">
            <div style="background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border-color); padding: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.04); text-align: center;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Intéressé ?</h3>
                <p style="color: var(--text-tertiary); font-size: 0.9rem; margin-bottom: 2rem;">Postulez dès maintenant et rejoignez l'équipe de <?php echo htmlspecialchars($offre['nom_entreprise'] ?? 'Entreprise'); ?>.</p>
                
                <form action="jobs_feed.php" method="GET">
                    <input type="hidden" name="apply_to" value="<?php echo $id_offre; ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border: none; color: white; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(168, 100, 228, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <i data-lucide="send" style="width: 20px; height: 20px;"></i>
                        Postuler maintenant
                    </button>
                </form>

                <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 1rem;">
                    <button class="btn btn-ghost" style="flex: 1; padding: 0.75rem; border-radius: 10px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem; border: 1px solid var(--border-color); color: var(--text-secondary);">
                        <i data-lucide="share-2" style="width: 16px; height: 16px;"></i> Partager
                    </button>
                    <button class="btn btn-ghost" style="flex: 1; padding: 0.75rem; border-radius: 10px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem; border: 1px solid var(--border-color); color: var(--text-secondary);">
                        <i data-lucide="bookmark" style="width: 16px; height: 16px;"></i> Sauver
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

<?php } ?>
