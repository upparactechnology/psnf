<?php
$layout    = 'app';
$pageTitle = 'Academic Subjects';
$breadcrumbs = [];
ob_start();
?>

<div x-data="{ 
    addModal: false, 
    editModal: false,
    editId: null,
    editCode: '',
    editName: '',
    editType: '',
    openEdit(id, code, name, type) {
        this.editId = id;
        this.editCode = code;
        this.editName = name;
        this.editType = type;
        this.editModal = true;
    }
}" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Academic Subjects</h1>
            <p class="text-xs text-slate-500 mt-0.5"><?= count($subjects ?? []) ?> active subjects & therapy modules</p>
        </div>
        <div>
            <button @click="addModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Add Subject
            </button>
        </div>
    </div>

    <!-- Subjects Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <?php foreach ($subjects as $s): ?>
        <div class="group p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/40 transition-all shadow-sm hover:shadow-md flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                    📖
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    <?= e($s['type']) ?>
                </span>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white"><?= e($s['name']) ?></h3>
                <p class="text-2xs font-mono text-slate-400 mt-0.5"><?= e($s['code']) ?></p>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-2xs text-slate-400">
                <div class="flex gap-2">
                    <button @click="openEdit(<?= $s['id'] ?>, '<?= e($s['code']) ?>', '<?= e($s['name']) ?>', '<?= e($s['type']) ?>')" class="text-indigo-500 hover:underline font-bold">Edit</button>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <form action="<?= url('academics/subjects/' . $s['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this subject?')" class="inline">
                        <?= \Core\View::csrf() ?>
                        <button type="submit" class="text-red-500 hover:underline font-bold">Delete</button>
                    </form>
                </div>
                <span class="text-3xs font-semibold px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">Manage</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Subject Modal -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="addModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add New Subject</h3>
            
            <form action="<?= url('academics/subjects') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Code</label>
                    <input type="text" name="code" placeholder="e.g. SUB-106" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Name</label>
                    <input type="text" name="name" placeholder="e.g. Sensory & Motor Skills" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select name="type" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="Academic">Academic</option>
                        <option value="Therapy">Therapy</option>
                        <option value="Skill">Skill Development</option>
                        <option value="Activity">Physical / Activity</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Subject</h3>
            
            <form :action="'/psnf/public/academics/subjects/' + editId" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Code</label>
                    <input type="text" name="code" :value="editCode" @input="editCode = $event.target.value" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Name</label>
                    <input type="text" name="name" :value="editName" @input="editName = $event.target.value" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select name="type" :value="editType" @change="editType = $event.target.value" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="Academic">Academic</option>
                        <option value="Therapy">Therapy</option>
                        <option value="Skill">Skill Development</option>
                        <option value="Activity">Physical / Activity</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
