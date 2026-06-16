<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border bg-slate-900/30 border-white/5">
        <div>
            <h3 class="text-base font-bold text-white">Attendance Log</h3>
            <p class="text-xs text-slate-500">Track class attendance details and reviews</p>
        </div>
        <form method="GET" class="flex gap-2">
            <!-- Month Selector -->
            <select name="month" onchange="this.form.submit()"
                    class="bg-slate-950 border border-slate-800 text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $month) ? 'selected' : '';
                    echo "<option value=\"" . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . "\" $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                }
                ?>
            </select>
            <!-- Year Selector -->
            <select name="year" onchange="this.form.submit()"
                    class="bg-slate-950 border border-slate-800 text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
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
        <div class="p-4 rounded-xl border border-white/5 bg-slate-900/20 text-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Present</span>
            <span class="text-2xl font-bold text-emerald-400 block mt-1"><?= $summary['present'] ?></span>
        </div>
        <!-- Absent -->
        <div class="p-4 rounded-xl border border-white/5 bg-slate-900/20 text-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Absent</span>
            <span class="text-2xl font-bold text-red-400 block mt-1"><?= $summary['absent'] ?></span>
        </div>
        <!-- Late -->
        <div class="p-4 rounded-xl border border-white/5 bg-slate-900/20 text-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Late</span>
            <span class="text-2xl font-bold text-yellow-400 block mt-1"><?= $summary['late'] ?></span>
        </div>
        <!-- Half Day -->
        <div class="p-4 rounded-xl border border-white/5 bg-slate-900/20 text-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Half Day</span>
            <span class="text-2xl font-bold text-blue-400 block mt-1"><?= $summary['half_day'] ?></span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="rounded-2xl border border-white/5 bg-slate-900/20 p-6">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Daily Logs</h4>
        <div class="overflow-hidden rounded-xl border border-slate-800/60 bg-slate-950/30">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-800/60 text-slate-400">
                        <th class="p-4 font-semibold">Date</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Teacher Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="3" class="p-8 text-center text-slate-500">No attendance data recorded for this month.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-slate-900/20 transition-colors">
                        <td class="p-4 font-medium text-slate-300"><?= date('l, d M Y', strtotime($log['date'])) ?></td>
                        <td class="p-4">
                            <?php
                            $st = $log['status'];
                            $col = $st === 'present' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : ($st === 'absent' ? 'text-red-400 bg-red-500/10 border-red-500/10' : ($st === 'late' ? 'text-yellow-400 bg-yellow-500/10 border-yellow-500/10' : 'text-blue-400 bg-blue-500/10 border-blue-500/10'));
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-semibold <?= $col ?>">
                                <?= ucfirst(str_replace('_', ' ', $st)) ?>
                            </span>
                        </td>
                        <td class="p-4 text-slate-400 leading-relaxed"><?= e($log['remarks'] ?: '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
