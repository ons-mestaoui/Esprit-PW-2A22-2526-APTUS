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
.progress-container { position: fixed; top: 0; left: 0; width: 100%; height: 4px; background: transparent; z-index: 9999; }
.progress-bar { height: 100%; background: linear-gradient(90deg, var(--accent-primary), #a855f7); width: 0%; transition: width 0.1s ease; border-radius: 0 4px 4px 0; }
.report-hero { margin-bottom: var(--space-8); background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(168,85,247,0.03) 100%); padding: var(--space-8); border-radius: var(--radius-lg); border: 1px solid rgba(99,102,241,0.1); position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
.report-hero::after { content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
.tab-controls { display: flex; gap: var(--space-3); margin-bottom: var(--space-8); border-bottom: 2px solid var(--border-color); padding-bottom: var(--space-4); }
.tab-btn { background: transparent; border: none; font-size: 1.05rem; font-weight: 600; color: var(--text-secondary); padding: var(--space-3) var(--space-6); border-radius: var(--radius-md); cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 10px; }
.tab-btn.active { background: var(--accent-primary); color: #fff; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35); transform: translateY(-2px); }
.tab-btn:hover:not(.active) { background: var(--bg-main); color: var(--text-primary); }
.tab-content { display: none; animation: fadeUp 0.5s ease forwards; opacity: 0; transform: translateY(15px); }
.tab-content.active { display: block; }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
.salary-card { transition: all 0.3s ease; background: var(--bg-main); padding: var(--space-5); border-radius: var(--radius-md); border: 1px solid var(--border-color); position: relative; overflow: hidden; }
.salary-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); border-color: var(--accent-primary); }
.salary-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--border-color); transition: background 0.3s ease; }
.salary-card:hover::before { background: var(--accent-primary); }
.data-table-interactive tbody tr { transition: background 0.2s ease, transform 0.2s ease; }
.data-table-interactive tbody tr:hover { background: rgba(99, 102, 241, 0.03); transform: translateX(4px); }
.lead-text { font-size: 1.25rem; font-weight: 500; line-height: 1.8; color: var(--text-primary); border-left: 4px solid var(--accent-primary); padding-left: var(--space-4); margin-bottom: var(--space-6); background: linear-gradient(90deg, rgba(99,102,241,0.05) 0%, transparent 100%); padding: var(--space-4) var(--space-5); border-radius: 0 var(--radius-md) var(--radius-md) 0; }
/* PDF Export Button */
.btn-pdf { display:inline-flex; align-items:center; gap:8px; padding:8px 18px; font-size:13px; font-weight:700;
    background: linear-gradient(135deg, #ef4444, #dc2626); color:#fff; border:none; border-radius:20px; cursor:pointer;
    box-shadow: 0 4px 12px rgba(239,68,68,0.35); transition: all 0.2s ease; text-decoration:none; }
.btn-pdf:hover { transform:translateY(-2px); box-shadow: 0 6px 20px rgba(239,68,68,0.45); color:#fff; }
/* @ Print / PDF Stylesheet */
@media print {
    .progress-container, nav, header, .tab-controls, footer, .btn, .btn-pdf,
    [class*="sidebar"], [class*="back-to"], a[href="veille_feed.php"] { display: none !important; }
    body, html { background: #fff !important; color: #111 !important; font-family: Georgia, serif; }
    .report-hero { border: none; background: none !important; box-shadow: none; padding: 0; }
    .report-hero::after { display: none; }
    h1.page-header__title { font-size: 28px !important; }
    .tab-content { display: block !important; opacity: 1 !important; transform: none !important; }
    .salary-card { border: 1px solid #ddd; box-shadow: none; }
    .data-table-interactive tbody tr:hover { background: none; transform: none; }
    .veille-layout { grid-template-columns: 1fr !important; }
    a::after { content: none !important; }
    .pdf-header { display: block !important; }
    @page { margin: 2cm; }
}
.pdf-header { display: none; text-align:center; border-bottom:2px solid #6366f1; padding-bottom:16px; margin-bottom:24px; }
.pdf-header__logo { font-size:22px; font-weight:800; color:#6366f1; letter-spacing:-0.5px; }
.pdf-header__meta { font-size:13px; color:#64748b; margin-top:6px; }
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
  <a href="veille_feed.php" class="btn btn-sm btn-ghost" style="margin-bottom:var(--space-6); padding:0; color:var(--text-secondary);"><i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Retour aux rapports</a>
  
  <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:var(--space-4); position: relative; z-index: 2;">
      <div>
        <span class="badge badge-info mb-3" style="display:inline-block; font-size:13px; padding:6px 12px;"><?php echo htmlspecialchars($rapport['secteur_principal']); ?></span>
        <h1 class="page-header__title" style="margin-bottom:var(--space-3); font-size: 2.5rem; line-height: 1.2; font-weight: 800; color: var(--text-primary);">
          <?php echo htmlspecialchars($rapport['titre']); ?>
        </h1>
        <div class="flex gap-5 text-secondary" style="margin-top:var(--space-4); font-size:0.95rem; font-weight:500;">
            <span style="display:flex;align-items:center;gap:8px;"><div class="avatar avatar-sm avatar-initials" style="width:24px;height:24px;font-size:10px;">A</div> <?php echo htmlspecialchars($rapport['auteur']); ?></span>
            <span style="display:flex;align-items:center;gap:6px;"><i data-lucide="calendar" style="width:16px;height:16px;"></i> <?php echo date('d M. Y', strtotime($rapport['date_publication'])); ?></span>
            <span style="display:flex;align-items:center;gap:6px;"><i data-lucide="eye" style="width:16px;height:16px;"></i> <?php echo $rapport['vues']; ?> lectures</span>
        </div>
        <!-- PDF Export Button -->
        <div style="margin-top:20px;">
            <button class="btn-pdf" onclick="exportToPDF()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exporter en PDF
            </button>
        </div>
      </div>
      <?php if (!empty($rapport['image_couverture'])): ?>
        <div style="flex-shrink:0;">
            <img src="<?php echo $rapport['image_couverture']; ?>" alt="Cover" style="width:180px; height:120px; object-fit:cover; border-radius:12px; border:2px solid #fff; box-shadow:0 8px 16px rgba(0,0,0,0.1);">
        </div>
      <?php endif; ?>
  </div>
</div>

<div class="veille-layout" style="grid-template-columns: 1fr; margin-top: -20px;">
    <article class="report-full-content card animate-on-scroll" style="background:var(--bg-secondary); border-radius:var(--radius-lg); padding:var(--space-8); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        
        <div class="tab-controls">
          <button class="tab-btn active" data-target="tab-analyse"><i data-lucide="file-text" style="width:20px;height:20px;"></i> Rapport d'Analyse</button>
          <button class="tab-btn" data-target="tab-data"><i data-lucide="database" style="width:20px;height:20px;"></i> Données Brutes (Explorateur)</button>
        </div>

        <div id="tab-analyse" class="tab-content active">
            <div class="report-content-body" style="font-size: 1.1rem; line-height: 1.8; color:var(--text-secondary);">
                <p class="lead-text" style="color:var(--text-primary);">
                    Synthèse du rapport publié pour la région <strong><?php echo htmlspecialchars($rapport['region']); ?></strong> concernant le secteur <strong><?php echo htmlspecialchars($rapport['secteur_principal']); ?></strong>.
                </p>
                <div style="white-space: pre-wrap; margin-bottom:var(--space-8); padding: var(--space-4); background: var(--bg-main); border-radius: var(--radius-sm); border-left: 4px solid var(--accent-primary);"><?php echo htmlspecialchars($rapport['description']); ?></div>

                <div class="rich-content" style="margin-bottom: var(--space-8);">
                    <?php if (!empty($rapport['contenu_detaille'])): ?>
                        <?php echo $rapport['contenu_detaille']; ?>
                    <?php else: ?>
                        <p style="font-style:italic; opacity:0.6;">Aucun contenu détaillé disponible.</p>
                    <?php endif; ?>
                </div>

                <h3 style="margin-top:var(--space-8); margin-bottom:var(--space-4); color:var(--text-primary); display:flex; align-items:center; gap:10px;">
                    <span style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; background:var(--accent-primary); color:#fff; border-radius:50%; font-size:16px;">$</span>
                    Tendance globale des Salaires (<?php echo htmlspecialchars($rapport['secteur_principal']); ?>)
                </h3>
                <div class="grid grid-3 gap-6" style="margin-top:var(--space-5); margin-bottom:var(--space-8);">
                    <div class="salary-card">
                        <div style="font-size:var(--fs-sm); font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--text-secondary); margin-bottom:8px;">Salaire Min. Global</div>
                        <div style="font-size:var(--fs-2xl); font-weight:800; color:var(--text-primary);"><?php echo number_format($rapport['salaire_min_global'], 0, ',', ' '); ?> <span style="font-size:18px; font-weight:500; color:var(--text-tertiary);">TND</span></div>
                    </div>
                    <div class="salary-card" style="border-color:var(--accent-primary); box-shadow: 0 4px 15px rgba(99,102,241,0.05);">
                        <div style="font-size:var(--fs-sm); font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--accent-primary); margin-bottom:8px;"><i data-lucide="trending-up" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> Salaire Moyen Global</div>
                        <div style="font-size:var(--fs-2xl); font-weight:800; color:var(--text-primary);"><?php echo number_format($rapport['salaire_moyen_global'], 0, ',', ' '); ?> <span style="font-size:18px; font-weight:500; color:var(--text-tertiary);">TND</span></div>
                    </div>
                    <div class="salary-card">
                        <div style="font-size:var(--fs-sm); font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--text-secondary); margin-bottom:8px;">Salaire Max. Global</div>
                        <div style="font-size:var(--fs-2xl); font-weight:800; color:var(--text-primary);"><?php echo number_format($rapport['salaire_max_global'], 0, ',', ' '); ?> <span style="font-size:18px; font-weight:500; color:var(--text-tertiary);">TND</span></div>
                    </div>
                </div>
            </div>
            
            <?php if(count($donnees) > 0): ?>
            <div style="margin-top:var(--space-8); padding:var(--space-4); background:rgba(99,102,241,0.05); border-radius:var(--radius-md); text-align:center;">
                <p style="margin-bottom:var(--space-4); font-weight:500;">Vous voulez explorer les chiffres par vous-même ?</p>
                <button class="btn btn-primary" onclick="document.querySelector('[data-target=\'tab-data\']').click();">
                    Basculer vers l'Explorateur de Données <i data-lucide="arrow-right" style="width:16px;height:16px;margin-left:4px;"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div id="tab-data" class="tab-content">
            <div class="report-data-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-6);">
                    <div>
                        <h3 style="margin-bottom:8px; display:flex; align-items:center; gap:10px; color:var(--text-primary);">
                            <span style="display:flex; align-items:center; justify-content:center; width:36px; height:36px; background:rgba(16, 185, 129, 0.1); color:#10b981; border-radius:8px;">
                                <i data-lucide="database" style="width:20px;height:20px;"></i>
                            </span>
                            Données sources
                        </h3>
                        <p class="text-secondary text-sm">Voici les données brutes synthétisées qui appuient cette recherche.</p>
                    </div>
                </div>

                <?php if(count($donnees) > 0): ?>
                <div class="table-responsive" style="border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
                    <table class="data-table data-table-interactive" style="width:100%; border-collapse: collapse; margin:0;">
                        <thead>
                            <tr style="background: var(--bg-main); text-align: left; border-bottom:2px solid var(--border-color);">
                                <th style="padding:var(--space-4); font-weight:600; font-size:var(--fs-sm); text-transform:uppercase; color:var(--text-secondary);">Domaine</th>
                                <th style="padding:var(--space-4); font-weight:600; font-size:var(--fs-sm); text-transform:uppercase; color:var(--text-secondary);">Compétence</th>
                                <th style="padding:var(--space-4); font-weight:600; font-size:var(--fs-sm); text-transform:uppercase; color:var(--text-secondary);">Fourchette Salariale (TND)</th>
                                <th style="padding:var(--space-4); font-weight:600; font-size:var(--fs-sm); text-transform:uppercase; color:var(--text-secondary);">Demande</th>
                                <th style="padding:var(--space-4); font-weight:600; font-size:var(--fs-sm); text-transform:uppercase; color:var(--text-secondary); text-align:right;">Date de collecte</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $demandeMap = [4 => 'Très forte', 3 => 'Forte', 2 => 'Modérée', 1 => 'Faible'];
                            foreach($donnees as $d): 
                                $demandeTexte = isset($demandeMap[$d['demande']]) ? $demandeMap[$d['demande']] : $d['demande'];
                            ?>
                            <tr style="border-bottom:1px solid var(--border-color); font-size:0.95rem;">
                                <td style="padding:var(--space-4);">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="width:8px; height:8px; border-radius:50%; background:var(--accent-primary);"></div> 
                                        <?php echo htmlspecialchars($d['domaine']); ?>
                                    </div>
                                </td>
                                <td style="padding:var(--space-4); font-weight:600; color:var(--text-primary);">
                                    <?php echo htmlspecialchars($d['competence']); ?>
                                    <?php if (!empty($d['description'])): ?>
                                        <div style="font-size:12px; font-weight:400; color:var(--text-tertiary); margin-top:4px;"><?php echo htmlspecialchars($d['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:var(--space-4);">
                                    <span style="font-family:monospace; background:var(--bg-main); padding:4px 8px; border-radius:4px;"><?php echo $d['salaire_min'] . ' - ' . $d['salaire_max']; ?></span> 
                                    <span style="color:var(--text-tertiary); font-size:0.85rem; margin-left:4px;">(Moy: <?php echo $d['salaire_moyen']; ?>)</span>
                                </td>
                                <td style="padding:var(--space-4);"><span class="badge badge-info" style="padding:4px 10px;"><?php echo $demandeTexte; ?></span></td>
                                <td style="padding:var(--space-4); color:var(--text-secondary); text-align:right;"><?php echo date('d M Y', strtotime($d['date_collecte'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p>Aucune donnée brute attachée à ce rapport.</p>
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

    // Tab Interface
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const targetId = btn.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });
});

function exportToPDF() {
    // Ensure all tab content is visible for printing
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.add('active');
        el.style.display = 'block';
        el.style.opacity = '1';
        el.style.transform = 'none';
    });
    window.print();
    // Restore after print dialog closes
    setTimeout(() => {
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(el => el.classList.remove('active'));
        const firstContent = document.getElementById('tab-analyse');
        if (firstContent) {
            firstContent.classList.add('active');
        }
    }, 1000);
}

</script>
