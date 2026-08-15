<?php
$layout    = 'app';
$pageTitle = 'Transport Pipeline Logs';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Pipeline Logs']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto" x-data="{
    filterType: 'ALL',
    searchDriver: '',
    autoRefresh: true,
    refreshInterval: null,
    
    init() {
        this.refreshInterval = setInterval(() => {
            if (this.autoRefresh) {
                window.location.reload();
            }
        }, 4000);
    },
    
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Transport Diagnostic Console</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time status stream of driver GPS updates, Google Routes API calls, and WebSocket broadcast events.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button @click="window.location.reload()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.27 15M20.27 18h-4.36"/></svg>
                Force Refresh
            </button>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" x-model="autoRefresh" class="sr-only peer">
                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                <span class="ml-2 text-xs font-semibold text-slate-600 dark:text-slate-400 select-none">Auto-refresh (4s)</span>
            </label>
        </div>
    </div>

    <!-- Diagnostic Stream & Logs Dashboard -->
    <div class="grid grid-cols-1 gap-6">

        <!-- Filters card -->
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex flex-col md:flex-row items-center gap-4 justify-between shadow-sm">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Filters:</span>
                <select x-model="filterType" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="ALL">All Event Types</option>
                    <option value="GPS_UPDATE">Driver GPS Packets</option>
                    <option value="GOOGLE_API_CALL">Google API Requests</option>
                    <option value="GOOGLE_API_RESPONSE">Google API Responses</option>
                    <option value="GOOGLE_API_ERROR">Google API Errors</option>
                    <option value="WEBSOCKET_BROADCAST">WebSocket Broadcasts</option>
                </select>
                <input type="text" x-model="searchDriver" placeholder="Filter by Driver..." class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            
            <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                Total Logs Recorded: <?= count($logs) ?> (showing last 500)
            </div>
        </div>

        <!-- Terminal Console Logs list -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-950 text-slate-200 font-mono shadow-xl overflow-hidden">
            <div class="bg-slate-900 px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                    <span class="text-2xs font-semibold text-slate-500 uppercase tracking-widest ml-3">Diagnostic_pipeline_stream.sh</span>
                </div>
                <span class="text-3xs text-slate-500 uppercase">Live Output</span>
            </div>
            
            <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto divide-y divide-slate-800/40 text-xs">
                <?php if (empty($logs)): ?>
                    <div class="py-12 text-center text-slate-500 select-none">
                        <svg class="w-12 h-12 mx-auto text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Diagnostic console empty. Start a driver trip and update GPS to see updates.
                    </div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php 
                        $type = $log['type'] ?? 'INFO';
                        $color = 'text-slate-400 bg-slate-900 border-slate-800';
                        if ($type === 'GPS_UPDATE') $color = 'text-sky-400 bg-sky-950/40 border-sky-900/50';
                        elseif ($type === 'GOOGLE_API_CALL') $color = 'text-purple-400 bg-purple-950/40 border-purple-900/50';
                        elseif ($type === 'GOOGLE_API_RESPONSE') $color = 'text-emerald-400 bg-emerald-950/40 border-emerald-900/50';
                        elseif ($type === 'GOOGLE_API_ERROR') $color = 'text-rose-400 bg-rose-950/40 border-rose-900/50';
                        elseif ($type === 'WEBSOCKET_BROADCAST') $color = 'text-amber-400 bg-amber-950/40 border-amber-900/50';
                        ?>
                        
                        <div class="pt-3 first:pt-0" 
                             x-show="(filterType === 'ALL' || filterType === '<?= $type ?>') && 
                                     (searchDriver === '' || '<?= strtolower(addslashes($log['driver_name'] ?? '')) ?>'.includes(searchDriver.toLowerCase()) || '<?= $log['driver_id'] ?? '' ?>'.includes(searchDriver))">
                            
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-slate-500 font-bold select-none">[<?= date('H:i:s', strtotime($log['time'])) ?>]</span>
                                    <span class="px-2 py-0.5 rounded border font-extrabold text-2xs uppercase tracking-wider <?= $color ?>"><?= $type ?></span>
                                    <span class="text-slate-300 font-semibold"><?= e($log['driver_name']) ?> <span class="text-slate-600 font-normal">(ID: <?= $log['driver_id'] ?>)</span></span>
                                </div>
                                <span class="text-slate-600 text-3xs font-semibold select-none"><?= e($log['time']) ?></span>
                            </div>
                            
                            <!-- Logs details display -->
                            <div class="pl-0 sm:pl-8 text-slate-400 space-y-1">
                                <?php if ($type === 'GPS_UPDATE'): ?>
                                    <div class="text-slate-300 font-medium">📍 Lat: <span class="text-white"><?= $log['details']['latitude'] ?></span>, Lng: <span class="text-white"><?= $log['details']['longitude'] ?></span> | Speed: <span class="text-indigo-400"><?= $log['details']['speed'] ?> km/h</span></div>
                                <?php elseif ($type === 'GOOGLE_API_CALL'): ?>
                                    <div class="text-purple-300 font-medium">🌐 Google Maps API Route Calculation Requested</div>
                                    <div class="text-3xs text-slate-500">Reason: <?= e($log['details']['trigger_reason']) ?></div>
                                    <div class="text-2xs">From: <span class="text-slate-300"><?= $log['details']['origin'] ?></span> ➔ To Campus stop: <span class="text-slate-300"><?= $log['details']['destination'] ?></span></div>
                                <?php elseif ($type === 'GOOGLE_API_RESPONSE'): ?>
                                    <div class="text-emerald-300 font-medium font-bold">✓ Campus calculated: <span class="text-white"><?= $log['details']['campus_remaining_km'] ?? '--' ?> km</span> | Campus ETA: <span class="text-white"><?= $log['details']['campus_eta_minutes'] ?? '--' ?> min</span></div>
                                    <div class="text-3xs text-slate-500">Google waypoint legs parsed: <?= $log['details']['legs_parsed'] ?? 0 ?></div>
                                <?php elseif ($type === 'GOOGLE_API_ERROR'): ?>
                                    <div class="text-rose-400 font-bold">☠ API Calculation Error: <?= e($log['details']['error'] ?? 'API response failed') ?></div>
                                <?php elseif ($type === 'WEBSOCKET_BROADCAST'): ?>
                                    <div class="text-amber-300 font-medium">📡 WebSocket Broadcast to port 8081: Status: <span class="text-white"><?= e($log['details']['status']) ?></span></div>
                                <?php endif; ?>
                                
                                <details class="group mt-2">
                                    <summary class="cursor-pointer text-slate-600 hover:text-slate-400 text-3xs select-none transition-colors">Expand raw payload JSON</summary>
                                    <pre class="bg-black/60 p-3 mt-1.5 rounded-lg text-slate-500 max-h-40 overflow-auto border border-slate-900 group-open:block hidden text-2xs select-all"><?= htmlspecialchars(json_encode($log['details'], JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="bg-slate-900 px-4 py-2 border-t border-slate-800 text-3xs text-slate-500 flex items-center justify-between">
                <span>Diagnostic output auto-updates dynamically</span>
                <span>Console v1.0.0</span>
            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
?>
