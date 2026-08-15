<?php
$layout = 'app';
$pageTitle = 'Student Reports & Analytics';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Students']];
ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Student & Academic Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Enrollment trends, classroom distribution, demographics, attendance & fees.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"/></svg>
            Print Report
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- SUMMARY CARDS -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-white"><?= number_format($totalStudents) ?></div>
            <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Total Students</div>
        </div>
        <div class="bg-emerald-950/30 border border-emerald-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-emerald-400"><?= number_format($enrolledCount) ?></div>
            <div class="text-2xs text-emerald-500/70 font-semibold mt-1 uppercase tracking-wider">Enrolled</div>
        </div>
        <div class="bg-blue-950/30 border border-blue-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-blue-400"><?= $attendanceRate ?>%</div>
            <div class="text-2xs text-blue-500/70 font-semibold mt-1 uppercase tracking-wider">Attendance (30d)</div>
        </div>
        <div class="bg-purple-950/30 border border-purple-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-purple-400"><?= $avgPercentage ?>%</div>
            <div class="text-2xs text-purple-500/70 font-semibold mt-1 uppercase tracking-wider">Avg Marks</div>
        </div>
        <div class="bg-amber-950/30 border border-amber-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-amber-400">₹<?= number_format($feeStats['collected'] ?? 0, 0) ?></div>
            <div class="text-2xs text-amber-500/70 font-semibold mt-1 uppercase tracking-wider">Fee Collected</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 1: Status + Gender + Blood Group Charts -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Registration Status Doughnut -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Registration Status</h3>
            <div class="relative h-64"><canvas id="statusChart"></canvas></div>
            <div class="mt-4 space-y-2">
                <?php foreach ($statusCounts as $s): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400 capitalize"><?= e($s['admission_status']) ?></span>
                    <span class="text-white font-bold"><?= $s['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gender Doughnut -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Gender Distribution</h3>
            <div class="relative h-64"><canvas id="genderChart"></canvas></div>
            <div class="mt-4 space-y-2">
                <?php foreach ($genderCounts as $g): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400 capitalize"><?= e($g['gender'] ?: 'Not Specified') ?></span>
                    <span class="text-white font-bold"><?= $g['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Blood Group Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Blood Group Breakdown</h3>
            <div class="relative h-64"><canvas id="bloodChart"></canvas></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 2: Class Distribution + Age Distribution -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Class-wise Bar Chart -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Class-wise Student Distribution</h3>
            <div class="relative h-72"><canvas id="classChart"></canvas></div>
        </div>

        <!-- Age Distribution Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Age Distribution</h3>
            <div class="relative h-72"><canvas id="ageChart"></canvas></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 3: Enrollment Trend + Attendance Overview -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Enrollment Trend Line -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Monthly Enrollment Trend (12 Months)</h3>
            <div class="relative h-72"><canvas id="enrollmentTrendChart"></canvas></div>
        </div>

        <!-- Attendance Overview Stacked Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Attendance Overview (6 Months)</h3>
            <div class="relative h-72"><canvas id="attendanceChart"></canvas></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 4: Exam Performance + Fee Collection -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Exam Performance Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Exam Performance Overview</h3>
            <div class="relative h-72"><canvas id="examChart"></canvas></div>
            <?php if (!empty($examPerformance)): ?>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-slate-500 border-b border-slate-800">
                        <th class="text-left py-2">Exam</th>
                        <th class="text-center py-2">Students</th>
                        <th class="text-center py-2">Avg Marks</th>
                        <th class="text-center py-2">Avg %</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($examPerformance as $ep): ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2 text-white font-medium"><?= e($ep['exam_name']) ?></td>
                        <td class="py-2 text-center text-slate-300"><?= $ep['student_count'] ?></td>
                        <td class="py-2 text-center text-white font-bold"><?= $ep['avg_marks'] ?>/<?= $ep['avg_max'] ?></td>
                        <td class="py-2 text-center font-bold <?= ($ep['avg_max'] > 0 && ($ep['avg_marks']/$ep['avg_max']*100) >= 50) ? 'text-emerald-400' : 'text-red-400' ?>">
                            <?= $ep['avg_max'] > 0 ? round($ep['avg_marks'] / $ep['avg_max'] * 100) : 0 ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fee Collection -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Fee Collection Summary</h3>
            <div class="relative h-60"><canvas id="feeTrendChart"></canvas></div>
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="text-center p-3 rounded-xl bg-slate-850 border border-slate-800/60">
                    <div class="text-lg font-extrabold text-white">₹<?= number_format($feeStats['total_amount'] ?? 0, 0) ?></div>
                    <div class="text-2xs text-slate-500 font-semibold">Total Billed</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-emerald-950/30 border border-emerald-800/40">
                    <div class="text-lg font-extrabold text-emerald-400">₹<?= number_format($feeStats['collected'] ?? 0, 0) ?></div>
                    <div class="text-2xs text-emerald-500/70 font-semibold">Collected</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-red-950/30 border border-red-800/40">
                    <div class="text-lg font-extrabold text-red-400">₹<?= number_format($feeStats['outstanding'] ?? 0, 0) ?></div>
                    <div class="text-2xs text-red-500/70 font-semibold">Outstanding</div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3 text-xs">
                <span class="text-slate-400">Paid: <span class="text-emerald-400 font-bold"><?= $feeStats['paid_count'] ?? 0 ?></span> invoices</span>
                <span class="text-slate-400">Unpaid: <span class="text-red-400 font-bold"><?= $feeStats['unpaid_count'] ?? 0 ?></span> invoices</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 5: Religion + Class Cards -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Religion -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Religion Distribution</h3>
            <?php if (empty($religionCounts)): ?>
                <p class="text-sm text-slate-500 text-center py-4">No data recorded.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($religionCounts as $r): ?>
                <div class="flex justify-between items-center text-xs py-1.5 border-b border-slate-800/40">
                    <span class="text-slate-300"><?= e($r['religion']) ?></span>
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-slate-800 rounded-full h-1.5">
                            <div class="bg-cyan-500 h-1.5 rounded-full" style="width: <?= $totalStudents > 0 ? round($r['cnt'] / $totalStudents * 100) : 0 ?>%"></div>
                        </div>
                        <span class="text-white font-bold w-8 text-right"><?= $r['cnt'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Class Cards -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Class Strength</h3>
            <div class="space-y-2 max-h-72 overflow-y-auto">
                <?php if (empty($classCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No classes registered.</p>
                <?php else: ?>
                    <?php foreach ($classCounts as $c): ?>
                    <div class="flex justify-between items-center bg-slate-850 border border-slate-800/60 p-3 rounded-lg">
                        <div>
                            <h4 class="text-xs font-bold text-white"><?= e($c['class_name']) ?></h4>
                            <p class="text-2xs text-slate-500">Sec: <?= e($c['section'] ?: 'A') ?></p>
                        </div>
                        <span class="px-2 py-0.5 bg-brand-500/10 text-brand-400 font-bold rounded text-xs border border-brand-500/20"><?= $c['cnt'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        brand: '#6366f1', emerald: '#10b981', amber: '#f59e0b', rose: '#f43f5e',
        blue: '#3b82f6', purple: '#a855f7', cyan: '#06b6d4', slate: '#64748b',
        orange: '#f97316', pink: '#ec4899', teal: '#14b8a6', lime: '#84cc16'
    };
    const palette = [colors.brand, colors.emerald, colors.amber, colors.rose, colors.blue, colors.purple, colors.cyan, colors.orange, colors.pink, colors.teal, colors.lime, colors.slate];
    const gridColor = 'rgba(148,163,184,0.08)';
    const tickColor = '#64748b';
    const defaultOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };

    // ── Status Doughnut ──
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($statusCounts, 'admission_status')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($statusCounts, 'cnt'))) ?>, backgroundColor: palette, borderWidth: 0, hoverOffset: 8 }]
        },
        options: { ...defaultOpts, cutout: '65%', plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 12, font: { size: 10 } } } } }
    });

    // ── Gender Doughnut ──
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(fn($g) => ucfirst($g['gender'] ?: 'Not Specified'), $genderCounts)) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($genderCounts, 'cnt'))) ?>, backgroundColor: [colors.blue, colors.rose, colors.slate, colors.purple], borderWidth: 0, hoverOffset: 8 }]
        },
        options: { ...defaultOpts, cutout: '65%', plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 12, font: { size: 10 } } } } }
    });

    // ── Blood Group Bar ──
    new Chart(document.getElementById('bloodChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($bloodGroupCounts, 'blood_group')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($bloodGroupCounts, 'cnt'))) ?>, backgroundColor: colors.rose, borderRadius: 6, barThickness: 20 }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, y: { grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });

    // ── Class Distribution Bar ──
    new Chart(document.getElementById('classChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($c) => $c['class_name'] . ($c['section'] ? ' ('.$c['section'].')' : ''), $classCounts)) ?>,
            datasets: [{ label: 'Students', data: <?= json_encode(array_map('intval', array_column($classCounts, 'cnt'))) ?>, backgroundColor: palette.slice(0, count($classCounts)), borderRadius: 6 }]
        },
        options: { ...defaultOpts, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, y: { grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });

    // ── Age Distribution Bar ──
    new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($ageDistribution, 'age_group')) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($ageDistribution, 'cnt'))) ?>, backgroundColor: [colors.cyan, colors.blue, colors.brand, colors.purple, colors.rose], borderRadius: 6 }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });

    // ── Enrollment Trend Line ──
    new Chart(document.getElementById('enrollmentTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($enrollmentTrend, 'month')) ?>,
            datasets: [{
                label: 'Enrollments', data: <?= json_encode(array_map('intval', array_column($enrollmentTrend, 'cnt'))) ?>,
                borderColor: colors.brand, backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: colors.brand
            }]
        },
        options: { ...defaultOpts, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true } } }
    });

    // ── Attendance Stacked Bar ──
    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($attendanceOverview, 'month')) ?>,
            datasets: [
                { label: 'Present', data: <?= json_encode(array_map('intval', array_column($attendanceOverview, 'present_cnt'))) ?>, backgroundColor: colors.emerald, borderRadius: 4 },
                { label: 'Late', data: <?= json_encode(array_map('intval', array_column($attendanceOverview, 'late_cnt'))) ?>, backgroundColor: colors.amber, borderRadius: 4 },
                { label: 'Absent', data: <?= json_encode(array_map('intval', array_column($attendanceOverview, 'absent_cnt'))) ?>, backgroundColor: colors.rose, borderRadius: 4 },
                { label: 'Half Day', data: <?= json_encode(array_map('intval', array_column($attendanceOverview, 'half_day_cnt'))) ?>, backgroundColor: colors.orange, borderRadius: 4 }
            ]
        },
        options: { ...defaultOpts, plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 10, font: { size: 10 } } } }, scales: { x: { stacked: true, grid: { display: false }, ticks: { color: tickColor } }, y: { stacked: true, grid: { color: gridColor }, ticks: { color: tickColor } } } }
    });

    // ── Exam Performance Bar ──
    new Chart(document.getElementById('examChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($examPerformance, 'exam_name')) ?>,
            datasets: [{
                label: 'Avg %', data: <?= json_encode(array_map(fn($e) => $e['avg_max'] > 0 ? round($e['avg_marks'] / $e['avg_max'] * 100) : 0, $examPerformance)) ?>,
                backgroundColor: <?= json_encode(array_map(fn($e) => $e['avg_max'] > 0 && ($e['avg_marks']/$e['avg_max']*100) >= 50 ? colors.emerald : colors.rose, $examPerformance)) ?>,
                borderRadius: 6
            }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 9 }, maxRotation: 45 } }, y: { grid: { color: gridColor }, ticks: { color: tickColor }, max: 100 } } }
    });

    // ── Fee Collection Trend Line ──
    new Chart(document.getElementById('feeTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($feeTrend, 'month')) ?>,
            datasets: [{
                label: 'Collected', data: <?= json_encode(array_map('floatval', array_column($feeTrend, 'collected'))) ?>,
                borderColor: colors.emerald, backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: colors.emerald
            }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹' + (v/1000).toFixed(0) + 'k' }, beginAtZero: true } } }
    });
});
</script>

<?php
$content = ob_get_clean();
?>
