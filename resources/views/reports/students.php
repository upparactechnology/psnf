<?php
$layout = 'app';
$pageTitle = 'Student Reports & Analytics';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports', 'url' => '/reports'], ['label' => 'Students']];
ob_start();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Student & Academic Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Enrollment trends, classroom distribution, and profiles demographics.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 0-2 2v4h10z"></path></svg>
            Print Report
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Admission Status Card -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <h3 class="text-base font-bold text-white mb-4">Registration Status Distribution</h3>
            <div class="space-y-4 flex-1 flex flex-col justify-center">
                <?php if (empty($statusCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No student records found.</p>
                <?php else: ?>
                    <?php 
                    $totalStudents = array_sum(array_column($statusCounts, 'cnt'));
                    foreach ($statusCounts as $status): 
                        $statusClass = 'badge-applied';
                        switch(strtolower($status['admission_status'])) {
                            case 'enrolled': $statusClass = 'badge-enrolled'; break;
                            case 'approved': $statusClass = 'badge-approved'; break;
                            case 'assessment': $statusClass = 'badge-assessment'; break;
                            case 'review': $statusClass = 'badge-review'; break;
                            case 'withdrawn': $statusClass = 'badge-withdrawn'; break;
                        }
                    ?>
                        <div class="flex items-center justify-between text-sm">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase <?= $statusClass ?>">
                                <?= e($status['admission_status']) ?>
                            </span>
                            <span class="font-bold text-white"><?= number_format($status['cnt']) ?> students (<?= $totalStudents > 0 ? round(($status['cnt'] / $totalStudents) * 100) : 0 ?>%)</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gender Demographics -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <h3 class="text-base font-bold text-white mb-4">Gender Distribution</h3>
            <div class="space-y-4 flex-1 flex flex-col justify-center">
                <?php if (empty($genderCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No gender logs found.</p>
                <?php else: ?>
                    <?php 
                    $totalG = array_sum(array_column($genderCounts, 'cnt'));
                    foreach ($genderCounts as $g): 
                        $pct = $totalG > 0 ? ($g['cnt'] / $totalG) * 100 : 0;
                    ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="capitalize text-slate-350"><?= e($g['gender']) ?></span>
                                <span class="font-bold text-white"><?= $g['cnt'] ?> (<?= round($pct) ?>%)</span>
                            </div>
                            <div class="w-full bg-slate-850 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Blood Group Demographics -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <h3 class="text-base font-bold text-white mb-4">Blood Group Breakdown</h3>
            <div class="space-y-3 flex-1 flex flex-col justify-center">
                <?php if (empty($disabilityCounts)): ?>
                    <p class="text-sm text-slate-500 text-center py-4">No blood group data recorded.</p>
                <?php else: ?>
                    <?php foreach ($disabilityCounts as $d): ?>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-800/40">
                            <span class="text-slate-400 font-medium"><?= e($d['disability_type']) ?></span>
                            <span class="text-white font-bold"><?= number_format($d['cnt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Strength by Class -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-base font-bold text-white mb-4">Class-wise Student Distribution</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if (empty($classCounts)): ?>
                <p class="col-span-full text-sm text-slate-500 text-center py-8">No classes registered.</p>
            <?php else: ?>
                <?php foreach ($classCounts as $c): ?>
                    <div class="flex justify-between items-center bg-slate-850 border border-slate-800/60 p-4 rounded-xl">
                        <div>
                            <h4 class="text-sm font-bold text-white"><?= e($c['class_name']) ?></h4>
                            <p class="text-xs text-slate-500 mt-0.5">Section: <?= e($c['section'] ?: 'A') ?></p>
                        </div>
                        <div class="px-3 py-1 bg-brand-500/10 text-brand-400 font-bold rounded-lg text-sm border border-brand-500/20">
                            <?= $c['cnt'] ?> Students
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
