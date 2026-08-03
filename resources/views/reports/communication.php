<?php
$layout = 'app';
$pageTitle = 'WhatsApp Communication Logs';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Communication']];
ob_start();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">WhatsApp logs & Communication Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Logs of all automated notifications and status of messages sent to guardians/staff.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"></path></svg>
            Print Report
        </button>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-medium text-slate-400">Total WhatsApp Messages</h3>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($whatsappTotal) ?></p>
            <span class="text-2xs text-slate-500">Cumulative log entries recorded</span>
        </div>

        <?php 
        $successCount = 0;
        $failedCount = 0;
        foreach ($statusSummary as $summary) {
            if (strtolower($summary['status']) === 'sent' || strtolower($summary['status']) === 'success' || strtolower($summary['status']) === 'delivered') {
                $successCount += $summary['cnt'];
            } elseif (strtolower($summary['status']) === 'failed') {
                $failedCount += $summary['cnt'];
            }
        }
        $successPercent = $whatsappTotal > 0 ? ($successCount / $whatsappTotal) * 100 : 0;
        ?>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-medium text-slate-400">Successful Deliveries</h3>
            <p class="text-3xl font-bold text-emerald-400 mt-1"><?= number_format($successCount) ?></p>
            <span class="text-2xs text-slate-500">Delivery rate: <?= round($successPercent, 1) ?>%</span>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-medium text-slate-400">Failed Messages</h3>
            <p class="text-3xl font-bold text-red-400 mt-1"><?= number_format($failedCount) ?></p>
            <span class="text-2xs text-slate-500">Failure rate: <?= $whatsappTotal > 0 ? round(($failedCount / $whatsappTotal) * 100, 1) : 0 ?>%</span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-base font-bold text-white mb-4">Latest WhatsApp Message Logs (up to 50 logs)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold">
                        <th class="py-3 px-4">Recipient Phone</th>
                        <th class="py-3 px-4">Template/Type</th>
                        <th class="py-3 px-4">Message Preview</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-300">
                    <?php if (empty($whatsappLogs)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No communication logs recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($whatsappLogs as $log): 
                            $statusText = strtolower($log['status'] ?? 'sent');
                            $badgeClass = 'bg-slate-700/50 text-slate-300 border border-slate-600/30';
                            if ($statusText === 'sent' || $statusText === 'success' || $statusText === 'delivered') {
                                $badgeClass = 'bg-emerald-900/30 text-emerald-400 border border-emerald-700/30';
                            } elseif ($statusText === 'failed') {
                                $badgeClass = 'bg-red-900/30 text-red-400 border border-red-700/30';
                            }
                        ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 px-4 font-mono font-medium text-slate-350"><?= e($log['phone'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4">
                                    <span class="text-white text-xs font-semibold uppercase bg-slate-800 px-2 py-0.5 rounded border border-slate-700">
                                        <?= e($log['template_name'] ?? $log['message_type'] ?? 'Notification') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs max-w-xs truncate text-slate-400" title="<?= e($log['message'] ?? '') ?>">
                                    <?= e($log['message'] ?? '') ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-2xs font-bold uppercase <?= $badgeClass ?>">
                                        <?= e($log['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500"><?= date('d M Y, h:i A', strtotime($log['sent_at'])) ?></td>
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
