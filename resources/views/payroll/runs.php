<?php
$layout    = 'app';
$pageTitle = 'Monthly Payroll Processing';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Processing Runs']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Panel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Payroll Runs Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Generate, approve, and track monthly employee salary disbursements and dynamic lateness penalties</p>
        </div>
        <div>
            <!-- Button to open run modal -->
            <button onclick="document.getElementById('runModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-9-4h18c1.1 0 2 .9 2 2v6c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6c0-1.1.9-2 2-2z"/></svg>
                Run Monthly Payroll
            </button>
        </div>
    </div>

    <!-- Active Runs Grid -->
    <div class="grid grid-cols-1 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Generated Monthly Batches</span>
                <span class="text-2xs font-mono text-slate-400">Total Runs: <?= count($runs) ?></span>
            </div>
            
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-2xs border-b border-slate-100 dark:border-slate-850">
                    <tr>
                        <th class="p-4">Billing Month</th>
                        <th class="p-4">Total Gross Payout</th>
                        <th class="p-4">Total Deductions</th>
                        <th class="p-4">Net Salary Paid</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($runs)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 text-xs">No payroll runs executed yet. Select a month and click "Run Monthly Payroll" to start processing.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($runs as $r): ?>
                        <tr>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold text-2xs">
                                        <?= strtoupper(substr($r['month_year'], 0, 3)) ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white"><?= e($r['month_year']) ?></span>
                                        <span class="text-3xs font-mono text-slate-400 block">Created: <?= date('d M Y H:i', strtotime($r['created_at'])) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float)$r['total_gross'], 2) ?></td>
                            <td class="p-4 font-mono font-bold text-rose-500">-₹<?= number_format((float)$r['total_deductions'], 2) ?></td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format((float)$r['total_net'], 2) ?></td>
                            <td class="p-4">
                                <?php if ($r['status'] === 'out_of_sync'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-3xs font-bold bg-amber-500/20 text-amber-600 dark:text-amber-400 uppercase tracking-wider animate-pulse">Out of Sync</span>
                                <?php elseif ($r['status'] === 'draft'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-3xs font-bold bg-slate-500/20 text-slate-600 dark:text-slate-400 uppercase tracking-wider">Draft</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-3xs font-bold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Approved ✓</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($r['status'] === 'out_of_sync'): ?>
                                        <form action="<?= url('payroll/runs/' . $r['id'] . '/regenerate') ?>" method="POST" onsubmit="return confirm('Recalculate entire run and synchronize overrides?')">
                                            <?= \Core\View::csrf() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-2xs bg-amber-500/20 hover:bg-amber-500 text-amber-600 dark:text-amber-400 hover:text-white font-bold transition-all">Regenerate</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= url('payroll/runs/' . $r['id']) ?>" class="px-2.5 py-1 rounded-lg text-2xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Details View</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Processing Run Modal -->
    <div id="runModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="document.getElementById('runModal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10 text-xs">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Run New Monthly Payroll</h3>
            <p class="text-slate-500 leading-relaxed">Processing payroll compiles all face recognition kiosk clock-ins, manual logs, and approved leaves for the chosen month, applying strict grace periods and lateness penalties.</p>
            
            <form action="<?= url('payroll/runs/run') ?>" method="POST" class="space-y-4">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Processing Month</label>
                    <input type="month" name="month" value="<?= date('Y-m') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('runModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-500 transition-all">Calculate Run</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
