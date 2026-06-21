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
    </div>

    <!-- Receipts Registry -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Receipt ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Invoice / Account</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Amount Paid</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Method & Reference</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Payment Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            No payment receipts found in the registry.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-slate-900/30 transition-colors group">
                            <!-- Receipt ID -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white font-mono">
                                REC-<?= str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT) ?>
                            </td>

                            <!-- Student -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></p>
                                        <p class="text-xs text-slate-500 font-mono"><?= e($p['admission_number']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Invoice Title -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <p class="font-medium text-white"><?= e($p['invoice_title']) ?></p>
                                <p class="text-xs text-slate-500 font-mono"><?= e($p['invoice_number']) ?></p>
                            </td>

                            <!-- Amount -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold text-emerald-400">
                                <?= number_format((float)$p['amount'], 2) ?> INR
                            </td>

                            <!-- Method -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <span class="font-medium text-slate-200"><?= e($p['payment_method']) ?></span>
                                <?php if ($p['payment_ref']): ?>
                                <span class="block text-xs text-slate-500 font-mono"><?= e($p['payment_ref']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Payment Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                <?= format_date($p['paid_at'], 'd M Y, H:i') ?>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <a href="<?= url('receipts/' . $p['id'] . '/view') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Voucher
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
