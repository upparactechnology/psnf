<?php
$layout    = 'app';
$pageTitle = 'Payslip: ' . $item['first_name'] . ' ' . $item['last_name'] . ' - ' . $run['month_year'];
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Run Detail', 'url' => '/payroll/runs/' . $run['id']], ['label' => 'Payslip']];
ob_start();
?>

<div class="space-y-6 max-w-4xl mx-auto text-xs">

    <!-- Header Actions -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Employee Payslip</h1>
            <p class="text-3xs text-slate-500">Month Period: <?= e($run['month_year']) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">Print Payslip</button>
            <a href="<?= url('payroll/runs/' . $run['id']) ?>" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Close</a>
        </div>
    </div>

    <!-- Printable Payslip Document -->
    <div class="p-8 rounded-2xl border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900/60 shadow-lg space-y-6 print:border-0 print:shadow-none print:p-0">
        
        <!-- School Logo & Header -->
        <div class="flex justify-between items-start border-b border-slate-200/60 dark:border-slate-800/60 pb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">PSNF ENGLISH MEDIUM SCHOOL</h2>
                <p class="text-3xs text-slate-400">School Administration & Enterprise HRMS Payout Portal</p>
                <p class="text-3xs text-slate-400 mt-1">Branch Code: PSNF-MAIN • Tenant: 1</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full uppercase border border-emerald-500/20">PAID STATUS</span>
                <span class="text-2xs font-mono text-slate-400 block mt-2">Payslip Date: <?= date('d M Y') ?></span>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50/50 dark:bg-slate-800/20 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/50">
            <div>
                <span class="text-3xs text-slate-400 font-bold uppercase block">Employee Name</span>
                <span class="font-bold text-slate-900 dark:text-white"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></span>
            </div>
            <div>
                <span class="text-3xs text-slate-400 font-bold uppercase block">Employee Code</span>
                <span class="font-bold text-slate-900 dark:text-white font-mono"><?= e($item['emp_code']) ?></span>
            </div>
            <div>
                <span class="text-3xs text-slate-400 font-bold uppercase block">Designation / Title</span>
                <span class="font-bold text-slate-900 dark:text-white"><?= e($item['designation_title'] ?? 'Educator') ?></span>
            </div>
            <div>
                <span class="text-3xs text-slate-400 font-bold uppercase block">Department</span>
                <span class="font-bold text-slate-900 dark:text-white"><?= e($item['department_name'] ?? 'Teaching Faculty') ?></span>
            </div>
        </div>

        <!-- Attendance Summary Metrics -->
        <div class="space-y-3">
            <h4 class="text-2xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Attendance & Lateness Summary</h4>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Salary Denominator</span>
                    <span class="text-lg font-bold text-slate-900 dark:text-white font-mono"><?= (int)$item['salary_days'] ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Days Present</span>
                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono"><?= (int)$item['present_days'] ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Days Absent</span>
                    <span class="text-lg font-bold text-rose-500 font-mono"><?= (int)$item['absent_days'] ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Approved Leaves</span>
                    <span class="text-lg font-bold text-indigo-500 font-mono"><?= (int)($item['paid_leave_days'] + $item['unpaid_leave_days']) ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Late Clock-Ins</span>
                    <span class="text-lg font-bold text-amber-500 font-mono"><?= (int)$item['late_count'] ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-850 text-center">
                    <span class="text-3xs text-slate-400 block">Penalty Half-Days</span>
                    <span class="text-lg font-bold text-rose-600 dark:text-rose-400 font-mono"><?= (int)$item['late_penalty_half_days'] ?></span>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Earnings -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2 border-b border-slate-200 dark:border-slate-800 font-bold text-slate-700 dark:text-slate-300">Earnings Detail</div>
                <table class="w-full">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <tr>
                            <td class="p-3">Basic Salary Payout</td>
                            <td class="p-3 text-right font-mono font-bold">₹<?= number_format((float)$item['salary_basic'], 2) ?></td>
                        </tr>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/10 font-bold">
                            <td class="p-3 text-slate-900 dark:text-white">Gross Salary Payout</td>
                            <td class="p-3 text-right font-mono text-slate-900 dark:text-white">₹<?= number_format((float)$item['salary_basic'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2 border-b border-slate-200 dark:border-slate-800 font-bold text-rose-600 dark:text-rose-400">Deductions Detail</div>
                <table class="w-full">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <tr>
                            <td class="p-3">Absent Days (<?= (int)$item['absent_days'] ?> days)</td>
                            <td class="p-3 text-right font-mono font-bold text-rose-500">-₹<?= number_format((float)$item['absent_deduction'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="p-3">Unpaid Leaves (<?= (int)$item['unpaid_leave_days'] ?> days)</td>
                            <td class="p-3 text-right font-mono font-bold text-rose-500">-₹<?= number_format((float)($item['unpaid_leave_deduction'] ?? 0), 2) ?></td>
                        </tr>
                        <tr>
                            <td class="p-3">Normal Half-Days (<?= (int)$item['normal_half_days'] ?> days)</td>
                            <td class="p-3 text-right font-mono font-bold text-rose-500">-₹<?= number_format((float)($item['normal_half_day_deduction'] ?? 0), 2) ?></td>
                        </tr>
                        <tr>
                            <td class="p-3">Lateness Penalties (<?= (int)$item['late_penalty_half_days'] ?> penalty half-days)</td>
                            <td class="p-3 text-right font-mono font-bold text-rose-500">-₹<?= number_format((float)($item['late_penalty_deduction'] ?? 0), 2) ?></td>
                        </tr>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/10 font-bold">
                            <td class="p-3 text-rose-600">Total Salary Deductions</td>
                            <td class="p-3 text-right font-mono text-rose-600">-₹<?= number_format((float)$item['total_deductions'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Net Salary Panel -->
        <div class="p-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 dark:bg-emerald-950/20 flex justify-between items-center">
            <div>
                <span class="text-3xs text-emerald-600 dark:text-emerald-400 font-bold uppercase block">NET SALARY PAYOUT</span>
                <span class="text-base font-black text-emerald-700 dark:text-emerald-300 font-mono">₹<?= number_format((float)$item['net_salary'], 2) ?></span>
            </div>
            <div class="text-right">
                <span class="text-4xs text-slate-400 block font-bold">Signature / Authority Approval</span>
                <span class="text-3xs font-serif italic text-slate-600 dark:text-slate-400 block mt-2">Approved by PSNF School Accounts Manager</span>
            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
?>
