<?php 
require_once '../../controller/offreC.php';
$offreC = new offreC();
$offres = $offreC->getOffresAvecLieu();

$pageTitle = "Carte des Offres"; 
$pageCSS = "feeds.css"; 
$userRole = "Candidat"; 

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
} else {
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet Routing Machine CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<style>
    #map-container {
        height: calc(100vh - 160px);
        margin: 0 25px 25px 25px;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        border: 1px solid var(--border-color);
        position: relative;
        z-index: 1; /* Pour rester sous la barre de navigation */
    }
    #map {
        height: 100%;
        width: 100%;
        background: #f8fafc;
    }
    .btn-back-map {
        background: linear-gradient(90deg, #0ea5e9 0%, #9333ea 100%);
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(147, 51, 234, 0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.85rem;
    }
    .btn-back-map:hover {
        transform: translateX(-5px);
        box-shadow: 0 6px 20px rgba(147, 51, 234, 0.3);
        color: white;
    }
    .map-overlay-card {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        padding: 1.25rem;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        max-width: 280px;
    }
    .route-info-card {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: white;
        padding: 0.75rem 1.25rem;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid var(--border-color);
        animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 280px;
    }
    .btn-start-navigation {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: center;
        font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(168, 100, 228, 0.25);
    }
    /* Style pour le panneau d'instructions Leaflet */
    .leaflet-routing-container {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        border-radius: 20px !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        max-height: 300px;
        overflow-y: auto;
        padding: 1rem !important;
        width: 320px !important;
    }
    .leaflet-routing-alt {
        max-height: none !important;
    }
    @keyframes slideUp {
        from { transform: translate(-50%, 100%); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    .route-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }
    .route-item i { color: var(--accent-primary); font-size: 1.2rem; }
    .route-val { font-weight: 800; color: var(--text-primary); font-size: 1rem; }
    .route-label { font-size: 0.75rem; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; }
    .search-box {
        margin-top: 1.25rem;
        position: relative;
    }
    .search-box input {
        width: 100%;
        padding: 0.85rem 1rem 0.85rem 3.25rem !important;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .search-box input:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 4px rgba(168, 100, 228, 0.1);
        outline: none;
    }
    .search-box i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--accent-primary);
        font-size: 1.1rem;
        z-index: 10;
    }
    .leaflet-routing-container {
        display: none; /* On gère l'affichage nous-mêmes */
    }
    .guidage-panel {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        padding: 1rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        width: 240px;
        display: none;
        border-right: 4px solid var(--accent-primary);
        animation: slideInRight 0.5s ease;
    }
    .btn-center-me {
        position: absolute;
        bottom: 40px; /* Un peu plus bas */
        right: 20px;
        z-index: 1000;
        width: 55px;
        height: 55px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        cursor: pointer;
        border: 2px solid rgba(168, 100, 228, 0.1);
        color: #4fb5ff; /* Bleu Aptus */
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .btn-center-me i {
        font-size: 1.5rem !important;
        position: relative;
        z-index: 1001;
    }
    .btn-center-me:hover {
        transform: scale(1.15) rotate(90deg);
        color: white;
        background: var(--gradient-primary);
        border-color: transparent;
    }
    .btn-center-me::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid var(--accent-primary);
        animation: pulse-me 2s infinite;
        opacity: 0;
    }
    @keyframes pulse-me {
        0% { transform: scale(1); opacity: 0.5; }
        100% { transform: scale(1.6); opacity: 0; }
    }
    
    /* Style Hover Cards Tooltip */
    .leaflet-tooltip-aptus {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(8px);
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        padding: 10px 15px !important;
        font-family: 'Outfit', sans-serif !important;
    }
    .leaflet-tooltip-aptus::before {
        display: none !important;
    }
    .hover-card-title {
        color: var(--text-primary);
        font-weight: 800;
        font-size: 0.9rem;
        margin-bottom: 2px;
        display: block;
    }
    .hover-card-company {
        color: var(--accent-primary);
        font-weight: 700;
        font-size: 0.75rem;
        display: block;
    }

    /* Side Drawer Style */
    .side-drawer {
        position: absolute;
        top: 0;
        left: 0;
        width: 380px;
        height: 100%;
        background: white;
        z-index: 2000;
        box-shadow: 20px 0 50px rgba(0,0,0,0.1);
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 30px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
    }
    .side-drawer.open {
        transform: translateX(0);
    }
    .drawer-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s;
    }
    .drawer-close:hover {
        background: #f1f5f9;
        color: #ef4444;
    }
    .drawer-image {
        width: 100%;
        height: 180px;
        border-radius: 20px;
        object-fit: cover;
        background: var(--gradient-primary);
    }
    .drawer-badge {
        background: rgba(168, 100, 228, 0.1);
        color: var(--accent-primary);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        width: fit-content;
    }
    .btn-apply-drawer {
        background: var(--gradient-primary);
        color: white;
        padding: 15px;
        border-radius: 14px;
        text-align: center;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 10px 20px rgba(168, 100, 228, 0.3);
        transition: all 0.3s;
    }
    .btn-apply-drawer:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(168, 100, 228, 0.4);
    }

    .btn-external-maps {
        background: #f8fafc;
        color: #1e293b;
        border: 2px solid #f1f5f9;
        padding: 15px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        text-decoration: none;
    }
    .btn-external-maps:hover {
        background: #fff;
        border-color: #4285F4;
        color: #4285F4;
        transform: translateY(-2px);
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .instruction-next {
        font-weight: 800;
        color: var(--text-primary);
        font-size: 0.95rem; /* Un peu plus petit pour le titre principal */
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .instruction-upcoming {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.5rem;
        width: 100%;
    }
    .instruction-dist {
        color: var(--accent-primary);
        font-weight: 700;
        font-size: 0.9rem;
    }
    .leaflet-marker-icon {
        transition: all 0.2s linear; /* Encore plus rapide */
    }
    .custom-marker {
        background: var(--gradient-primary);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 20px 20px 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transform: rotate(-45deg);
        box-shadow: 0 5px 15px rgba(168, 100, 228, 0.4);
        border: 2px solid white;
    }
    .custom-marker i {
        transform: rotate(45deg);
        color: white;
        font-size: 18px;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 15px;
        padding: 0.5rem;
    }
    .btn-route {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        background: linear-gradient(90deg, #0ea5e9 0%, #9333ea 100%);
        color: white !important;
        padding: 0.6rem 1rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        margin-top: 1rem;
        transition: all 0.3s;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(147, 51, 234, 0.15);
    }
    .btn-route:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(147, 51, 234, 0.3);
    }
    /* Style global des popups Leaflet */
    .leaflet-popup-content-wrapper {
        border-radius: 20px ;
        padding: 10px ;
    }
    .leaflet-popup-tip {
        background: white ;
    }
</style>

<div style="padding: 20px 0 0 0;">
    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px; width: calc(100% - 50px); margin-left: 25px;">
        <!-- Ligne du haut : Retour + Compteur -->
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <a href="jobs_feed.php" class="btn-back-map" title="Retour aux offres">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                <span style="font-weight: 700; margin-left: 0.5rem;">Retour aux offres</span>
            </a>
            <div id="job-count-badge" style="background: white; padding: 0.5rem 1.25rem; border-radius: 20px; font-size: 0.85rem; font-weight: 800; color: var(--accent-primary); box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
                <i class="fas fa-layer-group" style="margin-right: 8px; opacity: 0.7;"></i>
                <span id="current-count">0</span> offres trouvées
            </div>
        </div>

        <!-- Recherche dynamique d'offres (Full Width) -->
        <div style="display: flex; gap: 15px; width: 100%; align-items: stretch;">
            <div class="search-box" style="margin-top: 0; flex: 1; box-shadow: 0 10px 25px rgba(0,0,0,0.05); position: relative;">
                <i class="fas fa-search"></i>
                <input type="text" id="job-search" placeholder="Rechercher par titre..." onkeyup="filterAll()" style="height: 55px; font-size: 1.1rem; border-radius: 18px; border: 2px solid transparent; transition: all 0.3s; background: white; width: 100%;">
            </div>
            
            <!-- Slider de Rayon -->
            <div style="background: white; border-radius: 18px; padding: 0 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); min-width: 250px;">
                <i class="fas fa-map-marked-alt" style="color: var(--accent-primary);"></i>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-tertiary); text-transform: uppercase;">Rayon</span>
                        <span id="radius-val" style="font-size: 0.85rem; font-weight: 800; color: var(--accent-primary);">50 km</span>
                    </div>
                    <input type="range" id="radius-slider" min="1" max="100" value="100" oninput="filterAll()" style="width: 100%; accent-color: var(--accent-primary); cursor: pointer;">
                </div>
            </div>
        </div>
    </div>

    <div id="map-container">
        <!-- Side Drawer -->
        <div id="side-drawer" class="side-drawer">
            <div class="drawer-close" onclick="closeDrawer()">
                <i class="fas fa-times"></i>
            </div>
            <div id="drawer-content">
                <!-- Rempli par JS -->
            </div>
        </div>

        <!-- Panneau de guidage (Tout à droite) -->
        <div id="guidage-panel" class="guidage-panel">
            <div id="instruction-upcoming" class="instruction-upcoming" style="display: none;">
                <i class="fas fa-redo-alt" style="font-size: 0.6rem;"></i>
                <span>Ensuite : <span id="next-step-text">--</span></span>
            </div>
            <div class="instruction-next">
                <i id="instruction-icon" class="fas fa-arrow-up"></i>
                <span id="instruction-text">Calcul...</span>
            </div>
            <div id="instruction-dist" class="instruction-dist">-- m</div>
        </div>

        <!-- Itinéraire Info Card -->
        <div id="route-info" class="route-info-card">
            <div style="display: flex; align-items: center; gap: 2rem; width: 100%; justify-content: center;">
                <div class="route-item">
                    <i class="fas fa-car"></i>
                    <span id="car-time" class="route-val">--</span>
                    <span class="route-label">Voiture</span>
                </div>
                <div style="width: 1px; height: 30px; background: var(--border-color);"></div>
                <div class="route-item">
                    <i class="fas fa-walking"></i>
                    <span id="walk-time" class="route-val">--</span>
                    <span class="route-label">À pied</span>
                </div>
                <div style="width: 1px; height: 30px; background: var(--border-color);"></div>
                <div class="route-item">
                    <i class="fas fa-route"></i>
                    <span id="route-dist" class="route-val">--</span>
                    <span class="route-label">Distance</span>
                </div>
            </div>
            
            <button class="btn-start-navigation" id="btn-commencer" onclick="startGuidage()">
                <i class="fas fa-play"></i> Commencer le guidage
            </button>

            <button onclick="stopGuidage()" style="border:none; background:none; cursor:pointer; color:var(--text-tertiary); font-size: 0.8rem; text-decoration: underline;">
                Annuler
            </button>
        </div>

        <!-- Overlay d'info -->
        <div class="map-overlay-card">
            <h3 style="font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; font-size: 1.2rem;">Exploration Locale</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                Trouvez les meilleures opportunités à proximité de chez vous.
            </p>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="address-search" placeholder="Rechercher une ville..." onkeypress="if(event.key === 'Enter') searchAddress()">
            </div>

            <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 12px; height: 12px; background: #4fb5ff; border-radius: 50%; box-shadow: 0 0 0 4px rgba(79, 181, 255, 0.2);"></div>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">Ma position</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 12px; height: 12px; background: var(--accent-primary); border-radius: 50%; box-shadow: 0 0 0 4px rgba(168, 100, 228, 0.2);"></div>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">Offre disponible</span>
                </div>
            </div>
        </div>

        <div id="map"></div>
        
        <!-- Bouton Centrer sur moi -->
        <button class="btn-center-me" onclick="centerOnMe()" title="Ma position actuelle">
            <i class="fas fa-crosshairs" style="font-size: 1.25rem;"></i>
        </button>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine JS -->
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialisation de la carte (Centrée sur la Tunisie)
    const map = L.map('map').setView([36.8065, 10.1815], 11);

    // 2. Ajout de la couche de tuiles (Version Française d'OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap France | &copy; OpenStreetMap contributors'
    }).addTo(map);

    // 3. Récupération des offres depuis PHP
    const offres = <?php echo json_encode($offres); ?>;
    let userLocation = null;

    // 4. Géolocalisation de l'utilisateur (Rafraîchissement forcé toutes les 5s)
    let userMarker = null;

    function refreshLocation() {
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            const newPos = [latitude, longitude];
            
            if (!userMarker) {
                userMarker = L.circleMarker(newPos, {
                    radius: 12,
                    fillColor: "#4fb5ff",
                    color: "#fff",
                    weight: 4,
                    opacity: 1,
                    fillOpacity: 1
                }).addTo(map);
                map.setView(newPos, 18);
            } else {
                userMarker.setLatLng(newPos);
            }
            
            const oldPos = userLocation;
            userLocation = newPos;

            // Optimisation : Ne recalculer la route que si on a bougé de plus de 3 mètres
            if (routingControl && currentDest) {
                if (!oldPos || getDistance(oldPos, newPos) > 0.003) {
                    updateLiveRoute();
                    checkInstructionProgress(newPos);
                }
            }
        }, (err) => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 2000
        });
    }

    // Premier appel immédiat
    refreshLocation();
    // Puis toutes les 500ms (Ultra-réactif)
    setInterval(refreshLocation, 500);

    // Fonction pour vérifier si on a passé une étape sans attendre le serveur
    function checkInstructionProgress(currentPos) {
        if (!window.allInstructions || window.allInstructions.length <= 1) return;
        
        // Si on est à moins de 15m du prochain point de manœuvre, on passe à l'instruction suivante
        const nextInstr = window.allInstructions[0];
        if (nextInstr.distance < 15) {
            window.allInstructions.shift(); // Supprimer l'étape franchie
            updateInstructionPanel(window.allInstructions[0], window.allInstructions[1]);
        }
    }

    // Fonction utilitaire pour calculer la distance entre 2 points (en km)
    function getDistance(pos1, pos2) {
        const R = 6371;
        const dLat = (pos2[0] - pos1[0]) * Math.PI / 180;
        const dLon = (pos2[1] - pos1[1]) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(pos1[0] * Math.PI / 180) * Math.cos(pos2[0] * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    let currentDest = null;
    function updateLiveRoute() {
        if (!userLocation || !currentDest) return;
        routingControl.setWaypoints([
            L.latLng(userLocation[0], userLocation[1]),
            L.latLng(currentDest.lat, currentDest.lon)
        ]);
    }

    // 5. Géocodage et affichage des offres
    let jobMarkers = []; // Stocker les marqueurs pour le filtrage
    
    async function displayOffres(offresList) {
        jobMarkers.forEach(m => map.removeLayer(m));
        jobMarkers = [];

        for (let i = 0; i < offresList.length; i++) {
            const offre = offresList[i];
            if (!offre.lieu) continue;

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(offre.lieu)}`);
                const data = await response.json();
                
                if (data.length > 0) {
                    const lat = data[0].lat;
                    const lon = data[0].lon;
                    const markerHtml = `<div class="custom-marker"><i class="fas fa-briefcase"></i></div>`;
                    const customIcon = L.divIcon({ html: markerHtml, className: 'dummy', iconSize: [40, 40], iconAnchor: [20, 40] });
                    const marker = L.marker([lat, lon], { icon: customIcon }).addTo(map);

                    marker.on('click', () => openDrawer(offre, lat, lon));
                    marker.bindTooltip(`<div class="hover-card"><span class="hover-card-title">${offre.titre}</span><span class="hover-card-company">${offre.nom_entreprise || 'Aptus'}</span></div>`, { className: 'leaflet-tooltip-aptus', direction: 'top', offset: [0, -35], opacity: 1 });

                    jobMarkers.push(marker);
                }
            } catch (err) { console.error(err); }
            await new Promise(resolve => setTimeout(resolve, 50));
        }
    }

    displayOffres(offres).then(() => {
        document.getElementById('current-count').innerText = jobMarkers.length;
        const urlParams = new URLSearchParams(window.location.search);
        const jobId = urlParams.get('id');
        if (jobId) {
            const targetOffre = offres.find(o => o.id_offre == jobId);
            if (targetOffre) {
                setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(targetOffre.lieu)}`)
                        .then(r => r.json())
                        .then(data => { if (data.length > 0) openDrawer(targetOffre, data[0].lat, data[0].lon); });
                }, 500);
            }
        }
    });

    // 6. Gestion du Side Drawer
    window.openDrawer = function(offre, lat, lon) {
        const drawer = document.getElementById('side-drawer');
        const content = document.getElementById('drawer-content');
        
        content.innerHTML = `
            <div style="margin-top: 20px;">
                <div class="drawer-badge">Offre Active</div>
                <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 15px 0 5px 0; line-height: 1.1;">${offre.titre}</h2>
                <p style="color: var(--accent-primary); font-weight: 700; font-size: 1rem; margin-bottom: 20px;">${offre.nom_entreprise || offre.entreprise || 'Entreprise'}</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 25px;">
                    <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; display: block;">Lieu</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">${offre.lieu}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; display: block;">Type</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Temps Plein</span>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="font-size: 0.85rem; font-weight: 800; color: var(--text-primary); margin-bottom: 10px;">Description courte</h4>
                    <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6;">Nous recherchons un talent passionné pour rejoindre notre équipe à ${offre.lieu}. Cette opportunité vous permettra de travailler sur des projets innovants chez ${offre.nom_entreprise || offre.entreprise || 'notre partenaire'}.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="apply.php?id=${offre.id_offre}" class="btn-apply-drawer">
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Postuler maintenant
                    </a>
                    <button onclick="getDirections(${lat}, ${lon}); closeDrawer();" style="border: 2px solid #f1f5f9; background: white; color: #1e293b; padding: 15px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-route" style="color: var(--accent-primary);"></i> Itinéraire interne
                    </button>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}" target="_blank" class="btn-external-maps">
                        <img src="https://www.google.com/images/branding/product/2x/maps_96dp.png" style="width: 20px;" alt="Google Maps">
                        Ouvrir dans Google Maps
                    </a>
                    <a href="job_details.php?id=${offre.id_offre}" style="text-align: center; color: #94a3b8; font-size: 0.8rem; text-decoration: none; margin-top: 5px;">
                        Voir l'offre complète sur le site
                    </a>
                </div>
            </div>
        `;
        
        drawer.classList.add('open');
        map.flyTo([lat, lon], 15, { animate: true, duration: 0.8 });
    };

    window.closeDrawer = function() {
        document.getElementById('side-drawer').classList.remove('open');
    };

    // 7. Fonction pour l'itinéraire
    let routingControl = null;

    window.getDirections = function(destLat, destLon) {
        if (!userLocation) {
            alert("Veuillez autoriser la géolocalisation pour obtenir l'itinéraire.");
            return;
        }

        currentDest = { lat: destLat, lon: destLon };

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(userLocation[0], userLocation[1]),
                L.latLng(destLat, destLon)
            ],
            language: 'fr', // Forcer les instructions en français
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false,
            lineOptions: {
                styles: [
                    { color: 'white', opacity: 0.9, weight: 12 }, // Contour blanc pour le contraste
                    { color: '#007aff', opacity: 1, weight: 8 }    // Ligne bleue électrique au centre
                ]
            },
            createMarker: function() { return null; }
        }).on('routesfound', function(e) {
            const routes = e.routes;
            const summary = routes[0].summary;
            const instructions = routes[0].instructions;
            
            // Stocker les instructions pour les mettre à jour selon la position
            window.allInstructions = instructions;
            
            if (instructions && instructions.length > 0) {
                updateInstructionPanel(instructions[0], instructions[1]);
            }

            const distKm = (summary.totalDistance / 1000).toFixed(1);
            const timeCar = Math.round(summary.totalTime / 60);
            const timeWalk = Math.round(distKm * 12);

            document.getElementById('route-dist').innerText = distKm + ' km';
            document.getElementById('car-time').innerText = timeCar + ' min';
            document.getElementById('walk-time').innerText = timeWalk > 60 ? Math.floor(timeWalk/60) + 'h ' + (timeWalk%60) + 'm' : timeWalk + ' min';
            document.getElementById('route-info').style.display = 'flex';
        }).addTo(map);
    };

    function updateInstructionPanel(instr, nextInstr = null) {
        // Instruction principale
        let text = instr.text;
        const streetName = instr.road || "";
        if (streetName && !text.includes(streetName)) {
            text += " sur " + streetName;
        }

        const dist = Math.round(instr.distance);
        const iconElement = document.getElementById('instruction-icon');
        
        document.getElementById('instruction-text').innerText = text;
        document.getElementById('instruction-dist').innerText = dist + " m";

        // Instruction suivante (Ensuite)
        const upcomingPanel = document.getElementById('instruction-upcoming');
        if (nextInstr) {
            document.getElementById('next-step-text').innerText = nextInstr.text;
            upcomingPanel.style.display = 'flex';
        } else {
            upcomingPanel.style.display = 'none';
        }
        
        // Changer l'icône selon le texte
        if (text.toLowerCase().includes('gauche')) iconElement.className = "fas fa-arrow-left";
        else if (text.toLowerCase().includes('droite')) iconElement.className = "fas fa-arrow-right";
        else iconElement.className = "fas fa-arrow-up";
    }

    window.centerOnMe = function() {
        if (userLocation) {
            map.flyTo(userLocation, 18, {
                animate: true,
                duration: 1.5
            });
        }
    };

    window.startGuidage = function() {
        if (!routingControl) return;
        
        // Afficher le panneau de guidage
        document.getElementById('guidage-panel').style.display = 'block';
        
        // Masquer le bouton commencer
        document.getElementById('btn-commencer').style.display = 'none';
        
        if (userLocation) {
            map.setView(userLocation, 18);
        }
    };

    window.stopGuidage = function() {
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }
        document.getElementById('route-info').style.display = 'none';
        document.getElementById('guidage-panel').style.display = 'none';
        document.getElementById('btn-commencer').style.display = 'flex';
    };

    let radiusCircle = null;

    window.filterAll = function() {
        const query = document.getElementById('job-search').value.toLowerCase();
        const maxDist = parseInt(document.getElementById('radius-slider').value);
        document.getElementById('radius-val').innerText = maxDist === 100 ? "Illimité" : maxDist + " km";
        
        let count = 0;

        // Gérer le cercle de rayon
        if (userLocation && maxDist < 100) {
            const radiusInMeters = maxDist * 1000;
            if (!radiusCircle) {
                radiusCircle = L.circle(userLocation, {
                    radius: radiusInMeters,
                    color: '#c00d0dff',
                    fillColor: '#ea3535ff',
                    fillOpacity: 0.08,
                    weight: 2,
                    dashArray: '10, 10'
                }).addTo(map);
            } else {
                radiusCircle.setLatLng(userLocation);
                radiusCircle.setRadius(radiusInMeters);
            }
            
            // Auto-zoom pour que tout le cercle soit visible
            map.fitBounds(radiusCircle.getBounds(), { padding: [20, 20] });
        } else if (radiusCircle) {
            map.removeLayer(radiusCircle);
            radiusCircle = null;
        }
        
        jobMarkers.forEach(marker => {
            const title = (marker.options.jobTitle || "").toLowerCase();
            const markerPos = marker.getLatLng();
            const distanceToUser = userLocation ? (getDistance(userLocation, [markerPos.lat, markerPos.lng])) : 0;
            
            const matchesSearch = title.includes(query);
            const matchesRadius = (maxDist === 100) || (distanceToUser <= maxDist);

            if (matchesSearch && matchesRadius) {
                if (!map.hasLayer(marker)) marker.addTo(map);
                count++;
            } else {
                if (map.hasLayer(marker)) map.removeLayer(marker);
            }
        });

        document.getElementById('current-count').innerText = count;
    };

    window.searchAddress = function() {
        const query = document.getElementById('address-search').value;
        if (!query) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    map.setView([data[0].lat, data[0].lon], 13);
                } else {
                    alert("Lieu non trouvé.");
                }
            })
            .catch(err => console.error("Search error:", err));
    };

    if (window.lucide) lucide.createIcons();
});
</script>

<?php } ?>
