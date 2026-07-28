<?php
$layout    = 'app';
$pageTitle = 'Routes & Stops Management';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Routes']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Routes Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage route codes, assigned drivers, buses, pickup windows & stops</p>
        </div>
        <a href="<?= url('transport/create') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Create Route</a>
    </div>

    <!-- Routes Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Route Name</th>
                    <th class="p-4">Vehicle Bus</th>
                    <th class="p-4">Assigned Driver</th>
                    <th class="p-4">Morning Pickup</th>
                    <th class="p-4">Evening Drop</th>
                    <th class="p-4">Students</th>
                    <th class="p-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php foreach ($routes as $r): ?>
                <tr>
                    <td class="p-4 font-bold text-slate-900 dark:text-white"><?= e($r['route_name']) ?></td>
                    <td class="p-4 font-mono font-bold text-indigo-500"><?= e($r['bus_number'] ?? 'MH-12-AB-5678') ?></td>
                    <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($r['driver_name'] ?? 'Rajesh Patel') ?></td>
                    <td class="p-4 font-mono text-emerald-500 font-bold">07:30 AM</td>
                    <td class="p-4 font-mono text-indigo-500 font-bold">04:30 PM</td>
                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white"><?= (int)$r['student_count'] ?> Students</td>
                    <td class="p-4 text-right">
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($r['status'] ?? 'Active') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
