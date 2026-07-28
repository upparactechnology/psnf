<?php
$layout    = 'app';
$pageTitle = 'Designations Configuration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Designations']];
ob_start();
?>

<div x-data="{ createModal: false }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Designations & Job Titles</h1>
            <p class="text-xs text-slate-500 mt-0.5">Fully configurable employee titles (Principal, Teacher, Therapist, Driver, Accountant)</p>
        </div>
        <button @click="createModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Designation</button>
    </div>

    <!-- Designations List Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Designation Code</th>
                    <th class="p-4">Job Title</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Staff Count</th>
                    <th class="p-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php foreach ($designations as $des): ?>
                <tr>
                    <td class="p-4 font-mono font-bold text-indigo-500"><?= e($des['code']) ?></td>
                    <td class="p-4 font-bold text-slate-900 dark:text-white"><?= e($des['title']) ?></td>
                    <td class="p-4 text-slate-500"><?= e($des['description'] ?? 'Standard role designation') ?></td>
                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white"><?= (int)$des['total_staff'] ?> Employees</td>
                    <td class="p-4 text-right">
                        <span class="px-2 py-0.5 rounded text-2xs font-bold bg-emerald-500/10 text-emerald-500">ACTIVE</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Designation Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="createModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Designation</h3>
            
            <form action="<?= url('staff/designations') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" name="title" placeholder="e.g. Speech Therapist Specialist" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Designation Code</label>
                    <input type="text" name="code" placeholder="DES-ST" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Designation</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
