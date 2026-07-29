<?php
$layout    = 'app';
$pageTitle = 'Attendance Engine & Shift Logs';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Attendance']];
ob_start();
?>

<div x-data="{ logModal: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Date Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Attendance Engine</h1>
            <p class="text-xs text-slate-500 mt-0.5">Policy-driven shift tracking, grace minute calculations, clock-in/out & overtime rules</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl shadow-inner">
                <a href="<?= url('staff/attendance') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow shadow-slate-200/50 dark:shadow-none">Daily List</a>
                <a href="<?= url('staff/attendance?view=calendar') ?>" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Monthly Calendar</a>
            </div>
            <a href="<?= url('attendance/face-kiosk') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Clock In / Out Entry</a>
        </div>
    </div>

    <!-- Date Filter -->
    <form action="<?= url('staff/attendance') ?>" method="GET" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-2">
            <span class="font-bold text-slate-700 dark:text-slate-300">Target Attendance Date:</span>
            <input type="date" name="date" value="<?= e($date) ?>" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 font-mono text-slate-900 dark:text-white">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white font-bold">Load Date</button>
        </div>
        <span class="text-2xs font-mono text-indigo-500 font-bold">Assigned Shift: General Staff Shift (09:00 AM – 05:00 PM, 15m Grace)</span>
    </form>

    <!-- Attendance Logs Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Employee</th>
                    <th class="p-4">Department</th>
                    <th class="p-4">Clock In</th>
                    <th class="p-4">Clock Out</th>
                    <th class="p-4">Working Hours</th>
                    <th class="p-4">Method / Device</th>
                    <th class="p-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-400 text-xs">No attendance logs recorded for <?= e($date) ?> yet. Click "+ Clock In / Out Entry" to log attendance.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                    <tr>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                    <?= strtoupper(substr($l['first_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white"><?= e($l['first_name'] . ' ' . $l['last_name']) ?></span>
                                    <span class="font-mono text-2xs text-indigo-500 block"><?= e($l['emp_code']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-slate-500"><?= e($l['department_name'] ?? 'General Staff') ?></td>
                        <td class="p-4 font-mono font-bold <?= ($l['status'] === 'late' || $l['status'] === 'half_day') ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400' ?>">
                            <?= e($l['clock_in'] ?? '--:--') ?>
                            <?php if ($l['status'] === 'late'): ?>
                                <span class="text-[9px] bg-amber-500/20 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded ml-1 uppercase">Late</span>
                            <?php elseif ($l['status'] === 'half_day'): ?>
                                <span class="text-[9px] bg-orange-500/20 text-orange-600 dark:text-orange-400 px-1.5 py-0.5 rounded ml-1 uppercase">Half-Day</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            <?= e($l['clock_out'] ?? '--:--') ?>
                        </td>
                        <td class="p-4 font-mono text-indigo-500 font-bold">
                            <?= e($l['working_hours'] ?? '0 hrs 0 mins') ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-lg text-2xs font-bold border border-indigo-500/30 bg-indigo-500/10 text-indigo-500 inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= e($l['source'] ?? 'Face Kiosk') ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <?php 
                                $statusColor = 'bg-emerald-500/10 text-emerald-500';
                                if ($l['status'] === 'late') $statusColor = 'bg-amber-500/10 text-amber-600 dark:text-amber-500';
                                elseif ($l['status'] === 'half_day') $statusColor = 'bg-orange-500/10 text-orange-600 dark:text-orange-500';
                                elseif ($l['status'] === 'absent') $statusColor = 'bg-red-500/10 text-red-500';
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold <?= $statusColor ?> uppercase">
                                <?= e(str_replace('_', '-', $l['status'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Clock Entry Modal -->
    <div x-show="logModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="logModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Record Clock In / Out Entry</h3>
            
            <form action="<?= url('staff/attendance') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Employee</label>
                    <select name="employee_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= e($e['first_name'] . ' ' . $e['last_name'] . ' (' . $e['emp_code'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Date</label>
                    <input type="date" name="date" value="<?= e($date) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Clock In Time</label>
                        <input type="time" name="clock_in" value="09:00" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Clock Out Time</label>
                        <input type="time" name="clock_out" value="17:00" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="logModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
