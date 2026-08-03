<?php
$layout = 'app';
$pageTitle = 'Staff & HR Reports';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Staff']];
ob_start();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Staff & HR Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">HR departments distribution, designations breakdown, leave logs, and payroll summary.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"></path></svg>
            Print Report
        </button>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Leave Request stats -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Leave Requests Summary</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-slate-850 border border-slate-800 p-3 rounded-xl">
                        <p class="text-xs text-slate-500 font-medium">Pending</p>
                        <p class="text-xl font-bold text-amber-400 mt-1"><?= number_format($leaveStats['pending'] ?? 0) ?></p>
                    </div>
                    <div class="bg-slate-850 border border-slate-800 p-3 rounded-xl">
                        <p class="text-xs text-slate-500 font-medium">Approved</p>
                        <p class="text-xl font-bold text-emerald-400 mt-1"><?= number_format($leaveStats['approved'] ?? 0) ?></p>
                    </div>
                    <div class="bg-slate-850 border border-slate-800 p-3 rounded-xl">
                        <p class="text-xs text-slate-500 font-medium">Rejected</p>
                        <p class="text-xl font-bold text-red-400 mt-1"><?= number_format($leaveStats['rejected'] ?? 0) ?></p>
                    </div>
                </div>
                <div class="text-xs text-slate-500 text-center mt-2">
                    Total leave requests submitted: <span class="text-white font-semibold"><?= number_format($leaveStats['total'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- Department Breakdown -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Active Staff by Department</h3>
            <div class="max-h-60 overflow-y-auto space-y-3 pr-1">
                <?php if (empty($deptCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No active departments found.</p>
                <?php else: ?>
                    <?php foreach ($deptCounts as $d): ?>
                        <div class="flex justify-between items-center bg-slate-850/50 border border-slate-800/40 p-3 rounded-xl">
                            <span class="text-sm text-slate-300 font-medium"><?= e($d['department_name']) ?></span>
                            <span class="text-xs px-2.5 py-0.5 font-bold rounded-full bg-slate-800 text-slate-400 border border-slate-700"><?= $d['cnt'] ?> Staff</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Designation Distribution -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Staff by Designation</h3>
            <div class="max-h-60 overflow-y-auto space-y-3 pr-1">
                <?php if (empty($desigCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No active designations found.</p>
                <?php else: ?>
                    <?php foreach ($desigCounts as $des): ?>
                        <div class="flex justify-between items-center bg-slate-850/50 border border-slate-800/40 p-3 rounded-xl">
                            <span class="text-sm text-slate-300 font-medium"><?= e($des['designation_title']) ?></span>
                            <span class="text-xs px-2.5 py-0.5 font-bold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><?= $des['cnt'] ?> Staff</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Payroll Runs -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-base font-bold text-white mb-4">Recent Payroll Runs Payout</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold">
                        <th class="py-3 px-4">Payroll Period</th>
                        <th class="py-3 px-4">Processed Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Staff Count</th>
                        <th class="py-3 px-4">Total Net Payout</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-300">
                    <?php if (empty($payrollSummary)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No payroll runs recorded.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payrollSummary as $pr): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 px-4 font-medium text-white">
                                    <?php
                                    $monthText = $pr['month_year'] ?? '';
                                    if (preg_match('/^(\d{4})-(\d{2})$/', $monthText, $m)) {
                                        $monthText = date('F Y', mktime(0, 0, 0, (int)$m[2], 1, (int)$m[1]));
                                    } elseif (preg_match('/^(\d{2})-(\d{4})$/', $monthText, $m)) {
                                        $monthText = date('F Y', mktime(0, 0, 0, (int)$m[1], 1, (int)$m[2]));
                                    }
                                    echo e($monthText);
                                    ?>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-400"><?= date('d M Y', strtotime($pr['created_at'])) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase bg-emerald-900/30 text-emerald-400 border border-emerald-700/30">
                                        <?= e($pr['status'] ?? 'completed') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold"><?= number_format($pr['employee_count']) ?> Employees</td>
                                <td class="py-3 px-4 font-bold text-indigo-400">₹<?= number_format((float)$pr['total_payout'], 2) ?></td>
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
