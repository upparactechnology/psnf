<?php
$layout    = 'app';
$pageTitle = 'Curriculum Template Builder';
$breadcrumbs = [];
ob_start();
?>
<?php $isLocked = is_year_locked($selectedYearId); ?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{ addSectionModal: false, editSectionModal: false, addSubjectModal: false, activeSectionId: null, showCreateTemplateModal: false, editSection: { id: '', name: '', assessment_type: '' } }">

    <!-- Filters Selection Panel -->
    <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <?php if ($isLocked): ?>
            <div class="w-full mb-2 p-3.5 rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-500 text-xs font-bold flex items-center gap-2">
                <span>🔒</span>
                <span>This academic year is locked. Only administrators can edit curriculum mappings.</span>
            </div>
        <?php endif; ?>
        <form method="GET" action="<?= url('academics/curriculum') ?>" class="flex flex-wrap items-center gap-4 text-xs font-bold">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 dark:text-slate-500 uppercase tracking-wider">Academic Year</span>
                <select name="academic_year_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y['id'] ?>" <?= $y['id'] == $selectedYearId ? 'selected' : '' ?>><?= e($y['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-400 dark:text-slate-500 uppercase tracking-wider">Main Group</span>
                <select name="main_group_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                    <?php foreach ($mainGroups as $mg): ?>
                        <option value="<?= $mg['id'] ?>" <?= $mg['id'] == $selectedGroupId ? 'selected' : '' ?>><?= e($mg['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($templates)): ?>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 dark:text-slate-500 uppercase tracking-wider">Template</span>
                    <select name="template_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($template && $t['id'] == $template['id']) ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </form>

        <div class="flex items-center gap-2">
            <?php if (!$isLocked): ?>
                <button @click="showCreateTemplateModal = true" class="px-2.5 py-1.5 rounded-lg text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-all">
                    + New
                </button>
            <?php endif; ?>
            <?php if ($template && !$isLocked): ?>
                <form action="<?= url("academics/curriculum/templates/{$template['id']}/delete") ?>" method="POST" onsubmit="return confirm('Delete this template and all its sections & subjects? This cannot be undone.')">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                    <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-2xs font-bold text-white bg-red-600 hover:bg-red-500 shadow-sm transition-all">
                        Delete
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($template): ?>
                <div class="text-2xs font-extrabold text-indigo-500 bg-indigo-500/10 px-2.5 py-1.5 rounded-lg">
                    v<?= $template['version'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Curriculum Layout Container -->
    <?php if (!$template): ?>
        <div class="p-8 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-4 max-w-md mx-auto">
            <span class="text-4xl block">📁</span>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">No Curriculum Template Found</h3>
            <p class="text-xs text-slate-500">No template has been initialized for this Year and Main Group combo yet.</p>
            
            <form action="<?= url('academics/curriculum/templates') ?>" method="POST" class="space-y-3">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <input type="text" name="name" placeholder="Template Name (e.g. Functional Curriculum)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-white text-xs shadow">
                    Create Curriculum Template
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <!-- Header Title -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white"><?= e($template['name']) ?></h2>
                    <p class="text-2xs text-slate-400">Manage subject categories, ordering, and default grading formulas</p>
                </div>
                <?php if (!$isLocked): ?>
                    <button @click="addSectionModal = true" class="px-3.5 py-2 rounded-xl text-2xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm">
                        + Add Category Section
                    </button>
                <?php endif; ?>
            </div>

            <!-- Curriculum Tree Explorer -->
            <form action="<?= url('academics/curriculum/reorder') ?>" method="POST" class="space-y-6">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">

                <?php foreach ($sections as $sec): ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-5 space-y-4 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-850 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider"><?= e($sec['section_name']) ?></span>
                                <span class="px-2 py-0.5 rounded text-3xs font-bold bg-slate-100 dark:bg-slate-850 text-slate-400">Section ID: <?= $sec['id'] ?></span>
                                <span class="px-2 py-0.5 rounded text-3xs font-bold bg-indigo-500/10 text-indigo-500 font-mono"><?= e($sec['assessment_type'] ?? 'Grade') ?>-based</span>
                                <?php if (!$isLocked): ?>
                                    <button type="button" 
                                            @click="editSection = { id: <?= $sec['id'] ?>, name: '<?= e(addslashes($sec['section_name'])) ?>', assessment_type: '<?= e($sec['assessment_type'] ?? 'Grade') ?>' }; editSectionModal = true"
                                            class="px-2 py-1 rounded-lg text-3xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                        Edit Section
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isLocked): ?>
                                <button type="button" @click="activeSectionId = <?= $sec['id'] ?>; addSubjectModal = true" class="px-2.5 py-1.5 rounded-lg text-2xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                                    + Add Subject
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Subjects list in this section -->
                        <div class="space-y-2">
                            <?php if (empty($sec['subjects'])): ?>
                                <p class="text-3xs text-slate-400 text-center py-4">No subjects added to this section yet.</p>
                            <?php else: ?>
                                <?php foreach ($sec['subjects'] as $cs): ?>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-850 bg-slate-50/40 dark:bg-slate-900/60 gap-3 text-2xs">
                                        <div class="flex items-center gap-3">
                                            <span class="text-slate-400">☰</span>
                                            <div>
                                                <span class="font-extrabold text-slate-850 dark:text-slate-300"><?= e($cs['subject_name']) ?></span>
                                                <span class="text-3xs text-slate-450 block font-mono">Code: <?= e($cs['subject_code']) ?></span>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-3xs">
                                             <div class="flex items-center gap-1.5">
                                                  <span class="text-slate-400 font-medium">Evaluation:</span>
                                                  <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-500 font-bold"><?= e($sec['assessment_type'] ?? 'Grade') ?></span>
                                              </div>
                                             <div class="flex items-center gap-1.5">
                                                 <span class="text-slate-400 font-medium">Sequence:</span>
                                                 <input type="number" name="sequence[<?= $cs['id'] ?>]" value="<?= $cs['sequence'] ?>" <?= $isLocked ? 'disabled' : '' ?> class="w-12 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-center font-mono">
                                             </div>
                                             <?php if (!$isLocked): ?>
                                                 <form action="<?= url('academics/curriculum/subjects/' . $cs['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Remove this subject from curriculum?')" class="inline">
                                                     <?= \Core\View::csrf() ?>
                                                     <input type="hidden" name="academic_year_id" value="<?= e($selectedYearId) ?>">
                                                     <input type="hidden" name="main_group_id" value="<?= e($selectedGroupId) ?>">
                                                     <button type="submit" class="text-red-500 font-bold hover:underline ml-2">Remove</button>
                                                 </form>
                                             <?php endif; ?>
                                         </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($sections) && !$isLocked): ?>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-650 hover:bg-indigo-600 text-white font-bold text-xs shadow-md transition-all">
                            Save Changes & Reorder
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <!-- Add Section Modal -->
    <div x-show="addSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add Category Section</h3>
            
            <form action="<?= url('academics/curriculum/templates/' . ($template['id'] ?? 0) . '/sections') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Section Name</label>
                    <input type="text" name="section_name" placeholder="e.g. Life Skills (TILS)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Assessment Type</label>
                    <select name="assessment_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <option value="Grade">Grade-based</option>
                        <option value="Marks">Marks-based</option>
                        <option value="Rating">Rating-based</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addSectionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Add Section</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div x-show="editSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Category Section</h3>
            
            <form action="<?= url('academics/curriculum/sections/update') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <input type="hidden" name="section_id" :value="editSection.id">
                
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Section Name</label>
                    <input type="text" name="section_name" x-model="editSection.name" placeholder="e.g. Life Skills (TILS)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Assessment Type</label>
                    <select name="assessment_type" x-model="editSection.assessment_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <option value="Grade">Grade-based</option>
                        <option value="Marks">Marks-based</option>
                        <option value="Rating">Rating-based</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editSectionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div x-show="addSubjectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add Subject to Curriculum Category</h3>
            
            <form action="<?= url('academics/curriculum/subjects/add') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <input type="hidden" name="section_id" :value="activeSectionId">

                <div class="space-y-1 relative" x-data="subjectPicker(<?= htmlspecialchars(json_encode(array_values(array_map(fn($s) => ['id' => (int)$s['id'], 'name' => $s['name'], 'category' => $s['category']], $subjectsMaster)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Select Subjects (Master)</label>

                    <div class="relative">
                        <div class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 overflow-hidden" :class="open ? 'ring-2 ring-indigo-500 border-indigo-500' : ''">
                            <div x-show="selected.length > 0" class="flex flex-wrap gap-1 px-3 pt-2 pb-1">
                                <template x-for="s in selected" :key="s.id">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[11px] font-medium">
                                        <span x-html="s.name"></span>
                                        <button type="button" @click.stop="toggle(s)" class="text-indigo-400 hover:text-indigo-600 font-bold leading-none">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex items-center">
                                <input x-ref="searchInput" x-model="search" @focus="open = true" @click.stop @keydown.escape="open = false; search = ''" @keydown.backspace="if(!search && selected.length) { toggle(selected[selected.length - 1]) }" type="text" :placeholder="selected.length ? '' : 'Type to search & select...'" class="w-full px-3 py-2 bg-transparent text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none">
                            </div>
                        </div>

                        <div x-show="open" x-cloak @click.away="open = false; search = ''" class="absolute z-10 mt-1 w-full bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
                            <div class="max-h-60 overflow-y-auto">
                                <div class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 flex justify-between sticky top-0 z-10">
                                    <span x-text="selected.length + ' selected'"></span>
                                    <button type="button" @click="clearAll()" class="text-indigo-500 hover:text-indigo-700 underline" x-show="selected.length > 0">Clear all</button>
                                </div>
                                <template x-if="filtered.length === 0">
                                    <div class="px-3 py-4 text-center text-xs text-slate-400">No subjects found</div>
                                </template>
                                <template x-for="(items, cat) in grouped()" :key="cat">
                                    <div>
                                        <div class="px-3 py-1 bg-slate-50 dark:bg-slate-900 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 sticky top-0" x-text="cat"></div>
                                        <template x-for="s in items" :key="s.id">
                                            <label @click.prevent="toggle(s)" class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-xs text-slate-700 dark:text-slate-300 border-b border-slate-50 dark:border-slate-800 last:border-0 transition-colors">
                                                <input type="checkbox" :checked="isSelected(s)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 pointer-events-none">
                                                <span x-html="s.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="subject_ids" :value="selected.map(s => s.id).join(',')">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Is Required</label>
                    <select name="is_required" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addSubjectModal = false; activeSectionId = null" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Add Subjects</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div x-show="showCreateTemplateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create New Curriculum Template</h3>
            
            <form action="<?= url('academics/curriculum/templates') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="academic_year_id" value="<?= $selectedYearId ?>">
                <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Template Name</label>
                    <input type="text" name="name" placeholder="e.g. Functional Curriculum B" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Description</label>
                    <textarea name="description" placeholder="Describe this curriculum track..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" rows="3"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showCreateTemplateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Create Template</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subjectPicker', (subjects) => ({
        search: '',
        open: false,
        subjects: subjects,
        selected: [],
        get filtered() {
            if (!this.search) return this.subjects;
            let q = this.search.toLowerCase();
            return this.subjects.filter(s =>
                s.name.toLowerCase().includes(q) ||
                (s.category && s.category.toLowerCase().includes(q))
            );
        },
        grouped() {
            let groups = {};
            this.filtered.forEach(s => {
                let cat = s.category || 'Other';
                if (!groups[cat]) groups[cat] = [];
                groups[cat].push(s);
            });
            return groups;
        },
        isSelected(s) {
            return this.selected.some(x => x.id === s.id);
        },
        toggle(s) {
            if (this.isSelected(s)) {
                this.selected = this.selected.filter(x => x.id !== s.id);
            } else {
                this.selected.push({ id: s.id, name: s.name, category: s.category });
            }
        },
        clearAll() {
            this.selected = [];
        }
    }));
});
</script>

<?php
$content = ob_get_clean();
?>
