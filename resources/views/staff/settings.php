<?php
$layout    = 'app';
$pageTitle = 'Staff Workspace Configuration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Settings']];
ob_start();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure working days, shift policies, grace windows, and payroll rules per Academic Year.</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Academic Year:</label>
            <select onchange="window.location.href='<?= url('staff/settings?year_id=') ?>' + this.value" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y['id'] ?>" <?= (int)$y['id'] === (int)$selectedYearId ? 'selected' : '' ?>><?= e($y['year_name']) ?> <?= $y['status'] === 'current' ? '(Current)' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <form method="POST" action="<?= url('staff/settings') ?>" class="space-y-6">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="academic_year_id" value="<?= (int)$selectedYearId ?>">

        <?php
            $wdMap = json_decode($shift['working_days_json'] ?? '{}', true) ?: [];
        ?>

        <!-- Working Days Per Month -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Working Days Per Month</span>
                    <p class="text-3xs text-slate-400 mt-0.5">Set the official working days for each month. This is the single source of truth for attendance counts, salary calculations, and payroll processing.</p>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-3xs font-bold border border-indigo-200 dark:border-indigo-800/50">Source of Truth</span>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <?php
                        $monthKey = sprintf('%02d', $m);
                        $monthName = date('F', mktime(0, 0, 0, $m, 1));
                        $daysInThisMonth = cal_days_in_month(CAL_GREGORIAN, $m, (int)date('Y'));
                        $weekdays = 0;
                        for ($d = 1; $d <= $daysInThisMonth; $d++) {
                            $w = date('w', mktime(0, 0, 0, $m, $d, (int)date('Y')));
                            if ($w != 0) $weekdays++;
                        }
                        $saved = (int)($wdMap[$monthKey] ?? 0);
                    ?>
                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 space-y-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-2xs uppercase tracking-wider"><?= $monthName ?></label>
                        <input type="number" name="working_days_<?= $monthKey ?>" min="0" max="31"
                               value="<?= $saved > 0 ? $saved : '' ?>"
                               placeholder="<?= $weekdays ?>"
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono text-center font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                        <p class="text-3xs text-slate-400 text-center">Auto: <span class="font-semibold text-slate-500"><?= $weekdays ?> days</span></p>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/30 flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-3xs text-indigo-700 dark:text-indigo-300 leading-relaxed">
                        <strong>How it works:</strong> Leave a field empty or set to 0 to auto-calculate as all weekdays (Mon-Sat) in that month. The value you set here will be used for <strong>all</strong> salary calculations, attendance summaries, and payroll processing.
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Policy + Deductions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Shift Policy -->
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2">
                    Shift Policy
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Shift Start Time</label>
                        <input type="text" name="start_time" value="<?= e($shift['start_time'] ?? '09:00:00') ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Grace Window (Minutes)</label>
                        <input type="number" name="grace_minutes" value="<?= (int)($shift['grace_minutes'] ?? 15) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                        <span class="text-slate-500 text-[10px] mt-1 block">Custom shift employees get a 10 min grace window automatically.</span>
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Late Penalty After</label>
                        <input type="text" name="late_after" value="<?= e($shift['late_after'] ?? '09:16:00') ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Half-Day After</label>
                        <input type="text" name="half_day_after" value="<?= e($shift['half_day_after'] ?? '12:00:00') ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>
                </div>
            </div>

            <!-- Deductions & Lecture Settings -->
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2">
                    Deductions & Lecture Settings
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Max Late Occurrences Before Salary Cut</label>
                        <input type="number" name="late_limit_count" value="<?= (int)($shift['late_limit_count'] ?? 3) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Late Penalty Deduction (%)</label>
                        <input type="number" step="0.01" name="late_deduction_percent" value="<?= (float)($shift['late_deduction_percent'] ?? 10.00) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Half-Day Deduction (%)</label>
                        <input type="number" step="0.01" name="half_day_deduction_percent" value="<?= (float)($shift['half_day_deduction_percent'] ?? 50.00) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Lecture-Wise Grace Window (Minutes)</label>
                        <input type="number" name="lec_grace_minutes" value="<?= (int)($shift['lec_grace_minutes'] ?? 5) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-brand-500 font-mono">
                        <span class="text-slate-500 text-[10px] mt-1 block">Configures Academics lecture attendance grace limits (no salary effect).</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-semibold text-white shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all duration-200 hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save All Settings
            </button>
        </div>
    </form>

</div>

<?php
$content = ob_get_clean();
?>
