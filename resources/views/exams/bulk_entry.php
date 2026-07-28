<?php
$layout    = 'app';
$pageTitle = 'Exams & Bulk Grade Kiosk';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Exams', 'url' => '/exams'], ['label' => 'Bulk Entry']];
ob_start();
?>

<div x-data="{
    studentCount: <?= count($students) ?>,
    subjectCount: <?= count($subjects) ?>,
    calculateGrade(obt, max) {
        if (!obt || !max || max == 0) return '-';
        let pct = (obt / max) * 100;
        if (pct >= 90) return 'A+';
        if (pct >= 80) return 'A';
        if (pct >= 70) return 'B';
        if (pct >= 60) return 'C';
        if (pct >= 50) return 'D';
        return 'F';
    }
}" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-white">Bulk Excel Mark Entry Kiosk</h2>
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold uppercase tracking-wide bg-brand-500/20 text-brand-400 border border-brand-500/30">Excel Grid Active</span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Enter or paste student marks across all subjects in a spreadsheet layout</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('exams') ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                ← Back to List
            </a>
            <button form="bulk-exam-form" type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save All Marks
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="<?= url('exams/bulk-entry') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5 col-span-2">
                <label class="block text-xs font-medium text-slate-400">Class & Section</label>
                <select name="class_section" required onchange="
                    const val = this.value.split('|');
                    document.getElementById('bulk_class_input').value = val[0] || '';
                    document.getElementById('bulk_section_input').value = val[1] || '';
                " class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <?php foreach ($classes as $c): ?>
                        <?php 
                            $optionVal = $c['class'] . '|' . $c['section'];
                            $selected = ($selectedClass === $c['class'] && $selectedSection === $c['section']) ? 'selected' : '';
                        ?>
                        <option value="<?= $optionVal ?>" <?= $selected ?>><?= e($c['class']) ?> - <?= e($c['section'] ?: 'Default') ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="class" id="bulk_class_input" value="<?= e($selectedClass) ?>">
                <input type="hidden" name="section" id="bulk_section_input" value="<?= e($selectedSection) ?>">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Term / Evaluation</label>
                <input type="text" name="term" value="<?= e($selectedTerm) ?>" required placeholder="e.g. First Term Evaluation" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all bg-slate-800 hover:bg-slate-750 border border-slate-700/50 shadow-md">
                    Load Grid
                </button>
            </div>
        </form>
    </div>

    <!-- Excel Mark Entry Grid -->
    <form id="bulk-exam-form" method="POST" action="<?= url('exams/bulk-save') ?>" class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
        <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
        <input type="hidden" name="term" value="<?= e($selectedTerm) ?>">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <!-- Category Header Row -->
                    <tr class="border-b border-slate-800 bg-slate-900/90 text-center text-[10px] font-bold text-indigo-400 uppercase tracking-widest">
                        <th class="px-5 py-2 sticky left-0 bg-slate-900 z-10 border-r border-slate-800 text-left">Category</th>
                        <?php 
                        // Group subjects by category
                        $groupedSubjects = [];
                        foreach ($subjects as $subj) {
                            $cat = $subj['category'] ?? 'Academic';
                            $groupedSubjects[$cat][] = $subj;
                        }
                        
                        // Reconstruct subjects array to match category ordering
                        $orderedSubjects = [];
                        foreach ($groupedSubjects as $catName => $subjs) {
                            $colspan = count($subjs);
                            $orderedSubjects = array_merge($orderedSubjects, $subjs);
                            echo '<th colspan="' . $colspan . '" class="px-2 py-2 border-r border-slate-800/60">' . e($catName) . '</th>';
                        }
                        // Set subjects back to ordered
                        $subjects = $orderedSubjects;
                        ?>
                        <th class="px-4 py-2 text-slate-400">General</th>
                    </tr>
                    
                    <!-- Subject Names Row -->
                    <tr class="border-b border-slate-850 bg-slate-900/60">
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider sticky left-0 bg-slate-900 z-10 border-r border-slate-800">Student Name</th>
                        <?php foreach ($subjects as $subj): ?>
                        <th class="px-4 py-3 text-xs font-bold text-center text-slate-300 uppercase tracking-wider min-w-[130px] border-r border-slate-800/60">
                            <div class="truncate" title="<?= e($subj['name']) ?>"><?= e($subj['name']) ?></div>
                            <span class="text-[9px] font-mono text-slate-500"><?= e($subj['code']) ?> (Max: 100)</span>
                        </th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 text-xs font-bold text-center text-slate-400 uppercase tracking-wider min-w-[150px]">Teacher Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="<?= count($subjects) + 2 ?>" class="px-6 py-12 text-center text-slate-500">
                            No enrolled students found for selected class.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($students as $stuIdx => $stu): ?>
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <!-- Student Info -->
                            <td class="px-5 py-3.5 whitespace-nowrap sticky left-0 bg-slate-900/90 z-10 border-r border-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($stu['first_name'], 0, 1) . (isset($stu['last_name'][0]) ? substr($stu['last_name'], 0, 1) : 'S')) ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-white"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></p>
                                        <p class="text-[10px] text-slate-500 font-mono"><?= e($stu['admission_number']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Subject Cells -->
                            <?php foreach ($subjects as $subjIdx => $subj): ?>
                                <?php 
                                    $existingVal = $existingMarks[$stu['id']][$subj['name']]['marks_obtained'] ?? '';
                                ?>
                                <td class="p-1 border-r border-slate-800/60 text-center">
                                    <input type="number" 
                                           step="0.01" 
                                           max="100"
                                           name="marks[<?= $stu['id'] ?>][<?= e($subj['name']) ?>]" 
                                           value="<?= e($existingVal) ?>"
                                           placeholder="-" 
                                           class="w-full bg-slate-950/60 focus:bg-slate-950 border border-transparent focus:border-brand-500 text-slate-200 font-mono text-xs text-center py-2 px-2 rounded-lg transition-all focus:outline-none"
                                           @keydown.down.prevent="$event.target.closest('tr').nextElementSibling?.querySelectorAll('input')[<?= $subjIdx ?>]?.focus()"
                                           @keydown.up.prevent="$event.target.closest('tr').previousElementSibling?.querySelectorAll('input')[<?= $subjIdx ?>]?.focus()"
                                    >
                                </td>
                            <?php endforeach; ?>

                            <!-- Remarks Cell -->
                            <td class="p-1 text-center">
                                <input type="text" 
                                       name="remarks[<?= $stu['id'] ?>]" 
                                       value="<?= e($existingMarks[$stu['id']][$subjects[0]['name']]['remarks'] ?? '') ?>"
                                       placeholder="Remarks..." 
                                       class="w-full bg-slate-950/60 focus:bg-slate-950 border border-transparent focus:border-brand-500 text-slate-300 text-xs py-2 px-3 rounded-lg transition-all focus:outline-none">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($students)): ?>
        <div class="p-4 bg-slate-900/60 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">Tip: Use <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-350 border border-slate-700 font-mono">Arrow Up / Down</kbd> keys to navigate vertically through cells like Excel.</span>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 transition-all shadow-md">
                Save Grade Grid
            </button>
        </div>
        <?php endif; ?>
    </form>

</div>

<?php
$content = ob_get_clean();
?>
