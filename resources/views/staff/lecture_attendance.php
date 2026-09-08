<?php
$layout    = 'app';
$pageTitle = 'Lecture Check-ins';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Attendance', 'url' => '/staff/attendance'], ['label' => 'Lecture Check-ins']];
ob_start();
?>

<div x-data="{
    activeTab: '<?= e($viewMode) ?>',
    showFilters: true
}" class="space-y-6 max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Teacher Lecture Check-ins</h1>
            <p class="text-xs text-slate-500 mt-0.5">Attendance recorded automatically on login based on timetable schedule</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl shadow-inner">
                <a href="<?= url('staff/attendance') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Daily List</a>
                <a href="<?= url('staff/attendance?view=calendar') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Monthly Calendar</a>
                <a href="<?= url('staff/attendance/lectures') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow shadow-slate-200/50 dark:shadow-none">Lecture Check-ins</a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total Check-ins</p>
                    <p class="text-3xl font-black text-slate-800 dark:text-white mt-1"><?= $totalLogs ?></p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><?= e($dateRangeLabel) ?></p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-400">On Time</p>
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1"><?= $onTimeLogs ?></p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><?= $onTimePercent ?>% rate</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-red-400">Late</p>
                    <p class="text-3xl font-black text-red-600 dark:text-red-400 mt-1"><?= $lateLogs ?></p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><?= $latePercent ?>% rate</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Teachers Active</p>
                    <p class="text-3xl font-black text-slate-800 dark:text-white mt-1"><?= count($teacherStats) ?></p>
                    <p class="text-[10px] text-slate-400 mt-0.5"><?= count($groupStats) ?> groups</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <form action="<?= url('staff/attendance/lectures') ?>" method="GET" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors" @click="showFilters = !showFilters">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Filters & Period</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="showFilters" x-transition class="p-4 pt-0 border-t border-slate-100 dark:border-slate-800">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-4 text-xs">
                <!-- Teacher -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Teacher</label>
                    <select name="teacher_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                        <option value="">All Teachers</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $filterTeacher == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Main Group / Class -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Class / Group</label>
                    <select name="main_group_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                        <option value="">All Groups</option>
                        <?php foreach ($mainGroups as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= $filterGroup == $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</label>
                    <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="on_time" <?= $filterStatus === 'on_time' ? 'selected' : '' ?>>On Time</option>
                        <option value="late" <?= $filterStatus === 'late' ? 'selected' : '' ?>>Late</option>
                    </select>
                </div>

                <!-- Month -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Month</label>
                    <input type="month" name="month" value="<?= e($filterMonth) ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Academic Year -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Academic Year</label>
                    <select name="academic_year_id" id="academicYearSelect" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500"
                        x-on:change="
                            const opt = $el.options[$el.selectedIndex];
                            const sd = opt.dataset.start;
                            const ed = opt.dataset.end;
                            if (sd && ed && sd !== '0000-00-00') {
                                document.querySelector('[name=date_from]').value = sd;
                                document.querySelector('[name=date_to]').value = ed;
                            }
                        ">
                        <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>" 
                                <?= $academicYearId == $ay['id'] ? 'selected' : '' ?>
                                data-start="<?= e($ay['start_date']) ?>"
                                data-end="<?= e($ay['end_date']) ?>">
                                <?= e($ay['year_name']) ?><?= $ay['status'] === 'current' ? ' (Current)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date From / To -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Date From</label>
                    <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-3 text-xs">
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Date To</label>
                    <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Quick Period Buttons -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-transparent">Quick</label>
                    <div class="flex gap-1.5">
                        <a href="<?= url('staff/attendance/lectures?date=' . date('Y-m-d')) ?>" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-indigo-500 hover:text-white transition-all">Today</a>
                        <a href="<?= url('staff/attendance/lectures?month=' . date('Y-m') . '&academic_year_id=' . $academicYearId) ?>" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-indigo-500 hover:text-white transition-all">This Month</a>
                        <a href="<?= url('staff/attendance/lectures?week=' . date('Y-m-d') . '&academic_year_id=' . $academicYearId) ?>" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-indigo-500 hover:text-white transition-all">This Week</a>
                        <a href="<?= url('staff/attendance/lectures?academic_year_id=' . $academicYearId) ?>" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-indigo-500 hover:text-white transition-all">Academic Year</a>
                    </div>
                </div>

                <div class="space-y-1 flex items-end">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-550 transition-all shadow-md">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <!-- View Tabs + Date Range Label -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300"><?= e($dateRangeLabel) ?></span>
            <?php if ($filterTeacher): ?>
                <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">
                    <?= e($teachers[array_search($filterTeacher, array_column($teachers, 'id'))]['name'] ?? '') ?>
                </span>
            <?php endif; ?>
            <?php if ($filterGroup): ?>
                <span class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[10px] font-bold">
                    <?= e($mainGroups[array_search($filterGroup, array_column($mainGroups, 'id'))]['name'] ?? '') ?>
                </span>
            <?php endif; ?>
            <?php if ($filterStatus): ?>
                <span class="px-2 py-0.5 rounded-full <?= $filterStatus === 'on_time' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600' ?> text-[10px] font-bold uppercase">
                    <?= $filterStatus === 'on_time' ? 'On Time' : 'Late' ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl shadow-inner">
            <button @click="activeTab = 'table'" :class="activeTab === 'table' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">All Logs</button>
            <button @click="activeTab = 'by_teacher'" :class="activeTab === 'by_teacher' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">By Teacher</button>
            <button @click="activeTab = 'by_group'" :class="activeTab === 'by_group' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">By Class</button>
            <button @click="activeTab = 'by_date'" :class="activeTab === 'by_date' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">By Date</button>
        </div>
    </div>

    <!-- TAB: All Logs -->
    <div x-show="activeTab === 'table'" x-cloak>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/20 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest">Lecture Attendance Logs</h3>
                <span class="text-2xs font-semibold text-slate-550"><?= $totalLogs ?> record(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-650 dark:text-slate-350">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-300 font-semibold uppercase text-2xs border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Teacher</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Class / Group</th>
                            <th class="p-4">Lecture Start</th>
                            <th class="p-4">Checked In At</th>
                            <th class="p-4">Grace</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Phone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" class="p-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-500">No lecture check-ins found</p>
                                        <p class="text-2xs text-slate-400 mt-1">Try adjusting your filters or date range.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="p-4">
                                    <span class="font-mono font-semibold text-slate-700 dark:text-slate-300"><?= date('D, M d', strtotime($l['attendance_date'])) ?></span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            <?= strtoupper(mb_substr($l['teacher_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-slate-200"><?= e($l['teacher_name']) ?></p>
                                            <p class="text-[10px] text-slate-500 font-mono"><?= e($l['teacher_email']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">
                                        <?= e($l['timetable_subject'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php if ($l['main_group_name']): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold" style="background-color: <?= e($l['main_group_color'] ?? '#6366f1') ?>20; color: <?= e($l['main_group_color'] ?? '#6366f1') ?>">
                                            <?= e($l['main_group_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[10px]">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-indigo-500 dark:text-indigo-400 font-bold">
                                        <?= date('h:i A', strtotime($l['lecture_time'])) ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-slate-700 dark:text-slate-400 font-semibold">
                                        <?= date('h:i A', strtotime($l['opened_at'])) ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-slate-550 text-2xs"><?= (int) $l['grace_period'] ?>m</span>
                                </td>
                                <td class="p-4">
                                    <?php if ($l['status'] === 'on_time'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 uppercase">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            On Time
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-bold bg-red-500/10 text-red-600 dark:text-red-400 uppercase">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Late
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="text-2xs text-slate-400 font-mono"><?= e($l['teacher_phone'] ?? '—') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: By Teacher -->
    <div x-show="activeTab === 'by_teacher'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (empty($teacherStats)): ?>
                <div class="col-span-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center">
                    <p class="text-sm font-semibold text-slate-500">No teacher data found for this period</p>
                </div>
            <?php else: ?>
                <?php foreach ($teacherStats as $ts): ?>
                    <?php
                        $pct = $ts['total'] > 0 ? round(($ts['on_time'] / $ts['total']) * 100) : 0;
                    ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm hover:border-indigo-500/30 transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                    <?= strtoupper(mb_substr($ts['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-slate-800 dark:text-white"><?= e($ts['name']) ?></p>
                                    <p class="text-[10px] text-slate-400"><?= $ts['total'] ?> check-in(s)</p>
                                </div>
                            </div>
                            <a href="<?= url('staff/attendance/lectures?teacher_id=' . array_search($ts, $teacherStats)) ?>" class="text-[10px] font-bold text-indigo-500 hover:underline">View All →</a>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 mb-4">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-2 rounded-xl bg-emerald-500/5">
                                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400"><?= $ts['on_time'] ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold">On Time</p>
                            </div>
                            <div class="p-2 rounded-xl bg-red-500/5">
                                <p class="text-lg font-black text-red-600 dark:text-red-400"><?= $ts['late'] ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold">Late</p>
                            </div>
                            <div class="p-2 rounded-xl bg-indigo-500/5">
                                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400"><?= $pct ?>%</p>
                                <p class="text-[10px] text-slate-400 font-semibold">Punctual</p>
                            </div>
                        </div>

                        <?php if (!empty($ts['subjects'])): ?>
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] text-slate-400 font-semibold mb-1.5">Subjects</p>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($ts['subjects'] as $sub): ?>
                                    <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold"><?= e($sub) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($ts['classes'])): ?>
                        <div class="mt-2">
                            <p class="text-[10px] text-slate-400 font-semibold mb-1.5">Classes</p>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($ts['classes'] as $cls): ?>
                                    <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[10px] font-bold"><?= e($cls) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB: By Class / Group -->
    <div x-show="activeTab === 'by_group'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if (empty($groupStats)): ?>
                <div class="col-span-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center">
                    <p class="text-sm font-semibold text-slate-500">No class data found for this period</p>
                </div>
            <?php else: ?>
                <?php foreach ($groupStats as $gs): ?>
                    <?php
                        $pct = $gs['total'] > 0 ? round(($gs['on_time'] / $gs['total']) * 100) : 0;
                    ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm hover:border-indigo-500/30 transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm" style="background-color: <?= e($gs['color']) ?>">
                                    <?= strtoupper(mb_substr($gs['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-slate-800 dark:text-white"><?= e($gs['name']) ?></p>
                                    <p class="text-[10px] text-slate-400"><?= $gs['total'] ?> total lectures</p>
                                </div>
                            </div>
                            <a href="<?= url('staff/attendance/lectures?main_group_id=' . array_search($gs, $groupStats)) ?>" class="text-[10px] font-bold text-indigo-500 hover:underline">View →</a>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 mb-4">
                            <div class="h-2 rounded-full transition-all" style="width: <?= $pct ?>%; background-color: <?= e($gs['color']) ?>"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-2 rounded-xl bg-emerald-500/5">
                                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400"><?= $gs['on_time'] ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold">On Time</p>
                            </div>
                            <div class="p-2 rounded-xl bg-red-500/5">
                                <p class="text-lg font-black text-red-600 dark:text-red-400"><?= $gs['late'] ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold">Late</p>
                            </div>
                            <div class="p-2 rounded-xl" style="background-color: <?= e($gs['color']) ?>10">
                                <p class="text-lg font-black" style="color: <?= e($gs['color']) ?>"><?= $pct ?>%</p>
                                <p class="text-[10px] text-slate-400 font-semibold">Punctual</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB: By Date -->
    <div x-show="activeTab === 'by_date'" x-cloak>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/20">
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest">Daily Attendance Summary</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-650 dark:text-slate-350">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-300 font-semibold uppercase text-2xs border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Day</th>
                            <th class="p-4 text-center">Total</th>
                            <th class="p-4 text-center">On Time</th>
                            <th class="p-4 text-center">Late</th>
                            <th class="p-4 text-center">Punctuality %</th>
                            <th class="p-4">Visual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <?php if (empty($dailyStats)): ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">No data for this period</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($dailyStats as $ds):
                                $pct = $ds['total'] > 0 ? round(($ds['on_time'] / $ds['total']) * 100) : 0;
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="p-4">
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200"><?= date('M d, Y', strtotime($ds['date'])) ?></span>
                                </td>
                                <td class="p-4">
                                    <span class="text-slate-500"><?= date('l', strtotime($ds['date'])) ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-black text-lg text-slate-800 dark:text-white"><?= $ds['total'] ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400"><?= $ds['on_time'] ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-red-600 dark:text-red-400"><?= $ds['late'] ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold <?= $pct >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') ?>"><?= $pct ?>%</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1">
                                        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 max-w-[120px]">
                                            <div class="h-2 rounded-full bg-emerald-500" style="width: <?= $ds['total'] > 0 ? ($ds['on_time'] / $ds['total']) * 100 : 0 ?>%"></div>
                                        </div>
                                        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 max-w-[120px]">
                                            <div class="h-2 rounded-full bg-red-500" style="width: <?= $ds['total'] > 0 ? ($ds['late'] / $ds['total']) * 100 : 0 ?>%"></div>
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

    <!-- Subject Breakdown (always visible) -->
    <?php if (!empty($subjectsList)): ?>
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-4">Subject Breakdown</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <?php foreach (array_slice($subjectsList, 0, 10, true) as $sub):
                $maxTotal = max(array_column($subjectsList, 'total'));
                $barWidth = $maxTotal > 0 ? ($sub['total'] / $maxTotal) * 100 : 0;
            ?>
            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/30">
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate"><?= e($sub['name']) ?></p>
                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-0.5"><?= $sub['total'] ?></p>
                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1 mt-2">
                    <div class="bg-indigo-500 h-1 rounded-full" style="width: <?= $barWidth ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
