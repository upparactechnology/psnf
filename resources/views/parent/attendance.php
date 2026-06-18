<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Dynamic Attendance Declaration Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm backdrop-blur-md">
        <!-- Date Selector (compulsory) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-white/5 mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Compulsory Attendance Declaration</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select a date to submit or update your child's attendance declaration</p>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400" for="target_date">Target Date:</label>
                <input type="date" name="target_date" id="target_date" 
                       value="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                       min="<?= date('Y-m-d') ?>"
                       hx-get="<?= url('parent/students/' . $active_student['id'] . '/attendance/check-date') ?>"
                       hx-target="#declaration-form-container"
                       hx-trigger="change"
                       class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
            </div>
        </div>

        <!-- Dynamic Form Container -->
        <div id="declaration-form-container">
            <?php
            $targetDateTimestamp = strtotime($tomorrowDate);
            $deadlineTimestamp = strtotime(date('Y-m-d', $targetDateTimestamp) . ' -1 day 23:00:00');
            $canEdit = (time() < $deadlineTimestamp);
            $remainingSeconds = max(0, $deadlineTimestamp - time());
            $lockedReason = '';

            if ($tomorrowDate < date('Y-m-d')) {
                $canEdit = false;
                $lockedReason = 'past_date';
            } elseif (!$canEdit) {
                $lockedReason = 'time_expired';
            } elseif ($tomorrowAtt) {
                if (empty($tomorrowAtt['created_by']) || (int)$tomorrowAtt['created_by'] !== auth_id()) {
                    $canEdit = false;
                    $lockedReason = 'school_managed';
                }
            }

            echo \Core\View::render('parent/attendance_form', [
                'active_student'   => $active_student,
                'tomorrowAtt'      => $tomorrowAtt,
                'canEdit'          => $canEdit,
                'remainingSeconds' => $remainingSeconds,
                'lockedReason'     => $lockedReason,
                'date'             => $tomorrowDate,
            ]);
            ?>
        </div>
    </div>

    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Attendance Log</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track class attendance details and reviews</p>
        </div>
        <form method="GET" class="flex gap-2">
            <!-- Month Selector -->
            <select name="month" onchange="this.form.submit()"
                    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $month) ? 'selected' : '';
                    echo "<option value=\"" . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . "\" $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                }
                ?>
            </select>
            <!-- Year Selector -->
            <select name="year" onchange="this.form.submit()"
                    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
                <?php
                $cy = (int)date('Y');
                for ($y = $cy - 2; $y <= $cy; $y++) {
                    $selected = ($y == $year) ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <!-- Attendance Status Counter Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Present -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 text-center shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Present</span>
            <span class="text-2xl font-bold text-emerald-500 dark:text-emerald-400 block mt-1"><?= $summary['present'] ?></span>
        </div>
        <!-- Absent -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 text-center shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Absent</span>
            <span class="text-2xl font-bold text-rose-500 dark:text-rose-400 block mt-1"><?= $summary['absent'] ?></span>
        </div>
        <!-- Late -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 text-center shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Late</span>
            <span class="text-2xl font-bold text-amber-500 dark:text-amber-400 block mt-1"><?= $summary['late'] ?></span>
        </div>
        <!-- Half Day -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 text-center shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Half Day</span>
            <span class="text-2xl font-bold text-blue-500 dark:text-blue-400 block mt-1"><?= $summary['half_day'] ?></span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 shadow-sm">
        <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Daily Logs</h4>
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/60 bg-white/40 dark:bg-slate-950/30">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800/60 text-slate-700 dark:text-slate-400">
                        <th class="p-4 font-semibold">Date</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Teacher Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="3" class="p-8 text-center text-slate-500">No attendance data recorded for this month.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/20 transition-colors border-b border-slate-100 dark:border-slate-800/40">
                        <td class="p-4 font-medium text-slate-800 dark:text-slate-300"><?= date('l, d M Y', strtotime($log['date'])) ?></td>
                        <td class="p-4">
                            <?php
                            $st = $log['status'];
                            $col = $st === 'present' ? 'text-emerald-500 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : ($st === 'absent' ? 'text-rose-500 dark:text-rose-400 bg-rose-500/10 border-rose-500/10' : ($st === 'late' ? 'text-amber-500 dark:text-amber-400 bg-yellow-500/10 border-yellow-500/10' : 'text-blue-500 dark:text-blue-400 bg-blue-500/10 border-blue-500/10'));
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-semibold <?= $col ?>">
                                <?= ucfirst(str_replace('_', ' ', $st)) ?>
                            </span>
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                            <?= e($log['remarks'] ?: '—') ?>
                            <?php if (!empty($log['medical_certificate'])): ?>
                                <div class="mt-1">
                                    <a href="<?= url('storage/uploads/attendance/' . $log['student_id'] . '/' . $log['medical_certificate']) ?>" target="_blank" 
                                       class="inline-flex items-center gap-1 text-[10px] text-brand-500 dark:text-brand-400 hover:text-brand-650 dark:hover:text-brand-300 font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Medical Certificate
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

