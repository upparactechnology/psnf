<?php
$file = 'resources/views/parent/attendance.php';
$content = file_get_contents($file);

$leaveHistoryViewHtml = <<<HTML
    <!-- Leave History -->
    <?php if (!empty(\$leaveHistory)): ?>
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm mt-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Leave Applications History</h3>
        <div class="space-y-4">
            <?php foreach (\$leaveHistory as \$leave): ?>
            <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white"><?= e(\$leave['leave_type']) ?></h4>
                        <?php if (\$leave['status'] === 'Pending'): ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                        <?php elseif (\$leave['status'] === 'Approved'): ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Approved</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400">Rejected</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <?= date('M d, Y', strtotime(\$leave['start_date'])) ?> to <?= date('M d, Y', strtotime(\$leave['end_date'])) ?>
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2"><?= e(\$leave['reason']) ?></p>
                </div>
                <?php if (\$leave['medical_certificate']): ?>
                    <a href="<?= url('uploads/absences/' . \$leave['medical_certificate']) ?>" target="_blank" class="shrink-0 px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">
                        View Document
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

HTML;

// Inject it below the Attendance Status Counter Grid
$content = preg_replace('/<!-- Attendance Status Counter Grid -->.*?<\/div>\s*<\/div>/s', '$0' . "\n\n" . $leaveHistoryViewHtml, $content);

file_put_contents($file, $content);
echo "Parent view updated.";
