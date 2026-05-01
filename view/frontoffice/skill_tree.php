<?php
/**
 * ============================================================
 * skill_tree.php — Version Obsidian Neural Map
 * ============================================================
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Skill Tree — Parcours de Compétences - Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';

    $formationC = new FormationController();
    $id_user    = $_SESSION['user_id'] ?? 10;

    // 1. Données pour la Timeline
    if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
        $skillChain = $formationC->getSkillTree((int)$_GET['id'], $id_user);
        $viewMode   = 'chain';
    } else {
        $allTrees = $formationC->getAllFormationsWithSkillTree($id_user);
        $viewMode = 'all';
    }

    // 2. Données pour la Skill Map (Récupération récursive)
    $toutesLesFormations = $formationC->listerFormations()->fetchAll();
    $formationsData = [];
    foreach ($toutesLesFormations as $f) {
        $data = $formationC->getFormationWithPrerequisite((int)$f['id_formation'], $id_user);
        $isUnlocked = true;
        if (!empty($data['prerequis_id'])) {
            $prereq = $formationC->getFormationWithPrerequisite((int)$data['prerequis_id'], $id_user);
            $isUnlocked = ($prereq && $prereq['ma_progression'] >= 100);
        }
        $data['is_unlocked'] = $isUnlocked;
        $formationsData[] = $data;
    }

    // Stats
    $globalDone = 0;
    foreach ($formationsData as $f) { if ($f['ma_progression'] >= 100) $globalDone++; }
    $globalTotal = count($formationsData);
    $globalPercent = ($globalTotal > 0) ? round(($globalDone / $globalTotal) * 100) : 0;

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- Import Force Graph & Confetti -->
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://unpkg.com/force-graph"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

<style>
.skill-tree-page { max-width: 1200px; margin: 0 auto; padding: 1rem 0 4rem; }
.skill-tree-hero { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2rem; margin-bottom: 2.5rem; position: relative; }
.skill-tree-hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--gradient-primary); border-radius: 20px 20px 0 0; }

/* Switcher */
.view-switcher { display: flex; gap: 0.5rem; margin-bottom: 2rem; background: var(--bg-secondary); padding: 0.4rem; border-radius: 14px; width: fit-content; }
.switch-btn { padding: 0.6rem 1.2rem; border-radius: 10px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: 0.3s; background: transparent; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem; }
.switch-btn.active { background: var(--bg-card); color: var(--primary-cyan); box-shadow: var(--shadow-md); }

/* Timeline */
.timeline { position: relative; padding-left: 2.5rem; margin-top: 1rem; }
.timeline::before { content: ''; position: absolute; left: 0.9rem; top: 0.5rem; bottom: 0; width: 2px; background: var(--border-color); }
.timeline-node { position: relative; margin-bottom: 1.5rem; }
.timeline-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; }
.timeline-card:hover { transform: translateX(4px); box-shadow: var(--shadow-sm); }
.timeline-dot { position: absolute; left: -2.15rem; top: 1.25rem; width: 1.25rem; height: 1.25rem; border-radius: 50%; background: var(--bg-secondary); border: 3px solid var(--bg-card); z-index: 5; box-shadow: 0 0 0 1px var(--border-color); }
.timeline-dot--done { background: #10b981; border-color: var(--bg-card); box-shadow: 0 0 0 1px #10b981; }
.timeline-dot--unlocked { background: var(--primary-cyan); border-color: var(--bg-card); box-shadow: 0 0 0 1px var(--primary-cyan); }

/* Obsidian Map Container */
#view-map-container { 
    display: none; 
    background: #0f172a; 
    border: 1px solid #1e293b; 
    border-radius: 20px; 
    height: 650px; 
    position: relative; 
    overflow: hidden; 
}
.map-hud { 
    position: absolute; 
    bottom: 1.5rem; 
    right: 1.5rem; 
    width: 320px; 
    background: var(--bg-card); 
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-color); 
    padding: 1.5rem; 
    border-radius: 20px; 
    box-shadow: var(--shadow-xl); 
    display: none; 
    z-index: 100;
}

.map-controls {
    position: absolute;
    top: 1.5rem;
    left: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 10;
}
.control-btn {
    width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border-color); 
    display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);
}
</style>

<div class="skill-tree-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">     
        <a href="formations_catalog.php" class="cta-back" style="text-decoration: none; color: var(--text-secondary); font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="arrow-left" style="width: 16px;"></i> Retour
        </a>
        <div class="view-switcher">
            <button class="switch-btn active" onclick="switchView('timeline')" id="btn-timeline">
                <i data-lucide="list" style="width: 16px;"></i> Timeline
            </button>
            <button class="switch-btn" onclick="switchView('map')" id="btn-map">
                <i data-lucide="share-2" style="width: 16px;"></i> Neural Map
            </button>
        </div>
    </div>

    <!-- HUD Progression -->
    <div class="skill-tree-hero">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">🧠 Réseau de Compétences</h1>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="flex: 1; height: 8px; background: var(--bg-secondary); border-radius: 10px; overflow: hidden;">    
                <div style="width: <?php echo $globalPercent; ?>%; height: 100%; background: var(--gradient-primary);"></div>
            </div>
            <span style="font-weight: 800; color: var(--primary-cyan);"><?php echo $globalPercent; ?>%</span>  
        </div>
    </div>

    <!-- VUE 1 : TIMELINE -->
    <div id="view-timeline">
        <?php if ($viewMode === 'chain' && !empty($skillChain)): ?>
            <div class="timeline">
                <?php foreach ($skillChain as $i => $step): 
                    $isDone = ($step['ma_progression'] >= 100);
                    $isUnlocked = $step['is_unlocked'];
                ?>
                <div class="timeline-node">
                    <div class="timeline-dot <?php echo $isDone ? 'timeline-dot--done' : ($isUnlocked ? 'timeline-dot--unlocked' : ''); ?>"></div>
                    <div class="timeline-card <?php echo !$isUnlocked ? 'timeline-card--locked' : ''; ?>">     
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($step['titre']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.5rem 0 1rem;"><?php echo htmlspecialchars(substr(strip_tags($step['description'] ?? ''), 0, 100)); ?>...</p>
                        <?php if ($isUnlocked): ?>
                            <a href="formation_detail.php?id=<?php echo $step['id_formation']; ?>" class="btn btn-primary btn-sm">Continuer</a>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 0.8rem;">🔒 Verrouillé</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($viewMode === 'all' && !empty($allTrees)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($allTrees as $tree): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s;" class="card-formation-hover">
                    <h4 style="font-size: 0.75rem; color: var(--primary-cyan); text-transform: uppercase; margin-bottom: 1rem; font-weight: 800; display: flex; align-items: center; gap: 0.4rem;">
                        <i data-lucide="network" style="width: 14px; height: 14px;"></i> <?php echo htmlspecialchars($tree['root']['domaine']); ?>
                    </h4>
                    <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: var(--text-primary);">
                        <?php echo htmlspecialchars($tree['root']['titre']); ?>
                    </h3>
                    
                    <div style="background: var(--bg-secondary); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                        <p style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.5rem; text-transform: uppercase;">Étapes du parcours :</p>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                            <?php $nodes = array_merge([$tree['root']], $tree['children']);
                            foreach ($nodes as $index => $node): 
                                $isDone = ($node['ma_progression'] >= 100);
                            ?>
                            <li style="font-size: 0.85rem; color: <?php echo $isDone ? '#10b981' : '#475569'; ?>; display: flex; align-items: center; gap: 0.5rem;">
                                <?php if ($isDone): ?>
                                    <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                <?php else: ?>
                                    <span style="width: 14px; height: 14px; border-radius: 50%; border: 2px solid #cbd5e1; display: inline-block;"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($node['titre']); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <a href="skill_tree.php?id=<?php echo end($nodes)['id_formation']; ?>" class="btn btn-primary" style="width: 100%; text-align: center; display: block; font-size: 0.85rem;">
                        Explorer ce parcours →
                    </a>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- VUE 2 : NEURAL MAP -->
    <div id="view-map-container">
        <div class="map-controls">
            <button class="control-btn" onclick="graph.zoom(graph.zoom() * 1.5)"><i data-lucide="plus"></i></button>
            <button class="control-btn" onclick="graph.zoom(graph.zoom() / 1.5)"><i data-lucide="minus"></i></button>
            <button class="control-btn" onclick="graph.zoomToFit(400)"><i data-lucide="maximize"></i></button>
        </div>
        <div id="neural-graph"></div>
        <div id="map-hud" class="map-hud">
            <h4 id="hud-title" style="font-weight: 800; margin-bottom: 0.5rem; color: var(--text-primary);"></h4>
            <p id="hud-desc" style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem;"></p>
            <div id="hud-action"></div>
        </div>
    </div>
</div>

<script>
const formationsRaw = <?php echo json_encode($formationsData); ?>;
const gData = {
    nodes: formationsRaw.map(f => ({
        id: f.id_formation,
        name: f.titre,
        val: 12,
        isDone: f.ma_progression >= 100,
        isUnlocked: f.is_unlocked,
        domaine: f.domaine,
        description: f.description
    })),
    links: formationsRaw.filter(f => f.prerequis_id).map(f => ({ source: f.prerequis_id, target: f.id_formation }))
};

let graph;

function switchView(view) {
    document.getElementById('view-timeline').style.display = view === 'timeline' ? 'block' : 'none';
    document.getElementById('view-map-container').style.display = view === 'map' ? 'block' : 'none';
    document.getElementById('btn-timeline').classList.toggle('active', view === 'timeline');
    document.getElementById('btn-map').classList.toggle('active', view === 'map');
    if (view === 'map' && !graph) initGraph();
}

function initGraph() {
    graph = ForceGraph()(document.getElementById('neural-graph'))
        .graphData(gData)
        // --- GRAVITY PULL : Keeps all neurons anchored to the center ---
        .d3Force('center', d3.forceCenter(0, 0).strength(1))
        .d3Force('charge', d3.forceManyBody().strength(-150))
        .d3Force('radial', d3.forceRadial(80, 0, 0).strength(0.08))
        // ----------------------------------------------------------------
        .nodeRelSize(6)
        .nodeLabel('name')
        .nodeCanvasObject((node, ctx, globalScale) => {
            ctx.beginPath();
            ctx.arc(node.x, node.y, 5, 0, 2 * Math.PI, false);
            ctx.fillStyle = node.isDone ? '#10b981' : (node.isUnlocked ? '#0ea5e9' : '#334155');
            ctx.fill();
            if (globalScale > 1.5) {
                ctx.font = `${12/globalScale}px Inter`;
                ctx.fillStyle = 'rgba(255,255,255,0.8)';
                ctx.textAlign = 'center';
                ctx.fillText(node.name, node.x, node.y + 10);
            }
        })
        .linkColor(() => '#1e293b')
        .backgroundColor('#0f172a')
        .onNodeClick(node => {
            const hud = document.getElementById('map-hud');
            document.getElementById('hud-title').textContent = node.name;
            document.getElementById('hud-desc').textContent = node.description.replace(/<[^>]*>?/gm, '').substring(0, 100) + '...';
            document.getElementById('hud-action').innerHTML = node.isUnlocked ? 
                `<a href="formation_detail.php?id=${node.id}" class="btn btn-primary" style="width:100%; display:block; text-align:center;">🚀 Continuer</a>` : 
                `<div style="text-align:center; color:#94a3b8; font-size:0.8rem;">🔒 Verrouillé</div>`;
            hud.style.display = 'block';
            graph.centerAt(node.x, node.y, 1000);
            graph.zoom(3, 1000);
        });
    setTimeout(() => graph.zoomToFit(400), 500);
}

document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>