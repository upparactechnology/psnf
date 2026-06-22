<?php
$layout    = 'app';
$pageTitle = 'Fees & Invoicing';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees']];
ob_start();
?>

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
}" class="space-y-6">

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Invoiced -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Invoiced</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-3"><?= number_format((float)($stats['total_invoiced'] ?? 0)) ?> INR</p>
            <p class="text-xs text-slate-500 mt-1">Cumulative invoicing</p>
        </div>

        <!-- Card 2: Total Collected -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Collected</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-3 text-emerald-600 dark:text-emerald-400"><?= number_format((float)($stats['total_paid'] ?? 0)) ?> INR</p>
            <p class="text-xs text-slate-500 mt-1">Total revenue recorded</p>
        </div>

        <!-- Card 3: Outstanding Balance -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding Balance</span>
                <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-3 text-amber-600 dark:text-amber-500"><?= number_format((float)($stats['total_unpaid'] ?? 0)) ?> INR</p>
            <p class="text-xs text-slate-500 mt-1">Pending payments</p>
        </div>

        <!-- Card 4: Overdue Invoices -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overdue Invoices</span>
                <span class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-3 text-red-600 dark:text-red-400"><?= $stats['overdue_count'] ?></p>
            <p class="text-xs text-slate-500 mt-1">Past due date</p>
        </div>
    </div>

    <!-- View Switcher Tabs -->
    <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-6">
        <a href="<?= url('fees?view=pending' . ($search ? '&search=' . urlencode($search) : '') . ($status ? '&status=' . urlencode($status) : '')) ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none <?= $view === 'pending' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Pending Fee Collection
            <span class="inline-flex items-center justify-center px-2 py-0.5 ml-1 text-2xs font-semibold rounded-full bg-amber-50 dark:bg-amber-955/40 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/30">
                <?= count(array_filter($invoices, function($inv) { return $inv['status'] !== 'paid'; })) ?>
            </span>
        </a>
        <a href="<?= url('fees?view=kanban' . ($search ? '&search=' . urlencode($search) : '') . ($status ? '&status=' . urlencode($status) : '')) ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none <?= $view === 'kanban' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            Kanban Fee Collection
        </a>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="<?= url('fees') ?>" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
            <input type="hidden" name="view" value="<?= e($view) ?>">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" placeholder="Search by student name or invoice..." value="<?= e($search) ?>"
                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white placeholder-slate-400 rounded-xl py-2 pl-9 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
            </div>
            
            <select name="status" onchange="this.form.submit()"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <option value="">All Statuses</option>
                <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="partially_paid" <?= $status === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
            </select>
        </form>

        <a href="<?= url('fees/create') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90 w-full sm:w-auto justify-center"
           style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Invoice
        </a>
    </div>

    <?php if ($view === 'pending'): ?>
    <!-- Invoices Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 overflow-hidden shadow-sm">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/20">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Title</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                <?php 
                $pendingInvoices = array_filter($invoices, function($inv) { return $inv['status'] !== 'paid'; });
                ?>
                <?php if (empty($pendingInvoices)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        No pending invoices found.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($pendingInvoices as $inv): ?>
                <?php
                $statusColors = [
                    'unpaid'         => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/20',
                    'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-955/30 dark:text-amber-400 dark:border-amber-900/20',
                    'paid'           => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/20',
                ];
                $sc = $statusColors[$inv['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                $unpaidVal = (float)$inv['amount'] - (float)$inv['paid_amount'];
                ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/10 transition-colors group">
                    <td class="px-5 py-4">
                        <span class="text-xs font-semibold font-mono text-slate-850 dark:text-slate-300"><?= e($inv['invoice_number']) ?></span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-medium text-slate-800 dark:text-white">
                            <?= e($inv['first_name'] . ' ' . $inv['last_name']) ?>
                        </div>
                        <span class="text-2xs text-slate-500 font-mono"><?= e($inv['admission_number']) ?></span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm text-slate-700 dark:text-slate-300 font-medium"><?= e($inv['title']) ?></div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-bold text-slate-900 dark:text-white"><?= number_format((float)$inv['amount']) ?> INR</div>
                        <?php if ((float)$inv['paid_amount'] > 0): ?>
                        <span class="text-3xs text-emerald-600 dark:text-emerald-500">Paid: <?= number_format((float)$inv['paid_amount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs text-slate-600 dark:text-slate-400"><?= e($inv['due_date']) ?></span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex text-3xs px-2 py-0.5 rounded-full font-semibold border <?= $sc ?>">
                            <?= strtoupper($inv['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if ($inv['status'] !== 'paid'): ?>
                            <button @click="openPayment(<?= $inv['id'] ?>, '<?= e($inv['title']) ?>', <?= $unpaidVal ?>)"
                                    class="px-2.5 py-1 text-2xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-all"
                                    title="Record Payment">
                                Record Pay
                            </button>
                            <?php endif; ?>
                            
                            <form action="<?= url("fees/{$inv['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <?php
        $unpaidInvoices = array_filter($invoices, function($inv) { return $inv['status'] === 'unpaid'; });
        $partiallyPaidInvoices = array_filter($invoices, function($inv) { return $inv['status'] === 'partially_paid'; });
        $paidInvoices = array_filter($invoices, function($inv) { return $inv['status'] === 'paid'; });
        ?>
        
        <!-- Unpaid Column -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/10 p-4 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-850">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm shadow-red-500/50"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Unpaid</h3>
                </div>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-3xs font-semibold rounded-full bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200/50 dark:border-red-900/20">
                    <?= count($unpaidInvoices) ?>
                </span>
            </div>
            
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                <?php if (empty($unpaidInvoices)): ?>
                <p class="text-xs text-center py-8 text-slate-400 dark:text-slate-500 italic">No unpaid invoices.</p>
                <?php else: ?>
                    <?php foreach ($unpaidInvoices as $inv): ?>
                    <?php
                    $unpaidVal = (float)$inv['amount'] - (float)$inv['paid_amount'];
                    ?>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900/50 shadow-sm space-y-3 hover:border-slate-350 dark:hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                <?= e($inv['invoice_number']) ?>
                            </span>
                            <span class="text-3xs font-semibold px-2 py-0.5 rounded-full border bg-red-50 text-red-700 border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/20">
                                UNPAID
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate" title="<?= e($inv['title']) ?>"><?= e($inv['title']) ?></h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 font-semibold"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></p>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500 font-mono">Adm: <?= e($inv['admission_number']) ?></p>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800/65 pt-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Amount</p>
                                <p class="text-sm font-extrabold text-slate-800 dark:text-white"><?= number_format((float)$inv['amount']) ?> INR</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Due Date</p>
                                <p class="text-xs text-slate-655 dark:text-slate-400 font-mono font-semibold"><?= e($inv['due_date']) ?></p>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                            <button @click="openPayment(<?= $inv['id'] ?>, '<?= e($inv['title']) ?>', <?= $unpaidVal ?>)"
                                    class="flex-1 py-1.5 text-center text-3xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-all border border-emerald-200/40 dark:border-emerald-900/20">
                                Record Pay
                            </button>
                            <form action="<?= url("fees/{$inv['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?')" class="inline-block flex-1">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="w-full py-1.5 text-center text-3xs font-bold text-red-655 dark:text-red-405 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all border border-red-200/40 dark:border-red-900/20">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Partially Paid Column -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/10 p-4 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-850">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Partially Paid</h3>
                </div>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-3xs font-semibold rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/30">
                    <?= count($partiallyPaidInvoices) ?>
                </span>
            </div>
            
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                <?php if (empty($partiallyPaidInvoices)): ?>
                <p class="text-xs text-center py-8 text-slate-400 dark:text-slate-500 italic">No partially paid invoices.</p>
                <?php else: ?>
                    <?php foreach ($partiallyPaidInvoices as $inv): ?>
                    <?php
                    $unpaidVal = (float)$inv['amount'] - (float)$inv['paid_amount'];
                    ?>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-855 bg-white dark:bg-slate-900/50 shadow-sm space-y-3 hover:border-slate-350 dark:hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                <?= e($inv['invoice_number']) ?>
                            </span>
                            <span class="text-3xs font-semibold px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/20">
                                PARTIAL
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate" title="<?= e($inv['title']) ?>"><?= e($inv['title']) ?></h4>
                            <p class="text-xs text-slate-650 dark:text-slate-400 mt-1 font-semibold"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></p>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500 font-mono">Adm: <?= e($inv['admission_number']) ?></p>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800/60 pt-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Amount</p>
                                <p class="text-sm font-extrabold text-slate-800 dark:text-white"><?= number_format((float)$inv['amount']) ?> INR</p>
                                <p class="text-[9px] text-emerald-600 dark:text-emerald-500 font-medium">Paid: <?= number_format((float)$inv['paid_amount']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Due Date</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 font-mono font-semibold"><?= e($inv['due_date']) ?></p>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                            <button @click="openPayment(<?= $inv['id'] ?>, '<?= e($inv['title']) ?>', <?= $unpaidVal ?>)"
                                    class="flex-1 py-1.5 text-center text-3xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-all border border-emerald-200/40 dark:border-emerald-900/20">
                                Record Pay
                            </button>
                            <form action="<?= url("fees/{$inv['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?')" class="inline-block flex-1">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="w-full py-1.5 text-center text-3xs font-bold text-red-655 dark:text-red-405 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all border border-red-200/40 dark:border-red-900/20">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Paid Column -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/10 p-4 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-850">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Paid</h3>
                </div>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-3xs font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">
                    <?= count($paidInvoices) ?>
                </span>
            </div>
            
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                <?php if (empty($paidInvoices)): ?>
                <p class="text-xs text-center py-8 text-slate-400 dark:text-slate-500 italic">No paid invoices.</p>
                <?php else: ?>
                    <?php foreach ($paidInvoices as $inv): ?>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-855 bg-white dark:bg-slate-900/50 shadow-sm space-y-3 hover:border-slate-350 dark:hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                <?= e($inv['invoice_number']) ?>
                            </span>
                            <span class="text-3xs font-semibold px-2 py-0.5 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/20">
                                PAID
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate" title="<?= e($inv['title']) ?>"><?= e($inv['title']) ?></h4>
                            <p class="text-xs text-slate-650 dark:text-slate-400 mt-1 font-semibold"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></p>
                            <p class="text-[10px] text-slate-455 dark:text-slate-500 font-mono">Adm: <?= e($inv['admission_number']) ?></p>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800/60 pt-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Amount</p>
                                <p class="text-sm font-extrabold text-slate-800 dark:text-white"><?= number_format((float)$inv['amount']) ?> INR</p>
                                <p class="text-[9px] text-emerald-600 dark:text-emerald-500 font-medium">Paid: <?= number_format((float)$inv['paid_amount']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Due Date</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 font-mono font-semibold"><?= e($inv['due_date']) ?></p>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                            <form action="<?= url("fees/{$inv['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?')" class="inline-block w-full">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="w-full py-1.5 text-center text-3xs font-bold text-red-655 dark:text-red-405 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all border border-red-200/40 dark:border-red-900/20">
                                    Delete Invoice
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Slide-over/Modal Backdrop -->
    <div x-show="paymentModal" class="fixed inset-0 overflow-hidden z-[9999]" x-cloak>
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop transition -->
            <div x-show="paymentModal" x-transition.opacity class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="paymentModal = false"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="paymentModal" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="w-screen max-w-md">
                    
                    <form :action="'<?= url('fees') ?>/' + invoiceId + '/pay'" method="POST" class="h-full flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl">
                        <?= \Core\View::csrf() ?>
                        
                        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Record Manual Payment</h3>
                                <p class="text-2xs text-slate-500 mt-0.5" x-text="invoiceTitle"></p>
                            </div>
                            <button type="button" @click="paymentModal = false" class="p-1 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="flex-1 p-6 space-y-4">
                            <!-- Amount Field -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Amount Received (INR)</label>
                                <input type="number" step="0.01" name="amount" required :max="maxAmount" :value="maxAmount"
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-bold font-bold">
                                <p class="text-3xs text-slate-400 mt-1">Maximum outstanding: <span x-text="maxAmount + ' INR'"></span></p>
                            </div>

                            <!-- Payment Method Field -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Payment Method</label>
                                <select name="payment_method" required
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Check">Check</option>
                                    <option value="UPI">UPI / Digital Wallet</option>
                                </select>
                            </div>

                            <!-- Payment Reference Field -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Payment Reference / Transaction ID</label>
                                <input type="text" name="payment_ref" placeholder="e.g. TXN-1002345"
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20 flex gap-3">
                            <button type="button" @click="paymentModal = false"
                                    class="flex-1 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-sm transition-all text-center">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 text-white font-semibold rounded-xl text-sm transition-all text-center"
                                    style="background: linear-gradient(135deg, #10b981, #059669);">
                                Log Payment
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
