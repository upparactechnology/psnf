<?php
$layout    = 'app';
$pageTitle = 'Exams Setup';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Exams Setup']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Exams Setup</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage exam definitions — Semester 1 & 2 evaluations, unit tests, projects</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('academics/exams/marksheet') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Marksheet
            </a>
            <a href="<?= url('academics/assessments') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18"/></svg>
                Bulk Marks Entry
            </a>
            <a href="<?= url('academics/exams/create?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId . '&semester=' . urlencode($selectedSemester)) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Exam
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="<?= url('academics/exams') ?>" class="flex flex-wrap items-end gap-3 text-xs">
            <div class="space-y-1">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year_id" class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y['id'] ?>" <?= (isset($selectedYearId) && $selectedYearId == $y['id']) ? 'selected' : '' ?>><?= e($y['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Main Group</label>
                <select name="main_group_id" class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500">
                    <?php foreach ($mainGroups as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $selectedGroupId == $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Semester</label>
                <select name="semester" class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500">
                    <option value="">All Semesters</option>
                    <option value="Semester 1" <?= $selectedSemester === 'Semester 1' ? 'selected' : '' ?>>Semester 1</option>
                    <option value="Semester 2" <?= $selectedSemester === 'Semester 2' ? 'selected' : '' ?>>Semester 2</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/50 shadow-md">
                Filter
            </button>
        </form>
    </div>

    <!-- Exams List -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Exam Name</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Semester</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Max Marks</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Subjects</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($exams)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            No exams found. Create your first exam to get started.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $ex): ?>
                            <?php
                                $examSubjectIds = json_decode($ex['subject_ids'] ?? 'null', true) ?: [];
                            ?>
                        <tr class="hover:bg-slate-900/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($ex['name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white"><?= e($ex['name']) ?></p>
                                        <p class="text-xs text-slate-500 font-mono">ID: <?= $ex['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg <?= $ex['semester'] === 'Semester 1' ? 'bg-emerald-950/40 text-emerald-400 border border-emerald-900/30' : 'bg-amber-950/40 text-amber-400 border border-amber-900/30' ?>">
                                    <?= e($ex['semester']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350 text-center font-mono font-medium">
                                <?= $ex['max_marks'] ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($examSubjectIds)): ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50"><?= count($examSubjectIds) ?> subjects selected</span>
                                <?php else: ?>
                                    <span class="text-[10px] text-slate-600 italic">All subjects</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= url('academics/exams/' . $ex['id'] . '/edit') ?>"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] bg-slate-850 text-indigo-400 hover:bg-slate-800 border border-indigo-500/10">
                                        Edit
                                    </a>
                                    <form action="<?= url('academics/exams/' . $ex['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this exam?')" class="inline">
                                        <?= \Core\View::csrf() ?>
                                        <input type="hidden" name="main_group_id" value="<?= e($selectedGroupId) ?>">
                                        <input type="hidden" name="academic_year_id" value="<?= e($selectedYearId) ?>">
                                        <input type="hidden" name="semester" value="<?= e($selectedSemester) ?>">
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] bg-slate-855 text-red-400 hover:bg-slate-800 border border-red-500/10">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
