<?php
$layout    = 'app';
$pageTitle = 'Designations Configuration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Designations']];
ob_start();
?>

<div x-data="{ createModal: <?= isset($_GET['add_next']) ? 'true' : 'false' ?>, editModal: false, editId: '', editTitle: '', editCode: '', editDesc: '', addNext: false }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Designations & Job Titles</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure employee titles and roles</p>
        </div>
        <?php if (has_permission('create_designations')): ?>
        <button @click="createModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Designation</button>
        <?php endif; ?>
    </div>

    <!-- Designations Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Code</th>
                    <th class="p-4">Job Title</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Staff</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php if (empty($designations)): ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 text-xs">No designations found. Click "+ Add Designation" to create one.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($designations as $des): ?>
                <tr>
                    <td class="p-4 font-mono font-bold text-indigo-500"><?= e($des['code']) ?></td>
                    <td class="p-4 font-bold text-slate-900 dark:text-white"><?= e($des['title']) ?></td>
                    <td class="p-4 text-slate-500"><?= e($des['description'] ?? 'No description') ?></td>
                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white"><?= (int)$des['total_staff'] ?></td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <?php if (has_permission('edit_designations')): ?>
                            <button @click="editId = '<?= $des['id'] ?>'; editTitle = '<?= e($des['title']) ?>'; editCode = '<?= e($des['code']) ?>'; editDesc = '<?= e($des['description'] ?? '') ?>'; editModal = true" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Edit</button>
                            <?php endif; ?>
                            <?php if (has_permission('delete_designations')): ?>
                            <form action="<?= url('staff/designations/' . $des['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this designation? This cannot be undone.')" class="inline">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
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
                <input type="hidden" name="_add_next" :value="addNext ? '1' : '0'">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" name="title" placeholder="e.g. Speech Therapist" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Code</label>
                    <input type="text" name="code" placeholder="DES-ST" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" @click="addNext = false" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-300 dark:hover:bg-slate-600">Save & Close</button>
                    <button type="submit" @click="addNext = true" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save & Add Next</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Designation Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Designation</h3>
            <form :action="'<?= url('staff/designations/') ?>' + editId + '/update'" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" name="title" x-model="editTitle" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Code</label>
                    <input type="text" name="code" x-model="editCode" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" x-model="editDesc" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Update Designation</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
