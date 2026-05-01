<?php
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();

$room_url = $_GET['url'] ?? '';
$id_formation = $_GET['id_formation'] ?? 0;
// Utilisation du SessionManager centralisé
$id_user = SessionManager::getUserId();
$role = strtolower($_GET['role'] ?? $_SESSION['role'] ?? 'candidat'); 

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
        <a href="formations_my.php" 
           style="display:flex; align-items:center; gap:6px; color:var(--text-secondary); text-decoration:none; font-size:0.82rem; font-weight:600; padding:4px 10px; border-radius:8px; border:1px solid var(--border-color); transition: all 0.2s;"
           onmouseover="this.style.background='var(--bg-surface)'; this.style.color='var(--text-primary)'"
           onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Mes Formations
        </a>

        <div style="font-size:0.85rem;">
            <i data-lucide="video" style="width:16px;height:16px;vertical-align:middle;"></i> 
            Salle Virtuelle : <b><?php echo htmlspecialchars($room_name); ?></b>
        </div>

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
        const domain = 'meet.jit.si';
        const options = {
            roomName: '<?php echo $room_name; ?>',
            parentNode: document.querySelector('#jitsi-meet-wrap'),
            configOverwrite: { startWithAudioMuted: true, startWithVideoMuted: false },
            interfaceConfigOverwrite: { SHOW_JITSI_WATERMARK: false },
            userInfo: { displayName: 'Aptus User' }
        };
        const api = new JitsiMeetExternalAPI(domain, options);

        initSTT();

        api.addEventListener('videoConferenceLeft', () => {
            
            // 🗄️ SÉCURITÉ 2 : Consolidation & Purge (Point 2)
            <?php if ($role === 'tuteur'): ?>
            const fd = new FormData();
            fd.append('action', 'consolidate_emotions');
            fd.append('id_formation', <?php echo $id_formation; ?>);
            navigator.sendBeacon('ajax_handler.php', fd);
            <?php endif; ?>

            // Fausse notification d'enregistrement SUPPRIMÉE
            window.location.href = "formations_my.php";
        });

        <?php if ($role !== 'tuteur'): ?>
        initEdgeAI();
        <?php endif; ?>
    });

    function initSTT() {
        if (!('webkitSpeechRecognition' in window) && !('speechRecognition' in window)) return;
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        const bar = document.getElementById('stt-transcript-bar');
        const sttText = document.getElementById('stt-text');
        
        let isStopped = false;

        recognition.lang = 'fr-FR';
        recognition.continuous = true;
        recognition.interimResults = true;
        
        recognition.onstart = () => { bar.style.display = 'block'; };
        
        recognition.onerror = (event) => {
            console.warn('STT Error:', event.error);
            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                isStopped = true;
            }
        };

        recognition.onresult = (event) => {
            let interimTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    fullTranscript += event.results[i][0].transcript + ". ";
                    sttText.innerHTML = `<span style="color:#10b981; font-weight:600;">${event.results[i][0].transcript}</span>`;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            if(interimTranscript) sttText.innerText = interimTranscript || "Écoute en cours...";
        };
        
        recognition.onend = () => { 
            if(!isStopped) {
                setTimeout(() => { try { recognition.start(); } catch(e){} }, 200);
            }
        };
        
        try { recognition.start(); } catch(e){}
    }

    // La fonction sendRecordingNotif() a été supprimée d'ici (faux enregistrement).

    <?php if ($role !== 'tuteur'): ?>
    async function initEdgeAI() {
        const video = document.getElementById('ai-video-feed');
        const canvas = document.getElementById('ai-canvas');
        const indicator = document.getElementById('emotion-indicator');
        
        // 🗺️ SÉCURITÉ 3 : Chemins Robustes (Point 3)
        const MODEL_URL = APTUS_BASE_URL + 'view/models/'; 
        let lastEmotion = "neutre";

        const trad = {
            "neutral": "Concentré(e)", "happy": "Engagé(e)", "sad": "Ennuyé(e)",
            "angry": "Intrigué(e)", "fearful": "Confus(e)", "disgusted": "Rebuté(e)", "surprised": "Surpris(e)"
        };

        indicator.innerText = "Initialisation IA...";
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
            ]);
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch(err) {
            console.warn("Échec Edge AI :", err);
            indicator.innerText = "Caméra désactivée";
            return;
        }

        video.addEventListener('play', () => {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(canvas, displaySize);
            let emotionBuffer = [];

            setInterval(async () => {
                if (!video.srcObject || video.paused || video.ended) return;
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections && detections.length > 0) {
                    const exps = detections[0].expressions;
                    lastEmotion = Object.keys(exps).reduce((a, b) => exps[a] > exps[b] ? a : b);
                    indicator.innerText = trad[lastEmotion] || lastEmotion;
                    emotionBuffer.push(lastEmotion);
                    faceapi.draw.drawDetections(canvas, faceapi.resizeResults(detections, displaySize));
                } else {
                    indicator.innerText = "Visage absent";
                }
            }, 1000);

            setInterval(() => {
                if(emotionBuffer.length > 0) {
                    const counts = {};
                    emotionBuffer.forEach(e => counts[e] = (counts[e] || 0) + 1);
                    const dominantEmotion = Object.keys(counts).reduce((a, b) => counts[a] > counts[b] ? a : b);
                    const formData = new FormData();
                    formData.append('action', 'save_emotion');
                    formData.append('id_candidat', <?php echo $id_user; ?>);
                    formData.append('id_formation', <?php echo $id_formation; ?>);
                    formData.append('emotion', dominantEmotion);
                    fetch('ajax_handler.php', { method: 'POST', body: formData });
                    emotionBuffer = [];
                }
            }, 30000); // ⏱️ Debouncing étudiant
        });
    }
    <?php endif; ?>
    
    <?php if ($role === 'tuteur'): ?>
    let cockpitInterval = null;

    function showClassEmotions() {
        Swal.fire({
            title: false,
            html: `
                <div class="aptus-cockpit">
                    <div class="cockpit-header">
                        <div class="cockpit-logo"><div class="cockpit-orb-mini"></div><div><div class="cockpit-title">Bilan Cognitif</div><div class="cockpit-subtitle">Intelligence Aptus &mdash; Agent Llama 3</div></div></div>
                        <div class="cockpit-live-badge"><span class="live-dot"></span> LIVE ANALYSIS</div>
                    </div>
                    <div class="cockpit-body">
                        <div class="cockpit-chart-col">
                            <div class="chart-donut-wrap">
                                <canvas id="emotionsChart"></canvas>
                                <div class="donut-center"><div class="donut-score" id="engagementScore">—</div><div class="donut-label">Engagement</div></div>
                            </div>
                            <div id="emotion-bars" class="emotion-bars"></div>
                        </div>
                        <div class="cockpit-analysis-col">
                            <div id="ai-recommandations" class="cockpit-ai-panel">
                                <div class="cockpit-loader"><div class="neural-ring"></div><p>Synchronisation avec l&apos;Agent IA...</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '860px', padding: 0, showConfirmButton: true, confirmButtonText: '✕ &nbsp;Fermer', confirmButtonColor: '#6366f1', background: 'transparent', backdrop: 'rgba(5, 5, 20, 0.85)',
            customClass: { popup: 'swal-cockpit-popup', confirmButton: 'swal-cockpit-btn' },
            didOpen: () => {
                if (window.lucide) lucide.createIcons();
                const refresh = () => {
                    const fd = new FormData();
                    fd.append('action', 'get_emotion_stats');
                    fd.append('id_formation', <?php echo $id_formation; ?>);
                    fetch('ajax_handler.php', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(data => { if(data.success) updateCockpitUI(data.stats); });
                };
                refresh();
                // 📡 SÉCURITÉ 1 : Polling 10s (Point 1)
                cockpitInterval = setInterval(refresh, 10000);
            },
            willClose: () => { clearInterval(cockpitInterval); }
        });
    }

    function updateCockpitUI(stats) {
        const emotionColors = {
            'neutral': '#6366f1', 'happy': '#10b981', 'sad': '#ef4444', 'fearful': '#f59e0b',
            'angry': '#ec4899', 'disgusted': '#8b5cf6', 'surprised': '#3b82f6', 'Heureux': '#10b981'
        };
        const total = stats.reduce((a,b) => a + parseInt(b.count), 0);
        const positiveEmotions = ['happy', 'surprised', 'neutral', 'Heureux'];
        let positiveCount = 0;
        stats.forEach(s => { if (positiveEmotions.includes(s.emotion_detectee)) positiveCount += parseInt(s.count); });
        const score = total > 0 ? Math.round((positiveCount / total) * 100) : 0;
        
        document.getElementById('engagementScore').textContent = score + '%';
        document.getElementById('engagementScore').style.color = score >= 60 ? '#10b981' : score >= 40 ? '#f59e0b' : '#ef4444';
        
        let barsHtml = '';
        stats.sort((a,b) => b.count - a.count).forEach(s => {
            const pct = Math.round((s.count / total) * 100);
            const color = emotionColors[s.emotion_detectee] || '#94a3b8';
            barsHtml += `<div class="e-bar-row"><span class="e-bar-label">${s.emotion_detectee}</span><div class="e-bar-track"><div class="e-bar-fill" style="width:${pct}%; background:${color};"></div></div><span class="e-bar-pct">${pct}%</span></div>`;
        });
        document.getElementById('emotion-bars').innerHTML = barsHtml;

        // On ne recrée pas le chart s'il existe déjà pour éviter le clignotement
        const ctx = document.getElementById('emotionsChart').getContext('2d');
        if (window.myChart) window.myChart.destroy();
        window.myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: stats.map(s => s.emotion_detectee),
                datasets: [{ data: stats.map(s => s.count), backgroundColor: stats.map(s => emotionColors[s.emotion_detectee] || '#94a3b8'), borderWidth: 0 }]
            },
            options: { cutout: '78%', plugins: { legend: { display: false } } }
        });

        askGroqRecommandations(stats);
    }

    function askGroqRecommandations(stats) {
        const fd = new FormData();
        fd.append('action', 'analyze_student_emotions');
        fd.append('stats', JSON.stringify(stats));
        fetch('ajax_handler.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('ai-recommandations');
            if(data.success && data.data) {
                const info = data.data;
                const tips = info.conseils || info.tips || [];
                let html = `<div class="cockpit-agent-badge">✦ AGENT PÉDAGOGIQUE ACTIF</div><p class="cockpit-analysis-text">${info.analyse_globale || info.analyseGlobale}</p><div class="cockpit-divider"></div><div class="cockpit-tips">`;
                tips.forEach((c, i) => {
                    html += `<div class="cockpit-tip"><div class="cockpit-tip-num">${String(i+1).padStart(2,'0')}</div><p class="cockpit-tip-text">${c}</p></div>`;
                });
                container.innerHTML = html + '</div>';
            }
        });
    }
    <?php endif; ?>
</script>

<style>
    .swal-cockpit-popup { border-radius: 24px !important; background: linear-gradient(145deg, #0f1121, #1a1d35) !important; border: 1px solid rgba(99, 102, 241, 0.3) !important; box-shadow: 0 40px 80px rgba(0,0,0,0.6) !important; padding: 0 !important; overflow: hidden !important; color: white !important; }
    .swal-cockpit-btn { border-radius: 10px !important; font-weight: 700 !important; padding: 10px 28px !important; margin-bottom: 1.5rem !important; }
    .aptus-cockpit { font-family: 'Inter', sans-serif; }
    .cockpit-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(99, 102, 241, 0.05); }
    .cockpit-orb-mini { width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 50%; animation: orb-pulse 2s infinite ease-in-out; }
    .cockpit-title { font-size: 1.1rem; font-weight: 800; background: linear-gradient(90deg, #a5b4fc, #e879f9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .cockpit-subtitle { font-size: 0.7rem; opacity: 0.5; }
    .cockpit-live-badge { display: flex; align-items: center; gap: 7px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; font-size: 0.65rem; font-weight: 800; padding: 4px 12px; border-radius: 50px; letter-spacing: 1px; }
    .live-dot { width: 6px; height: 6px; background: #ef4444; border-radius: 50%; animation: blink 1.2s infinite; }
    .cockpit-body { display: grid; grid-template-columns: 280px 1fr; min-height: 380px; }
    .cockpit-chart-col { padding: 1.5rem; border-right: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.2); }
    .chart-donut-wrap { width: 160px; height: 160px; position: relative; margin: 0 auto 1.5rem; }
    .donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
    .donut-score { font-size: 1.8rem; font-weight: 900; }
    .donut-label { font-size: 0.6rem; opacity: 0.4; text-transform: uppercase; letter-spacing: 1px; }
    .emotion-bars { display: flex; flex-direction: column; gap: 8px; }
    .e-bar-row { display: flex; align-items: center; gap: 8px; }
    .e-bar-label { font-size: 0.7rem; width: 85px; opacity: 0.8; }
    .e-bar-track { flex: 1; height: 4px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
    .e-bar-fill { height: 100%; transition: width 0.8s; }
    .e-bar-pct { font-size: 0.6rem; opacity: 0.5; width: 25px; text-align: right; }
    .cockpit-analysis-col { padding: 1.5rem 2rem; }
    .cockpit-agent-badge { display: inline-flex; background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.4); color: #a5b4fc; font-size: 0.6rem; font-weight: 800; padding: 4px 12px; border-radius: 50px; margin-bottom: 1rem; }
    .cockpit-analysis-text { font-size: 0.85rem; line-height: 1.6; color: rgba(255,255,255,0.7); }
    .cockpit-divider { height: 1px; background: linear-gradient(90deg, rgba(99,102,241,0.4), transparent); margin: 1.2rem 0; }
    .cockpit-tip { display: flex; gap: 12px; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 8px; }
    .cockpit-tip-num { color: #6366f1; font-weight: 900; font-size: 0.7rem; }
    .cockpit-tip-text { font-size: 0.8rem; margin: 0; color: rgba(255,255,255,0.8); }
    .neural-ring { width: 40px; height: 40px; border: 3px solid rgba(99,102,241,0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
    @keyframes orb-pulse { 0%,100%{box-shadow: 0 0 10px rgba(99,102,241,0.4);} 50%{box-shadow: 0 0 20px rgba(99,102,241,0.6);} }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
