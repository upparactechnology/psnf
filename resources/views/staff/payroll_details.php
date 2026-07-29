<?php
$layout    = 'app';
$pageTitle = 'Payroll Run Details';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Payroll', 'url' => '/staff/payroll'], ['label' => 'Details']];
ob_start();
?>

<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Payroll Run: <?= e($run['month_year']) ?></h1>
            <p class="text-xs text-slate-500 mt-0.5">Detailed breakdown of gross salaries, deductions, and net payouts for this cycle.</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <div class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold">
                Total Net: <span class="text-emerald-600 dark:text-emerald-400 font-mono ml-1">₹<?= number_format((float)$run['total_net'], 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Payroll Items Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                    <tr>
                        <th class="p-4">Employee</th>
                        <th class="p-4">Stats</th>
                        <th class="p-4">Base Salary</th>
                        <th class="p-4 text-red-400">Absent Ded.</th>
                        <th class="p-4 text-red-400">Late Ded.</th>
                        <th class="p-4">Net Salary</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 text-xs">No employee records found in this payroll run.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 dark:text-white"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></span>
                                    <span class="font-mono text-2xs text-indigo-500"><?= e($item['emp_code']) ?> - <?= e($item['department_name'] ?? 'General') ?></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1 text-2xs">
                                    <span class="text-slate-500">Working: <span class="font-bold text-slate-700 dark:text-slate-300"><?= (int)$item['working_days'] ?></span></span>
                                    <span class="text-emerald-500">Present: <span class="font-bold"><?= (int)$item['present_days'] ?></span></span>
                                    <span class="text-amber-500">Late: <span class="font-bold"><?= (int)$item['late_days'] ?></span></span>
                                </div>
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                ₹<?= number_format((float)$item['gross_salary'], 2) ?>
                            </td>
                            <td class="p-4 font-mono text-red-400">
                                -₹<?= number_format((float)$item['absent_deduction'], 2) ?>
                            </td>
                            <td class="p-4 font-mono text-red-400">
                                -₹<?= number_format((float)$item['late_deduction'], 2) ?>
                            </td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                ₹<?= number_format((float)$item['net_salary'], 2) ?>
                            </td>
                            <td class="p-4 text-right">
                                <a href="<?= url('staff/payroll/' . $run['id'] . '/payslip/' . $item['employee_id']) ?>" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Payslip
                                </a>
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
