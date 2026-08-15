<?php $layout = 'parent'; ?>

<div class="space-y-4 max-w-md mx-auto w-full pb-20">

    <!-- Header info -->
    <div class="p-4 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm text-center">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Live Tracking</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Track your child's school bus in real-time</p>
    </div>

    <?php if (!$transport): ?>
    <div class="p-8 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/10 text-center">
        <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>
        <p class="text-xs text-slate-500">This student is not registered on any school bus route.</p>
    </div>
    <?php else: ?>

    <?php 
    $isEnRoute = $transport['route_status'] === 'en_route'; 
    $tripStatus = $transport['trip_status'] ?? null;
    $isCompleted = in_array($tripStatus, ['Dropped Off', 'Skipped', 'Absent']);
    $isPickedUp = $tripStatus === 'Picked Up';
    ?>

    <?php if ($isCompleted): ?>
    <div class="p-8 rounded-2xl border border-dashed border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/10 text-center">
        <svg class="w-8 h-8 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-emerald-700 dark:text-emerald-400 font-bold">Student has been <?= htmlspecialchars($tripStatus) ?></p>
        <p class="text-[10px] text-emerald-600/70 dark:text-emerald-500/70 mt-1">Live tracking is no longer active for this trip.</p>
    </div>
    <?php elseif (!$isEnRoute): ?>
    <div class="p-8 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/10 text-center">
        <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs text-slate-500 font-medium">The trip has not started yet.</p>
        <p class="text-[10px] text-slate-400 mt-1">Live tracking and ETA will be available once the driver starts the trip.</p>
    </div>
    <?php else: ?>
    <!-- ETA Card -->
    <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-5 shadow-sm text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent"></div>
        <div class="relative z-10">
            <?php if ($isPickedUp): ?>
                <h4 class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Student Picked Up - Heading to Campus</h4>
            <?php else: ?>
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Estimated Arrival (ETA)</h4>
            <?php endif; ?>
            <?php 
            $initialEta = $isPickedUp ? ($transport['eta_minutes'] ?? '--') : ($transport['student_eta'] ?? '--');
            $initialKm = $isPickedUp ? ($transport['remaining_km'] ?? '--') : ($transport['student_km'] ?? '--');
            ?>
            <div class="text-4xl font-black text-indigo-600 dark:text-indigo-400 my-2" id="live-eta-text"><?= $initialEta ?> min</div>
            <p class="text-xs text-slate-600 dark:text-slate-300">Distance: <span id="live-distance-text"><?= $initialKm ?> km</span> | Speed: <span id="live-speed-text"><?= round($transport['current_speed'] ?? 0) ?> km/h</span></p>
        </div>
    </div>

    <!-- Live Map Container -->
    <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800/80 shadow-sm bg-slate-100 dark:bg-slate-950" style="height: 300px; position: relative;">
        <div id="live-map" style="width:100%; height:100%; z-index:1;"></div>
    </div>
    <?php endif; ?>

    <!-- Driver Info & Call Button -->
    <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-4 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 border border-slate-200 dark:border-white/10 flex items-center justify-center text-white text-base font-extrabold">
                <?= strtoupper(substr($transport['name'], 0, 1)) ?>
            </div>
            <div class="min-w-0 flex-1">
                <h5 class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate"><?= e($transport['name']) ?></h5>
                <p class="text-xs text-slate-500 dark:text-slate-400">Driver • <?= e($transport['phone']) ?></p>
            </div>
            <?php
            $statusColor = $isEnRoute ? 'text-emerald-600 bg-emerald-100 dark:bg-emerald-500/20' : 'text-slate-600 bg-slate-100 dark:bg-slate-800';
            ?>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold <?= $statusColor ?>" id="live-status-badge">
                <?= $isEnRoute ? 'En Route' : 'Inactive' ?>
            </span>
        </div>
        
        <a href="tel:<?= e($transport['phone']) ?>"
           class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-bold text-white shadow-md transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Call Driver
        </a>
    </div>

</div>

<!-- Leaflet CSS + JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const routeId = <?= (int)$transport['id'] ?>;
    let currentLat = <?= (float)($transport['current_latitude'] ?? 19.076090) ?>;
    let currentLng = <?= (float)($transport['current_longitude'] ?? 72.877426) ?>;
    
    <?php if ($isPickedUp): ?>
    const targetLat = <?= $campusLat ?>; // School Campus Lat
    const targetLng = <?= $campusLng ?>; // School Campus Lng
    const targetName = "School Campus";
    <?php else: ?>
    const targetLat = <?= (float)($transport['pickup_lat'] ?? 0) ?>;
    const targetLng = <?= (float)($transport['pickup_lng'] ?? 0) ?>;
    const targetName = "Pickup Point";
    <?php endif; ?>
    
    <?php if ($isEnRoute): ?>
    // Initialize Leaflet Map
    const map = L.map('live-map').setView([currentLat, currentLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Custom Icons
    const busIcon = L.divIcon({
        html: `<div style="width:36px;height:36px;background:#4f46e5;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg></div>`,
        className: '', iconSize: [36, 36], iconAnchor: [18, 18]
    });
    
    const pickupIcon = L.divIcon({
        html: `<div style="width:24px;height:24px;background:#ef4444;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>`,
        className: '', iconSize: [24, 24], iconAnchor: [12, 12]
    });
    
    let routingLayer;
    let marker = L.marker([currentLat, currentLng], { icon: busIcon }).addTo(map).bindPopup("<b>School Bus</b><br>Speed: <span id='popup-speed'><?= round($transport['current_speed'] ?? 0) ?></span> km/h");

    if (targetLat && targetLng) {
        L.marker([targetLat, targetLng], { icon: pickupIcon }).addTo(map).bindPopup(`<b>${targetName}</b>`);
        calculateETA(currentLat, currentLng, targetLat, targetLng);
    }

    let lastEtaCall = 0;
    async function calculateETA(lat1, lng1, lat2, lng2, force = false) {
        const now = Date.now();
        if (!force && now - lastEtaCall < 5000) {
            return;
        }
        lastEtaCall = now;
        try {
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=full&geometries=geojson`;
            const res = await fetch(osrmUrl);
            const data = await res.json();
            
            if (data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                
                if (routingLayer) map.removeLayer(routingLayer);
                
                routingLayer = L.geoJSON(route.geometry, {
                    style: { color: '#6366f1', weight: 4, opacity: 0.8, dashArray: '10, 10' }
                }).addTo(map);
                
                map.fitBounds(routingLayer.getBounds(), { padding: [40, 40] });
            }
        } catch (e) {
            console.error("OSRM Error", e);
        }
    }

    let lastFetchCall = 0;
    async function fetchLiveEta() {
        const now = Date.now();
        if (now - lastFetchCall < 8000) { // Limit DB fetches to once every 8 seconds
            return;
        }
        lastFetchCall = now;
        try {
            const res = await fetch('<?= url("parent/students/{$active_student['id']}/live-eta") ?>');
            const data = await res.json();
            if (data.success) {
                if (data.eta_minutes !== null) {
                    document.getElementById('live-eta-text').innerText = data.eta_minutes + ' min';
                }
                if (data.remaining_km !== null) {
                    document.getElementById('live-distance-text').innerText = data.remaining_km + ' km';
                }
                document.getElementById('live-speed-text').innerText = Math.round(data.speed) + ' km/h';
            }
        } catch (e) {
            console.error("Fetch ETA Error", e);
        }
    }
    <?php endif; ?>

    // Start Polling
    // Start WebSocket
    const wsUrl = 'ws://' + window.location.hostname + ':8080';
    const ws = new WebSocket(wsUrl);
    
    ws.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            if (data.event === 'gps_update' && data.route_id == routeId) {
                const isNowActive = data.status === 'en_route';
                const wasActive = <?= $isEnRoute ? 'true' : 'false' ?>;
                
                if (isNowActive !== wasActive) {
                    window.location.reload();
                    return;
                }

                <?php if ($isEnRoute): ?>
                currentLat = data.lat;
                currentLng = data.lng;
                
                marker.setLatLng([currentLat, currentLng]);
                
                // Fetch the ultra-accurate Google Routes API calculations from the backend
                fetchLiveEta();
                
                // Recalculate OSRM geometry path
                if (targetLat && targetLng) {
                    calculateETA(currentLat, currentLng, targetLat, targetLng);
                }
                
                map.panTo([currentLat, currentLng], {animate: true, duration: 1.0});
                <?php endif; ?>
            }
        } catch (e) {}
    };
    
    ws.onclose = () => {
        console.log('WebSocket disconnected. Reconnecting...');
        setTimeout(() => window.location.reload(), 5000);
    };
});
</script>
<?php endif; ?>
