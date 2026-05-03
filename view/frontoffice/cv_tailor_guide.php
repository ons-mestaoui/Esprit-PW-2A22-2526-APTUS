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
            <span style="background: var(--gradient-primary); color: #fff; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
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

    <!-- Formations & Salary -->
    <div class="card-grid">
    <!-- Formations & Salary -->
    <div class="card-grid">
        <!-- Formations -->
        <div class="premium-card">
            <h2 class="section-title" style="margin-bottom: 1.5rem;">
                <i data-lucide="graduation-cap" style="color:#10b981;"></i> Plan de Montée en Compétences
            </h2>
            <?php if (empty($guide['skill_gaps'])): ?>
                <p style="color: var(--text-tertiary);">Votre profil semble déjà parfaitement aligné avec les besoins techniques du poste.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <?php foreach ($guide['skill_gaps'] as $gap): ?>
                        <?php if ($gap['real_formation']): ?>
                            <!-- FORMATION RÉELLE DU SITE -->
                            <div style="background: rgba(16, 185, 129, 0.05); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
                                    <div style="background:#10b981; color:#fff; font-size:0.65rem; font-weight:900; padding:4px 10px; border-radius:50px; text-transform:uppercase;">Disponible sur Aptus</div>
                                    <i data-lucide="award" style="color:#10b981; width:18px;"></i>
                                </div>
                                <div style="font-weight: 850; font-size: 1.1rem; color: var(--text-primary);"><?php echo htmlspecialchars($gap['real_formation']['titre']); ?></div>
                                <div style="font-size: 0.85rem; color: #10b981; font-weight: 700; margin-top: 5px;"><?php echo htmlspecialchars($gap['skill']); ?></div>
                                <p style="font-size: 0.9rem; margin-top: 12px; color: var(--text-secondary); line-height:1.5;">
                                    Nous avons trouvé cette formation dans notre catalogue pour vous aider à maîtriser cet aspect critique du poste.
                                </p>
                                <a href="formations_catalog.php?id=<?php echo $gap['real_formation']['id_formation']; ?>" class="btn-primary" style="display:inline-block; margin-top:15px; padding: 10px 20px; font-size: 0.85rem; border-radius:12px;">S'inscrire maintenant</a>
                            </div>
                        <?php else: ?>
                            <!-- CONSEIL STRATÉGIQUE (PAS DE FORMATION LOCALE) -->
                            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); border-left: 4px solid var(--accent-primary);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
                                    <div style="color:var(--accent-primary); font-size:0.75rem; font-weight:900; text-transform:uppercase; letter-spacing:0.5px;">Conseil Stratégique</div>
                                    <i data-lucide="compass" style="color:var(--accent-primary); width:18px;"></i>
                                </div>
                                <div style="font-weight: 850; font-size: 1rem; color: var(--text-primary); margin-bottom: 8px;">Auto-formation : <?php echo htmlspecialchars($gap['skill']); ?></div>
                                <p style="font-size: 0.9rem; color: var(--text-secondary); line-height:1.5; font-style: italic;">
                                    "<?php echo htmlspecialchars($gap['strategic_advice']); ?>"
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

        <!-- Salary & Soft Skills -->
        <div class="premium-card">
            <h2 class="section-title" style="margin-bottom: 1.5rem;">
                <i data-lucide="banknote" style="color:#f59e0b;"></i> Stratégie Salariale
            </h2>
            <div style="text-align:center;">
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-tertiary);">ESTIMATION MARCHÉ</div>
                <div class="salary-badge"><?php echo htmlspecialchars($guide['salary_strategy']['estimated_range']); ?></div>
            </div>
            <div style="margin-top: 2rem;">
                <div style="font-weight: 800; font-size: 0.9rem; margin-bottom: 1rem;">POINTS DE NÉGOCIATION :</div>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($guide['salary_strategy']['negotiation_points'] as $p): ?>
                    <li style="display:flex; gap: 10px; margin-bottom: 10px; font-size: 0.95rem; align-items:flex-start;">
                        <i data-lucide="check" style="color:#16a34a; width:18px; flex-shrink:0;"></i>
                        <?php echo htmlspecialchars($p); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <h3 style="font-size: 1.1rem; display:flex; align-items:center; gap: 8px;">
                    <i data-lucide="heart" style="color:#e11d48;"></i> Qualités Humaines (Soft Skills)
                </h3>
                <p style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; margin-top: 10px;">
                    <?php echo htmlspecialchars($guide['soft_skills_advice']); ?>
                </p>
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
