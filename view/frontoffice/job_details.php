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

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.2);
        --premium-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
    }
    .details-header {
        position: relative;
        height: 400px;
        border-radius: 0 0 60px 60px;
        overflow: hidden;
        margin-bottom: -120px;
        z-index: 1;
    }
    .details-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.65);
    }
    .details-header-content {
        position: absolute;
        bottom: 160px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        width: 100%;
        padding: 0 2rem;
    }
    .main-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 2.5rem;
        position: relative;
        z-index: 2;
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    .side-info {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }
    .glass-card {
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-color);
        border-radius: 32px;
        box-shadow: var(--premium-shadow);
        overflow: hidden;
    }
    .job-badge {
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .job-badge-primary {
        background: rgba(168, 100, 228, 0.1);
        color: var(--accent-primary);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-1 { animation: fadeInUp 0.6s ease forwards; }
    .stagger-2 { animation: fadeInUp 0.6s ease 0.15s forwards; opacity: 0; }
</style>

<div style="min-height: 100vh; background: var(--bg-secondary); padding-bottom: 6rem;">
    <!-- Hero Header -->
    <div class="details-header">
        <?php if (!empty($offre['img_post'])): ?>
            <img src="<?php echo htmlspecialchars($offre['img_post']); ?>" alt="Banner">
        <?php else: ?>
            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%);"></div>
        <?php endif; ?>
        <div class="details-header-content">
            <div style="display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); padding: 0.5rem 1.25rem; border-radius: 50px; border: 1px solid rgba(255,255,255,0.3); color: white; font-weight: 700; font-size: 0.9rem; margin-bottom: 1.5rem;">
                <i data-lucide="building" style="width: 16px; height: 16px;"></i>
                <?php echo htmlspecialchars($offre['nom_entreprise'] ?? 'Entreprise Privée'); ?>
            </div>
            <h1 style="color: white; font-size: 3.5rem; font-weight: 900; letter-spacing: -0.04em; margin-bottom: 0.5rem; text-shadow: 0 4px 30px rgba(0,0,0,0.4);">
                <?php echo htmlspecialchars($offre['titre']); ?>
            </h1>
        </div>
    </div>

    <div class="main-grid">
        <!-- Sidebar Info -->
        <aside class="side-info stagger-1">
            <div class="glass-card" style="padding: 2.5rem;">
                <!-- Bouton Retour -->
                <a href="jobs_feed.php" style="display: flex; align-items: center; gap: 0.6rem; color: var(--text-tertiary); text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-bottom: 2.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-primary)'" onmouseout="this.style.color='var(--text-tertiary)'">
                    <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i> Retour aux offres
                </a>

                <div style="display: grid; gap: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 52px; height: 52px; background: rgba(79, 70, 229, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                            <i data-lucide="award" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 700;">Expérience</div>
                            <div style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($offre['experience_requise']); ?></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 52px; height: 52px; background: rgba(16, 185, 129, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                            <i data-lucide="banknote" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 700;">Salaire</div>
                            <div style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($offre['salaire']); ?> TND / mois</div>
                        </div>
                    </div>

                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <div style="width: 52px; height: 52px; background: rgba(245, 158, 11, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                                <i data-lucide="map-pin" style="width: 26px; height: 26px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 700;">Localisation</div>
                                <div style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($offre['lieu'] ?? 'Tunis, Tunisie'); ?></div>
                            </div>
                        </div>
                        <a href="jobs_map.php?id=<?php echo $id_offre; ?>" class="btn btn-sm btn-primary" style="padding: 0.5rem 1rem; border-radius: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                            Afficher Maps <i data-lucide="map" style="width: 14px; height: 14px;"></i>
                        </a>
                </div>

                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <form action="apply.php" method="GET" style="margin-bottom: 1.5rem;">
                        <input type="hidden" name="id" value="<?php echo $id_offre; ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; border-radius: 18px; font-weight: 800; font-size: 1.1rem; border: none; color: white;">
                            <i data-lucide="send" style="width: 22px; height: 22px;"></i>
                            Postuler maintenant
                        </button>
                    </form>

                    <div style="display: flex; gap: 1rem;">
                        <button id="btn-share-<?php echo $id_offre; ?>" onclick="copyShareLink(<?php echo $id_offre; ?>)" class="btn-ghost" style="flex: 1; padding: 0.85rem; border-radius: 14px; border: 2px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; font-size: 0.85rem;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.color='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)';">
                            <i data-lucide="share-2" style="width: 18px; height: 18px;"></i> <span id="share-text-<?php echo $id_offre; ?>">Partager</span>
                        </button>
                        
                        <?php $is_fav = $offreC->isFavori(1, $id_offre); ?>
                        <button id="btn-fav-<?php echo $id_offre; ?>" onclick="toggleFavori(<?php echo $id_offre; ?>)" class="btn-ghost" style="flex: 1; padding: 0.85rem; border-radius: 14px; border: 2px solid <?php echo $is_fav ? 'var(--accent-primary)' : 'var(--border-color)'; ?>; background: <?php echo $is_fav ? 'rgba(168, 100, 228, 0.05)' : 'var(--bg-secondary)'; ?>; color: <?php echo $is_fav ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; font-size: 0.85rem;">
                            <i data-lucide="bookmark" style="width: 18px; height: 18px; fill: <?php echo $is_fav ? 'currentColor' : 'none'; ?>;"></i> 
                            <span id="fav-text-<?php echo $id_offre; ?>"><?php echo $is_fav ? 'Sauvé' : 'Sauver'; ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="stagger-2">
            <div class="glass-card" style="padding: 4rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 4px; height: 32px; background: var(--accent-primary); border-radius: 50px;"></div>
                        <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-primary); letter-spacing: -0.02em; margin: 0;">Description du poste</h2>
                    </div>
                    <button type="button" id="btn-read-desc" style="display: flex; align-items: center; gap: 0.5rem; color: #a864e4; border: 1px solid rgba(168, 100, 228, 0.3); padding: 0.6rem 1.2rem; border-radius: 20px; transition: all 0.3s; font-weight: 700; font-size: 0.9rem; background: rgba(168, 100, 228, 0.1); cursor: pointer;" title="Écouter la description" onmouseover="this.style.background='rgba(168, 100, 228, 0.2)';" onmouseout="this.style.background='rgba(168, 100, 228, 0.1)';">
                        <i data-lucide="volume-2" id="read-desc-icon" style="width: 20px; height: 20px;"></i>
                        <span id="read-desc-text">Écouter l'offre</span>
                    </button>
                </div>

                <div id="job-description-text" style="color: var(--text-secondary); line-height: 1.8; font-size: 1.15rem; white-space: pre-wrap; font-weight: 400; margin-bottom: 4rem;">
                    <?php echo htmlspecialchars($offre['description']); ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; padding: 3rem; background: var(--bg-secondary); border-radius: 28px; border: 1px solid var(--border-color);">
                    <div>
                        <h4 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 800; letter-spacing: 0.1em; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                            <i data-lucide="check-circle" style="width: 18px; height: 18px; color: #10b981;"></i>
                            Compétences clés
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            <?php 
                                $tags = explode(',', $offre['competences_requises']);
                                foreach($tags as $tag): 
                            ?>
                                <span style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 700; color: var(--text-primary); font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s; cursor: default;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 800; letter-spacing: 0.1em; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                            <i data-lucide="layers" style="width: 18px; height: 18px; color: var(--accent-primary);"></i>
                            Domaine d'activité
                        </h4>
                        <div style="color: var(--text-primary); font-weight: 800; font-size: 1.25rem;">
                            <?php echo htmlspecialchars($offre['domaine']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function copyShareLink(id) {
    const btn = document.getElementById('btn-share-' + id);
    const text = document.getElementById('share-text-' + id);
    const url = window.location.origin + window.location.pathname + '?id=' + id;
    
    navigator.clipboard.writeText(url).then(() => {
        const originalText = text.innerText;
        text.innerText = 'Lien copié !';
        btn.style.borderColor = '#10b981';
        btn.style.color = '#10b981';
        
        setTimeout(() => {
            text.innerText = originalText;
            btn.style.borderColor = 'var(--border-color)';
            btn.style.color = 'var(--text-secondary)';
        }, 2000);
    });
}

function toggleFavori(id) {
    const btn = document.getElementById('btn-fav-' + id);
    const text = document.getElementById('fav-text-' + id);
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id_offre', id);

    fetch('ajax_favoris.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.action === 'added') {
            btn.style.color = 'var(--accent-primary)';
            btn.style.borderColor = 'var(--accent-primary)';
            btn.style.background = 'rgba(168, 100, 228, 0.05)';
            text.innerText = 'Sauvé';
            btn.querySelector('i').style.fill = 'currentColor';
        } else {
            btn.style.color = 'var(--text-secondary)';
            btn.style.borderColor = 'var(--border-color)';
            btn.style.background = 'var(--bg-secondary)';
            text.innerText = 'Sauver';
            btn.querySelector('i').style.fill = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();

    // --- TEXT TO SPEECH LOGIC (READ DESCRIPTION) ---
    const btnReadDesc = document.getElementById('btn-read-desc');
    const descTextElement = document.getElementById('job-description-text');
    const readDescIcon = document.getElementById('read-desc-icon');
    const readDescText = document.getElementById('read-desc-text');

    if ('speechSynthesis' in window && btnReadDesc && descTextElement) {
        let isPlaying = false;
        let utterance = null;
        let readAnimation = null;

        btnReadDesc.addEventListener('click', function() {
            if (isPlaying) {
                window.speechSynthesis.cancel();
                isPlaying = false;
                readDescText.innerText = "Écouter l'offre";
                readDescIcon.setAttribute('data-lucide', 'volume-2');
                lucide.createIcons();
                btnReadDesc.style.background = 'rgba(168, 100, 228, 0.1)';
                if(readAnimation) readAnimation.cancel();
            } else {
                window.speechSynthesis.cancel(); // Stop any previous
                const textToRead = descTextElement.innerText || descTextElement.textContent;
                utterance = new SpeechSynthesisUtterance(textToRead);
                utterance.lang = 'fr-FR'; 
                
                utterance.onstart = function() {
                    isPlaying = true;
                    readDescText.innerText = 'Arrêter';
                    readDescIcon.setAttribute('data-lucide', 'square');
                    lucide.createIcons();
                    btnReadDesc.style.background = 'rgba(168, 100, 228, 0.2)';
                    
                    readAnimation = btnReadDesc.animate([
                        { boxShadow: '0 0 0 0 rgba(168, 100, 228, 0.4)' },
                        { boxShadow: '0 0 0 10px rgba(168, 100, 228, 0)' }
                    ], {
                        duration: 1500,
                        iterations: Infinity
                    });
                };
                
                utterance.onend = function() {
                    isPlaying = false;
                    readDescText.innerText = "Écouter l'offre";
                    readDescIcon.setAttribute('data-lucide', 'volume-2');
                    lucide.createIcons();
                    btnReadDesc.style.background = 'rgba(168, 100, 228, 0.1)';
                    if(readAnimation) readAnimation.cancel();
                };
                
                utterance.onerror = function() {
                    isPlaying = false;
                    readDescText.innerText = "Écouter l'offre";
                    readDescIcon.setAttribute('data-lucide', 'volume-2');
                    lucide.createIcons();
                    btnReadDesc.style.background = 'rgba(168, 100, 228, 0.1)';
                    if(readAnimation) readAnimation.cancel();
                };

                window.speechSynthesis.speak(utterance);
            }
        });
    } else if (btnReadDesc) {
        btnReadDesc.style.display = 'none';
    }
});
</script>

<?php } ?>