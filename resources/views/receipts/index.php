<?php
$layout    = 'app';
$pageTitle = 'Payment Receipts';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Receipts']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Payment Receipts</h2>
            <p class="text-sm text-slate-500 mt-0.5">View and print official payment receipts and invoices</p>
        </div>
        <a href="/psnf/public/certificate_generator/" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90"
           style="background: linear-gradient(135deg, #10b981, #059669);">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Designer Receipts
        </a>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="<?= url('receipts') ?>" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" placeholder="Search by Student, Invoice, Reference, or Receipt ID..." value="<?= e($search ?? '') ?>"
                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white placeholder-slate-400 rounded-xl py-2 pl-9 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
            </div>
            <?php if (!empty($search)): ?>
            <a href="<?= url('receipts') ?>" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-450 hover:text-slate-800 dark:hover:text-white rounded-xl text-sm transition-colors bg-slate-50 dark:bg-slate-900">
                Clear Search
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Receipts Registry -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Receipt ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Amount Paid</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            No payment receipts found matching your search.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors group">
                            <!-- Receipt ID & Linked Invoice -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 dark:text-white">
                                <p class="font-bold font-mono">REC-<?= str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT) ?></p>
                                <p class="text-3xs text-slate-500 font-mono mt-0.5">Inv: <?= e($p['invoice_number']) ?> (<?= e($p['invoice_title']) ?>)</p>
                            </td>

                            <!-- Student -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></p>
                                        <p class="text-xs text-slate-500 font-mono">Adm: <?= e($p['admission_number']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Amount Paid -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                <?= number_format((float)$p['amount'], 2) ?> INR
                            </td>

                            <!-- Method -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-semibold"><?= e($p['payment_method']) ?></span>
                                <?php if ($p['payment_ref']): ?>
                                <span class="block text-2xs text-slate-500 font-mono">Ref: <?= e($p['payment_ref']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Payment Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-400 font-mono">
                                <?= format_date($p['paid_at'], 'd M Y, H:i') ?>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <a href="<?= url('receipts/' . $p['id'] . '/view') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700/50 shadow-sm hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    View & Print
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
