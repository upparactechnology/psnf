<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Report Card Info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Evaluations & Certificates</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Academic progress charts, test performance logs, and issued achievements</p>
        </div>
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
                        <div class="flex gap-2 w-full pt-1">
                            <a href="<?= e(url("parent/certificates/" . (int)($cert['participant_id'] ?: $cert['id']) . "/download?format=jpg&disposition=attachment")) ?>" download
                               class="flex-1 text-center py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-750 text-xs font-bold text-slate-800 dark:text-white border border-slate-250 hover:border-slate-650 transition-all shadow-sm">
                                JPG
                            </a>
                            <a href="<?= e(url("parent/certificates/" . (int)($cert['participant_id'] ?: $cert['id']) . "/download?format=pdf&disposition=inline")) ?>" target="_blank"
                               class="flex-1 text-center py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                PDF
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>
