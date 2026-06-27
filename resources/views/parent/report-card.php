<?php
$layout    = 'parent';
$pageTitle = 'Student Progress Report Card';
$name = $student['first_name'] . ' ' . $student['last_name'];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Overview Card -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-semibold text-brand-500 dark:text-brand-400 uppercase tracking-widest">Student Academics</span>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mt-1">Progress Report Card</h2>
                <p class="text-sm text-slate-500 dark:text-slate-450 mt-1">
                    Student Name: <strong class="text-slate-800 dark:text-slate-200 font-semibold"><?= e($name) ?></strong> | 
                    Class: <span class="font-medium text-slate-700 dark:text-slate-300"><?= e($student['class'] ?? '—') ?> (<?= e($student['section'] ?? '—') ?>)</span>
                </p>
            </div>
            
            <?php if ($selectedReportCard): ?>
            <a href="<?= url('students/' . $student['id'] . '/report-card/view?semester=' . urlencode($selectedReportCard['semester']) . '&academic_year=' . urlencode($selectedReportCard['academic_year']) . '&pdf=1') ?>" 
               target="_blank"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:shadow-lg" 
               style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download / Print PDF
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($reportCards)): ?>
    <!-- Empty State -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 p-12 text-center shadow-sm">
        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 dark:text-white">No Report Cards Generated Yet</h3>
        <p class="text-sm text-slate-500 dark:text-slate-450 mt-1 max-w-sm mx-auto">The teachers or administrative team have not yet finalized the progress report card evaluations for <?= e($student['first_name']) ?>. Please check back later.</p>
    </div>
    <?php else: ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: Semester Selector List -->
        <div class="space-y-3">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Available Reports</span>
            <div class="space-y-2">
                <?php foreach ($reportCards as $rc): 
                    $isSelected = $selectedReportCard && (int)$selectedReportCard['id'] === (int)$rc['id'];
                    $btnClass = $isSelected 
                        ? 'w-full text-left p-3.5 rounded-xl border border-brand-500/30 bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold'
                        : 'w-full text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors';
                ?>
                <a href="?report_card_id=<?= (int)$rc['id'] ?>" class="block <?= $btnClass ?>">
                    <div class="flex justify-between items-center">
                        <span class="text-sm truncate"><?= e($rc['semester']) ?></span>
                        <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500"><?= e($rc['academic_year']) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Side: Detailed Highlights Card -->
        <?php if ($selectedReportCard): ?>
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
                
                <!-- Report Card Summary Info -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/60 pb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white"><?= e($selectedReportCard['semester']) ?> Summary</h4>
                        <p class="text-xs text-slate-500">Academic session: <?= e($selectedReportCard['academic_year']) ?></p>
                    </div>
                    <span class="text-xs bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold px-2.5 py-1 rounded-full border border-brand-500/20">Active</span>
                </div>

                <!-- Academic highlights summary -->
                <div class="space-y-4">
                    <h5 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Subject Performance Summary</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <?php
                        $subMap = [
                            'english' => 'English Language',
                            'maths' => 'Mathematics',
                            'evs_gk' => 'GK / EVS'
                        ];
                        foreach ($subMap as $key => $subLabel):
                            $subjData = $selectedReportCard['academic_profile'][$key] ?? [];
                            $ut = (float)($subjData['unit_test'] ?? 0);
                            $th = (float)($subjData['theory'] ?? 0);
                            $pr = (float)($subjData['practical'] ?? 0);
                            $total = $ut + $th + $pr;
                        ?>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/60 text-center">
                            <span class="block text-xs font-medium text-slate-500"><?= $subLabel ?></span>
                            <span class="block text-xl font-bold text-slate-800 dark:text-white mt-1.5"><?= $total ?> <span class="text-[10px] text-slate-400">/75</span></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Attendance details summary -->
                <div class="space-y-4">
                    <h5 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Attendance Status</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/60 rounded-xl flex justify-between items-center">
                            <span class="text-xs text-slate-500">Working Days:</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white"><?= e($selectedReportCard['attendance_profile']['total_days'] ?? '0') ?></span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/60 rounded-xl flex justify-between items-center">
                            <span class="text-xs text-slate-500">Days Present:</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white"><?= e($selectedReportCard['attendance_profile']['present_days'] ?? '0') ?></span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/60 rounded-xl flex justify-between items-center">
                            <span class="text-xs text-slate-500">Punctuality:</span>
                            <span class="text-sm font-bold text-emerald-500"><?= e($selectedReportCard['attendance_profile']['punctuality'] ?? 'A') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Teacher Remarks Narrative -->
                <div class="space-y-3">
                    <h5 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Class Teacher's Evaluative Narrative</h5>
                    <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/20 text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-sans italic">
                        "<?= nl2br(e($selectedReportCard['feedback_text'] ?? 'Student shows consistent growth.')) ?>"
                    </div>
                </div>

                <!-- Footer disclaimer -->
                <div class="text-[10px] text-slate-400 dark:text-slate-500 text-center pt-2">
                    To view full evaluated routines, learning skills scales, and authorized signature designations, please download the printable PDF version.
                </div>

            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
