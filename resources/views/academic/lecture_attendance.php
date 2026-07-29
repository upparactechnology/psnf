<?php
$layout    = 'app';
$pageTitle = 'Teacher Lecture Attendance Registry';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Lecture Attendance Logs']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-200 dark:border-slate-800/50 bg-white dark:bg-slate-900/30 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Teacher Lecture-Wise Attendance Registry</h2>
            <p class="text-sm text-slate-500 mt-0.5">Logs and tracking of teacher check-ins against timetabled class lectures</p>
        </div>
    </div>

    <!-- Attendance Table Card -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/20 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest">Attendance Logs Registry</h3>
            <span class="text-2xs font-semibold text-slate-550"><?= count($logs) ?> entries recorded</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-650 dark:text-slate-350">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-300 font-semibold uppercase text-2xs border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4">Teacher</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 font-mono">Lecture Start Time</th>
                        <th class="p-4 font-mono">Checked In At</th>
                        <th class="p-4 font-mono">Grace Window</th>
                        <th class="p-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">No lecture attendance logs recorded yet.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                        <?= strtoupper(substr($l['teacher_name'] ?? 'T', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-slate-200"><?= e($l['teacher_name']) ?></p>
                                        <p class="text-[10px] text-slate-500 font-mono"><?= e($l['teacher_email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-400 font-semibold">
                                <?= date('M d, Y', strtotime($l['attendance_date'])) ?>
                            </td>
                            <td class="p-4 font-mono text-indigo-400 font-bold">
                                <?= date('h:i A', strtotime($l['lecture_time'])) ?>
                            </td>
                            <td class="p-4 font-mono text-slate-700 dark:text-slate-400 font-semibold">
                                <?= date('h:i A', strtotime($l['opened_at'])) ?>
                            </td>
                            <td class="p-4 font-mono text-slate-550">
                                <?= (int) $l['grace_period'] ?> mins
                            </td>
                            <td class="p-4 text-right">
                                <?php if ($l['status'] === 'on_time'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase">On Time</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-red-500/10 text-red-500 uppercase">Late</span>
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

<?php
$content = ob_get_clean();
?>
