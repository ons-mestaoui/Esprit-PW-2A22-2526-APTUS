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

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- =====================================================
     STYLES SKILL TREE (Timeline + états débloqué/bloqué)
     ===================================================== -->
<style>
/* ── Container global ── */
.skill-tree-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem 0 4rem;
}
.skill-tree-page h1 {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--primary-cyan, #06b6d4), var(--accent-primary, #6366f1));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.skill-tree-page .subtitle {
    color: var(--text-secondary, #64748b);
    margin-bottom: 3rem;
    font-size: 0.95rem;
}

/* ── Section par domaine ── */
.skill-tree-section {
    margin-bottom: 4rem;
}
.skill-tree-section__title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-light, #94a3b8);
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
    background: var(--border-color, #e2e8f0);
}

/* ── Timeline ── */
.timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0;
    padding-left: 2rem;
}

/* Ligne verticale centrale */
.timeline::before {
    content: '';
    position: absolute;
    left: 0.7rem;
    top: 1.5rem;
    bottom: 1.5rem;
    width: 2px;
    background: linear-gradient(
        to bottom,
        var(--primary-cyan, #06b6d4),
        var(--accent-primary, #6366f1) 70%,
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
    animation: fadeSlideIn 0.4s ease both;
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
    width: 1.4rem;
    height: 1.4rem;
    border-radius: 50%;
    border: 2px solid white;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    font-weight: 900;
    z-index: 2;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.timeline-dot--done {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 0 0 4px rgba(16,185,129,0.2);
}
.timeline-dot--unlocked {
    background: linear-gradient(135deg, var(--primary-cyan, #06b6d4), var(--accent-primary, #6366f1));
    box-shadow: 0 0 0 4px rgba(99,102,241,0.15);
}
.timeline-dot--locked {
    background: var(--border-color, #cbd5e1);
    box-shadow: 0 0 0 4px rgba(203,213,225,0.3);
}
.timeline-node:hover .timeline-dot {
    transform: scale(1.2);
}

/* Badge d'étape */
.step-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.4rem;
}
.step-badge--done     { background: rgba(16,185,129,0.12); color: #059669; }
.step-badge--unlocked { background: rgba(99,102,241,0.12); color: #6366f1; }
.step-badge--locked   { background: rgba(100,116,139,0.1); color: #94a3b8; }

/* Carte de formation dans la timeline */
.timeline-card {
    flex: 1;
    background: white;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--border-color, #e2e8f0);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
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
.timeline-card--unlocked::before { background: linear-gradient(to bottom, var(--primary-cyan, #06b6d4), var(--accent-primary, #6366f1)); }
.timeline-card--locked::before   { background: #cbd5e1; }

.timeline-card--locked {
    opacity: 0.65;
    filter: grayscale(0.4);
}
.timeline-card:not(.timeline-card--locked):hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

/* Interne de la carte */
.timeline-card__meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.6rem;
}
.timeline-card__title { font-size: 1.05rem; font-weight: 700; margin-bottom: 0.3rem; }
.timeline-card__desc  { font-size: 0.83rem; color: var(--text-secondary,#64748b); margin-bottom: 1rem; }
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
    background: var(--border-color, #e2e8f0);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 0.35rem;
}
.timeline-card__progress-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--primary-cyan, #06b6d4), var(--accent-primary, #6366f1));
    transition: width 1s ease;
}

/* Lock overlay */
.lock-overlay {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: #94a3b8;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    background: rgba(148,163,184,0.1);
    border-radius: 8px;
}

/* ── Mode "all trees" ── */
.trees-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}
.tree-card {
    background: white;
    border-radius: 14px;
    border: 1px solid var(--border-color, #e2e8f0);
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}
.tree-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.1);
}
.tree-card__icon { font-size: 2rem; margin-bottom: 0.75rem; }
.tree-card__title { font-size: 1rem; font-weight: 700; margin-bottom: 0.4rem; }
.tree-card__meta { font-size: 0.8rem; color: var(--text-secondary,#64748b); }
.tree-card__steps {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color,#e2e8f0);
    font-size: 0.78rem;
    color: var(--text-secondary,#64748b);
}

/* ── CTA ── */
.cta-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-secondary,#64748b);
    text-decoration: none;
    margin-bottom: 2rem;
    transition: color 0.2s;
}
.cta-back:hover { color: var(--accent-primary,#6366f1); }
</style>

<div class="skill-tree-page">

    <!-- En-tête -->
    <a href="formations_catalog.php" class="cta-back">
        ← Retour au catalogue
    </a>
    <h1>🗺️ Skill Tree — Parcours de Compétences</h1>
    <p class="subtitle">
        Visualisez l'ordre logique des formations et débloquez chaque étape en progressant.
    </p>

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
                        <span class="badge" style="font-size:0.7rem; background:rgba(0,0,0,0.05);">
                            <?php echo htmlspecialchars($step['domaine'] ?? 'Général'); ?>
                        </span>
                        <span class="badge" style="font-size:0.7rem; background:rgba(0,0,0,0.05);">
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
                                 style="width: <?php echo (int)$step['ma_progression']; ?>%;">
                            </div>
                        </div>
                        <div class="timeline-card__footer">
                            <span style="font-size:0.8rem; color:var(--text-secondary);">
                                Progression : <b><?php echo (int)$step['ma_progression']; ?>%</b>
                            </span>
                            <?php if ($isDone): ?>
                                <a href="certificate.php?f_id=<?php echo $step['id_formation']; ?>"
                                   class="btn btn-primary btn-sm"
                                   style="font-size:0.8rem; padding:0.4rem 0.9rem;">
                                    🎓 Certificat
                                </a>
                            <?php else: ?>
                                <a href="formation_detail.php?id=<?php echo $step['id_formation']; ?>"
                                   class="btn btn-primary btn-sm"
                                   style="font-size:0.8rem; padding:0.4rem 0.9rem;">
                                    Commencer
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Formation bloquée -->
                        <div class="lock-overlay">
                            🔒 Nécessite de compléter l'étape <?php echo $stepNum - 1; ?> d'abord
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
                             style="width:<?php echo (int)$root['ma_progression']; ?>%;"></div>
                    </div>
                    <div class="timeline-card__footer">
                        <span style="font-size:0.8rem;"><?php echo (int)$root['ma_progression']; ?>%</span>
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
                                 style="width:<?php echo (int)$child['ma_progression']; ?>%;"></div>
                        </div>
                        <div class="timeline-card__footer">
                            <span style="font-size:0.8rem;"><?php echo (int)$child['ma_progression']; ?>%</span>
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
    <div style="text-align:center; padding:5rem; opacity:0.45;">
        <span style="font-size:4rem;">🌱</span>
        <p style="margin-top:1rem;">Aucun parcours configuré pour le moment.</p>
        <p style="font-size:0.85rem;">Exécutez le fichier <code>migration_skill_tree.sql</code> et ajoutez des liaisons de prérequis.</p>
        <a href="formations_catalog.php" class="btn btn-primary" style="margin-top:1.5rem;">
            Explorer le catalogue
        </a>
    </div>
    <?php endif; ?>

</div>

<script>
    // Animation des barres de progression au chargement
    document.addEventListener('DOMContentLoaded', () => {
        // On met les barres à 0 et on anime vers la valeur réelle
        document.querySelectorAll('.timeline-card__progress-fill').forEach(bar => {
            const target = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = target;
            }, 300);
        });
    });
</script>
