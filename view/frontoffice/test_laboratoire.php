<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Laboratoire d'Analyse - Edge AI";

// ID candidat récupéré de la session ou de l'URL pour test
$id_candidat = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_GET['id_candidat'] ?? 5;
$id_formation = $_GET['id_formation'] ?? 1;

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    .lab-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: var(--bg-card);
        padding: 3rem 2rem;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        margin: 2rem auto;
        max-width: 800px;
    }
    
    .video-wrapper {
        position: relative;
        width: 100%;
        max-width: 640px;
        border-radius: 12px;
        overflow: hidden;
        border: 4px solid var(--border-color);
        background: #000;
        margin-top: 1.5rem;
    }

    #videoEle {
        width: 100%;
        height: auto;
        display: block;
    }

    canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .emotion-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1.2rem;
        margin-top: 1.5rem;
        text-transform: capitalize;
        transition: all 0.3s;
    }

    .loading-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        z-index: 10;
        border-radius: 12px;
    }
</style>

<div class="lab-container">
    <div style="text-align:center; margin-bottom: 1rem;">
        <h1 style="color: var(--text-primary); font-size: 2.2rem; margin-bottom: 0.5rem;">Laboratoire Edge AI 🔬</h1>
        <p style="color: var(--text-secondary);">Analyse continue de l'attention en cours via FaceAPI (Côté Navigateur)</p>
    </div>

    <div class="video-wrapper">
        <video id="videoEle" autoplay muted playsinline></video>
        <div id="loading" class="loading-overlay">
            <i data-lucide="loader-2" style="width: 40px; height: 40px; animation: spin 1s linear infinite;"></i>
            <p style="margin-top: 1rem; font-weight: 500;">Chargement des modèles Edge AI...</p>
        </div>
    </div>

    <div id="current-emotion" class="emotion-badge" style="display:none;">En attente de détection...</div>
    <p style="margin-top: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
        <i data-lucide="shield-check" style="width:16px;height:16px; vertical-align:middle;"></i> L'analyse se fait localement. Seule l'émotion textuelle est envoyée au serveur.
    </p>
</div>

<!-- Inclusion FaceAPI -->
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const video = document.getElementById('videoEle');
        const emotionBadge = document.getElementById('current-emotion');
        const loadingOver = document.getElementById('loading');
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        
        let lastEmotion = "neutre";

        // 1. Chargement des modèles
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
            ]);
            console.log("Modèles chargés.");
            startVideo();
        } catch (error) {
            console.error("Erreur de chargement des modèles :", error);
            loadingOver.innerHTML = `<span style="color:#ef4444;">Erreur: Impossible de charger les modèles AI.</span>`;
        }

        // 2. Démarrage Webcam
        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.error("Erreur d'accès à la webcam", err);
                    loadingOver.innerHTML = `<span style="color:#ef4444;">Erreur: Veuillez autoriser la webcam.</span>`;
                });
        }

        video.addEventListener('play', () => {
            loadingOver.style.display = 'none';
            emotionBadge.style.display = 'inline-block';

            // Créer le canvas par dessus la vidéo
            const canvas = faceapi.createCanvasFromMedia(video);
            document.querySelector('.video-wrapper').append(canvas);
            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            // Interval de Détection (toutes les 1 seconde)
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceExpressions();
                
                if (detections.length > 0) {
                    const expressions = detections[0].expressions;
                    
                    // Trouver l'expression max
                    let maxEmotion = Object.keys(expressions).reduce((a, b) => expressions[a] > expressions[b] ? a : b);
                    lastEmotion = maxEmotion;
                    
                    // Traduction basique
                    const trad = {
                        "neutral": "Concentré(e)",
                        "happy": "Heureux(se)",
                        "sad": "Ennuyé(e)",
                        "angry": "Intrigué(e)",
                        "fearful": "Confus(e)",
                        "disgusted": "Rebuté(e)",
                        "surprised": "Surpris(e)"
                    };
                    
                    emotionBadge.innerText = trad[maxEmotion] || maxEmotion;
                    
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    faceapi.draw.drawDetections(canvas, resizedDetections);
                } else {
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                }
            }, 1000);

            // Interval Envoi BDD (toutes les 10 secondes)
            setInterval(() => {
                if(lastEmotion) {
                    saveEmotionToDB(lastEmotion);
                }
            }, 10000);
        });

        // 3. Pont AJAX vers la DB
        function saveEmotionToDB(emotion) {
            const formData = new FormData();
            formData.append('id_candidat', <?php echo $id_candidat; ?>);
            formData.append('id_formation', <?php echo $id_formation; ?>);
            formData.append('emotion', emotion);

            fetch('../../controller/api_save_emotion.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log("Émotion sauvegardée :", data);
            })
            .catch(err => console.error("Erreur Backup:", err));
        }
    });
</script>
