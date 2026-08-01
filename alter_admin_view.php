<?php
$file = 'resources/views/attendance/index.php';
$content = file_get_contents($file);

$leaveHistoryAdminHtml = <<<HTML
    <?php if (!empty(\$pendingLeaves)): ?>
    <!-- Leave Requests History -->
    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-amber-500 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Leave Requests History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider text-slate-400 bg-slate-900/50 rounded-t-xl">
                    <tr>
                        <th class="px-4 py-3 font-semibold rounded-tl-xl">Student</th>
                        <th class="px-4 py-3 font-semibold">Dates</th>
                        <th class="px-4 py-3 font-semibold">Reason</th>
                        <th class="px-4 py-3 font-semibold">Document</th>
                        <th class="px-4 py-3 font-semibold text-right rounded-tr-xl">Action/Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    <?php foreach (\$pendingLeaves as \$leave): ?>
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-200"><?= e(\$leave['first_name'] . ' ' . \$leave['last_name']) ?></div>
                            <div class="text-[10px] text-slate-500"><?= e(\$leave['class']) ?> - <?= e(\$leave['section'] ?: 'Default') ?></div>
                        </td>
                        <td class="px-4 py-3 text-slate-300 text-xs">
                            <?= date('M d', strtotime(\$leave['start_date'])) ?> to <?= date('M d', strtotime(\$leave['end_date'])) ?>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs whitespace-normal min-w-[200px]">
                            <span class="font-semibold text-slate-300"><?= e(\$leave['leave_type']) ?>:</span> <?= e(\$leave['reason']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if (\$leave['medical_certificate']): ?>
                                <a href="<?= url('uploads/absences/' . \$leave['medical_certificate']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    View Doc
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-slate-600">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <?php if (\$leave['status'] === 'Pending'): ?>
                                <form method="POST" action="<?= url('academics/attendance/approve-leave/' . \$leave['id']) ?>" class="inline-block">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-white rounded-lg text-xs font-bold transition-colors">Approve</button>
                                </form>
                                <form method="POST" action="<?= url('academics/attendance/reject-leave/' . \$leave['id']) ?>" class="inline-block">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-xs font-bold transition-colors">Reject</button>
                                </form>
                            <?php elseif (\$leave['status'] === 'Approved'): ?>
                                <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-500 rounded-lg text-xs font-bold">Approved</span>
                            <?php else: ?>
                                <span class="px-3 py-1.5 bg-rose-500/10 text-rose-500 rounded-lg text-xs font-bold">Rejected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

HTML;

// Inject before Filter Card
\$content = preg_replace('/<!-- Filter Card -->/', \$leaveHistoryAdminHtml . "    <!-- Filter Card -->", \$content);

file_put_contents(\$file, \$content);
echo "Admin view updated.";
