<?php 
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';

$vc = new VeilleC();
$rapport = null;
$donnees = [];

if (isset($_GET['id'])) {
    $id_rapport = $_GET['id'];
    $rapport = $vc->recupererRapport($id_rapport);
    if ($rapport) {
        $vc->incrementerViews($id_rapport);
        $donnees = $vc->recupererDonneesParRapport($rapport['id_rapport_marche']);
    }
}

$pageTitle = $rapport ? htmlspecialchars($rapport['titre']) : "Détail du Rapport"; 
$pageCSS = "veille.css"; 

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}

if (!$rapport) {
    echo "<div style='padding:50px;text-align:center;'><h2>Rapport introuvable</h2><a href='veille_feed.php'>Retour</a></div>";
    exit;
}
?>

<!-- Custom Styles for Interactivity -->
<style>
/* ── Global Enhancements ────────────────────────────── */
:root {
    --hero-blur: 15px;
    --card-shadow: 0 12px 40px rgba(0,0,0,0.03);
}

.progress-container { position: fixed; top: 0; left: 0; width: 100%; height: 5px; background: rgba(0,0,0,0.05); z-index: 10000; }
.progress-bar { height: 100%; background: var(--gradient-primary); width: 0%; transition: width 0.2s ease; border-radius: 0 5px 5px 0; box-shadow: 0 0 15px var(--accent-primary); }

/* ── Hero Section (Glassmorphism) ───────────────────── */
.report-hero { 
    margin-bottom: var(--space-10); 
    background: linear-gradient(135deg, rgba(107, 52, 163, 0.05) 0%, rgba(0, 163, 218, 0.05) 100%);
    backdrop-filter: blur(var(--hero-blur));
    -webkit-backdrop-filter: blur(var(--hero-blur));
    padding: var(--space-10); 
    border-radius: 24px; 
    border: 1px solid rgba(255, 255, 255, 0.4); 
    position: relative; 
    overflow: hidden; 
    box-shadow: var(--card-shadow); 
}
[data-theme="dark"] .report-hero {
    background: linear-gradient(135deg, rgba(162, 117, 211, 0.1) 0%, rgba(41, 191, 255, 0.05) 100%);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.report-hero::before { 
    content: ''; position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; 
    background: radial-gradient(circle, var(--accent-primary-light) 0%, transparent 70%); 
    opacity: 0.4; border-radius: 50%; pointer-events: none; z-index: 0;
}

/* ── Tab Interface ──────────────────────────────────── */
.tab-controls { display: flex; gap: var(--space-2); margin-bottom: var(--space-8); background: var(--bg-secondary); padding: 6px; border-radius: 16px; width: fit-content; }
.tab-btn { 
    background: transparent; border: none; font-size: 0.95rem; font-weight: 600; 
    color: var(--text-secondary); padding: 10px 24px; border-radius: 12px; 
    cursor: pointer; transition: var(--transition-base); display: flex; align-items: center; gap: 8px; 
}
.tab-btn.active { background: var(--bg-primary); color: var(--accent-primary); box-shadow: var(--shadow-md); transform: translateY(-1px); }
.tab-btn:hover:not(.active) { color: var(--text-primary); }

.tab-content { display: none; animation: entryAnim 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
.tab-content.active { display: block; }
@keyframes entryAnim { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Salary Visualization ───────────────────────────── */
.salary-visualizer { margin-top: var(--space-6); background: var(--bg-main); padding: var(--space-6); border-radius: 16px; border: 1px solid var(--border-color); }
.salary-track { height: 12px; background: var(--bg-secondary); border-radius: 6px; position: relative; margin: 40px 0 20px; }
.salary-range-bar { height: 100%; background: var(--gradient-accent); border-radius: 6px; position: absolute; box-shadow: 0 0 15px rgba(107, 52, 163, 0.2); }
.salary-marker { position: absolute; top: -30px; transform: translateX(-50%); font-weight: 800; font-size: 0.85rem; color: var(--text-primary); white-space: nowrap; }
.salary-marker::after { content: ''; position: absolute; bottom: -10px; left: 50%; width: 2px; height: 25px; background: var(--border-color); }
.salary-marker.avg { top: 25px; color: var(--accent-primary); }
.salary-marker.avg::after { top: -20px; bottom: auto; background: var(--accent-primary); }

.salary-card { 
    background: var(--bg-primary); padding: var(--space-6); border-radius: 20px; border: 1px solid var(--border-color); 
    transition: var(--transition-bounce); display: flex; flex-direction: column; gap: 8px; position: relative;
}
.salary-card:hover { transform: translateY(-6px); border-color: var(--accent-primary); box-shadow: var(--shadow-lg); }

/* ── Content Typography ─────────────────────────────── */
.lead-text { 
    font-size: 1.3rem; font-weight: 500; line-height: 1.6; color: var(--text-primary); 
    border-left: 5px solid var(--accent-primary); padding: var(--space-5) var(--space-8); 
    background: linear-gradient(90deg, var(--accent-primary-light) 0%, transparent 100%); 
    border-radius: 0 16px 16px 0; margin-bottom: var(--space-8); 
}
[data-theme="dark"] .lead-text { background: linear-gradient(90deg, rgba(162, 117, 211, 0.1) 0%, transparent 100%); }

.rich-content { font-size: 1.15rem; line-height: 1.9; color: var(--text-secondary); }
.rich-content h2, .rich-content h3 { color: var(--text-primary); margin-top: 2em; margin-bottom: 0.8em; }

/* ── PDF Export Button ─────────────────────────────── */
.btn-pdf { 
    display:inline-flex; align-items:center; gap:10px; padding:12px 28px; font-size:14px; font-weight:700;
    background: var(--gradient-accent); color:#fff; border:none; border-radius:30px; cursor:pointer;
    box-shadow: 0 10px 25px rgba(107, 52, 163, 0.25); transition: var(--transition-bounce); text-decoration:none; 
}
.btn-pdf:hover { transform:scale(1.05) translateY(-2px); box-shadow: 0 15px 35px rgba(107, 52, 163, 0.35); color:#fff; }

/* ── Print / PDF Styles ────────────────────────────── */
@media print {
    .progress-container, nav, header, .tab-controls, footer, .btn, .btn-pdf,
    [class*="sidebar"], [class*="back-to"], a[href="veille_feed.php"] { display: none !important; }
    body, html { background: #fff !important; color: #111 !important; }
    .report-hero { border: none; background: none !important; box-shadow: none; padding: 0; backdrop-filter: none; }
    .tab-content { display: block !important; opacity: 1 !important; transform: none !important; }
    .salary-visualizer { border: 1px solid #eee; }
    @page { margin: 1.5cm; }
}

.pdf-header { display: none; text-align:center; border-bottom:2px solid var(--accent-primary); padding-bottom:16px; margin-bottom:24px; }
.pdf-header__logo { font-size:24px; font-weight:800; color:var(--accent-primary); }
</style>

<div class="progress-container">
  <div class="progress-bar" id="reading-progress"></div>
</div>

<!-- PDF-only header (hidden on screen, visible in print) -->
<div class="pdf-header">
    <div class="pdf-header__logo">⚡ Aptus — Veille du Marché</div>
    <div class="pdf-header__meta">Rapport généré le <?php echo date('d/m/Y'); ?> · <?php echo htmlspecialchars($rapport['secteur_principal']); ?> · <?php echo htmlspecialchars($rapport['region']); ?></div>
</div>

<div class="report-hero">
  <div style="position: relative; z-index: 2;">
    <a href="veille_feed.php" class="btn btn-sm btn-ghost" style="margin-bottom:var(--space-6); padding:0; color:var(--text-secondary);"><i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Retour aux rapports</a>
    
    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:var(--space-6);">
        <div style="flex: 1;">
          <div style="display:flex; align-items:center; gap:12px; margin-bottom:var(--space-4);">
            <span class="badge" style="background:var(--accent-primary-light); color:var(--accent-primary); font-weight:700; font-size:12px; padding:6px 14px; border-radius:100px;"><?php echo htmlspecialchars($rapport['secteur_principal']); ?></span>
            <span style="font-size:13px; color:var(--text-tertiary); display:flex; align-items:center; gap:6px;"><i data-lucide="map-pin" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($rapport['region']); ?></span>
          </div>
          
          <h1 class="page-header__title" style="margin-bottom:var(--space-4); font-size: 3rem; line-height: 1.1; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
            <?php echo htmlspecialchars($rapport['titre']); ?>
          </h1>
          
          <div class="flex gap-6 text-secondary" style="margin-top:var(--space-6); font-size:0.95rem; font-weight:500;">
              <span style="display:flex;align-items:center;gap:10px;"><div class="avatar avatar-sm avatar-initials" style="width:28px;height:28px;font-size:11px;background:var(--accent-primary);color:#fff;">A</div> Par <?php echo htmlspecialchars($rapport['auteur']); ?></span>
              <span style="display:flex;align-items:center;gap:8px;"><i data-lucide="calendar" style="width:18px;height:18px;opacity:0.7;"></i> <?php echo date('d F Y', strtotime($rapport['date_publication'])); ?></span>
              <span style="display:flex;align-items:center;gap:8px;"><i data-lucide="eye" style="width:18px;height:18px;opacity:0.7;"></i> <?php echo number_format($rapport['vues']); ?> vues</span>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:16px;">
            <?php if (!empty($rapport['image_couverture'])): ?>
              <img src="<?php echo $rapport['image_couverture']; ?>" alt="Cover" style="width:220px; height:140px; object-fit:cover; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.15); border: 3px solid #fff;">
            <?php endif; ?>
            <button class="btn-pdf" onclick="exportToPDF()">
                <i data-lucide="download-cloud" style="width:18px;height:18px;"></i>
                Exporter le Rapport
            </button>
        </div>
    </div>
  </div>
</div>

<div class="veille-layout" style="grid-template-columns: 1fr; margin-top: -30px;">
    <article class="report-full-content card" style="background:var(--bg-primary); border-radius:32px; padding:var(--space-10); box-shadow: var(--shadow-xl); border: 1px solid var(--border-color);">
        
        <div class="tab-controls">
          <button class="tab-btn active" data-target="tab-analyse"><i data-lucide="file-text" style="width:18px;height:18px;"></i> Analyse Stratégique</button>
          <button class="tab-btn" data-target="tab-data"><i data-lucide="bar-chart-3" style="width:18px;height:18px;"></i> Indicateurs & Données</button>
        </div>

        <div id="tab-analyse" class="tab-content active">
            <div class="report-content-body">
                <p class="lead-text">
                    Ce rapport présente une analyse approfondie du marché du travail pour le secteur <strong><?php echo htmlspecialchars($rapport['secteur_principal']); ?></strong> dans la région de <strong><?php echo htmlspecialchars($rapport['region']); ?></strong>.
                </p>
                
                <div style="white-space: pre-wrap; margin-bottom:var(--space-10); font-size:1.1rem; line-height:1.8; color:var(--text-secondary);"><?php echo htmlspecialchars($rapport['description']); ?></div>

                <div class="rich-content">
                    <?php if (!empty($rapport['contenu_detaille'])): ?>
                        <?php echo $rapport['contenu_detaille']; ?>
                    <?php else: ?>
                        <div style="padding:40px; text-align:center; background:var(--bg-secondary); border-radius:20px; border:2px dashed var(--border-color);">
                            <i data-lucide="info" style="width:32px;height:32px;margin-bottom:12px;opacity:0.3;"></i>
                            <p style="opacity:0.5;">Détails d'analyse non disponibles pour ce rapport.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top:var(--space-12);">
                    <h3 style="margin-bottom:var(--space-6); color:var(--text-primary); display:flex; align-items:center; gap:12px; font-size:1.5rem; font-weight:800;">
                        <i data-lucide="coins" style="width:28px;height:28px;color:var(--accent-primary);"></i>
                        Analyse Comparative des Salaires
                    </h3>
                    
                    <!-- Salary Visualizer -->
                    <?php 
                        $min = $rapport['salaire_min_global'];
                        $max = $rapport['salaire_max_global'];
                        $avg = $rapport['salaire_moyen_global'];
                        $range = $max - $min;
                        $avgPos = (($avg - $min) / ($range ?: 1)) * 100;
                    ?>
                    <div class="salary-visualizer">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-weight:700; font-size:0.9rem; color:var(--text-secondary);">
                            <span>Échelle Salariale (TND)</span>
                            <span style="color:var(--accent-primary);">Moyenne : <?php echo number_format($avg, 0, ',', ' '); ?> TND</span>
                        </div>
                        <div class="salary-track">
                            <div class="salary-marker min"><?php echo number_format($min, 0, ',', ' '); ?></div>
                            <div class="salary-marker max" style="left:100%;"><?php echo number_format($max, 0, ',', ' '); ?></div>
                            <div class="salary-marker avg" style="left:<?php echo $avgPos; ?>%;"><?php echo number_format($avg, 0, ',', ' '); ?></div>
                            <div class="salary-range-bar" style="left:0%; width:100%;"></div>
                        </div>
                    </div>

                    <div class="grid grid-3 gap-6" style="margin-top:var(--space-8);">
                        <div class="salary-card">
                            <span style="font-size:12px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Minimum</span>
                            <div style="font-size:1.8rem; font-weight:800; color:var(--text-primary);"><?php echo number_format($min, 0, ',', ' '); ?> <span style="font-size:14px; opacity:0.5;">TND</span></div>
                        </div>
                        <div class="salary-card" style="border-color:var(--accent-primary); background:rgba(107, 52, 163, 0.02);">
                            <span style="font-size:12px; font-weight:700; color:var(--accent-primary); text-transform:uppercase;">Moyen</span>
                            <div style="font-size:1.8rem; font-weight:800; color:var(--accent-primary);"><?php echo number_format($avg, 0, ',', ' '); ?> <span style="font-size:14px; opacity:0.5;">TND</span></div>
                        </div>
                        <div class="salary-card">
                            <span style="font-size:12px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Maximum</span>
                            <div style="font-size:1.8rem; font-weight:800; color:var(--text-primary);"><?php echo number_format($max, 0, ',', ' '); ?> <span style="font-size:14px; opacity:0.5;">TND</span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(count($donnees) > 0): ?>
            <div style="margin-top:var(--space-10); padding:var(--space-6); background:var(--bg-secondary); border-radius:24px; text-align:center; border:1px solid var(--border-color);">
                <p style="margin-bottom:var(--space-5); font-weight:600; color:var(--text-primary);">Prêt pour une analyse granulaire ?</p>
                <button class="btn btn-primary" onclick="document.querySelector('[data-target=\'tab-data\']').click();" style="padding:12px 30px; border-radius:100px;">
                    Explorer les Données Sources <i data-lucide="chevron-right" style="width:18px;height:18px;margin-left:6px;"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div id="tab-data" class="tab-content">
            <div class="report-data-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-8);">
                    <div>
                        <h3 style="margin-bottom:8px; display:flex; align-items:center; gap:12px; color:var(--text-primary); font-size:1.5rem; font-weight:800;">
                            <i data-lucide="bar-chart-big" style="width:28px;height:28px;color:#10b981;"></i>
                            Indicateurs Détaillés
                        </h3>
                        <p class="text-secondary">Base de données compilée pour l'analyse sectorielle <?php echo htmlspecialchars($rapport['secteur_principal']); ?>.</p>
                    </div>
                </div>

                <?php if(count($donnees) > 0): ?>
                <div class="table-responsive" style="border:1px solid var(--border-color); border-radius:24px; overflow:hidden; background:var(--bg-primary); box-shadow:var(--shadow-sm);">
                    <table class="data-table" style="width:100%; border-collapse: collapse; margin:0;">
                        <thead>
                            <tr style="background: var(--bg-secondary); text-align: left;">
                                <th style="padding:var(--space-5); font-weight:700; font-size:12px; text-transform:uppercase; color:var(--text-tertiary); letter-spacing:1px;">Domaine</th>
                                <th style="padding:var(--space-5); font-weight:700; font-size:12px; text-transform:uppercase; color:var(--text-tertiary); letter-spacing:1px;">Compétence Clé</th>
                                <th style="padding:var(--space-5); font-weight:700; font-size:12px; text-transform:uppercase; color:var(--text-tertiary); letter-spacing:1px;">Fourchette (TND)</th>
                                <th style="padding:var(--space-5); font-weight:700; font-size:12px; text-transform:uppercase; color:var(--text-tertiary); letter-spacing:1px;">Intensité Demande</th>
                                <th style="padding:var(--space-5); font-weight:700; font-size:12px; text-transform:uppercase; color:var(--text-tertiary); letter-spacing:1px; text-align:right;">Collecte</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $demandeMap = [4 => 'Critique', 3 => 'Élevée', 2 => 'Stable', 1 => 'Émergente'];
                            foreach($donnees as $d): 
                                $demandeTexte = isset($demandeMap[$d['demande']]) ? $demandeMap[$d['demande']] : $d['demande'];
                                $badgeClass = ($d['demande'] >= 3) ? 'badge-info' : 'badge-secondary';
                            ?>
                            <tr style="border-bottom:1px solid var(--border-color); transition: background 0.2s ease;">
                                <td style="padding:var(--space-5);">
                                    <div style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($d['domaine']); ?></div>
                                </td>
                                <td style="padding:var(--space-5);">
                                    <div style="font-weight:700; color:var(--accent-primary);"><?php echo htmlspecialchars($d['competence']); ?></div>
                                    <?php if (!empty($d['description'])): ?>
                                        <div style="font-size:12px; color:var(--text-tertiary); margin-top:4px;"><?php echo htmlspecialchars($d['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:var(--space-5);">
                                    <div style="font-family:'JetBrains Mono', monospace; font-weight:600; font-size:0.9rem;">
                                        <?php echo number_format($d['salaire_min'], 0, '.', ' '); ?> - <?php echo number_format($d['salaire_max'], 0, '.', ' '); ?>
                                    </div>
                                    <div style="font-size:11px; color:var(--text-tertiary);">Moy: <?php echo number_format($d['salaire_moyen'], 0, '.', ' '); ?> TND</div>
                                </td>
                                <td style="padding:var(--space-5);"><span class="badge <?php echo $badgeClass; ?>" style="padding:6px 12px; border-radius:8px; font-weight:700;"><?php echo $demandeTexte; ?></span></td>
                                <td style="padding:var(--space-5); color:var(--text-tertiary); text-align:right; font-size:12px;"><?php echo date('M Y', strtotime($d['date_collecte'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div style="padding:60px; text-align:center; opacity:0.5;">
                        <i data-lucide="layers" style="width:48px;height:48px;margin-bottom:16px;"></i>
                        <p>Aucune donnée granulaire disponible pour ce rapport.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </article>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Scroll Progress Indicator
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        const progressBar = document.getElementById('reading-progress');
        if(progressBar) {
            progressBar.style.width = scrolled + '%';
        }
    });

    // Tab Interface with Animation Reset
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const targetId = btn.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            targetContent.classList.add('active');
            
            // Re-run icons for new content if any
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    });
});

function exportToPDF() {
    // Reveal all for printing
    document.querySelectorAll('.tab-content').forEach(el => {
        el.style.display = 'block';
        el.style.opacity = '1';
        el.style.transform = 'none';
    });
    window.print();
    // Restore
    setTimeout(() => {
        location.reload(); // Cleanest way to restore tab state after print
    }, 500);
}
</script>
