<?php
$layout = 'app';
$pageTitle = 'Staff & HR Reports';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Staff']];
ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Staff & HR Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Departments, designations, attendance, leaves, payroll, and salary distribution.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"/></svg>
            Print Report
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- SUMMARY CARDS -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-emerald-400"><?= number_format($totalStaff) ?></div>
            <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Active Staff</div>
        </div>
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-blue-400"><?= number_format($totalEmployees) ?></div>
            <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Total Employees</div>
        </div>
        <div class="bg-red-950/30 border border-red-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-red-400"><?= number_format($inactiveStaff) ?></div>
            <div class="text-2xs text-red-500/70 font-semibold mt-1 uppercase tracking-wider">Inactive</div>
        </div>
        <div class="bg-amber-950/30 border border-amber-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-amber-400"><?= number_format($leaveStats['pending'] ?? 0) ?></div>
            <div class="text-2xs text-amber-500/70 font-semibold mt-1 uppercase tracking-wider">Pending Leaves</div>
        </div>
        <div class="bg-purple-950/30 border border-purple-800/40 rounded-2xl p-5 text-center">
            <?php
                $attRate = ($staffAttStats['total'] ?? 0) > 0 ? round(($staffAttStats['present_cnt'] ?? 0) / $staffAttStats['total'] * 100) : 0;
            ?>
            <div class="text-3xl font-extrabold text-purple-400"><?= $attRate ?>%</div>
            <div class="text-2xs text-purple-500/70 font-semibold mt-1 uppercase tracking-wider">Staff Attendance (30d)</div>
        </div>
        <div class="bg-cyan-950/30 border border-cyan-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-cyan-400"><?= $staffAttStats['avg_hours'] ?? 0 ?>h</div>
            <div class="text-2xs text-cyan-500/70 font-semibold mt-1 uppercase tracking-wider">Avg Hours (30d)</div>
        </div>
    </div>

    <!-- Secondary KPI row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-emerald-950/20 border border-emerald-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-emerald-400"><?= number_format($leaveStats['approved'] ?? 0) ?></div>
            <div class="text-2xs text-emerald-500/60">Approved Leaves</div>
        </div>
        <div class="bg-red-950/20 border border-red-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-red-400"><?= number_format($leaveStats['rejected'] ?? 0) ?></div>
            <div class="text-2xs text-red-500/60">Rejected Leaves</div>
        </div>
        <div class="bg-blue-950/20 border border-blue-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-blue-400"><?= number_format($leaveStats['total_days'] ?? 0) ?></div>
            <div class="text-2xs text-blue-500/60">Total Leave Days</div>
        </div>
        <div class="bg-purple-950/20 border border-purple-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-purple-400"><?= number_format($leaveStats['total'] ?? 0) ?></div>
            <div class="text-2xs text-purple-500/60">Total Leave Requests</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 1: Department Pie + Designation Bar + Employment Type -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Department Doughnut -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Staff by Department</h3>
            <?php if (empty($deptCounts)): ?>
                <p class="text-sm text-slate-500 text-center py-6">No departments configured.</p>
            <?php else: ?>
            <div class="relative h-56"><canvas id="deptChart"></canvas></div>
            <div class="mt-3 space-y-2 max-h-32 overflow-y-auto">
                <?php foreach ($deptCounts as $d): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400"><?= e($d['department_name']) ?></span>
                    <span class="text-white font-bold"><?= $d['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Designation Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Staff by Designation</h3>
            <?php if (empty($desigCounts)): ?>
                <p class="text-sm text-slate-500 text-center py-6">No designations configured.</p>
            <?php else: ?>
            <div class="relative h-56"><canvas id="desigChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Employment Type -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Employment Type</h3>
            <?php if (empty($employmentTypeCounts)): ?>
                <p class="text-sm text-slate-500 text-center py-6">No employment data.</p>
            <?php else: ?>
            <div class="relative h-56"><canvas id="empTypeChart"></canvas></div>
            <div class="mt-3 space-y-2">
                <?php foreach ($employmentTypeCounts as $et): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400 capitalize"><?= e(str_replace('_', ' ', $et['emp_type'])) ?></span>
                    <span class="text-white font-bold"><?= $et['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 2: Attendance Trend + Leave Trend -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Trend Stacked Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Staff Attendance Trend (6 Months)</h3>
            <?php if (empty($staffAttTrend)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No attendance records yet.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="attTrendChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Leave Trend -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Leave Requests Trend (6 Months)</h3>
            <?php if (empty($leaveTrend)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No leave requests yet.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="leaveTrendChart"></canvas></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 3: Payroll Trend + Salary Ranges -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payroll Trend Line -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Payroll Trend (Gross vs Net vs Deductions)</h3>
            <?php if (empty($payrollTrend)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No payroll runs recorded.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="payrollTrendChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Salary Range Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Salary Range Distribution</h3>
            <?php if (empty($salaryRanges)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No salary structures defined.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="salaryRangeChart"></canvas></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 4: Avg Salary by Dept + Top Punctual Staff -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Avg Salary by Dept Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Average Salary by Department</h3>
            <?php if (empty($avgSalaryByDept)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No salary data available.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="avgSalaryChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Top Punctual Staff -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Top Punctual Staff (30 Days)</h3>
            <?php if (empty($topPunctualStaff)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No attendance data available.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-slate-500 border-b border-slate-800">
                        <th class="text-left py-2">#</th>
                        <th class="text-left py-2">Employee</th>
                        <th class="text-left py-2">Code</th>
                        <th class="text-center py-2">Days</th>
                        <th class="text-right py-2">Attendance %</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topPunctualStaff as $i => $ps): ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2 text-slate-500"><?= $i + 1 ?></td>
                        <td class="py-2 text-white font-medium"><?= e($ps['first_name'] . ' ' . $ps['last_name']) ?></td>
                        <td class="py-2 text-slate-400 font-mono text-2xs"><?= e($ps['emp_code'] ?? $ps['employee_code'] ?? '-') ?></td>
                        <td class="py-2 text-center text-slate-400"><?= $ps['present_days'] ?>/<?= $ps['total_days'] ?></td>
                        <td class="py-2 text-right">
                            <span class="px-2 py-0.5 rounded-full text-2xs font-bold <?= $ps['attendance_pct'] >= 90 ? 'bg-emerald-900/30 text-emerald-400' : ($ps['attendance_pct'] >= 75 ? 'bg-amber-900/30 text-amber-400' : 'bg-red-900/30 text-red-400') ?>">
                                <?= $ps['attendance_pct'] ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 5: Payroll Runs + Latest Payroll Detail -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payroll Runs Table -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Recent Payroll Runs</h3>
            <?php if (empty($payrollSummary)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No payroll runs recorded.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-slate-500 border-b border-slate-800">
                        <th class="text-left py-2">Period</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-center py-2">Staff</th>
                        <th class="text-right py-2">Gross</th>
                        <th class="text-right py-2">Deductions</th>
                        <th class="text-right py-2">Net Payout</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($payrollSummary as $pr):
                        $monthText = $pr['month_year'] ?? '';
                        if (preg_match('/^(\d{4})-(\d{2})$/', $monthText, $m)) {
                            $monthText = date('M Y', mktime(0,0,0,(int)$m[2],1,(int)$m[1]));
                        } elseif (preg_match('/^(\d{2})-(\d{4})$/', $monthText, $m)) {
                            $monthText = date('M Y', mktime(0,0,0,(int)$m[1],1,(int)$m[2]));
                        }
                    ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2 text-white font-medium"><?= e($monthText) ?></td>
                        <td class="py-2"><span class="px-2 py-0.5 rounded text-2xs font-semibold bg-emerald-900/30 text-emerald-400 border border-emerald-700/30 capitalize"><?= e($pr['status'] ?? 'completed') ?></span></td>
                        <td class="py-2 text-center text-slate-300"><?= $pr['employee_count'] ?></td>
                        <td class="py-2 text-right text-blue-400 font-bold">₹<?= number_format($pr['total_gross'] ?? 0, 0) ?></td>
                        <td class="py-2 text-right text-red-400">₹<?= number_format($pr['total_deductions'] ?? 0, 0) ?></td>
                        <td class="py-2 text-right text-emerald-400 font-bold">₹<?= number_format($pr['total_payout'] ?? 0, 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Latest Payroll Detail -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Latest Payroll Breakdown</h3>
            <?php if (empty($latestPayrollItems)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No payroll items recorded.</p>
            <?php else: ?>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 bg-slate-900"><tr class="text-slate-500 border-b border-slate-800">
                        <th class="text-left py-2">Employee</th>
                        <th class="text-center py-2">Present</th>
                        <th class="text-center py-2">Late</th>
                        <th class="text-right py-2">Gross</th>
                        <th class="text-right py-2">Deductions</th>
                        <th class="text-right py-2">Net</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($latestPayrollItems as $li): ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2">
                            <div class="text-white font-medium"><?= e($li['first_name'] . ' ' . $li['last_name']) ?></div>
                            <div class="text-2xs text-slate-500 font-mono"><?= e($li['emp_code'] ?? $li['employee_code'] ?? '-') ?></div>
                        </td>
                        <td class="py-2 text-center text-emerald-400"><?= $li['present_days'] ?>/<?= $li['working_days'] ?></td>
                        <td class="py-2 text-center text-amber-400"><?= $li['late_days'] ?? $li['late_count'] ?? 0 ?></td>
                        <td class="py-2 text-right text-blue-400 font-bold">₹<?= number_format($li['gross_salary'], 0) ?></td>
                        <td class="py-2 text-right text-red-400">₹<?= number_format($li['total_deductions'], 0) ?></td>
                        <td class="py-2 text-right text-emerald-400 font-bold">₹<?= number_format($li['net_salary'], 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const palette = ['#6366f1','#10b981','#f59e0b','#f43f5e','#3b82f6','#a855f7','#06b6d4','#f97316','#ec4899','#14b8a6','#84cc16'];
    const gridColor = 'rgba(148,163,184,0.08)';
    const tickColor = '#64748b';
    const defaultOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };

    // ── Department Doughnut ──
    <?php if (!empty($deptCounts)): ?>
    new Chart(document.getElementById('deptChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($deptCounts, 'department_name')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($deptCounts, 'cnt'))) ?>, backgroundColor: palette, borderWidth: 0, hoverOffset: 6 }]
        },
        options: { ...defaultOpts, cutout: '60%', plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } } }
    });
    <?php endif; ?>

    // ── Designation Bar ──
    <?php if (!empty($desigCounts)): ?>
    new Chart(document.getElementById('desigChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($desigCounts, 'designation_title')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($desigCounts, 'cnt'))) ?>, backgroundColor: palette.slice(1), borderRadius: 6 }]
        },
        options: { ...defaultOpts, indexAxis: 'y', scales: { y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, x: { grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });
    <?php endif; ?>

    // ── Employment Type Doughnut ──
    <?php if (!empty($employmentTypeCounts)): ?>
    new Chart(document.getElementById('empTypeChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(fn($et) => ucfirst(str_replace('_',' ',$et['emp_type'])), $employmentTypeCounts)) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($employmentTypeCounts, 'cnt'))) ?>, backgroundColor: ['#6366f1','#10b981','#f59e0b','#f43f5e','#3b82f6'], borderWidth: 0 }]
        },
        options: { ...defaultOpts, cutout: '60%', plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } } }
    });
    <?php endif; ?>

    // ── Attendance Trend Stacked Bar ──
    <?php if (!empty($staffAttTrend)): ?>
    new Chart(document.getElementById('attTrendChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($staffAttTrend, 'month')) ?>,
            datasets: [
                { label: 'Present', data: <?= json_encode(array_map('intval', array_column($staffAttTrend, 'present_cnt'))) ?>, backgroundColor: '#10b981', borderRadius: 3 },
                { label: 'Late', data: <?= json_encode(array_map('intval', array_column($staffAttTrend, 'late_cnt'))) ?>, backgroundColor: '#f59e0b', borderRadius: 3 },
                { label: 'Half Day', data: <?= json_encode(array_map('intval', array_column($staffAttTrend, 'half_day_cnt'))) ?>, backgroundColor: '#f97316', borderRadius: 3 },
                { label: 'Absent', data: <?= json_encode(array_map('intval', array_column($staffAttTrend, 'absent_cnt'))) ?>, backgroundColor: '#f43f5e', borderRadius: 3 }
            ]
        },
        options: { ...defaultOpts, plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } }, scales: { x: { stacked: true, grid: { display: false }, ticks: { color: tickColor } }, y: { stacked: true, grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });
    <?php endif; ?>

    // ── Leave Trend Line ──
    <?php if (!empty($leaveTrend)): ?>
    new Chart(document.getElementById('leaveTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($leaveTrend, 'month')) ?>,
            datasets: [
                { label: 'Total', data: <?= json_encode(array_map('intval', array_column($leaveTrend, 'total_requests'))) ?>, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4, pointRadius: 4 },
                { label: 'Approved', data: <?= json_encode(array_map('intval', array_column($leaveTrend, 'approved_cnt'))) ?>, borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.4, pointRadius: 3 },
                { label: 'Rejected', data: <?= json_encode(array_map('intval', array_column($leaveTrend, 'rejected_cnt'))) ?>, borderColor: '#f43f5e', backgroundColor: 'transparent', borderDash: [4,4], tension: 0.4, pointRadius: 3 }
            ]
        },
        options: { ...defaultOpts, plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 10, font: { size: 10 } } } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true } } }
    });
    <?php endif; ?>

    // ── Payroll Trend Line ──
    <?php if (!empty($payrollTrend)): ?>
    new Chart(document.getElementById('payrollTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($payrollTrend, 'month_year')) ?>,
            datasets: [
                { label: 'Gross', data: <?= json_encode(array_map('floatval', array_column($payrollTrend, 'total_gross'))) ?>, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, pointRadius: 4 },
                { label: 'Net', data: <?= json_encode(array_map('floatval', array_column($payrollTrend, 'total_net'))) ?>, borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.4, pointRadius: 3 },
                { label: 'Deductions', data: <?= json_encode(array_map('floatval', array_column($payrollTrend, 'total_deductions'))) ?>, borderColor: '#f43f5e', backgroundColor: 'transparent', borderDash: [4,4], tension: 0.4, pointRadius: 3 }
            ]
        },
        options: { ...defaultOpts, plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 10, font: { size: 10 } } } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' }, beginAtZero: true } } }
    });
    <?php endif; ?>

    // ── Salary Range Bar ──
    <?php if (!empty($salaryRanges)): ?>
    new Chart(document.getElementById('salaryRangeChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($salaryRanges, 'salary_range')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($salaryRanges, 'cnt'))) ?>, backgroundColor: ['#10b981','#3b82f6','#6366f1','#f59e0b','#f43f5e'], borderRadius: 6 }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });
    <?php endif; ?>

    // ── Avg Salary by Dept Horizontal Bar ──
    <?php if (!empty($avgSalaryByDept)): ?>
    new Chart(document.getElementById('avgSalaryChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($avgSalaryByDept, 'department_name')) ?>,
            datasets: [{ data: <?= json_encode(array_map('floatval', array_column($avgSalaryByDept, 'avg_basic'))) ?>, backgroundColor: palette.slice(0, count($avgSalaryByDept)), borderRadius: 6 }]
        },
        options: { ...defaultOpts, indexAxis: 'y', scales: { y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, x: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' } } } }
    });
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
?>
