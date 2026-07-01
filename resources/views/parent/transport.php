<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white">Bus Transport Tracking</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Real-time bus route status, driver profiles, and pickup coordination</p>
    </div>

    <?php if (!$transport): ?>
    <div class="p-10 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/10 text-center">
        <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>
        <p class="text-xs text-slate-500">This student is not registered on any school bus route.</p>
    </div>
    <?php else: ?>
    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Bus Status and Progress Tracker (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Route Details & Live Map Tracking -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white" id="live-route-name"><?= e($transport['route_name']) ?></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Bus Number: <span class="text-indigo-650 dark:text-indigo-400 font-semibold" id="live-bus-number"><?= e($transport['bus_number']) ?></span></p>
                    </div>
                    <?php
                    $isEnRoute = $transport['status'] === 'en_route';
                    $statusColor = $isEnRoute ? 'text-emerald-605 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : 'text-slate-600 dark:text-slate-500 bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800';
                    ?>
                    <span class="px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-1.5 <?= $statusColor ?>" id="live-status-badge">
                        <?php if ($isEnRoute): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping" id="live-status-ping"></span>
                        <?php endif; ?>
                        <span id="live-status-text"><?= $isEnRoute ? 'En Route' : ($transport['status'] === 'completed' ? 'Completed' : 'Inactive') ?></span>
                    </span>
                </div>

                <!-- Live Map Container -->
                <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800/80 shadow-sm bg-slate-100 dark:bg-slate-950" style="height: 350px; position: relative;">
                    <div id="live-map" style="width:100%; height:100%; z-index:1;"></div>
                </div>

                <!-- Live Details Info Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-955/30 border border-slate-150 dark:border-slate-800/40 rounded-xl shadow-inner">
                        <span class="text-slate-500 dark:text-slate-450 uppercase text-[9px] font-bold tracking-wider">Live Speed</span>
                        <p class="text-sm font-bold text-slate-800 dark:text-white mt-0.5" id="live-speed-text"><?= round($transport['current_speed'] ?? 0) ?> km/h</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-955/30 border border-slate-150 dark:border-slate-800/40 rounded-xl shadow-inner">
                        <span class="text-slate-500 dark:text-slate-450 uppercase text-[9px] font-bold tracking-wider">Last Position Update</span>
                        <p class="text-sm font-bold text-slate-800 dark:text-white mt-0.5" id="live-updated-text"><?= $transport['last_updated_at'] ? date('h:i:s A', strtotime($transport['last_updated_at'])) : '—' ?></p>
                    </div>
                </div>
            </div>

            <!-- Bus Route Progress Tracker (Dynamic Stops) -->
            <div class="p-6 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-955/40 relative overflow-hidden shadow-inner">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-6">Bus Stops & Schedule</p>

                <div class="relative pl-8 space-y-6">
                    <!-- Vertical timeline bar -->
                    <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-200 dark:bg-slate-800"></div>

                    <!-- Route Start -->
                    <div class="relative flex items-center justify-between gap-4">
                        <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-emerald-500 border-4 border-white dark:border-slate-950"></div>
                        <div>
                            <h5 class="text-xs font-bold text-slate-700 dark:text-slate-350">Route Start: Depot</h5>
                            <p class="text-[10px] text-slate-500">Scheduled: 08:00 AM</p>
                        </div>
                        <span class="text-[9px] font-semibold text-emerald-600 dark:text-emerald-450 uppercase tracking-wider">Departed</span>
                    </div>

                    <!-- Student's Pickup Stop -->
                    <div class="relative flex items-center justify-between gap-4">
                        <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-indigo-500 border-4 border-white dark:border-slate-950" id="pickup-node-dot"></div>
                        <div>
                            <h5 class="text-xs font-bold text-slate-805 dark:text-white">Your Pickup Stop: <?= e($transport['pickup_point']) ?></h5>
                            <p class="text-[10px] text-slate-500">Scheduled: <?= date('h:i A', strtotime($transport['pickup_time'])) ?></p>
                        </div>
                        <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider" id="live-timeline-status">
                            <?= $transport['status'] === 'en_route' ? 'En Route' : ($transport['status'] === 'completed' ? 'Arrived' : 'Scheduled') ?>
                        </span>
                    </div>

                    <!-- Destination School -->
                    <div class="relative flex items-center justify-between gap-4">
                        <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-700 border-4 border-white dark:border-slate-950" id="school-node-dot"></div>
                        <div>
                            <h5 class="text-xs font-bold text-slate-550 dark:text-slate-400">Destination: PSNF School</h5>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500">Expected: 08:55 AM</p>
                        </div>
                        <span class="text-[9px] font-semibold text-slate-455 dark:text-slate-600 uppercase tracking-wider" id="live-school-status">
                            <?= $transport['status'] === 'completed' ? 'Arrived' : 'Pending' ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Driver profile card & Contact details (1 col wide) -->
        <div class="space-y-6">

            <!-- Driver profile card -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Driver Contact Profile</h4>

                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 border border-slate-200 dark:border-white/10 flex items-center justify-center text-white text-base font-extrabold flex-shrink-0">
                        <?= strtoupper(substr($transport['driver_name'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h5 class="text-sm font-bold text-slate-750 dark:text-slate-200 truncate"><?= e($transport['driver_name']) ?></h5>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Senior Bus Marshall</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-955/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 space-y-3 shadow-inner">
                    <div>
                        <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Registered Driver Phone</span>
                        <p class="text-xs font-semibold text-slate-750 dark:text-slate-200 mt-0.5"><?= e($transport['driver_phone']) ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Assigned Pickup Node</span>
                        <p class="text-xs font-semibold text-indigo-605 dark:text-indigo-300 mt-0.5"><?= e($transport['pickup_point']) ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Expected Pickup Time</span>
                        <p class="text-xs font-semibold text-slate-750 dark:text-slate-200 mt-0.5"><?= date('h:i A', strtotime($transport['pickup_time'])) ?></p>
                    </div>
                </div>

                <a href="tel:<?= e($transport['driver_phone']) ?>"
                   class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Call Driver
                </a>
            </div>

        </div>

    </div>
    <?php endif; ?>

</div>

<?php if ($transport): ?>
<!-- Leaflet CSS + JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const routeId = <?= (int)$transport['id'] ?>;
    let currentLat = <?= (float)($transport['current_latitude'] ?? 19.076090) ?>;
    let currentLng = <?= (float)($transport['current_longitude'] ?? 72.877426) ?>;
    let currentStatus = "<?= e($transport['status']) ?>";
    
    // Initialize Leaflet Map
    const map = L.map('live-map').setView([currentLat, currentLng], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Custom Icon for Bus
    function busIcon(status) {
        const color = status === 'en_route' ? '#10b981' : '#94a3b8';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36">
            <circle cx="18" cy="18" r="18" fill="${color}" opacity="0.15"/>
            <circle cx="18" cy="18" r="13" fill="${color}"/>
            <g transform="translate(10, 10) scale(0.67)">
                <path d="M19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17a2 2 0 11-4 0 2 2 0 014 0z" stroke="white" stroke-width="2" fill="none"/>
                <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4" stroke="white" stroke-width="2" fill="none"/>
            </g>
        </svg>`;
        return L.divIcon({
            html: svg,
            iconSize:   [36, 36],
            iconAnchor: [18, 18],
            popupAnchor:[0, -20],
            className:  '',
        });
    }

    // Add Bus Marker
    const marker = L.marker([currentLat, currentLng], { icon: busIcon(currentStatus) }).addTo(map);
    marker.bindPopup(`
        <div style="font-family:Inter,sans-serif;color:#1e293b;min-width:140px;">
            <div style="font-weight:700;font-size:12px;margin-bottom:4px;color:#0f172a;"><?= e($transport['route_name']) ?></div>
            <div style="font-size:10px;color:#64748b;margin-bottom:4px;">Bus: <?= e($transport['bus_number']) ?></div>
            <div style="font-size:10px;color:#64748b;">Driver: <?= e($transport['driver_name']) ?></div>
        </div>
    `).openPopup();

    // Start Polling for location updates
    setInterval(async () => {
        try {
            const res = await fetch('<?= url("transport/live-data") ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success && data.routes) {
                const route = data.routes.find(r => r.id === routeId);
                if (route) {
                    currentLat = route.lat;
                    currentLng = route.lng;
                    currentStatus = route.status;
                    
                    // Update Map Marker position & icon
                    marker.setLatLng([currentLat, currentLng]);
                    marker.setIcon(busIcon(currentStatus));
                    map.panTo([currentLat, currentLng]);

                    // Update UI text values dynamically
                    document.getElementById('live-speed-text').innerText = Math.round(route.speed) + ' km/h';
                    const lastUpdated = new Date(route.updated_at || new Date());
                    document.getElementById('live-updated-text').innerText = lastUpdated.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    
                    // Update Status Badge
                    const badge = document.getElementById('live-status-badge');
                    const statusText = document.getElementById('live-status-text');
                    const statusPing = document.getElementById('live-status-ping');
                    const timelineStatus = document.getElementById('live-timeline-status');
                    const schoolStatus = document.getElementById('live-school-status');
                    const schoolDot = document.getElementById('school-node-dot');

                    if (currentStatus === 'en_route') {
                        badge.className = "px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-1.5 text-emerald-605 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10";
                        statusText.innerText = "En Route";
                        if (!statusPing) {
                            badge.insertAdjacentHTML('afterbegin', '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping" id="live-status-ping"></span>');
                        }
                        if (timelineStatus) timelineStatus.innerText = "En Route";
                        if (schoolStatus) schoolStatus.innerText = "Pending";
                        if (schoolDot) schoolDot.className = "absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-700 border-4 border-white dark:border-slate-950";
                    } else if (currentStatus === 'completed') {
                        badge.className = "px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-1.5 text-emerald-605 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10";
                        statusText.innerText = "Completed";
                        if (statusPing) statusPing.remove();
                        if (timelineStatus) timelineStatus.innerText = "Arrived";
                        if (schoolStatus) schoolStatus.innerText = "Arrived";
                        if (schoolDot) schoolDot.className = "absolute -left-7 w-2.5 h-2.5 rounded-full bg-emerald-500 border-4 border-white dark:border-slate-950";
                    } else {
                        badge.className = "px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-1.5 text-slate-600 dark:text-slate-500 bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800";
                        statusText.innerText = "Inactive";
                        if (statusPing) statusPing.remove();
                        if (timelineStatus) timelineStatus.innerText = "Scheduled";
                        if (schoolStatus) schoolStatus.innerText = "Pending";
                        if (schoolDot) schoolDot.className = "absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-700 border-4 border-white dark:border-slate-950";
                    }
                </div>
            }
        } catch (e) {
            console.error("Failed to fetch live tracking data", e);
        }
    }, 5000);
});
</script>
<?php endif; ?>
