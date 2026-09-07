<?php
$layout    = 'app';
$pageTitle = 'School & Public Holidays';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Holidays Calendar']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Panel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Holidays Calendar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure public and local school holidays. Holidays are counted as paid salary days and exclude check-in demands.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Add Holiday Form -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 space-y-4 h-fit text-xs">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add New Holiday</h3>
            
            <form action="<?= url('payroll/holidays') ?>" method="POST" class="space-y-3">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Holiday Date</label>
                    <input type="date" name="holiday_date" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Holiday Description / Name</label>
                    <input type="text" name="name" placeholder="e.g. Independence Day" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Category Type</label>
                    <select name="type" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="public">Public / National Holiday</option>
                        <option value="school">Local School Holiday</option>
                        <option value="festive">Religious / Festive Off</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Paid Status</label>
                    <select name="is_paid" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="1">Paid Salary Day (Standard)</option>
                        <option value="0">Unpaid Non-Working Day</option>
                    </select>
                </div>
                
                <?php if (has_permission('create_holidays_calendar')): ?>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 font-semibold text-white transition-all shadow-sm">Save Holiday</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Holidays List Table -->
        <div class="md:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Registered Holidays</span>
            </div>
            
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-2xs border-b border-slate-100 dark:border-slate-850">
                    <tr>
                        <th class="p-4">Holiday Date</th>
                        <th class="p-4">Name / Title</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Salary Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($holidays)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">No school or public holidays configured for this academic period.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($holidays as $h): ?>
                        <tr>
                            <td class="p-4 font-mono font-bold text-slate-900 dark:text-white"><?= date('d M Y', strtotime($h['holiday_date'])) ?></td>
                            <td class="p-4 text-slate-800 dark:text-slate-200 font-semibold"><?= e($h['name']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-3xs font-bold border uppercase <?= $h['type'] === 'public' ? 'border-indigo-500/20 bg-indigo-500/10 text-indigo-500' : 'border-amber-500/20 bg-amber-500/10 text-amber-600 dark:text-amber-400' ?>">
                                    <?= e($h['type']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <?php if ($h['is_paid']): ?>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">Paid Day</span>
                                <?php else: ?>
                                    <span class="text-rose-500 font-bold">Unpaid Day</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <?php if (has_permission('delete_holidays_calendar')): ?>
                                <form action="<?= url('payroll/holidays/' . $h['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Remove holiday from registry?')">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="text-2xs font-bold text-rose-500 hover:text-rose-600 transition-colors">Delete</button>
                                </form>
                                <?php endif; ?>
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
