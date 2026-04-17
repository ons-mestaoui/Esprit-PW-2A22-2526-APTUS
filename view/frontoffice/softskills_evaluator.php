<?php
/**
 * ============================================================
 * softskills_evaluator.php — Concept 3 : Évaluateur Soft-Skills
 * ============================================================
 * Interface webcam avec face-api.js.
 * Analyse les expressions faciales pendant 10 secondes et calcule
 * un score de "confiance/engagement". Si le score dépasse 55/100,
 * le certificat est validé via AJAX → SoftSkillsController.
 *
 * URL : softskills_evaluator.php?id=<id_formation>
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Évaluateur Soft-Skills — Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';

    // Validation du paramètre
    $id_formation = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_formation <= 0) {
        header('Location: formations_my.php');
        exit();
    }

    // Récupérer le titre de la formation pour l'affichage
    try {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT titre FROM Formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $titreFormation = $stmt->fetchColumn() ?: 'Formation';
    } catch (Exception $e) {
        $titreFormation = 'Formation';
    }

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- =====================================================
     STYLES : Évaluateur Soft-Skills (webcam + jauges)
     ===================================================== -->
<style>
.softskills-page {
    max-width: 860px;
    margin: 0 auto;
    padding: 1rem 0 4rem;
}
.softskills-page h1 {
    font-size: 1.7rem;
    font-weight: 800;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}
.softskills-page .subtitle {
    color: var(--text-secondary, #64748b);
    margin-bottom: 2.5rem;
    font-size: 0.95rem;
}

/* ── Layout principal ── */
.evaluator-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}
@media (max-width: 700px) {
    .evaluator-grid { grid-template-columns: 1fr; }
}

/* ── Zone webcam ── */
.webcam-container {
    background: #0f0f1a;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    aspect-ratio: 4/3;
    box-shadow: 0 20px 60px rgba(99,102,241,0.25);
    border: 2px solid rgba(99,102,241,0.3);
}
.webcam-container video,
.webcam-container canvas {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    object-fit: cover;
}
.webcam-container canvas {
    pointer-events: none;
}

/* Overlay d'état de la webcam */
.webcam-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15,15,26,0.85);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    z-index: 10;
    transition: opacity 0.4s ease;
    border-radius: inherit;
}
.webcam-overlay.hidden { opacity: 0; pointer-events: none; }

.webcam-icon { font-size: 3rem; animation: pulse-cam 2s infinite; }
@keyframes pulse-cam {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.1); }
}

/* Timer ring */
.timer-ring {
    position: absolute;
    top: 1rem; right: 1rem;
    width: 56px;
    height: 56px;
}
.timer-ring svg { transform: rotate(-90deg); }
.timer-ring circle {
    fill: none;
    stroke-width: 4;
}
.timer-ring .bg  { stroke: rgba(255,255,255,0.1); }
.timer-ring .fg  {
    stroke: #6366f1;
    stroke-dasharray: 138;
    stroke-dashoffset: 0;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s linear, stroke 0.3s;
}
.timer-ring .label {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 800;
    color: white;
}

/* Face detection frame */
.face-frame {
    position: absolute;
    border: 2px solid #6366f1;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(99,102,241,0.5);
    animation: frame-pulse 1.5s infinite;
    z-index: 5;
}
@keyframes frame-pulse {
    0%, 100% { box-shadow: 0 0 10px rgba(99,102,241,0.4); }
    50%       { box-shadow: 0 0 25px rgba(99,102,241,0.8); }
}

/* ── Panneau d'analyse (jauges) ── */
.analysis-panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.analysis-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid var(--border-color, #e2e8f0);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.analysis-card__title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-light, #94a3b8);
    font-weight: 700;
    margin-bottom: 1.2rem;
}

/* Score global */
.score-display {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.score-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(
        #6366f1 0deg,
        #e2e8f0 0deg
    );
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex-shrink: 0;
    transition: background 0.5s ease;
}
.score-circle::before {
    content: '';
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: white;
    position: absolute;
}
.score-circle .score-val {
    position: relative;
    z-index: 1;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1e293b;
}
.score-status {
    font-size: 0.85rem;
    color: var(--text-secondary, #64748b);
}
.score-status strong {
    display: block;
    font-size: 1.1rem;
    color: #1e293b;
    margin-bottom: 0.2rem;
}

/* Jauges d'expression */
.gauge-list { display: flex; flex-direction: column; gap: 1rem; }
.gauge-item {}
.gauge-item__label {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
    margin-bottom: 0.4rem;
    color: #475569;
}
.gauge-item__label span:last-child { font-weight: 700; }
.gauge-bar {
    width: 100%;
    height: 10px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.gauge-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.4s ease, background 0.3s;
    min-width: 2%;
}

/* ── Bouton de démarrage ── */
.btn-start {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 2rem;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(99,102,241,0.35);
    transition: transform 0.2s, box-shadow 0.2s;
    width: 100%;
    justify-content: center;
}
.btn-start:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(99,102,241,0.45);
}
.btn-start:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Status bar ── */
.status-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(99,102,241,0.08);
    border-radius: 10px;
    font-size: 0.85rem;
    color: #6366f1;
    font-weight: 500;
}
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6366f1;
    animation: blink 1s infinite;
    flex-shrink: 0;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.2; }
}

/* ── Résultats finaux ── */
.result-banner {
    margin-top: 2rem;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    animation: slideUp 0.5s ease both;
}
.result-banner.success {
    background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(5,150,105,0.1));
    border: 1px solid rgba(16,185,129,0.3);
}
.result-banner.fail {
    background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(220,38,38,0.1));
    border: 1px solid rgba(239,68,68,0.3);
}
.result-banner__icon { font-size: 3rem; margin-bottom: 0.75rem; }
.result-banner__title { font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; }
.result-banner__msg { font-size: 0.9rem; color: var(--text-secondary,#64748b); }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Instructions ── */
.instructions {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color,#e2e8f0);
}
.instructions ul {
    list-style: none;
    padding: 0; margin: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}
@media(max-width:600px) { .instructions ul { grid-template-columns: 1fr; } }
.instructions li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #475569;
    padding: 0.6rem 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
}
</style>

<div class="softskills-page">
    <a href="formations_my.php" style="display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--text-secondary);text-decoration:none;margin-bottom:2rem;transition:color .2s;" onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color=''">
        ← Retour à mes formations
    </a>

    <h1>🧠 Évaluateur Soft-Skills</h1>
    <p class="subtitle">
        Validez votre certificat pour <strong id="formation-title">
            <?php echo htmlspecialchars($titreFormation); ?>
        </strong> grâce à une analyse de votre expression faciale.
    </p>

    <!-- Instructions -->
    <div class="instructions">
        <h3 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light,#94a3b8);margin-bottom:1rem;font-weight:700;">
            📋 Instructions
        </h3>
        <ul>
            <li>😊 Souriez et montrez votre confiance</li>
            <li>💡 Bonne lumière sur votre visage</li>
            <li>👀 Regardez la caméra sans bouger</li>
            <li>⏱️ L'analyse dure exactement 10 secondes</li>
            <li>🏆 Score minimum requis : <strong>55/100</strong></li>
            <li>🎓 Réessayez autant de fois que nécessaire</li>
        </ul>
    </div>

    <div class="evaluator-grid">

        <!-- ── Zone Webcam ── -->
        <div>
            <div class="webcam-container" id="webcam-container">
                <video id="webcam-video" autoplay muted playsinline></video>
                <canvas id="webcam-canvas"></canvas>

                <!-- Timer circulaire -->
                <div class="timer-ring" id="timer-ring" style="display:none;">
                    <svg width="56" height="56" viewBox="0 0 56 56">
                        <circle class="bg" cx="28" cy="28" r="22"/>
                        <circle class="fg" id="timer-circle" cx="28" cy="28" r="22"/>
                    </svg>
                    <div class="label" id="timer-label">10</div>
                </div>

                <!-- Overlay (état initial) -->
                <div class="webcam-overlay" id="webcam-overlay">
                    <div class="webcam-icon">📷</div>
                    <p style="color:white;font-size:.95rem;font-weight:600;text-align:center;">
                        Autorisez l'accès<br>à la caméra
                    </p>
                    <div style="font-size:.8rem;color:rgba(255,255,255,.5);text-align:center;">
                        Cliquez sur "Démarrer l'évaluation"<br>pour activer la webcam
                    </div>
                </div>
            </div>

            <!-- Bouton démarrer -->
            <button class="btn-start" id="btn-start" style="margin-top:1.5rem;" onclick="startEvaluation()">
                🚀 Démarrer l'évaluation
            </button>
        </div>

        <!-- ── Panneau d'analyse ── -->
        <div class="analysis-panel">

            <!-- Score global -->
            <div class="analysis-card">
                <div class="analysis-card__title">🏅 Score Soft-Skills Global</div>
                <div class="score-display">
                    <div class="score-circle" id="score-circle">
                        <span class="score-val" id="score-val">—</span>
                    </div>
                    <div class="score-status">
                        <strong id="score-label">En attente</strong>
                        <span id="score-desc">Démarrez l'évaluation pour voir votre score en temps réel.</span>
                    </div>
                </div>
            </div>

            <!-- Status live -->
            <div class="status-bar" id="status-bar">
                <div class="status-dot" style="background:#94a3b8;animation:none;"></div>
                <span id="status-text">Prêt à démarrer — Assurez-vous d'être bien éclairé</span>
            </div>

            <!-- Jauges d'expression -->
            <div class="analysis-card">
                <div class="analysis-card__title">📊 Détail des Expressions</div>
                <div class="gauge-list">
                    <?php
                    $gauges = [
                        ['id' => 'gauge-happy',     'emoji' => '😊', 'label' => 'Sourire / Positif',   'color' => '#10b981'],
                        ['id' => 'gauge-surprised', 'emoji' => '😲', 'label' => 'Enthousiasme',         'color' => '#3b82f6'],
                        ['id' => 'gauge-neutral',   'emoji' => '😐', 'label' => 'Calme / Sérénité',     'color' => '#8b5cf6'],
                        ['id' => 'gauge-confident', 'emoji' => '🧠', 'label' => 'Confiance (composite)', 'color' => '#f59e0b'],
                        ['id' => 'gauge-sad',       'emoji' => '😞', 'label' => 'Stress / Négatif',     'color' => '#ef4444'],
                    ];
                    foreach ($gauges as $g): ?>
                    <div class="gauge-item">
                        <div class="gauge-item__label">
                            <span><?php echo $g['emoji']; ?> <?php echo $g['label']; ?></span>
                            <span id="<?php echo $g['id']; ?>-val">0%</span>
                        </div>
                        <div class="gauge-bar">
                            <div class="gauge-fill" id="<?php echo $g['id']; ?>"
                                 style="width:0%; background:<?php echo $g['color']; ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bannière de résultat (cachée par défaut) -->
    <div id="result-banner" style="display:none;"></div>
</div>

<!-- =====================================================
     JAVASCRIPT — face-api.js + logique d'évaluation
     ===================================================== -->

<!-- Chargement de face-api.js depuis CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
// ================================================================
// CONFIGURATION
// ================================================================
const FORMATION_ID   = <?php echo (int)$id_formation; ?>;
const EVAL_DURATION  = 10;       // Durée d'analyse en secondes
const SEUIL_SCORE    = 55;       // Seuil minimum pour valider (mirrors PHP)
const TIMER_CIRCLE   = 138;      // Circonférence du cercle SVG (2π × 22)
const MODEL_URL      = 'https://justadudewhohacks.github.io/face-api.js/models';

// ================================================================
// ÉTAT GLOBAL
// ================================================================
let isRunning    = false;     // Évaluation en cours ?
let videoStream  = null;      // Stream webcam actif
let frameScores  = [];        // Scores collectés frame par frame
let analysisLoop = null;      // ID du setInterval d'analyse
let timerCountdown = null;    // ID du setInterval du compte à rebours

// Éléments DOM fréquemment utilisés
const video      = document.getElementById('webcam-video');
const canvas     = document.getElementById('webcam-canvas');
const overlay    = document.getElementById('webcam-overlay');
const btnStart   = document.getElementById('btn-start');
const timerRing  = document.getElementById('timer-ring');
const timerLabel = document.getElementById('timer-label');
const timerCircle= document.getElementById('timer-circle');
const statusBar  = document.getElementById('status-bar');

// ================================================================
// CHARGEMENT DES MODÈLES face-api.js
// ================================================================

/**
 * Charge les 3 modèles nécessaires :
 * - tinyFaceDetector    : détection rapide du visage
 * - faceExpressionNet   : reconnaissance des 7 expressions
 * - faceLandmark68Net   : landmarks (pour l'overlay de points)
 */
async function loadModels() {
    updateStatus('⏳ Chargement des modèles IA...', '#6366f1', true);
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL)
        ]);
        updateStatus('✅ Modèles chargés — Prêt à démarrer !', '#10b981', false);
        btnStart.disabled = false;
    } catch (err) {
        updateStatus('❌ Erreur chargement des modèles : ' + err.message, '#ef4444', false);
        console.error('face-api load error:', err);
    }
}

// ================================================================
// FONCTION PRINCIPALE : Démarrer l'évaluation
// ================================================================
async function startEvaluation() {
    if (isRunning) return;

    // Réinitialisation
    frameScores = [];
    document.getElementById('result-banner').style.display = 'none';

    // Activation de la webcam
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        video.srcObject = videoStream;
        await new Promise(resolve => { video.onloadedmetadata = resolve; });

        // Redimensionner le canvas
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;

    } catch (err) {
        updateStatus('❌ Accès caméra refusé : ' + err.message, '#ef4444', false);
        return;
    }

    // Masquer l'overlay webcam
    overlay.classList.add('hidden');
    timerRing.style.display = 'block';
    btnStart.disabled = true;
    isRunning = true;

    updateStatus('🎥 Analyse en cours... Souriez et regardez la caméra !', '#6366f1', true);

    // Lancement de la boucle d'analyse (toutes les 200ms)
    analysisLoop = setInterval(analyzeFrame, 200);

    // Compte à rebours de 10 secondes
    let secondsLeft = EVAL_DURATION;
    timerLabel.textContent = secondsLeft;
    timerCircle.style.strokeDashoffset = 0;

    timerCountdown = setInterval(() => {
        secondsLeft--;
        timerLabel.textContent = secondsLeft;

        // Mise à jour de l'arc SVG
        const progress = (EVAL_DURATION - secondsLeft) / EVAL_DURATION;
        timerCircle.style.strokeDashoffset = TIMER_CIRCLE * progress;

        // Changement de couleur selon le temps restant
        if (secondsLeft <= 3)       timerCircle.style.stroke = '#ef4444';
        else if (secondsLeft <= 6)  timerCircle.style.stroke = '#f59e0b';
        else                        timerCircle.style.stroke = '#6366f1';

        if (secondsLeft <= 0) {
            finishEvaluation(); // Fin de l'évaluation
        }
    }, 1000);
}

// ================================================================
// ANALYSE D'UNE FRAME (appelée toutes les 200ms)
// ================================================================
async function analyzeFrame() {
    if (!isRunning || video.paused || video.ended) return;

    try {
        const detections = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceExpressions();

        if (!detections) {
            updateStatus('⚠️ Visage non détecté — Positionnez-vous face à la caméra', '#f59e0b', true);
            return;
        }

        const { expressions } = detections;

        // Mise à jour des jauges
        updateGauge('gauge-happy',     expressions.happy     || 0);
        updateGauge('gauge-surprised', expressions.surprised  || 0);
        updateGauge('gauge-neutral',   expressions.neutral    || 0);
        updateGauge('gauge-sad',       expressions.sad        || 0);

        // Score de "confiance composite" : combinaison pondérée des expressions positives
        // Formule : 70% sourire + 20% surprise + 10% neutre - 30% tristesse/colère
        const confidence = Math.min(100, Math.max(0,
            (expressions.happy     || 0) * 70 +
            (expressions.surprised || 0) * 20 +
            (expressions.neutral   || 0) * 10 -
            (expressions.sad       || 0) * 30 -
            (expressions.angry     || 0) * 30 -
            (expressions.fearful   || 0) * 20
        ) * 100);

        updateGauge('gauge-confident', confidence / 100);
        frameScores.push(confidence);

        // Mise à jour du score en temps réel
        const avgScore = frameScores.reduce((a, b) => a + b, 0) / frameScores.length;
        updateScoreDisplay(avgScore);

        // Dessin du mesh facial sur canvas
        drawDetections(detections);

        updateStatus('🎥 Analyse en cours... Continuez à sourire !', '#6366f1', true);

    } catch (err) {
        console.warn('Frame analysis error:', err);
    }
}

// ================================================================
// DESSIN DES DÉTECTIONS SUR LE CANVAS (overlay visuel)
// ================================================================
function drawDetections(detections) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Redimensionnement pour correspondre aux dimensions vidéo
    const displaySize = { width: canvas.width, height: canvas.height };
    const resized = faceapi.resizeResults(detections, displaySize);

    // Dessine les landmarks du visage (points de repère)
    faceapi.draw.drawFaceLandmarks(canvas, resized);
}

// ================================================================
// FIN DE L'ÉVALUATION
// ================================================================
function finishEvaluation() {
    // Arrêt des boucles
    clearInterval(analysisLoop);
    clearInterval(timerCountdown);
    isRunning = false;

    // Arrêt de la webcam
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }

    // Calcul du score final (moyenne de toutes les frames)
    const finalScore = frameScores.length > 0
        ? Math.round(frameScores.reduce((a, b) => a + b, 0) / frameScores.length)
        : 0;

    timerRing.style.display = 'none';
    updateScoreDisplay(finalScore);
    updateStatus('📊 Analyse terminée !', '#10b981', false);

    // Envoi du score au backend via AJAX
    submitScore(finalScore);
}

// ================================================================
// ENVOI DU SCORE AU BACKEND PHP (SoftSkillsController)
// ================================================================
function submitScore(score) {
    updateStatus('⬆️ Envoi du score au serveur...', '#f59e0b', true);

    const formData = new FormData();
    formData.append('id_formation', FORMATION_ID);
    formData.append('score', score);

    fetch('ajax_handler.php?action=softskills_validate', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showResultBanner(data.success, data.message, score);
        if (data.success) {
            updateStatus('🎓 Certificat validé avec succès !', '#10b981', false);
        } else {
            updateStatus('📉 ' + data.message, '#ef4444', false);
            btnStart.disabled = false; // Permettre de réessayer
            btnStart.textContent = '🔄 Réessayer l\'évaluation';
        }
    })
    .catch(err => {
        showResultBanner(false, 'Erreur réseau : ' + err.message, score);
        btnStart.disabled = false;
    });
}

// ================================================================
// FONCTIONS UI HELPERS
// ================================================================

/** Met à jour une jauge d'expression (valeur 0-1) */
function updateGauge(id, value) {
    const pct = Math.round(value * 100);
    const fillEl = document.getElementById(id);
    const valEl  = document.getElementById(id + '-val');
    if (fillEl) fillEl.style.width = pct + '%';
    if (valEl)  valEl.textContent  = pct + '%';
}

/** Met à jour l'affichage du score circulaire */
function updateScoreDisplay(score) {
    const val = Math.round(score);
    document.getElementById('score-val').textContent = val;

    // Mise à jour du cercle CSS conic-gradient
    const color = val >= SEUIL_SCORE ? '#10b981' : (val >= 40 ? '#f59e0b' : '#ef4444');
    const circle = document.getElementById('score-circle');
    circle.style.background =
        `conic-gradient(${color} ${val * 3.6}deg, #e2e8f0 ${val * 3.6}deg)`;

    // Label de statut
    const label = document.getElementById('score-label');
    const desc  = document.getElementById('score-desc');
    if (val >= SEUIL_SCORE) {
        label.textContent = '🟢 Excellent !';
        desc.textContent  = 'Vous allez probablement valider votre certificat.';
    } else if (val >= 40) {
        label.textContent = '🟡 Passable';
        desc.textContent  = 'Souriez davantage pour atteindre le seuil de 55/100.';
    } else {
        label.textContent = '🔴 Insuffisant';
        desc.textContent  = 'Montrez plus de confiance et d\'enthousiasme !';
    }
}

/** Met à jour la barre de statut */
function updateStatus(text, color, animated) {
    const dot = statusBar.querySelector('.status-dot');
    statusBar.querySelector('#status-text').textContent = text;
    statusBar.style.background = color + '18';
    statusBar.style.color      = color;
    if (dot) {
        dot.style.background  = color;
        dot.style.animation   = animated ? 'blink 1s infinite' : 'none';
    }
}

/** Affiche la bannière de résultat final */
function showResultBanner(success, message, score) {
    const banner = document.getElementById('result-banner');
    banner.className = 'result-banner ' + (success ? 'success' : 'fail');
    banner.innerHTML = `
        <div class="result-banner__icon">${success ? '🎓' : '😔'}</div>
        <div class="result-banner__title">
            ${success ? 'Certificat Validé !' : 'Score Insuffisant'}
        </div>
        <div class="result-banner__msg">${message}</div>
        ${success ? `
            <a href="certificate.php?f_id=${FORMATION_ID}"
               class="btn btn-primary" style="margin-top:1.25rem;display:inline-block;">
                🎓 Télécharger le Certificat
            </a>
            <a href="formations_my.php"
               style="display:block;margin-top:.75rem;font-size:.85rem;color:var(--text-secondary);text-decoration:none;">
               ← Retour à mes formations
            </a>
        ` : `
            <button onclick="resetEvaluation()"
                    class="btn btn-primary" style="margin-top:1.25rem;">
                🔄 Réessayer l'évaluation
            </button>
        `}
    `;
    banner.style.display = 'block';
    banner.scrollIntoView({ behavior: 'smooth' });
}

/** Remet tout à zéro pour permettre une nouvelle tentative */
function resetEvaluation() {
    document.getElementById('result-banner').style.display = 'none';
    document.getElementById('score-val').textContent = '—';
    document.getElementById('score-circle').style.background =
        'conic-gradient(#6366f1 0deg, #e2e8f0 0deg)';
    document.getElementById('score-label').textContent = 'En attente';
    document.getElementById('score-desc').textContent = 'Cliquez sur Démarrer pour lancer une nouvelle évaluation.';

    ['gauge-happy','gauge-surprised','gauge-neutral','gauge-sad','gauge-confident'].forEach(id => {
        updateGauge(id, 0);
    });

    overlay.classList.remove('hidden');
    btnStart.disabled = false;
    btnStart.innerHTML = '🚀 Démarrer l\'évaluation';
    updateStatus('Prêt à démarrer — Assurez-vous d\'être bien éclairé', '#94a3b8', false);
    frameScores = [];

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// ================================================================
// INITIALISATION AU CHARGEMENT
// ================================================================
document.addEventListener('DOMContentLoaded', () => {
    btnStart.disabled = true; // Désactivé jusqu'au chargement des modèles
    loadModels();
});
</script>
