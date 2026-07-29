<?php
$layout    = 'app';
$pageTitle = 'Attendance Calendar';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Attendance Calendar']];
ob_start();

$weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Toggle -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Attendance Calendar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Visualize monthly attendance records and patterns for individual employees.</p>
        </div>
        <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl shadow-inner">
            <a href="<?= url('staff/attendance') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Daily List</a>
            <a href="<?= url('staff/attendance?view=calendar') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow shadow-slate-200/50 dark:shadow-none">Monthly Calendar</a>
        </div>
    </div>

    <!-- Filters -->
    <form action="<?= url('staff/attendance') ?>" method="GET" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex flex-wrap items-center justify-between gap-4 text-xs">
        <input type="hidden" name="view" value="calendar">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700 dark:text-slate-300">Select Month:</span>
                <input type="month" name="month" value="<?= e($month) ?>" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 font-mono text-slate-900 dark:text-white">
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700 dark:text-slate-300">Employee:</span>
                <select name="employee_id" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white max-w-[200px]">
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= $employeeId == $e['id'] ? 'selected' : '' ?>>
                            <?= e($e['first_name'] . ' ' . $e['last_name'] . ' (' . $e['emp_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition-colors">Load Calendar</button>
        </div>
    </form>

    <?php if ($selectedEmp): ?>
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                <?= e($selectedEmp['first_name'] . ' ' . $selectedEmp['last_name']) ?>'s Attendance 
                <span class="text-sm font-normal text-slate-500 ml-2">(<?= date('F Y', strtotime($month.'-01')) ?>)</span>
            </h3>
            
            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Present</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Late</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-500"></span> Half-Day</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> Absent</div>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <!-- Headers -->
            <?php foreach ($weekDays as $wd): ?>
                <div class="bg-slate-50 dark:bg-slate-800/80 p-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <?= $wd ?>
                </div>
            <?php endforeach; ?>

            <!-- Days -->
            <?php 
                // Fill empty days before 1st of month
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="bg-white dark:bg-slate-900/60 p-4 min-h-[100px]"></div>';
                }

                // Render calendar days
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $d = $calData[$day];
                    
                    $bgColor = 'bg-white dark:bg-slate-900/60';
                    $borderColor = 'border-transparent';
                    $textColor = 'text-slate-400';
                    $badge = '';

                    if ($d['status'] === 'present') {
                        $bgColor = 'bg-emerald-50 dark:bg-emerald-900/10';
                        $borderColor = 'border-emerald-200 dark:border-emerald-800/50';
                        $textColor = 'text-emerald-700 dark:text-emerald-400';
                        $badge = '<span class="text-[9px] bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded font-bold uppercase">Present</span>';
                    } elseif ($d['status'] === 'late') {
                        $bgColor = 'bg-amber-50 dark:bg-amber-900/10';
                        $borderColor = 'border-amber-200 dark:border-amber-800/50';
                        $textColor = 'text-amber-700 dark:text-amber-500';
                        $badge = '<span class="text-[9px] bg-amber-500/20 text-amber-600 dark:text-amber-500 px-1.5 py-0.5 rounded font-bold uppercase">Late</span>';
                    } elseif ($d['status'] === 'half_day') {
                        $bgColor = 'bg-orange-50 dark:bg-orange-900/10';
                        $borderColor = 'border-orange-200 dark:border-orange-800/50';
                        $textColor = 'text-orange-700 dark:text-orange-500';
                        $badge = '<span class="text-[9px] bg-orange-500/20 text-orange-600 dark:text-orange-500 px-1.5 py-0.5 rounded font-bold uppercase">Half-Day</span>';
                    } elseif ($d['status'] === 'absent') {
                        $bgColor = 'bg-red-50 dark:bg-red-900/10';
                        $borderColor = 'border-red-200 dark:border-red-800/50';
                        $textColor = 'text-red-700 dark:text-red-500';
                        $badge = '<span class="text-[9px] bg-red-500/20 text-red-600 dark:text-red-400 px-1.5 py-0.5 rounded font-bold uppercase">Absent</span>';
                    } elseif ($d['status'] === 'sunday') {
                        $bgColor = 'bg-slate-50 dark:bg-slate-900/80';
                        $textColor = 'text-slate-400';
                        $badge = '<span class="text-[9px] bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 px-1.5 py-0.5 rounded font-bold uppercase">Sunday</span>';
                    }

                    echo '<div class="' . $bgColor . ' border border-l-0 border-t-0 ' . $borderColor . ' p-3 min-h-[110px] relative transition-colors">';
                    echo '<span class="absolute top-3 right-3 text-sm font-bold opacity-30 ' . $textColor . '">' . $day . '</span>';
                    
                    if ($d['status'] !== 'pending' && $d['status'] !== 'sunday') {
                        echo '<div class="mt-6 flex flex-col gap-1.5">';
                        echo '  <div class="flex items-center justify-between text-xs font-mono">';
                        echo '      <span class="text-slate-400">IN</span>';
                        echo '      <span class="font-bold ' . $textColor . '">' . e($d['in']) . '</span>';
                        echo '  </div>';
                        echo '  <div class="flex items-center justify-between text-xs font-mono">';
                        echo '      <span class="text-slate-400">OUT</span>';
                        echo '      <span class="font-bold ' . $textColor . '">' . e($d['out']) . '</span>';
                        echo '  </div>';
                        echo '</div>';
                    }
                    
                    echo '<div class="absolute bottom-3 left-3">' . $badge . '</div>';
                    echo '</div>';
                }

                // Fill remaining empty days
                $totalCells = $firstDayOfWeek + $daysInMonth;
                $remaining = 7 - ($totalCells % 7);
                if ($remaining < 7) {
                    for ($i = 0; $i < $remaining; $i++) {
                        echo '<div class="bg-white dark:bg-slate-900/60 p-4 min-h-[100px]"></div>';
                    }
                }
            ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
