<?php
$layout    = 'app';
$pageTitle = 'Bus & Transport';
$breadcrumbs = [
    ['label' => 'Bus & Transport', 'url' => '/transport'],
    ['label' => 'Live Tracking'],
];
?>

<div class="space-y-6" x-data="liveTracking()" x-init="init()">

    <!-- View Switcher Tabs -->
    <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-6">
        <a href="<?= url('transport') ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            Routes & Vehicles
        </a>
        <a href="<?= url('transport/tracking') ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none border-indigo-500 text-indigo-600 dark:text-indigo-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Live Tracking
        </a>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Live Tracking</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Real-time positions of all active school transport routes</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Live indicator -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">LIVE</span>
                <span class="text-xs text-emerald-600 dark:text-emerald-500" x-text="'· refreshes in ' + countdown + 's'"></span>
            </div>
            <a href="<?= url('transport') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Manage Routes
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide">Total Routes</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1"><?= count($routes) ?></p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide">Active Buses</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1"><?= count(array_filter($routes, fn($r) => $r['status'] === 'en_route')) ?></p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide">Students on Bus</p>
            <p class="text-2xl font-bold text-brand-600 dark:text-brand-400 mt-1"><?= array_sum(array_column($routes, 'student_count')) ?></p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide">Last Updated</p>
            <p class="text-sm font-bold text-slate-800 dark:text-white mt-1" x-text="lastUpdated"></p>
        </div>
    </div>

    <!-- Main Layout: Map + Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Route List Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            
            <!-- Bus Routes List -->
            <div class="space-y-3">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-1">Bus Routes</h3>

                <?php foreach ($routes as $route): ?>
                <div class="route-card rounded-2xl bg-white dark:bg-slate-900/40 border transition-all duration-200 hover:shadow-md cursor-pointer"
                     @click="selectRoute(<?= $route['id'] ?>)"
                     :class="selectedRoute == <?= $route['id'] ?> ? 'border-brand-500 dark:border-brand-500 shadow-md shadow-brand-500/10' : 'border-slate-200 dark:border-slate-800/60'">

                    <!-- Card Content -->
                    <div class="p-3.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                    <?= $route['status'] === 'en_route' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-slate-100 dark:bg-slate-800' ?>">
                                    <svg class="w-5 h-5 <?= $route['status'] === 'en_route' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate"><?= htmlspecialchars($route['name']) ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($route['phone']) ?></p>
                                </div>
                            </div>
                            <span class="flex-shrink-0 text-2xs px-2 py-0.5 rounded-full font-semibold
                                <?= $route['status'] === 'en_route' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' ?>">
                                <?= $route['status'] === 'en_route' ? 'En Route' : ucfirst($route['status']) ?>
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span><?= htmlspecialchars($route['name']) ?></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span><?= $route['student_count'] ?> students</span>
                            </div>
                            <div class="flex items-center gap-1.5 col-span-2 font-medium">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span><?= htmlspecialchars($route['driver_phone']) ?></span>
                            </div>

                            <!-- Live Speed Indicator Inside Card -->
                            <div class="col-span-2 mt-2 py-1.5 px-3 rounded-xl bg-slate-50 dark:bg-slate-950/20 border border-slate-100 dark:border-slate-800/40 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1 text-2xs font-semibold">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Live Speed:
                                </span>
                                <div class="flex items-center gap-1.5 font-bold">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                    </span>
                                    <span :class="{
                                              'text-slate-400 dark:text-slate-650': (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) == 0,
                                              'text-emerald-500 dark:text-emerald-400': (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) > 0 && (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) <= 60,
                                              'text-amber-500 dark:text-amber-400': (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) > 60 && (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) <= 80,
                                              'text-red-500 dark:text-red-400 animate-pulse': (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) > 80
                                          }"
                                          x-text="Math.round(routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0) + ' km/h'"></span>
                                </div>
                            </div>
                            
                            <!-- Live ETA & KM Indicator Inside Card -->
                            <div class="col-span-2 mt-1 py-1.5 px-3 rounded-xl bg-slate-50 dark:bg-slate-950/20 border border-slate-100 dark:border-slate-800/40 flex items-center justify-between text-xs"
                                 x-show="(routesList.find(r => r.id == <?= $route['id'] ?>)?.eta_minutes) !== null">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1 text-2xs font-semibold">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    To Campus:
                                </span>
                                <div class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                    <span x-text="(routesList.find(r => r.id == <?= $route['id'] ?>)?.eta_minutes ?? '—') + ' min'"></span>
                                    <span class="text-slate-400 font-normal text-2xs" x-text="'(' + (routesList.find(r => r.id == <?= $route['id'] ?>)?.remaining_km ?? '—') + ' km)'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Row -->
                        <div class="mt-3.5 grid grid-cols-2 gap-2">
                            <!-- View Map button -->
                            <button @click.stop="openGoogleMap(<?= $route['id'] ?>)"
                                    class="flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-750 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>View Map</span>
                            </button>

                            <button @click.stop="openUpdateModal(<?= $route['id'] ?>, '<?= htmlspecialchars($route['route_name']) ?>', <?= empty($route['lat']) ? 'null' : (float)$route['lat'] ?>, <?= empty($route['lng']) ? 'null' : (float)$route['lng'] ?>, (routesList.find(r => r.id == <?= $route['id'] ?>)?.speed ?? 0))"
                                    class="flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-xl text-xs font-semibold bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-800/40 hover:bg-brand-100 dark:hover:bg-brand-900/40 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Update
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($routes)): ?>
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 text-center">
                    <p class="text-sm text-slate-500">No routes configured yet.</p>
                    <a href="<?= url('transport/create') ?>" class="mt-2 inline-block text-sm text-brand-500 font-medium hover:underline">Create first route →</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Simulation Controls Removed -->
        </div>

        <!-- Map -->
        <div class="lg:col-span-3">
            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800/60 shadow-sm bg-white dark:bg-slate-900/40" style="height: 560px; position: relative;">
                <!-- Map container -->
                <div id="live-map" style="width:100%; height:100%; z-index:1;"></div>

                <!-- Map overlay controls -->
                <div class="absolute top-3 left-3 z-10 flex flex-col gap-2">
                    <button @click="fitAllMarkers()" title="Fit all buses"
                            class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-md text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </button>
                </div>

                <!-- Selected bus info overlay -->
                <div x-show="selectedRoute" x-cloak
                     class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 px-4 py-2.5 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur border border-slate-200 dark:border-slate-700 shadow-xl flex items-center gap-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></div>
                    <span class="font-semibold text-slate-800 dark:text-white" x-text="selectedRouteName"></span>
                    <span class="text-slate-500 dark:text-slate-400 text-xs" x-text="selectedRouteCoords"></span>
                    <button @click="selectedRoute = null" class="text-slate-400 hover:text-slate-700 dark:hover:text-white ml-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Location Modal -->
    <div x-show="updateModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="updateModal = false"></div>

        <!-- Modal Panel -->
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Update Bus Location & Speed</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="'Route: ' + updateRouteName"></p>
                </div>
                <button @click="updateModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Mini map for picking location -->
            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700" style="height:200px;">
                <div id="update-map" style="width:100%;height:100%;z-index:1;"></div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Click anywhere on the map to set the bus location
            </p>

            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Latitude</label>
                        <input type="number" step="0.000001" x-model="updateLat"
                               @input="moveUpdateMarker()"
                               class="w-full px-3 py-2 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Longitude</label>
                        <input type="number" step="0.000001" x-model="updateLng"
                               @input="moveUpdateMarker()"
                               class="w-full px-3 py-2 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Current Speed (km/h)</label>
                    <input type="number" min="0" max="120" step="1" x-model="updateSpeed"
                           class="w-full px-3 py-2 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button @click="updateModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-750 transition-all">
                    Cancel
                </button>
                <button @click="saveLocation()" :disabled="saving"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-sm disabled:opacity-60 flex items-center justify-center gap-2">
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="saving ? 'Saving…' : 'Save Location'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS + JS (CDN, no API key required) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Route data from PHP
const ROUTES_DATA = <?= json_encode(array_map(fn($r) => [
    'id'          => $r['id'],
    'name'        => $r['route_name'],
    'bus'         => $r['bus_number'],
    'driver'      => $r['driver_name'],
    'phone'       => $r['driver_phone'],
    'students'    => $r['student_count'],
    'status'      => $r['status'] ?? 'inactive',
    'lat'         => isset($r['lat']) ? (float)$r['lat'] : null,
    'lng'         => isset($r['lng']) ? (float)$r['lng'] : null,
    'speed'       => (float)($r['speed'] ?? 0.0),
    'eta_minutes' => isset($r['eta_minutes']) ? (int)$r['eta_minutes'] : null,
    'remaining_km' => isset($r['remaining_km']) ? (float)$r['remaining_km'] : null,
    'updated_at'  => $r['updated_at'] ?? null,
], $routes), JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE) ?>;

const UPDATE_URL  = '<?= url('transport/{id}/location') ?>';
const POLL_URL    = '<?= url('transport/live-data') ?>';

function liveTracking() {
    return {
        // State
        selectedRoute:      null,
        selectedRouteName:  '',
        selectedRouteCoords:'',
        updateModal:        false,
        updateRouteId:      null,
        updateRouteName:    '',
        updateLat:          13.0827,
        updateLng:          80.2707,
        updateSpeed:        0,
        saving:             false,
        lastUpdated:        '—',
        countdown:          30,
        countdownTimer:     null,
        pollTimer:          null,

        // Live speeds
        routesList:         [],

        // Leaflet instances
        map:          null,
        markers:      {},
        updateMap:    null,
        updateMarker: null,

        init() {
            this.routesList = [...ROUTES_DATA];
            this.$nextTick(async () => {
                this.initMap();
                this.startPolling();
                await this.fetchLiveData();
                this.updateLastUpdated();
                // Removed speed simulation start
            });
        },

        // ── Map init ────────────────────────────────────────────────────────────
        initMap() {
            // Determine center: average of all routes with valid coords
            const validRoutes = ROUTES_DATA.filter(r => r.lat !== null && r.lng !== null && r.status === 'en_route');
            const center = validRoutes.length
                ? [
                    validRoutes.reduce((s, r) => s + parseFloat(r.lat), 0) / validRoutes.length,
                    validRoutes.reduce((s, r) => s + parseFloat(r.lng), 0) / validRoutes.length,
                  ]
                : [<?= (float)($campusLat ?? 23.0225) ?: 23.0225 ?>, <?= (float)($campusLng ?? 72.5714) ?: 72.5714 ?>];

            this.map = L.map('live-map', { zoomControl: true }).setView(center, 12);

            // Tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(this.map);

            // Add School Marker
            const schoolIcon = L.divIcon({
                html: `<div style="width:36px;height:36px;background:#4f46e5;border-radius:8px;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></div>`,
                className: '', iconSize: [36, 36], iconAnchor: [18, 18]
            });
            L.marker([<?= $campusLat ?>, <?= $campusLng ?>], { icon: schoolIcon }).bindPopup('<div style="font-weight:bold;font-size:14px;">PSNF Main Campus</div>').addTo(this.map);

            // Add markers for active routes
            this.routesList.forEach(r => {
                if (r.status === 'en_route' && r.lat !== null && r.lng !== null) {
                    this.addOrUpdateMarker(r);
                }
            });

            if (this.routesList.length > 1) this.fitAllMarkers();
        },

        busIcon(status) {
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
        },

        addOrUpdateMarker(route) {
            const speedColor = route.speed === 0 ? '#64748b' : (route.speed <= 60 ? '#10b981' : (route.speed <= 80 ? '#f59e0b' : '#ef4444'));
            const speedText = route.speed === 0 ? 'Stopped' : `${Math.round(route.speed)} km/h`;
            const popup = `
                <div style="min-width:180px;font-family:Inter,sans-serif;color:#1e293b;">
                    <div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#0f172a;">${route.name}</div>
                    
                    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#475569;margin-bottom:6px;">
                        <svg style="width:14px;height:14px;color:#64748b;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span><strong>Driver:</strong> ${route.driver}</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#475569;margin-bottom:6px;">
                        <svg style="width:14px;height:14px;color:#64748b;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span><strong>Phone:</strong> ${route.phone}</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#475569;margin-bottom:8px;">
                        <svg style="width:14px;height:14px;color:#64748b;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span><strong>Students:</strong> ${route.students}</span>
                    </div>

                    <div style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;display:inline-block;
                             background:${route.status === 'en_route' ? '#d1fae5' : '#f1f5f9'};
                             color:${route.status === 'en_route' ? '#065f46' : '#64748b'}; border: 1px solid ${route.status === 'en_route' ? '#a7f3d0' : '#e2e8f0'};">
                            ${route.status === 'en_route' ? 'EN ROUTE' : route.status.toUpperCase()}
                        </span>
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;display:flex;align-items:center;gap:4px;
                             background:${route.speed === 0 ? '#f1f5f9' : (route.speed <= 60 ? '#d1fae5' : (route.speed <= 80 ? '#fef3c7' : '#fee2e2'))};
                             color:${speedColor}; border: 1px solid ${route.speed === 0 ? '#e2e8f0' : (route.speed <= 60 ? '#a7f3d0' : (route.speed <= 80 ? '#fde68a' : '#fca5a5'))};">
                            <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            ${speedText}
                        </span>
                    </div>
                    ${route.updated_at ? `<div style="margin-top:8px;font-size:10px;color:#94a3b8;">Updated: ${route.updated_at}</div>` : ''}
                </div>`;

            if (this.markers[route.id]) {
                this.markers[route.id].setLatLng([route.lat, route.lng])
                                      .setIcon(this.busIcon(route.status))
                                      .setPopupContent(popup);
            } else {
                const marker = L.marker([route.lat, route.lng], { icon: this.busIcon(route.status) })
                    .bindPopup(popup)
                    .addTo(this.map);
                this.markers[route.id] = marker;
            }
        },

        fitAllMarkers() {
            if (!this.map) return;
            const pts = Object.values(this.markers).map(m => m.getLatLng());
            if (pts.length === 1) {
                this.map.setView(pts[0], 14);
            } else if (pts.length > 1) {
                this.map.fitBounds(L.latLngBounds(pts), { padding: [40, 40] });
            }
        },

        selectRoute(id) {
            this.selectedRoute = id;
            const r = this.routesList.find(x => x.id == id);
            if (r) {
                this.selectedRouteName  = r.name + ' • ' + r.bus;
                this.selectedRouteCoords= `${r.lat.toFixed(5)}, ${r.lng.toFixed(5)}`;
                if (this.map && this.markers[id]) {
                    this.map.flyTo([r.lat, r.lng], 15, { duration: 1 });
                    this.markers[id].openPopup();
                }
            }
        },

        openGoogleMap(id) {
            const r = this.routesList.find(x => x.id == id);
            const lat = r ? r.lat : 13.0827;
            const lng = r ? r.lng : 80.2707;
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        },

        // ── Update Location Modal ────────────────────────────────────────────────
        openUpdateModal(id, name, lat, lng, speed) {
            this.updateRouteId   = id;
            this.updateRouteName = name;
            this.updateLat       = lat;
            this.updateLng       = lng;
            this.updateSpeed     = Math.round(speed || 0);
            this.updateModal     = true;

            this.$nextTick(() => {
                setTimeout(() => {
                    if (!this.updateMap) {
                        this.updateMap = L.map('update-map', { zoomControl: false }).setView([lat, lng], 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.updateMap);

                        this.updateMarker = L.marker([lat, lng], { draggable: true, icon: this.busIcon('en_route') }).addTo(this.updateMap);
                        this.updateMarker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.updateLat = parseFloat(pos.lat.toFixed(6));
                            this.updateLng = parseFloat(pos.lng.toFixed(6));
                        });

                        this.updateMap.on('click', (e) => {
                            this.updateLat = parseFloat(e.latlng.lat.toFixed(6));
                            this.updateLng = parseFloat(e.latlng.lng.toFixed(6));
                            this.updateMarker.setLatLng(e.latlng);
                        });
                    } else {
                        this.updateMap.setView([lat, lng], 14);
                        this.updateMarker.setLatLng([lat, lng]);
                        this.updateMap.invalidateSize();
                    }
                }, 150);
            });
        },

        moveUpdateMarker() {
            if (this.updateMarker) {
                const lat = parseFloat(this.updateLat);
                const lng = parseFloat(this.updateLng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    this.updateMarker.setLatLng([lat, lng]);
                    this.updateMap.setView([lat, lng], this.updateMap.getZoom());
                }
            }
        },

        async saveLocation() {
            this.saving = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const url  = '<?= url('transport') ?>/' + this.updateRouteId + '/location';
                const res  = await fetch(url, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body:    JSON.stringify({ lat: this.updateLat, lng: this.updateLng, speed: parseFloat(this.updateSpeed) }),
                });
                const json = await res.json();
                if (json.success) {
                    // Update local data & marker
                    const r = ROUTES_DATA.find(x => x.id == this.updateRouteId);
                    if (r) { 
                        r.lat = this.updateLat; 
                        r.lng = this.updateLng; 
                        r.speed = this.updateSpeed; 
                        r.eta_minutes = json.eta_minutes;
                        r.remaining_km = json.remaining_km;
                    }
                    
                    const listRoute = this.routesList.find(x => x.id == this.updateRouteId);
                    if (listRoute) { 
                        listRoute.lat = this.updateLat; 
                        listRoute.lng = this.updateLng; 
                        listRoute.speed = this.updateSpeed; 
                        listRoute.eta_minutes = json.eta_minutes;
                        listRoute.remaining_km = json.remaining_km;
                    }
                    
                    this.addOrUpdateMarker({
                        ...this.routesList.find(x => x.id == this.updateRouteId),
                        lat: this.updateLat,
                        lng: this.updateLng,
                        speed: this.updateSpeed,
                        eta_minutes: json.eta_minutes,
                        remaining_km: json.remaining_km
                    });
                    this.updateLastUpdated();
                    this.updateModal = false;
                } else {
                    alert(json.message || 'Failed to update location.');
                }
            } catch (e) {
                alert('Error saving location: ' + e.message);
            } finally {
                this.saving = false;
            }
        },

        // ── WebSocket Real-Time Tracking ─────────────────────────────────────────
        startPolling() {
            // No more countdown timer! Real-time WebSocket connection
            const wsUrl = 'ws://' + window.location.hostname + ':8080';
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('Connected to real-time WebSocket server');
                // Fetch initial data once on load
                this.fetchLiveData();
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    if (data.event === 'gps_update') {
                        this.handleRealTimeUpdate(data);
                    }
                } catch (e) {}
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected. Reconnecting in 5s...');
                setTimeout(() => this.startPolling(), 5000);
            };
        },
        
        handleRealTimeUpdate(r) {
            const id = parseInt(r.route_id);
            const lat = parseFloat(r.lat);
            const lng = parseFloat(r.lng);
            const speed = parseFloat(r.speed);
            const status = r.status;
            
            const existing = ROUTES_DATA.find(x => x.id == id);
            if (existing) {
                existing.lat = lat;
                existing.lng = lng;
                existing.status = status;
                existing.speed = speed;
                existing.eta_minutes = r.eta_minutes;
                existing.remaining_km = r.remaining_km;
            }
            
            const listRoute = this.routesList.find(x => x.id == id);
            if (listRoute) {
                listRoute.lat = lat;
                listRoute.lng = lng;
                listRoute.status = status;
                listRoute.speed = speed;
                listRoute.eta_minutes = r.eta_minutes;
                listRoute.remaining_km = r.remaining_km;
            }
            
            // Only show marker if active
            if (status === 'en_route' && lat && lng) {
                this.addOrUpdateMarker(listRoute || existing);
                
                // Auto follow selected driver
                if (this.selectedRoute == id && this.map) {
                    // Smooth pan
                    this.map.panTo([lat, lng], {animate: true, duration: 1.0});
                    this.selectedRouteCoords = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
            } else {
                if (this.markers[id]) {
                    this.map.removeLayer(this.markers[id]);
                    delete this.markers[id];
                }
            }
            this.updateLastUpdated();
        },

        async fetchLiveData() {
            try {
                const res  = await fetch('<?= url('transport/live-data') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.routes) {
                    data.routes.forEach(r => {
                        this.handleRealTimeUpdate({
                            route_id: r.id,
                            lat: r.lat,
                            lng: r.lng,
                            status: r.status,
                            speed: r.speed,
                            eta_minutes: r.eta_minutes,
                            remaining_km: r.remaining_km
                        });
                    });
                }
            } catch (e) { /* silent */ }
        },

        // ── Speed simulation removed ──────────────────────────────────────────

        updateLastUpdated() {
            const now = new Date();
            this.lastUpdated = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },
    };
}
</script>
