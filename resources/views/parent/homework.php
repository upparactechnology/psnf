<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white">Active Homework</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">View tasks and therapeutic exercises assigned for home practice</p>
    </div>

    <!-- Homework Assignments List -->
    <div class="space-y-4">
        <?php if (empty($homeworks)): ?>
        <div class="p-10 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/10 text-center">
            <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-xs text-slate-500">No homework assignments found.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($homeworks as $hw): ?>
            <?php
            $isOverdue = strtotime($hw['due_date']) < time() && date('Y-m-d') !== $hw['due_date'];
            ?>
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-5 hover:border-slate-300 dark:hover:border-slate-800 transition-colors flex flex-col justify-between space-y-4 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/10 border border-indigo-500/10 text-indigo-650 dark:text-indigo-400 text-[10px] font-semibold">
                            <?= e($hw['subject']) ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isOverdue ? 'text-red-650 dark:text-red-400 bg-red-500/10 border-red-500/10' : 'text-yellow-600 dark:text-yellow-400 bg-yellow-500/10 border-yellow-500/10' ?>">
                            <?= $isOverdue ? 'Overdue' : 'Assigned' ?>
                        </span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200"><?= e($hw['title']) ?></h4>
                    <p class="text-xs text-slate-605 dark:text-slate-400 leading-relaxed"><?= e($hw['description']) ?></p>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800/60 pt-3.5 flex items-center justify-between text-[10px] text-slate-500 font-semibold uppercase tracking-wider">
                    <div class="space-y-0.5">
                        <p>Assigned: <span class="text-slate-700 dark:text-slate-300 font-medium"><?= date('d M Y', strtotime($hw['assigned_at'])) ?></span></p>
                        <p>Due Date: <span class="text-slate-700 dark:text-slate-300 font-medium"><?= date('d M Y', strtotime($hw['due_date'])) ?></span></p>
                    </div>

                    <?php if ($hw['file_path']): ?>
                    <a href="<?= url($hw['file_path']) ?>" download
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-750 dark:text-white border border-slate-200 dark:border-slate-700/60 hover:border-slate-650 dark:hover:border-slate-600 transition-all font-bold">
                        <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PDF
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
