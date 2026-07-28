<?php
$layout    = 'app';
$pageTitle = 'Department Management';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Departments']];
ob_start();
?>

<div x-data="{ createModal: false }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Departments Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure school organizational units (Academic, Admin, Therapy, Finance, Transport)</p>
        </div>
        <button @click="createModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Create Department</button>
    </div>

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($departments as $d): ?>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-indigo-500"><?= e($d['code']) ?></span>
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500"><?= (int)$d['total_staff'] ?> Staff Members</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= e($d['name']) ?></h3>
            <p class="text-xs text-slate-500"><?= e($d['description'] ?? 'School operational department') ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Create Department Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="createModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create Department</h3>
            
            <form action="<?= url('staff/departments') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Department Name</label>
                    <input type="text" name="name" placeholder="e.g. IT & Security" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Department Code</label>
                    <input type="text" name="code" placeholder="DEP-IT" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Department</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
