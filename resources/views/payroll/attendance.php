<?php
$layout    = 'app';
$pageTitle = 'Detailed Attendance Tracker';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Payroll Workspace', 'url' => '/payroll/runs'], ['label' => 'Detailed Attendance']];
ob_start();
?>

<div x-data="{ logModal: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Panel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Detailed Payroll Attendance</h1>
            <p class="text-xs text-slate-500 mt-0.5">Audit check-in bounds, exempt late marks, and adjust shift overrides to sync directly with monthly payroll calculations</p>
        </div>
        <div>
            <button @click="logModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-9-4h18c1.1 0 2 .9 2 2v6c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6c0-1.1.9-2 2-2z"/></svg>
                Log Manual Override
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <form action="<?= url('payroll/attendance') ?>" method="GET" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex items-center justify-between gap-4 text-xs shadow-sm">
        <div class="flex items-center gap-2">
            <span class="font-bold text-slate-700 dark:text-slate-300">Target Attendance Date:</span>
            <input type="date" name="date" value="<?= e($date) ?>" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 font-mono text-slate-900 dark:text-white">
            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition-all">Load Date</button>
        </div>
        <span class="text-2xs font-mono text-rose-500 font-bold">Shift Start Policy: Standard start + grace rules apply.</span>
    </form>

    <!-- Daily Log Grid Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden text-xs shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-2xs border-b border-slate-100 dark:border-slate-850">
                <tr>
                    <th class="p-4">Employee Details</th>
                    <th class="p-4">Department</th>
                    <th class="p-4">Clock In Scan</th>
                    <th class="p-4">Clock Out Scan</th>
                    <th class="p-4">Logged Hours</th>
                    <th class="p-4">Input Source</th>
                    <th class="p-4">Exemption Status</th>
                    <th class="p-4 text-right">Lateness Penalty Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="8" class="p-8 text-center text-slate-400">No attendance logs logged for <?= e(date('d F Y', strtotime($date))) ?> yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                    <tr class="hover:bg-slate-50/45 dark:hover:bg-slate-850/10 transition-all">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-500 to-amber-600 text-white font-bold text-xs flex items-center justify-center">
                                    <?= strtoupper(substr($l['first_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white"><?= e($l['first_name'] . ' ' . $l['last_name']) ?></span>
                                    <span class="font-mono text-3xs text-rose-500 block"><?= e($l['emp_code']) ?></span>
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
                        <td class="p-4 font-mono text-rose-500 font-bold">
                            <?= e($l['working_hours'] ?? '0 hrs 0 mins') ?>
                        </td>
                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400">
                            <?= e($l['source'] ?? 'Face Kiosk') ?>
                        </td>
                        <td class="p-4">
                            <?php if ($l['status'] === 'late'): ?>
                                <?php if (!empty($l['late_exempted'])): ?>
                                    <span class="px-2 py-1 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold cursor-pointer inline-flex items-center gap-1" title="Reason: <?= e($l['late_exemption_reason'] ?? 'Exempted') ?>" onclick="toggleExemption(<?= $l['id'] ?>, '<?= e($l['source']) ?>', false)">
                                        Exempted ✓
                                    </span>
                                <?php else: ?>
                                    <button class="px-2 py-1 rounded bg-slate-100 hover:bg-rose-600 hover:text-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold transition-all" onclick="toggleExemption(<?= $l['id'] ?>, '<?= e($l['source']) ?>', true)">
                                        Apply Exemption
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <?php 
                                $statusColor = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
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

    <!-- Manual Log Modal -->
    <div x-show="logModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="logModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10 text-xs">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Record Manual Attendance Override</h3>
            <p class="text-slate-500 leading-relaxed">Applying a manual attendance override modifies the default kiosk record for the date and automatically triggers an out-of-sync status flag for the active payroll run.</p>

            <form action="<?= url('payroll/attendance') ?>" method="POST" class="space-y-4">
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
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Date</label>
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
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Classification</label>
                    <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="present">Present (On Time)</option>
                        <option value="late">Late Arrival</option>
                        <option value="half_day">Half-Day Scan</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="logModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-500 transition-all">Save Override Log</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function toggleExemption(logId, source, exempt) {
    if (exempt) {
        const reason = prompt("Enter reason for lateness exemption:", "Official school duty");
        if (reason === null) return;
        
        fetch("<?= url('payroll/attendance/exempt') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `log_id=${logId}&source=${source === 'Manual Entry' ? 'manual' : 'kiosk'}&exempt=1&reason=${encodeURIComponent(reason)}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        });
    } else {
        if (!confirm("Are you sure you want to remove this exemption?")) return;
        fetch("<?= url('payroll/attendance/exempt') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `log_id=${logId}&source=${source === 'Manual Entry' ? 'manual' : 'kiosk'}&exempt=0`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        });
    }
}
</script>

<?php
$content = ob_get_clean();
?>
