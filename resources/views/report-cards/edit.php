<?php
$layout    = 'app';
$pageTitle = 'Edit Student Report Card';
$name = $student['first_name'] . ' ' . $student['last_name'];
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/academics'],
    ['label' => 'Students', 'url' => '/academics/students'],
    ['label' => $name, 'url' => '/academics/students/' . $student['id']],
    ['label' => 'Report Card']
];
ob_start();

$scores = $reportCard['academic_profile'] ?? [];
if (is_string($scores)) {
    $scores = json_decode($scores, true) ?: [];
}

$activeExams = \Core\Application::$app->db->select(
    "SELECT name, max_marks FROM exams WHERE tenant_id = ? AND semester = ? ORDER BY id ASC",
    [$student['tenant_id'], $semester]
);
?>
<?php $isLocked = is_year_locked($academicYear); ?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{ activeTab: 'sec_<?= !empty($curriculumTree) ? $curriculumTree[0]['id'] : 'summary' ?>' }">

    <!-- Profile Header -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <?php if ($isLocked): ?>
            <div class="w-full mb-2 p-3.5 rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-500 text-xs font-bold flex items-center gap-2">
                <span>🔒</span>
                <span>This academic year is locked. Report card updates are frozen. (Only administrators can modify report cards.)</span>
            </div>
        <?php endif; ?>
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Progress Report Card Editor</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Student: <span class="font-extrabold text-slate-800 dark:text-slate-200"><?= e($name) ?></span> | 
                Academic Year: <span class="font-bold text-slate-800 dark:text-slate-200"><?= e($academicYear) ?></span>
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="<?= url('academics/students/' . $student['id'] . '/report-card/view?semester=' . urlencode($semester) . '&academic_year=' . urlencode($academicYear)) ?>" 
               target="_blank"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all border border-emerald-200">
                Printable View
            </a>
            <a href="<?= url('academics/students/' . $student['id']) ?>" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                Back to Profile
            </a>
        </div>
    </div>

    <!-- Parameter Context Selector Form -->
    <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end text-xs">
            <div class="space-y-1">
                <label class="block font-bold text-slate-400 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                    <option value="2026-27">2026-27</option>
                </select>
            </div>
            
            <div class="space-y-1">
                <label class="block font-bold text-slate-400 uppercase tracking-wider">Semester</label>
                <select name="semester" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                    <option value="Semester 1" <?= $semester === 'Semester 1' ? 'selected' : '' ?>>Semester 1</option>
                    <option value="Semester 2" <?= $semester === 'Semester 2' ? 'selected' : '' ?>>Semester 2</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-white shadow transition-all">
                    Load Saved Scores
                </button>
            </div>
        </form>
    </div>

    <!-- Dynamic Entry Form -->
    <form method="POST" action="<?= url('academics/students/' . $student['id'] . '/report-card') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <input type="hidden" name="semester" value="<?= e($semester) ?>">

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-1 text-xs">
            <?php foreach ($curriculumTree as $sec): ?>
                <button type="button" @click="activeTab = 'sec_<?= $sec['id'] ?>'" 
                        :class="activeTab === 'sec_<?= $sec['id'] ?>' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" 
                        class="px-4 py-2 font-medium transition-all whitespace-nowrap">
                    <?= e($sec['section_name']) ?>
                </button>
            <?php endforeach; ?>
            <button type="button" @click="activeTab = 'summary'" 
                    :class="activeTab === 'summary' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" 
                    class="px-4 py-2 font-medium transition-all whitespace-nowrap">
                Attendance & Comments
            </button>
        </div>

        <!-- Section Content Panels -->
        <?php foreach ($curriculumTree as $sec): ?>
            <div x-show="activeTab === 'sec_<?= $sec['id'] ?>'" class="space-y-4">
                <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white"><?= e($sec['section_name']) ?> Evaluation</h3>
                    
                    <div class="space-y-4 text-xs">
                        <?php foreach ($sec['subjects'] as $cs): ?>
                            <?php 
                                $val = $scores[$cs['subject_id']] ?? '';
                            ?>
                            
                            <?php if ($cs['assessment_type'] === 'Marks'): ?>
                                <?php 
                                    if (!is_array($val)) {
                                        $val = ['marks' => []];
                                    }
                                ?>
                                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                     x-data="{
                                         scores: {
                                             <?php foreach ($activeExams as $exam) {
                                                 $valObt = $val['marks'][$exam['name']] ?? '';
                                                 echo "'" . e($exam['name']) . "': " . ($valObt !== '' ? (float)$valObt : "''") . ", ";
                                             } ?>
                                         },
                                         maxMarks: {
                                             <?php foreach ($activeExams as $exam) {
                                                 echo "'" . e($exam['name']) . "': " . (float)$exam['max_marks'] . ", ";
                                             } ?>
                                         },
                                         sumScores() {
                                             return Object.values(this.scores).reduce((a, b) => (parseFloat(a) || 0) + (parseFloat(b) || 0), 0);
                                         },
                                         sumMax() {
                                             return Object.values(this.maxMarks).reduce((a, b) => (parseFloat(a) || 0) + (parseFloat(b) || 0), 0);
                                         },
                                         calculateGrade(obt, max) {
                                             if (!max || max == 0) return 'F';
                                             let pct = (obt / max) * 100;
                                             if (pct >= 90) return 'A+';
                                             if (pct >= 80) return 'A';
                                             if (pct >= 70) return 'B';
                                             if (pct >= 60) return 'C+';
                                             if (pct >= 41) return 'C';
                                             if (pct >= 33) return 'D';
                                             return 'F';
                                         }
                                     }"
                                >
                                    <!-- Name -->
                                    <div class="space-y-0.5">
                                        <span class="font-black text-slate-850 dark:text-white text-xs"><?= e($cs['subject_name']) ?></span>
                                        <span class="text-3xs block text-slate-400 font-mono">Code: <?= e($cs['subject_code']) ?> &middot; Type: <?= e($cs['assessment_type']) ?></span>
                                    </div>

                                    <!-- Component Inputs and Dynamic Calculations -->
                                    <div class="sm:w-2/3 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <?php foreach ($activeExams as $exam): ?>
                                                <div class="space-y-1">
                                                    <label class="block text-[10px] text-slate-400 font-bold"><?= e($exam['name']) ?> (Max <?= number_format((float)$exam['max_marks'], 0) ?>)</label>
                                                    <input type="number" 
                                                           step="0.01" 
                                                           max="<?= (float)$exam['max_marks'] ?>"
                                                           name="academic_profile[<?= $cs['subject_id'] ?>][marks][<?= e($exam['name']) ?>]"
                                                           x-model.number="scores['<?= e($exam['name']) ?>']"
                                                           placeholder="-" 
                                                           <?= $isLocked ? 'disabled' : '' ?>
                                                           class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 font-mono text-center text-xs focus:outline-none focus:border-indigo-500">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Sum of scores -->
                                        <div class="flex items-center gap-4 text-center font-mono text-xs">
                                            <div>
                                                <span class="block text-[9px] text-slate-450 uppercase font-sans font-bold">Total</span>
                                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="sumScores().toFixed(1) + ' / ' + sumMax().toFixed(1)"></span>
                                                <input type="hidden" name="academic_profile[<?= $cs['subject_id'] ?>][total]" :value="sumScores().toFixed(2)">
                                                <input type="hidden" name="academic_profile[<?= $cs['subject_id'] ?>][pct]" :value="sumMax() > 0 ? (sumScores() / sumMax() * 100).toFixed(2) : '0.00'">
                                                <input type="hidden" name="academic_profile[<?= $cs['subject_id'] ?>][grade]" :value="calculateGrade(sumScores(), sumMax())">
                                            </div>
                                            <div>
                                                <span class="block text-[9px] text-slate-450 uppercase font-sans font-bold">Grade</span>
                                                <span x-text="calculateGrade(sumScores(), sumMax())" 
                                                      class="px-2 py-0.5 rounded bg-indigo-950/40 text-indigo-400 border border-indigo-900/30 font-bold">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-0.5">
                                        <span class="font-black text-slate-850 dark:text-white text-xs"><?= e($cs['subject_name']) ?></span>
                                        <span class="text-3xs block text-slate-400 font-mono">Code: <?= e($cs['subject_code']) ?> &middot; Type: <?= e($cs['assessment_type']) ?></span>
                                    </div>

                                    <div class="sm:w-2/3">
                                        <?php if ($cs['assessment_type'] === 'Grade'): ?>
                                            <select name="academic_profile[<?= $cs['subject_id'] ?>]" <?= $isLocked ? 'disabled' : '' ?> class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950">
                                                <option value="">-- Choose Grade --</option>
                                                <?php foreach (['A+', 'A', 'B', 'C+', 'C', 'D', 'F'] as $gOption): ?>
                                                    <option value="<?= $gOption ?>" <?= $val === $gOption ? 'selected' : '' ?>><?= $gOption ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: // Rating ?>
                                            <div class="flex flex-wrap items-center gap-3">
                                                <?php foreach (['A' => 'Excellent (A)', 'B' => 'Good (B)', 'C' => 'Needs Improvement (C)', 'R' => 'Refused (R)', 'N/A' => 'N/A'] as $rKey => $rLbl): ?>
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="radio" name="academic_profile[<?= $cs['subject_id'] ?>]" value="<?= $rKey ?>" <?= $val === $rKey ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
                                                        <span class="text-3xs font-bold text-slate-600 dark:text-slate-400"><?= $rLbl ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Summary & Signatures Tab -->
        <div x-show="activeTab === 'summary'" class="space-y-4" x-cloak>
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Report Card Summary & Signatures</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 dark:text-slate-350">General Comments / Teacher Feedback</label>
                        <textarea name="feedback_text" rows="4" placeholder="Type term feedback..." <?= $isLocked ? 'disabled' : '' ?> class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950"><?= e($reportCard['feedback_text'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-700 dark:text-slate-350">Total Days</label>
                            <input type="number" name="attendance_profile[total_days]" value="<?= e($reportCard['attendance_profile']['total_days'] ?? $attendanceAuto['total_days'] ?? '') ?>" <?= $isLocked ? 'disabled' : '' ?> class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950">
                            <?php if (empty($reportCard['attendance_profile']['total_days']) && !empty($attendanceAuto['total_days'])): ?>
                                <p class="text-2xs text-indigo-500 mt-0.5">Auto from semester settings</p>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-700 dark:text-slate-350">Days Present</label>
                            <input type="number" name="attendance_profile[days_present]" value="<?= e($reportCard['attendance_profile']['days_present'] ?? $attendanceAuto['days_present'] ?? '') ?>" <?= $isLocked ? 'disabled' : '' ?> class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950">
                            <?php if (empty($reportCard['attendance_profile']['days_present']) && !empty($attendanceAuto['days_present'])): ?>
                                <p class="text-2xs text-indigo-500 mt-0.5">Auto from attendance records</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <?php if (!$isLocked): ?>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-white shadow-md transition-all">
                            Save Report Card
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </form>

</div>

<?php
$content = ob_get_clean();
?>
