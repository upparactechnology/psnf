<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Report Card Info -->
    <div class="p-5 rounded-2xl border bg-slate-900/30 border-white/5">
        <h3 class="text-base font-bold text-white">Evaluations & Certificates</h3>
        <p class="text-xs text-slate-500">Academic progress charts, test performance logs, and issued achievements</p>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Exam Results (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-white/5 bg-slate-900/20 p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Evaluation Grades</h4>

                <div class="overflow-hidden rounded-xl border border-slate-800/60 bg-slate-950/30">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 border-b border-slate-800/60 text-slate-400">
                                <th class="p-4 font-semibold">Subject</th>
                                <th class="p-4 font-semibold">Exam Type</th>
                                <th class="p-4 font-semibold">Score</th>
                                <th class="p-4 font-semibold">Grade</th>
                                <th class="p-4 font-semibold">Teacher Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40">
                            <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">No evaluation results published yet.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($results as $res): ?>
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="p-4 font-medium text-slate-200"><?= e($res['subject']) ?></td>
                                <td class="p-4 text-slate-400"><?= e($res['exam_name']) ?></td>
                                <td class="p-4 font-semibold text-slate-300"><?= $res['marks_obtained'] ?> / <?= $res['max_marks'] ?></td>
                                <td class="p-4 font-bold text-indigo-400"><?= e($res['grade'] ?: '—') ?></td>
                                <td class="p-4 text-slate-400 leading-relaxed"><?= e($res['remarks'] ?: '—') ?></td>
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
            <div class="rounded-2xl border border-white/5 bg-slate-900/20 p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Achievements & Certificates</h4>

                <div class="space-y-3.5">
                    <?php if (empty($certificates)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No certificates issued yet.</p>
                    <?php else: ?>
                    <?php foreach ($certificates as $cert): ?>
                    <div class="p-4 rounded-xl border border-slate-800/80 bg-slate-950/20 space-y-3 hover:border-slate-700 transition-colors">
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-white leading-tight"><?= e($cert['title']) ?></h5>
                            <p class="text-[9px] text-indigo-400 font-semibold uppercase tracking-wider"><?= e($cert['certificate_type']) ?> category</p>
                            <p class="text-[9px] text-slate-500">Issued: <?= date('d M Y', strtotime($cert['issued_at'])) ?></p>
                        </div>
                        <a href="#" onclick="alert('Downloading certificate PDF: ' + '<?= e(basename($cert['file_path'])) ?>')"
                           class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-750 text-xs font-bold text-white border border-slate-700/60 hover:border-slate-600 transition-all">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download PDF
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>
