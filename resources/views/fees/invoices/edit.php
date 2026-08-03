<?php
$layout    = 'app';
$pageTitle = 'Edit Invoice';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'], 
    ['label' => 'Fees', 'url' => '/fees'], 
    ['label' => 'Invoices', 'url' => '/fees/invoices'],
    ['label' => 'Edit']
];
ob_start();
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Edit Invoice: <?= e($invoice['invoice_number']) ?></h1>
        <a href="<?= url('fees/invoices') ?>" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-bold text-sm">
            &larr; Back to Invoices
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm">
        <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Student: <span class="font-normal"><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?> (<?= e($invoice['admission_number']) ?>)</span></p>
        </div>

        <form action="<?= url("fees/invoices/{$invoice['id']}") ?>" method="POST" class="space-y-6">
            <?= \Core\View::csrf() ?>

            <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Invoice Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="<?= e($invoice['title']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium"><?= e($invoice['description']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" value="<?= e($invoice['amount']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
                    <p class="text-xs text-slate-500 mt-1">Changing this amount will automatically update the student's ledger.</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Due Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="due_date" value="<?= e($invoice['due_date']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-500/30">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
