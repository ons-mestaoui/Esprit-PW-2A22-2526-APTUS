<?php
$pageTitle = "Guide de Réussite Recrutement";
$pageCSS = "cv_premium.css";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$cvId = $_GET['id'] ?? null;

if (!$cvId) {
    header('Location: cv_my.php');
    exit;
}

$cvc = new CVC();
$cv = $cvc->getCVById($cvId);

$guide = null;
if ($cv && !empty($cv['tailoring_report'])) {
    $guide = json_decode($cv['tailoring_report'], true);
} elseif (isset($_SESSION['tailor_guide'])) {
    $guide = $_SESSION['tailor_guide'];
    // Mock a CV structure for display if not in DB yet
    if (!$cv) {
        $cv = [
            'titrePoste' => $_SESSION['tailor_job_data']['title'] ?? 'Poste',
            'is_tailored' => 1
        ];
    }
}

if (!$guide) {
    header('Location: cv_my.php');
    exit;
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    .guide-dashboard {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        animation: fadeIn 0.8s ease-out;
    }

    .hero-guide-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 40px;
        padding: 3rem;
        box-shadow: 0 25px 80px rgba(0,0,0,0.08);
        margin-bottom: 3rem;
        border: 1px solid rgba(255, 255, 255, 0.4);
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    [data-theme='dark'] .hero-guide-card {
        background: rgba(17, 24, 39, 0.6);
        border-color: rgba(255, 255, 255, 0.05);
    }

    .section-title {
        font-size: 1.8rem;
        font-weight: 850;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .premium-card {
        background: var(--bg-card);
        border-radius: 24px;
        padding: 2rem;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
    }

    .justification-item {
        background: var(--bg-secondary);
        padding: 1.2rem;
        border-radius: 16px;
        margin-bottom: 1rem;
        border-left: 4px solid var(--accent-primary);
    }

    /* Quiz Styling */
    .quiz-container {
        background: var(--bg-secondary);
        border-radius: 30px;
        padding: 2.5rem;
        margin-bottom: 4rem;
        border: 2px solid var(--border-color);
    }

    .quiz-question {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .quiz-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .quiz-option {
        background: var(--bg-card);
        padding: 1.2rem;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .quiz-option:hover {
        border-color: var(--accent-primary);
        background: var(--accent-primary-light);
    }

    .quiz-option.correct {
        background: #dcfce7;
        border-color: #16a34a;
        color: #166534;
    }

    .quiz-option.wrong {
        background: #fee2e2;
        border-color: #dc2626;
        color: #991b1b;
    }

    .explanation-box {
        margin-top: 1.5rem;
        padding: 1.2rem;
        border-radius: 12px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 0.9rem;
        display: none;
    }

    .salary-badge {
        background: #f0fdf4;
        color: #16a34a;
        padding: 1rem 2rem;
        border-radius: 20px;
        font-size: 1.5rem;
        font-weight: 800;
        display: inline-block;
        margin-top: 1rem;
    }

    /* Timer Styles */
    .timer-container {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--bg-card);
        padding: 8px 16px;
        border-radius: 50px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .timer-ring {
        position: relative;
        width: 32px;
        height: 32px;
    }

    .timer-ring svg {
        transform: rotate(-90deg);
        width: 32px;
        height: 32px;
    }

    .timer-ring-bg {
        fill: none;
        stroke: var(--border-color);
        stroke-width: 3;
    }

    .timer-ring-fill {
        fill: none;
        stroke: var(--accent-primary);
        stroke-width: 3;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s linear, stroke 0.3s ease;
        stroke-dasharray: 100;
        stroke-dashoffset: 0;
    }

    @keyframes timer-pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    .timer-warning .timer-ring-fill {
        stroke: #f59e0b;
    }

    .timer-danger .timer-ring-fill {
        stroke: #ef4444;
        animation: timer-pulse 0.5s infinite;
    }

    .timer-text {
        font-weight: 850;
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        font-size: 0.9rem;
    }
    .indicator-badge {
        background: var(--bg-card);
        padding: 20px 25px;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        flex: 1;
        min-width: 280px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        transition: transform 0.3s ease;
    }

    .indicator-badge:hover {
        transform: translateY(-3px);
    }

    .indicator-icon-wrapper {
        width: 50px; 
        height: 50px; 
        border-radius: 16px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        flex-shrink: 0;
    }

    .indicator-label {
        font-size: 0.75rem; 
        color: var(--text-tertiary); 
        font-weight: 800; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        margin-bottom: 4px;
    }

    .indicator-value {
        font-weight: 800; 
        color: var(--text-primary); 
        font-size: 1.1rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="guide-dashboard">
    <div class="hero-guide-card">
        <div>
            <span style="background: var(--gradient-primary); color: #fff; padding: 8px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                Guide de Réussite Personnel
            </span>
            <h1 style="font-size: 2.5rem; font-weight: 900; margin-top: 1rem;">
                Prêt pour votre entretien chez <span style="color: var(--accent-primary);"><?php echo htmlspecialchars($cv['titrePoste']); ?></span>
            </h1>
            <p style="color: var(--text-secondary); max-width: 800px; font-size: 1.1rem;">
                Ce guide a été généré spécifiquement pour votre candidature optimisée sur mesure. Suivez ces conseils stratégiques pour maximiser vos chances.
            </p>
        </div>
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="indicator-badge">
                <div class="indicator-icon-wrapper" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                    <i data-lucide="trending-up" style="width: 26px;"></i>
                </div>
                <div>
                    <div class="indicator-label">Stratégie</div>
                    <div class="indicator-value">Optimisé pour le Marché</div>
                </div>
            </div>
            
            <div class="indicator-badge">
                <div class="indicator-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i data-lucide="shield-check" style="width: 26px;"></i>
                </div>
                <div>
                    <div class="indicator-label">Sécurité</div>
                    <div class="indicator-value">Vérifié par Aptus AI</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Justifications -->
    <h2 class="section-title">
        <i data-lucide="info" style="color:var(--accent-primary);"></i> Pourquoi ce CV a-t-il été modifié ?
    </h2>
    <div class="card-grid">
        <div class="premium-card">
            <?php foreach ($guide['justifications'] as $just): ?>
            <div class="justification-item">
                <div style="font-weight: 800; color: var(--accent-primary); margin-bottom: 5px; font-size: 0.9rem;"><?php echo htmlspecialchars($just['champ']); ?></div>
                <div style="font-size: 0.95rem; color: var(--text-primary);"><?php echo htmlspecialchars($just['raison']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Company Insights -->
        <div class="premium-card" style="background: var(--gradient-primary) !important; color: #ffffff !important; border: none;">
            <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem; color: #ffffff !important;">
                <i data-lucide="building-2"></i> Culture d'Entreprise
            </h3>
            <p style="font-size: 1rem; opacity: 0.95; line-height: 1.6; margin-bottom: 2rem; color: #e7e5e5 !important;">
                <?php echo htmlspecialchars($guide['company_insights']['culture']); ?>
            </p>
            <div style="background: rgba(255,255,255,0.15); padding: 1.5rem; border-radius: 18px; border: 1px solid rgba(255,255,255,0.2); color: #ffffff !important;">
                <div style="font-size: 0.8rem; font-weight: 800; margin-bottom: 8px; opacity: 0.9;">CONSEIL STRATÉGIQUE</div>
                <div style="font-weight: 500; color: #ffffff !important;"><?php echo htmlspecialchars($guide['company_insights']['strategic_tips']); ?></div>
            </div>
        </div>
    </div>

    <!-- Interactive Quiz -->
    <h2 class="section-title">
        <i data-lucide="brain-circuit" style="color:#6b34a3;"></i> Quiz Interactif d'Entraînement
    </h2>
    <div class="quiz-container" id="quiz-section">
        <!-- QUIZ INTRO -->
        <div id="quiz-intro" style="text-align:center; padding: 1rem 0;">
            <div style="background: rgba(107, 52, 163, 0.1); padding: 2rem; border-radius: 24px; margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Règles de l'Entraînement</h3>
                <ul style="list-style:none; padding:0; text-align:left; max-width:400px; margin: 0 auto; display:flex; flex-direction:column; gap:12px;">
                    <li style="display:flex; gap:10px;"><i data-lucide="timer" style="color:#6b34a3; width:18px;"></i> 30 secondes par question.</li>
                    <li style="display:flex; gap:10px;"><i data-lucide="zap" style="color:#6b34a3; width:18px;"></i> Passage automatique à la fin du temps.</li>
                    <li style="display:flex; gap:10px;"><i data-lucide="check-circle-2" style="color:#6b34a3; width:18px;"></i> Bilan complet de vos réponses à la fin.</li>
                </ul>
            </div>
            <button class="btn-primary" onclick="initQuiz()" style="padding: 15px 40px; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(107, 52, 163, 0.2);">
                Démarrer l'Entraînement
            </button>
        </div>

        <div id="quiz-steps-container" style="display:none;">
            <?php foreach ($guide['interview_quiz'] as $idx => $q): ?>
            <div class="quiz-step" data-step="<?php echo $idx; ?>" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <span style="font-weight: 800; color: var(--accent-primary); letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem;">Question <?php echo $idx + 1; ?> / 3</span>
                
                <div class="timer-container" id="timer-box-<?php echo $idx; ?>">
                    <div class="timer-ring">
                        <svg>
                            <circle class="timer-ring-bg" cx="16" cy="16" r="14"></circle>
                            <circle class="timer-ring-fill" id="ring-<?php echo $idx; ?>" cx="16" cy="16" r="14"></circle>
                        </svg>
                    </div>
                    <span class="timer-text" id="timer-text-<?php echo $idx; ?>">30s</span>
                </div>
            </div>
            <div class="quiz-question"><?php echo htmlspecialchars($q['question']); ?></div>
            <div class="quiz-options">
                <?php foreach ($q['options'] as $oIdx => $option): ?>
                <div class="quiz-option" onclick="checkAnswer(this, <?php echo $idx; ?>, <?php echo $oIdx; ?>, <?php echo $q['correct_index']; ?>)">
                    <span><?php echo htmlspecialchars($option); ?></span>
                    <i data-lucide="chevron-right" style="width:16px;"></i>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="explanation-box" id="exp-<?php echo $idx; ?>">
                <strong>Feedback de l'Expert :</strong><br>
                <?php echo htmlspecialchars($q['explanation']); ?>
                <div style="margin-top: 1rem;">
                    <button class="btn-primary" id="btn-next-<?php echo $idx; ?>" onclick="nextQuestion(<?php echo $idx; ?>)" style="padding: 10px 25px; font-size: 0.9rem; border-radius: 12px;">Question Suivante</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        
        <div id="quiz-final" style="display:none;">
            <div style="text-align:center; margin-bottom: 2rem;">
                <i data-lucide="award" style="width:64px; height:64px; color:#f59e0b; margin-bottom: 1rem;"></i>
                <h3>Bilan de votre Entraînement</h3>
                <p id="quiz-score-text">Voici l'analyse détaillée de vos réponses.</p>
            </div>
            <div id="quiz-results-summary" style="display:flex; flex-direction:column; gap:15px;">
                <!-- Dynamically filled -->
            </div>
            <div style="text-align:center; margin-top: 2rem;">
                <button class="btn-primary" onclick="location.reload()" style="padding: 12px 30px; border-radius: 12px;">Recommencer le Quiz</button>
            </div>
        </div>
    </div>

    <!-- Formations Section -->
    <div class="card-grid">
        <div class="premium-card">
            <h2 class="section-title" style="margin-bottom: 1.5rem;">
                <i data-lucide="graduation-cap" style="color:#10b981;"></i> Plan de Montée en Compétences
            </h2>
            <?php 
            $gaps = $guide['skill_gaps'] ?? [];
            if (empty($gaps)): 
            ?>
                <p style="color: var(--text-tertiary);">Votre profil semble déjà parfaitement aligné avec les besoins techniques du poste.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <?php foreach ($gaps as $gap): ?>
                        <?php if (!empty($gap['real_formation'])): ?>
                            <div style="background: rgba(16, 185, 129, 0.05); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
                                    <div style="background:#10b981; color:#fff; font-size:0.65rem; font-weight:900; padding:4px 10px; border-radius:50px; text-transform:uppercase;">Disponible sur Aptus</div>
                                    <i data-lucide="award" style="color:#10b981; width:18px;"></i>
                                </div>
                                <div style="font-weight: 850; font-size: 1.1rem; color: var(--text-primary);"><?php echo htmlspecialchars($gap['real_formation']['titre'] ?? 'Formation'); ?></div>
                                <div style="font-size: 0.85rem; color: #10b981; font-weight: 700; margin-top: 5px;"><?php echo htmlspecialchars($gap['skill'] ?? 'Compétence'); ?></div>
                                <p style="font-size: 0.9rem; margin-top: 12px; color: var(--text-secondary); line-height:1.5;">
                                    Nous avons trouvé cette formation dans notre catalogue pour vous aider à maîtriser cet aspect critique du poste.
                                </p>
                                <a href="formations_catalog.php?id=<?php echo $gap['real_formation']['id_formation'] ?? '#'; ?>" class="btn-primary" style="display:inline-block; margin-top:15px; padding: 10px 20px; font-size: 0.85rem; border-radius:12px;">S'inscrire maintenant</a>
                            </div>
                        <?php else: ?>
                            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); border-left: 4px solid var(--accent-primary);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
                                    <div style="color:var(--accent-primary); font-size:0.75rem; font-weight:900; text-transform:uppercase; letter-spacing:0.5px;">Conseil Stratégique</div>
                                    <i data-lucide="compass" style="color:var(--accent-primary); width:18px;"></i>
                                </div>
                                <div style="font-weight: 850; font-size: 1rem; color: var(--text-primary); margin-bottom: 8px;">Auto-formation : <?php echo htmlspecialchars($gap['skill'] ?? 'Compétence'); ?></div>
                                <p style="font-size: 0.9rem; color: var(--text-secondary); line-height:1.5; font-style: italic;">
                                    "<?php echo htmlspecialchars($gap['strategic_advice'] ?? 'Suivez des tutoriels en ligne pour renforcer ce point.'); ?>"
                                </p>
                                <div style="margin-top:10px; font-size:0.8rem; color:var(--text-tertiary); display:flex; align-items:center; gap:5px;">
                                    <i data-lucide="info" style="width:14px;"></i> Note : Formation non disponible dans notre catalogue actuel.
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ENRICHED STRATEGY SECTION -->
    <div class="card-grid">
        <!-- SALARY STRATEGY -->
        <div class="premium-card" style="position:relative; overflow:hidden; background: linear-gradient(165deg, var(--bg-card) 0%, rgba(245, 158, 11, 0.05) 100%);">
            <!-- Decorative Icon -->
            <div style="position:absolute; bottom:-30px; left:-20px; font-size:120px; color:rgba(245, 158, 11, 0.05); transform:rotate(-15deg); pointer-events:none;">
                <i data-lucide="trending-up"></i>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h2 class="section-title" style="margin:0;">
                    <i data-lucide="banknote" style="color:#f59e0b;"></i> Stratégie Salariale
                </h2>
                <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">
                    Analyse Fintech
                </div>
            </div>

            <?php 
                $sal = $guide['salary_strategy'] ?? null; 
                $extracted = $sal['extracted_or_estimated'] ?? 'Estimation';
                $period = $sal['range']['period'] ?? 'an';
                
                // Extraction intelligente des nombres pour éviter le "42-46 - 48"
                $rawMin = $sal['range']['min'] ?? ($sal['estimated_range'] ?? '42');
                $rawMax = $sal['range']['max'] ?? '';

                // On cherche tous les nombres dans les chaînes
                preg_match_all('/\d+/', $rawMin . ' ' . $rawMax, $matches);
                $numbers = $matches[0] ?? [];
                
                if (count($numbers) >= 2) {
                    $displayMin = $numbers[0];
                    $displayMax = end($numbers);
                } else {
                    $displayMin = $numbers[0] ?? '42';
                    $displayMax = '48';
                }

                $currency = $sal['range']['currency'] ?? 'k€';
                $marketContext = $sal['market_context'] ?? 'Analyse stratégique basée sur la rareté de vos compétences et la tension du marché actuel.';
                $scripts = $sal['negotiation_scripts'] ?? [];
                if (empty($scripts) && isset($sal['negotiation_points'])) {
                    foreach($sal['negotiation_points'] as $p) {
                        $scripts[] = [
                            'moment' => 'Argument Stratégique', 
                            'script' => $p,
                            'rationale' => "Cette analyse a été générée avec l'ancienne version. Relancez l'optimisation pour obtenir une analyse psychologique profonde et personnalisée de cet argument."
                        ];
                    }
                }
            ?>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 2rem;">
                <!-- Main Value -->
                <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 30px; border: 1px solid var(--border-color); display:flex; flex-direction:column; justify-content:center; align-items:center; min-height: 160px;">
                    <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-tertiary); letter-spacing: 1.5px; margin-bottom: 10px; text-transform: uppercase;">VOTRE VALEUR</div>
                    <div style="display:flex; align-items:center; gap:2px; flex-wrap: nowrap; white-space: nowrap;">
                        <span style="font-size: 3.2rem; font-weight: 900; color: var(--text-primary); line-height:1;"><?php echo $displayMin; ?></span>
                        <?php if($displayMax && $displayMax !== $displayMin): ?>
                            <span style="font-size: 1.8rem; color: var(--text-tertiary); font-weight: 300; margin: 0 5px;">-</span>
                            <span style="font-size: 3.2rem; font-weight: 900; color: var(--text-primary); line-height:1;"><?php echo $displayMax; ?></span>
                        <?php endif; ?>
                        <span style="font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-left: 5px;"><?php echo $currency; ?></span>
                    </div>
                    <div style="margin-top:15px; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">Par <?php echo $period; ?></div>
                </div>

                <!-- Market Power Meter -->
                <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 30px; border: 1px solid var(--border-color); display:flex; flex-direction:column; justify-content:center; min-height: 160px;">
                    <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-tertiary); letter-spacing: 1.5px; margin-bottom: 20px; text-transform: uppercase;">PUISSANCE DE NÉGOCIATION</div>
                    <div style="height: 14px; background: var(--border-color); border-radius: 20px; overflow:hidden; position:relative; margin-bottom: 12px;">
                        <div style="position:absolute; left:0; top:0; height:100%; width:75%; background: linear-gradient(90deg, #f59e0b, #10b981); box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:800; letter-spacing: 0.5px;">
                        <span style="color: var(--text-tertiary);">FAIBLE</span>
                        <span style="color:#10b981;">DOMINANTE</span>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 15px; line-height: 1.4;">Basé sur le match <?php echo $guide['score_ats'] ?? '85'; ?>% de votre CV.</p>
                </div>
            </div>

            <div style="font-weight: 850; font-size: 0.8rem; margin-bottom: 1.2rem; letter-spacing:1px; color:var(--text-tertiary); display:flex; align-items:center; gap:8px;">
                <i data-lucide="map" style="width:14px;"></i> ROADMAP DE NÉGOCIATION :
            </div>
            
            <div style="display:grid; gap:12px;">
                <?php foreach ($scripts as $ix => $script): ?>
                <div class="nego-item" style="background: var(--bg-card); padding: 1.2rem; border-radius: 20px; border: 1px solid var(--border-color); border-left: 4px solid #f59e0b; transition: all 0.3s ease;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 8px;">
                        <div style="font-weight: 800; font-size: 0.75rem; color: #f59e0b; text-transform: uppercase; letter-spacing:0.5px;">
                            <?php echo $ix+1; ?>. <?php echo htmlspecialchars($script['moment'] ?? 'Phase'); ?>
                        </div>
                        <?php if(isset($script['rationale'])): ?>
                        <button onclick="toggleDrawer(this)" style="background: rgba(245, 158, 11, 0.1); border:none; color:#f59e0b; font-size:0.65rem; font-weight:900; padding:4px 10px; border-radius:50px; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;">
                            <i data-lucide="key" style="width:10px;"></i> ANALYSE TACTIQUE
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div style="font-size: 1rem; color: var(--text-primary); font-style: italic; line-height:1.4; margin-bottom: 0;">
                        "<?php echo htmlspecialchars($script['script'] ?? $script); ?>"
                    </div>

                    <?php if(isset($script['rationale'])): ?>
                    <div class="tactical-drawer" style="max-height: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0;">
                        <div style="margin-top: 15px; padding: 15px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(255,255,255,0.02) 100%); border-radius: 12px; border: 1px dashed rgba(245, 158, 11, 0.2); font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                            <div style="font-weight: 900; color: #f59e0b; font-size: 0.7rem; margin-bottom: 5px; display:flex; align-items:center; gap:5px;">
                                <i data-lucide="lightbulb" style="width:14px;"></i> LEVIER PSYCHOLOGIQUE :
                            </div>
                            <?php echo htmlspecialchars($script['rationale']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PSYCHOLOGY OF SUCCESS -->
        <div class="premium-card" style="position:relative; overflow:hidden; background: linear-gradient(165deg, var(--bg-card) 0%, rgba(107, 52, 163, 0.05) 100%);">
            <!-- Decorative Icon -->
            <div style="position:absolute; top:-20px; right:-20px; font-size:130px; color:rgba(107, 52, 163, 0.04); pointer-events:none;">
                <i data-lucide="brain"></i>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h2 class="section-title" style="margin:0;">
                    <i data-lucide="brain-circuit" style="color:#6b34a3;"></i> Psychologie & Réussite
                </h2>
                <div style="background: rgba(107, 52, 163, 0.1); color: #6b34a3; padding: 6px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">
                    Analyse Comportementale
                </div>
            </div>

            <?php 
                $psy = $guide['psychology_of_success'] ?? null;
                $traits = $psy['target_traits'] ?? [];
                $tactics = $psy['winning_tactics'] ?? [];
                $posture = $psy['ideal_posture'] ?? ($guide['soft_skills_advice'] ?? 'Adoptez une posture professionnelle et proactive.');
            ?>

            <!-- Culture Fit Radar Mock -->
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 2rem; display:flex; align-items:center; gap:20px;">
                <div style="width:80px; height:80px; border-radius:50%; border:8px solid #6b34a3; border-top-color:var(--border-color); display:flex; align-items:center; justify-content:center; transform:rotate(45deg); flex-shrink:0;">
                    <div style="transform:rotate(-45deg); font-weight:900; font-size:1.2rem; color:#6b34a3;">92%</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-tertiary); letter-spacing: 1.5px; margin-bottom: 5px;">CULTURE FIT INDEX</div>
                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-primary);">Excellente Compatibilité</div>
                    <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 3px;">Votre profil match les valeurs de l'entreprise.</p>
                </div>
            </div>

            <!-- Traits & Tactics -->
            <div style="display:grid; gap:20px;">
                <!-- Winning Traits -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                    <?php if(empty($traits)): ?>
                        <div style="background: rgba(107, 52, 163, 0.05); padding: 12px; border-radius: 16px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 800; color: #6b34a3; font-size: 0.9rem;">Intelligence Émotionnelle</div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top:3px;">Maîtrise de la communication interpersonnelle.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($traits as $trait): ?>
                        <div style="background: rgba(107, 52, 163, 0.08); padding: 12px; border-radius: 16px; border: 1px solid rgba(107, 52, 163, 0.15);">
                            <div style="font-weight: 800; color: #6b34a3; font-size: 0.9rem; margin-bottom: 4px;"><?php echo htmlspecialchars($trait['trait'] ?? 'Atout'); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.4; margin-bottom: 8px;"><?php echo htmlspecialchars($trait['why'] ?? ''); ?></div>
                            <?php if(isset($trait['how_to_demonstrate'])): ?>
                            <div style="font-size: 0.7rem; color: #6b34a3; font-weight: 700; background: rgba(107, 52, 163, 0.05); padding: 5px 8px; border-radius: 6px;">
                                💡 Preuve : <span style="font-weight: 500;"><?php echo htmlspecialchars($trait['how_to_demonstrate']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Winning Tactics -->
                <?php if(!empty($tactics)): ?>
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 24px; border: 1px solid var(--border-color);">
                    <div style="font-weight: 850; font-size: 0.8rem; margin-bottom: 1.2rem; color: #6b34a3; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="zap" style="width:16px;"></i> TACTIQUES DE PERSUASION :
                    </div>
                    <?php foreach ($tactics as $tactic): ?>
                    <div style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px dashed var(--border-color); last-child: border-bottom: none;">
                        <div style="font-weight:800; font-size:0.95rem; color:var(--text-primary); margin-bottom: 2px;"><?php echo htmlspecialchars($tactic['tactic'] ?? ''); ?></div>
                        <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom: 6px;"><?php echo htmlspecialchars($tactic['description'] ?? ''); ?></div>
                        <?php if(isset($tactic['how_to_apply'])): ?>
                        <div style="font-size: 0.8rem; color: var(--text-tertiary); padding-left: 10px; border-left: 2px solid #6b34a3; line-height: 1.4;">
                            <strong>Méthode :</strong> <?php echo htmlspecialchars($tactic['how_to_apply']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Expert Posture -->
                <div style="background: var(--bg-card); padding: 1.5rem; border-radius: 24px; border: 1px solid var(--border-color); border-left: 5px solid #6b34a3; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                    <div style="font-weight: 900; font-size: 0.8rem; margin-bottom: 10px; color: #6b34a3; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="shield-check" style="width:16px;"></i> CONSEIL DE POSTURE EXPERTE
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text-primary); line-height: 1.6; font-style: italic; font-weight: 500;">
                        "<?php echo htmlspecialchars($posture); ?>"
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    let timerInterval;
    let timeLeft = 30;
    let userAnswers = [];
    const quizData = <?php echo json_encode($guide['interview_quiz']); ?>;
    
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playTick() {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // Note A5 (Bip aigu)
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
        
        osc.start();
        osc.stop(audioCtx.currentTime + 0.1);
    }

    function toggleDrawer(btn) {
        const item = btn.closest('.nego-item');
        const drawer = item.querySelector('.tactical-drawer');
        const isOpening = drawer.style.maxHeight === '0px' || !drawer.style.maxHeight;

        if (isOpening) {
            drawer.style.maxHeight = '300px';
            drawer.style.opacity = '1';
            btn.style.background = '#f59e0b';
            btn.style.color = '#fff';
        } else {
            drawer.style.maxHeight = '0px';
            drawer.style.opacity = '0';
            btn.style.background = 'rgba(245, 158, 11, 0.1)';
            btn.style.color = '#f59e0b';
        }
    }

    function initQuiz() {
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        document.getElementById('quiz-intro').style.display = 'none';
        document.getElementById('quiz-steps-container').style.display = 'block';
        const firstStep = document.querySelector('.quiz-step[data-step="0"]');
        if (firstStep) {
            firstStep.style.display = 'block';
            startTimer(0);
        }
    }

    function startTimer(qIdx) {
        clearInterval(timerInterval);
        timeLeft = 30;
        const ring = document.getElementById('ring-' + qIdx);
        const text = document.getElementById('timer-text-' + qIdx);
        const box = document.getElementById('timer-box-' + qIdx);
        
        box.classList.remove('timer-warning', 'timer-danger');
        
        timerInterval = setInterval(() => {
            timeLeft--;
            text.innerText = timeLeft + 's';
            
            const offset = 100 - (timeLeft / 30 * 100);
            if (ring) ring.style.strokeDashoffset = offset;
            
            if (timeLeft <= 15) box.classList.add('timer-warning');
            if (timeLeft <= 5) {
                box.classList.add('timer-danger');
                if (timeLeft <= 5 && timeLeft > 0) {
                    playTick();
                }
            }
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                handleTimeout(qIdx);
            }
        }, 1000);
    }

    function handleTimeout(qIdx) {
        userAnswers[qIdx] = { choice: -1, correct: false };
        document.getElementById('exp-' + qIdx).style.display = 'block';
        const nextBtn = document.getElementById('btn-next-' + qIdx);
        if (nextBtn) {
            nextBtn.innerText = "Temps écoulé - Question Suivante";
            nextBtn.style.background = "#ef4444";
        }
        // Auto-next after 3 seconds to let user see feedback
        setTimeout(() => nextQuestion(qIdx), 3000);
    }

    function checkAnswer(el, qIdx, oIdx, correctIdx) {
        if (userAnswers[qIdx] !== undefined) return; // Already answered or timeout
        
        clearInterval(timerInterval);
        userAnswers[qIdx] = { choice: oIdx, correct: oIdx === correctIdx };
        
        const options = el.parentElement.querySelectorAll('.quiz-option');
        options.forEach(opt => opt.style.pointerEvents = 'none');
        
        if (oIdx === correctIdx) {
            el.classList.add('correct');
            el.innerHTML += '<i data-lucide="check" style="color:#16a34a; width:20px;"></i>';
        } else {
            el.classList.add('wrong');
            el.innerHTML += '<i data-lucide="x" style="color:#dc2626; width:20px;"></i>';
            options[correctIdx].classList.add('correct');
            options[correctIdx].innerHTML += '<i data-lucide="check" style="color:#16a34a; width:20px;"></i>';
        }
        
        document.getElementById('exp-' + qIdx).style.display = 'block';
        if (window.lucide) lucide.createIcons();
    }

    function nextQuestion(currIdx) {
        const steps = document.querySelectorAll('.quiz-step');
        steps[currIdx].style.display = 'none';
        
        if (steps[currIdx + 1]) {
            steps[currIdx + 1].style.display = 'block';
            startTimer(currIdx + 1);
        } else {
            showFinalResults();
        }
    }

    function showFinalResults() {
        document.getElementById('quiz-steps-container').style.display = 'none';
        const finalBox = document.getElementById('quiz-final');
        const summary = document.getElementById('quiz-results-summary');
        finalBox.style.display = 'block';
        
        let correctCount = userAnswers.filter(a => a && a.correct).length;
        document.getElementById('quiz-score-text').innerText = `Vous avez réussi ${correctCount} questions sur ${quizData.length}.`;

        summary.innerHTML = '';
        quizData.forEach((q, i) => {
            const ans = userAnswers[i] || { choice: -1, correct: false };
            const item = document.createElement('div');
            item.className = 'premium-card';
            item.style.padding = '1.5rem';
            item.style.marginBottom = '0';
            
            let statusHTML = ans.correct ? 
                '<span style="color:#16a34a; font-weight:800;">✓ CORRECT</span>' : 
                (ans.choice === -1 ? '<span style="color:#ef4444; font-weight:800;">⏱ TEMPS ÉCOULÉ</span>' : '<span style="color:#ef4444; font-weight:800;">✗ INCORRECT</span>');

            item.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <strong style="font-size:0.9rem;">QUESTION ${i+1}</strong>
                    ${statusHTML}
                </div>
                <p style="margin-bottom:10px; font-weight:600;">${q.question}</p>
                <div style="font-size:0.85rem; background:var(--bg-secondary); padding:10px; border-radius:10px;">
                    <strong style="color:var(--accent-primary);">Réponse attendue :</strong> ${q.options[q.correct_index]}
                </div>
            `;
            summary.appendChild(item);
        });
        
        if (window.lucide) lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
