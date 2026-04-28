<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Salle de cours virtuelle - Aptus";

$room_url = $_GET['url'] ?? '';
$id_formation = $_GET['id_formation'] ?? 0;
// Fallback sur session ou 5
$id_user = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 5;
$role = strtolower($_GET['role'] ?? $_SESSION['role'] ?? 'candidat'); // tuteur ou candidat

// On s'assure d'avoir un lien jitsi valide
if (strpos($room_url, 'meet.jit.si') === false) {
    die("Lien Jitsi invalide ou non fourni.");
}

// Nettoyage de l'URL pour la passer dans l'API iframe Jitsi
$room_name = str_replace('https://meet.jit.si/', '', $room_url);
$room_name = explode('?', $room_name)[0]; // Remove query params if any

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    .jitsi-container {
        display: flex;
        flex-direction: column;
        margin-top: 2rem;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        background: var(--bg-card);
        height: 80vh;
        position: relative;
    }
    
    #jitsi-meet-wrap {
        flex: 1;
        width: 100%;
        background: #000;
    }
    
    .ia-status-bar {
        background: var(--bg-surface);
        padding: 0.75rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    #stt-transcript-bar {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 70%;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(5px);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        text-align: center;
        font-size: 1.1rem;
        z-index: 100;
        display: none;
        border: 1px solid rgba(255,255,255,0.2);
    }

    .ai-monitor-wrapper {
        position: absolute;
        bottom: 85px;
        right: 20px;
        width: 180px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--accent-primary);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        z-index: 1000;
        background: #000;
    }

    #ai-video-feed {
        width: 100%;
        display: block;
        transform: scaleX(-1);
    }

    #ai-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1);
    }

    #emotion-indicator {
        font-weight: bold;
        color: var(--accent-primary);
        text-transform: capitalize;
    }
</style>

<div class="jitsi-container">
    <div class="ia-status-bar">
        <!-- Bouton Retour -->
        <a href="formations_my.php" 
           style="display:flex; align-items:center; gap:6px; color:var(--text-secondary); text-decoration:none; font-size:0.82rem; font-weight:600; padding:4px 10px; border-radius:8px; border:1px solid var(--border-color); transition: all 0.2s;"
           onmouseover="this.style.background='var(--bg-surface)'; this.style.color='var(--text-primary)'"
           onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Mes Formations
        </a>

        <!-- Nom de la salle (centré) -->
        <div style="font-size:0.85rem;">
            <i data-lucide="video" style="width:16px;height:16px;vertical-align:middle;"></i> 
            Salle Virtuelle : <b><?php echo htmlspecialchars($room_name); ?></b>
        </div>

        <!-- Statut IA / Contrôles Tuteur -->
        <?php if ($role !== 'tuteur'): ?>
            <div title="Analyse cognitive IA en Edge Computing" style="font-size:0.82rem;">
                <i data-lucide="brain-circuit" style="width:16px;height:16px;vertical-align:middle;color:var(--accent-primary);"></i> 
                Agent Aptus actif — État : <span id="emotion-indicator" style="font-weight:700; color:var(--accent-primary);">Chargement...</span>
            </div>
        <?php else: ?>
            <div style="color:var(--text-secondary); display:flex; align-items:center; gap:1rem;">
                <div><i data-lucide="shield" style="width:16px;height:16px;vertical-align:middle;"></i> Mode Tuteur</div>
                <button onclick="showClassEmotions()" class="btn btn-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border:none; padding:4px 12px; border-radius:6px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(99,102,241,0.3);">
                    <i data-lucide="brain-circuit" style="width:14px;height:14px;"></i> Bilan IA de la classe
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="jitsi-meet-wrap"></div>
    
    <div id="stt-transcript-bar">
        <span style="opacity:0.6; font-size: 0.8rem; display:block; margin-bottom: 2px;">Transcription en direct (STT)</span>
        <span id="stt-text">En attente de parole...</span>
    </div>
    
    <?php if ($role !== 'tuteur'): ?>
        <!-- Moniteur Edge AI (Pip visible avec détection) -->
        <div class="ai-monitor-wrapper">
            <video id="ai-video-feed" autoplay muted playsinline></video>
            <canvas id="ai-canvas"></canvas>
        </div>
    <?php endif; ?>
</div>

<script src='https://meet.jit.si/external_api.js'></script>
<?php if ($role !== 'tuteur'): ?>
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<?php endif; ?>

<script>
    let fullTranscript = "";

    document.addEventListener("DOMContentLoaded", () => {
        // 1. Initialiser Jitsi
        const domain = 'meet.jit.si';
        const options = {
            roomName: '<?php echo $room_name; ?>',
            parentNode: document.querySelector('#jitsi-meet-wrap'),
            configOverwrite: { startWithAudioMuted: true, startWithVideoMuted: false },
            interfaceConfigOverwrite: { SHOW_JITSI_WATERMARK: false },
            userInfo: { displayName: 'Aptus User' }
        };
        const api = new JitsiMeetExternalAPI(domain, options);

        // STT Logic
        initSTT();

        // Detect when user leaves to send recording notification
        api.addEventListener('videoConferenceLeft', () => {
            sendRecordingNotif();
        });

        <?php if ($role !== 'tuteur'): ?>
        // 2. Initialiser l'analyse FaceAPI en tâche de fond pour l'étudiant
        initEdgeAI();
        <?php endif; ?>
    });

    function initSTT() {
        if (!('webkitSpeechRecognition' in window) && !('speechRecognition' in window)) {
            console.warn("STT not supported in this browser.");
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        const bar = document.getElementById('stt-transcript-bar');
        const sttText = document.getElementById('stt-text');

        recognition.lang = 'fr-FR';
        recognition.continuous = true;
        recognition.interimResults = true;

        recognition.onstart = () => { bar.style.display = 'block'; };
        
        recognition.onresult = (event) => {
            let interimTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    fullTranscript += event.results[i][0].transcript + ". ";
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            sttText.innerText = interimTranscript || "Écoute en cours...";
        };

        recognition.onerror = (event) => { console.error("STT Error:", event.error); };
        recognition.onend = () => { recognition.start(); }; // Auto-restart

        recognition.start();
    }

    function sendRecordingNotif() {
        const formData = new FormData();
        formData.append('action', 'send_recording_notif');
        formData.append('id_formation', <?php echo $id_formation; ?>);
        formData.append('transcript_summary', fullTranscript.substring(0, 500)); // Send first part
        
        // We use fetch with keepalive to ensure it goes through even if page closes
        fetch('ajax_handler.php', { 
            method: 'POST', 
            body: formData,
            keepalive: true 
        });
    }

    <?php if ($role !== 'tuteur'): ?>
    async function initEdgeAI() {
        const video = document.getElementById('ai-video-feed');
        const canvas = document.getElementById('ai-canvas');
        const indicator = document.getElementById('emotion-indicator');
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        let lastEmotion = "neutre";

        const trad = {
            "neutral": "Concentré(e)",
            "happy": "Engagé(e)",
            "sad": "Ennuyé(e)",
            "angry": "Intrigué(e)",
            "fearful": "Confus(e)",
            "disgusted": "Rebuté(e)",
            "surprised": "Surpris(e)"
        };

        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
            ]);
            
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            
        } catch(err) {
            console.error("FaceAPI Webcam Error:", err);
            indicator.innerText = "IA Offline";
            indicator.style.color = "#ef4444";
        }

        video.addEventListener('play', () => {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(canvas, displaySize);
            
            indicator.innerText = "Analyse Active";
            
            // Détection toutes les 1 seconde
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections.length > 0) {
                    const exps = detections[0].expressions;
                    lastEmotion = Object.keys(exps).reduce((a, b) => exps[a] > exps[b] ? a : b);
                    indicator.innerText = trad[lastEmotion] || lastEmotion;

                    // Dessiner le rectangle de détection
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    faceapi.draw.drawDetections(canvas, resizedDetections);
                }
            }, 1000);

            // Envoi à la BDD toutes les 10 secondes
            setInterval(() => {
                if(lastEmotion) {
                    const formData = new FormData();
                    formData.append('action', 'save_emotion');
                    formData.append('id_candidat', <?php echo $id_user; ?>);
                    formData.append('id_formation', <?php echo $id_formation; ?>);
                    formData.append('emotion', lastEmotion);

                    fetch('ajax_handler.php', { method: 'POST', body: formData })
                    .catch(e => console.error(e));
                }
            }, 10000);
        });
    }
    <?php endif; ?>
    
    <?php if ($role === 'tuteur'): ?>
    function showClassEmotions() {
        if (typeof Swal === 'undefined') {
            alert('Veuillez patienter, chargement des outils IA...');
            return;
        }
        Swal.fire({
            title: false,
            html: `
                <div class="aptus-cockpit">
                    <!-- HEADER -->
                    <div class="cockpit-header">
                        <div class="cockpit-logo">
                            <div class="cockpit-orb-mini"></div>
                            <div>
                                <div class="cockpit-title">Bilan Cognitif</div>
                                <div class="cockpit-subtitle">Intelligence Aptus &mdash; Agent Llama 3</div>
                            </div>
                        </div>
                        <div class="cockpit-live-badge">
                            <span class="live-dot"></span> LIVE ANALYSIS
                        </div>
                    </div>

                    <!-- BODY: 2 colonnes -->
                    <div class="cockpit-body">
                        <!-- Colonne gauche : Chart -->
                        <div class="cockpit-chart-col">
                            <div class="chart-donut-wrap">
                                <canvas id="emotionsChart"></canvas>
                                <div class="donut-center">
                                    <div class="donut-score" id="engagementScore">—</div>
                                    <div class="donut-label">Engagement</div>
                                </div>
                            </div>
                            <div id="emotion-bars" class="emotion-bars"></div>
                        </div>

                        <!-- Colonne droite : Analyse IA -->
                        <div class="cockpit-analysis-col">
                            <div id="ai-recommandations" class="cockpit-ai-panel">
                                <div class="cockpit-loader">
                                    <div class="neural-ring"></div>
                                    <p>Synchronisation avec l&apos;Agent IA...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '860px',
            padding: 0,
            showConfirmButton: true,
            confirmButtonText: '✕ &nbsp;Fermer',
            confirmButtonColor: '#6366f1',
            background: 'transparent',
            backdrop: 'rgba(5, 5, 20, 0.85)',
            customClass: { 
                popup: 'swal-cockpit-popup',
                confirmButton: 'swal-cockpit-btn'
            },
            didOpen: () => {
                if (window.lucide) lucide.createIcons();

                const emotionColors = {
                    'neutral':   { color: '#6366f1', emoji: '😐' },
                    'happy':     { color: '#10b981', emoji: '😊' },
                    'sad':       { color: '#ef4444', emoji: '😢' },
                    'fearful':   { color: '#f59e0b', emoji: '😰' },
                    'angry':     { color: '#ec4899', emoji: '😤' },
                    'disgusted': { color: '#8b5cf6', emoji: '🤢' },
                    'surprised': { color: '#3b82f6', emoji: '😲' },
                    'Confusion': { color: '#f59e0b', emoji: '😕' },
                    'Triste':    { color: '#ef4444', emoji: '😢' },
                    'Heureux':   { color: '#10b981', emoji: '😊' }
                };
                
                const formData = new FormData();
                formData.append('action', 'get_emotion_stats');
                formData.append('id_candidat', 0);
                formData.append('id_formation', <?php echo $id_formation; ?>);

                fetch('ajax_handler.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.stats.length > 0) {
                        const labels = data.stats.map(s => s.emotion_detectee);
                        const values = data.stats.map(s => parseInt(s.count));
                        const total = values.reduce((a,b) => a+b, 0);

                        // Score d'engagement : % émotions positives
                        const positiveEmotions = ['happy', 'surprised', 'neutral', 'Heureux'];
                        let positiveCount = 0;
                        data.stats.forEach(s => {
                            if (positiveEmotions.includes(s.emotion_detectee)) positiveCount += parseInt(s.count);
                        });
                        const score = total > 0 ? Math.round((positiveCount / total) * 100) : 0;
                        document.getElementById('engagementScore').textContent = score + '%';
                        document.getElementById('engagementScore').style.color = score >= 60 ? '#10b981' : score >= 40 ? '#f59e0b' : '#ef4444';

                        // Barres d'émotions
                        let barsHtml = '';
                        data.stats.sort((a,b) => b.count - a.count).forEach(s => {
                            const pct = Math.round((s.count / total) * 100);
                            const info = emotionColors[s.emotion_detectee] || { color: '#94a3b8', emoji: '🔵' };
                            barsHtml += `
                                <div class="e-bar-row">
                                    <span class="e-bar-label">${info.emoji} ${s.emotion_detectee}</span>
                                    <div class="e-bar-track">
                                        <div class="e-bar-fill" style="width:${pct}%; background:${info.color};"></div>
                                    </div>
                                    <span class="e-bar-pct">${pct}%</span>
                                </div>
                            `;
                        });
                        document.getElementById('emotion-bars').innerHTML = barsHtml;

                        // Chart.js
                        const bgColors = labels.map(l => (emotionColors[l] || {color:'#94a3b8'}).color);
                        const ctx = document.getElementById('emotionsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: bgColors,
                                    borderWidth: 4,
                                    borderColor: 'rgba(255,255,255,0.05)',
                                    hoverOffset: 12
                                }]
                            },
                            options: { 
                                responsive: true, 
                                maintainAspectRatio: false,
                                cutout: '78%',
                                plugins: { legend: { display: false } }
                            }
                        });

                        askGroqRecommandations(data.stats);
                    } else {
                        document.getElementById('ai-recommandations').innerHTML = '<p style="text-align:center; padding:2rem; opacity:0.4; color:white;">Aucun flux émotionnel capturé.</p>';
                    }
                }).catch(err => {
                    console.error(err);
                    document.getElementById('ai-recommandations').innerHTML = '<p style="color:#ef4444;">Liaison IA interrompue.</p>';
                });
            }
        });
    }

    function askGroqRecommandations(stats) {
        const formData = new FormData();
        formData.append('action', 'analyze_student_emotions');
        formData.append('stats', JSON.stringify(stats));

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('ai-recommandations');
            if (data.success && data.data) {
                const info = data.data;
                const analysis = info.analyse_globale || info.analyseGlobale || "Analyse indisponible";
                const tips = info.conseils || info.tips || [];

                let html = `
                    <div class="cockpit-agent-badge">
                        ✦ AGENT PÉDAGOGIQUE ACTIF
                    </div>
                    <p class="cockpit-analysis-text">${analysis}</p>
                    <div class="cockpit-divider"></div>
                    <div class="cockpit-tips">
                `;
                tips.forEach((c, i) => {
                    html += `
                        <div class="cockpit-tip" style="animation-delay: ${i * 0.12}s">
                            <div class="cockpit-tip-num">${String(i+1).padStart(2,'0')}</div>
                            <p class="cockpit-tip-text">${c}</p>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            } else {
                container.innerHTML = `<p style="color:#ef4444; padding:1rem;">Erreur Groq : ${data.message || 'Vide'}</p>`;
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            document.getElementById('ai-recommandations').innerHTML = `<p style="color:#ef4444;">Liaison satellite interrompue.</p>`;
        });
    }
    <?php endif; ?>
</script>
<style>
    /* ═══════════════════════════════════════════════════════
       APTUS COCKPIT — Adaptive Dark / Light Theme
       ═══════════════════════════════════════════════════════ */

    /* ── DARK MODE (default for this modal) ── */
    .swal-cockpit-popup {
        border-radius: 24px !important;
        background: linear-gradient(145deg, #0f1121, #1a1d35) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        box-shadow: 0 0 0 1px rgba(99,102,241,0.15), 0 40px 80px rgba(0,0,0,0.6), 0 0 60px rgba(99,102,241,0.08) !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .swal-cockpit-btn {
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        letter-spacing: 0.5px !important;
        padding: 10px 28px !important;
        margin-top: 0 !important;
        margin-bottom: 1.5rem !important;
    }

    .swal2-actions {
        background: linear-gradient(145deg, #0f1121, #1a1d35) !important;
        margin-top: 0 !important;
        padding-top: 0.5rem !important;
    }

    /* ── LIGHT MODE OVERRIDES ── */
    [data-theme="light"] .swal-cockpit-popup {
        background: linear-gradient(145deg, #f8f9ff, #ffffff) !important;
        border: 1px solid rgba(99, 102, 241, 0.2) !important;
        box-shadow: 0 0 0 1px rgba(99,102,241,0.1), 0 40px 80px rgba(99,102,241,0.08) !important;
    }

    [data-theme="light"] .swal2-actions {
        background: linear-gradient(145deg, #f8f9ff, #ffffff) !important;
    }

    /* ── COCKPIT LAYOUT ── */
    .aptus-cockpit { font-family: 'Inter', sans-serif; }

    /* Dark text */
    .aptus-cockpit { color: white; }
    [data-theme="light"] .aptus-cockpit { color: #1e293b; }

    .cockpit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem 1.2rem;
        border-bottom: 1px solid rgba(99,102,241,0.2);
        background: rgba(99, 102, 241, 0.05);
    }

    [data-theme="light"] .cockpit-header {
        border-bottom: 1px solid rgba(99,102,241,0.12);
        background: rgba(99, 102, 241, 0.03);
    }

    .cockpit-logo { display: flex; align-items: center; gap: 12px; }

    .cockpit-orb-mini {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 50%;
        box-shadow: 0 0 15px rgba(99,102,241,0.5);
        animation: orb-pulse 2s infinite ease-in-out;
        flex-shrink: 0;
    }

    .cockpit-title {
        font-size: 1.1rem; font-weight: 800;
        background: linear-gradient(90deg, #a5b4fc, #e879f9);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    [data-theme="light"] .cockpit-title {
        background: linear-gradient(90deg, #4f46e5, #a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .cockpit-subtitle {
        font-size: 0.72rem; opacity: 0.45; letter-spacing: 0.5px; margin-top: 1px;
    }

    .cockpit-live-badge {
        display: flex; align-items: center; gap: 7px;
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #f87171;
        font-size: 0.65rem; font-weight: 800;
        padding: 5px 12px; border-radius: 50px;
        letter-spacing: 1px;
    }

    .live-dot {
        width: 7px; height: 7px;
        background: #ef4444; border-radius: 50%;
        animation: blink 1.2s infinite;
    }

    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }

    /* ── BODY GRID ── */
    .cockpit-body {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 0;
        min-height: 360px;
    }

    /* ── LEFT COLUMN ── */
    .cockpit-chart-col {
        padding: 1.5rem;
        border-right: 1px solid rgba(99,102,241,0.15);
        display: flex; flex-direction: column; gap: 1.2rem;
        background: rgba(0,0,0,0.15);
    }

    [data-theme="light"] .cockpit-chart-col {
        background: rgba(99, 102, 241, 0.02);
        border-right: 1px solid rgba(99,102,241,0.1);
    }

    .chart-donut-wrap {
        width: 180px; height: 180px;
        position: relative; margin: 0 auto;
    }

    .donut-center {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        text-align: center; pointer-events: none;
    }

    .donut-score { font-size: 1.8rem; font-weight: 900; line-height: 1; }

    .donut-label {
        font-size: 0.65rem; opacity: 0.45;
        text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;
    }

    /* Emotion bars */
    .emotion-bars { display: flex; flex-direction: column; gap: 8px; }
    .e-bar-row { display: flex; align-items: center; gap: 8px; }
    .e-bar-label { font-size: 0.72rem; font-weight: 600; width: 100px; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .e-bar-track { flex: 1; height: 5px; background: rgba(255,255,255,0.08); border-radius: 99px; overflow: hidden; }

    [data-theme="light"] .e-bar-track { background: rgba(0,0,0,0.07); }

    .e-bar-fill { height: 100%; border-radius: 99px; transition: width 1s cubic-bezier(.4,0,.2,1); }
    .e-bar-pct { font-size: 0.65rem; opacity: 0.5; width: 28px; text-align: right; font-weight: 700; }

    /* ── RIGHT COLUMN ── */
    .cockpit-analysis-col { padding: 1.5rem 1.75rem; overflow-y: auto; max-height: 400px; }
    .cockpit-ai-panel { height: 100%; }

    .cockpit-agent-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(90deg, rgba(99,102,241,0.25), rgba(168,85,247,0.15));
        border: 1px solid rgba(99,102,241,0.4);
        color: #a5b4fc;
        font-size: 0.6rem; font-weight: 800;
        padding: 4px 12px; border-radius: 50px;
        margin-bottom: 1rem; letter-spacing: 1px;
    }

    [data-theme="light"] .cockpit-agent-badge { color: #6366f1; }

    .cockpit-analysis-text {
        font-size: 0.88rem; line-height: 1.7;
        color: rgba(255,255,255,0.65);
        font-weight: 400; margin-bottom: 1.25rem;
    }

    [data-theme="light"] .cockpit-analysis-text { color: #475569; }

    .cockpit-divider {
        height: 1px;
        background: linear-gradient(90deg, rgba(99,102,241,0.5), transparent);
        margin-bottom: 1.25rem;
    }

    .cockpit-tips { display: flex; flex-direction: column; gap: 0.75rem; }

    .cockpit-tip {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 12px 14px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 12px;
        animation: tipSlide 0.4s ease both;
        transition: background 0.2s, transform 0.2s;
    }

    [data-theme="light"] .cockpit-tip {
        background: rgba(99,102,241,0.04);
        border: 1px solid rgba(99,102,241,0.1);
    }

    .cockpit-tip:hover { background: rgba(99,102,241,0.1); transform: translateX(4px); }

    @keyframes tipSlide {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cockpit-tip-num {
        font-size: 0.65rem; font-weight: 900;
        color: #6366f1; opacity: 0.9;
        letter-spacing: 1px; padding-top: 2px; flex-shrink: 0;
    }

    .cockpit-tip-text {
        font-size: 0.83rem; line-height: 1.55;
        color: rgba(255,255,255,0.78);
        font-weight: 500; margin: 0;
    }

    [data-theme="light"] .cockpit-tip-text { color: #334155; }

    /* ── LOADER ── */
    .cockpit-loader {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        height: 200px; gap: 1rem;
    }
    .cockpit-loader p { font-size: 0.85rem; opacity: 0.45; }

    .neural-ring {
        width: 48px; height: 48px; border-radius: 50%;
        border: 3px solid rgba(99,102,241,0.2);
        border-top-color: #6366f1;
        animation: spin 0.9s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    @keyframes orb-pulse {
        0%   { box-shadow: 0 0 10px rgba(99,102,241,0.4); }
        50%  { box-shadow: 0 0 25px rgba(99,102,241,0.7); }
        100% { box-shadow: 0 0 10px rgba(99,102,241,0.4); }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
