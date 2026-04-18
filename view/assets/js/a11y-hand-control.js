import { HandLandmarker, FilesetResolver } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.3/vision_bundle.mjs";

const toggleBtn = document.getElementById("a11y-toggle");
const videoContainer = document.getElementById("a11y-video-container");
const video = document.getElementById("a11y-webcam");
const canvas = document.getElementById("a11y-canvas");
const cursor = document.getElementById("a11y-cursor");

let handLandmarker = null;
let webcamRunning = false;
let lastVideoTime = -1;
let isModelLoaded = false;
let animationId = null;
let cursorAnimationId = null;

// Cursor Smoothing (Lerp)
let targetX = window.innerWidth / 2;
let targetY = window.innerHeight / 2;
let currentX = targetX;
let currentY = targetY;

// Gestures State
let isPinching = false;
let dragStartY = 0;
let scrollStartY = 0;
let hasDragged = false;
let pinchStartTime = 0;



// Initialize MediaPipe
async function initializeHandTracking() {
  toggleBtn.innerHTML = '<i class="lucide-loader animate-spin" data-lucide="loader-2"></i>';
  if(window.lucide) window.lucide.createIcons();

  try {
    const vision = await FilesetResolver.forVisionTasks(
      "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.3/wasm"
    );
    handLandmarker = await HandLandmarker.createFromOptions(vision, {
      baseOptions: {
        modelAssetPath: "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task",
        delegate: "GPU"
      },
      runningMode: "VIDEO",
      numHands: 1,
      minHandDetectionConfidence: 0.2, // Lower threshold = easier detection in bad lighting
      minHandPresenceConfidence: 0.2,
      minTrackingConfidence: 0.2
    });
    isModelLoaded = true;
    startWebcam();
  } catch (error) {
    console.error("Failed to load MediaPipe Hands:", error);
    alert("Impossible de charger le modèle d'Intelligence Artificielle pour le suivi des mains.");
    toggleBtn.innerHTML = '<i data-lucide="hand"></i>';
    if(window.lucide) window.lucide.createIcons();
  }
}

// Toggle Webcam
toggleBtn.addEventListener("click", () => {
  if (!isModelLoaded) {
    initializeHandTracking();
    return;
  }

  if (webcamRunning) {
    stopWebcam();
  } else {
    startWebcam();
  }
});

// Auto-start if previously active
window.addEventListener('DOMContentLoaded', () => {
  if (localStorage.getItem('a11y-active') === 'true') {
    initializeHandTracking();
  }
});

function startWebcam() {
  const constraints = { video: { facingMode: "user" } };
  localStorage.setItem('a11y-active', 'true');

  navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
    video.srcObject = stream;
    webcamRunning = true;
    toggleBtn.classList.add("active");
    toggleBtn.innerHTML = '<i data-lucide="hand" style="color:var(--bg-card);"></i>';
    if(window.lucide) window.lucide.createIcons();
    videoContainer.style.display = "block";
    cursor.style.display = "block";

    video.addEventListener("loadeddata", () => {
      predictWebcam();
      if (!cursorAnimationId) animateCursor();
    });
  }).catch((err) => {
    console.error("Camera access denied", err);
    alert("Veuillez autoriser l'accès à la caméra pour utiliser la navigation gestuelle.");
  });
}

function stopWebcam() {
  webcamRunning = false;
  localStorage.setItem('a11y-active', 'false');
  if(video.srcObject) {
    video.srcObject.getTracks().forEach(t => t.stop());
  }
  toggleBtn.classList.remove("active");
  toggleBtn.innerHTML = '<i data-lucide="hand"></i>';
  if(window.lucide) window.lucide.createIcons();
  videoContainer.style.display = "none";
  cursor.style.display = "none";
  if(animationId) cancelAnimationFrame(animationId);
  if(cursorAnimationId) {
    cancelAnimationFrame(cursorAnimationId);
    cursorAnimationId = null;
  }
}

// Main Loop
async function predictWebcam() {
  if (!webcamRunning) return;

  if (lastVideoTime !== video.currentTime && handLandmarker) {
    lastVideoTime = video.currentTime;
    // Process Frame
    const results = handLandmarker.detectForVideo(video, performance.now());
    processResults(results);
  }

  animationId = requestAnimationFrame(predictWebcam);
}

function processResults(results) {
  if (results.landmarks && results.landmarks.length > 0) {
    const landmarks = results.landmarks[0];
    
    // 1. Move Cursor (Index fingertip = landmark 8)
    const indexTip = landmarks[8];
    targetX = (1 - indexTip.x) * window.innerWidth;
    targetY = indexTip.y * window.innerHeight;

    // 2. Click Logic (Pinch to Drag or Click)
    const thumbTip = landmarks[4];
    const wrist = landmarks[0]; // Wrist used for stable scroll tracking
    const dist = Math.sqrt(Math.pow(thumbTip.x - indexTip.x, 2) + Math.pow(thumbTip.y - indexTip.y, 2));
    
    // Hysteresis: makes it harder to accidentally unpinch 
    const pinchThreshold = isPinching ? 0.07 : 0.05;

    if (dist < pinchThreshold) { 
      // Finger is pinched (Pressed)
      if (!isPinching) {
        // Just started pinching
        isPinching = true;
        pinchStartTime = Date.now();
        dragStartY = wrist.y * window.innerHeight; // Track wrist for stable dragging
        scrollStartY = window.scrollY;
        hasDragged = false;
        cursor.style.backgroundColor = 'var(--accent-tertiary)';
        cursor.style.borderColor = 'var(--accent-tertiary)';
      } else {
        // Holding pinch - dragging
        const wristY = wrist.y * window.innerHeight;
        const deltaY = dragStartY - wristY;
        const timeElapsed = Date.now() - pinchStartTime;
        
        // Drag threshold: movement > 40px, OR movement > 20px if held for longer than 300ms
        if (Math.abs(deltaY) > 40 || (timeElapsed > 300 && Math.abs(deltaY) > 20)) { 
          hasDragged = true;
          // deltaY is positive when hand moves UP. 
          // Hand moving up -> pull page up -> scroll down
          window.scrollTo({
            top: scrollStartY + (deltaY * 1.5), 
            behavior: 'instant' 
          });
        }
      }
    } else { 
      // Finger is open (Released)
      if (isPinching) {
        isPinching = false;
        cursor.style.backgroundColor = 'var(--accent-primary)';
        cursor.style.borderColor = 'white';
        
        // If we didn't drag enough, AND it was a relatively quick pinch (<1000ms), it's a solid click!
        if (!hasDragged && (Date.now() - pinchStartTime < 1000)) {
           performClick(currentX, currentY);
        }
      }
    }

  } else {
    // Hide cursor if hand lost
    cursor.style.opacity = '0';
  }
  
  if(results.landmarks && results.landmarks.length > 0) {
      cursor.style.opacity = '1';
  }
}

// 60FPS Cursor Animation Loop
function animateCursor() {
  if (!webcamRunning) return;
  
  // Linear Interpolation (smooths out the MediaPipe frame rate)
  currentX += (targetX - currentX) * 0.35;
  currentY += (targetY - currentY) * 0.35;
  
  // Hardware accelerated transform instead of left/top style updates
  const scale = isPinching ? 0.6 : 1;
  cursor.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translate(-50%, -50%) scale(${scale})`;
  
  cursorAnimationId = requestAnimationFrame(animateCursor);
}

function performClick(x, y) {
  // Hide cursor temporarily so we don't click on the cursor itself
  cursor.style.display = 'none';
  const element = document.elementFromPoint(x, y);
  cursor.style.display = 'block';

  if (element) {
    // Visual feedback
    element.style.transition = "transform 0.1s";
    element.style.transform = "scale(0.95)";
    setTimeout(() => { element.style.transform = ""; }, 150);

    // Handle Anchor links directly to bypass programmatic click restrictions in some browsers
    const anchor = element.tagName === 'A' ? element : element.closest('a');
    if (anchor && anchor.href) {
        anchor.click(); // Trigger any attached listeners
        window.location.href = anchor.href; // Force the navigation
        return;
    }

    // Simulate click for buttons or generic elements
    if (element.tagName === 'BUTTON' || element.onclick != null || getComputedStyle(element).cursor === 'pointer') {
         element.click();
    } else if (element.closest('button')) {
         element.closest('button').click();
    } else {
         // Generic synthetic event
         element.dispatchEvent(new MouseEvent('click', {
            view: window,
            bubbles: true,
            cancelable: true,
            clientX: x,
            clientY: y
          }));
    }
  }
}

