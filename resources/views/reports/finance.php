<?php
$layout = 'app';
$pageTitle = 'Financial Reports & Analytics';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Finance']];
ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Financial Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Revenue, invoices, collections, class-wise and category-wise breakdown.</p>
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
            <div class="text-3xl font-extrabold text-emerald-400">₹<?= number_format($totalRevenue, 0) ?></div>
            <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Collected</div>
        </div>
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-blue-400">₹<?= number_format($totalExpected, 0) ?></div>
            <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Expected</div>
        </div>
        <div class="bg-red-950/30 border border-red-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-red-400">₹<?= number_format($outstandingFees, 0) ?></div>
            <div class="text-2xs text-red-500/70 font-semibold mt-1 uppercase tracking-wider">Outstanding</div>
        </div>
        <div class="bg-amber-950/30 border border-amber-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-amber-400"><?= number_format($overdueCount) ?></div>
            <div class="text-2xs text-amber-500/70 font-semibold mt-1 uppercase tracking-wider">Overdue Invoices</div>
        </div>
        <div class="bg-purple-950/30 border border-purple-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-purple-400"><?= number_format($collectionRate) ?>%</div>
            <div class="text-2xs text-purple-500/70 font-semibold mt-1 uppercase tracking-wider">Collection Rate</div>
        </div>
        <div class="bg-emerald-950/30 border border-emerald-800/40 rounded-2xl p-5 text-center">
            <div class="text-3xl font-extrabold text-emerald-400"><?= number_format($totalInvoiceCount) ?></div>
            <div class="text-2xs text-emerald-500/70 font-semibold mt-1 uppercase tracking-wider">Total Invoices</div>
        </div>
    </div>

    <!-- Secondary KPI row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-emerald-950/20 border border-emerald-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-emerald-400"><?= number_format($paidCount) ?></div>
            <div class="text-2xs text-emerald-500/60">Paid</div>
        </div>
        <div class="bg-red-950/20 border border-red-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-red-400"><?= number_format($unpaidCount) ?></div>
            <div class="text-2xs text-red-500/60">Unpaid</div>
        </div>
        <div class="bg-amber-950/20 border border-amber-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-amber-400"><?= number_format($partialCount) ?></div>
            <div class="text-2xs text-amber-500/60">Partially Paid</div>
        </div>
        <div class="bg-purple-950/20 border border-purple-800/30 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-purple-400">₹<?= number_format($discountSummary['total_discount'] ?? 0, 0) ?></div>
            <div class="text-2xs text-purple-500/60">Total Discounts</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 1: Monthly Trend + Invoice Status -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Collection Trend -->
        <div class="lg:col-span-2 bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Monthly Collection Trend (12 Months)</h3>
            <div class="relative h-72"><canvas id="monthlyTrendChart"></canvas></div>
        </div>

        <!-- Invoice Status Doughnut -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Invoice Status</h3>
            <div class="relative h-56"><canvas id="invoiceStatusChart"></canvas></div>
            <div class="mt-3 space-y-2">
                <?php foreach ($invoiceStatusCounts as $s): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400 capitalize"><?= e($s['status']) ?></span>
                    <span class="text-white font-bold"><?= number_format($s['cnt']) ?> &middot; ₹<?= number_format($s['total_amount'], 0) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 2: Payment Mode + Outstanding Trend -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Mode Pie -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Collections by Payment Mode</h3>
            <div class="relative h-64"><canvas id="paymentModeChart"></canvas></div>
            <div class="mt-3 space-y-2">
                <?php foreach ($collectionsByMode as $m): ?>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400"><?= e($m['payment_mode']) ?> (<?= $m['cnt'] ?>)</span>
                    <span class="text-white font-bold">₹<?= number_format($m['total'], 0) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Outstanding by Month -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Outstanding Balance Trend</h3>
            <div class="relative h-64"><canvas id="outstandingTrendChart"></canvas></div>
            <?php if ($overdueAmount > 0): ?>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-red-400 font-bold">₹<?= number_format($overdueAmount, 0) ?></span>
                <span class="text-slate-500">overdue beyond due date</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 3: Category + Class-wise Collection -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category Collection Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Fee Category Collection</h3>
            <?php if (empty($categoryCollection)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No fee categories configured.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="categoryChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Class-wise Collection Bar -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Class-wise Collection</h3>
            <?php if (empty($classWiseCollection)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No class-wise data available.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="classWiseChart"></canvas></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 4: Daily Collection + Top Paying Students -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Collection This Month -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Daily Collection (This Month)</h3>
            <?php if (empty($dailyCollection)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No collections this month yet.</p>
            <?php else: ?>
            <div class="relative h-64"><canvas id="dailyChart"></canvas></div>
            <?php endif; ?>
        </div>

        <!-- Top Paying Students -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Top Paying Students</h3>
            <?php if (empty($topPayingStudents)): ?>
                <p class="text-sm text-slate-500 text-center py-8">No payment records yet.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-slate-500 border-b border-slate-800">
                        <th class="text-left py-2">#</th>
                        <th class="text-left py-2">Student</th>
                        <th class="text-left py-2">Class</th>
                        <th class="text-right py-2">Paid</th>
                        <th class="text-right py-2">Txns</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topPayingStudents as $i => $ts): ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2 text-slate-500"><?= $i + 1 ?></td>
                        <td class="py-2 text-white font-medium"><?= e($ts['first_name'] . ' ' . $ts['last_name']) ?></td>
                        <td class="py-2 text-slate-400"><?= e($ts['class'] ?? '-') ?></td>
                        <td class="py-2 text-right text-emerald-400 font-bold">₹<?= number_format($ts['total_paid'], 0) ?></td>
                        <td class="py-2 text-right text-slate-400"><?= $ts['payment_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ROW 5: Recent Payments -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-base font-bold text-white mb-4">Recent Payment Logs</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold">
                        <th class="py-3 px-4">Receipt</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-300">
                    <?php if (empty($recentPayments)): ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">No payment logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $p): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="py-3 px-4 font-mono text-xs text-indigo-400">REC-<?= sprintf("%05d", $p['id']) ?></td>
                            <td class="py-3 px-4"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                            <td class="py-3 px-4">
                                <p class="text-white font-medium text-xs"><?= e($p['invoice_title']) ?></p>
                                <span class="text-2xs text-slate-500"><?= e($p['invoice_number']) ?></span>
                            </td>
                            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700 capitalize"><?= e($p['payment_method'] ?? 'N/A') ?></span></td>
                            <td class="py-3 px-4 font-semibold text-emerald-400">₹<?= number_format((float)$p['amount'], 2) ?></td>
                            <td class="py-3 px-4 text-xs text-slate-400"><?= date('d M Y, h:i A', strtotime($p['paid_at'] ?? $p['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const palette = ['#10b981','#3b82f6','#f43f5e','#f59e0b','#a855f7','#06b6d4','#f97316','#ec4899','#14b8a6','#84cc16'];
    const gridColor = 'rgba(148,163,184,0.08)';
    const tickColor = '#64748b';
    const defaultOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };

    // ── Monthly Trend Line ──
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthlyTrend, 'month')) ?>,
            datasets: [{
                label: 'Collected', data: <?= json_encode(array_map('floatval', array_column($monthlyTrend, 'collected'))) ?>,
                borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#10b981'
            },{
                label: 'Transactions', data: <?= json_encode(array_map('intval', array_column($monthlyTrend, 'txn_count'))) ?>,
                borderColor: '#6366f1', backgroundColor: 'transparent', borderDash: [5,5], tension: 0.4, pointRadius: 3, yAxisID: 'y1'
            }]
        },
        options: {
            ...defaultOpts,
            plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 10, font: { size: 10 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' }, beginAtZero: true },
                y1: { position: 'right', grid: { display: false }, ticks: { color: '#6366f1' }, beginAtZero: true }
            }
        }
    });

    // ── Invoice Status Doughnut ──
    new Chart(document.getElementById('invoiceStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(fn($s) => ucfirst(str_replace('_',' ',$s['status'])), $invoiceStatusCounts)) ?>,
            datasets: [{ data: <?= json_encode(array_map('intval', array_column($invoiceStatusCounts, 'cnt'))) ?>, backgroundColor: ['#10b981','#f43f5e','#f59e0b','#64748b','#a855f7'], borderWidth: 0, hoverOffset: 6 }]
        },
        options: { ...defaultOpts, cutout: '60%', plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } } }
    });

    // ── Payment Mode Pie ──
    new Chart(document.getElementById('paymentModeChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($collectionsByMode, 'payment_mode')) ?>,
            datasets: [{ data: <?= json_encode(array_map('floatval', array_column($collectionsByMode, 'total'))) ?>, backgroundColor: palette, borderWidth: 0 }]
        },
        options: { ...defaultOpts, plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } } }
    });

    // ── Outstanding Trend Bar ──
    new Chart(document.getElementById('outstandingTrendChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($outstandingByMonth, 'month')) ?>,
            datasets: [{
                label: 'Outstanding', data: <?= json_encode(array_map('floatval', array_column($outstandingByMonth, 'outstanding'))) ?>,
                backgroundColor: '#f43f5e', borderRadius: 4
            },{
                label: 'Invoices', data: <?= json_encode(array_map('intval', array_column($outstandingByMonth, 'invoice_count'))) ?>,
                backgroundColor: '#f59e0b', borderRadius: 4, yAxisID: 'y1'
            }]
        },
        options: {
            ...defaultOpts,
            plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 8, padding: 8, font: { size: 10 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' }, beginAtZero: true },
                y1: { position: 'right', grid: { display: false }, ticks: { color: '#f59e0b' }, beginAtZero: true }
            }
        }
    });

    // ── Category Bar ──
    <?php if (!empty($categoryCollection)): ?>
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($categoryCollection, 'category_name')) ?>,
            datasets: [{ data: <?= json_encode(array_map('floatval', array_column($categoryCollection, 'collected'))) ?>, backgroundColor: palette.slice(0, count($categoryCollection)), borderRadius: 6 }]
        },
        options: { ...defaultOpts, indexAxis: 'y', scales: { y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, x: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' } } } }
    });
    <?php endif; ?>

    // ── Class-wise Collection Bar ──
    <?php if (!empty($classWiseCollection)): ?>
    new Chart(document.getElementById('classWiseChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($c) => $c['class_name'].($c['section']?' ('.$c['section'].')':''), $classWiseCollection)) ?>,
            datasets: [
                { label: 'Collected', data: <?= json_encode(array_map('floatval', array_column($classWiseCollection, 'collected'))) ?>, backgroundColor: '#10b981', borderRadius: 4 },
                { label: 'Billed', data: <?= json_encode(array_map('floatval', array_column($classWiseCollection, 'billed'))) ?>, backgroundColor: '#3b82f6', borderRadius: 4 }
            ]
        },
        options: {
            ...defaultOpts,
            plugins: { legend: { display: true, position: 'bottom', labels: { color: tickColor, boxWidth: 10, padding: 10, font: { size: 10 } } } },
            scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 9 }, maxRotation: 45 } }, y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+(v/1000).toFixed(0)+'k' }, beginAtZero: true } }
        }
    });
    <?php endif; ?>

    // ── Daily Collection Bar ──
    <?php if (!empty($dailyCollection)): ?>
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d['pay_date'])), $dailyCollection)) ?>,
            datasets: [{ data: <?= json_encode(array_map('floatval', array_column($dailyCollection, 'daily_total'))) ?>, backgroundColor: '#a855f7', borderRadius: 6, barThickness: 16 }]
        },
        options: { ...defaultOpts, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } }, y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: v => '₹'+v.toLocaleString() }, beginAtZero: true } } }
    });
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
?>
