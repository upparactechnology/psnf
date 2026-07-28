<?php
$layout    = 'app';
$pageTitle = 'Transport Overview Dashboard';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace']];
ob_start();
?>

<div class="space-y-8 max-w-6xl mx-auto">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Transport Management Workspace</h1>
            <p class="text-xs text-slate-500 mt-0.5">Centralized Fleet Management, Active Routes, Drivers & Student Pickups</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('transport/routes') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Route</a>
            <a href="<?= url('transport/live-tracking') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition-all">📍 Live Tracking</a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Total Vehicles</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalVehicles ?></p>
            <span class="text-2xs text-emerald-500 font-medium">Buses & Vans Active</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Active Routes</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $activeRoutes ?></p>
            <span class="text-2xs text-indigo-500 font-medium">Mapped Routes</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Drivers</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalDrivers ?></p>
            <span class="text-2xs text-purple-500 font-medium">Licensed Operators</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Students Using Transport</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalStudents ?></p>
            <span class="text-2xs text-amber-500 font-medium">Assigned Commuters</span>
        </div>
    </div>

    <!-- Active Routes Cards -->
    <div class="space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active Fleet Routes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($routes as $r): ?>
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500 uppercase font-mono">EN ROUTE</span>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mt-1"><?= e($r['route_name']) ?></h4>
                        <p class="text-2xs font-mono text-slate-400"><?= e($r['bus_number'] ?? 'MH-12-AB-5678') ?></p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-extrabold text-slate-900 dark:text-white"><?= (int)$r['student_count'] ?></span>
                        <span class="text-2xs text-slate-400 block font-medium">Students Assigned</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs text-slate-600 dark:text-slate-300">
                    <div><span class="text-slate-400">Driver:</span> <span class="font-bold"><?= e($r['driver_name'] ?? 'Rajesh Patel') ?></span></div>
                    <div><span class="text-slate-400">Phone:</span> <span class="font-mono font-bold text-indigo-500"><?= e($r['driver_phone'] ?? '+91-9123456789') ?></span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
