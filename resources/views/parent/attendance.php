<?php $layout = 'parent'; ?>

<div class="space-y-6">


        <!-- Header & Filter -->
    <div x-data="{ leaveModal: false, startDate: '', endDate: '', get needsDoc() { return this.startDate && this.endDate && (new Date(this.endDate) > new Date(this.startDate)); } }" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Attendance Log</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track class attendance details and reviews</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="leaveModal = true" class="px-4 py-2 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Request Leave
            </button>
            <form method="GET" class="flex gap-2">
            <!-- Academic Year Selector -->
            <?php if (!empty($academic_years)): ?>
            <select name="academic_year_id" onchange="this.form.submit()"
                    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-slate-300 text-xs rounded-xl px-3.5 py-2 focus:outline-none focus:border-brand-500">
                <?php foreach ($academic_years as $ay): ?>
                    <option value="<?= e((string)$ay['id']) ?>" <?= (int)$ay['id'] === $selected_year_id ? 'selected' : '' ?>>
                        <?= e($ay['year_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
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
        <!-- Leave Request Modal -->
        <div x-show="leaveModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center">
            <div x-show="leaveModal" @click="leaveModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
            <div x-show="leaveModal" class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden mt-10 transition-transform transform">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">📅</span>
                        Request Leave
                    </h3>
                    <button @click="leaveModal = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <form action="<?= url('parent/students/' . $active_student['id'] . '/attendance/request-leave') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Start Date</label>
                                <input type="date" name="start_date" x-model="startDate" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">End Date</label>
                                <input type="date" name="end_date" x-model="endDate" required :min="startDate || '<?= date('Y-m-d', strtotime('+1 day')) ?>'" class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Leave Type</label>
                            <select name="leave_type" required class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                                <option value="Medical">Medical Leave</option>
                                <option value="Personal">Personal Leave</option>
                                <option value="Family">Family Event</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Reason</label>
                            <textarea name="reason" rows="2" required class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500" placeholder="Please provide a brief reason"></textarea>
                        </div>
                        <div x-show="needsDoc" x-transition class="space-y-1.5 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 rounded-xl">
                            <label class="block text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Document Required (Multi-day Leave)
                            </label>
                            <p class="text-[10px] text-amber-700 dark:text-amber-500 mb-2">Since you selected more than 1 day, please upload a supporting document (e.g. Medical Certificate).</p>
                            <input type="file" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png" :required="needsDoc" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200">
                        </div>
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-brand-600 to-indigo-600 rounded-xl text-xs font-bold text-white shadow-lg hover:shadow-brand-600/20 transition-all mt-4">
                            Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
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

    <!-- Leave History -->
    <?php if (!empty($leaveHistory)): ?>
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm mt-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Leave Applications History</h3>
        <div class="space-y-4">
            <?php foreach ($leaveHistory as $leave): ?>
            <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white"><?= e($leave['leave_type']) ?></h4>
                        <?php if ($leave['status'] === 'Pending'): ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                        <?php elseif ($leave['status'] === 'Approved'): ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Approved</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400">Rejected</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <?= date('M d, Y', strtotime($leave['start_date'])) ?> to <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2"><?= e($leave['reason']) ?></p>
                </div>
                <?php if ($leave['medical_certificate']): ?>
                    <a href="<?= url('uploads/absences/' . $leave['medical_certificate']) ?>" target="_blank" class="shrink-0 px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">
                        View Document
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>


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

