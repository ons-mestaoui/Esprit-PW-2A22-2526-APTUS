<?php
$pageTitle = "Audit Stratégique IA";
$pageCSS = "cv_premium.css"; // Reuse existing premium styles

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/RapportIAController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$cvId = $_GET['id'] ?? null;

if (!$cvId) {
    header('Location: cv_my.php');
    exit;
}

$cvc = new CVC();
$riac = new RapportIAController();
$cv = $cvc->getCVById($cvId);

$analysis = null;

// Priorité au nouveau modèle MVC (table rapport_ia)
$rapportData = $riac->getRapportByCvId($cvId);
if ($rapportData) {
    // On reconstruit l'objet analysis attendu par la vue à partir des colonnes
    $analysis = [
        'score_ats' => $rapportData['scoreGlobal'],
        'points_forts' => json_decode($rapportData['pointsForts'], true),
        'points_faibles' => json_decode($rapportData['pointsFaibles'], true),
        'missing_skills' => json_decode($rapportData['sectionsManquantes'], true),
        'detailed_recommendations' => json_decode($rapportData['suggestions'], true),
    ];
    
    // Si on a l'original complet dans cv table, on merge pour récupérer les scores détaillés
    if ($cv && !empty($cv['ai_analysis'])) {
        $oldAnalysis = json_decode($cv['ai_analysis'], true);
        $analysis = array_merge($oldAnalysis, $analysis);
    }
} 
// Fallback sur l'ancienne méthode
elseif ($cv && !empty($cv['ai_analysis'])) {
    $analysis = json_decode($cv['ai_analysis'], true);
}

if (!$analysis) {
    header('Location: cv_my.php');
    exit;
}

// Matching Logic (via le nouveau contrôleur MVC)
$jobMatches = $riac->matchJobs($analysis['keywords'] ?? []);
$trainingMatches = $riac->matchTrainingsByDomain($analysis['suggested_training_domains'] ?? []);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    .audit-dashboard {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        animation: fadeIn 0.8s ease-out;
    }

    /* Hero Score Section - Redesigned */
    .hero-score-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 40px;
        padding: 4rem;
        box-shadow: 0 25px 80px rgba(0,0,0,0.08);
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.4);
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 3rem;
        align-items: center;
        color: var(--text-primary);
    }

    [data-theme='dark'] .hero-score-card {
        background: rgba(17, 24, 39, 0.6);
        border-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 25px 80px rgba(0,0,0,0.3);
    }

    @media (max-width: 992px) {
        .hero-score-card { grid-template-columns: 1fr; text-align: center; padding: 2.5rem; }
    }

    .score-visual {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .circular-progress {
        width: 220px;
        height: 220px;
        position: relative;
    }

    .circular-progress svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .circular-progress circle {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
    }

    .circle-bg { stroke: #f1f5f9; transition: stroke 0.3s; }
    [data-theme='dark'] .circle-bg { stroke: #1f2937; }
    
    .circle-val {
        stroke: url(#score-gradient);
        stroke-dasharray: 628;
        stroke-dashoffset: 628;
        transition: stroke-dashoffset 2s ease-out;
    }

    .score-number-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .score-value-big {
        font-size: 5rem;
        font-weight: 900;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
    }

    /* Sub-scores Grid */
    .sub-scores-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .sub-score-item {
        background: var(--bg-card);
        padding: 1.2rem;
        border-radius: 20px;
        box-shadow: var(--shadow-xs);
        display: flex;
        flex-direction: column;
        gap: 8px;
        border: 1px solid var(--border-color);
    }

    .sub-score-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
    }

    .sub-score-bar-wrap {
        height: 6px;
        background: var(--border-color-light);
        border-radius: 3px;
        overflow: hidden;
    }

    .sub-score-bar-fill {
        height: 100%;
        background: var(--gradient-primary);
        border-radius: 3px;
        width: 0%;
        transition: width 1.5s ease-out;
    }

    /* Market Pulse Card - Gradient Theme (Matching CV Hero) */
    .market-pulse-card {
        background: var(--gradient-primary);
        border-radius: 30px;
        padding: 2.2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(107, 52, 163, 0.3);
        color: #fff;
    }

    .market-pulse-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.5;
    }

    .pulse-dot {
        width: 12px; height: 12px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        box-shadow: 0 0 10px #10b981;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Detailed Recommendations */
    .section-title {
        font-size: 1.8rem;
        font-weight: 850;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .recommendations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 4rem;
    }

    .rec-card {
        background: var(--bg-card);
        border-radius: 24px;
        padding: 2rem;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .rec-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        border-color: rgba(139, 92, 246, 0.2);
    }

    .rec-finding {
        background: #fff5f5;
        color: #e11d48;
        padding: 1.2rem;
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        gap: 12px;
        border-left: 4px solid #e11d48;
    }

    .rec-correction {
        background: #f0fdf4;
        color: #16a34a;
        padding: 1.2rem;
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 500;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-left: 4px solid #16a34a;
    }

    [data-theme='dark'] .rec-finding { background: rgba(225, 29, 72, 0.1); color: #fb7185; }
    [data-theme='dark'] .rec-correction { background: rgba(22, 163, 74, 0.1); color: #4ade80; }

    .rec-type-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 800;
        margin-bottom: 5px;
        display: inline-block;
    }

    .type-logic { background: #fee2e2; color: #991b1b; }
    .type-linguistic { background: #e0f2fe; color: #075985; }
    .type-skill_gap { background: #fef3c7; color: #92400e; }
    .type-structure { background: #f3e8ff; color: #6b21a8; }

    .impact-badge {
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    } 
    .impact-high { color: #dc2626; }
    .impact-medium { color: #d97706; }
    .impact-low { color: #059669; }

    /* Market Benchmarking Section */
    .market-card {
        background: var(--bg-secondary);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 4rem;
    }

    .skill-tag-missing {
        background: rgba(225, 29, 72, 0.1);
        color: #e11d48;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid rgba(225, 29, 72, 0.2);
    }

    /* Matching Sections */
    .matching-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 4rem;
    }

    .match-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        position: relative;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .match-card:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    .match-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
    }

    .match-high { background: #dcfce7; color: #166534; }
    .match-medium { background: #fef3c7; color: #92400e; }
    .match-low { background: #fee2e2; color: #991b1b; }

    .match-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .match-subtitle {
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 1rem;
    }

    .match-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    .btn-apply-small {
        background: var(--bg-secondary);
        color: var(--accent-primary);
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-apply-small:hover {
        background: var(--accent-primary);
        color: #fff;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Premium Loading Modal Styles */
    .aptus-modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .aptus-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .aptus-modal-content {
        background: #fff;
        border-radius: 30px;
        padding: 2.5rem;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        text-align: center;
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .aptus-modal-overlay.active .aptus-modal-content {
        transform: translateY(0);
    }

    [data-theme='dark'] .aptus-modal-content {
        background: #1e293b;
    }

    .spin {
        width: 60px; height: 60px;
        border: 4px solid var(--accent-primary);
        border-top-color: transparent;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .tailor-progress-bar {
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 10px;
        overflow: hidden;
        margin: 1.5rem 0;
    }

    .tailor-progress-fill {
        height: 100%;
        width: 0%;
        background: var(--gradient-primary);
        transition: width 0.5s ease;
    }

    .tailor-steps-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        text-align: left;
    }

    .tailor-step {
        display: flex;
        align-items: center;
        gap: 15px;
        opacity: 0.4;
        transition: all 0.4s ease;
        color: var(--text-secondary);
    }

    .tailor-step.active {
        opacity: 1;
        color: var(--accent-primary);
        font-weight: 700;
    }

    .tailor-step.completed {
        opacity: 1;
        color: #10b981;
    }

    .tailor-step-icon {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: var(--bg-secondary);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 800;
        flex-shrink: 0;
    }

    .tailor-step.active .tailor-step-icon {
        background: var(--accent-primary);
        color: #fff;
        box-shadow: 0 0 15px rgba(107, 52, 163, 0.3);
    }

    .tailor-step.completed .tailor-step-icon {
        background: #10b981;
        color: #fff;
    }
</style>

<div id="ai-loading-overlay" class="aptus-modal-overlay">
    <div class="aptus-modal-content">
        <div class="spin"></div>
        <h2 style="font-size: 1.6rem; font-weight: 850; color: var(--text-primary); margin-bottom: 8px;">Analyse IA Stratégique</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Veuillez patienter, nos agents IA travaillent sur votre dossier...</p>
        
        <div class="tailor-progress-bar">
            <div class="tailor-progress-fill" id="load-progress"></div>
        </div>

        <div class="tailor-steps-container">
            <div class="tailor-step active" id="rescan-step-1">
                <div class="tailor-step-icon">1</div>
                <div style="font-size: 0.95rem;">Initialisation de l'audit...</div>
            </div>
            <div class="tailor-step" id="rescan-step-2">
                <div class="tailor-step-icon">2</div>
                <div style="font-size: 0.95rem;">Analyse des sections et compétences...</div>
            </div>
            <div class="tailor-step" id="rescan-step-3">
                <div class="tailor-step-icon">3</div>
                <div style="font-size: 0.95rem;">Génération des corrections stratégiques...</div>
            </div>
        </div>
    </div>
</div>

<div class="audit-dashboard">
    <!-- HERO SCORE REDESIGNED -->
    <div class="hero-score-card">
        <div class="score-visual">
            <div class="circular-progress">
                <svg>
                    <defs>
                        <linearGradient id="score-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#00A3DA" />
                            <stop offset="50%" stop-color="#6B34A3" />
                            <stop offset="100%" stop-color="#8D2587" />
                        </linearGradient>
                    </defs>
                    <circle class="circle-bg" cx="110" cy="110" r="100"></circle>
                    <circle class="circle-val" id="score-circle" cx="110" cy="110" r="100"></circle>
                </svg>
                <div class="score-number-center">
                    <div class="score-value-big" id="score-val">0</div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-tertiary);">SCORE GLOBAL</div>
                </div>
            </div>
            
            <div class="sub-scores-grid">
                <?php 
                $sub = $analysis['sub_scores'] ?? ['structure'=>60,'content_quality'=>60,'keyword_relevance'=>60,'impact_metrics'=>60];
                $labels = ['structure'=>'Structure','content_quality'=>'Contenu','keyword_relevance'=>'Mots-clés','impact_metrics'=>'Impact'];
                foreach ($labels as $k => $label): ?>
                <div class="sub-score-item" data-value="<?php echo $sub[$k]; ?>">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="sub-score-label"><?php echo $label; ?></span>
                        <span class="sub-percent" style="font-weight: 800; font-size: 0.9rem; color: var(--accent-primary);">0%</span>
                    </div>
                    <div class="sub-score-bar-wrap">
                        <div class="sub-score-bar-fill" style="width: 0%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="score-info">
            <div class="market-pulse-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div>
                        <span class="pulse-dot"></span>
                        <span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">PULSION DU MARCHÉ : <?php echo $cv['titrePoste']; ?></span>
                    </div>
                    <i data-lucide="trending-up" style="color: #fff; opacity: 0.8;"></i>
                </div>
                
                <div style="display: flex; gap: 2rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 1.8rem; font-weight: 800;"><?php echo $analysis['market_positioning']['percentile'] ?? 75; ?>%</div>
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Percentile Top</div>
                    </div>
                    <div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #facc15;"><?php echo $analysis['market_positioning']['demand_level'] ?? 'Élevée'; ?></div>
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Demande</div>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.1); padding: 1.2rem; border-radius: 18px; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(5px);">
                    <div style="font-size: 0.75rem; opacity: 0.7; margin-bottom: 4px;">Estimation Salariale</div>
                    <div style="font-size: 1.15rem; font-weight: 700; color: #4ade80;"><?php echo $analysis['market_positioning']['salary_estimate'] ?? '---'; ?></div>
                </div>
            </div>

            <p class="score-explanation" style="margin-top: 2rem; text-align: left; max-width: 100%;">
                <?php echo htmlspecialchars($analysis['score_explanation'] ?? "Analyse globale incisive sur la compétitivité du profil."); ?>
            </p>
            
            <div style="display: flex; gap: 15px; margin-top: 1.5rem;">
                <div style="background: var(--stat-teal-bg); color: var(--stat-teal); padding: 10px 20px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" style="width:16px;"></i> Prêt pour le Marché
                </div>
                <button id="rescan-btn" style="background: var(--bg-secondary); color: var(--accent-primary); border: 1px solid var(--accent-primary); padding: 10px 20px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s;">
                    <i data-lucide="refresh-cw" style="width:16px;"></i> Mettre à jour l'audit
                </button>
            </div>
        </div>
    </div>

    <!-- PATH TO 100% -->
    <div style="background: var(--gradient-primary); border-radius: 30px; padding: 2px; margin-bottom: 4rem;">
        <div style="background: var(--bg-card); border-radius: 28px; padding: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 850; margin: 0; display: flex; align-items: center; gap: 12px; color: var(--text-primary);">
                    <i data-lucide="map" style="color: var(--accent-primary);"></i> Votre Chemin vers 100%
                </h2>
                <span style="background: var(--accent-primary-light); color: var(--accent-primary); padding: 5px 15px; border-radius: 10px; font-weight: 800; font-size: 0.8rem;">
                    3 ACTIONS CRITIQUES
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php 
                $topActions = array_slice($analysis['detailed_recommendations'] ?? [], 0, 3);
                foreach ($topActions as $i => $act): 
                    if (!is_array($act)) continue; // Robustness check
                ?>
                <div style="border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 20px; position: relative; background: var(--bg-secondary);">
                    <div style="position: absolute; top: -10px; left: 20px; background: var(--bg-card); padding: 0 10px; font-weight: 900; color: var(--accent-primary);">0<?php echo $i+1; ?></div>
                    <p style="font-size: 0.9rem; font-weight: 600; margin-bottom: 10px; color: var(--text-primary);"><?php echo htmlspecialchars($act['finding'] ?? ''); ?></p>
                    <div style="font-size: 0.8rem; color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                        <i data-lucide="trending-up" style="width:14px;"></i> Score +<?php echo (($act['impact'] ?? '') === 'high' ? '15' : (($act['impact'] ?? '') === 'medium' ? '8' : '3')); ?> pts
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- RECOMMENDATIONS -->
    <h2 class="section-title">
        <i data-lucide="shield-check" style="color:var(--accent-primary);"></i> Audit Stratégique & Corrections
    </h2>
    <div class="recommendations-grid">
        <?php 
        $recs = $analysis['detailed_recommendations'] ?? [];
        foreach ($recs as $rec): 
            if (!is_array($rec)) continue;
            $typeClass = "type-" . ($rec['type'] ?? 'logic');
            $impactClass = "impact-" . ($rec['impact'] ?? 'medium');
            $typeLabel = str_replace('_', ' ', $rec['type'] ?? 'logic');
        ?>
        <div class="rec-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <span class="rec-type-badge <?php echo $typeClass; ?>"><?php echo $typeLabel; ?></span>
                <div class="impact-badge <?php echo $impactClass; ?>">
                    <i data-lucide="alert-circle" style="width:14px;"></i> Impact <?php echo ucfirst($rec['impact'] ?? 'moyen'); ?>
                </div>
            </div>
            
            <div class="rec-finding">
                <i data-lucide="x-circle" style="width:18px; flex-shrink:0;"></i>
                <div>
                    <strong style="display:block; margin-bottom:4px;">Observation :</strong>
                    <?php echo htmlspecialchars($rec['finding'] ?? ''); ?>
                </div>
            </div>

            <div class="rec-correction">
                <i data-lucide="check-circle" style="width:18px; flex-shrink:0;"></i>
                <div>
                    <strong style="display:block; margin-bottom:4px;">Correction suggérée :</strong>
                    <?php echo htmlspecialchars($rec['correction'] ?? ''); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- MARKET BENCHMARKING -->
    <div class="market-card">
        <div style="display: flex; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <h2 class="section-title" style="margin-bottom: 1rem;">
                    <i data-lucide="globe" style="color:var(--accent-secondary);"></i> Benchmarking Marché
                </h2>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Basé sur les standards mondiaux du recrutement pour un poste de <strong><?php echo htmlspecialchars($cv['titrePoste']); ?></strong>, voici les expertises qu'il vous manque pour passer au niveau supérieur.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <?php foreach (($analysis['missing_skills'] ?? []) as $skill): ?>
                        <div style="display: flex; align-items: center; gap: 8px; background: var(--bg-card); padding: 10px 15px; border-radius: 15px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                            <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);"><?php echo htmlspecialchars($skill); ?></span>
                            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 5px;">+<?php echo rand(4,8); ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="background: var(--bg-card); padding: 2.5rem; border-radius: 20px; flex: 0.8; min-width: 300px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; color: var(--text-primary);">
                    <i data-lucide="lightbulb" style="color: #f59e0b;"></i> Conseil d'Expert
                </h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    "Le marché actuel ne valorise plus seulement la maîtrise des outils, mais l'impact business. Pour un profil comme le vôtre, concentrez-vous sur la démonstration de résultats quantifiables dans vos expériences futures."
                </p>
            </div>
        </div>
    </div>

    <!-- JOB MATCHING -->
    <h2 class="section-title">
        <i data-lucide="briefcase" style="color:var(--accent-primary);"></i> Opportunités de Carrière
    </h2>
    <div class="matching-grid">
        <?php foreach ($jobMatches as $job): 
            $lvl = $job['match_score'] >= 80 ? 'match-high' : ($job['match_score'] >= 50 ? 'match-medium' : 'match-low');
        ?>
        <div class="match-card">
            <span class="match-badge <?php echo $lvl; ?>"><?php echo $job['match_score']; ?>% Match</span>
            <h3 class="match-title"><?php echo htmlspecialchars($job['title']); ?></h3>
            <p class="match-subtitle"><?php echo htmlspecialchars($job['domain']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
            <div class="match-footer">
                <span style="font-size: 0.75rem; color:#64748b;">Postuler sur Aptus</span>
                <button class="btn-apply-small" onclick="window.location.href='hr_posts.php'">Voir l'offre</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TRAINING MATCHING -->
    <h2 class="section-title">
        <i data-lucide="graduation-cap" style="color:#10b981;"></i> Combler vos lacunes
    </h2>
    <div class="matching-grid">
        <?php foreach ($trainingMatches as $tr): 
            $lvlTr = ($tr['match_score'] ?? 80) >= 80 ? 'match-high' : (($tr['match_score'] ?? 80) >= 50 ? 'match-medium' : 'match-low');
        ?>
        <div class="match-card">
            <span class="match-badge <?php echo $lvlTr; ?>"><?php echo $tr['match_score'] ?? 80; ?>% Recommandé</span>
            <h3 class="match-title"><?php echo htmlspecialchars($tr['title']); ?></h3>
            <p class="match-subtitle"><?php echo htmlspecialchars($tr['domain']); ?> • Niveau <?php echo htmlspecialchars($tr['level']); ?></p>
            <div class="match-footer">
                <span style="font-size: 0.75rem; color:#64748b;">Formation certifiante</span>
                <button class="btn-apply-small" style="color:#10b981;" onclick="window.location.href='formations_catalog.php'">Découvrir</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Animate Score
        const target = <?php echo (int)($analysis['score_ats'] ?? 0); ?>;
        const el = document.getElementById('score-val');
        const circle = document.getElementById('score-circle');
        
        // Circular Progress
        const circumference = 2 * Math.PI * 100; // r=100
        const offset = circumference - (target / 100) * circumference;
        if (circle) circle.style.strokeDashoffset = offset;

        let current = 0;
        const interval = setInterval(() => {
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current;
            if (current === target) return;
            current++;
        }, 20);

        // Animate Sub-scores
        document.querySelectorAll('.sub-score-item').forEach(item => {
            const bar = item.querySelector('.sub-score-bar-fill');
            const percentEl = item.querySelector('.sub-percent');
            const val = parseInt(item.getAttribute('data-value'));
            
            // Animate bar width
            setTimeout(() => {
                bar.style.width = val + '%';
            }, 300);

            // Animate percentage text
            let currSub = 0;
            const subInterval = setInterval(() => {
                if (currSub >= val) {
                    currSub = val;
                    clearInterval(subInterval);
                }
                percentEl.textContent = currSub + '%';
                if (currSub === val) return;
                currSub++;
            }, 25);
        });

        if (window.lucide) lucide.createIcons();

        // Rescan Logic
        const rescanBtn = document.getElementById('rescan-btn');
        const overlay = document.getElementById('ai-loading-overlay');
        const progressFill = document.getElementById('load-progress');

        function updateStep(step) {
            document.querySelectorAll('.tailor-step').forEach((item, i) => {
                const idx = i + 1;
                if (idx < step) {
                    item.classList.add('completed');
                    item.classList.remove('active');
                    item.querySelector('.tailor-step-icon').innerHTML = '<i data-lucide="check" style="width:16px;"></i>';
                } else if (idx === step) {
                    item.classList.add('active');
                    item.classList.remove('completed');
                    item.querySelector('.tailor-step-icon').innerHTML = idx;
                } else {
                    item.classList.remove('active', 'completed');
                    item.querySelector('.tailor-step-icon').innerHTML = idx;
                }
            });
            if(window.lucide) lucide.createIcons();
            progressFill.style.width = ((step / 3) * 100) + '%';
        }

        if (rescanBtn) {
            rescanBtn.addEventListener('click', async () => {
                overlay.classList.add('active');
                updateStep(1);

                setTimeout(() => updateStep(2), 2000);
                setTimeout(() => updateStep(3), 5000);

                try {
                    const response = await fetch('ajax_ai_analyze_cv.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id_cv: <?php echo $cvId; ?>,
                            cvText: <?php echo json_encode($cv['resume'] . " " . $cv['experience'] . " " . $cv['competences']); ?>
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        updateStep(4); // Mark all as done
                        progressFill.style.width = '100%';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        overlay.classList.remove('active');
                        alert("Erreur : " + result.error);
                    }
                } catch (e) {
                    overlay.classList.remove('active');
                    alert("Une erreur est survenue lors de l'analyse.");
                }
            });
        }
    });
</script>
