<?php
$layout    = 'app';
$pageTitle = 'Payroll Audit History Logs';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Audit History']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Payroll Audit Trail</h1>
            <p class="text-xs text-slate-500 mt-0.5">Full audit history of all manual overrides, exemptions, leave changes, and payroll run state mutations.</p>
        </div>
        <a href="<?= url('payroll/attendance') ?>" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">← Attendance View</a>
    </div>

    <!-- Audit Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Audit Log Entries</span>
            <span class="text-2xs font-mono text-slate-400">Showing last 200 records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-2xs border-b border-slate-100 dark:border-slate-850">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Action Performed</th>
                        <th class="p-3">Target Entity</th>
                        <th class="p-3">Entity ID</th>
                        <th class="p-3">Old Value</th>
                        <th class="p-3">New Value</th>
                        <th class="p-3">Reason / Notes</th>
                        <th class="p-3">Performed By</th>
                        <th class="p-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($auditLogs)): ?>
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">No audit log entries found yet. Override actions will appear here.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $log): ?>
                        <?php
                            $actionColor = 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400';
                            if (str_contains($log['action'], 'exempt')) $actionColor = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
                            elseif (str_contains($log['action'], 'override')) $actionColor = 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
                            elseif (str_contains($log['action'], 'payroll')) $actionColor = 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400';
                            elseif (str_contains($log['action'], 'leave')) $actionColor = 'bg-rose-500/10 text-rose-600 dark:text-rose-400';
                        ?>
                        <tr class="hover:bg-slate-50/45 dark:hover:bg-slate-850/10 transition-all">
                            <td class="p-3 text-slate-400 font-mono"><?= (int)$log['id'] ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-2xs font-bold <?= $actionColor ?>">
                                    <?= e(str_replace('_', ' ', strtoupper($log['action']))) ?>
                                </span>
                            </td>
                            <td class="p-3 font-mono text-slate-500"><?= e($log['entity']) ?></td>
                            <td class="p-3 font-mono text-slate-400">#<?= (int)$log['entity_id'] ?></td>
                            <td class="p-3 text-slate-400 italic"><?= e($log['old_value'] ?? '—') ?></td>
                            <td class="p-3 font-bold text-slate-700 dark:text-slate-300"><?= e($log['new_value'] ?? '—') ?></td>
                            <td class="p-3 text-slate-500 max-w-xs truncate"><?= e($log['reason'] ?? '—') ?></td>
                            <td class="p-3 font-semibold text-slate-700 dark:text-slate-300"><?= e($log['user_name'] ?? 'System') ?></td>
                            <td class="p-3 font-mono text-2xs text-slate-400"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
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
