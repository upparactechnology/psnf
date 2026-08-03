<?php
$layout    = 'app';
$pageTitle = 'Curriculum Template Builder';
$breadcrumbs = [];
ob_start();
?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{ addSectionModal: false, addSubjectModal: false, activeSectionId: null, showCreateTemplateModal: false }">

    <!-- Filters Selection Panel -->
    <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
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

        <div class="flex items-center gap-3">
            <button @click="showCreateTemplateModal = true" class="px-3.5 py-2 rounded-xl text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-505 shadow-sm transition-all">
                + New Template
            </button>
            <?php if ($template): ?>
                <div class="text-xs font-extrabold text-indigo-500 bg-indigo-500/10 px-3.5 py-2 rounded-xl">
                    Version: <?= $template['version'] ?>
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
                <button @click="addSectionModal = true" class="px-3.5 py-2 rounded-xl text-2xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm">
                    + Add Category Section
                </button>
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
                            </div>
                            <button type="button" @click="activeSectionId = <?= $sec['id'] ?>; addSubjectModal = true" class="text-2xs font-bold text-indigo-500 hover:underline">
                                + Add Subject
                            </button>
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
                                                <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-500 font-bold"><?= e($cs['assessment_type']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-slate-400 font-medium">Sequence:</span>
                                                <input type="number" name="sequence[<?= $cs['id'] ?>]" value="<?= $cs['sequence'] ?>" class="w-12 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-center font-mono">
                                            </div>
                                            <a href="<?= url('academics/curriculum/subjects/' . $cs['id'] . '/delete?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId) ?>" class="text-red-500 font-bold hover:underline ml-2">
                                                Remove
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($sections)): ?>
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

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addSectionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Add Section</button>
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

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Select Subject (Master)</label>
                    <select name="subject_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <?php foreach ($subjectsMaster as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= e($sub['name']) ?> (Category: <?= e($sub['category']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Assessment Type</label>
                        <select name="assessment_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            <option value="Marks">Marks</option>
                            <option value="Grade">Grade</option>
                            <option value="Rating">Rating</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Is Required</label>
                        <select name="is_required" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addSubjectModal = false; activeSectionId = null" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Add Subject</button>
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

<?php
$content = ob_get_clean();
?>
