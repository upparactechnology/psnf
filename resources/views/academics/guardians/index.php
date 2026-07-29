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
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php if (empty($guardians)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                            No parents found. <a href="<?= url('academics/parents/create') ?>" class="text-indigo-600 hover:underline">Add one now</a>.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($guardians as $g): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/25 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-white"><?= e($g['name']) ?></div>
                                <div class="text-xs text-slate-500 capitalize"><?= e($g['relationship'] ?? 'Parent') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700 dark:text-slate-300"><?= e($g['phone']) ?></div>
                                <div class="text-xs text-slate-500"><?= e($g['email'] ?? 'No email') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                    <?= $g['students_count'] ?> Student(s)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="<?= url("academics/parents/{$g['id']}/edit") ?>" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 font-medium text-sm">Edit</a>
                                <form action="<?= url("academics/parents/{$g['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Delete this parent?');">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


