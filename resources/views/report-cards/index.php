<?php
$layout    = 'app';
$pageTitle = 'Student Report Cards Dashboard';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Report Cards']
];
ob_start();

$db = \Core\Application::$app->db;
?>

<div class="max-w-6xl mx-auto space-y-6">

    <!-- Dashboard Header -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Report Cards Control Panel</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Manage, generate, and view progress report cards for all enrolled students.</p>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= url('report-cards/settings') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/50 transition-all shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
                <form method="GET" action="" class="flex flex-wrap items-center gap-3">
                <div>
                    <select name="academic_year" onchange="this.form.submit()" class="bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-850 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-xs font-medium focus:outline-none focus:border-brand-500 shadow-sm">
                        <?php foreach ($yearsList as $ay): ?>
                        <option value="<?= $ay['year_name'] ?>" <?= $academicYear === $ay['year_name'] ? 'selected' : '' ?>><?= $ay['year_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="semester" onchange="this.form.submit()" class="bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-850 text-slate-800 dark:text-white rounded-xl py-2 px-3.5 text-xs font-medium focus:outline-none focus:border-brand-500 shadow-sm">
                        <?php foreach ($semestersList as $sem): ?>
                        <option value="<?= $sem['name'] ?>" <?= $semester === $sem['name'] ? 'selected' : '' ?>><?= $sem['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Student Cards & Report Card Matrix -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/20">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Enrolled Student Registry & Status Matrix (<?= e($semester) ?> - <?= e($academicYear) ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-350">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-600 dark:text-slate-300 font-semibold uppercase text-xs border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4 border-b border-slate-200 dark:border-slate-800">Student Name</th>
                        <th class="p-4 border-b border-slate-200 dark:border-slate-800">Class & Section</th>
                        <th class="p-4 border-b border-slate-200 dark:border-slate-800">GR Number</th>
                        <th class="p-4 border-b border-slate-200 dark:border-slate-800 text-center">Status</th>
                        <th class="p-4 border-b border-slate-200 dark:border-slate-800 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 bg-white dark:bg-slate-900/10">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">No enrolled students found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($students as $std): 
                        // Let's check report card existence for BOTH semesters to make generation instant from this page
                        $sem1Exists = $db->selectOne(
                            "SELECT id FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = 'Semester 1'",
                            [$std['id'], $academicYear]
                        );
                        $sem2Exists = $db->selectOne(
                            "SELECT id FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = 'Semester 2'",
                            [$std['id'], $academicYear]
                        );
                    ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xs">
                                    <?= strtoupper(substr($std['first_name'],0,1) . substr($std['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-slate-200"><?= e($std['first_name'] . ' ' . $std['last_name']) ?></p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500">GR: <?= e($std['gr_number'] ?? '—') ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?= e($std['class'] ?? '—') ?></span>
                            <span class="text-xs text-slate-450 dark:text-slate-500 ml-1">(<?= e($std['section'] ?? '—') ?>)</span>
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-550 dark:text-slate-400"><?= e($std['gr_number'] ?? '—') ?></td>
                        
                        <!-- Status Indicators -->
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Sem 1 -->
                                <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-semibold border <?= $sem1Exists ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30' : 'bg-slate-50 dark:bg-slate-950/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-850' ?>">
                                    Sem 1: <?= $sem1Exists ? 'Done' : 'Pending' ?>
                                </span>
                                <!-- Sem 2 -->
                                <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-semibold border <?= $sem2Exists ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30' : 'bg-slate-50 dark:bg-slate-950/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-850' ?>">
                                    Sem 2: <?= $sem2Exists ? 'Done' : 'Pending' ?>
                                </span>
                            </div>
                        </td>

                        <!-- Actions Grid -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Semester 1 Action -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700/80 transition-all shadow-2xs">
                                        Sem 1 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-40 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-2xl z-50 overflow-hidden text-left p-1 space-y-0.5">
                                        <a href="<?= url('students/' . $std['id'] . '/report-card/edit?semester=Semester+1&academic_year=' . urlencode($academicYear)) ?>" class="block w-full px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors font-medium">
                                            <?= $sem1Exists ? 'Edit Scores' : 'Generate Report' ?>
                                        </a>
                                        <?php if ($sem1Exists): ?>
                                        <a href="<?= url('students/' . $std['id'] . '/report-card/view?semester=Semester+1&academic_year=' . urlencode($academicYear)) ?>" target="_blank" class="block w-full px-3 py-2 text-xs text-emerald-600 dark:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-350 rounded-lg transition-colors font-medium">
                                            Print Report
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Semester 2 Action -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 dark:bg-brand-600/10 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-500/20 hover:bg-brand-100 dark:hover:bg-brand-600/20 transition-all shadow-2xs">
                                        Sem 2 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-40 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-2xl z-50 overflow-hidden text-left p-1 space-y-0.5">
                                        <a href="<?= url('students/' . $std['id'] . '/report-card/edit?semester=Semester+2&academic_year=' . urlencode($academicYear)) ?>" class="block w-full px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors font-medium">
                                            <?= $sem2Exists ? 'Edit Scores' : 'Generate Report' ?>
                                        </a>
                                        <?php if ($sem2Exists): ?>
                                        <a href="<?= url('students/' . $std['id'] . '/report-card/view?semester=Semester+2&academic_year=' . urlencode($academicYear)) ?>" target="_blank" class="block w-full px-3 py-2 text-xs text-emerald-600 dark:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-350 rounded-lg transition-colors font-medium">
                                            Print Report
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
