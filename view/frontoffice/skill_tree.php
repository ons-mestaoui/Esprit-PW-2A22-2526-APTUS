<?php
/**
 * ============================================================
 * skill_tree.php — Version Obsidian Neural Map (Optimisée AJAX)
 * ============================================================
 */
$pageTitle = "Skill Tree — Parcours de Compétences - Aptus AI";

require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();
SessionManager::requireLogin();
$id_user = SessionManager::getUserId() ?? 0;

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';

    $formationC = new FormationController();
    $start_time = microtime(true);
    $treeId = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $treeData = $formationC->getSkillTreePageData($id_user, $treeId);
    header('X-Process-Time: ' . (microtime(true) - $start_time));

    $viewMode = $treeData['viewMode'];
    $skillChain = $treeData['skillChain'];
    $allTrees = $treeData['allTrees'];
    $globalPercent = $treeData['globalPercent'];

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- Robust Visualization Engine Load (CDN with Fallback) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>window.d3 || document.write('<script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"><\/script>')</script>

<script src="https://cdn.jsdelivr.net/npm/force-graph@1.43.0/dist/force-graph.min.js"></script>
<script>window.ForceGraph || document.write('<script src="https://unpkg.com/force-graph@1.43.0/dist/force-graph.min.js"><\/script>')</script>

<style>
    .skill-tree-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 0 4rem;
    }

    .skill-tree-hero {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2.5rem;
        position: relative;
    }

    .skill-tree-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        border-radius: 20px 20px 0 0;
    }

    /* Switcher */
    .view-switcher {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: var(--bg-secondary);
        padding: 0.4rem;
        border-radius: 14px;
        width: fit-content;
    }

    .switch-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 700;
        transition: 0.3s;
        background: transparent;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .switch-btn.active {
        background: var(--bg-card);
        color: var(--primary-cyan);
        box-shadow: var(--shadow-md);
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 2.5rem;
        margin-top: 1rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.9rem;
        top: 0.5rem;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-node {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .timeline-card:hover {
        transform: translateX(4px);
        box-shadow: var(--shadow-sm);
    }

    .timeline-dot {
        position: absolute;
        left: -2.15rem;
        top: 1.25rem;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        background: var(--bg-secondary);
        border: 3px solid var(--bg-card);
        z-index: 5;
        box-shadow: 0 0 0 1px var(--border-color);
    }

    .timeline-dot--done {
        background: #10b981;
        border-color: var(--bg-card);
        box-shadow: 0 0 0 1px #10b981;
    }

    .timeline-dot--unlocked {
        background: var(--primary-cyan);
        border-color: var(--bg-card);
        box-shadow: 0 0 0 1px var(--primary-cyan);
    }

    /* Neural Map Container */
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

    .map-loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .spin {
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
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
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-secondary);
    }
</style>

<div class="skill-tree-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <a href="formations_catalog.php" class="cta-back"
            style="text-decoration: none; color: var(--text-secondary); font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
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
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">🧠 Réseau de
            Compétences</h1>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="flex: 1; height: 8px; background: var(--bg-secondary); border-radius: 10px; overflow: hidden;">
                <div style="width: <?php echo $globalPercent; ?>%; height: 100%; background: var(--gradient-primary);">
                </div>
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
                        <div
                            class="timeline-dot <?php echo $isDone ? 'timeline-dot--done' : ($isUnlocked ? 'timeline-dot--unlocked' : ''); ?>">
                        </div>
                        <div class="timeline-card">
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);">
                                <?php echo htmlspecialchars($step['titre']); ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.5rem 0 1rem;">
                                <?php echo htmlspecialchars(substr(strip_tags($step['description'] ?? ''), 0, 100)); ?>...</p>
                            <?php if ($isUnlocked): ?>
                                <a href="formation_detail.php?id=<?php echo $step['id_formation']; ?>"
                                    class="btn btn-primary btn-sm">
                                    <?php echo ($step['ma_progression'] > 0 || !empty($step['mon_statut'])) ? 'Continuer' : 'S\'inscrire'; ?>
                                </a>
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
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem;"
                        class="card-formation-hover">
                        <h4
                            style="font-size: 0.75rem; color: <?php echo $tree['root']['lieu_color'] ?? 'var(--primary-cyan)'; ?>; text-transform: uppercase; margin-bottom: 1rem; font-weight: 800; display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="network" style="width: 14px; height: 14px;"></i>
                            <?php echo htmlspecialchars($tree['root']['domaine']); ?>
                        </h4>
                        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: var(--text-primary);">
                            <?php echo htmlspecialchars($tree['root']['titre']); ?>
                        </h3>

                        <div
                            style="background: var(--bg-secondary); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                            <ul
                                style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                                <?php $nodes = array_merge([$tree['root']], $tree['children'] ?? []);
                                foreach ($nodes as $node):
                                    $isDone = ($node['ma_progression'] >= 100);
                                    ?>
                                    <li
                                        style="font-size: 0.85rem; color: <?php echo $isDone ? '#10b981' : ($node['is_unlocked'] ? '#0ea5e9' : '#475569'); ?>; display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="<?php echo $isDone ? 'check-circle-2' : ($node['is_unlocked'] ? 'circle' : 'lock'); ?>"
                                            style="width: 14px; height: 14px;"></i>
                                        <?php echo htmlspecialchars($node['titre']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <a href="skill_tree.php?id=<?php echo end($nodes)['id_formation']; ?>" class="btn btn-primary"
                            style="width: 100%; text-align: center; display: block; font-size: 0.85rem;">
                            Explorer ce parcours →
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- VUE 2 : NEURAL MAP -->
    <div id="view-map-container">
        <div id="map-loader" class="map-loader">
            <i data-lucide="loader-2" class="spin" style="width: 30px; height: 30px; margin-bottom: 0.5rem;"></i>
            <p>Initialisation du réseau neuronal...</p>
        </div>
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
    let graph;
    let graphDataLoaded = false;
    let selectedNode = null;
    let hoveredNode = null;

    function switchView(view) {
        document.getElementById('view-timeline').style.display = view === 'timeline' ? 'block' : 'none';
        document.getElementById('view-map-container').style.display = view === 'map' ? 'block' : 'none';
        document.getElementById('btn-timeline').classList.toggle('active', view === 'timeline');
        document.getElementById('btn-map').classList.toggle('active', view === 'map');
        if (view === 'map' && !graphDataLoaded) initGraph();
    }

    async function initGraph() {
        graphDataLoaded = true;
        const loader = document.getElementById('map-loader');

        try {
            const response = await fetch(`ajax_handler.php?action=get_skill_tree_data&user_id=<?php echo $id_user; ?>&id=<?php echo $treeId; ?>`);
            const result = await response.json();
            const formationsRaw = result.formationsData;

            const gData = {
                nodes: formationsRaw.map(f => ({
                    id: String(f.id_formation),
                    name: f.titre,
                    val: 12,
                    isDone: f.ma_progression >= 100,
                    isUnlocked: f.is_unlocked,
                    domaine: f.domaine,
                    description: f.description,
                    color: f.niveau_color
                })),
                links: formationsRaw.filter(f => f.prerequis_id).map(f => ({
                    source: String(f.prerequis_id),
                    target: String(f.id_formation)
                }))
            };

            if (typeof ForceGraph === 'undefined') {
                loader.innerHTML = '<p style="color:#ef4444;">Erreur : La bibliothèque de visualisation n\'a pas pu être chargée. Vérifiez votre connexion.</p>';
                return;
            }

            graph = ForceGraph()(document.getElementById('neural-graph'))
                .graphData(gData)
                .nodeRelSize(7)
                .nodeCanvasObject((node, ctx, globalScale) => {
                    const isSelected = selectedNode && selectedNode.id === node.id;
                    const isHovered = hoveredNode && hoveredNode.id === node.id;

                    if (isSelected || isHovered) {
                        ctx.beginPath();
                        ctx.arc(node.x, node.y, 10, 0, 2 * Math.PI, false);
                        ctx.fillStyle = node.isDone ? 'rgba(16, 185, 129, 0.2)' : 'rgba(14, 165, 233, 0.2)';
                        ctx.fill();
                    }

                    ctx.beginPath();
                    ctx.arc(node.x, node.y, 6, 0, 2 * Math.PI, false);
                    ctx.fillStyle = node.isDone ? '#10b981' : (node.isUnlocked ? (node.color || '#0ea5e9') : '#475569');
                    ctx.shadowBlur = (isSelected || isHovered) ? 15 : 5;
                    ctx.shadowColor = ctx.fillStyle;
                    ctx.fill();
                    ctx.shadowBlur = 0;
                })
                .onNodeHover(node => {
                    hoveredNode = node;
                    document.getElementById('neural-graph').style.cursor = node ? 'pointer' : null;
                })
                .linkColor(() => '#38bdf8')
                .linkWidth(1.5)
                .backgroundColor('#0f172a')
                .onNodeClick(node => {
                    selectedNode = node;
                    const hud = document.getElementById('map-hud');
                    document.getElementById('hud-title').textContent = node.name;
                    document.getElementById('hud-desc').textContent = (node.description || '').replace(/<[^>]*>?/gm, '').substring(0, 100) + '...';
                    document.getElementById('hud-action').innerHTML = node.isUnlocked ?
                        `<a href="formation_detail.php?id=${node.id}" class="btn btn-primary" style="width:100%; display:block; text-align:center;">🚀 ${node.isDone ? 'Revoir' : 'Continuer'}</a>` :
                        `<div style="text-align:center; color:#94a3b8; font-size:0.8rem;">🔒 Verrouillé</div>`;
                    hud.style.display = 'block';
                    graph.centerAt(node.x, node.y, 1000);
                    graph.zoom(3, 1000);
                });

            loader.style.display = 'none';
            setTimeout(() => graph.zoomToFit(400), 500);

        } catch (e) {
            console.error(e);
            loader.innerHTML = '<p style="color:#ef4444;">Erreur de chargement.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>