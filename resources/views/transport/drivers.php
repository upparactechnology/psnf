<?php
$layout    = 'app';
$pageTitle = 'Drivers Directory';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Drivers']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Transport Drivers Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Licensed bus drivers, phone numbers, license numbers & assigned vehicles</p>
        </div>
        <?php if (has_permission('create_transport_drivers')): ?>
        <a href="<?= url('users/create?redirect_to=/transport/drivers') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Driver</a>
        <?php endif; ?>
    </div>

    <!-- Drivers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($drivers as $d): ?>
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-base flex items-center justify-center shadow-md">
                    <?= strtoupper(substr($d['first_name'], 0, 1)) ?>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></h3>
                    <p class="text-xs text-indigo-500 font-mono font-bold"><?= e($d['emp_code']) ?> • <?= e($d['phone']) ?></p>
                </div>
            </div>

            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 font-mono">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">License No:</span> <span class="font-bold"><?= e($d['license_number']) ?></span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">License Expiry:</span> <span class="font-bold text-emerald-500"><?= e($d['license_expiry']) ?></span></div>
                <div class="flex justify-between py-1"><span class="text-slate-400">Assigned Bus:</span> <span class="font-bold text-indigo-500"><?= e($d['vehicle_number'] ?? 'BUS-01') ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
