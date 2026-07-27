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
    </div>

    <!-- Filter Card -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="<?= url('attendance') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Class & Section</label>
                <select name="class_section" required onchange="
                    const val = this.value.split('|');
                    document.getElementById('class_input').value = val[0] || '';
                    document.getElementById('section_input').value = val[1] || '';
                    this.form.submit();
                " class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
                    <option value="">Select Class & Section</option>
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
                <input type="date" name="date" value="<?= e($selectedDate) ?>" required onchange="if(document.getElementById('class_input').value) this.form.submit();"
                       class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
            </div>

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all bg-brand-600 hover:bg-brand-500 shadow-md">
                    Load Student List
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Form Sheet -->
    <?php if ($selectedClass): ?>
    <form method="POST" action="<?= url('attendance/save') ?>" class="space-y-6">
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
                                $attRecord = $attendanceMap[$s['id']] ?? ['status' => 'present', 'remarks' => ''];
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
                                    <div class="inline-flex p-1 bg-slate-950/40 border border-slate-850 rounded-xl">
                                        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                            <input type="radio" name="attendance[<?= $s['id'] ?>]" value="present" <?= $status === 'present' ? 'checked' : '' ?> class="sr-only">
                                            Present
                                        </label>
                                        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-amber-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                            <input type="radio" name="attendance[<?= $s['id'] ?>]" value="late" <?= $status === 'late' ? 'checked' : '' ?> class="sr-only">
                                            Late
                                        </label>
                                        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-red-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                            <input type="radio" name="attendance[<?= $s['id'] ?>]" value="absent" <?= $status === 'absent' ? 'checked' : '' ?> class="sr-only">
                                            Absent
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
    <?php else: ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center shadow-sm">
        <svg class="w-12 h-12 text-slate-650 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        <h3 class="text-sm font-semibold text-white">Select a class to mark attendance</h3>
        <p class="text-xs text-slate-500 mt-1">Choose a Class and Section from the filter block above to retrieve students.</p>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
