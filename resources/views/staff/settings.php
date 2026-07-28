<?php
$layout    = 'app';
$pageTitle = 'Staff Workspace Configuration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Settings']];
ob_start();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Workspace Settings</h1>
        <p class="text-xs text-slate-500 mt-0.5">Tab-based administration for Shift Templates, Attendance Policies, Working Hours & Overtime Rules</p>
    </div>

    <!-- Active Shift Configuration Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Default Staff Shift Policy Template</h3>
            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500">ACTIVE SHIFT</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-mono">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Shift Start</span>
                <span class="font-bold text-slate-900 dark:text-white"><?= e($shift['start_time'] ?? '09:00:00') ?></span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Grace Window</span>
                <span class="font-bold text-indigo-500"><?= (int)($shift['grace_minutes'] ?? 15) ?> minutes</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Late Penalty After</span>
                <span class="font-bold text-amber-500"><?= e($shift['late_after'] ?? '09:16:00') ?></span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Half-Day After</span>
                <span class="font-bold text-red-500"><?= e($shift['half_day_after'] ?? '12:00:00') ?></span>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
