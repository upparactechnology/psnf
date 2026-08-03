<?php
$layout    = 'app';
$pageTitle = 'Invoice Details';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'], 
    ['label' => 'Fees', 'url' => '/fees'], 
    ['label' => 'Invoices', 'url' => '/fees/invoices'],
    ['label' => 'View']
];
ob_start();
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Invoice <?= e($invoice['invoice_number']) ?></h1>
            <a href="<?= url('fees/invoices') ?>" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-bold text-sm">
                &larr; Back to Invoices
            </a>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-sm">
                Print Invoice
            </button>
            <?php if ($invoice['status'] !== 'paid'): ?>
            <button onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-500/30">
                Record Payment
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Printable Area -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 md:p-12 shadow-sm print:shadow-none print:border-none print:p-0 print:m-0 w-full" id="printable-invoice">
        
        <!-- Invoice Header -->
        <div class="flex justify-between items-start border-b border-slate-200 dark:border-slate-800 pb-8 mb-8">
            <div>
                <h2 class="text-3xl font-black text-indigo-600 tracking-tight">INVOICE</h2>
                <p class="text-slate-500 font-medium mt-1">#<?= e($invoice['invoice_number']) ?></p>
            </div>
            <div class="text-right">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">PSNF School ERP</h3>
                <p class="text-slate-500 text-sm mt-1">123 Education Lane<br>Knowledge City, 10001<br>contact@psnfschool.com</p>
            </div>
        </div>

        <!-- Student & Dates -->
        <div class="flex justify-between mb-10">
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">Billed To</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white"><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></p>
                <p class="text-slate-600 dark:text-slate-400 text-sm">Admission No: <?= e($invoice['admission_number']) ?></p>
                <?php if ($invoice['class_name']): ?>
                <p class="text-slate-600 dark:text-slate-400 text-sm">Class: <?= e($invoice['class_name']) ?> <?= e($invoice['section_name']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="mb-4">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Issue Date</p>
                    <p class="text-slate-900 dark:text-white font-medium"><?= date('d M, Y', strtotime($invoice['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Due Date</p>
                    <p class="text-slate-900 dark:text-white font-bold <?= strtotime($invoice['due_date']) < time() && $invoice['status'] !== 'paid' ? 'text-rose-500' : '' ?>">
                        <?= date('d M, Y', strtotime($invoice['due_date'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoice Details Table -->
        <div class="mb-10">
            <table class="w-full text-left">
                <thead class="border-b-2 border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 text-sm font-bold text-slate-700 dark:text-slate-300">Description</th>
                        <th class="py-3 text-sm font-bold text-slate-700 dark:text-slate-300 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-4">
                            <p class="font-bold text-slate-900 dark:text-white"><?= e($invoice['title']) ?></p>
                            <?php if ($invoice['description']): ?>
                            <p class="text-sm text-slate-500 mt-1"><?= e($invoice['description']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-right font-bold text-slate-900 dark:text-white">
                            ₹<?= number_format($invoice['amount'], 2) ?>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="pt-6 text-right font-bold text-slate-700 dark:text-slate-300">Total Amount:</td>
                        <td class="pt-6 text-right font-black text-xl text-slate-900 dark:text-white">₹<?= number_format($invoice['amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="pt-2 text-right font-bold text-slate-500">Amount Paid:</td>
                        <td class="pt-2 text-right font-bold text-emerald-600">₹<?= number_format($invoice['paid_amount'], 2) ?></td>
                    </tr>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-800">
                        <td class="pt-4 text-right font-black text-slate-900 dark:text-white uppercase tracking-wider">Balance Due:</td>
                        <td class="pt-4 text-right font-black text-2xl text-rose-600">₹<?= number_format($invoice['amount'] - $invoice['paid_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!empty($payments)): ?>
        <!-- Payment History -->
        <div class="mt-12">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Payment History</h4>
            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td class="px-4 py-3 font-medium"><?= date('d M, Y h:i A', strtotime($payment['paid_at'])) ?></td>
                            <td class="px-4 py-3"><?= e($payment['payment_method']) ?></td>
                            <td class="px-4 py-3 text-slate-500"><?= e($payment['payment_ref'] ?: '-') ?></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">₹<?= number_format($payment['amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer Notes -->
        <div class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-800 text-center text-sm text-slate-500">
            <p>Please make all cheques payable to PSNF School ERP.</p>
            <p class="mt-1">Thank you for your timely payment!</p>
        </div>
    </div>
</div>

<!-- Payment Modal (Reused from Dashboard but customized for this view) -->
<div id="payment-modal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm overflow-y-auto no-print">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl w-full max-w-md overflow-hidden relative">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Record Payment</h3>
                    <button onclick="document.getElementById('payment-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form action="<?= url("fees/{$invoice['id']}/pay") ?>" method="POST" class="space-y-5">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="return_url" value="/fees/invoices/<?= $invoice['id'] ?>">

                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Amount Received (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="1" max="<?= $invoice['amount'] - $invoice['paid_amount'] ?>" value="<?= $invoice['amount'] - $invoice['paid_amount'] ?>" required 
                               class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-emerald-500 font-medium">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Payment Method <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="UPI">UPI / Online</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Reference Number</label>
                        <input type="text" name="payment_ref" placeholder="Txn ID, Cheque No, etc." 
                               class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-emerald-500 font-medium">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-emerald-500/30">
                            Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-invoice, #printable-invoice * {
            visibility: visible;
        }
        #printable-invoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
