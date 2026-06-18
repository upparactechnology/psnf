<?php $layout = 'parent'; ?>

<div class="space-y-6" x-data="{ payModal: false, invoiceId: null, invoiceTitle: '', invoiceAmount: 0 }">

    <!-- Header summaries -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Fee Invoices & Payments</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage school fee payments, track invoices, and download transaction logs</p>
        </div>
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
                                    <button @click="payModal = true; invoiceId = <?= $inv['id'] ?>; invoiceTitle = '<?= e($inv['title']) ?>'; invoiceAmount = <?= $inv['amount'] ?>"
                                             class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[10px] shadow transition-all">
                                        Pay Online
                                    </button>
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

    <!-- Simulated Payment Slide-over Modal -->
    <div x-show="payModal" class="fixed inset-0 z-[9999] overflow-hidden" x-cloak>
        <div class="absolute inset-0 bg-slate-955/80 backdrop-blur-sm transition-opacity" @click="payModal = false"></div>
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800/80 shadow-2xl flex flex-col"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">

                <!-- Header -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">💳 Secure Fee Payment</h3>
                    <button @click="payModal = false" class="text-slate-500 hover:text-slate-350 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form :action="'<?= url('parent/students/' . $active_student['id'] . '/fees/') ?>' + invoiceId + '/pay'"
                      method="POST"
                      class="flex-1 overflow-y-auto p-6 space-y-6"
                      x-data="{ method: 'card', processing: false }"
                      @submit="processing = true">

                    <?= \Core\View::csrf() ?>

                    <!-- Invoice Info summary -->
                    <div class="p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/30 bg-indigo-50/50 dark:bg-indigo-955/10">
                        <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Selected Invoice</p>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white mt-0.5" x-text="invoiceTitle"></h4>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800/60 mt-3">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Amount to Pay</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white"><span x-text="invoiceAmount"></span> INR</span>
                        </div>
                    </div>

                    <!-- Payment Method Select -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Payment Method</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="method = 'card'"
                                    :class="method === 'card' ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-100 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-400'"
                                    class="py-2.5 rounded-lg border text-xs font-semibold text-center transition-all">
                                Card Payment
                            </button>
                            <button type="button" @click="method = 'upi'"
                                    :class="method === 'upi' ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-100 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-400'"
                                    class="py-2.5 rounded-lg border text-xs font-semibold text-center transition-all">
                                UPI / QR Code
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" :value="method === 'card' ? 'Credit/Debit Card' : 'UPI'">
                    </div>

                    <!-- Card Details inputs -->
                    <div class="space-y-4" x-show="method === 'card'">
                        <!-- Card number -->
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Card Number</label>
                            <input type="text" placeholder="4111 2222 3333 4444" maxlength="19" required
                                   class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Expiry -->
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-slate-455 dark:text-slate-500 uppercase tracking-wider">Expiry Date</label>
                                <input type="text" placeholder="MM/YY" maxlength="5" required
                                       class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                            <!-- CVV -->
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-slate-455 dark:text-slate-500 uppercase tracking-wider">CVV</label>
                                <input type="password" placeholder="•••" maxlength="3" required
                                       class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                        </div>
                    </div>

                    <!-- UPI Info Mockup -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-850 bg-slate-50 dark:bg-slate-950/60 text-center space-y-4" x-show="method === 'upi'">
                        <p class="text-xs text-slate-600 dark:text-slate-400">Scan this QR Code using any UPI App (GPay, PhonePe, Paytm)</p>
                        <!-- QR Mock -->
                        <div class="w-36 h-36 bg-white p-2 rounded-xl mx-auto flex items-center justify-center border border-slate-200 dark:border-slate-800">
                            <svg class="w-32 h-32 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm0 12v2h2v-2H5zm12-12h2v2h-2V5zm-4 4h2v2h-2V9zm4 4h2v2h-2v-2zm-4 4h2v2h-2v-2zm-6 2v2H3v-6h6v6H5v-2H5zm2-2V9H3V3h6v6H7zm6-6h6v6h-6V3zm2 2v2h2V5h-2zm-4 8h2v2h-2v-2zm4 4h2v2h-2v-2z"/>
                            </svg>
                        </div>
                        <p class="text-[10px] text-slate-500 font-mono">UPI ID: psnf@schoolupi</p>
                    </div>

                    <button type="submit" :disabled="processing"
                            class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl text-xs font-bold text-white shadow-lg hover:shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                        <svg x-show="processing" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="processing ? 'Simulating payment transaction...' : 'Confirm Simulated Payment'">Confirm Simulated Payment</span>
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>
