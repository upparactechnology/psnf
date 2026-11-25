<?php $layout = 'parent'; ?>

<div class="space-y-6" >

    <!-- Header summaries -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Fee Invoices & Payments</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage school fee payments, track invoices, and download transaction logs</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (!empty($academic_years)): ?>
            <form method="GET" class="flex gap-2">
                <select name="academic_year_id" onchange="this.form.submit()"
                        class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
                    <?php foreach ($academic_years as $ay): ?>
                        <option value="<?= e((string)$ay['id']) ?>" <?= (int)$ay['id'] === $selected_year_id ? 'selected' : '' ?>>
                            <?= e($ay['year_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
        <div class="flex gap-4">
            <!-- Paid summary -->
            <?php
            $paidCount = 0; $unpaidCount = 0; $totalUnpaidVal = 0.00;
            foreach ($invoices as $inv) {
                if ($inv['status'] === 'paid') $paidCount++;
                else {
                    $unpaidCount++;
                    $totalUnpaidVal += ((float)$inv['amount'] - (float)$inv['paid_amount']);
                }
            }
            ?>
            <div class="px-4 py-2 bg-emerald-500/10 border border-emerald-500/10 rounded-xl text-center min-w-[100px]">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wider block">Paid Invoices</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 block mt-0.5"><?= $paidCount ?></span>
            </div>
            <div class="px-4 py-2 bg-red-500/10 border border-red-500/10 rounded-xl text-center min-w-[100px]">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wider block">Total Outstanding</span>
                <span class="text-lg font-bold text-red-600 dark:text-red-400 block mt-0.5"><?= number_format($totalUnpaidVal, 2) ?> INR</span>
            </div>
        </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Fee Invoices (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Invoice History</h4>

                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-955/30">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800/60 text-slate-550 dark:text-slate-400">
                                <th class="p-4 font-semibold">Invoice Details</th>
                                <th class="p-4 font-semibold">Amount</th>
                                <th class="p-4 font-semibold">Due Date</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                            <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">No invoices generated for this student.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="p-4 space-y-0.5">
                                    <h5 class="font-bold text-slate-700 dark:text-slate-200"><?= e($inv['title']) ?></h5>
                                    <p class="text-[10px] text-slate-500">No: <?= e($inv['invoice_number']) ?></p>
                                </td>
                                <td class="p-4 font-semibold text-slate-650 dark:text-slate-300"><?= number_format($inv['amount'], 2) ?> INR</td>
                                <td class="p-4 text-slate-600 dark:text-slate-400"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                                <td class="p-4">
                                    <?php
                                    $st = $inv['status'];
                                    $col = $st === 'paid' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : 'text-red-600 dark:text-red-400 bg-red-500/10 border-red-500/10';
                                    ?>
                                    <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-semibold <?= $col ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <?php if ($inv['status'] !== 'paid'): ?>
                                    <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400" title="Online payment not configured — pay at school office">Pay at school office</span>
                                    <?php else: ?>
                                    <span class="text-slate-500 font-medium">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Recent Payments Logs (1 col wide) -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Recent Transactions</h4>

                <div class="space-y-3">
                    <?php if (empty($payments)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No transaction records found.</p>
                    <?php else: ?>
                    <?php foreach ($payments as $pay): ?>
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-955/20 space-y-1.5 hover:border-slate-300 dark:hover:border-slate-700 transition-colors shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/40 pb-1.5">
                            <h5 class="text-xs font-bold text-slate-750 dark:text-white leading-tight truncate max-w-[70%]" title="<?= e($pay['invoice_title']) ?>">
                                <?= e($pay['invoice_title']) ?>
                            </h5>
                            <span class="text-xs font-semibold text-emerald-650 dark:text-emerald-400"><?= number_format($pay['amount'], 2) ?></span>
                        </div>
                        <div class="text-[9px] text-slate-500 flex justify-between">
                            <span>Ref: <?= e($pay['payment_ref']) ?></span>
                            <span><?= date('d M Y', strtotime($pay['paid_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Online payment is not configured (no payment gateway integration). Fees are collected at the school office. -->
    <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-xs font-medium text-amber-700 dark:text-amber-300">
        Online payment not configured — please pay at the school office. Payments recorded by the office appear under Recent Transactions.
    </div>

</div>
