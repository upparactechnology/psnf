<?php
$layout    = 'app';
$paymentId = str_pad((string)$payment['id'], 5, '0', STR_PAD_LEFT);
$pageTitle = 'Voucher REC-' . $paymentId;
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Receipts', 'url' => '/receipts'], ['label' => 'Voucher Details']];

// Load Custom Receipt Settings
$tenantSettings = json_decode($payment['tenant_settings'] ?? '{}', true) ?: [];
$receiptConfig = $tenantSettings['receipt'] ?? [];
$headerTitle = $receiptConfig['header_title'] ?? 'Official Fee Payment Receipt';
$footerNotes = $receiptConfig['footer_notes'] ?? 'This receipt is automatically generated and serves as official proof of payment.';
$showWatermark = ($receiptConfig['show_watermark'] ?? '1') == '1';
$accentColor = $receiptConfig['accent_color'] ?? '#6366f1';
$fontFamily = $receiptConfig['font_family'] ?? 'Inter';

ob_start();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap');

:root {
    --receipt-accent: <?= $accentColor ?>;
    --receipt-font: <?= $fontFamily ?>;
}

#receipt-card {
    font-family: var(--receipt-font), 'Inter', sans-serif !important;
}

@media print {
    /* Hide layout sidebars, nav, header, flash alerts and control toolbar */
    aside, header, #flash-container, .print\:hidden {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Reset global margins and page background */
    html, body {
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #000000 !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
    }

    /* Un-restrict layout wrappers so contents can render full page */
    .flex.h-screen {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
    }
    
    .flex.flex-col.flex-1 {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
    }

    #main-content {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        height: auto !important;
        width: 100% !important;
        display: block !important;
    }

    .max-w-3xl {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Force the receipt card to fill print layout */
    #receipt-card:not(.custom-template) {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: none !important;
        box-shadow: none !important;
        padding: 24px !important;
        margin: 0 !important;
        width: 100% !important;
        position: relative !important;
        font-family: var(--receipt-font), 'Inter', sans-serif !important;
    }

    #receipt-card.custom-template {
        background-image: url('<?= url('storage/uploads/receipt_templates/' . $bgImage) ?>') !important;
        background-size: 100% 100% !important;
        background-repeat: no-repeat !important;
        background-color: transparent !important;
        print-color-adjust: exact !important;
        -webkit-print-color-adjust: exact !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        aspect-ratio: 1.414/1 !important;
        position: relative !important;
        font-family: var(--receipt-font), 'Inter', sans-serif !important;
    }

    /* Force print colors for standard template only */
    #receipt-card:not(.custom-template) * {
        color: #000000 !important;
        border-color: #cbd5e1 !important;
    }
    
    /* Retain color for stamp if printed */
    #receipt-card .stamp-watermark {
        color: #10b981 !important;
        border-color: #10b981 !important;
        opacity: 0.3 !important;
    }
}
</style>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Action Toolbar (hidden on print) -->
    <div class="flex items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur print:hidden">
        <a href="<?= url('receipts') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">← Back to Registry</a>
        <div class="flex items-center gap-3">
            <?php if (has_permission('view_settings')): ?>
            <a href="<?= url('receipts/settings') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-350 hover:text-white bg-slate-800 border border-slate-700/50 hover:bg-slate-750 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Receipt Designer
            </a>
            <?php endif; ?>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md"
                    style="background: var(--receipt-accent);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
        </div>
    </div>

    <?php if (!empty($bgImage)): ?>
    <!-- Official Printable Receipt Card: Custom Canva Design -->
    <div id="receipt-card" class="custom-template w-full relative bg-white text-black aspect-[1.414/1] shadow-2xl rounded-3xl overflow-hidden print:rounded-none print:shadow-none"
         style="background-image: url('<?= url('storage/uploads/receipt_templates/' . $bgImage) ?>'); background-size: 100% 100%; background-repeat: no-repeat; min-height: 480px; font-family: var(--receipt-font), 'Inter', sans-serif;">
         
         <?php if ($showWatermark): ?>
         <!-- Watermark / Paid Stamp (CSS) -->
         <div class="absolute right-8 top-1/4 select-none pointer-events-none opacity-20 rotate-12 border-4 border-emerald-500 text-emerald-500 font-extrabold uppercase rounded-2xl px-6 py-2 text-xl tracking-widest stamp-watermark print:opacity-30">
             Paid
         </div>
         <?php endif; ?>

         <?php foreach ($mappings as $item): 
             $key = $item['key'] ?? '';
             if (empty($key)) continue;
             $x = $item['x'] ?? 0;
             $y = $item['y'] ?? 0;
             $fontSize = $item['font_size'] ?? 11;
             $fontWeight = $item['font_weight'] ?? 'normal';
             $color = in_array($key, ['amount', 'receipt_number']) ? $accentColor : '#1e293b';
         ?>
             <div class="absolute p-1" style="left: <?= $x ?>%; top: <?= $y ?>%; font-size: <?= $fontSize ?>px; font-weight: <?= $fontWeight ?>; color: <?= $color ?>; <?= !in_array($key, ['footer_notes', 'account_items']) ? 'white-space: nowrap;' : '' ?>">
                 <?php if ($key === 'tenant_name'): ?>
                     <?= e($payment['tenant_name']) ?>
                 <?php elseif ($key === 'header_title'): ?>
                     <?= e($headerTitle) ?>
                 <?php elseif ($key === 'receipt_number'): ?>
                     REC-<?= $paymentId ?>
                 <?php elseif ($key === 'paid_at'): ?>
                     Paid on: <?= date('d M Y, h:i A', strtotime($payment['paid_at'])) ?>
                 <?php elseif ($key === 'student_name'): ?>
                     <?= e($payment['first_name'] . ' ' . $payment['last_name']) ?>
                 <?php elseif ($key === 'admission_num'): ?>
                     Admission: <?= e($payment['admission_number']) ?>
                 <?php elseif ($key === 'class_section'): ?>
                     Class & Section: <?= e($payment['class']) ?> - <?= e($payment['section'] ?: 'Default') ?>
                 <?php elseif ($key === 'payment_method'): ?>
                     Method: <?= e($payment['payment_method']) ?>
                 <?php elseif ($key === 'payment_ref'): ?>
                     <?php if ($payment['payment_ref']): ?>
                         Reference / Txn: <?= e($payment['payment_ref']) ?>
                     <?php endif; ?>
                 <?php elseif ($key === 'invoice_number'): ?>
                     Linked Invoice: <?= e($payment['invoice_number']) ?>
                 <?php elseif ($key === 'footer_notes'): ?>
                     <?= e($footerNotes) ?>
                 <?php elseif ($key === 'amount'): ?>
                     <?= number_format((float)$payment['amount'], 2) ?> INR
                 <?php elseif ($key === 'account_items'): ?>
                     <div class="border border-slate-300 rounded bg-slate-50 p-2 mt-1 min-w-[260px] text-[9px] text-left">
                         <div class="flex justify-between font-bold border-b border-slate-300 pb-0.5 text-slate-500">
                             <span>Item Description</span>
                             <span>Paid Balance</span>
                         </div>
                         <div class="flex justify-between pt-1">
                             <div>
                                 <p class="font-bold text-slate-900"><?= e($payment['invoice_title']) ?></p>
                                 <?php if ($payment['invoice_desc']): ?>
                                 <p class="text-[8px] text-slate-500 mt-0.5"><?= e($payment['invoice_desc']) ?></p>
                                 <?php endif; ?>
                             </div>
                             <span class="font-mono"><?= number_format((float)$payment['amount'], 2) ?> INR</span>
                         </div>
                     </div>
                 <?php endif; ?>
             </div>
         <?php endforeach; ?>
    </div>

    <?php else: ?>

    <!-- Official Printable Receipt Card (Default Fallback) -->
    <div id="receipt-card" class="bg-slate-900/80 border border-slate-800 rounded-3xl p-8 relative overflow-hidden shadow-2xl print:bg-white print:text-black print:border-none print:shadow-none print:p-0">
        
        <?php if ($showWatermark): ?>
        <!-- Watermark / Paid Stamp (CSS) -->
        <div class="absolute right-8 top-28 select-none pointer-events-none opacity-10 rotate-12 border-4 border-emerald-500 text-emerald-500 font-extrabold uppercase rounded-2xl px-6 py-3 text-3xl tracking-widest stamp-watermark print:opacity-30">
            Paid
        </div>
        <?php endif; ?>

        <!-- Header Block -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-800 pb-6 print:border-slate-300">
            <div>
                <!-- Brand logo/name -->
                <div class="flex items-center gap-3">
                    <?php if (!empty($payment['tenant_logo'])): ?>
                    <img src="<?= url('storage/uploads/logo/' . $payment['tenant_logo']) ?>" class="w-11 h-11 object-contain rounded-xl bg-slate-800/40 p-1 print:bg-transparent" alt="Logo">
                    <?php else: ?>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold print:hidden"
                          style="background: linear-gradient(135deg, var(--receipt-accent), #a855f7);">
                        <?= strtoupper(substr($payment['tenant_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="text-lg font-extrabold text-white print:text-black"><?= e($payment['tenant_name']) ?></h2>
                        <p class="text-xs text-slate-500 print:text-slate-600"><?= e($headerTitle) ?></p>
                    </div>
                </div>
            </div>
            <div class="text-right sm:text-right flex flex-col items-end w-full sm:w-auto">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest print:text-slate-600">Receipt Voucher</span>
                <span class="text-lg font-mono font-bold text-white print:text-black mt-1" style="color: var(--receipt-accent);">REC-<?= $paymentId ?></span>
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
                    <span class="font-mono text-emerald-400 print:text-black" style="color: var(--receipt-accent);"><?= number_format((float)$payment['amount'], 2) ?> INR</span>
                </div>
                <p class="text-[10px] text-slate-550 print:text-slate-600 italic"><?= e($footerNotes) ?></p>
            </div>
        </div>

    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
