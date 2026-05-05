<?php
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';
$vc = new VeilleC();
$stats = $vc->getRegionalMarketStats();
$reports = $vc->afficherRapports();

// Aggregate sector data for the cubes
$secteurData = [];
foreach ($reports as $r) {
    if (!empty($r['secteur_principal'])) {
        $tags = explode(',', $r['secteur_principal']);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!$tag) continue;
            if (!isset($secteurData[$tag])) {
                $secteurData[$tag] = [
                    'count' => 0, 
                    'salaries' => [],
                    'demande' => $r['niveau_demande_global']
                ];
            }
            $secteurData[$tag]['count']++;
            if ($r['salaire_moyen_global'] > 0) {
                $secteurData[$tag]['salaries'][] = $r['salaire_moyen_global'];
            }
        }
    }
}
$topSectors = array_slice($secteurData, 0, 6);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aptus AR - Command Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
    <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { margin: 0; overflow: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Start Overlay */
        #start-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.95); z-index: 9999; display: flex;
            flex-direction: column; justify-content: center; align-items: center; color: white;
            transition: opacity 0.5s ease;
        }
        .btn-start {
            background: #6366f1; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold;
            color: white; border-radius: 30px; cursor: pointer; box-shadow: 0 10px 25px rgba(99,102,241,0.5);
            margin-top: 20px;
        }

        /* UI Overlay */
        #ar-ui-container {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 100; display: flex; flex-direction: column; justify-content: space-between;
        }
        
        .header-panel {
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px);
            color: white; padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center; pointer-events: auto;
        }
        
        .header-title { margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .header-subtitle { margin: 2px 0 0 0; font-size: 11px; color: #94a3b8; }

        .controls-panel { display: flex; gap: 10px; }

        .btn-ui {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: white; border-radius: 8px; padding: 8px 12px; font-size: 14px;
            cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;
        }
        .btn-ui:hover, .btn-ui:active { background: rgba(99, 102, 241, 0.6); }

        .footer-panel {
            padding: 20px; display: flex; justify-content: center; align-items: flex-end; pointer-events: auto;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        }

        #ptt-btn {
            width: 70px; height: 70px; border-radius: 50%; border: none;
            background: #6366f1; color: white; box-shadow: 0 4px 15px rgba(99,102,241,0.5);
            display: flex; justify-content: center; align-items: center; cursor: pointer;
            transition: all 0.2s ease;
        }
        #ptt-btn.recording { background: #ef4444; box-shadow: 0 0 25px #ef4444; transform: scale(1.1); animation: pulse-ring 1s infinite; }
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        #ai-status {
            position: absolute; bottom: 100px; left: 50%; transform: translateX(-50%);
            background: rgba(0,0,0,0.7); color: white; padding: 6px 12px; border-radius: 12px;
            font-size: 13px; opacity: 0; transition: opacity 0.3s; pointer-events: none;
        }
    </style>

    <script>
        // Inject PHP stats into JS
        window.heatmapStats = <?php echo json_encode($stats); ?>;

        AFRAME.registerComponent('tunisia-3d-map', {
            init: function () {
                const el = this.el;
                
                const statsDict = {};
                let maxSalary = 1;
                window.heatmapStats.forEach(s => {
                    const name = s.region.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
                    statsDict[name] = s;
                    if (s.avg_salary > maxSalary) maxSalary = s.avg_salary;
                });

                // Load the exact geojson boundaries
                fetch('../../assets/js/tunisia.json')
                .then(res => res.json())
                .then(data => {
                    // Center of Tunisia roughly Lng 9.5, Lat 34.0
                    const centerLng = 9.5;
                    const minLat = 30.2; // Southernmost tip so the map rests on Y=0
                    const scaleMultiplier = 0.55; 

                    function project(lng, lat) {
                        // Standard projection, no negation. East is right, North is up.
                        return new THREE.Vector2((lng - centerLng) * scaleMultiplier, (lat - minLat) * scaleMultiplier);
                    }

                    data.features.forEach(feature => {
                        const rawName = feature.properties.shapeName || feature.properties.name || "Inconnu";
                        const regionName = rawName.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
                        
                        let salary = 0;
                        let regionTitle = rawName;
                        
                        // Fuzzy match the region name from DB to GeoJSON
                        for(const key in statsDict) {
                            if(regionName.includes(key) || key.includes(regionName)) {
                                regionTitle = statsDict[key].region;
                                salary = statsDict[key].avg_salary;
                                break;
                            }
                        }

                        // Determine height and color based on stats
                        // Subtle extrusion: max height 0.15, base 0.02
                        let height = (salary / maxSalary) * 0.15;
                        if (height < 0.02) height = 0.02;
                        
                        let colorHex = 0x1e293b; // Default dark (no data)
                        if (salary > 0) colorHex = 0x818cf8; // Mid (Blue/Purple)
                        if (salary > 3000) colorHex = 0xf43f5e; // High (Pink/Red)
                        if (salary > 0 && salary < 1500) colorHex = 0x38bdf8; // Low (Light Blue)

                        const shapes = [];
                        const coords = feature.geometry.type === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;
                        
                        coords.forEach(polygon => {
                            const shape = new THREE.Shape();
                            const exteriorRing = polygon[0];
                            
                            for (let i = 0; i < exteriorRing.length; i++) {
                                const pt = project(exteriorRing[i][0], exteriorRing[i][1]);
                                if (i === 0) shape.moveTo(pt.x, pt.y);
                                else shape.lineTo(pt.x, pt.y);
                            }
                            shapes.push(shape);
                        });

                        const extrudeSettings = { depth: height, bevelEnabled: false };
                        const geometry = new THREE.ExtrudeGeometry(shapes, extrudeSettings);
                        
                        // Tilt back by 20 degrees to stand upright like a display board
                        geometry.rotateX(-Math.PI / 9);

                        const material = new THREE.MeshStandardMaterial({
                            color: colorHex,
                            opacity: 0.9,
                            transparent: true,
                            roughness: 0.4,
                            metalness: 0.1
                        });

                        const mesh = new THREE.Mesh(geometry, material);

                        // Create A-Frame Entity
                        const regionEl = document.createElement('a-entity');
                        regionEl.setObject3D('mesh', mesh);
                        // Place at origin since projection handles offsets
                        regionEl.setAttribute('position', '0 0 0');
                        regionEl.classList.add('clickable');
                        regionEl.classList.add('heatmap-pillar'); // Keeps existing event listeners
                        
                        regionEl.dataset.region = regionTitle;
                        regionEl.dataset.salary = Math.round(salary);

                        // Attach event listeners manually to ensure they bind to dynamically created elements
                        regionEl.addEventListener('click', function() {
                            window.showInfo(this.dataset.region, `Salaire Moyen: ${this.dataset.salary} TND`, "Données du marché régional");
                            const core = document.getElementById('ai-core');
                            if(core) { core.setAttribute('color', '#38bdf8'); setTimeout(() => core.setAttribute('color', '#6366f1'), 500); }
                            
                            // Highlight animation
                            this.setAttribute('animation__pulse', 'property: scale; from: 1 1.2 1; to: 1 1 1; dur: 400');
                        });
                        
                        // Touchstart fallback for mobile
                        regionEl.addEventListener('touchstart', function(e) {
                            e.preventDefault(); // Prevent double fire
                            this.emit('click');
                        });

                        el.appendChild(regionEl);

                        // Add Floating Text for regions with data
                        if (salary > 0) {
                            geometry.computeBoundingBox();
                            const center = new THREE.Vector3();
                            geometry.boundingBox.getCenter(center);
                            
                            const textEl = document.createElement('a-text');
                            textEl.setAttribute('value', regionTitle);
                            textEl.setAttribute('position', `${center.x} ${height + 0.15} ${center.z}`);
                            textEl.setAttribute('align', 'center');
                            textEl.setAttribute('color', '#ffffff');
                            textEl.setAttribute('width', '1.5');
                            textEl.setAttribute('look-at', '[camera]');
                            el.appendChild(textEl);
                        }
                    });
                })
                .catch(err => console.error("Error loading GeoJSON map:", err));
            }
        });

        // Allow components to face camera
        AFRAME.registerComponent('look-at', {
            schema: { type: 'selector' },
            tick: function () {
                if(this.data && this.el.object3D && this.data.object3D) {
                    this.el.object3D.lookAt(this.data.object3D.position);
                }
            }
        });
    </script>
</head>
<body>

    <!-- Start Overlay (Required for Audio Context) -->
    <div id="start-overlay">
        <img src="../assets/img/logo sans bg.png" alt="Aptus Logo" width="150" style="margin-bottom: 20px; filter: drop-shadow(0 0 10px rgba(99,102,241,0.8));">
        <h2>Centre de Commande AR</h2>
        <p style="color: #94a3b8; text-align: center; max-width: 300px;">Pointez la caméra vers le marqueur Hiro et touchez les régions pour explorer les données.</p>
        <button class="btn-start" onclick="startAR()">Activer l'Hologramme</button>
    </div>

    <!-- UI Overlay -->
    <div id="ar-ui-container">
        <div class="header-panel">
            <div>
                <h1 class="header-title"><img src="../assets/img/logo sans bg.png" height="20" alt="Logo"> Aptus AR</h1>
                <p class="header-subtitle" id="view-indicator">Vue actuelle : Carte Thermique 3D</p>
            </div>
            <div class="controls-panel">
                <button class="btn-ui" onclick="changeScale(-0.2)" title="Zoom Out"><i data-lucide="zoom-out"></i></button>
                <button class="btn-ui" onclick="changeScale(0.2)" title="Zoom In"><i data-lucide="zoom-in"></i></button>
                <button class="btn-ui" onclick="switchView()" id="btn-switch" style="background: rgba(99,102,241,0.3); border-color: #6366f1;">
                    <i data-lucide="layers"></i> Basculer
                </button>
            </div>
        </div>

        <div id="ai-status">Aptus AI vous écoute...</div>

        <div class="footer-panel">
            <button id="ptt-btn" title="Maintenir pour parler à l'IA">
                <i data-lucide="mic" style="width: 32px; height: 32px;"></i>
            </button>
        </div>
    </div>

    <!-- A-Frame Scene -->
    <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false; detectionMode: mono_and_matrix; matrixCodeType: 3x3;" cursor="rayOrigin: mouse" raycaster="objects: .clickable">
        <a-assets>
            <img id="aptus-logo" src="../assets/img/logo sans bg.png">
        </a-assets>

        <a-marker preset="hiro" id="main-marker">
            <a-entity id="hologram-wrapper" scale="1 1 1" position="0 0 0">
                
                <!-- Aptus AI Avatar -->
                <a-entity id="ai-avatar" position="1.5 1.5 -1.5" animation="property: position; dir: alternate; dur: 2000; loop: true; to: 1.5 1.6 -1.5">
                    <a-sphere id="ai-core" radius="0.3" color="#6366f1" material="emissive: #4f46e5; emissiveIntensity: 0.5" opacity="0.8" animation="property: scale; dir: alternate; dur: 1500; loop: true; to: 1.1 1.1 1.1"></a-sphere>
                    <a-image src="#aptus-logo" position="0 0 0.35" scale="0.4 0.4 0.4"></a-image>
                    <a-ring radius-inner="0.4" radius-outer="0.42" color="#38bdf8" rotation="45 0 0" animation="property: rotation; to: 45 360 0; loop: true; dur: 5000; easing: linear"></a-ring>
                    <a-ring radius-inner="0.5" radius-outer="0.51" color="#a855f7" rotation="0 45 0" animation="property: rotation; to: 0 360 45; loop: true; dur: 8000; easing: linear"></a-ring>
                </a-entity>

                <!-- VIEW 1: True 3D Topographical Map -->
                <!-- The custom component 'tunisia-3d-map' dynamically builds the extruded meshes here -->
                <a-entity id="heatmap-view" visible="true" tunisia-3d-map>
                    <!-- Decorative Base Glow -->
                    <a-cylinder position="0 -0.05 0" radius="2.0" height="0.02" color="#0f172a" opacity="0.5"></a-cylinder>
                    <a-ring position="0 -0.04 0" radius-inner="1.95" radius-outer="2.0" color="#6366f1" rotation="-90 0 0" opacity="0.5"></a-ring>
                </a-entity>

                <!-- VIEW 2: Holographic Carousel -->
                <a-entity id="cubes-view" visible="false">
                    <!-- Base Pad -->
                    <a-cylinder position="0 -0.05 0" radius="1.8" height="0.02" color="#0f172a" opacity="0.8"></a-cylinder>
                    <a-text value="TOP SECTEURS" position="0 2.2 0" align="center" color="#a855f7" width="4" look-at="[camera]"></a-text>

                    <!-- Rotating Wrapper -->
                    <a-entity animation="property: rotation; to: 0 -360 0; loop: true; dur: 25000; easing: linear">
                    <?php
                    $angle = 0;
                    $step = count($topSectors) > 0 ? (pi() * 2) / count($topSectors) : 0;
                    $radius = 1.5; // Wider radius for panels
                    
                    foreach($topSectors as $sector => $data):
                        $x = cos($angle) * $radius;
                        $z = sin($angle) * $radius;
                        $avgSal = count($data['salaries']) > 0 ? round(array_sum($data['salaries']) / count($data['salaries'])) : 0;
                        // Calculate rotation so panel faces outward
                        $rotY = -($angle * 180 / pi()) + 90; 
                    ?>
                        <!-- Info Panel Poster -->
                        <a-plane position="<?php echo $x; ?> 1.0 <?php echo $z; ?>" 
                                 rotation="0 <?php echo $rotY; ?> 0"
                                 width="1.2" height="0.8" 
                                 color="#1e293b" opacity="0.9"
                                 material="side: double; transparent: true">
                            <!-- Inner glowing border -->
                            <a-plane position="0 0 0.01" width="1.15" height="0.75" color="#0f172a" opacity="1"></a-plane>
                            <a-text value="<?php echo htmlspecialchars(substr($sector, 0, 20)); ?>" position="0 0.25 0.02" align="center" color="#38bdf8" width="1.8"></a-text>
                            <a-text value="Rapports: <?php echo $data['count']; ?>" position="0 0.05 0.02" align="center" color="#fff" width="1.5"></a-text>
                            <a-text value="Moyenne: <?php echo $avgSal; ?> TND" position="0 -0.15 0.02" align="center" color="#f43f5e" width="1.5"></a-text>
                        </a-plane>
                    <?php 
                        $angle += $step;
                    endforeach; 
                    ?>
                    </a-entity>
                </a-entity>

                <!-- Info Panel -->
                <a-plane id="info-panel" position="0 1.8 0" width="1.5" height="0.6" color="#1e293b" opacity="0.95" visible="false" look-at="[camera]">
                    <a-text id="info-title" value="Titre" position="-0.65 0.15 0.01" color="#38bdf8" width="1.8"></a-text>
                    <a-text id="info-detail1" value="Detail 1" position="-0.65 -0.05 0.01" color="#fff" width="1.5"></a-text>
                    <a-text id="info-detail2" value="Detail 2" position="-0.65 -0.2 0.01" color="#94a3b8" width="1.5"></a-text>
                </a-plane>

            </a-entity>
        </a-marker>
        <a-entity camera></a-entity>
    </a-scene>

    <script>
        // Info Panel Global Function
        window.showInfo = function(title, line1, line2) {
            const panel = document.getElementById('info-panel');
            document.getElementById('info-title').setAttribute('value', title);
            document.getElementById('info-detail1').setAttribute('value', line1);
            document.getElementById('info-detail2').setAttribute('value', line2);
            panel.setAttribute('visible', 'true');
            panel.setAttribute('animation', 'property: scale; from: 0 0 0; to: 1 1 1; dur: 300; easing: easeOutElastic');
        }

        lucide.createIcons();

        // Audio Context & Start Overlay
        let speechSynthReady = false;
        function startAR() {
            document.getElementById('start-overlay').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('start-overlay').style.display = 'none';
            }, 500);

            if ('speechSynthesis' in window) {
                let silent = new SpeechSynthesisUtterance('');
                window.speechSynthesis.speak(silent);
                speechSynthReady = true;

                setTimeout(() => {
                    playTTS("Bienvenue dans le centre de commande holographique Aptus. Je suis votre IA d'analyse. Touchez les régions extrudées de la carte ou maintenez le bouton micro pour me parler.");
                }, 1000);
            }
        }

        // Scale & View Controls
        const wrapper = document.getElementById('hologram-wrapper');
        let currentScale = 1;

        function changeScale(delta) {
            currentScale += delta;
            if (currentScale < 0.2) currentScale = 0.2;
            if (currentScale > 3) currentScale = 3;
            wrapper.setAttribute('scale', `${currentScale} ${currentScale} ${currentScale}`);
        }

        let isHeatmap = true;
        function switchView() {
            isHeatmap = !isHeatmap;
            document.getElementById('heatmap-view').setAttribute('visible', isHeatmap);
            document.getElementById('cubes-view').setAttribute('visible', !isHeatmap);
            document.getElementById('view-indicator').innerText = isHeatmap ? 'Vue actuelle : Carte Thermique 3D' : 'Vue actuelle : Data Cubes';
            document.getElementById('info-panel').setAttribute('visible', 'false');
        }

        // Data Cubes Interaction
        document.querySelectorAll('.data-cube').forEach(el => {
            const handler = function(e) {
                if(e && e.type === 'touchstart') e.preventDefault();
                showInfo(
                    this.dataset.sector, 
                    `Rapports liés: ${this.dataset.count}`, 
                    `Moyenne des salaires: ${this.dataset.salary} TND`
                );
                const core = document.getElementById('ai-core');
                if(core) { core.setAttribute('color', '#a855f7'); setTimeout(() => core.setAttribute('color', '#6366f1'), 500); }
            };
            el.addEventListener('click', handler);
            el.addEventListener('touchstart', handler);
        });

        // Aptus AI & Voice Interaction (PTT)
        const pttBtn = document.getElementById('ptt-btn');
        const statusEl = document.getElementById('ai-status');
        const aiCore = document.getElementById('ai-core');
        
        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
                mediaRecorder.onstop = sendAudioToBackend;
            })
            .catch(err => {
                console.error("Mic access denied", err);
                statusEl.innerText = "Accès micro refusé.";
                statusEl.style.opacity = 1;
                pttBtn.disabled = true;
            });

        pttBtn.addEventListener('mousedown', startRecording);
        pttBtn.addEventListener('touchstart', startRecording, {passive: true});
        
        pttBtn.addEventListener('mouseup', stopRecording);
        pttBtn.addEventListener('touchend', stopRecording);

        function startRecording(e) {
            if(e && e.type === 'mousedown' && e.button !== 0) return;
            if(!mediaRecorder || isRecording) return;
            audioChunks = [];
            mediaRecorder.start();
            isRecording = true;
            pttBtn.classList.add('recording');
            statusEl.innerText = "Enregistrement en cours...";
            statusEl.style.opacity = 1;
            aiCore.setAttribute('material', 'emissive: #ef4444; emissiveIntensity: 0.8');
        }

        function stopRecording(e) {
            if(!isRecording) return;
            mediaRecorder.stop();
            isRecording = false;
            pttBtn.classList.remove('recording');
            statusEl.innerText = "Analyse par l'IA...";
            aiCore.setAttribute('material', 'emissive: #a855f7; emissiveIntensity: 0.6');
        }

        async function sendAudioToBackend() {
            pttBtn.disabled = true;
            pttBtn.innerHTML = '<i data-lucide="loader-2" class="spin"></i>';
            lucide.createIcons();

            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            
            try {
                const reader = new FileReader();
                reader.readAsDataURL(audioBlob);
                reader.onloadend = async function() {
                    const base64data = reader.result.split(',')[1];
                    
                    const response = await fetch('/aptus_first_official_version/controller/AgentController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            text: "L'utilisateur me parle via le micro de l'interface de réalité augmentée. Tu es Aptus AI. Réponds brièvement à sa voix.",
                            audio: base64data,
                            mimeType: 'audio/webm'
                        })
                    });

                    const result = await response.json();
                    statusEl.style.opacity = 0;
                    
                    if (result && result.spoken_text) {
                        playTTS(result.spoken_text);
                    } else {
                        playTTS("Désolé, je n'ai pas pu générer une réponse.");
                    }
                }
            } catch (error) {
                console.error(error);
                statusEl.innerText = "Erreur réseau.";
                playTTS("Erreur de connexion.");
            } finally {
                pttBtn.disabled = false;
                pttBtn.innerHTML = '<i data-lucide="mic" style="width: 32px; height: 32px;"></i>';
                lucide.createIcons();
                setTimeout(() => statusEl.style.opacity = 0, 3000);
            }
        }

        function playTTS(text) {
            if (!speechSynthReady && 'speechSynthesis' in window) {
                console.warn("TTS blocked. User must interact with 'Start AR' button first.");
                return;
            }
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            utterance.pitch = 1.0;
            utterance.rate = 1.1;

            utterance.onstart = () => {
                aiCore.setAttribute('animation__talk', 'property: scale; dir: alternate; dur: 200; loop: true; to: 1.3 1.3 1.3');
                aiCore.setAttribute('material', 'emissive: #38bdf8; emissiveIntensity: 1.0');
            };
            utterance.onend = () => {
                aiCore.removeAttribute('animation__talk');
                aiCore.setAttribute('scale', '1 1 1');
                aiCore.setAttribute('material', 'emissive: #4f46e5; emissiveIntensity: 0.5');
            };

            window.speechSynthesis.speak(utterance);
        }
    </script>
</body>
</html>