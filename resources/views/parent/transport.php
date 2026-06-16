<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header info -->
    <div class="p-5 rounded-2xl border bg-slate-900/30 border-white/5">
        <h3 class="text-base font-bold text-white">Bus Transport Tracking</h3>
        <p class="text-xs text-slate-500">Real-time bus route status, driver profiles, and pickup coordination</p>
    </div>

    <?php if (!$transport): ?>
    <div class="p-10 rounded-2xl border border-dashed border-slate-800 bg-slate-900/10 text-center">
        <svg class="w-8 h-8 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>
        <p class="text-xs text-slate-500">This student is not registered on any school bus route.</p>
    </div>
    <?php else: ?>
    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Bus Status and Progress Tracker (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Route Details & Visual Map Simulation -->
            <div class="rounded-2xl border border-white/5 bg-slate-900/20 p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-bold text-white"><?= e($transport['route_name']) ?></h4>
                        <p class="text-xs text-slate-500">Bus Number: <span class="text-indigo-400 font-semibold"><?= e($transport['bus_number']) ?></span></p>
                    </div>
                    <?php
                    $isEnRoute = $transport['status'] === 'en_route';
                    $statusColor = $isEnRoute ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : 'text-slate-500 bg-slate-900 border-slate-800';
                    ?>
                    <span class="px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-1.5 <?= $statusColor ?>">
                        <?php if ($isEnRoute): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <?php endif; ?>
                        <?= $isEnRoute ? 'En Route' : 'Inactive' ?>
                    </span>
                </div>

                <!-- Bus Route Progress Tracker (Simulated Map Route) -->
                <div class="p-6 rounded-xl border border-slate-800/80 bg-slate-950/40 relative overflow-hidden">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">Bus Stop Schedule & Tracking</p>

                    <div class="relative pl-8 space-y-6">
                        <!-- Vertical timeline bar -->
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-800"></div>

                        <!-- Stops -->
                        <div class="relative flex items-center justify-between gap-4">
                            <!-- Node dot -->
                            <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-700 border-4 border-slate-950"></div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-400">Stop 1: Society Main Gate</h5>
                                <p class="text-[10px] text-slate-500">Scheduled: 08:15 AM &nbsp;|&nbsp; Actual: 08:16 AM</p>
                            </div>
                            <span class="text-[9px] font-semibold text-emerald-400 uppercase tracking-wider">Passed</span>
                        </div>

                        <div class="relative flex items-center justify-between gap-4">
                            <!-- Active node dot (flashing) -->
                            <div class="absolute -left-8 w-4 h-4 rounded-full bg-indigo-500 border-4 border-slate-950 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                                <span class="absolute w-full h-full rounded-full bg-indigo-400 animate-ping opacity-70"></span>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-white">Stop 2: Western Express Crossroad</h5>
                                <p class="text-[10px] text-slate-400">Scheduled: 08:30 AM &nbsp;|&nbsp; Est: 08:32 AM</p>
                            </div>
                            <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-wider animate-pulse">Current Position</span>
                        </div>

                        <div class="relative flex items-center justify-between gap-4">
                            <!-- Node dot -->
                            <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-700 border-4 border-slate-950"></div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500">Stop 3: Link Road Flyover</h5>
                                <p class="text-[10px] text-slate-500">Scheduled: 08:42 AM</p>
                            </div>
                            <span class="text-[9px] font-semibold text-slate-600 uppercase tracking-wider">Pending</span>
                        </div>

                        <div class="relative flex items-center justify-between gap-4">
                            <!-- Node dot -->
                            <div class="absolute -left-7 w-2.5 h-2.5 rounded-full bg-slate-700 border-4 border-slate-950"></div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500">Stop 4: PSNF Main School</h5>
                                <p class="text-[10px] text-slate-500">Scheduled: 08:55 AM</p>
                            </div>
                            <span class="text-[9px] font-semibold text-slate-600 uppercase tracking-wider">School</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column: Driver profile card & Contact details (1 col wide) -->
        <div class="space-y-6">

            <!-- Driver profile card -->
            <div class="rounded-2xl border border-white/5 bg-slate-900/20 p-6 space-y-5">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Driver Contact Profile</h4>

                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 border border-white/10 flex items-center justify-center text-white text-base font-extrabold flex-shrink-0">
                        <?= strtoupper(substr($transport['driver_name'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h5 class="text-sm font-bold text-slate-200 truncate"><?= e($transport['driver_name']) ?></h5>
                        <p class="text-xs text-slate-500">Senior Bus Marshall</p>
                    </div>
                </div>

                <div class="bg-slate-950/40 p-4 rounded-xl border border-slate-800/80 space-y-3">
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Registered Driver Phone</span>
                        <p class="text-xs font-semibold text-slate-200 mt-0.5"><?= e($transport['driver_phone']) ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Assigned Pickup Node</span>
                        <p class="text-xs font-semibold text-indigo-300 mt-0.5"><?= e($transport['pickup_point']) ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Expected Pickup Time</span>
                        <p class="text-xs font-semibold text-slate-200 mt-0.5"><?= date('h:i A', strtotime($transport['pickup_time'])) ?></p>
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
