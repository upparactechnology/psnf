<?php
$layout    = 'app';
$pageTitle = 'Employee Profile – ' . e($employee['first_name'] . ' ' . $employee['last_name']);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Directory', 'url' => '/staff/employees'], ['label' => $employee['emp_code']]];
ob_start();

$linkedUser = null;
if (!empty($employee['user_id'])) {
    $linkedUser = \Core\Application::$app->db->selectOne("SELECT id, name, email, is_active FROM users WHERE id = ?", [$employee['user_id']]);
}
?>

<div x-data="{ tab: 'overview' }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Profile Header Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex flex-col sm:flex-row items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-xl flex items-center justify-center shadow-lg">
                <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white"><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
                <p class="text-xs text-slate-500 mt-0.5"><span class="font-mono text-indigo-500 font-bold"><?= e($employee['emp_code']) ?></span> • <?= e($employee['designation_title'] ?? 'Staff') ?> (<?= e($employee['department_name'] ?? 'General') ?>)</p>
                <div class="flex items-center gap-3 text-2xs text-slate-400 mt-2 font-mono">
                    <span>Joined: <?= e($employee['joining_date']) ?></span>
                    <span>•</span>
                    <span><?= e($employee['email']) ?></span>
                </div>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($employee['status']) ?></span>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-1 text-xs">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Overview</button>
        <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Attendance History</button>
        <button @click="tab = 'payroll'" :class="tab === 'payroll' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Salary & Payroll</button>
    </div>

    <!-- Tab 1: Overview & Edit -->
    <div x-show="tab === 'overview'" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Employment Details & Shift Limits</h3>
                <div class="space-y-2 text-slate-600 dark:text-slate-300">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Department:</span> <span class="font-bold"><?= e($employee['department_name'] ?? 'N/A') ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Designation:</span> <span class="font-bold"><?= e($employee['designation_title'] ?? 'N/A') ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Employment Type:</span> <span class="font-bold uppercase"><?= e($employee['employment_type']) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Min Clock In Time:</span> <span class="font-bold font-mono text-emerald-500"><?= date('h:i A', strtotime($employee['min_clock_in'] ?? '09:00:00')) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Max Clock Out Time:</span> <span class="font-bold font-mono text-indigo-500"><?= date('h:i A', strtotime($employee['max_clock_out'] ?? '17:00:00')) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Linked User Account:</span> 
                        <span class="font-bold">
                            <?php if ($linkedUser): ?>
                                <a href="<?= url('users/' . $linkedUser['id'] . '/edit?redirect_to=' . urlencode('/staff/employees/' . $employee['id'])) ?>" class="text-indigo-500 hover:underline">
                                    <?= e($linkedUser['name']) ?> (<?= e($linkedUser['email']) ?>)
                                </a>
                            <?php else: ?>
                                <span class="text-slate-500">Not Linked</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit Employee Form Card -->
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Edit Employee Shift & Profile</h3>
                <form action="<?= url('staff/employees/' . $employee['id']) ?>" method="POST" class="space-y-3">
                    <?= \Core\View::csrf() ?>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">First Name</label>
                            <input type="text" name="first_name" value="<?= e($employee['first_name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="<?= e($employee['last_name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Email</label>
                            <input type="email" name="email" value="<?= e($employee['email']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Status</label>
                            <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                                <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $employee['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="terminated" <?= $employee['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Department</label>
                            <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                                <option value="">Select Department...</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $employee['department_id'] == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Designation</label>
                            <select name="designation_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                                <option value="">Select Designation...</option>
                                <?php foreach ($designations as $des): ?>
                                    <option value="<?= $des['id'] ?>" <?= $employee['designation_id'] == $des['id'] ? 'selected' : '' ?>><?= e($des['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Min Clock In</label>
                            <input type="time" name="min_clock_in" value="<?= e(substr($employee['min_clock_in'] ?? '09:00:00', 0, 5)) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Max Clock Out</label>
                            <input type="time" name="max_clock_out" value="<?= e(substr($employee['max_clock_out'] ?? '17:00:00', 0, 5)) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-1">Basic Monthly Salary (₹)</label>
                        <input type="number" step="0.01" name="salary_basic" value="<?= e($employee['salary_basic']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition-all">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 2: Attendance -->
    <div x-show="tab === 'attendance'" class="space-y-4 text-xs" x-cloak>

        <!-- Month Filter -->
        <div class="flex items-center gap-3 flex-wrap">
            <form method="GET" class="flex items-center gap-2" id="attFilterForm">
                <select name="att_month" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int)$attMonth === $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="att_year" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= (int)$attYear === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>

        <!-- Summary Cards -->
        <?php $s = is_array($summary) ? (object)$summary : ($summary ?? (object)['total'=>0,'present_count'=>0,'late_count'=>0,'absent_count'=>0,'half_day_count'=>0,'on_leave_count'=>0,'holiday_count'=>0,'wfh_count'=>0,'total_hours'=>0,'avg_hours'=>0,'total_late_mins'=>0]); ?>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 text-center">
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= (int)($s->total ?? 0) ?></div>
                <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Total Days</div>
            </div>
            <div class="p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/40 bg-emerald-50 dark:bg-emerald-950/20 text-center">
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"><?= (int)($s->present_count ?? 0) ?></div>
                <div class="text-2xs text-emerald-600 dark:text-emerald-500 font-semibold mt-1 uppercase tracking-wider">Present</div>
            </div>
            <div class="p-4 rounded-2xl border border-amber-200 dark:border-amber-800/40 bg-amber-50 dark:bg-amber-950/20 text-center">
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400"><?= (int)($s->late_count ?? 0) ?></div>
                <div class="text-2xs text-amber-600 dark:text-amber-500 font-semibold mt-1 uppercase tracking-wider">Late</div>
            </div>
            <div class="p-4 rounded-2xl border border-red-200 dark:border-red-800/40 bg-red-50 dark:bg-red-950/20 text-center">
                <div class="text-2xl font-extrabold text-red-600 dark:text-red-400"><?= (int)($s->absent_count ?? 0) ?></div>
                <div class="text-2xs text-red-600 dark:text-red-500 font-semibold mt-1 uppercase tracking-wider">Absent</div>
            </div>
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 text-center">
                <div class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400"><?= number_format((float)($s->total_hours ?? 0), 1) ?>h</div>
                <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Total Hours</div>
            </div>
        </div>

        <!-- Extra Stats Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 dark:text-white"><?= (int)($s->half_day_count ?? 0) ?></div>
                    <div class="text-2xs text-slate-400 font-semibold">Half Days</div>
                </div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 dark:text-white"><?= (int)($s->on_leave_count ?? 0) ?></div>
                    <div class="text-2xs text-slate-400 font-semibold">On Leave</div>
                </div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 dark:text-white"><?= (int)($s->wfh_count ?? 0) ?></div>
                    <div class="text-2xs text-slate-400 font-semibold">WFH</div>
                </div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-rose-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 dark:text-white"><?= (int)($s->total_late_mins ?? 0) ?>m</div>
                    <div class="text-2xs text-slate-400 font-semibold">Late Mins</div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
            <?php if (empty($allAttendance)): ?>
                <div class="p-10 text-center text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm font-medium">No attendance records for <?= date('F Y', mktime(0, 0, 0, $attMonth, 1, $attYear)) ?></p>
                </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-400 uppercase tracking-wider text-2xs">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Day</th>
                            <th class="px-4 py-3 font-semibold">Clock In</th>
                            <th class="px-4 py-3 font-semibold">Clock Out</th>
                            <th class="px-4 py-3 font-semibold">Hours</th>
                            <th class="px-4 py-3 font-semibold">Late</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <?php foreach ($allAttendance as $att): ?>
                        <?php
                            $status = $att['status'] ?? 'absent';
                            $lateMins = (int)($att['late_minutes'] ?? 0);
                            $source = $att['source'] ?? 'none';

                            $statusColors = [
                                'present'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                                'late'     => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800/50',
                                'absent'   => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800/50',
                                'half_day' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800/50',
                                'on_leave' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
                                'holiday'  => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800/50',
                                'wfh'      => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-200 dark:border-cyan-800/50',
                            ];
                            $colorClass = $statusColors[$status] ?? $statusColors['absent'];

                            $rowBg = '';
                            if ($status === 'absent') $rowBg = 'bg-red-50/30 dark:bg-red-950/5';
                            elseif ($status === 'late') $rowBg = 'bg-amber-50/30 dark:bg-amber-950/5';
                            elseif ($status === 'half_day') $rowBg = 'bg-orange-50/20 dark:bg-orange-950/5';
                        ?>
                        <tr class="<?= $rowBg ?> hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap"><?= e($att['date']) ?></td>
                            <td class="px-4 py-3 text-slate-500 font-medium"><?= e($att['day']) ?></td>
                            <td class="px-4 py-3 font-mono <?= !empty($att['clock_in']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-300 dark:text-slate-600' ?>">
                                <?= !empty($att['clock_in']) ? date('h:i A', strtotime($att['clock_in'])) : '—' ?>
                            </td>
                            <td class="px-4 py-3 font-mono <?= !empty($att['clock_out']) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-300 dark:text-slate-600' ?>">
                                <?= !empty($att['clock_out']) ? date('h:i A', strtotime($att['clock_out'])) : '—' ?>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                <?php if ($att['working_hours'] > 0): ?>
                                    <?= number_format((float)$att['working_hours'], 1) ?>h
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($lateMins > 0): ?>
                                    <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400"><?= $lateMins ?>m</span>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-lg text-2xs font-bold uppercase border <?= $colorClass ?>">
                                    <?= e(str_replace('_', ' ', $status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($source === 'face'): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-2xs font-semibold bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-800/50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Face
                                    </span>
                                <?php elseif ($source === 'manual'): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-2xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Manual
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab 3: Payroll -->
    <div x-show="tab === 'payroll'" class="space-y-4 text-xs" x-cloak>

        <!-- Salary Structure Card -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Salary Structure</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Earnings -->
                <div class="space-y-2">
                    <div class="text-2xs font-bold text-emerald-500 uppercase tracking-wider mb-2">Earnings</div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Basic Salary</span> <span class="font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format($basicSalary, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">HRA (40%)</span> <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format($hra, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Medical Allowance</span> <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format($medicalAllowance, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Transport Allowance</span> <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format($transportAllowance, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Special Allowance</span> <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₹<?= number_format($specialAllowance, 2) ?></span></div>
                    <div class="flex justify-between py-2 border-t-2 border-emerald-200 dark:border-emerald-800/50 mt-1"><span class="font-bold text-slate-900 dark:text-white">Gross Monthly</span> <span class="font-mono font-extrabold text-emerald-600 dark:text-emerald-400">₹<?= number_format($grossMonthly, 2) ?></span></div>
                </div>
                <!-- Deductions -->
                <div class="space-y-2">
                    <div class="text-2xs font-bold text-red-500 uppercase tracking-wider mb-2">Deductions</div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">PF (12% of Basic)</span> <span class="font-mono font-bold text-red-600 dark:text-red-400">-₹<?= number_format($pfDeduction, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">ESI</span> <span class="font-mono font-bold text-red-600 dark:text-red-400">-₹<?= number_format($esiDeduction, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Professional Tax</span> <span class="font-mono font-bold text-red-600 dark:text-red-400">-₹<?= number_format($taxDeduction, 2) ?></span></div>
                    <div class="flex justify-between py-2 border-t-2 border-red-200 dark:border-red-800/50 mt-1"><span class="font-bold text-slate-900 dark:text-white">Total Deductions</span> <span class="font-mono font-extrabold text-red-600 dark:text-red-400">-₹<?= number_format($totalDeductions, 2) ?></span></div>
                    <div class="flex justify-between py-2.5 border-t-2 border-indigo-200 dark:border-indigo-800/50 mt-2 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl px-3"><span class="font-extrabold text-slate-900 dark:text-white">Net Monthly In-Hand</span> <span class="font-mono font-extrabold text-lg text-indigo-600 dark:text-indigo-400">₹<?= number_format($netMonthly, 2) ?></span></div>
                </div>
            </div>
        </div>

        <!-- Annual Projection -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 text-center">
                <div class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">₹<?= number_format($grossMonthly * 12, 0) ?></div>
                <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Annual Gross</div>
            </div>
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 text-center">
                <div class="text-lg font-extrabold text-red-500 dark:text-red-400">₹<?= number_format($totalDeductions * 12, 0) ?></div>
                <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Annual Deductions</div>
            </div>
            <div class="p-4 rounded-2xl border border-indigo-200 dark:border-indigo-800/40 bg-indigo-50 dark:bg-indigo-950/20 text-center">
                <div class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400">₹<?= number_format($netMonthly * 12, 0) ?></div>
                <div class="text-2xs text-indigo-500 font-semibold mt-1 uppercase tracking-wider">Annual In-Hand</div>
            </div>
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 text-center">
                <div class="text-lg font-extrabold text-slate-900 dark:text-white">₹<?= number_format($pfDeduction * 12, 0) ?></div>
                <div class="text-2xs text-slate-400 font-semibold mt-1 uppercase tracking-wider">Annual PF</div>
            </div>
        </div>

        <!-- Payroll History -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Payroll History</h3>
            <?php if (empty($payrollHistory)): ?>
                <div class="p-8 text-center text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <p class="text-sm font-medium">No payroll records yet</p>
                    <p class="text-xs text-slate-400 mt-1">Payroll will appear here once processed</p>
                </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-400 uppercase tracking-wider text-2xs">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Period</th>
                            <th class="px-4 py-3 font-semibold text-center">Work Days</th>
                            <th class="px-4 py-3 font-semibold text-center">Present</th>
                            <th class="px-4 py-3 font-semibold text-center">Late</th>
                            <th class="px-4 py-3 font-semibold text-center">Absent</th>
                            <th class="px-4 py-3 font-semibold text-right">Gross</th>
                            <th class="px-4 py-3 font-semibold text-right">Deductions</th>
                            <th class="px-4 py-3 font-semibold text-right">Net Pay</th>
                            <th class="px-4 py-3 font-semibold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <?php foreach ($payrollHistory as $ph): ?>
                        <?php
                            $prStatus = $ph['status'] ?? 'draft';
                            $prColors = [
                                'draft'    => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                'approved' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                                'locked'   => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
                            ];
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white"><?= e($ph['month_year']) ?></td>
                            <td class="px-4 py-3 text-center font-mono"><?= (int)($ph['working_days'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center font-mono text-emerald-600 dark:text-emerald-400 font-bold"><?= (int)($ph['present_days'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center font-mono text-amber-600 dark:text-amber-400"><?= (int)($ph['late_days'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center font-mono text-red-600 dark:text-red-400"><?= (int)($ph['absent_days'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float)$ph['gross_salary'], 2) ?></td>
                            <td class="px-4 py-3 text-right font-mono text-red-600 dark:text-red-400">-₹<?= number_format((float)($ph['late_deduction'] ?? 0) + (float)($ph['absent_deduction'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-right font-mono font-extrabold text-indigo-600 dark:text-indigo-400">₹<?= number_format((float)$ph['net_salary'], 2) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-lg text-2xs font-bold uppercase border <?= $prColors[$prStatus] ?? $prColors['draft'] ?>">
                                    <?= e(ucfirst($prStatus)) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Attendance-Based Deduction Summary -->
        <?php if (isset($s) && ((int)($s->late_count ?? 0) > 0 || (int)($s->absent_count ?? 0) > 0)): ?>
        <div class="p-5 rounded-2xl border border-amber-200 dark:border-amber-800/40 bg-amber-50/50 dark:bg-amber-950/20 space-y-3">
            <h3 class="font-bold text-amber-700 dark:text-amber-400 text-xs uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Current Month Attendance Impact
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div>
                    <div class="text-lg font-extrabold text-amber-600 dark:text-amber-400"><?= (int)($s->late_count ?? 0) ?></div>
                    <div class="text-2xs text-amber-600/70 font-semibold">Late Days</div>
                </div>
                <div>
                    <div class="text-lg font-extrabold text-red-600 dark:text-red-400"><?= (int)($s->absent_count ?? 0) ?></div>
                    <div class="text-2xs text-red-600/70 font-semibold">Absent Days</div>
                </div>
                <div>
                    <div class="text-lg font-extrabold text-orange-600 dark:text-orange-400"><?= (int)($s->half_day_count ?? 0) ?></div>
                    <div class="text-2xs text-orange-600/70 font-semibold">Half Days</div>
                </div>
                <div>
                    <div class="text-lg font-extrabold text-slate-900 dark:text-white"><?= number_format((float)($s->total_hours ?? 0), 1) ?>h</div>
                    <div class="text-2xs text-slate-500 font-semibold">Total Worked</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
