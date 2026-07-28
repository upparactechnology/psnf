<?php
$layout    = 'app';
$pageTitle = 'Vehicles Fleet Management';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Vehicles']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Vehicles Fleet Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage school buses, mini-buses, passenger capacity, registration & driver links</p>
        </div>
        <button class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Vehicle</button>
    </div>

    <!-- Vehicles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($vehicles as $v): ?>
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-indigo-500"><?= e($v['vehicle_number']) ?></span>
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($v['status']) ?></span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($v['registration_number']) ?></h3>
            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 font-mono">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Vehicle Type:</span> <span class="font-bold"><?= e($v['vehicle_type']) ?></span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Capacity:</span> <span class="font-bold"><?= (int)$v['capacity'] ?> Passengers</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Assigned Driver:</span> <span class="font-bold"><?= e($v['driver_name'] ?? 'Rajesh Patel') ?></span></div>
                <div class="flex justify-between py-1"><span class="text-slate-400">Assigned Route:</span> <span class="font-bold text-indigo-500"><?= e($v['route_name'] ?? 'Route 5') ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
