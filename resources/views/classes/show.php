<?php
$layout    = 'app';
$pageTitle = 'Class Details: ' . $class['name'];
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/academics'], ['label' => 'Classes', 'url' => '/academics/classes'], ['label' => 'Show']];
ob_start();
?>

<div x-data="{ 
    showEditModal: false,
    showEnrollModal: false,
    selectedGroupId: '<?= $class['main_group_id'] ?>',
    selectedYearId: '<?= $class['academic_year_id'] ?>'
}" class="max-w-6xl mx-auto space-y-6 py-2">

    <!-- Header Panel detailing Class Info -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div style="background-color: <?= $class['group_color'] ?: '#6366f1' ?>20;" class="w-12 h-12 rounded-2xl flex items-center justify-center text-3xl shadow-sm">
                <?= e($class['group_icon'] ?: '🎓') ?>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight"><?= e($class['name']) ?> (Section: <?= e($class['section'] ?: 'Default') ?>)</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Main Group: <span class="font-extrabold" style="color: <?= $class['group_color'] ?: '#6366f1' ?>;"><?= e($class['group_name'] ?: 'None') ?></span> &middot; 
                    Academic Year: <span class="font-bold text-slate-800 dark:text-slate-200"><?= e($class['year_name'] ?: '—') ?></span> &middot;
                    Template: <span class="font-bold text-indigo-500"><?= e($curriculum['name'] ?? 'None (Default Mapping)') ?></span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="space-y-1 text-right text-xs">
                <p class="text-slate-400">Class Teacher</p>
                <p class="font-black text-slate-850 dark:text-white text-sm"><?= e($class['teacher_name'] ?: 'Not Assigned') ?></p>
            </div>
            <button @click="showEditModal = true" class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                Edit Class
            </button>
        </div>
    </div>

    <!-- Main Grid: Enrolled Students vs. Inherited Curriculum Subjects -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Students List (Span 2) -->
        <div class="md:col-span-2 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Enrolled Students (<?= count($students) ?>)</h3>
                <button @click="showEnrollModal = true" class="px-2.5 py-1.5 rounded-xl text-3xs font-extrabold text-indigo-500 bg-indigo-500/10 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                    + Enroll Students
                </button>
            </div>
            
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="p-3">Student Name</th>
                            <th class="p-3">Roll No</th>
                            <th class="p-3">GR No</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400">No students enrolled in this class.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr class="border-b border-slate-200 dark:border-slate-800/60 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                    <td class="p-3 font-bold text-slate-900 dark:text-white"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                    <td class="p-3 font-mono text-slate-500"><?= e($s['roll_number'] ?: '—') ?></td>
                                    <td class="p-3 font-mono text-slate-500"><?= e($s['gr_number'] ?: '—') ?></td>
                                    <td class="p-3 text-right flex items-center justify-end gap-3">
                                        <a href="<?= url('academics/students/' . $s['id']) ?>" class="text-indigo-500 font-bold hover:underline">Profile &rarr;</a>
                                        <form action="<?= url('academics/classes/' . $class['id'] . '/remove-student/' . $s['id']) ?>" method="POST" class="inline" onsubmit="return confirm('Remove student from this class?')">
                                            <?= \Core\View::csrf() ?>
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold hover:underline bg-transparent border-0 p-0 cursor-pointer">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inherited Curriculum Subjects Panel -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inherited Curriculum</h3>
                <span class="text-[10px] text-slate-400 font-mono">v1.0</span>
            </div>
            
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm space-y-4">
                <div>
                    <h4 class="font-extrabold text-slate-900 dark:text-white text-xs"><?= e($curriculum['name'] ?? 'No curriculum active') ?></h4>
                    <p class="text-3xs text-slate-400 mt-0.5">Automatically inherited by all students enrolled in this class.</p>
                </div>

                <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                    <?php if (empty($subjects)): ?>
                        <p class="text-3xs text-slate-400 text-center py-4">No subjects found in resolved curriculum.</p>
                    <?php else: ?>
                        <?php foreach ($subjects as $sub): ?>
                            <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-850 bg-slate-50/40 dark:bg-slate-900/50 flex items-center justify-between text-2xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    <span class="font-extrabold text-slate-850 dark:text-slate-350"><?= e($sub['name']) ?></span>
                                </div>
                                <span class="text-3xs text-slate-400"><?= e($sub['category']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Edit Class Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Class Details</h3>
            
            <form action="<?= url('academics/classes/' . $class['id']) ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Class Name</label>
                        <input type="text" name="class" required value="<?= e($class['name']) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Section</label>
                        <input type="text" name="section" value="<?= e($class['section']) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
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
                                    x-show="selectedGroupId == '<?= $curr['main_group_id'] ?>' && selectedYearId == '<?= $curr['academic_year_id'] ?>'"
                                    <?= $curr['id'] == $class['curriculum_template_id'] ? 'selected' : '' ?>>
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
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $class['class_teacher_id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enroll Students Modal -->
    <div x-show="showEnrollModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Enroll Students in <?= e($class['name']) ?></h3>
            
            <form action="<?= url('academics/classes/' . $class['id'] . '/enroll') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                
                <p class="text-2xs text-slate-505">Only enrolled students without an active class assignment are listed below.</p>
                
                <div class="max-h-[300px] overflow-y-auto border border-slate-200 dark:border-slate-800 rounded-xl p-3 space-y-2">
                    <?php if (empty($unassignedStudents)): ?>
                        <p class="text-2xs text-slate-400 text-center py-6">No unassigned students available to enroll.</p>
                    <?php else: ?>
                        <?php foreach ($unassignedStudents as $ustu): ?>
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer">
                                <input type="checkbox" name="student_ids[]" value="<?= $ustu['id'] ?>" class="rounded text-indigo-650 focus:ring-indigo-500 bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700">
                                <div class="text-left">
                                    <p class="font-bold text-slate-850 dark:text-slate-200"><?= e($ustu['first_name'] . ' ' . $ustu['last_name']) ?></p>
                                    <p class="text-[10px] text-slate-450 font-mono">Admission No: <?= e($ustu['admission_number']) ?></p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEnrollModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500" <?= empty($unassignedStudents) ? 'disabled' : '' ?>>Enroll Students</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
