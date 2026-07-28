<?php
$layout    = 'app';
$pageTitle = 'Exams & Grades';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Exams']];
ob_start();
?>

<div x-data="{ showAddModal: false }" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Exams & Evaluations</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage report cards, graded evaluations, and student progress metrics</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('exams/bulk-entry') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18"/></svg>
                Bulk Excel Entry Kiosk
            </a>
            <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Record Grade
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="<?= url('exams') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5 col-span-2">
                <label class="block text-xs font-medium text-slate-400">Class & Section</label>
                <select name="class_section" required onchange="
                    const val = this.value.split('|');
                    document.getElementById('class_input').value = val[0] || '';
                    document.getElementById('section_input').value = val[1] || '';
                " class="w-full bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all">
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

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all bg-slate-800 hover:bg-slate-750 border border-slate-700/50 shadow-md">
                    Load Records
                </button>
            </div>
        </form>
    </div>

    <!-- Grades Table -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Evaluation Term</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Score</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Grade</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($examResults)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            No graded evaluation records found for this class.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($examResults as $r): ?>
                        <tr class="hover:bg-slate-900/30 transition-colors">
                            <!-- Student Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></p>
                                        <p class="text-xs text-slate-500 font-mono"><?= e($r['admission_number']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Exam Name -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350 font-medium">
                                <?= e($r['exam_name']) ?>
                            </td>

                            <!-- Subject -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <?= e($r['subject']) ?>
                            </td>

                            <!-- Score -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350 text-center font-mono font-medium">
                                <?= $r['marks_obtained'] ?> / <?= $r['max_marks'] ?>
                            </td>

                            <!-- Grade -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-lg bg-indigo-950/40 text-indigo-400 border border-indigo-900/30">
                                    <?= e($r['grade']) ?>
                                </span>
                            </td>

                            <!-- Remarks -->
                            <td class="px-6 py-4 text-xs text-slate-400 max-w-xs truncate" title="<?= e($r['remarks']) ?>">
                                <?= e($r['remarks']) ?>
                            </td>

                            <!-- Date Published -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                <?= format_date($r['date_published']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Grade Modal Dialog -->
    <div x-show="showAddModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Record Grade</h3>
                    <button @click="showAddModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="<?= url('exams/store') ?>" class="p-6 space-y-4">
                    <?= \Core\View::csrf() ?>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Select Student <span class="text-red-400">*</span></label>
                        <select name="student_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="">Select Student...</option>
                            <?php foreach ($students as $stu): ?>
                                <option value="<?= $stu['id'] ?>"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Evaluation Term <span class="text-red-400">*</span></label>
                            <input type="text" name="exam_name" required placeholder="e.g. First Term Evaluation" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Subject Name <span class="text-red-400">*</span></label>
                            <input type="text" name="subject" required placeholder="e.g. Music Therapy" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Marks Obtained <span class="text-red-400">*</span></label>
                            <input type="number" step="0.01" name="marks_obtained" required placeholder="e.g. 42.5" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Max Marks <span class="text-red-400">*</span></label>
                            <input type="number" step="0.01" name="max_marks" required placeholder="e.g. 50" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Grade Letter <span class="text-red-400">*</span></label>
                            <input type="text" name="grade" required placeholder="e.g. A+" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Teacher Remarks / Notes</label>
                        <textarea name="remarks" rows="2" placeholder="Teacher comments regarding student participation and development..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-650 hover:bg-brand-500 transition-all shadow-md">Record Result</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
