<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Report Card Info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white">Evaluations & Certificates</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Academic progress charts, test performance logs, and issued achievements</p>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Exam Results (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Evaluation Grades</h4>

                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-950/30">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800/60 text-slate-550 dark:text-slate-400">
                                <th class="p-4 font-semibold">Subject</th>
                                <th class="p-4 font-semibold">Exam Type</th>
                                <th class="p-4 font-semibold">Score</th>
                                <th class="p-4 font-semibold">Grade</th>
                                <th class="p-4 font-semibold">Teacher Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                            <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">No evaluation results published yet.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($results as $res): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="p-4 font-medium text-slate-700 dark:text-slate-200"><?= e($res['subject']) ?></td>
                                <td class="p-4 text-slate-500 dark:text-slate-400"><?= e($res['exam_name']) ?></td>
                                <td class="p-4 font-semibold text-slate-650 dark:text-slate-300"><?= $res['marks_obtained'] ?> / <?= $res['max_marks'] ?></td>
                                <td class="p-4 font-bold text-indigo-650 dark:text-indigo-400"><?= e($res['grade'] ?: '—') ?></td>
                                <td class="p-4 text-slate-600 dark:text-slate-400 leading-relaxed"><?= e($res['remarks'] ?: '—') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Certificates (1 col wide) -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Achievements & Certificates</h4>

                <div class="space-y-3.5">
                    <?php if (empty($certificates)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No certificates issued yet.</p>
                    <?php else: ?>
                    <?php foreach ($certificates as $cert): ?>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/20 space-y-3 hover:border-slate-300 dark:hover:border-slate-700 transition-colors shadow-sm">
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-850 dark:text-white leading-tight"><?= e($cert['title']) ?></h5>
                            <p class="text-[9px] text-indigo-600 dark:text-indigo-400 font-semibold uppercase tracking-wider"><?= e($cert['certificate_type']) ?> category</p>
                            <p class="text-[9px] text-slate-500">Issued: <?= date('d M Y', strtotime($cert['issued_at'])) ?></p>
                        </div>
                        <a href="<?= url('certificates/' . $cert['id'] . '/view') ?>" target="_blank"
                           class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-750 text-xs font-bold text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700/60 hover:border-slate-650 dark:hover:border-slate-600 transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-450" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View Certificate
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>
