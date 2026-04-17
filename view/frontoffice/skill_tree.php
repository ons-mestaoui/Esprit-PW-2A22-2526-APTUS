<?php
/**
 * ============================================================
 * skill_tree.php — Concept 2 : Vue "Cartographe de Skill Tree"
 * ============================================================
 * Affiche les parcours de compétences sous forme de Timeline
 * interactive : formations débloquées vs bloquées.
 *
 * URL d'accès : skill_tree.php  ou  skill_tree.php?id=X
 *   - Sans paramètre  → affiche tous les parcours disponibles
 *   - Avec ?id=X      → affiche la chaîne complète pour la formation X
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Skill Tree — Parcours de Compétences - Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';

    $formationC = new FormationController();
    $id_user    = $_SESSION['user_id'] ?? 10; // Demo user

    // Mode 1 : affichage d'un parcours spécifique (chaîne récursive)
    if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
        $skillChain   = $formationC->getSkillTree((int)$_GET['id'], $id_user);
        $viewMode     = 'chain';
        $pageTitle    = "Parcours vers : " . ($skillChain ? end($skillChain)['titre'] : '?') . " — Aptus AI";
    } else {
        // Mode 2 : affichage de tous les parcours du catalogue
        $allTrees = $formationC->getAllFormationsWithSkillTree($id_user);
        $viewMode = 'all';
    }

    // Compute global stats
    $globalDone = 0;
    $globalTotal = 0;
    if ($viewMode === 'chain' && !empty($skillChain)) {
        $globalTotal = count($skillChain);
        foreach ($skillChain as $s) {
            if ($s['ma_progression'] >= 100) $globalDone++;
        }
    } elseif ($viewMode === 'all' && !empty($allTrees)) {
        foreach ($allTrees as $tree) {
            $globalTotal++;
            if ($tree['root']['ma_progression'] >= 100) $globalDone++;
            foreach ($tree['children'] as $child) {
                $globalTotal++;
                if ($child['ma_progression'] >= 100) $globalDone++;
            }
        }
    }
    $globalPercent = ($globalTotal > 0) ? round(($globalDone / $globalTotal) * 100) : 0;

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- =====================================================
     STYLES SKILL TREE (Modern + Dark Mode Compatible)
     ===================================================== -->
<style>
/* ── Container global ── */
.skill-tree-page {
    max-width: 920px;
    margin: 0 auto;
    padding: 1rem 0 4rem;
}

/* ── Hero Header ── */
.skill-tree-hero {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 2.5rem;
    position: relative;
    overflow: hidden;
}
.skill-tree-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--gradient-primary);
}
.skill-tree-hero h1 {
    font-size: 1.75rem;
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}
.skill-tree-hero .subtitle {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

/* ── Global Progress ── */
.skill-global-progress {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.skill-global-progress__bar {
    flex: 1;
    height: 10px;
    background: var(--bg-tertiary);
    border-radius: 999px;
    overflow: hidden;
}
.skill-global-progress__fill {
    height: 100%;
    border-radius: 999px;
    background: var(--gradient-primary);
    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.skill-global-progress__fill::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}
@keyframes shimmer {
    from { transform: translateX(-100%); }
    to   { transform: translateX(100%); }
}
.skill-global-progress__stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}
.skill-global-progress__text {
    font-weight: 700;
    font-size: 1rem;
    color: var(--primary-cyan);
}
.skill-global-progress__label {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

/* ── Section par domaine ── */
.skill-tree-section {
    margin-bottom: 3rem;
}
.skill-tree-section__title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-tertiary);
    font-weight: 700;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.skill-tree-section__title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

/* ── Timeline ── */
.timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0;
    padding-left: 2rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0.7rem;
    top: 1.5rem;
    bottom: 1.5rem;
    width: 2px;
    background: linear-gradient(
        to bottom,
        var(--primary-cyan),
        var(--accent-primary) 70%,
        transparent
    );
    border-radius: 2px;
}

/* ── Nœud de la Timeline ── */
.timeline-node {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1rem 0;
    position: relative;
    animation: fadeSlideIn 0.5s ease both;
}
.timeline-node:nth-child(1) { animation-delay: 0.05s; }
.timeline-node:nth-child(2) { animation-delay: 0.15s; }
.timeline-node:nth-child(3) { animation-delay: 0.25s; }
.timeline-node:nth-child(4) { animation-delay: 0.35s; }
.timeline-node:nth-child(5) { animation-delay: 0.45s; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateX(-16px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* Pastille (dot) */
.timeline-dot {
    position: absolute;
    left: -2rem;
    top: 1.4rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    border: 3px solid var(--bg-body);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    font-weight: 900;
    z-index: 2;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #fff;
}
.timeline-dot--done {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 0 0 5px rgba(16,185,129,0.15);
}
.timeline-dot--unlocked {
    background: var(--gradient-primary);
    box-shadow: 0 0 0 5px rgba(0,163,218,0.15);
    animation: pulse-dot 2s ease-in-out infinite;
}
.timeline-dot--locked {
    background: var(--bg-tertiary);
    box-shadow: 0 0 0 5px rgba(203,213,225,0.1);
    color: var(--text-tertiary);
}
.timeline-node:hover .timeline-dot {
    transform: scale(1.25);
}

@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 5px rgba(0,163,218,0.15); }
    50%      { box-shadow: 0 0 0 10px rgba(0,163,218,0.08); }
}

/* Badge d'étape */
.step-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.4rem;
}
.step-badge--done     { background: rgba(16,185,129,0.12); color: #10b981; }
.step-badge--unlocked { background: rgba(0,163,218,0.12); color: var(--primary-cyan); }
.step-badge--locked   { background: var(--bg-secondary); color: var(--text-tertiary); }

/* Carte de formation dans la timeline */
.timeline-card {
    flex: 1;
    background: var(--bg-card);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
}
.timeline-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
}
.timeline-card--done::before     { background: linear-gradient(to bottom, #10b981, #059669); }
.timeline-card--unlocked::before { background: var(--gradient-primary); }
.timeline-card--locked::before   { background: var(--bg-tertiary); }

.timeline-card--locked {
    opacity: 0.55;
}
.timeline-card:not(.timeline-card--locked):hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    border-color: var(--primary-cyan);
}

/* Interne de la carte */
.timeline-card__meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.6rem;
    align-items: center;
}
.timeline-card__title { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.3rem; color: var(--text-primary); }
.timeline-card__desc  { font-size: 0.83rem; color: var(--text-secondary); margin-bottom: 1rem; }
.timeline-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.timeline-card__progress-bar {
    width: 100%;
    height: 6px;
    background: var(--bg-tertiary);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}
.timeline-card__progress-fill {
    height: 100%;
    border-radius: 999px;
    background: var(--gradient-primary);
    transition: width 1s ease;
}

/* Lock overlay */
.lock-overlay {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: var(--text-tertiary);
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    border: 1px dashed var(--border-color);
}

/* ── CTA ── */
.cta-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-decoration: none;
    margin-bottom: 2rem;
    transition: color 0.2s;
    font-weight: 500;
}
.cta-back:hover { color: var(--primary-cyan); }
</style>

<div class="skill-tree-page">

    <!-- En-tête -->
    <a href="formations_catalog.php" class="cta-back">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour au catalogue
    </a>

    <!-- Hero Header with Global Progression -->
    <div class="skill-tree-hero">
        <h1>🗺️ Skill Tree — Parcours de Compétences</h1>
        <p class="subtitle">
            Visualisez l'ordre logique des formations et débloquez chaque étape en progressant.
        </p>

        <?php if ($globalTotal > 0): ?>
        <div class="skill-global-progress">
            <div class="skill-global-progress__bar">
                <div class="skill-global-progress__fill" id="globalProgressFill" style="width: <?php echo $globalPercent; ?>%;"></div>
            </div>
            <div class="skill-global-progress__stats">
                <span class="skill-global-progress__text"><?php echo $globalPercent; ?>%</span>
                <span class="skill-global-progress__label"><?php echo $globalDone; ?>/<?php echo $globalTotal; ?> complétées</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($viewMode === 'chain' && !empty($skillChain)): ?>
    <!-- =====================================================
         MODE CHAÎNE : Parcours complet vers une formation
         ===================================================== -->
    <div class="skill-tree-section">
        <div class="skill-tree-section__title">
            Chemin vers : <?php echo htmlspecialchars(end($skillChain)['titre']); ?>
        </div>

        <div class="timeline">
            <?php foreach ($skillChain as $i => $step):
                $isDone     = ($step['ma_progression'] >= 100);
                $isUnlocked = $step['is_unlocked'];
                $isLocked   = !$isUnlocked;
                $stepNum    = $i + 1;
                $totalSteps = count($skillChain);

                // Classes CSS selon l'état
                $dotClass  = $isDone ? 'done' : ($isUnlocked ? 'unlocked' : 'locked');
                $cardClass = $isDone ? 'done' : ($isUnlocked ? 'unlocked' : 'locked');
                $badgeText = $isDone ? '✓ Complétée' : ($isUnlocked ? '⚡ Accessible' : '🔒 Bloquée');
                $badgeClass= $isDone ? 'done' : ($isUnlocked ? 'unlocked' : 'locked');
            ?>
            <div class="timeline-node">
                <!-- Pastille -->
                <div class="timeline-dot timeline-dot--<?php echo $dotClass; ?>">
                    <?php echo $isDone ? '✓' : $stepNum; ?>
                </div>

                <!-- Carte -->
                <div class="timeline-card timeline-card--<?php echo $cardClass; ?>">
                    <div class="timeline-card__meta">
                        <span class="step-badge step-badge--<?php echo $badgeClass; ?>">
                            Étape <?php echo $stepNum; ?> / <?php echo $totalSteps; ?> — <?php echo $badgeText; ?>
                        </span>
                        <span class="badge" style="font-size:0.65rem; background:var(--bg-secondary); color:var(--text-secondary);">
                            <?php echo htmlspecialchars($step['domaine'] ?? 'Général'); ?>
                        </span>
                        <span class="badge" style="font-size:0.65rem; background:var(--bg-secondary); color:var(--text-secondary);">
                            <?php echo htmlspecialchars($step['niveau'] ?? ''); ?>
                        </span>
                    </div>

                    <div class="timeline-card__title">
                        <?php echo htmlspecialchars($step['titre']); ?>
                    </div>

                    <div class="timeline-card__desc">
                        <?php echo htmlspecialchars(substr(strip_tags($step['description']), 0, 110)) . '...'; ?>
                    </div>

                    <?php if (!$isLocked): ?>
                        <!-- Barre de progression -->
                        <div class="timeline-card__progress-bar">
                            <div class="timeline-card__progress-fill"
                                 style="width: <?php echo (int)$step['ma_progression']; ?>%; <?php if($isDone) echo 'background:linear-gradient(90deg,#10b981,#059669);'; ?>">
                            </div>
                        </div>
                        <div class="timeline-card__footer">
                            <span style="font-size:0.8rem; color:var(--text-secondary);">
                                Progression : <b style="color: var(--primary-cyan);"><?php echo (int)$step['ma_progression']; ?>%</b>
                            </span>
                            <?php if ($isDone): ?>
                                <a href="certificate.php?f_id=<?php echo $step['id_formation']; ?>"
                                   class="btn btn-sm" style="background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.3); font-size:0.8rem; padding:0.4rem 0.9rem;">
                                    🎓 Certificat
                                </a>
                            <?php else: ?>
                                <a href="formation_detail.php?id=<?php echo $step['id_formation']; ?>"
                                   class="btn btn-primary btn-sm"
                                   style="font-size:0.8rem; padding:0.4rem 0.9rem;">
                                    Voir le parcours →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Formation bloquée -->
                        <div class="lock-overlay">
                            🔒 Complétez l'étape <?php echo $stepNum - 1; ?> pour débloquer
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php elseif ($viewMode === 'all' && !empty($allTrees)): ?>
    <!-- =====================================================
         MODE CATALOGUE : Tous les arbres de compétences
         ===================================================== -->
    <?php foreach ($allTrees as $tree): ?>
    <div class="skill-tree-section">
        <div class="skill-tree-section__title">
            <?php echo htmlspecialchars($tree['root']['domaine'] ?? 'Parcours'); ?>
        </div>

        <div class="timeline">
            <!-- Nœud racine -->
            <?php
            $root    = $tree['root'];
            $isDone  = ($root['ma_progression'] >= 100);
            $dotClass= $isDone ? 'done' : 'unlocked';
            $badgeText = $isDone ? '✓ Complétée' : '⚡ Accessible';
            $badgeClass= $isDone ? 'done' : 'unlocked';
            ?>
            <div class="timeline-node">
                <div class="timeline-dot timeline-dot--<?php echo $dotClass; ?>">
                    <?php echo $isDone ? '✓' : '1'; ?>
                </div>
                <div class="timeline-card timeline-card--<?php echo $dotClass; ?>">
                    <div class="timeline-card__meta">
                        <span class="step-badge step-badge--<?php echo $badgeClass; ?>">
                            Point de départ — <?php echo $badgeText; ?>
                        </span>
                    </div>
                    <div class="timeline-card__title"><?php echo htmlspecialchars($root['titre']); ?></div>
                    <div class="timeline-card__desc">
                        <?php echo htmlspecialchars(substr(strip_tags($root['description']), 0, 100)) . '...'; ?>
                    </div>
                    <div class="timeline-card__progress-bar">
                        <div class="timeline-card__progress-fill"
                             style="width:<?php echo (int)$root['ma_progression']; ?>%; <?php if($isDone) echo 'background:linear-gradient(90deg,#10b981,#059669);'; ?>"></div>
                    </div>
                    <div class="timeline-card__footer">
                        <span style="font-size:0.8rem; color:var(--text-secondary);"><?php echo (int)$root['ma_progression']; ?>%</span>
                        <a href="skill_tree.php?id=<?php echo $root['id_formation']; ?>"
                           class="btn btn-primary btn-sm" style="font-size:0.8rem; padding:0.4rem 0.9rem;">
                            Voir le parcours →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formations enfants (dépendantes) -->
            <?php foreach ($tree['children'] as $i => $child):
                $cDone    = ($child['ma_progression'] >= 100);
                $cUnlocked = $child['is_unlocked'];
                $cDot     = $cDone ? 'done' : ($cUnlocked ? 'unlocked' : 'locked');
                $cText    = $cDone ? '✓ Complétée' : ($cUnlocked ? '⚡ Accessible' : '🔒 Bloquée');
                $cBadge   = $cDone ? 'done' : ($cUnlocked ? 'unlocked' : 'locked');
            ?>
            <div class="timeline-node">
                <div class="timeline-dot timeline-dot--<?php echo $cDot; ?>">
                    <?php echo $cDone ? '✓' : ($i + 2); ?>
                </div>
                <div class="timeline-card timeline-card--<?php echo $cDot; ?>">
                    <div class="timeline-card__meta">
                        <span class="step-badge step-badge--<?php echo $cBadge; ?>">
                            Étape <?php echo $i + 2; ?> — <?php echo $cText; ?>
                        </span>
                    </div>
                    <div class="timeline-card__title"><?php echo htmlspecialchars($child['titre']); ?></div>
                    <div class="timeline-card__desc">
                        <?php echo htmlspecialchars(substr(strip_tags($child['description']), 0, 100)) . '...'; ?>
                    </div>
                    <?php if ($cUnlocked || $cDone): ?>
                        <div class="timeline-card__progress-bar">
                            <div class="timeline-card__progress-fill"
                                 style="width:<?php echo (int)$child['ma_progression']; ?>%; <?php if($cDone) echo 'background:linear-gradient(90deg,#10b981,#059669);'; ?>"></div>
                        </div>
                        <div class="timeline-card__footer">
                            <span style="font-size:0.8rem; color:var(--text-secondary);"><?php echo (int)$child['ma_progression']; ?>%</span>
                            <a href="skill_tree.php?id=<?php echo $child['id_formation']; ?>"
                               class="btn btn-primary btn-sm" style="font-size:0.8rem; padding:0.4rem 0.9rem;">
                                Voir →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="lock-overlay">🔒 Complétez l'étape précédente pour débloquer</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <!-- État vide -->
    <div class="empty-state">
        <div class="empty-state__icon" style="width: 100px; height: 100px;">
            <i data-lucide="trees" style="width: 44px; height: 44px;"></i>
        </div>
        <h3 class="empty-state__title">Aucun parcours configuré</h3>
        <p class="empty-state__text">Les parcours de compétences n'ont pas encore été configurés. Explorez le catalogue en attendant.</p>
        <a href="formations_catalog.php" class="btn btn-primary" style="margin-top:1rem;">
            Explorer le catalogue
        </a>
    </div>
    <?php endif; ?>

</div>

<script>
    // Animation des barres de progression au chargement
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.timeline-card__progress-fill, .skill-global-progress__fill').forEach(bar => {
            const target = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = target;
            }, 300);
        });
    });
</script>
