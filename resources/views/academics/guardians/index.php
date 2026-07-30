<?php
$layout = 'app';
?>

<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= e($title ?? 'Parents Directory') ?></h1>
            <p class="text-sm text-slate-500 mt-1">Manage parents and guardians in the academic workspace.</p>
        </div>
        <div>
            <a href="<?= url('academics/parents/create') ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm font-medium">
                + Add Parent
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Linked Parents / Guardians</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php 
                    $hasRows = false;
                    if (!empty($students)): 
                        foreach ($students as $s): 
                            if (empty($studentGuardians[$s['id']])) continue; // Only show students who actually have guardians
                            $hasRows = true;
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/25 transition-colors align-top">
                            <td class="px-6 py-4 w-1/3 border-r border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($s['photo'])): ?>
                                        <img src="<?= url($s['photo']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-brand-500/10 text-brand-500 flex items-center justify-center font-bold text-sm">
                                            <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                        <div class="text-xs text-slate-500">ADM: <?= e($s['admission_number']) ?> &bull; <?= e($s['class'] ?? '') ?> <?= e($s['section'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($studentGuardians[$s['id']] as $g): ?>
                                    <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 relative group">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="font-medium text-sm text-slate-900 dark:text-white"><?= e($g['name']) ?></div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400 capitalize">
                                                <?= e($g['relationship'] ?? 'Guardian') ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5">
                                            <div class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><?= e($g['phone']) ?></div>
                                            <?php if($g['email']): ?>
                                            <div class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><?= e($g['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2 bg-slate-50 dark:bg-slate-800 pl-2">
                                            <a href="<?= url("academics/parents/{$g['id']}/edit") ?>" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                            <form action="<?= url("academics/parents/{$g['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Delete this parent/guardian?');">
                                                <?= \Core\View::csrf() ?>
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    
                    <?php if (!$hasRows && empty($unlinkedGuardians)): ?>
                    <tr>
                        <td colspan="2" class="px-6 py-12 text-center text-slate-500">
                            <div class="mx-auto w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="text-base font-medium text-slate-900 dark:text-white mb-1">No Parents Found</p>
                            <p class="text-sm">Add a parent to get started.</p>
                            <a href="<?= url('academics/parents/create') ?>" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">Add Parent</a>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($unlinkedGuardians)): ?>
                        <tr class="bg-red-50/50 dark:bg-red-900/10 align-top">
                            <td class="px-6 py-4 w-1/3 border-r border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/20 text-red-500 flex items-center justify-center font-bold text-sm">
                                        !
                                    </div>
                                    <div>
                                        <div class="font-medium text-red-700 dark:text-red-400">Unlinked Parents</div>
                                        <div class="text-xs text-red-500">Not assigned to any student</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($unlinkedGuardians as $g): ?>
                                    <div class="bg-white dark:bg-slate-800 border border-red-200 dark:border-red-500/30 rounded-lg p-3 relative group shadow-sm">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="font-medium text-sm text-slate-900 dark:text-white"><?= e($g['name']) ?></div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 capitalize">
                                                Unlinked
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5">
                                            <div class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><?= e($g['phone']) ?></div>
                                            <?php if($g['email']): ?>
                                            <div class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><?= e($g['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2 bg-white dark:bg-slate-800 pl-2">
                                            <a href="<?= url("academics/parents/{$g['id']}/edit") ?>" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                            <form action="<?= url("academics/parents/{$g['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Delete this parent/guardian?');">
                                                <?= \Core\View::csrf() ?>
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


