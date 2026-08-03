<?php
$layout    = 'app';
$pageTitle = 'Fees Dashboard';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees Dashboard']];
ob_start();
?>

<!-- Load Chart.js for beautiful analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div x-data="{ 
    paymentModal: false, 
    invoiceId: null, 
    invoiceTitle: '', 
    maxAmount: 0,
    openPayment(id, title, maxAmt) {
        this.invoiceId = id;
        this.invoiceTitle = title;
        this.maxAmount = maxAmt;
        this.paymentModal = true;
    }
}" class="space-y-8 animate-fade-in-up pb-10">

    <!-- Hero Header section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 p-8 shadow-2xl border border-indigo-500/30">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTU0LjYyNyAwTDYwIDUuMzczdjU0LjYyN0wwIDBoNTQuNjI3eiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIuMDUiLz48L3N2Zz4=')] opacity-30"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">Financial Overview</h1>
                <p class="text-indigo-200 mt-2 text-sm font-medium">Real-time ledger analytics & revenue management</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= url('fees/export') ?>" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-lg border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Ledger
                </a>
                <a href="<?= url('fees/batch-generator') ?>" class="inline-flex items-center gap-2 bg-indigo-500/80 hover:bg-indigo-500 backdrop-blur-md text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-lg border border-indigo-400/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Batch Generator
                </a>
                <a href="<?= url('fees/create') ?>" class="inline-flex items-center gap-2 bg-white text-indigo-900 hover:bg-indigo-50 px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Invoice
                </a>
            </div>
        </div>
    </div>

    <!-- Glassmorphic Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue -->
        <div class="group relative p-6 rounded-3xl bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl border border-white/50 dark:border-slate-800/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] transition-transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Expected Revenue</p>
                <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white">₹<?= number_format((float)($stats['total_revenue_expected'] ?? 0)) ?></h3>
            </div>
        </div>

        <!-- Collected -->
        <div class="group relative p-6 rounded-3xl bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl border border-white/50 dark:border-slate-800/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] transition-transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Total Collected</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">₹<?= number_format((float)($stats['total_collected'] ?? 0)) ?></h3>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="group relative p-6 rounded-3xl bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl border border-white/50 dark:border-slate-800/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] transition-transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4 text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Outstanding Balance</p>
                <h3 class="text-3xl font-extrabold text-amber-500">₹<?= number_format((float)($stats['total_outstanding'] ?? 0)) ?></h3>
            </div>
        </div>

        <!-- Overdue -->
        <div class="group relative p-6 rounded-3xl bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl border border-white/50 dark:border-slate-800/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] transition-transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-pink-500/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4 text-rose-600 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Overdue Invoices</p>
                <h3 class="text-3xl font-extrabold text-rose-500"><?= $stats['overdue_count'] ?></h3>
            </div>
        </div>
    </div>

    <!-- Charts & Activities Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Collection Chart -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Revenue Analytics</h3>
            <div class="relative h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Recent Online Payments -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-indigo-50 to-white dark:from-slate-800 dark:to-slate-900 border border-indigo-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Recent Online Payments
            </h3>
            <div class="flex-1 overflow-y-auto pr-2 space-y-4">
                <?php if (empty($recentParentPayments)): ?>
                    <p class="text-sm text-slate-500 text-center py-8">No recent online payments.</p>
                <?php else: ?>
                    <?php foreach ($recentParentPayments as $rp): ?>
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate"><?= e($rp['first_name'] . ' ' . $rp['last_name']) ?></p>
                            <p class="text-xs text-slate-500 truncate mt-0.5"><?= e($rp['invoice_title']) ?></p>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-semibold tracking-wider"><?= date('M d, Y', strtotime($rp['paid_at'])) ?> &bull; <?= e($rp['payment_method']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">+₹<?= number_format($rp['amount']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Active Invoices</h3>
            <form method="GET" action="<?= url('fees') ?>" class="flex w-full sm:w-auto gap-3">
                <input type="text" name="search" placeholder="Search invoices..." value="<?= e($search) ?>" class="w-full sm:w-64 bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-indigo-500">
                <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="">All Statuses</option>
                    <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-6 py-4">Invoice #</th>
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Details</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($invoices)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No invoices found matching your criteria.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="font-mono font-medium text-slate-900 dark:text-slate-300"><?= e($inv['invoice_number']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    <?= strtoupper(substr($inv['first_name'], 0, 1) . substr($inv['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></p>
                                    <p class="text-[11px] text-slate-500"><?= e($inv['admission_number']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-800 dark:text-slate-200"><?= e($inv['title']) ?></p>
                            <p class="text-xs text-slate-500 mt-0.5">Due: <?= date('M d, Y', strtotime($inv['due_date'])) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 dark:text-white">₹<?= number_format($inv['amount'], 2) ?></p>
                            <?php if ($inv['paid_amount'] > 0): ?>
                            <p class="text-xs text-emerald-500 font-medium">Paid: ₹<?= number_format($inv['paid_amount'], 2) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Paid
                                </span>
                            <?php elseif ($inv['status'] === 'unpaid' && strtotime($inv['due_date']) < time()): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Overdue
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php if ($inv['status'] !== 'paid'): ?>
                            <button @click="openPayment(<?= $inv['id'] ?>, '<?= e($inv['title']) ?>', <?= $inv['amount'] - $inv['paid_amount'] ?>)" 
                                    class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Collect
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Collect Payment Modal (Glassmorphic) -->
    <div x-show="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" x-cloak>
        <div x-show="paymentModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="paymentModal = false"></div>
        <div x-show="paymentModal" x-transition.scale.origin.bottom class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-800">
            <form :action="'<?= url('fees/') ?>' + invoiceId + '/pay'" method="POST" class="flex flex-col">
                <?= \Core\View::csrf() ?>
                <div class="px-8 py-6 bg-gradient-to-br from-indigo-50 to-white dark:from-slate-800 dark:to-slate-900 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Collect Payment</h3>
                    <p class="text-sm text-slate-500 mt-1 font-medium" x-text="invoiceTitle"></p>
                </div>
                <div class="p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Amount to Collect (INR) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500 font-bold">₹</span>
                            <input type="number" step="0.01" name="amount" :max="maxAmount" :value="maxAmount" required 
                                   class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-3 pl-9 pr-4 font-bold text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-3 px-4 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="UPI">UPI / Digital Wallet</option>
                            <option value="Card">Credit/Debit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Reference / Txn ID (Optional)</label>
                        <input type="text" name="payment_ref" placeholder="e.g. UPI-12345678" 
                               class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-3 px-4 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                    </div>
                </div>
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="paymentModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/30 transform hover:-translate-y-0.5">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient for the chart line
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.5)'); // Indigo
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue Collected',
                data: [12000, 19000, 15000, 22000, 18000, 24000, 21000, 28000, 25000, 31000, 29000, 35000],
                borderColor: '#6366f1',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 13, family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { family: "'Inter', sans-serif" }, callback: function(value) { return '₹' + value / 1000 + 'k'; } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#94a3b8', font: { family: "'Inter', sans-serif" } }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    });
});
</script>

<?php
$content = ob_get_clean();

