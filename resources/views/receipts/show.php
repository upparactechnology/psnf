<?php
$layout    = 'app';
$pageTitle = 'Voucher REC-' . str_pad((string)$payment['id'], 5, '0', STR_PAD_LEFT);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Receipts', 'url' => '/receipts'], ['label' => 'Voucher Details']];
ob_start();
?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Action Toolbar (hidden on print) -->
    <div class="flex items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur print:hidden">
        <a href="<?= url('receipts') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">← Back to Registry</a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Receipt
        </button>
    </div>

    <!-- Official Printable Receipt Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-8 relative overflow-hidden shadow-2xl print:bg-white print:text-black print:border-none print:shadow-none print:p-0">
        
        <!-- Watermark / Paid Stamp (CSS) -->
        <div class="absolute right-8 top-28 select-none pointer-events-none opacity-10 rotate-12 border-4 border-emerald-500 text-emerald-500 font-extrabold uppercase rounded-2xl px-6 py-3 text-3xl tracking-widest print:opacity-40">
            Paid
        </div>

        <!-- Header Block -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-800 pb-6 print:border-slate-300">
            <div>
                <!-- Brand logo/name -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold print:hidden">
                        P
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-white print:text-black"><?= e($payment['tenant_name']) ?></h2>
                        <p class="text-xs text-slate-500 print:text-slate-600">Official Fee Payment Receipt</p>
                    </div>
                </div>
            </div>
            <div class="text-right sm:text-right flex flex-col items-end w-full sm:w-auto">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest print:text-slate-600">Receipt Voucher</span>
                <span class="text-lg font-mono font-bold text-white print:text-black mt-1">REC-<?= str_pad((string)$payment['id'], 5, '0', STR_PAD_LEFT) ?></span>
                <span class="text-2xs text-slate-500 print:text-slate-600 font-mono mt-1">Paid on: <?= date('d M Y, h:i A', strtotime($payment['paid_at'])) ?></span>
            </div>
        </div>

        <!-- Meta Details Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-6 border-b border-slate-800 print:border-slate-300">
            <!-- Student/Recipient details -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider print:text-slate-600">Receipt Issued To</h4>
                <div class="text-sm">
                    <p class="font-bold text-white print:text-black text-base"><?= e($payment['first_name'] . ' ' . $payment['last_name']) ?></p>
                    <p class="text-slate-400 print:text-slate-600 mt-1">Admission Number: <span class="font-mono"><?= e($payment['admission_number']) ?></span></p>
                    <p class="text-slate-400 print:text-slate-600">Class & Section: <?= e($payment['class']) ?> - <?= e($payment['section'] ?: 'Default') ?></p>
                </div>
            </div>
            <!-- Invoice info / transaction details -->
            <div class="space-y-2 md:text-right md:flex md:flex-col md:items-end w-full">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider print:text-slate-600">Payment Reference Details</h4>
                <div class="text-sm md:text-right space-y-1">
                    <p class="text-slate-400 print:text-slate-600">Method: <span class="text-white print:text-black font-semibold"><?= e($payment['payment_method']) ?></span></p>
                    <?php if ($payment['payment_ref']): ?>
                    <p class="text-slate-400 print:text-slate-600">Reference / Txn: <span class="font-mono text-white print:text-black"><?= e($payment['payment_ref']) ?></span></p>
                    <?php endif; ?>
                    <p class="text-slate-400 print:text-slate-600">Linked Invoice: <span class="font-mono text-white print:text-black"><?= e($payment['invoice_number']) ?></span></p>
                </div>
            </div>
        </div>

        <!-- Ledger Table Details -->
        <div class="py-6 border-b border-slate-800 print:border-slate-300">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3.5 print:text-slate-600">Account Items</h4>
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-850 text-slate-400 print:border-slate-300 print:text-slate-600">
                        <th class="py-2 text-xs font-bold">Item Description</th>
                        <th class="py-2 text-right text-xs font-bold">Paid Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850 print:divide-slate-300">
                    <tr class="text-sm">
                        <td class="py-4">
                            <p class="font-bold text-white print:text-black"><?= e($payment['invoice_title']) ?></p>
                            <?php if ($payment['invoice_desc']): ?>
                            <p class="text-xs text-slate-500 print:text-slate-600 mt-1"><?= e($payment['invoice_desc']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-right font-mono font-bold text-white print:text-black">
                            <?= number_format((float)$payment['amount'], 2) ?> INR
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals Block -->
        <div class="flex justify-end pt-6">
            <div class="w-full sm:w-1/2 space-y-2 text-sm text-right">
                <div class="flex justify-between font-bold text-base text-white print:text-black border-t border-slate-800 pt-3 print:border-slate-300">
                    <span>Total Amount Paid:</span>
                    <span class="font-mono text-emerald-400 print:text-black"><?= number_format((float)$payment['amount'], 2) ?> INR</span>
                </div>
                <p class="text-[10px] text-slate-500 print:text-slate-600 italic">This receipt is automatically generated and serves as official proof of payment.</p>
            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
?>
