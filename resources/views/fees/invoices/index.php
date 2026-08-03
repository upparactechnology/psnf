<?php
$layout    = 'app';
$pageTitle = 'All Invoices';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'All Invoices']];
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Invoices</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Manage all student fee invoices across the institution</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('fees/invoices/create') ?>" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Ad-hoc Invoice
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-4">
        <form method="GET" action="<?= url('fees/invoices') ?>" class="flex-1 flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by ID, student, title..." 
                       class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>
            <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-indigo-500 font-medium w-full sm:w-48">
                <option value="">All Statuses</option>
                <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2.5 rounded-xl font-bold text-sm transition-colors">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-6 py-4">Invoice No</th>
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($invoices)): ?>
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500 font-medium">No invoices found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-slate-300">
                            <?= e($inv['invoice_number']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 dark:text-white"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></p>
                            <p class="text-xs text-slate-500 font-medium"><?= e($inv['admission_number']) ?></p>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300">
                            <?= e($inv['title']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 dark:text-white">₹<?= number_format($inv['amount'], 2) ?></p>
                            <?php if ($inv['paid_amount'] > 0): ?>
                            <p class="text-[11px] text-emerald-500 font-bold uppercase tracking-wide">Paid: ₹<?= number_format($inv['paid_amount'], 2) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="<?= strtotime($inv['due_date']) < time() && $inv['status'] !== 'paid' ? 'text-rose-500 font-bold' : 'text-slate-600 dark:text-slate-400 font-medium' ?>">
                                <?= date('d M, Y', strtotime($inv['due_date'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 uppercase tracking-wider">
                                    Paid
                                </span>
                            <?php elseif ($inv['status'] === 'unpaid' && strtotime($inv['due_date']) < time()): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 uppercase tracking-wider">
                                    Overdue
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase tracking-wider">
                                    Unpaid
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="<?= url("fees/invoices/{$inv['id']}") ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 font-bold text-xs">View</a>
                            <?php if ($inv['status'] === 'unpaid' && $inv['paid_amount'] == 0): ?>
                                <a href="<?= url("fees/invoices/{$inv['id']}/edit") ?>" class="text-slate-600 hover:text-slate-900 dark:text-slate-400 font-bold text-xs">Edit</a>
                                <form action="<?= url("fees/invoices/{$inv['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this invoice? This will rollback ledger entries.');">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold text-xs">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Note: no manual layout inclusion here since View::render() handles it natively.
