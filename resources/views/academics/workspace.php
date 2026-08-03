<?php
$layout    = 'app';
$pageTitle = 'Academic Workspace';
$breadcrumbs = [];
ob_start();
?>

<div class="max-w-6xl mx-auto space-y-6 py-2">

    <!-- Top Action Bar: Academic Year Selector -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                🎓
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Academic Workspace</h1>
                <p class="text-xs text-slate-500">Manage school divisions, versioned curriculum templates, classes, and report cards</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" action="<?= url('academics') ?>" class="flex items-center gap-2">
                <?php if ($selectedGroupId): ?>
                    <input type="hidden" name="main_group_id" value="<?= $selectedGroupId ?>">
                <?php endif; ?>
                <label class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y['id'] ?>" <?= $y['id'] == $selectedYearId ? 'selected' : '' ?>>
                            <?= e($y['year_name']) ?> <?= $y['status'] === 'current' ? '(Active)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Groups List / Navigation Tabs -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Main Groups</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            <?php foreach ($mainGroups as $mg): ?>
                <?php 
                    $isSelected = ($mg['id'] == $selectedGroupId);
                    $styleStr = $isSelected 
                        ? "background-color: {$mg['color']}20; border-color: {$mg['color']}; color: {$mg['color']};" 
                        : "border-color: transparent;";
                ?>
                <a href="<?= url('academics?academic_year_id=' . $selectedYearId . '&main_group_id=' . $mg['id']) ?>" 
                   style="<?= $styleStr ?>"
                   class="group p-4 rounded-2xl border transition-all duration-200 text-center shadow-sm bg-white dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-700 flex flex-col items-center justify-center space-y-2">
                    <span class="text-2xl"><?= e($mg['icon']) ?></span>
                    <span class="text-xs font-extrabold block truncate text-slate-900 dark:text-white group-hover:text-indigo-500"><?= e($mg['name']) ?></span>
                    <span class="text-3xs font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                        <?= $mg['class_count'] ?> Classes
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Scoped Group Workspace View -->
    <?php if ($groupContext): ?>
        <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 space-y-6 shadow-sm">
            
            <!-- Group Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-5 gap-4">
                <div class="flex items-center gap-3">
                    <div style="background-color: <?= $groupContext['color'] ?>20;" class="w-12 h-12 rounded-2xl flex items-center justify-center text-3xl">
                        <?= e($groupContext['icon']) ?>
                    </div>
                    <div>
                        <h2 style="color: <?= $groupContext['color'] ?>;" class="text-lg font-black tracking-tight"><?= e($groupContext['name']) ?> Workspace</h2>
                        <p class="text-2xs text-slate-400 max-w-lg mt-0.5"><?= e($groupContext['description']) ?></p>
                    </div>
                </div>

                <!-- Group Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= url('academics/curriculum?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId) ?>" class="px-3 py-1.5 rounded-lg text-2xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">
                        Edit Curriculum
                    </a>
                    <a href="<?= url('academics/classes') ?>" class="px-3 py-1.5 rounded-lg text-2xs font-bold text-slate-700 dark:text-slate-350 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition-all">
                        + New Class
                    </a>
                </div>
            </div>

            <!-- Stats Rows Scoped to the Selected Group -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/10">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Classes</span>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1"><?= count($workspaceData['classes']) ?></p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/10">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Students</span>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1"><?= count($workspaceData['students']) ?></p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/10">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Attendance Today</span>
                    <p class="text-lg font-black text-emerald-500 mt-1"><?= $workspaceData['attendancePct'] ?>%</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/10">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Pending Report Cards</span>
                    <p class="text-lg font-black text-amber-500 mt-1"><?= $workspaceData['pendingReportCards'] ?></p>
                </div>
            </div>

            <!-- Workspace Submodules Panels -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Classes Panel -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Classes</h4>
                        <a href="<?= url('academics/classes') ?>" class="text-3xs text-indigo-500 font-bold hover:underline">Manage Classes &rarr;</a>
                    </div>
                    <div class="space-y-2">
                        <?php if (empty($workspaceData['classes'])): ?>
                            <p class="text-2xs text-slate-500 py-3 text-center bg-slate-50/50 dark:bg-slate-800/10 rounded-xl">No classes found in this Main Group.</p>
                        <?php else: ?>
                            <?php foreach ($workspaceData['classes'] as $cls): ?>
                                <a href="<?= url('academics/classes/' . $cls['id']) ?>" class="block p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/20 dark:bg-slate-800/5 hover:border-indigo-500/40 transition-all">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-black text-slate-900 dark:text-white"><?= e($cls['name']) ?> (Section: <?= e($cls['section'] ?: '—') ?>)</span>
                                            <span class="text-3xs block text-slate-400 mt-0.5">Teacher: <?= e($cls['teacher_name'] ?: 'Not Assigned') ?></span>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-500 text-3xs font-bold"><?= $cls['student_count'] ?> Students</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Curriculum Subjects (Dynamic Section Layout) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inherited Curriculum Subjects</h4>
                        <a href="<?= url('academics/curriculum?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId) ?>" class="text-3xs text-indigo-500 font-bold hover:underline">Manage Template &rarr;</a>
                    </div>
                    <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-800/5 space-y-3 max-h-[250px] overflow-y-auto">
                        <?php if (empty($workspaceData['curriculumSubjects'])): ?>
                            <p class="text-2xs text-slate-500 text-center py-4">No curriculum template resolved for this Main Group and Year. Please create one.</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach ($workspaceData['curriculumSubjects'] as $sub): ?>
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800/80 text-2xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            <span class="font-extrabold text-slate-850 dark:text-slate-350"><?= e($sub['name']) ?></span>
                                            <span class="text-3xs text-slate-400 font-mono">(<?= e($sub['code']) ?>)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-3xs font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            <?= e($sub['category']) ?> (<?= e($sub['assessment_type']) ?>)
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    <?php else: ?>
        <!-- Global Dashboard Summary -->
        <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-6 space-y-6 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Academic Overview (<?= e($selectedYearName) ?>)</h3>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/30">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Main Groups</span>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?= $stats['groups_count'] ?></p>
                </div>
                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/30">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Classes</span>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?= $stats['classes_count'] ?></p>
                </div>
                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/30">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Students Enrolled</span>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?= $stats['students_count'] ?></p>
                </div>
                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/30">
                    <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Teachers</span>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?= $stats['teachers_count'] ?></p>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 text-center py-6">Please choose a Main Group from above to enter its specific scoped workspace.</p>
        </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
