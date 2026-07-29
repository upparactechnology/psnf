<?php
$layout    = 'app';
$pageTitle = 'Payroll Engine & Salary Generation';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Payroll']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Payroll Engine & Salary Slips</h1>
            <p class="text-xs text-slate-500 mt-0.5">Automated attendance-driven salary structures, deduction formulas & payslip generator</p>
        </div>
        <form action="<?= url('staff/payroll/run') ?>" method="POST" class="flex items-center gap-2">
            <?= \Core\View::csrf() ?>
            <input type="month" name="month" value="<?= date('Y-m') ?>" required class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-slate-900 dark:text-white">
            <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-all shadow-sm">💰 Run Payroll</button>
        </form>
    </div>

    <!-- Active Employees Salary Structures -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Configured Employee Salary Structures</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <?php foreach ($employees as $e): ?>
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 dark:text-white"><?= e($e['first_name'] . ' ' . $e['last_name']) ?></span>
                    <span class="font-mono text-2xs text-indigo-500 font-bold"><?= e($e['emp_code']) ?></span>
                </div>
                <p class="text-2xs text-slate-400"><?= e($e['department_name'] ?? 'General') ?></p>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between font-mono">
                    <span class="text-slate-400">Basic Pay:</span>
                    <span class="font-bold text-slate-900 dark:text-white">₹<?= number_format((float)$e['salary_basic'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Generated Payroll Runs Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-xs">Recent Payroll Runs</h3>
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Payroll Cycle</th>
                    <th class="p-4">Gross Disbursed</th>
                    <th class="p-4">Total Deductions (Absent + Late)</th>
                    <th class="p-4">Net Payout</th>
                    <th class="p-4">Generated Date</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php if (empty($runs)): ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-400 text-xs">No payroll runs generated yet. Select a month and click "💰 Run Payroll" to execute.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($runs as $r): ?>
                    <tr>
                        <td class="p-4 font-bold text-slate-900 dark:text-white"><?= e($r['month_year']) ?></td>
                        <td class="p-4 font-mono">₹<?= number_format((float)$r['total_gross'], 2) ?></td>
                        <td class="p-4 font-mono text-red-400">₹<?= number_format((float)$r['total_deductions'], 2) ?></td>
                        <td class="p-4 font-mono font-bold text-emerald-500">₹<?= number_format((float)$r['total_net'], 2) ?></td>
                        <td class="p-4 font-mono text-slate-400"><?= e($r['created_at']) ?></td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($r['status']) ?></span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="<?= url('staff/payroll/' . $r['id']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
