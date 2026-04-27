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
        <div>
            <i data-lucide="video" style="width:16px;height:16px;vertical-align:middle;"></i> Salle Virtuelle : <b><?php echo htmlspecialchars($room_name); ?></b>
        </div>
        <?php if ($role !== 'tuteur'): ?>
            <div title="Analyse cognitive IA en Edge Computing">
                <i data-lucide="brain-circuit" style="width:16px;height:16px;vertical-align:middle;color:var(--accent-primary);"></i> 
                Agent Aptus actif — État : <span id="emotion-indicator">Chargement...</span>
            </div>
        <?php else: ?>
            <div style="color:var(--text-secondary); display:flex; align-items:center; gap:1rem;">
                <div><i data-lucide="shield" style="width:16px;height:16px;vertical-align:middle;"></i> Mode Tuteur</div>
                <button onclick="showClassEmotions()" class="btn btn-sm" style="background:var(--accent-primary); color:white; border:none; padding:4px 12px; border-radius:6px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
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
            title: `Bilan IA - Classe Entière`,
            html: `
                <div style="display:flex; flex-direction:column; align-items:center; gap: 1.5rem;">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="emotionsChart"></canvas>
                    </div>
                    <div id="ai-recommandations" style="text-align: left; width: 100%; min-height: 100px; background: var(--bg-surface); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <div style="text-align:center; color: var(--text-secondary);">
                            <i data-lucide="loader-2" style="width: 20px; height: 20px; animation: spin 1s linear infinite;"></i>
                            <p>Analyse cognitive globale en cours (Agent Llama 3)...</p>
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
            showConfirmButton: true,
            confirmButtonText: 'Fermer',
            confirmButtonColor: '#3b82f6',
            background: 'var(--bg-card)',
            color: 'var(--text-primary)',
            customClass: { popup: 'swal-ai-custom' },
            didOpen: () => {
                if (window.lucide) lucide.createIcons();
                // 1. Fetch data for whole class (id_candidat = 0)
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

                        // 2. Draw Chart
                        const ctx = document.getElementById('emotionsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6366f1', '#64748b'],
                                    borderWidth: 0
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false }
                        });

                        // 3. Ask Groq
                        askGroqRecommandations(data.stats);
                    } else {
                        document.getElementById('ai-recommandations').innerHTML = '<p style="text-align:center;">Aucune donnée émotionnelle enregistrée pour la classe (les caméras sont peut-être désactivées).</p>';
                    }
                }).catch(err => {
                    console.error(err);
                    document.getElementById('ai-recommandations').innerHTML = '<p style="color:#ef4444;">Erreur serveur.</p>';
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
                    <h4 style="color: var(--accent-primary); margin-bottom: 0.5rem; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="brain-circuit" style="width:18px;height:18px;"></i> Agent Pédagogique Aptus
                    </h4>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">${analysis}</p>
                    <ul style="padding-left: 1.2rem; font-size: 0.9rem; font-weight: 500; color: var(--text-primary);">
                `;
                tips.forEach(c => {
                    html += `<li style="margin-bottom: 0.5rem;">${c}</li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            } else {
                container.innerHTML = `<p style="color:#ef4444;">Erreur de l'Agent IA : ${data.message || 'Réponse invalide'}</p>`;
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            document.getElementById('ai-recommandations').innerHTML = `<p style="color:#ef4444;">Erreur réseau IA.</p>`;
        });
    }
    <?php endif; ?>
</script>
<style>
    .swal-ai-custom {
        border-radius: 20px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid var(--border-color) !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
