<?php
$layout = 'app';
$pageTitle = 'Financial Reports & Analytics';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Finance']];
ob_start();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Financial Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Detailed overview of revenue, invoices, and billing statistics.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"></path></svg>
            Print Report
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Collected -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Collected</h3>
                    <p class="text-2xl font-bold text-white">₹<?= number_format((float)$totalRevenue, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Expected -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Expected</h3>
                    <p class="text-2xl font-bold text-white">₹<?= number_format((float)$totalExpected, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Outstanding Balance</h3>
                    <p class="text-2xl font-bold text-white">₹<?= number_format((float)$outstandingFees, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Overdue Invoices</h3>
                    <p class="text-2xl font-bold text-white"><?= number_format($overdueCount) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Collections by Mode -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <h3 class="text-base font-bold text-white mb-4">Collections by Payment Mode</h3>
            <div class="space-y-4 flex-1 flex flex-col justify-center">
                <?php if (empty($collectionsByMode)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No collection logs available.</p>
                <?php else: ?>
                    <?php foreach ($collectionsByMode as $mode): ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-slate-350"><?= e($mode['payment_mode']) ?> (<?= $mode['cnt'] ?>)</span>
                                <span class="font-bold text-white">₹<?= number_format((float)$mode['total'], 2) ?></span>
                            </div>
                            <div class="w-full bg-slate-850 rounded-full h-2">
                                <?php 
                                $percent = $totalRevenue > 0 ? ($mode['total'] / $totalRevenue) * 100 : 0; 
                                ?>
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: <?= min(100, max(2, $percent)) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Payments Transaction Log -->
        <div class="lg:col-span-2 bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Recent Payment Logs (10 latest)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 font-semibold">
                            <th class="py-3 px-4">Receipt Ref</th>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Fee Structure / Invoice</th>
                            <th class="py-3 px-4">Mode</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Paid Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-slate-300">
                        <?php if (empty($recentPayments)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No payment logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-4 font-mono text-xs text-indigo-400">REC-<?= sprintf("%05d", $p['id']) ?></td>
                                    <td class="py-3 px-4"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                                    <td class="py-3 px-4">
                                        <p class="text-white font-medium"><?= e($p['invoice_title']) ?></p>
                                        <span class="text-xs text-slate-500"><?= e($p['invoice_number']) ?></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700 capitalize">
                                            <?= e($p['payment_method'] ?? 'Online') ?>
                                        </span>
                                    </td>
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
</div>

<?php
$content = ob_get_clean();
?>
