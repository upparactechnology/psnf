<?php
$layout    = 'app';
$pageTitle = 'Payroll Run: ' . $run['month_year'];
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Runs Details']];
ob_start();
?>

<div x-data="{ expandedRow: null }" class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Panel -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold bg-rose-500/10 text-rose-500 px-2 py-0.5 rounded uppercase">Monthly Run Batch</span>
                <span class="text-2xs text-slate-400 font-mono">ID: #<?= $run['id'] ?></span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">Payroll for <?= e($run['month_year']) ?></h1>
            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Processing denominator: all calendar days in the month (including Sundays & Paid Holidays).</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-xl text-xs font-bold border border-emerald-500/20">Approved Run</span>
            <a href="<?= url('payroll/runs') ?>" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Back to Runs</a>
        </div>
    </div>

    <!-- Overview Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40">
            <span class="text-2xs font-semibold text-slate-400 uppercase">Gross Base Payout</span>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">₹<?= number_format((float)$run['total_gross'], 2) ?></h3>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40">
            <span class="text-2xs font-semibold text-slate-400 uppercase">Accumulated Deductions</span>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">-₹<?= number_format((float)$run['total_deductions'], 2) ?></h3>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40">
            <span class="text-2xs font-semibold text-slate-400 uppercase">Net Salary Disbursed</span>
            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">₹<?= number_format((float)$run['total_net'], 2) ?></h3>
        </div>
    </div>

    <!-- Payout Items List -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Employee Breakdown</span>
            <span class="text-2xs text-slate-400 font-mono">Tally: <?= count($items) ?> staff members</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-3xs border-b border-slate-100 dark:border-slate-850">
                    <tr>
                        <th class="p-3">Staff Details</th>
                        <th class="p-3">Base Basic</th>
                        <th class="p-3">Days Denominator</th>
                        <th class="p-3">Attendance Summary</th>
                        <th class="p-3">Lateness / Penalty</th>
                        <th class="p-3">Itemized Deductions</th>
                        <th class="p-3">Total Deductions</th>
                        <th class="p-3">Net Salary</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">No payouts resolved for this payroll batch.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $it): ?>
                        <?php 
                            $empId = (int)$it['employee_id'];
                            $empChronology = $chronology[$empId] ?? [];
                        ?>
                        <tr class="hover:bg-slate-50/45 dark:hover:bg-slate-850/20 transition-all">
                            <td class="p-3">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block"><?= e($it['first_name'] . ' ' . $it['last_name']) ?></span>
                                    <span class="text-3xs font-mono text-slate-400 block"><?= e($it['emp_code']) ?> • <?= e($it['designation_title'] ?? 'Staff') ?></span>
                                </div>
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float)$it['salary_basic'], 2) ?></td>
                            <td class="p-3 font-mono text-slate-500">
                                <?= (int)$it['salary_days'] ?> Days
                                <span class="text-3xs font-bold text-slate-400 block">₹<?= number_format((float)$it['daily_salary'], 2) ?>/day</span>
                            </td>
                            <td class="p-3">
                                <div class="space-y-0.5 text-3xs leading-tight">
                                    <div><span class="text-emerald-500 font-bold">Present:</span> <?= (int)$it['present_days'] ?></div>
                                    <div><span class="text-rose-500 font-bold">Absent:</span> <?= (int)$it['absent_days'] ?></div>
                                    <div><span class="text-indigo-500 font-bold">Leaves:</span> <?= (int)($it['paid_leave_days'] + $it['unpaid_leave_days']) ?> <span class="text-[9px] text-slate-400">(<?= (int)$it['paid_leave_days'] ?>P/<?= (int)$it['unpaid_leave_days'] ?>U)</span></div>
                                    <div><span class="text-slate-500 font-bold">Holiday/Sun:</span> <?= (int)($it['paid_holiday_days'] + $it['sunday_days']) ?></div>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="space-y-0.5 text-3xs leading-tight">
                                    <div><span class="text-amber-500 font-bold">Lates:</span> <?= (int)$it['late_count'] ?> <span class="text-slate-400">(Ex: <?= (int)$it['late_exempted_count'] ?>)</span></div>
                                    <div><span class="text-rose-500 font-bold">Penalty Days:</span> <?= (int)$it['late_penalty_half_days'] ?></div>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-rose-500 text-3xs leading-tight">
                                <div>Absents: ₹<?= number_format((float)$it['absent_deduction'], 2) ?></div>
                                <div>Unpaid Leaves: ₹<?= number_format((float)($it['unpaid_leave_deduction'] ?? 0), 2) ?></div>
                                <div>Half-Days: ₹<?= number_format((float)($it['normal_half_day_deduction'] ?? 0), 2) ?></div>
                                <div>Lates Penalty: ₹<?= number_format((float)($it['late_penalty_deduction'] ?? 0), 2) ?></div>
                            </td>
                            <td class="p-3 font-mono font-bold text-rose-500">-₹<?= number_format((float)$it['total_deductions'], 2) ?></td>
                            <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format((float)$it['net_salary'], 2) ?></td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="expandedRow === <?= $empId ?> ? expandedRow = null : expandedRow = <?= $empId ?>" class="px-2 py-1 rounded text-3xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition-all">Audit Trail</button>
                                    <a href="<?= url('payroll/runs/' . $run['id'] . '/payslip/' . $empId) ?>" target="_blank" class="px-2 py-1 rounded text-3xs bg-rose-600 hover:bg-rose-500 text-white font-bold transition-all">Payslip</a>
                                </div>
                            </td>
                        </tr>

                        <!-- Collapsible Audit Chronology row -->
                        <tr x-show="expandedRow === <?= $empId ?>" x-cloak class="bg-slate-50/60 dark:bg-slate-900/60">
                            <td colspan="9" class="p-4 border-t border-slate-200/50 dark:border-slate-800/50">
                                <div class="space-y-3">
                                    <h4 class="text-2xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Lateness & Deductions Chronology Audit Trail</h4>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                                        <?php foreach ($empChronology as $dayIdx => $ch): ?>
                                        <?php 
                                            $cardBorder = 'border-slate-200 dark:border-slate-800';
                                            $cardBg = 'bg-white dark:bg-slate-900/40';
                                            $badgeText = '';
                                            
                                            if ($ch['status'] === 'SUNDAY') {
                                                $badgeText = 'Sunday';
                                                $cardBg = 'bg-slate-100/50 dark:bg-slate-850/50';
                                            } elseif ($ch['status'] === 'PAID_HOLIDAY') {
                                                $badgeText = 'Paid Hol';
                                                $cardBg = 'bg-indigo-50/50 dark:bg-indigo-950/20';
                                            } elseif ($ch['status'] === 'PAID_LEAVE') {
                                                $badgeText = 'Paid Leave';
                                                $cardBg = 'bg-emerald-50/50 dark:bg-emerald-950/20';
                                            } elseif ($ch['status'] === 'UNPAID_LEAVE') {
                                                $badgeText = 'Unpaid Leave';
                                                $cardBg = 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-500/20';
                                            } elseif ($ch['status'] === 'ABSENT') {
                                                $badgeText = 'Absent';
                                                $cardBg = 'bg-red-500/10 text-red-500 border-red-500/20';
                                            } elseif ($ch['status'] === 'HALF_DAY') {
                                                $badgeText = 'Half-Day';
                                                $cardBg = 'bg-orange-500/10 text-orange-600 border-orange-500/20';
                                            }

                                            // Highlight late entries or late penalties
                                            if ($ch['is_late']) {
                                                if ($ch['late_exempted']) {
                                                    $badgeText = 'Late (Exempt)';
                                                    $cardBg = 'bg-slate-200 dark:bg-slate-850 border-emerald-500/45';
                                                } else {
                                                    $badgeText = 'Late #' . $ch['ticker'];
                                                    $cardBg = 'bg-amber-500/10 text-amber-600 border-amber-500/45';
                                                }
                                            }

                                            if ($ch['penalty']) {
                                                $badgeText = 'Late Penalty';
                                                $cardBg = 'bg-rose-600/15 border-rose-500 text-rose-600 dark:text-rose-400 font-bold';
                                            }
                                        ?>
                                        <div class="p-2.5 rounded-xl border <?= $cardBorder ?> <?= $cardBg ?> flex flex-col justify-between h-16 transition-all shadow-sm">
                                            <span class="text-3xs font-mono font-bold text-slate-400"><?= date('d M', strtotime($ch['date'])) ?></span>
                                            <span class="text-3xs font-bold block truncate"><?= $badgeText ?: $ch['status'] ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
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
