<?php
$layout    = 'app';
$pageTitle = 'Classes & Sections';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/academics'], ['label' => 'Classes']];
ob_start();
?>

<div x-data="{ 
    showCreateModal: <?= isset($_GET['add_next']) ? 'true' : 'false' ?>,
    addNext: false,
    selectedGroupId: '<?= $groups[0]['id'] ?? '' ?>',
    selectedYearId: '<?= $years[0]['id'] ?? '' ?>'
}" class="space-y-6 max-w-6xl mx-auto py-2">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Classes & Sections</h2>
            <p class="text-xs text-slate-500 mt-0.5">Directory of active classes, section configurations, and enrolled students</p>
        </div>
        <?php if (has_permission('create_classes')): ?>
        <button @click="showCreateModal = true" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
            + Create Class
        </button>
        <?php endif; ?>
    </div>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <?php if (empty($classes)): ?>
            <div class="col-span-full rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-sm">
                <span class="text-3xl block mb-2">📁</span>
                <h3 class="text-sm font-bold text-slate-850 dark:text-white">No active classes found</h3>
                <p class="text-xs text-slate-500 mt-1">Initialize classes to manage student groups.</p>
            </div>
        <?php else: ?>
            <?php foreach ($classes as $c): ?>
                <?php 
                    $color = $c['group_color'] ?: '#6366f1';
                    $icon = $c['group_icon'] ?: '🎓';
                ?>
                <div class="relative group">
                    <a href="<?= url('academics/classes/' . $c['id']) ?>" 
                       class="block rounded-2xl border border-slate-200 dark:border-slate-850 p-5 bg-white dark:bg-slate-900/40 hover:border-indigo-500/40 transition-all shadow-sm h-full flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div style="background-color: <?= $color ?>20;" class="w-10 h-10 rounded-xl flex items-center justify-center text-xl">
                                    <?= e($icon) ?>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-500"><?= e($c['name']) ?></h4>
                                    <p class="text-3xs text-slate-400 font-mono">Section: <?= e($c['section'] ?: 'Default') ?></p>
                                </div>
                            </div>
                            
                            <div class="space-y-1.5 text-xs text-slate-500">
                                <p><span class="font-bold text-slate-700 dark:text-slate-350">Main Group:</span> <?= e($c['group_name'] ?: 'None') ?></p>
                                <p><span class="font-bold text-slate-700 dark:text-slate-350">Year:</span> <?= e($c['year_name'] ?: 'None') ?></p>
                                <p><span class="font-bold text-slate-700 dark:text-slate-350">Curriculum:</span> <?= e($c['curriculum_name'] ?: 'Default Mapping') ?></p>
                                <p><span class="font-bold text-slate-700 dark:text-slate-350">Teacher:</span> <?= e($c['teacher_name'] ?: 'Not Assigned') ?></p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-850 flex items-center justify-between text-xs font-bold text-indigo-500">
                            <span><?= $c['student_count'] ?> Students</span>
                            <span>Details &rarr;</span>
                        </div>
                    </a>

                    <!-- Delete Form -->
                    <form action="<?= url('academics/classes/' . $c['id'] . '/delete') ?>" method="POST" class="absolute top-4 right-4 z-20 opacity-0 group-hover:opacity-100 transition-all" onsubmit="return confirm('Delete this class? Enrolled students will be unassigned.')">
                        <?= \Core\View::csrf() ?>
                        <button type="submit" class="p-1 rounded bg-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition-all text-xs font-bold">
                            Delete
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Create Class Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create New Class</h3>
            
            <form action="<?= url('academics/classes') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_add_next" :value="addNext ? '1' : '0'">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Class Name</label>
                        <input type="text" name="class" required placeholder="e.g. Functional A" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Section</label>
                        <input type="text" name="section" placeholder="e.g. A" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Main Group</label>
                    <select name="main_group_id" x-model="selectedGroupId" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Academic Year</label>
                    <select name="academic_year_id" x-model="selectedYearId" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y['id'] ?>"><?= e($y['year_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Curriculum Template</label>
                    <select name="curriculum_template_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <option value="">-- No Specific Curriculum (Default Mapping) --</option>
                        <?php foreach ($curriculums as $curr): ?>
                            <option value="<?= $curr['id'] ?>"
                                    x-show="selectedGroupId == '<?= $curr['main_group_id'] ?>' && selectedYearId == '<?= $curr['academic_year_id'] ?>'">
                                <?= e($curr['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">Class Teacher</label>
                    <select name="class_teacher_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <option value="">-- Choose Class Teacher --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" @click="addNext = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Save & Close</button>
                    <button type="submit" @click="addNext = true" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save & Add Next</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
