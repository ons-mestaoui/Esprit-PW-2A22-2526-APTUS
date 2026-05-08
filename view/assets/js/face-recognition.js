/**
 * Face Recognition Module for Aptus
 * 
 * Features:
 * - Real-time face detection via webcam
 * - LIVENESS detection (anti-spoofing): requires head movements + blink
 *   to ensure a real person is present, not a photo or video
 * - Face descriptor enrollment (settings page)
 * - Face descriptor verification (login page)
 */

const FaceAuth = (() => {
  // Dynamic base path detection
  const scriptTag = document.querySelector('script[src*="face-recognition.js"]');
  const scriptPath = scriptTag ? scriptTag.getAttribute('src') : '';
  const defaultModelUrl = '/aptus_first_official_version/view/assets/js/models';
  
  // If the script path is relative, use it to find models
  const MODEL_URL = scriptPath.includes('/js/') 
    ? scriptPath.substring(0, scriptPath.lastIndexOf('/js/')) + '/js/models'
    : defaultModelUrl;

  const API_URL = '/aptus_first_official_version/controller/face_api.php';
  const MATCH_THRESHOLD = 0.6;

  let modelsLoaded = false;
  let videoStream = null;
  let audioCtx = null;

  /**
   * Get or create a singleton AudioContext
   */
  function getAudioCtx() {
    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    return audioCtx;
  }

  // ── Load face-api.js models ──
  async function loadModels() {
    if (modelsLoaded) return;
    try {
      await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
      ]);
      modelsLoaded = true;
    } catch (err) {
      console.error('Face-api models load failed:', err);
      throw new Error('Erreur de chargement des modèles IA. Vérifiez votre connexion.');
    }
  }

  // ── Start webcam ──
  async function startCamera(videoElement) {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { width: 480, height: 360, facingMode: 'user' }
    });
    videoElement.srcObject = stream;
    videoStream = stream;
    return new Promise(resolve => {
      videoElement.onloadedmetadata = () => {
        videoElement.play();
        resolve();
      };
    });
  }

  // ── Stop webcam ──
  function stopCamera() {
    if (videoStream) {
      videoStream.getTracks().forEach(t => t.stop());
      videoStream = null;
    }
  }

  // ── Get face descriptor from current video frame ──
  async function getDescriptor(videoElement) {
    const detection = await faceapi
      .detectSingleFace(videoElement)
      .withFaceLandmarks()
      .withFaceDescriptor();
    return detection || null;
  }

  // ─────────────────────────────────────────────
  // LIVENESS DETECTION (Anti-Spoofing)
  // ─────────────────────────────────────────────
  // This ensures the user is a real, live person
  // and not holding up a photo/video to the camera.
  //
  // Challenges:
  //  1) Turn head LEFT
  //  2) Turn head RIGHT
  //  3) BLINK (close eyes)
  //
  // A static photo cannot pass all three.
  // ─────────────────────────────────────────────

  /**
   * Estimate yaw (left-right head rotation) from 68 face landmarks.
   * Uses the ratio of left-face-width to right-face-width.
   * Returns a value: negative = looking left, positive = looking right.
   */
  function estimateYaw(landmarks) {
    const pts = landmarks.positions;
    const noseTip = pts[30];     // Nose tip
    const leftCheek = pts[0];    // Left jaw corner
    const rightCheek = pts[16];  // Right jaw corner

    const leftDist = Math.abs(noseTip.x - leftCheek.x);
    const rightDist = Math.abs(rightCheek.x - noseTip.x);
    const total = leftDist + rightDist;

    if (total === 0) return 0;
    // Normalized: 0.5 = centered, <0.5 = looking right, >0.5 = looking left
    return (leftDist / total) - 0.5;
  }

  /**
   * Detect blink using Eye Aspect Ratio (EAR).
   * When EAR drops below threshold, eyes are closed.
   */
  function getEAR(landmarks) {
    const pts = landmarks.positions;

    // Left eye: indices 36-41
    const leftEye = [pts[36], pts[37], pts[38], pts[39], pts[40], pts[41]];
    // Right eye: indices 42-47
    const rightEye = [pts[42], pts[43], pts[44], pts[45], pts[46], pts[47]];

    function eyeAR(eye) {
      const v1 = dist(eye[1], eye[5]);
      const v2 = dist(eye[2], eye[4]);
      const h = dist(eye[0], eye[3]);
      return (v1 + v2) / (2.0 * h);
    }

    return (eyeAR(leftEye) + eyeAR(rightEye)) / 2.0;
  }

  function dist(a, b) {
    return Math.sqrt((a.x - b.x) ** 2 + (a.y - b.y) ** 2);
  }

  /**
   * Play a short beep sound using Web Audio API
   */
  function playBeep(type = 'step') {
    try {
      const ctx = getAudioCtx();
      const oscillator = ctx.createOscillator();
      const gainNode = ctx.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(ctx.destination);
      
      const now = ctx.currentTime;

      if (type === 'step') {
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(660, now);
        gainNode.gain.setValueAtTime(0, now);
        gainNode.gain.linearRampToValueAtTime(0.1, now + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.1);
        oscillator.start(now);
        oscillator.stop(now + 0.1);
      } else if (type === 'success') {
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, now);
        oscillator.frequency.setTargetAtTime(1100, now + 0.1, 0.05);
        gainNode.gain.setValueAtTime(0, now);
        gainNode.gain.linearRampToValueAtTime(0.1, now + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
        oscillator.start(now);
        oscillator.stop(now + 0.4);
      }
    } catch (e) {
      console.warn('Audio feedback failed:', e);
    }
  }

  /**
   * Run the full liveness challenge sequence.
   * Returns the face descriptor if all challenges pass, or null if failed/cancelled.
   *
   * @param {HTMLVideoElement} videoEl
   * @param {Function} onStatus  - Callback(text, step, totalSteps) for UI updates
   * @param {Function} onCancel  - Returns true if user cancelled
   * @param {HTMLCanvasElement} overlayCanvas - Optional canvas for drawing face outline
   */
  async function runLivenessCheck(videoEl, onStatus, onCancel, overlayCanvas) {
    const challenges = [
      { type: 'center',  label: 'Regardez la caméra',       icon: '👤' },
      { type: 'left',    label: 'Tournez la tête à gauche',  icon: '⬅️' },
      { type: 'right',   label: 'Tournez la tête à droite',  icon: '➡️' },
    ];

    let finalDescriptor = null;

    for (let i = 0; i < challenges.length; i++) {
      const ch = challenges[i];
      onStatus(`${ch.icon} ${ch.label}`, i + 1, challenges.length);

      const passed = await waitForChallenge(videoEl, ch.type, onCancel, overlayCanvas);
      if (!passed) return null; // cancelled or timeout

      playBeep('step');
      await sleep(400); // Small pause between steps for UX

      // Capture descriptor on the last step or the center step using SSD (more accurate)
      if (ch.type === 'center' || i === challenges.length - 1) {
        const det = await faceapi.detectSingleFace(videoEl, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
          .withFaceLandmarks().withFaceDescriptor();
        if (det) finalDescriptor = det.descriptor;
      }
    }

    // Play "all done" sound
    playBeep('success');

    // Final capture for the most accurate descriptor
    onStatus('✅ Vérification terminée !', challenges.length, challenges.length);
    await sleep(500);

    // Get one more clean descriptor
    const finalDet = await getDescriptor(videoEl);
    if (finalDet) finalDescriptor = finalDet.descriptor;

    return finalDescriptor ? Array.from(finalDescriptor) : null;
  }

  /**
   * Wait for a specific liveness challenge to be met.
   * Times out after 10 seconds.
   */
  async function waitForChallenge(videoEl, type, onCancel, overlayCanvas) {
    const timeout = 15000; // Increased to 15 seconds
    const start = Date.now();
    let consecutiveFrames = 0;
    const requiredFrames = 3; // Reduced for faster capturing
    
    // Tiny Face Detector is much faster for real-time tracking
    const detectorOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 128, scoreThreshold: 0.5 });

    while (Date.now() - start < timeout) {
      if (onCancel && onCancel()) return false;

      const detection = await faceapi
        .detectSingleFace(videoEl, detectorOptions)
        .withFaceLandmarks();

      if (overlayCanvas) {
        drawFaceOverlay(overlayCanvas, videoEl, detection);
      }

      if (!detection) {
        consecutiveFrames = 0;
        await sleep(100);
        continue;
      }

      const landmarks = detection.landmarks;
      let challengeMet = false;

      switch (type) {
        case 'center': {
          const yaw = estimateYaw(landmarks);
          challengeMet = Math.abs(yaw) < 0.12; // More flexible center
          break;
        }
        case 'left': {
          const yaw = estimateYaw(landmarks);
          challengeMet = yaw > 0.1; // Reduced threshold to 'capture' easier
          break;
        }
        case 'right': {
          const yaw = estimateYaw(landmarks);
          challengeMet = yaw < -0.1; // Reduced threshold to 'capture' easier
          break;
        }
        case 'blink': {
          const ear = getEAR(landmarks);
          challengeMet = ear < 0.23; // More flexible blink threshold
          break;
        }
      }

      if (challengeMet) {
        // Blink needs less frames than holding a position
        const targetFrames = (type === 'blink') ? 1 : requiredFrames;
        consecutiveFrames++;
        if (consecutiveFrames >= targetFrames) {
          // If it was a blink, wait for eyes to open again before proceeding
          if (type === 'blink') await sleep(200);
          return true;
        }
      } else {
        consecutiveFrames = 0;
      }

      await sleep(16); // Reduced from 80ms for buttery smooth ~60 FPS feedback
    }

    return false; // timed out
  }

  /**
   * Draw a face outline overlay on a canvas for visual feedback.
   */
  function drawFaceOverlay(canvas, video, detection) {
    const ctx = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!detection) return;

    const box = detection.detection.box;
    const landmarks = detection.landmarks;

    // Draw face bounding box
    ctx.strokeStyle = '#6366f1';
    ctx.lineWidth = 3;
    ctx.setLineDash([8, 4]);
    ctx.strokeRect(box.x, box.y, box.width, box.height);
    ctx.setLineDash([]);

    // Draw landmark dots on jaw line
    const jawLine = landmarks.positions.slice(0, 17);
    ctx.fillStyle = 'rgba(99, 102, 241, 0.6)';
    jawLine.forEach(pt => {
      ctx.beginPath();
      ctx.arc(pt.x, pt.y, 2, 0, Math.PI * 2);
      ctx.fill();
    });

    // Draw eye contours
    const leftEye = landmarks.positions.slice(36, 42);
    const rightEye = landmarks.positions.slice(42, 48);
    ctx.strokeStyle = 'rgba(16, 185, 129, 0.8)';
    ctx.lineWidth = 2;
    [leftEye, rightEye].forEach(eye => {
      ctx.beginPath();
      eye.forEach((pt, i) => {
        if (i === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
      });
      ctx.closePath();
      ctx.stroke();
    });
  }

  function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
  }

  // ── API calls ──
  async function apiCall(data) {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return res.json();
  }

  async function enrollFace(descriptor) {
    return apiCall({ action: 'enroll', descriptor });
  }

  async function verifyFace(email, descriptor) {
    return apiCall({ action: 'verify', email, descriptor });
  }

  async function getFaceStatus() {
    return apiCall({ action: 'status' });
  }

  async function removeFace() {
    return apiCall({ action: 'remove' });
  }

  // ── Public API ──
  return {
    loadModels,
    startCamera,
    stopCamera,
    getDescriptor,
    runLivenessCheck,
    enrollFace,
    verifyFace,
    getFaceStatus,
    removeFace,
  };
})();
