<?php
$layout    = 'app';
$pageTitle = 'Staff Workspace Configuration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Settings']];
ob_start();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Workspace Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure default Shift Templates, Grace Windows, Lecture settings, and Payroll Deduction Rules</p>
        </div>
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="<?= url('staff/settings') ?>" class="space-y-6">
        <?= \Core\View::csrf() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Shift Policy Settings -->
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2">
                    Shift Policy Template
                </h3>
                
                    <?php
                        $wdMap = json_decode($shift['working_days_json'] ?? '{}', true) ?: [];
                        $months = [
                            '01'=>'Jan', '02'=>'Feb', '03'=>'Mar', '04'=>'Apr', '05'=>'May', '06'=>'Jun',
                            '07'=>'Jul', '08'=>'Aug', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dec'
                        ];
                    ?>
                    <div>
                        <label class="block text-slate-400 mb-2 font-medium">Working Days Per Month (0 for Auto-Calculate)</label>
                        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <?php foreach ($months as $mNum => $mName): ?>
                            <div>
                                <label class="block text-2xs text-slate-500 mb-1"><?= $mName ?></label>
                                <input type="number" name="working_days_<?= $mNum ?>" value="<?= (int)($wdMap[$mNum] ?? 0) ?>" 
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500 font-mono text-center">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <span class="text-slate-500 text-[10px] mt-2 block">Set any month to 0 to auto-calculate (Total Days - Sundays) when running payroll.</span>
                    </div>

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

            <!-- Payroll & Lecture Attendance Rules -->
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
                Save Settings
            </button>
        </div>
    </form>

</div>

<?php
$content = ob_get_clean();
?>
