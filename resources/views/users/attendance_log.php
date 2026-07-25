<?php
$layout    = 'app';
$pageTitle = 'Staff Kiosk Attendance';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Staff Attendance Log']];
ob_start();
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Staff Attendance Log</h2>
            <p class="text-sm text-slate-500 mt-0.5">Real-time clock-in and clock-out logs synced from Face Recognition Kiosk</p>
        </div>
        <a href="<?= url('dashboard') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back to Portal Home</a>
    </div>

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-4 shadow-sm">
        <form method="GET" action="<?= url('staff/attendance') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-400">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="<?= e($search ?? '') ?>"
                           placeholder="Name, Code, Email..."
                           class="w-full bg-slate-950 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2 pl-9 pr-3 text-xs focus:outline-none focus:border-brand-500 transition-all">
                </div>
            </div>

            <!-- Date Filter -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-400">Date</label>
                <input type="date" name="date" value="<?= e($date ?? '') ?>"
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all">
            </div>

            <!-- Status Filter -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-400">Status</label>
                <select name="status" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <option value="">All Statuses</option>
                    <option value="present" <?= ($status ?? '') === 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="LATE" <?= ($status ?? '') === 'LATE' ? 'selected' : '' ?>>Late</option>
                    <option value="EARLY_LEAVE" <?= ($status ?? '') === 'EARLY_LEAVE' ? 'selected' : '' ?>>Early Leave</option>
                    <option value="absent" <?= ($status ?? '') === 'absent' ? 'selected' : '' ?>>Absent</option>
                </select>
            </div>

            <!-- Staff Member Filter -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-400">Staff Member</label>
                <select name="employee_id" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <option value="">All Staff Members</option>
                    <?php if (!empty($employeesList)): ?>
                        <?php foreach ($employeesList as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ((string)($employee_id ?? '') === (string)$emp['id']) ? 'selected' : '' ?>>
                                <?= e($emp['name']) ?> (<?= e($emp['employee_code'] ?? 'EMP-'.$emp['id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <a href="<?= url('staff/attendance') ?>" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold py-2.5 px-3 rounded-xl transition-all" title="Reset All Filters">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <?php if (empty($records)): ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-650" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-slate-500 text-sm">No kiosk attendance records found.</p>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-800/60 text-slate-400">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Staff Member</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Employee ID</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Clock Event</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Clock Time</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Device ID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                <?php foreach ($records as $rec): ?>
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                <?= strtoupper(substr($rec['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white"><?= e($rec['first_name'] . ' ' . $rec['last_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($rec['emp_email'] ?? '') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-350">
                        <?= e($rec['emp_code']) ?>
                    </td>
                    <td class="px-5 py-4">
                        <?php if (($rec['clock_type'] ?? '') === 'CHECK_IN'): ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-blue-950/45 text-blue-400 border border-blue-900/50">
                            Check In
                        </span>
                        <?php else: ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-purple-950/45 text-purple-400 border border-purple-900/50">
                            Check Out
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-300">
                        <?= date('d M Y, h:i A', strtotime($rec['clock_time'])) ?>
                    </td>
                    <td class="px-5 py-4">
                        <?php if (in_array($rec['status'] ?? '', ['LATE', 'EARLY_LEAVE'])): ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-red-950/45 text-red-500 border border-red-900/50">
                            <?= e($rec['status']) ?>
                        </span>
                        <?php else: ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-950/45 text-emerald-400 border border-emerald-900/50">
                            <?= e($rec['status']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-500">
                        <?= e($rec['device_id'] ?? 'N/A') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
?>
