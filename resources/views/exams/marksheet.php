<?php
$layout    = 'app';
$pageTitle = 'Marksheet';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Exams', 'url' => '/academics/exams'], ['label' => 'Marksheet']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Marksheet</h2>
            <p class="text-sm text-slate-500 mt-0.5">Semester-wise marks summary for all subjects</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('academics/exams') ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                ← Back to Exams
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="" id="marksheetFilterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
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
                        document.getElementById('ms_class_input').value = val[0] || '';
                        document.getElementById('ms_section_input').value = val[1] || '';
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
                    <input type="hidden" name="class" id="ms_class_input" value="<?= e($selectedClass) ?>">
                    <input type="hidden" name="section" id="ms_section_input" value="<?= e($selectedSection) ?>">
                </div>

                <div class="space-y-1.5">
                    <button type="button" onclick="window.print()" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 transition-all shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Marksheet
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Marksheet Tables -->
    <?php if (empty($classes)): ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center">
            <p class="text-slate-500 text-sm">No classes found for the selected Main Group.</p>
        </div>
    <?php elseif (empty($result)): ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center">
            <p class="text-slate-500 text-sm">No students found for the selected class.</p>
        </div>
    <?php else: ?>
        <?php foreach ($result as $entry): ?>
            <?php $stu = $entry['student']; $subjects = $entry['subjects']; ?>
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
                <!-- Student Header -->
                <div class="px-6 py-4 bg-slate-900/50 border-b border-slate-800 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        <?= strtoupper(substr($stu['first_name'], 0, 1) . (isset($stu['last_name'][0]) ? substr($stu['last_name'], 0, 1) : 'S')) ?>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></h3>
                        <p class="text-xs text-slate-500 font-mono">Admission: <?= e($stu['admission_number']) ?></p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 bg-slate-900/30">
                                <th rowspan="2" class="px-4 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider border-r border-slate-800 sticky left-0 bg-slate-900/90 z-10">Subject</th>
                                <th colspan="<?= count($semester1Exams) + 2 ?>" class="px-4 py-2 text-xs font-bold text-center text-emerald-400 uppercase tracking-wider border-r border-slate-800 bg-emerald-950/20">First Semester</th>
                                <th colspan="<?= count($semester2Exams) + 2 ?>" class="px-4 py-2 text-xs font-bold text-center text-amber-400 uppercase tracking-wider border-r border-slate-800 bg-amber-950/20">Second Semester</th>
                            </tr>
                            <tr class="border-b border-slate-800 bg-slate-900/30">
                                <?php foreach ($semester1Exams as $ex): ?>
                                    <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">
                                        <?= e($ex['name']) ?><br><span class="text-slate-600">MAX-<?= number_format((float)$ex['max_marks'], 0) ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">Total</th>
                                <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">Grade</th>
                                <?php foreach ($semester2Exams as $ex): ?>
                                    <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">
                                        <?= e($ex['name']) ?><br><span class="text-slate-600">MAX-<?= number_format((float)$ex['max_marks'], 0) ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">Total</th>
                                <th class="px-3 py-2 text-[10px] font-semibold text-slate-500 text-center border-r border-slate-800/60">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850">
                            <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="<?= count($semester1Exams) + count($semester2Exams) + 4 ?>" class="px-6 py-8 text-center text-slate-500 text-xs">No subjects configured.</td>
                            </tr>
                            <?php else: ?>
                                <?php 
                                    $grandSem1Total = 0;
                                    $grandSem1Max = 0;
                                    $grandSem2Total = 0;
                                    $grandSem2Max = 0;
                                ?>
                                <?php foreach ($subjects as $subj): ?>
                                    <?php
                                        $grandSem1Total += $subj['sem1_total'] ?? 0;
                                        $grandSem1Max += $subj['sem1_max'] ?? 0;
                                        $grandSem2Total += $subj['sem2_total'] ?? 0;
                                        $grandSem2Max += $subj['sem2_max'] ?? 0;
                                    ?>
                                    <tr class="hover:bg-slate-900/30 transition-colors">
                                        <td class="px-4 py-3 text-xs font-semibold text-white border-r border-slate-800 sticky left-0 bg-slate-900/90 z-10">
                                            <?= e($subj['name']) ?>
                                        </td>
                                        <!-- Semester 1 -->
                                        <?php foreach ($semester1Exams as $ex): ?>
                                            <?php
                                                $exName = $ex['name'];
                                                $val = null;
                                                if (stripos($exName, 'unit test') !== false) $val = $subj['sem1_ut'];
                                                elseif (stripos($exName, 'project') !== false || stripos($exName, 'practical') !== false) $val = $subj['sem1_proj'];
                                                else $val = $subj['sem1_exam'];
                                            ?>
                                            <td class="px-3 py-3 text-center text-xs font-mono text-slate-300 border-r border-slate-800/60">
                                                <?= $val !== null && $val !== '' ? number_format((float)$val, 1) : '-' ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-3 py-3 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                            <?= $subj['sem1_total'] !== null ? number_format((float)$subj['sem1_total'], 1) : '-' ?>
                                            <?php if ($subj['sem1_max'] !== null): ?>
                                                <span class="text-[9px] text-slate-600">/<?= number_format((float)$subj['sem1_max'], 0) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-center border-r border-slate-800/60">
                                            <?php if ($subj['sem1_grade'] !== null): ?>
                                                <?php
                                                    $gradeColors = [
                                                        'A+' => 'bg-emerald-950/50 text-emerald-400 border-emerald-900/30',
                                                        'A'  => 'bg-emerald-950/40 text-emerald-400 border-emerald-900/30',
                                                        'B'  => 'bg-blue-950/40 text-blue-400 border-blue-900/30',
                                                        'C+' => 'bg-cyan-950/40 text-cyan-400 border-cyan-900/30',
                                                        'C'  => 'bg-amber-950/40 text-amber-400 border-amber-900/30',
                                                        'D'  => 'bg-orange-950/40 text-orange-400 border-orange-900/30',
                                                        'F'  => 'bg-red-950/40 text-red-400 border-red-900/30',
                                                    ];
                                                    $gc = $gradeColors[$subj['sem1_grade']] ?? 'bg-slate-800 text-slate-400 border-slate-700';
                                                ?>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border <?= $gc ?>"><?= e($subj['sem1_grade']) ?></span>
                                            <?php else: ?>
                                                <span class="text-[10px] text-slate-600">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Semester 2 -->
                                        <?php foreach ($semester2Exams as $ex): ?>
                                            <?php
                                                $exName = $ex['name'];
                                                $val = null;
                                                if (stripos($exName, 'unit test') !== false) $val = $subj['sem2_ut'];
                                                elseif (stripos($exName, 'project') !== false || stripos($exName, 'practical') !== false) $val = $subj['sem2_proj'];
                                                else $val = $subj['sem2_exam'];
                                            ?>
                                            <td class="px-3 py-3 text-center text-xs font-mono text-slate-300 border-r border-slate-800/60">
                                                <?= $val !== null && $val !== '' ? number_format((float)$val, 1) : '-' ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-3 py-3 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                            <?= $subj['sem2_total'] !== null ? number_format((float)$subj['sem2_total'], 1) : '-' ?>
                                            <?php if ($subj['sem2_max'] !== null): ?>
                                                <span class="text-[9px] text-slate-600">/<?= number_format((float)$subj['sem2_max'], 0) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-center border-r border-slate-800/60">
                                            <?php if ($subj['sem2_grade'] !== null): ?>
                                                <?php
                                                    $gc = $gradeColors[$subj['sem2_grade']] ?? 'bg-slate-800 text-slate-400 border-slate-700';
                                                ?>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border <?= $gc ?>"><?= e($subj['sem2_grade']) ?></span>
                                            <?php else: ?>
                                                <span class="text-[10px] text-slate-600">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Grand Total Row -->
                                <tr class="bg-slate-900/40 border-t-2 border-slate-700">
                                    <td class="px-4 py-3 text-xs font-bold text-white border-r border-slate-800 sticky left-0 bg-slate-900/90 z-10">TOTAL</td>
                                    <td colspan="<?= count($semester1Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-3 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                        <?= $grandSem1Max > 0 ? number_format($grandSem1Total, 1) . ' / ' . $grandSem1Max : '-' ?>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                    <td colspan="<?= count($semester2Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-3 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                        <?= $grandSem2Max > 0 ? number_format($grandSem2Total, 1) . ' / ' . $grandSem2Max : '-' ?>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                </tr>

                                <!-- Grand Percentage & Grade -->
                                <tr class="bg-slate-900/30">
                                    <td class="px-4 py-2 text-xs font-bold text-slate-400 border-r border-slate-800 sticky left-0 bg-slate-900/90 z-10">PERCENTAGE</td>
                                    <td colspan="<?= count($semester1Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-2 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                        <?= $grandSem1Max > 0 ? number_format(($grandSem1Total / $grandSem1Max) * 100, 2) . '%' : '-' ?>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                    <td colspan="<?= count($semester2Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-2 text-center text-xs font-mono font-bold text-white border-r border-slate-800/60">
                                        <?= $grandSem2Max > 0 ? number_format(($grandSem2Total / $grandSem2Max) * 100, 2) . '%' : '-' ?>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                </tr>
                                <tr class="bg-slate-900/30">
                                    <td class="px-4 py-2 text-xs font-bold text-slate-400 border-r border-slate-800 sticky left-0 bg-slate-900/90 z-10">GRADE</td>
                                    <td colspan="<?= count($semester1Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-2 text-center border-r border-slate-800/60">
                                        <?php
                                            $sem1Pct = $grandSem1Max > 0 ? ($grandSem1Total / $grandSem1Max) * 100 : 0;
                                            $sem1Grade = '-';
                                            if ($grandSem1Max > 0) {
                                                if ($sem1Pct >= 90) $sem1Grade = 'A+';
                                                elseif ($sem1Pct >= 80) $sem1Grade = 'A';
                                                elseif ($sem1Pct >= 70) $sem1Grade = 'B';
                                                elseif ($sem1Pct >= 60) $sem1Grade = 'C+';
                                                elseif ($sem1Pct >= 41) $sem1Grade = 'C';
                                                elseif ($sem1Pct >= 33) $sem1Grade = 'D';
                                                else $sem1Grade = 'F';
                                            }
                                            $gc = $gradeColors[$sem1Grade] ?? 'bg-slate-800 text-slate-400 border-slate-700';
                                        ?>
                                        <span class="text-xs font-bold px-3 py-1 rounded border <?= $gc ?>"><?= $sem1Grade ?></span>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                    <td colspan="<?= count($semester2Exams) ?>" class="border-r border-slate-800/60"></td>
                                    <td class="px-3 py-2 text-center border-r border-slate-800/60">
                                        <?php
                                            $sem2Pct = $grandSem2Max > 0 ? ($grandSem2Total / $grandSem2Max) * 100 : 0;
                                            $sem2Grade = '-';
                                            if ($grandSem2Max > 0) {
                                                if ($sem2Pct >= 90) $sem2Grade = 'A+';
                                                elseif ($sem2Pct >= 80) $sem2Grade = 'A';
                                                elseif ($sem2Pct >= 70) $sem2Grade = 'B';
                                                elseif ($sem2Pct >= 60) $sem2Grade = 'C+';
                                                elseif ($sem2Pct >= 41) $sem2Grade = 'C';
                                                elseif ($sem2Pct >= 33) $sem2Grade = 'D';
                                                else $sem2Grade = 'F';
                                            }
                                            $gc = $gradeColors[$sem2Grade] ?? 'bg-slate-800 text-slate-400 border-slate-700';
                                        ?>
                                        <span class="text-xs font-bold px-3 py-1 rounded border <?= $gc ?>"><?= $sem2Grade ?></span>
                                    </td>
                                    <td class="border-r border-slate-800/60"></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .rounded-2xl { border-radius: 0 !important; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
    }
</style>

<?php
$content = ob_get_clean();
?>
