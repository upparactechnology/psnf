<?php
$layout    = 'app';
$pageTitle = 'Academic Subjects';
$breadcrumbs = [];
ob_start();
?>

<script>
function subjectsApp() {
    return {
        viewMode: 'grid',
        filterType: 'All',
        sortBy: 'code',
        search: '',
        addModal: <?= isset($_GET['add_next']) ? 'true' : 'false' ?>,
        addNext: false,
        editModal: false,
        manageTypesModal: false,
        editId: null,
        editCode: '',
        editName: '',
        editType: '',
        newTypeName: '',
        newTypeColor: '#6366F1',
        editTypeId: null,
        editTypeName: '',
        editTypeColor: '',
        types: <?= json_encode($subjectTypes ?? []) ?>,
        init() {
            let raw = <?= json_encode($subjects ?? []) ?>;
            this.subjects = raw.map(s => {
                let d = document.createElement('div');
                for (let k of Object.keys(s)) {
                    if (typeof s[k] === 'string') {
                        d.innerHTML = s[k];
                        s[k] = d.textContent;
                    }
                }
                return s;
            });
        },
        subjects: [],
        typeColor(category) {
            const cat = (category || '').toLowerCase();
            const match = this.types.find(t => t.slug === cat || t.name.toLowerCase() === cat);
            if (match) return 'background-color:' + match.color + '20;color:' + match.color;
            return 'background-color:#F1F5F9;color:#64748B';
        },
        openEdit(id, code, name, type) {
            this.editId = id;
            this.editCode = code;
            this.editName = name;
            this.editType = type;
            this.editModal = true;
        },
        openEditType(id, name, color) {
            this.editTypeId = id;
            this.editTypeName = name;
            this.editTypeColor = color;
        },
        closeEditType() {
            this.editTypeId = null;
            this.editTypeName = '';
            this.editTypeColor = '';
        },
        get filtered() {
            let list = this.subjects;
            if (this.filterType !== 'All') {
                let f = this.filterType.toLowerCase();
                list = list.filter(s => (s.category || s.type || '').toLowerCase() === f);
            }
            if (this.search.trim()) {
                let q = this.search.toLowerCase();
                list = list.filter(s => (s.name || '').toLowerCase().includes(q) || (s.code || '').toLowerCase().includes(q));
            }
            list.sort((a, b) => {
                let va = (a[this.sortBy] || '').toLowerCase();
                let vb = (b[this.sortBy] || '').toLowerCase();
                return va.localeCompare(vb);
            });
            return list;
        }
    }
}
</script>

<div x-data="subjectsApp()" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Academic Subjects</h1>
            <p class="text-xs text-slate-500 mt-0.5"><?= count($subjects ?? []) ?> active subjects & therapy modules</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50">
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Grid Cards View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Compact Table View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <button @click="manageTypesModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Manage Types
            </button>

            <button @click="addModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Subject
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search by name or code..."
                   class="w-full bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 rounded-xl py-2.5 pl-10 pr-4 text-xs focus:outline-none focus:border-indigo-500 transition-all">
        </div>
        <!-- Sort -->
        <div class="flex items-center gap-2">
            <label class="text-2xs font-semibold text-slate-500 whitespace-nowrap">Sort:</label>
            <select x-model="sortBy" class="bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-indigo-500 transition-all">
                <option value="code">Code</option>
                <option value="name">Name</option>
                <option value="category">Category</option>
                <option value="type">Type</option>
            </select>
        </div>
        <!-- Type Filter -->
        <div class="flex items-center gap-2">
            <label class="text-2xs font-semibold text-slate-500 whitespace-nowrap">Filter:</label>
            <select x-model="filterType" class="bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-indigo-500 transition-all">
                <option value="All">All Types</option>
                <?php foreach ($subjectTypes as $t): ?>
                <option value="<?= e($t['name']) ?>"><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Empty State -->
    <template x-if="filtered.length === 0">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500">No subjects found matching your criteria.</p>
        </div>
    </template>

    <!-- GRID VIEW -->
    <div x-show="viewMode === 'grid' && filtered.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <template x-for="s in filtered" :key="s.id">
            <div class="group p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/40 transition-all shadow-sm hover:shadow-md flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        📖
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-semibold"
                          :style="typeColor(s.category || s.type || '')"
                          x-text="s.category || s.type || ''"></span>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="s.name"></h3>
                    <p class="text-2xs font-mono text-slate-400 mt-0.5" x-text="s.code"></p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-2xs text-slate-400">
                    <div class="flex gap-2">
                        <button @click="openEdit(s.id, s.code, s.name, s.category || s.type || '')" class="text-indigo-500 hover:underline font-bold">Edit</button>
                        <span class="text-slate-300 dark:text-slate-700">|</span>
                        <form :action="'<?= url('academics/subjects') ?>/' + s.id + '/delete'" method="POST" onsubmit="return confirm('Delete this subject?')" class="inline">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="text-red-500 hover:underline font-bold">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- TABLE VIEW -->
    <div x-show="viewMode === 'table' && filtered.length > 0" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-slate-400 text-2xs font-bold uppercase tracking-wider">
                    <th class="px-5 py-3">Code</th>
                    <th class="px-5 py-3">Subject Name</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-xs">
                <template x-for="s in filtered" :key="s.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-5 py-3 font-mono font-semibold text-slate-900 dark:text-white" x-text="s.code"></td>
                        <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white" x-text="s.name"></td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400 capitalize" x-text="s.type || ''"></td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-semibold"
                                  :style="typeColor(s.category || '')"
                                  x-text="s.category || ''"></span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openEdit(s.id, s.code, s.name, s.category || s.type || '')" class="text-indigo-500 hover:text-indigo-700 font-bold text-2xs">Edit</button>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <form :action="'<?= url('academics/subjects') ?>/' + s.id + '/delete'" method="POST" onsubmit="return confirm('Delete this subject?')" class="inline">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-2xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Add Subject Modal -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="addModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add New Subject</h3>
            
            <form action="<?= url('academics/subjects') ?>" method="POST" class="space-y-4 text-xs" x-ref="addSubjectForm">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_add_next" :value="addNext ? '1' : '0'" x-ref="addNextField">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Code</label>
                    <input type="text" name="code" value="<?= e($nextCode ?? 'SUB-101') ?>" placeholder="e.g. SUB-106" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Name</label>
                    <input type="text" name="name" placeholder="e.g. Sensory & Motor Skills" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select name="type" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($subjectTypes as $t): ?>
                        <option value="<?= e($t['name']) ?>"><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" @click="addNext = false" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-300 dark:hover:bg-slate-600">Save & Close</button>
                    <button type="submit" @click="addNext = true" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save & Add Next</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Subject</h3>
            
            <form :action="'<?= url('academics/subjects') ?>/' + editId" method="POST" class="space-y-4 text-xs" @keydown.enter.prevent="$el.querySelector('[type=submit]').click()">
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
                        <?php foreach ($subjectTypes as $t): ?>
                        <option value="<?= e($t['name']) ?>"><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Types Modal -->
    <div x-show="manageTypesModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="manageTypesModal = false"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Manage Subject Types</h3>
                <button @click="manageTypesModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Add New Type -->
            <form action="<?= url('academics/subject-types') ?>" method="POST" class="flex items-center gap-2 mb-5 pb-4 border-b border-slate-200 dark:border-slate-700">
                <?= \Core\View::csrf() ?>
                <input type="color" name="color" x-model="newTypeColor" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer flex-shrink-0">
                <input type="text" name="name" x-model="newTypeName" placeholder="New type name..." required class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-900 dark:text-white">
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-500 flex-shrink-0">Add</button>
            </form>

            <!-- Types List -->
            <div class="space-y-2 max-h-80 overflow-y-auto">
                <?php foreach ($subjectTypes as $t): ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50">
                    <?php if ($t['slug'] === 'academic' || $t['slug'] === 'therapy' || $t['slug'] === 'life-skills' || $t['slug'] === 'co-curricular' || $t['slug'] === 'vocational'): ?>
                        <span class="w-6 h-6 rounded-lg flex-shrink-0" style="background-color: <?= e($t['color']) ?>"></span>
                        <span class="flex-1 text-sm font-semibold text-slate-900 dark:text-white"><?= e($t['name']) ?></span>
                        <span class="text-2xs text-slate-400 italic">Default</span>
                    <?php else: ?>
                        <form action="<?= url('academics/subject-types') ?>/<?= $t['id'] ?>" method="POST" class="contents">
                            <?= \Core\View::csrf() ?>
                            <input type="color" name="color" value="<?= e($t['color']) ?>" class="w-6 h-6 rounded-lg border-0 cursor-pointer flex-shrink-0">
                            <input type="text" name="name" value="<?= e($t['name']) ?>" class="flex-1 bg-transparent border-b border-slate-300 dark:border-slate-600 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 px-1">
                            <button type="submit" class="text-indigo-500 hover:text-indigo-700 text-xs font-bold">Save</button>
                        </form>
                        <form action="<?= url('academics/subject-types') ?>/<?= $t['id'] ?>/delete" method="POST" class="contents" onsubmit="return confirm('Delete this type? Subjects using it will need to be updated.')">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700">
                <button @click="manageTypesModal = false" class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Done</button>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
