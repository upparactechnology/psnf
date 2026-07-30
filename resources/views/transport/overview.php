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
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Active Drivers</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $activeDrivers ?></p>
            <span class="text-2xs text-indigo-500 font-medium">Currently En Route</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Total Drivers</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalDrivers ?></p>
            <span class="text-2xs text-purple-500 font-medium">Licensed Operators</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Students Using Transport</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalStudents ?></p>
            <span class="text-2xs text-amber-500 font-medium">Assigned Commuters</span>
        </div>
    </div>

    <!-- Active Drivers Cards -->
    <div class="space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active Drivers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($routes as $r): ?>
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <?php if ($r['route_status'] === 'en_route'): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500 uppercase font-mono">EN ROUTE</span>
                        <?php else: ?>
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-slate-500/10 text-slate-500 uppercase font-mono"><?= strtoupper($r['route_status']) ?></span>
                        <?php endif; ?>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mt-1"><?= e($r['route_name'] ?? 'Driver') ?></h4>
                        <p class="text-2xs font-mono text-slate-400"><?= e($r['phone']) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-extrabold text-slate-900 dark:text-white"><?= (int)$r['student_count'] ?></span>
                        <span class="text-2xs text-slate-400 block font-medium">Students Assigned</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tomorrow's Bus Transport Reflections -->
    <div class="space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Tomorrow's Confirmed Bus Commuters (<?= date('d M Y', strtotime($tomorrowStr)) ?>)</h3>
        <p class="text-xs text-slate-500 mb-2">Reflects real-time attendance declarations from the Parent Portal for tomorrow's classes.</p>
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold">Student Name</th>
                        <th class="p-3 font-semibold">Class/Section</th>
                        <th class="p-3 font-semibold">Driver Name</th>
                        <th class="p-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    <?php if (empty($tomorrowBusStudents)): ?>
                    <tr>
                        <td colspan="4" class="p-6 text-center text-slate-500">No students have confirmed bus transport for tomorrow yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($tomorrowBusStudents as $student): ?>
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="p-3 font-medium text-slate-800 dark:text-slate-200">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td class="p-3 text-slate-600 dark:text-slate-400">
                            <?= e($student['class'] . ' (' . $student['section'] . ')') ?>
                        </td>
                        <td class="p-3">
                            <span class="inline-block px-2.5 py-1 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-semibold text-xs border border-indigo-100 dark:border-indigo-500/20">
                                <?= e($student['driver_name']) ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                Confirmed
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
