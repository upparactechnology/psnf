<?php
$layout    = 'app';
$pageTitle = 'Edit Student Report Card';
$name = $student['first_name'] . ' ' . $student['last_name'];
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Students', 'url' => '/students'],
    ['label' => $name, 'url' => '/students/' . $student['id']],
    ['label' => 'Report Card']
];
ob_start();

// Helper to get rating value safely
$getVal = function($arr, $key, $default = '') {
    return $arr[$key] ?? $default;
};

// Fetch dynamic fields configuration
$fieldsConfig = json_decode($settings['fields_config'] ?? '[]', true) ?: [];
$fields = $fieldsConfig[$academicYear][$semester] ?? \App\Controllers\ReportCardController::getDefaultFields();

$routineParams = $fields['routine'] ?? [];
$skillParams = $fields['skills'] ?? [];
$subjects = $fields['academics'] ?? [];
$activities = $fields['cocurricular'] ?? [];
?>

<div class="max-w-6xl mx-auto" x-data="reportCardForm()">
    <!-- Profile Header -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Progress Report Card Editor</h2>
                <p class="text-sm text-slate-550 dark:text-slate-400">
                    Student: <span class="text-slate-900 dark:text-white font-semibold"><?= e($name) ?></span> | 
                    Class: <span class="text-slate-800 dark:text-white"><?= e($student['class'] ?? '—') ?></span> (<?= e($student['section'] ?? '—') ?>) | 
                    GR Number: <span class="text-slate-800 dark:text-white font-mono"><?= e($student['gr_number'] ?? '—') ?></span>
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="<?= url('students/' . $student['id'] . '/report-card/view?semester=' . urlencode($semester) . '&academic_year=' . urlencode($academicYear)) ?>" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30 transition-all shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Printable View
                </a>
                <a href="<?= url('students/' . $student['id']) ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/50 transition-all shadow-2xs">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Parameter Context Selector Form -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-5 mb-6 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-550 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all shadow-2xs">
                    <?php foreach (['2023-24', '2024-25', '2025-26', '2026-27'] as $ay): ?>
                        <option value="<?= $ay ?>" <?= $academicYear === $ay ? 'selected' : '' ?>><?= $ay ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-550 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Semester</label>
                <select name="semester" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all shadow-2xs">
                    <?php foreach (['Semester 1', 'Semester 2'] as $sem): ?>
                        <option value="<?= $sem ?>" <?= $semester === $sem ? 'selected' : '' ?>><?= $sem ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-95 shadow-md" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                     Load Saved Scores
                </button>
            </div>
        </form>
    </div>

    <!-- Tabbed Entry Form -->
    <form method="POST" action="<?= url('students/' . $student['id'] . '/report-card') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <input type="hidden" name="semester" value="<?= e($semester) ?>">

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 border-b border-slate-200 dark:border-slate-800/60 mb-6 overflow-x-auto">
            <button type="button" @click="activeTab = 'routine'" :class="activeTab === 'routine' ? 'text-brand-600 dark:text-brand-400 border-brand-500 border-b-2 font-semibold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 border-b-2 border-transparent'" class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">Routine Profile</button>
            <button type="button" @click="activeTab = 'skills'" :class="activeTab === 'skills' ? 'text-brand-600 dark:text-brand-400 border-brand-500 border-b-2 font-semibold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 border-b-2 border-transparent'" class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">Learning Skills</button>
            <button type="button" @click="activeTab = 'academics'" :class="activeTab === 'academics' ? 'text-brand-600 dark:text-brand-400 border-brand-500 border-b-2 font-semibold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 border-b-2 border-transparent'" class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">Academic Profile</button>
            <button type="button" @click="activeTab = 'cocurriculum'" :class="activeTab === 'cocurriculum' ? 'text-brand-600 dark:text-brand-400 border-brand-500 border-b-2 font-semibold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 border-b-2 border-transparent'" class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">Co-Curriculum</button>
            <button type="button" @click="activeTab = 'summary'" :class="activeTab === 'summary' ? 'text-brand-600 dark:text-brand-400 border-brand-500 border-b-2 font-semibold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 border-b-2 border-transparent'" class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">Attendance & Sign</button>
        </div>

        <!-- Routine Profile Tab -->
        <div x-show="activeTab === 'routine'" class="space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Routine Evaluation (A: Excellent, B: Good, C: Needs Improvement, N/A, Refused)</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200 border-collapse border border-slate-200 dark:border-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white font-bold uppercase text-xs border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800">Evaluation Parameter</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Excellent (A)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Good (B)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Needs Improvement (C)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Refused (R)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">N/A</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php
                            foreach ($routineParams as $key => $label):
                                $val = $getVal($reportCard['routine_profile'] ?? [], $key, 'B');
                            ?>
                            <tr class="odd:bg-slate-50/50 even:bg-white dark:odd:bg-slate-950/40 dark:even:bg-slate-900/10 hover:bg-slate-100/50 dark:hover:bg-slate-850/30 transition-colors">
                                <td class="p-3.5 font-semibold text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-800"><?= $label ?></td>
                                <?php foreach (['A', 'B', 'C', 'Refused', 'N/A'] as $rating): ?>
                                <td class="p-3.5 text-center border border-slate-200 dark:border-slate-800">
                                    <input type="radio" name="routine_profile[<?= $key ?>]" value="<?= $rating ?>" <?= $val === $rating ? 'checked' : '' ?> class="w-4 h-4 text-brand-500 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 focus:ring-brand-500 focus:ring-offset-white dark:focus:ring-offset-slate-900 focus:ring-2">
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Learning Skills Tab -->
        <div x-show="activeTab === 'skills'" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Learning Skills Evaluation</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200 border-collapse border border-slate-200 dark:border-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white font-bold uppercase text-xs border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800">Skill Indicator</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Excellent (A)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Good (B)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Needs Improvement (C)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">Refused (R)</th>
                                <th class="p-3.5 text-center border border-slate-200 dark:border-slate-800">N/A</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php
                            foreach ($skillParams as $key => $label):
                                $val = $getVal($reportCard['learning_skills'] ?? [], $key, 'B');
                            ?>
                            <tr class="odd:bg-slate-50/50 even:bg-white dark:odd:bg-slate-950/40 dark:even:bg-slate-900/10 hover:bg-slate-100/50 dark:hover:bg-slate-850/30 transition-colors">
                                <td class="p-3.5 font-semibold text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-800"><?= $label ?></td>
                                <?php foreach (['A', 'B', 'C', 'Refused', 'N/A'] as $rating): ?>
                                <td class="p-3.5 text-center border border-slate-200 dark:border-slate-800">
                                    <input type="radio" name="learning_skills[<?= $key ?>]" value="<?= $rating ?>" <?= $val === $rating ? 'checked' : '' ?> class="w-4 h-4 text-brand-500 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 focus:ring-brand-500 focus:ring-offset-white dark:focus:ring-offset-slate-900 focus:ring-2">
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Academic Profile Tab -->
        <div x-show="activeTab === 'academics'" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Academic Profiles (Quantitative Marks Entry)</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200 border-collapse border border-slate-200 dark:border-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white font-bold uppercase text-xs border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800">Subject</th>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800 text-center">Unit Test (Max 20)</th>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800 text-center">Theory (Max 40)</th>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800 text-center">Practical / Oral (Max 15)</th>
                                <th class="p-3.5 border border-slate-200 dark:border-slate-800">Remarks / Feedback</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php
                            foreach ($subjects as $key => $label):
                                $subjData = ($reportCard['academic_profile'] ?? [])[$key] ?? [];
                            ?>
                            <tr class="odd:bg-slate-50/50 even:bg-white dark:odd:bg-slate-950/40 dark:even:bg-slate-900/10 hover:bg-slate-100/50 dark:hover:bg-slate-850/30 transition-colors">
                                <td class="p-3.5 font-bold text-slate-850 dark:text-slate-100 border border-slate-200 dark:border-slate-800"><?= $label ?></td>
                                <td class="p-3.5 text-center border border-slate-200 dark:border-slate-800">
                                    <input type="number" name="academic_profile[<?= $key ?>][unit_test]" min="0" max="20" step="0.5" value="<?= e($subjData['unit_test'] ?? '0') ?>" class="w-24 bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-white rounded-lg py-1.5 px-2.5 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                                </td>
                                <td class="p-3.5 text-center border border-slate-200 dark:border-slate-800">
                                    <input type="number" name="academic_profile[<?= $key ?>][theory]" min="0" max="40" step="0.5" value="<?= e($subjData['theory'] ?? '0') ?>" class="w-24 bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-white rounded-lg py-1.5 px-2.5 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                                </td>
                                <td class="p-3.5 text-center border border-slate-200 dark:border-slate-800">
                                    <input type="number" name="academic_profile[<?= $key ?>][practical]" min="0" max="15" step="0.5" value="<?= e($subjData['practical'] ?? '0') ?>" class="w-24 bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-white rounded-lg py-1.5 px-2.5 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                                </td>
                                <td class="p-3.5 border border-slate-200 dark:border-slate-800">
                                    <input type="text" name="academic_profile[<?= $key ?>][remarks]" value="<?= e($subjData['remarks'] ?? '') ?>" class="w-full min-w-[200px] bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-white rounded-lg py-1.5 px-2.5 text-sm focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="e.g. Good reading skills">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Co-Curriculum Tab -->
        <div x-show="activeTab === 'cocurriculum'" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Co-Curricular / Sensory Activity Profiles</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    foreach ($activities as $key => $label):
                        $actVal = ($reportCard['cocurriculum_profile'] ?? [])[$key] ?? 'Good';
                    ?>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200"><?= $label ?></label>
                        <select name="cocurriculum_profile[<?= $key ?>]" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all shadow-2xs">
                            <?php foreach (['Outstanding', 'Excellent', 'Good', 'Satisfactory', 'Needs Attention'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $actVal === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Summary & Attendance Tab -->
        <div x-show="activeTab === 'summary'" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 space-y-6 shadow-sm">
                
                <!-- Attendance Grid -->
                <div>
                    <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Attendance Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Total Working Days</label>
                            <input type="number" name="attendance_profile[total_days]" value="<?= e(($reportCard['attendance_profile'] ?? [])['total_days'] ?? '90') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Days Present</label>
                            <input type="number" name="attendance_profile[present_days]" value="<?= e(($reportCard['attendance_profile'] ?? [])['present_days'] ?? '85') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Punctuality / Discipline Grade</label>
                            <select name="attendance_profile[punctuality]" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                                <?php foreach (['A', 'B', 'C', 'D'] as $g): ?>
                                <option value="<?= $g ?>" <?= (($reportCard['attendance_profile'] ?? [])['punctuality'] ?? 'A') === $g ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-800/60">

                <!-- Overall Teacher Narrative -->
                <div>
                    <h3 class="text-md font-bold text-slate-800 dark:text-white mb-3">Overall Class Teacher Feedback & Assessment Narrative</h3>
                    <textarea name="feedback_text" rows="5" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="Write overall comments on social integration, speech development, fine motor control, academic focus, and general behaviors..."><?= e($reportCard['feedback_text'] ?? '') ?></textarea>
                </div>

                <hr class="border-slate-200 dark:border-slate-800/60">

                <!-- Signature Authority Configuration -->
                <div>
                    <h3 class="text-md font-bold text-slate-800 dark:text-white mb-4">Authorized Signature Designations (Only 3 designations: Class Teacher, Coordinator/Trustee, and Parent)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Class Teacher -->
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 space-y-3">
                            <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">1. Class Teacher Designation</h4>
                            <div>
                                <label class="block text-2xs font-semibold text-slate-400 dark:text-slate-500 mb-1 uppercase">Teacher Name</label>
                                <input type="text" name="authorized_by[class_teacher]" value="<?= e(($reportCard['authorized_by'] ?? [])['class_teacher'] ?? 'Class Teacher') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-xs focus:outline-none focus:border-brand-500 shadow-2xs">
                            </div>
                            <div>
                                <label class="block text-2xs font-semibold text-slate-400 dark:text-slate-500 mb-1 uppercase">Signature Image (PNG/JPG)</label>
                                <input type="file" name="class_teacher_sig" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-brand-600/20 file:text-brand-400">
                                <input type="hidden" name="old_class_teacher_sig" value="<?= e(($reportCard['authorized_by'] ?? [])['class_teacher_sig'] ?? '') ?>">
                                <?php if (!empty(($reportCard['authorized_by'] ?? [])['class_teacher_sig'])): ?>
                                    <div class="mt-2">
                                        <p class="text-2xs text-slate-400 dark:text-slate-500">Current Signature Preview:</p>
                                        <img src="<?= ($reportCard['authorized_by'])['class_teacher_sig'] ?>" class="h-10 object-contain mt-1 rounded bg-white px-2 py-0.5 border border-slate-200 dark:border-slate-700">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Trustee / Coordinator -->
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 space-y-3">
                            <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">2. Coordinator / Trustee Designation</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-2xs font-semibold text-slate-400 dark:text-slate-500 mb-1 uppercase">Designation Title</label>
                                    <input type="text" name="authorized_by[coordinator_title]" value="<?= e(($reportCard['authorized_by'] ?? [])['coordinator_title'] ?? 'Trustee-PSNF') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-xs focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="e.g. Trustee-PSNF or MT-PSNF">
                                </div>
                                <div>
                                    <label class="block text-2xs font-semibold text-slate-400 dark:text-slate-500 mb-1 uppercase">Official Name</label>
                                    <input type="text" name="authorized_by[coordinator]" value="<?= e(($reportCard['authorized_by'] ?? [])['coordinator'] ?? 'Bijal Fadia') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-xs focus:outline-none focus:border-brand-500 shadow-2xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-2xs font-semibold text-slate-400 dark:text-slate-500 mb-1 uppercase">Signature Image (PNG/JPG)</label>
                                <input type="file" name="coordinator_sig" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-brand-600/20 file:text-brand-400">
                                <input type="hidden" name="old_coordinator_sig" value="<?= e(($reportCard['authorized_by'] ?? [])['coordinator_sig'] ?? '') ?>">
                                <?php if (!empty(($reportCard['authorized_by'] ?? [])['coordinator_sig'])): ?>
                                    <div class="mt-2">
                                        <p class="text-2xs text-slate-400 dark:text-slate-500">Current Signature Preview:</p>
                                        <img src="<?= ($reportCard['authorized_by'])['coordinator_sig'] ?>" class="h-10 object-contain mt-1 rounded bg-white px-2 py-0.5 border border-slate-200 dark:border-slate-700">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-end gap-3 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 shadow-sm">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all shadow-xl hover:opacity-95" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save Report Card Data
            </button>
        </div>
    </form>
</div>

<script>
function reportCardForm() {
    return {
        activeTab: 'routine'
    };
}
</script>

<?php
$content = ob_get_clean();
?>
