<?php
$layout    = 'app';
$pageTitle = 'Attendance Registry';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Attendance']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Attendance Registry</h2>
            <p class="text-sm text-slate-500 mt-0.5">Record and view daily student attendance logs</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex bg-slate-800 p-1 rounded-xl">
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow">Daily List</span>
                <a href="<?= url('academics/attendance?view=calendar') ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-colors">Monthly Calendar</a>
            </div>
            <a href="<?= url('academics/attendance/leaves') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 transition-all border border-amber-500/20"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Leave Applications</a>
        </div>
    </div>

    <?php if (!empty($pendingLeaves)): ?>
    <!-- Leave Requests History -->
    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-amber-500 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Leave Requests History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider text-slate-400 bg-slate-900/50 rounded-t-xl">
                    <tr>
                        <th class="px-4 py-3 font-semibold rounded-tl-xl">Student</th>
                        <th class="px-4 py-3 font-semibold">Dates</th>
                        <th class="px-4 py-3 font-semibold">Reason</th>
                        <th class="px-4 py-3 font-semibold">Document</th>
                        <th class="px-4 py-3 font-semibold text-right rounded-tr-xl">Action/Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    <?php foreach ($pendingLeaves as $leave): ?>
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-200"><?= e($leave['first_name'] . ' ' . $leave['last_name']) ?></div>
                            <div class="text-[10px] text-slate-500"><?= e($leave['class']) ?> - <?= e($leave['section'] ?: 'Default') ?></div>
                        </td>
                        <td class="px-4 py-3 text-slate-300 text-xs">
                            <?= date('M d', strtotime($leave['start_date'])) ?> to <?= date('M d', strtotime($leave['end_date'])) ?>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs whitespace-normal min-w-[200px]">
                            <span class="font-semibold text-slate-300"><?= e($leave['leave_type']) ?>:</span> <?= e($leave['reason']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($leave['medical_certificate']): ?>
                                <a href="<?= url('uploads/absences/' . $leave['medical_certificate']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    View Doc
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-slate-600">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <?php if ($leave['status'] === 'Pending'): ?>
                                <form method="POST" action="<?= url('academics/attendance/approve-leave/' . $leave['id']) ?>" class="inline-block">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-white rounded-lg text-xs font-bold transition-colors">Approve</button>
                                </form>
                                <form method="POST" action="<?= url('academics/attendance/reject-leave/' . $leave['id']) ?>" class="inline-block">
                                    <?= \Core\View::csrf() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-xs font-bold transition-colors">Reject</button>
                                </form>
                            <?php elseif ($leave['status'] === 'Approved'): ?>
                                <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-500 rounded-lg text-xs font-bold">Approved</span>
                            <?php else: ?>
                                <span class="px-3 py-1.5 bg-rose-500/10 text-rose-500 rounded-lg text-xs font-bold">Rejected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Class & Section</label>
                <select name="class_section" onchange="
                    const val = this.value.split('|');
                    document.getElementById('class_input').value = val[0] || '';
                    document.getElementById('section_input').value = val[1] || '';
                    this.form.submit();
                " class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <option value="">All Classes & Sections</option>
                    <?php foreach ($classes as $c): ?>
                        <?php 
                            $optionVal = $c['class'] . '|' . $c['section'];
                            $selected = ($selectedClass === $c['class'] && $selectedSection === $c['section']) ? 'selected' : '';
                        ?>
                        <option value="<?= $optionVal ?>" <?= $selected ?>><?= e($c['class']) ?> - <?= e($c['section'] ?: 'Default') ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="class" id="class_input" value="<?= e($selectedClass) ?>">
                <input type="hidden" name="section" id="section_input" value="<?= e($selectedSection) ?>">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Date</label>
                <input type="date" name="date" value="<?= e($selectedDate) ?>" required onchange="this.form.submit();"
                       class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
            </div>

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all bg-brand-600 hover:bg-brand-500 shadow-md">
                    Filter Attendance
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Form Sheet -->
    <form method="POST" action="<?= url('student-attendance/save') ?>" class="space-y-6">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_to" value="<?= e(\Core\Application::$app->request->getPath()) ?>">
        <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
        <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
        <input type="hidden" name="date" value="<?= e($selectedDate) ?>">

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-900/50">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admission #</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Remarks / Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                No enrolled students found in this class.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                                        <?php 
                                $defaultStatus = null;
                                $attRecord = $attendanceMap[$s['id']] ?? ['status' => $defaultStatus, 'remarks' => ''];
                                $status = $attRecord['status'];
                            ?>
                            <tr class="hover:bg-slate-900/30 transition-colors">
                                <!-- Student Name -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white"
                                             style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                            <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Adm No -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-450 font-mono">
                                    <?= e($s['admission_number']) ?>
                                </td>

                                                                <!-- Status Toggles -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (has_role('super_admin')): ?>
                                        <div class="inline-flex p-1 bg-slate-100 border border-slate-200 rounded-xl gap-1" x-data="{ status: '<?= $status ?>' }">
                                            <label class="relative cursor-pointer" @click="status = 'present'">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="present" x-model="status" class="sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                                      :style="status === 'present' ? 'background-color: #10b981; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : 'background-color: transparent; color: #64748b;'">
                                                    Present
                                                </span>
                                            </label>
                                            <label class="relative cursor-pointer" @click="status = 'absent'">
                                                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="absent" x-model="status" class="sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                                      :style="status === 'absent' ? 'background-color: #f43f5e; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : 'background-color: transparent; color: #64748b;'">
                                                    Absent
                                                </span>
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($status === 'present'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Present</span>
                                        
                                        <?php elseif ($status === 'absent'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Absent</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">Not Marked</span>
                                        <?php endif; ?>
                                        <input type="hidden" name="attendance[<?= $s['id'] ?>]" value="<?= e($status ?? '') ?>">
                                    <?php endif; ?>
                                </td>

                                                                <!-- Remarks -->
                                <td class="px-6 py-4">
                                    <?php if (has_role('super_admin')): ?>
                                        <input type="text" name="remarks[<?= $s['id'] ?>]" value="<?= e($attRecord['remarks']) ?>"
                                               class="w-full bg-slate-900 border border-slate-800 text-slate-300 placeholder-slate-600 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all"
                                               placeholder="e.g. sick leave, late bus">
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?= e($attRecord['remarks'] ?: '-') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($students)): ?>
        <?php if (has_role('super_admin')): ?>
        <div class="flex justify-end pt-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                Save Daily Attendance
            </button>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </form>

</div>

<?php
$content = ob_get_clean();
?>
