<?php
$layout    = 'app';
$pageTitle = 'Exams & Bulk Grade Kiosk';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Exams', 'url' => '/academics/exams'], ['label' => 'Bulk Entry']];
ob_start();
?>
<?php
$selectedYearName = '';
foreach ($years as $y) {
    if ($y['id'] == $selectedYearId) {
        $selectedYearName = $y['year_name'];
        break;
    }
}
$isLocked = is_year_locked($selectedYearName ?: $selectedYearId);
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-white font-mono tracking-tight">Bulk Evaluation entry Kiosk</h2>
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold uppercase tracking-wide bg-brand-500/20 text-brand-400 border border-brand-500/30">Spreadsheet Active</span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Enter grades or marks component scores for all subjects under the selected exam</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('academics/exams') ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                ← Back to List
            </a>
            <?php if (!$isLocked): ?>
                <button form="bulk-exam-form" type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save All Marks
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isLocked): ?>
        <div class="p-4 rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-500 text-xs font-semibold flex items-center gap-2">
            <span>🔒</span>
            <span>This academic year is locked. Marks entry is frozen. (Only administrators can modify marks.)</span>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Academic Year</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all font-semibold">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y['id'] ?>" <?= $y['id'] == $selectedYearId ? 'selected' : '' ?>><?= e($y['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Main Group</label>
                <select name="main_group_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all font-semibold">
                    <?php foreach ($mainGroups as $mg): ?>
                        <option value="<?= $mg['id'] ?>" <?= (int)$mg['id'] === $selectedMainGroupId ? 'selected' : '' ?>><?= e($mg['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Class & Section</label>
                <select name="class_section" required onchange="
                    const val = this.value.split('|');
                    document.getElementById('bulk_class_input').value = val[0] || '';
                    document.getElementById('bulk_section_input').value = val[1] || '';
                    this.form.submit();
                " class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <?php foreach ($classes as $c): ?>
                        <?php 
                            $cName = $c['class'] ?? $c['name'] ?? '';
                            $optionVal = $cName . '|' . ($c['section'] ?? '');
                            $selected = ($selectedClass === $cName && $selectedSection === ($c['section'] ?? '')) ? 'selected' : '';
                        ?>
                        <option value="<?= $optionVal ?>" <?= $selected ?>><?= e($cName) ?> - <?= e(($c['section'] ?? '') ?: 'Default') ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="class" id="bulk_class_input" value="<?= e($selectedClass) ?>">
                <input type="hidden" name="section" id="bulk_section_input" value="<?= e($selectedSection) ?>">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Exam / Evaluation Component</label>
                <select name="exam_name" required onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all font-bold">
                    <?php foreach ($examsList as $ex): ?>
                        <option value="<?= e($ex['name']) ?>" <?= $selectedExamName === $ex['name'] ? 'selected' : '' ?>><?= e($ex['name']) ?> (<?= e($ex['semester']) ?> - Max <?= number_format((float)$ex['max_marks'], 0) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Excel Mark Entry Grid -->
    <?php if (empty($classes)): ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center">
            <p class="text-slate-500 text-sm">No classes found for the selected Main Group. Please create classes and assign them to this group.</p>
        </div>
    <?php else: ?>
    <form id="bulk-exam-form" method="POST" action="<?= url('academics/assessments/bulk-save') ?>" class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
        <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
        <input type="hidden" name="exam_name" value="<?= e($selectedExamName) ?>">
        <input type="hidden" name="term" value="<?= e($selectedTerm) ?>">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-850 bg-slate-900/60">
                        <th class="px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider sticky left-0 bg-slate-900 z-10 border-r border-slate-800">Student Name</th>
                        
                        <?php foreach ($subjects as $subj): ?>
                            <th class="px-4 py-3 text-xs font-bold text-center text-slate-300 uppercase tracking-wider min-w-[140px] border-r border-slate-800/60">
                                <?= e($subj['name']) ?>
                                <?php if ($subj['assessment_type'] === 'Marks'): ?>
                                    <span class="text-[9px] font-mono text-slate-500 block">Max: <?= $selectedExam ? number_format((float)$selectedExam['max_marks'], 1) : '-' ?></span>
                                <?php else: ?>
                                    <span class="text-[9px] font-mono text-indigo-400 block">Grade-based</span>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                        
                        <th class="px-4 py-3 text-xs font-bold text-center text-slate-400 uppercase tracking-wider min-w-[160px]">Teacher Remarks</th>
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
                        <?php foreach ($students as $stu): ?>
                            <?php 
                                $firstSubKey = !empty($subjects) ? strtolower(trim($subjects[0]['name'])) : '';
                                $stuRemark = $existingMarks[$stu['id']][$firstSubKey]['remarks'] ?? '';
                            ?>
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

                                <!-- Subject Columns -->
                                <?php foreach ($subjects as $subjIdx => $subj): ?>
                                    <?php 
                                        $subKey = strtolower(trim($subj['name']));
                                    ?>
                                    <td class="p-1 border-r border-slate-800/60 text-center">
                                        <?php if ($subj['assessment_type'] === 'Marks'): ?>
                                            <?php 
                                                $existingMark = $existingMarks[$stu['id']][$subKey]['marks_obtained'] ?? '';
                                            ?>
                                             <input type="number" 
                                                    step="0.01" 
                                                    max="<?= $selectedExam ? (float)$selectedExam['max_marks'] : 100 ?>"
                                                    name="marks[<?= $stu['id'] ?>][<?= $subj['id'] ?>]" 
                                                    value="<?= e($existingMark) ?>"
                                                    placeholder="-" 
                                                    <?= $isLocked ? 'disabled' : '' ?>
                                                    class="cell-input w-full bg-slate-950/60 focus:bg-slate-950 border border-transparent focus:border-brand-500 text-slate-200 font-mono text-xs text-center py-2 px-2 rounded-lg transition-all focus:outline-none"
                                                    @keydown.down.prevent="$event.target.closest('tr').nextElementSibling?.querySelectorAll('.cell-input')[<?= $subjIdx ?>]?.focus()"
                                                    @keydown.up.prevent="$event.target.closest('tr').previousElementSibling?.querySelectorAll('.cell-input')[<?= $subjIdx ?>]?.focus()"
                                             >
                                        <?php else: ?>
                                            <?php 
                                                $existingGrade = $existingMarks[$stu['id']][$subKey]['grade'] ?? '';
                                            ?>
                                             <select name="marks[<?= $stu['id'] ?>][<?= $subj['id'] ?>]" 
                                                     <?= $isLocked ? 'disabled' : '' ?>
                                                     class="cell-input w-full bg-slate-950/60 focus:bg-slate-950 border border-transparent focus:border-brand-500 text-slate-200 text-xs py-2 px-3 rounded-lg focus:outline-none"
                                                     @keydown.down.prevent="$event.target.closest('tr').nextElementSibling?.querySelectorAll('.cell-input')[<?= $subjIdx ?>]?.focus()"
                                                     @keydown.up.prevent="$event.target.closest('tr').previousElementSibling?.querySelectorAll('.cell-input')[<?= $subjIdx ?>]?.focus()"
                                             >
                                                 <option value="">- Select -</option>
                                                <option value="A+" <?= $existingGrade === 'A+' ? 'selected' : '' ?>>A+</option>
                                                <option value="A" <?= $existingGrade === 'A' ? 'selected' : '' ?>>A</option>
                                                <option value="B" <?= $existingGrade === 'B' ? 'selected' : '' ?>>B</option>
                                                <option value="C+" <?= $existingGrade === 'C+' ? 'selected' : '' ?>>C+</option>
                                                <option value="C" <?= $existingGrade === 'C' ? 'selected' : '' ?>>C</option>
                                                <option value="D" <?= $existingGrade === 'D' ? 'selected' : '' ?>>D</option>
                                                <option value="F" <?= $existingGrade === 'F' ? 'selected' : '' ?>>F</option>
                                                <option value="R" <?= $existingGrade === 'R' ? 'selected' : '' ?>>R</option>
                                                <option value="N/A" <?= $existingGrade === 'N/A' ? 'selected' : '' ?>>N/A</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Remarks Cell -->
                                <td class="p-1 text-center font-mono">
                                     <input type="text" 
                                            name="remarks[<?= $stu['id'] ?>]" 
                                            value="<?= e($stuRemark) ?>"
                                            placeholder="Remarks..." 
                                            <?= $isLocked ? 'disabled' : '' ?>
                                            class="cell-input w-full bg-slate-950/60 focus:bg-slate-950 border border-transparent focus:border-brand-500 text-slate-350 text-xs py-2 px-3 rounded-lg transition-all focus:outline-none"
                                            @keydown.down.prevent="$event.target.closest('tr').nextElementSibling?.querySelectorAll('.cell-input')[<?= count($subjects) ?>]?.focus()"
                                            @keydown.up.prevent="$event.target.closest('tr').previousElementSibling?.querySelectorAll('.cell-input')[<?= count($subjects) ?>]?.focus()"
                                     >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($students)): ?>
        <div class="p-4 bg-slate-900/60 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">Tip: Use <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-350 border border-slate-700 font-mono">Arrow Up / Down</kbd> keys inside entry fields to navigate vertically through spreadsheet cells.</span>
            <?php if (!$isLocked): ?>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 transition-all shadow-md">
                    Save Grade Grid
                </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </form>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
