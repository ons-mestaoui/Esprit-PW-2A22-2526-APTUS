<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Salle de cours virtuelle - Aptus";

$room_url = $_GET['url'] ?? '';
$id_formation = $_GET['id_formation'] ?? 0;
// Fallback sur session ou 5
$id_user = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 5;
$role = strtolower($_SESSION['role'] ?? 'candidat'); // tuteur ou candidat

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

    #invisible-video {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 150px;
        height: auto;
        border-radius: 8px;
        border: 2px solid var(--accent-primary);
        background: #000;
        z-index: 10;
        pointer-events: none;
        opacity: 0.5; /* rendu semi-transparent pour ne pas gêner */
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
            <div style="color:var(--text-secondary);">
                <i data-lucide="shield" style="width:16px;height:16px;vertical-align:middle;"></i> Mode Tuteur (Analyse IA désactivée)
            </div>
        <?php endif; ?>
    </div>
    
    <div id="jitsi-meet-wrap"></div>
    
    <div id="stt-transcript-bar">
        <span style="opacity:0.6; font-size: 0.8rem; display:block; margin-bottom: 2px;">Transcription en direct (STT)</span>
        <span id="stt-text">En attente de parole...</span>
    </div>
    
    <?php if ($role !== 'tuteur'): ?>
        <!-- Webcam cachée dédiée à l'analyse FaceAPI (En plus de celle de Jitsi) -->
        <video id="invisible-video" autoplay muted playsinline></video>
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
        fetch('../../view/frontoffice/ajax_handler.php', { 
            method: 'POST', 
            body: formData,
            keepalive: true 
        });
    }

    <?php if ($role !== 'tuteur'): ?>
    async function initEdgeAI() {
        const video = document.getElementById('invisible-video');
        const indicator = document.getElementById('emotion-indicator');
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        let lastEmotion = "neutre";

        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
            ]);
            
            // Obtenir le flux de la webcam pour FaceAPI (peut parfois nécessiter l'autorisation même si Jitsi l'a)
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            
        } catch(err) {
            console.error("FaceAPI Webcam Error:", err);
            indicator.innerText = "Non autorisé";
            indicator.style.color = "#ef4444";
        }

        video.addEventListener('play', () => {
            indicator.innerText = "Analyse en cours";
            
            // Détection toutes les 1.5 secondes
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
                if (detections.length > 0) {
                    const exps = detections[0].expressions;
                    lastEmotion = Object.keys(exps).reduce((a, b) => exps[a] > exps[b] ? a : b);
                    indicator.innerText = lastEmotion;
                }
            }, 1500);

            // Envoi à la BDD toutes les 10 secondes
            setInterval(() => {
                if(lastEmotion) {
                    const formData = new FormData();
                    formData.append('action', 'save_emotion');
                    formData.append('id_candidat', <?php echo $id_user; ?>);
                    formData.append('id_formation', <?php echo $id_formation; ?>);
                    formData.append('emotion', lastEmotion);

                    // Re-utilisation du point d'entrée existant dans ajax_handler
                    fetch('ajax_handler.php', { method: 'POST', body: formData })
                    .catch(e => console.error(e));
                }
            }, 10000);
        });
    }
    <?php endif; ?>
</script>
