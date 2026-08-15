<?php
$layout    = 'app';
$pageTitle = 'Payroll & Shift Rules Settings';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Settings']];
ob_start();
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Panel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Payroll Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure default work hours, late tolerance buffers, penalty triggers, and deductions percentages.</p>
        </div>
    </div>

    <!-- Settings Card -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Global Shift Policies & Penalty Allocations</span>
        </div>
        
        <form action="<?= url('payroll/settings/save') ?>" method="POST" class="p-6 space-y-6">
            <?= \Core\View::csrf() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Standard Shift start time -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Standard Start Time</label>
                    <input type="time" name="start_time" value="<?= e($shift['start_time'] ?? '09:00:00') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Employees must scan in before this boundary (unless a personalized override is configured).</span>
                </div>

                <!-- Grace Minutes -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Grace Period (Minutes)</label>
                    <input type="number" name="grace_minutes" value="<?= (int)($shift['grace_minutes'] ?? 15) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Buffer minutes allowed before marking as late. (e.g. 15 minutes makes 09:15:00 late penalty start).</span>
                </div>

                <!-- Late Scan Boundary -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Late Calculation Boundary</label>
                    <input type="time" name="late_after" value="<?= e($shift['late_after'] ?? '09:16:00') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>

                <!-- Half Day Boundary -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Half-Day Boundary Threshold</label>
                    <input type="time" name="half_day_after" value="<?= e($shift['half_day_after'] ?? '12:00:00') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Check-ins past this boundary automatically count as a Half-Day status.</span>
                </div>

                <!-- Late Count limit -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Late Limit Threshold (Occurrences)</label>
                    <input type="number" name="late_limit_count" value="<?= (int)($shift['late_limit_count'] ?? 3) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Accumulating this count of non-exempt late marks triggers a half-day salary penalty.</span>
                </div>

                <!-- Late Penalty Rate -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Late Exceeded Deduction (%)</label>
                    <input type="number" step="0.01" name="late_deduction_percent" value="<?= number_format((float)($shift['late_deduction_percent'] ?? 10.00), 2) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Percentage of basic salary deducted once the late limit is breached (legacy flat setting).</span>
                </div>

                <!-- Half-Day Penalty Rate -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Half-Day Deduction (%)</label>
                    <input type="number" step="0.01" name="half_day_deduction_percent" value="<?= number_format((float)($shift['half_day_deduction_percent'] ?? 50.00), 2) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    <span class="text-3xs text-slate-400 mt-1 block">Deduction applied to a single day's salary rate for every half-day status recorded.</span>
                </div>
            </div>
            
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-end gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 font-semibold text-white transition-all shadow-sm">Save Parameters & Policies</button>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
