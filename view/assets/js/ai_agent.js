class AIAgentWidget {
    constructor() {
        this.state = 'idle'; 
        // isActive: whether the AI is "on" (power button)
        this.isActive = sessionStorage.getItem('aiAgentActive') === 'true'; 
        // isVisible: whether the widget is shown at all (from settings)
        this.isVisible = localStorage.getItem('aiAgentVisible') === 'true';
        
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.audioController = null;
        this.micStream = null;

        // Draggable State
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;
        
        // Initial Position Logic with strict bounds
        let savedX = parseInt(localStorage.getItem('aiAgentX'));
        let savedY = parseInt(localStorage.getItem('aiAgentY'));
        
        if (isNaN(savedX)) savedX = window.innerWidth - 88;
        if (isNaN(savedY)) savedY = window.innerHeight - 88;
        
        // Force bounds within current window size (fixes out-of-screen bug)
        this.widgetX = Math.max(24, Math.min(savedX, window.innerWidth - 88));
        this.widgetY = Math.max(24, Math.min(savedY, window.innerHeight - 88));

        this.initDOM();
        this.bindEvents();
        this.updatePowerState(true);
        this.applyPosition();
        this.updateVisibility();
    }

    initDOM() {
        if (!document.getElementById('ai-agent-widget')) {
            const widgetHTML = `
                <div id="ai-agent-widget" class="state-idle power-off dock-right">
                    <div class="ai-controls">
                        <span class="ai-status-text" id="ai-status-text">Désactivé</span>
                        <input type="text" id="ai-text-input" placeholder="Tapez ici..." style="border:1px solid #e5e7eb; border-radius:12px; padding:4px 8px; font-size:12px; width:120px; outline:none; display:none;">
                        <button class="ai-btn" id="ai-ptt-btn" title="Maintenir pour parler">
                            <i data-lucide="mic"></i>
                        </button>
                        <button class="ai-btn success" id="ai-power-btn" title="Démarrer/Arrêter">
                            <i data-lucide="power"></i>
                        </button>
                    </div>
                    <div class="ai-face-container" id="ai-face-trigger" title="Faites glisser pour déplacer">
                        <div class="ai-face">
                            <div class="ai-eye left"></div>
                            <div class="ai-eye right"></div>
                            <div class="ai-mouth"></div>
                        </div>
                        <div class="listening-indicator">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', widgetHTML);

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        this.widgetEl = document.getElementById('ai-agent-widget');
        this.statusTextEl = document.getElementById('ai-status-text');
        this.textInputEl = document.getElementById('ai-text-input');
        this.pttBtn = document.getElementById('ai-ptt-btn');
        this.powerBtn = document.getElementById('ai-power-btn');
        this.faceTrigger = document.getElementById('ai-face-trigger');
    }

    updateVisibility() {
        if (this.isVisible) {
            this.widgetEl.classList.remove('hidden');
        } else {
            this.widgetEl.classList.add('hidden');
        }
    }

    // Toggle visibility from external call (settings)
    toggleVisibility(show) {
        this.isVisible = show;
        localStorage.setItem('aiAgentVisible', show);
        this.updateVisibility();
    }

    updatePowerState(isSilentStart = false) {
        sessionStorage.setItem('aiAgentActive', this.isActive);

        if (this.isActive) {
            this.widgetEl.classList.remove('power-off');
            this.powerBtn.classList.remove('success');
            this.powerBtn.classList.add('danger');
            this.textInputEl.style.display = 'block';

            this.setState('idle', 'Prêt');
            if (!isSilentStart) {
                this.playTTS("Bonjour ! Je suis en ligne.");
            }

            navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                this.micStream = stream;
            }).catch(err => {
                console.error("Mic access denied:", err);
                this.setState('idle', 'Micro bloqué');
            });

        } else {
            this.widgetEl.classList.add('power-off');
            this.powerBtn.classList.remove('danger');
            this.powerBtn.classList.add('success');
            this.textInputEl.style.display = 'none';
            this.stopAll();

            if (this.micStream) {
                this.micStream.getTracks().forEach(track => track.stop());
                this.micStream = null;
            }

            this.setState('idle', 'Désactivé');
        }
    }

    applyPosition() {
        this.widgetEl.style.top = `${this.widgetY}px`;
        this.widgetEl.style.bottom = 'auto';

        const centerX = window.innerWidth / 2;
        if (this.widgetX + 32 > centerX) {
            this.widgetEl.classList.add('dock-right');
            this.widgetEl.classList.remove('dock-left');
            this.widgetEl.style.left = 'auto';
            this.widgetEl.style.right = `${window.innerWidth - this.widgetX - 64}px`;
        } else {
            this.widgetEl.classList.add('dock-left');
            this.widgetEl.classList.remove('dock-right');
            this.widgetEl.style.right = 'auto';
            this.widgetEl.style.left = `${this.widgetX}px`;
        }
    }

    bindEvents() {
        // Drag and Drop
        this.faceTrigger.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // Left click only
            this.isDragging = true;
            this.dragStartX = e.clientX - this.widgetX;
            this.dragStartY = e.clientY - this.widgetY;
            this.widgetEl.classList.add('is-dragging');
            e.preventDefault();
        });

        // Ensure resizing window doesn't leave widget offscreen
        window.addEventListener('resize', () => {
            this.widgetX = Math.max(24, Math.min(this.widgetX, window.innerWidth - 88));
            this.widgetY = Math.max(24, Math.min(this.widgetY, window.innerHeight - 88));
            this.applyPosition();
        });

        window.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            this.widgetX = e.clientX - this.dragStartX;
            this.widgetY = e.clientY - this.dragStartY;

            // Bounds during drag
            this.widgetX = Math.max(0, Math.min(this.widgetX, window.innerWidth - 64));
            this.widgetY = Math.max(0, Math.min(this.widgetY, window.innerHeight - 64));

            this.applyPosition();
        });

        window.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                this.widgetEl.classList.remove('is-dragging');

                // Snap to sides (Magnetic)
                if (this.widgetX < window.innerWidth / 2) {
                    this.widgetX = 24; // Left margin
                } else {
                    this.widgetX = window.innerWidth - 88; // Right margin (88 = 64px width + 24px padding)
                }

                localStorage.setItem('aiAgentX', this.widgetX);
                localStorage.setItem('aiAgentY', this.widgetY);
                this.applyPosition();
            }
        });

        // PTT Events
        const startPtt = (e) => {
            if (e) e.preventDefault();
            if (!this.isActive || this.isDragging) return;
            this.pttBtn.classList.add('active-ptt');
            this.startRecording();
        };

        const stopPtt = (e) => {
            if (!this.isActive) return;
            this.pttBtn.classList.remove('active-ptt');
            if (this.state === 'listening') {
                this.stopRecording();
            }
        };

        this.pttBtn.addEventListener('mousedown', startPtt);
        window.addEventListener('mouseup', stopPtt);

        this.powerBtn.addEventListener('click', () => {
            this.isActive = !this.isActive;
            this.updatePowerState(false);
        });

        this.textInputEl.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && this.textInputEl.value.trim() !== '') {
                const text = this.textInputEl.value.trim();
                this.textInputEl.value = '';
                this.setState('loading', 'Envoi...');
                this.processUserRequest(text);
            }
        });

        // Listen for external toggle from settings
        window.addEventListener('toggleAIAgent', (e) => {
            this.toggleVisibility(e.detail.show);
        });
    }

    setState(newState, statusText = '') {
        this.widgetEl.classList.remove(`state-${this.state}`);
        this.state = newState;
        this.widgetEl.classList.add(`state-${this.state}`);
        if (statusText) this.statusTextEl.innerText = statusText;
    }

    async playTTS(text) {
        this.setState('speaking', 'Parle...');
        this.stopAudio();
        try {
            this.audioController = new AbortController();
            const response = await fetch('/aptus_first_official_version/controller/tts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text }),
                signal: this.audioController.signal
            });
            if (!response.ok) throw new Error("TTS server error");
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            this.audioElement = new Audio(url);
            this.audioElement.volume = 0.4;
            return new Promise((resolve) => {
                this.audioElement.onended = () => {
                    URL.revokeObjectURL(url);
                    if(this.state === 'speaking') this.setState('idle', 'Prêt');
                    resolve();
                };
                this.audioElement.play().catch(e => resolve());
            });
        } catch (error) {
            if (error.name !== 'AbortError') this.setState('idle', 'Prêt');
        }
    }

    async startRecording() {
        if (!this.micStream) return;
        this.stopAudio();
        this.audioChunks = [];
        const mimeType = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/mp4';
        this.mediaRecorder = new MediaRecorder(this.micStream, { mimeType });
        this.mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) this.audioChunks.push(e.data); };
        this.mediaRecorder.onstop = () => {
            this.setState('loading', 'Analyse...');
            let finalMime = this.mediaRecorder.mimeType.split(';')[0];
            if (!finalMime) finalMime = 'audio/webm';
            const audioBlob = new Blob(this.audioChunks, { type: finalMime });
            this.processAudioRequest(audioBlob, finalMime);
        };
        this.mediaRecorder.start();
        this.setState('listening', 'Écoute...');
    }

    stopRecording() { if (this.mediaRecorder && this.mediaRecorder.state === 'recording') this.mediaRecorder.stop(); }
    stopAudio() {
        if (this.audioController) { this.audioController.abort(); this.audioController = null; }
        if (this.audioElement) { this.audioElement.pause(); this.audioElement = null; }
    }
    stopAll() { this.stopRecording(); this.stopAudio(); this.setState('idle', 'Arrêté'); }

    async processUserRequest(text) {
        try {
            const response = await fetch('/aptus_first_official_version/controller/AgentController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text })
            });
            this.handleBackendResponse(response);
        } catch (e) {
            this.setState('idle', 'Erreur réseau');
        }
    }

    async processAudioRequest(blob, mimeType) {
        const reader = new FileReader();
        reader.onloadend = async () => {
            const base64Audio = reader.result.split(',')[1];
            try {
                const response = await fetch('/aptus_first_official_version/controller/AgentController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ audio: base64Audio, mimeType: mimeType })
                });
                this.handleBackendResponse(response);
            } catch (e) {
                this.setState('idle', 'Erreur réseau');
            }
        };
        reader.readAsDataURL(blob);
    }

    async handleBackendResponse(response) {
        if (!response.ok) { this.setState('idle', 'Erreur'); return; }
        const data = await response.json();

        if (data.error) {
            alert("Erreur API Gemini: " + JSON.stringify(data.error));
            this.setState('idle', 'Erreur API');
            return;
        }

        if (data.spoken_text) await this.playTTS(data.spoken_text);
        if (data.action) this.executeAction(data.action);
        else this.setState('idle', 'Prêt');
    }

    executeAction(action) {
        if (action.type === 'navigate') window.location.href = action.target;
        else if (action.type === 'script') eval(action.code);
    }
}

document.addEventListener('DOMContentLoaded', () => { window.AIAssistant = new AIAgentWidget(); });