<?php
$layout    = 'app';
$pageTitle = 'Manage Main Groups';
$breadcrumbs = [];
ob_start();
?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{ createModal: <?= isset($_GET['add_next']) ? 'true' : 'false' ?>, editModal: false, activeGroup: {}, addNext: false }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Main Groups</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define core school categories, color codes, and default icons</p>
        </div>
        <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
            + Add Main Group
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <?php foreach ($groups as $g): ?>
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4 shadow-sm flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div style="background-color: <?= $g['color'] ?>20;" class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl">
                            <?= e($g['icon']) ?>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold" 
                              style="background-color: <?= $g['color'] ?>20; color: <?= $g['color'] ?>;">
                            <?= $g['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?= e($g['name']) ?></h4>
                        <p class="text-3xs text-slate-400 font-mono mt-0.5">Age Range: <?= e($g['age_range'] ?: 'Any') ?></p>
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2"><?= e($g['description'] ?: 'No description provided.') ?></p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button @click="activeGroup = <?= e(json_encode($g)) ?>; editModal = true" class="text-2xs text-indigo-500 font-bold hover:underline">
                        Edit Settings &rarr;
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create Main Group</h3>
            
            <form action="<?= url('academics/main-groups') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_add_next" :value="addNext ? '1' : '0'">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Age Range (optional)</label>
                        <input type="text" name="age_range" placeholder="e.g. 6-10" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Color</label>
                        <input type="color" name="color" value="#6366f1" class="w-full h-9 rounded-xl border border-slate-200 dark:border-slate-700">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Icon Emoji</label>
                    <input type="text" name="icon" value="🎓" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
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
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Main Group</h3>
            
            <form :action="'<?= url('academics/main-groups') ?>/' + activeGroup.id" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Name</label>
                    <input type="text" name="name" :value="activeGroup.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Description</label>
                    <textarea name="description" rows="2" :value="activeGroup.description" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Age Range (optional)</label>
                        <input type="text" name="age_range" :value="activeGroup.age_range" placeholder="e.g. 6-10" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Color</label>
                        <input type="color" name="color" :value="activeGroup.color" class="w-full h-9 rounded-xl border border-slate-200 dark:border-slate-700">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Icon Emoji</label>
                        <input type="text" name="icon" :value="activeGroup.icon" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Status</label>
                        <select name="is_active" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            <option value="1" :selected="activeGroup.is_active == 1">Active</option>
                            <option value="0" :selected="activeGroup.is_active == 0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
