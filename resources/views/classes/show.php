<?php
$layout    = 'app';
$pageTitle = 'Class Directory — ' . e($className);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Classes', 'url' => '/classes'], ['label' => $className]];
ob_start();
?>

<div x-data="{ showAssignModal: false, searchStudent: '' }" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white"><?= e($className) ?> Student Directory</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= count($students) ?> students enrolled in this class</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('classes') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">← Back to Classes</a>
            <?php if ($className !== 'Unassigned'): ?>
            <button @click="showAssignModal = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90"
                    style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Students
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Student Table / Grid -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admission #</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Age & Gender</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Disability Classification</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                             No students enrolled in this class yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <tr class="hover:bg-slate-900/30 transition-colors group">
                            <!-- Name / avatar -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-white"
                                         style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                        <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white group-hover:text-brand-400 transition-colors">
                                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                                        </p>
                                        <p class="text-xs text-slate-500"><?= e($s['blood_group'] ? $s['blood_group'] . ' Blood Group' : '—') ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Adm No -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-450 font-mono">
                                <?= e($s['admission_number']) ?>
                            </td>

                            <!-- Age / Gender -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <?= ucfirst($s['gender']) ?> (<?= age($s['dob']) ?>)
                            </td>

                            <!-- Disability -->
                            <td class="px-6 py-4 text-sm text-slate-350 max-w-xs truncate" title="<?= e($s['disability_detail']) ?>">
                                <span class="font-medium text-white block truncate"><?= e($s['disability_type']) ?></span>
                                <span class="text-xs text-slate-500 block truncate"><?= e($s['special_needs_summary'] ?: 'No summary') ?></span>
                            </td>

                            <!-- Branch -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <?= e($s['branch_name'] ?? 'Main') ?>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <a href="<?= url('students/' . $s['id']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-sm">
                                    Profile
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Students Modal -->
    <div x-show="showAssignModal" 
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
          x-cloak
          x-transition>
        <div class="w-full max-w-lg rounded-2xl border border-slate-800/60 bg-slate-900 p-6 space-y-5 shadow-2xl relative"
             @click.outside="showAssignModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Add Students to <?= e($className) ?></h3>
                <button @click="showAssignModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="<?= url('classes') ?>" class="space-y-4">
                <?= \Core\View::csrf() ?>
                
                <input type="hidden" name="class" value="<?= e($className) ?>">
                <input type="hidden" name="redirect_to" value="<?= url('classes/' . urlencode($className)) ?>">

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Section</label>
                    <input type="text" name="section" placeholder="e.g. S1 (optional)" list="existing-sections"
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    <datalist id="existing-sections">
                        <?php foreach ($sections as $sec): ?>
                            <?php if (!empty($sec['section'])): ?>
                                <option value="<?= e($sec['section']) ?>"></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <!-- Select Students list -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Assign Students</label>
                    <input type="text" x-model="searchStudent" placeholder="Search students..."
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all mb-2">
                    
                    <div class="max-h-48 overflow-y-auto border border-slate-800 rounded-xl divide-y divide-slate-800 bg-slate-950/20">
                        <?php if (empty($assignableStudents)): ?>
                            <div class="p-4 text-center text-xs text-slate-500">No other active students found.</div>
                        <?php else: ?>
                            <?php foreach ($assignableStudents as $student): ?>
                            <?php 
                                $studentName = $student['first_name'] . ' ' . $student['last_name'];
                                $assignedStr = $student['class'] ? " ({$student['class']}-" . ($student['section'] ?: 'Default') . ")" : ' (Unassigned)';
                            ?>
                            <label x-show="searchStudent === '' || '<?= strtolower(addslashes($studentName)) ?>'.includes(searchStudent.toLowerCase())"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-900/50 cursor-pointer text-xs transition-colors">
                                <input type="checkbox" name="student_ids[]" value="<?= (int)$student['id'] ?>"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                                <div>
                                    <span class="font-semibold text-slate-200"><?= e($studentName) ?></span>
                                    <span class="text-3xs text-slate-500 font-mono ml-2">ADM: <?= e($student['admission_number']) ?></span>
                                    <span class="text-3xs text-brand-400 ml-1"><?= $assignedStr ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showAssignModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-350 hover:bg-slate-750 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white shadow-md hover:opacity-95 transition-all"
                            style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                        Assign Students
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
