<?php 
require_once '../../controller/candidatureC.php';
require_once '../../controller/offreC.php';

$candidatureC = new candidatureC();
$offreC = new offreC();

// Pour le moment on utilise l'ID candidat 1 (à remplacer par $_SESSION['user_id'] plus tard)
$id_candidat = 1;
$candidatures = $candidatureC->getCandidaturesByCandidat($id_candidat);

$pageTitle = "Mes Candidatures"; 
$pageCSS = "feeds.css"; 
$userRole = "Candidat"; 

if (!isset($content)) {
    $content = __FILE__;
    require_once 'layout_front.php';
} else {
?>

<style>
    .applications-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem;
    }
    .application-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        align-items: center;
        gap: 2rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .application-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: var(--accent-primary);
    }
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
    }
    .status-pending { background: rgba(245, 158, 11, 0.1) !important; color: #f59e0b !important; border-color: rgba(245, 158, 11, 0.2); }
    .status-accepted { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border-color: rgba(16, 185, 129, 0.2); }
    .status-rejected { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; border-color: rgba(239, 68, 68, 0.2); }
    
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: var(--bg-card);
        border-radius: 30px;
        border: 2px dashed var(--border-color);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-item { animation: fadeIn 0.5s ease forwards; }
</style>

<div class="applications-container">
    <div style="margin-bottom: 3rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-primary); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Suivi de mes candidatures</h1>
            <p style="color: var(--text-secondary); font-size: 1.1rem;">Gérez et suivez l'état de vos demandes d'emploi en temps réel.</p>
        </div>
        <div style="background: var(--accent-primary); color: white; padding: 1rem 1.5rem; border-radius: 16px; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 10px 20px rgba(168, 100, 228, 0.2);">
            <i data-lucide="clipboard-check" style="width: 24px; height: 24px;"></i>
            <span><?php echo count($candidatures); ?> Candidatures</span>
        </div>
    </div>

    <?php if (empty($candidatures)): ?>
        <div class="empty-state">
            <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-tertiary);">
                <i data-lucide="inbox" style="width: 40px; height: 40px;"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem;">Aucune candidature pour le moment</h2>
            <p style="color: var(--text-tertiary); margin-bottom: 2rem;">Vous n'avez pas encore postulé à des offres d'emploi.</p>
            <a href="jobs_feed.php" class="btn btn-primary" style="padding: 1rem 2rem; border-radius: 12px; text-decoration: none; background: var(--accent-primary); color: white; font-weight: 700;">Découvrir les offres</a>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($candidatures as $index => $cand): 
                $offreDetails = $offreC->getOffreById($cand['id_offre']);
                $statusClass = 'status-pending';
                $statusIcon = 'clock';
                if ($cand['statut'] === 'Accepté') { $statusClass = 'status-accepted'; $statusIcon = 'check-circle'; }
                if ($cand['statut'] === 'Refusé') { $statusClass = 'status-rejected'; $statusIcon = 'x-circle'; }
            ?>
                <div class="application-card stagger-item" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <!-- Logo/Image Entreprise -->
                    <div style="width: 64px; height: 64px; background: var(--bg-secondary); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <?php if (!empty($offreDetails['img_post'])): ?>
                            <img src="<?php echo htmlspecialchars($offreDetails['img_post']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i data-lucide="building-2" style="width: 28px; height: 28px; color: var(--text-tertiary);"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Infos Poste -->
                    <div>
                        <h3 style="font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; font-size: 1.15rem;"><?php echo htmlspecialchars($offreDetails['titre'] ?? 'Offre inconnue'); ?></h3>
                        <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-tertiary); font-size: 0.9rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem;">
                                <i data-lucide="building" style="width: 14px; height: 14px;"></i>
                                <?php echo htmlspecialchars($offreDetails['nom_entreprise'] ?? 'Entreprise'); ?>
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.4rem;">
                                <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                Postulé le <?php echo date('d M Y', strtotime($cand['date_candidature'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div>
                        <?php 
                            $rawStatus = trim($cand['statut']);
                            $statusClass = 'status-pending';
                            $statusIcon = 'clock';
                            
                            if (strcasecmp($rawStatus, 'Accepté') == 0 || strcasecmp($rawStatus, 'Accepte') == 0) { 
                                $statusClass = 'status-accepted'; 
                                $statusIcon = 'check-circle'; 
                            } elseif (strcasecmp($rawStatus, 'Refusé') == 0 || strcasecmp($rawStatus, 'Refuse') == 0) { 
                                $statusClass = 'status-rejected'; 
                                $statusIcon = 'x-circle'; 
                            }
                        ?>
                        <div class="status-badge <?php echo $statusClass; ?>">
                            <i data-lucide="<?php echo $statusIcon; ?>" style="width: 16px; height: 16px;"></i>
                            <?php echo htmlspecialchars($cand['statut']); ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="job_details.php?id=<?php echo (int)$cand['id_offre']; ?>" class="btn-ghost" title="Voir l'offre" style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: all 0.2s;">
                            <i data-lucide="external-link" style="width: 20px; height: 20px;"></i>
                        </a>
                        <button onclick="viewApplicationDetails(<?php echo (int)$cand['id_candidature']; ?>)" class="btn-ghost" title="Détails de ma candidature" style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: all 0.2s;">
                            <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Détails Candidature -->
<div id="application-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; padding: 2rem; animation: fadeIn 0.2s ease;">
    <div style="background: var(--bg-card); width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; position: relative; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid var(--border-color); animation: slideUp 0.3s ease-out;">
        <!-- Header du Modal -->
        <div style="padding: 2rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 id="modal-offre-titre" style="font-size: 1.5rem; font-weight: 900; color: var(--text-primary); margin-bottom: 0.25rem;">Chargement...</h2>
                <p id="modal-entreprise" style="color: var(--text-secondary); font-weight: 600;"></p>
            </div>
            <button onclick="closeModal()" style="background: var(--bg-secondary); border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; color: var(--text-tertiary); display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.color='#ef4444'; this.style.background='#fef2f2';" onmouseout="this.style.color='var(--text-tertiary)'; this.style.background='var(--bg-secondary)';">
                <i data-lucide="x" style="width: 24px; height: 24px;"></i>
            </button>
        </div>

        <!-- Corps du Modal -->
        <div style="padding: 2rem;">
            <div style="display: flex; gap: 1rem; margin-bottom: 2.5rem;">
                <div id="modal-status-badge" class="status-badge">
                    <i id="modal-status-icon" data-lucide="clock" style="width: 16px; height: 16px;"></i>
                    <span id="modal-status-text">...</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-tertiary); font-size: 0.9rem;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                    <span id="modal-date">...</span>
                </div>
            </div>

            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem;">
                    <i data-lucide="help-circle" style="width: 18px; height: 18px; color: var(--accent-primary);"></i>
                    Question de l'employeur
                </h4>
                <div id="modal-question" style="font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: 12px; border-left: 4px solid var(--accent-primary); text-align: center;">
                    ...
                </div>
                <h4 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 800; margin-bottom: 1rem;">Votre réponse</h4>
                <div id="modal-reponse" style="line-height: 1.6; color: var(--text-secondary); background: var(--bg-secondary); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color); max-height: 250px; overflow-y: auto;">
                    ...
                </div>
            </div>

            <div style="padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <button id="btn-download-cv" onclick="downloadCV()" style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 1.25rem; border-radius: 16px; background: var(--accent-primary); color: white; border: none; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 10px 20px rgba(168, 100, 228, 0.2);">
                    <i data-lucide="download" style="width: 22px; height: 22px;"></i>
                    Télécharger mon CV (PDF)
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>


<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});

function viewApplicationDetails(id) {
    const modal = document.getElementById('application-modal');
    modal.style.display = 'flex';
    
    // Reset modal content with placeholders
    document.getElementById('modal-offre-titre').innerText = "Chargement...";
    document.getElementById('modal-entreprise').innerText = "";
    document.getElementById('modal-reponse').innerHTML = "Veuillez patienter...";
    
    const formData = new FormData();
    formData.append('id', id);

    fetch('ajax_application_details.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('modal-offre-titre').innerText = data.titre;
            document.getElementById('modal-entreprise').innerText = data.entreprise;
            document.getElementById('modal-question').innerText = data.question;
            document.getElementById('modal-reponse').innerHTML = data.reponse;
            document.getElementById('modal-date').innerText = "Postulé le " + data.date;
            document.getElementById('modal-status-text').innerText = data.statut;
            
            // Mise à jour de l'icône et du badge (insensible à la casse)
            const raw = data.statut.trim().toLowerCase();
            let iconName = 'clock';
            let newClass = 'status-pending';

            if (raw === 'accepté' || raw === 'accepte') { 
                iconName = 'check-circle'; 
                newClass = 'status-accepted';
            } else if (raw === 'refusé' || raw === 'refuse') { 
                iconName = 'x-circle'; 
                newClass = 'status-rejected';
            }

            document.getElementById('modal-status-icon').setAttribute('data-lucide', iconName);
            const badge = document.getElementById('modal-status-badge');
            badge.className = 'status-badge ' + newClass;

            // Stockage temporaire du CV pour le téléchargement
            window.currentCVData = data.cv;
            window.currentCVName = "CV_" + data.titre.replace(/\s+/g, '_') + ".pdf";
            
            if (window.lucide) lucide.createIcons();
        } else {
            alert(data.message);
            closeModal();
        }
    })
    .catch(err => {
        console.error(err);
        closeModal();
    });
}

function closeModal() {
    document.getElementById('application-modal').style.display = 'none';
}

function downloadCV() {
    if (!window.currentCVData) return;
    
    const link = document.createElement('a');
    link.href = window.currentCVData;
    link.download = window.currentCVName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Fermer au clic extérieur
window.onclick = function(event) {
    const modal = document.getElementById('application-modal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php } ?>
