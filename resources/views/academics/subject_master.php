<?php
$layout    = 'app';
$pageTitle = 'Subject Master Settings';
$breadcrumbs = [];
ob_start();

$categories = array_column($subjectTypes ?? [], 'name');
?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{ createModal: <?= isset($_GET['add_next']) ? 'true' : 'false' ?>, editModal: false, activeSubject: {}, addNext: false }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Subject Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Central repository of subjects and skills. These subjects can be reused in any curriculum template.</p>
        </div>
        <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
            + Add New Subject
        </button>
    </div>

    <!-- Table of Subjects -->
    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-400 font-bold uppercase tracking-wider">
                    <th class="p-4">Subject Name</th>
                    <th class="p-4">Code</th>
                    <th class="p-4">Category</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $s): ?>
                    <tr class="border-b border-slate-200 dark:border-slate-800/60 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                        <td class="p-4 font-extrabold text-slate-900 dark:text-white"><?= e($s['name']) ?></td>
                        <td class="p-4 font-mono text-slate-500"><?= e($s['code']) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded text-3xs font-bold bg-indigo-500/10 text-indigo-500">
                                <?= e($s['category']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button @click="activeSubject = <?= e(json_encode($s)) ?>; editModal = true" class="text-indigo-500 font-bold hover:underline">Edit</button>
                            <form action="<?= url('academics/subject-master/' . $s['id'] . '/delete') ?>" method="POST" class="inline" onsubmit="return confirm('Remove this subject from Subject Master?')">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="text-red-500 font-bold hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add New Subject</h3>
            
            <form action="<?= url('academics/subject-master') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_add_next" :value="addNext ? '1' : '0'">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Subject Name</label>
                    <input type="text" name="name" placeholder="e.g. Money Skills" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Subject Code</label>
                    <input type="text" name="code" placeholder="e.g. MONEY01" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Category</label>
                    <input type="text" name="category" list="subject_categories_list" placeholder="e.g. Academic, Therapy, Life Skills" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" @click="addNext = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Save & Close</button>
                    <button type="submit" @click="addNext = true" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save & Add Next</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Subject Details</h3>
            
            <form :action="'<?= url('academics/subject-master') ?>/' + activeSubject.id" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Subject Name</label>
                    <input type="text" name="name" :value="activeSubject.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Subject Code</label>
                    <input type="text" name="code" :value="activeSubject.code" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Category</label>
                    <input type="text" name="category" :value="activeSubject.category" list="subject_categories_list" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <datalist id="subject_categories_list">
        <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat) ?>"></option>
        <?php endforeach; ?>
    </datalist>

</div>

<?php
$content = ob_get_clean();
?>
