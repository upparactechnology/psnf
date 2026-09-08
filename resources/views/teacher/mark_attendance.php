<?php
$layout    = 'app';
$pageTitle = 'Mark Attendance';
$breadcrumbs = [['label' => 'Teacher Dashboard', 'url' => '/teacher/dashboard'], ['label' => 'Mark Attendance']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Mark Student Attendance</h2>
            <p class="text-sm text-slate-500 mt-0.5">Record daily attendance for your assigned class</p>
        </div>
        <a href="<?= url('teacher/dashboard') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-400 bg-slate-800 hover:bg-slate-700 transition-all border border-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>

    <?php if (empty($assignedClasses)): ?>
    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-8 text-center">
        <svg class="w-12 h-12 text-amber-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <h3 class="text-sm font-bold text-amber-500">No Assigned Classes</h3>
        <p class="text-xs text-slate-400 mt-1">You are not assigned as a class teacher for any class. Please contact the administrator.</p>
    </div>
    <?php else: ?>

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
                    <?php foreach ($assignedClasses as $c): ?>
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
                    Load Students
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Form Sheet -->
    <form method="POST" action="<?= url('teacher/attendance/save') ?>" class="space-y-6">
        <?= \Core\View::csrf() ?>
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
                                        <div class="inline-flex p-1 bg-slate-100 border border-slate-200 rounded-xl gap-1" x-data="{ status: '<?= $status ?? 'present' ?>' }">
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
                                    </td>

                                    <!-- Remarks -->
                                    <td class="px-6 py-4">
                                        <input type="text" name="remarks[<?= $s['id'] ?>]" value="<?= e($attRecord['remarks']) ?>"
                                               class="w-full bg-slate-900 border border-slate-800 text-slate-300 placeholder-slate-600 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all"
                                               placeholder="e.g. sick leave, late bus">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($students)): ?>
        <div class="flex justify-end pt-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                Save Daily Attendance
            </button>
        </div>
        <?php endif; ?>

    </form>

    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
