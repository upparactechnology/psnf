<?php
$layout    = 'app';
$pageTitle = 'Classes & Sections';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Classes']];
ob_start();
?>

<div x-data="{ showCreateModal: false, searchStudent: '' }" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Classes & Sections</h2>
            <p class="text-sm text-slate-500 mt-0.5">Directory of active classes, section configurations, and enrolled students</p>
        </div>
        <button @click="showCreateModal = true" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90"
                style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Class
        </button>
    </div>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (empty($classes)): ?>
        <div class="col-span-full rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-650 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="text-sm font-semibold text-white">No active classes found</h3>
            <p class="text-xs text-slate-500 mt-1">Enroll students and assign them classes to populate this directory.</p>
        </div>
        <?php else: ?>
            <?php foreach ($classes as $c): ?>
            <?php 
                $displayClass = $c['class'] ?: 'Unassigned';
                $classUrl = url('classes/' . urlencode((string)$displayClass));
            ?>
            <div class="relative group">
                <a href="<?= $classUrl ?>" class="block rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 hover:border-indigo-500/30 hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-md h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors"><?= e($displayClass) ?></h4>
                            <p class="text-xs text-slate-500">Section: <span class="text-slate-300 font-semibold font-mono"><?= e($c['section'] ?: 'Default') ?></span></p>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-between border-t border-slate-800/60 pt-3">
                        <span class="text-xs text-slate-400"><?= $c['student_count'] ?> Enrolled</span>
                        <span class="text-xs text-brand-400 hover:text-brand-300 font-medium flex items-center gap-0.5">
                            Students
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
                
                <?php if ($c['class'] && $displayClass !== 'Unassigned'): ?>
                <form action="<?= url('classes/delete') ?>" method="POST" class="absolute top-4 right-4 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="class" value="<?= e($c['class']) ?>">
                    <input type="hidden" name="section" value="<?= e($c['section']) ?>">
                    <button type="submit" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this class? This will also unassign all enrolled students.')" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 bg-slate-950/80 hover:bg-slate-950 rounded-lg border border-slate-850 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Create Class Modal -->
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak
         x-transition>
        <div class="w-full max-w-lg rounded-2xl border border-slate-800/60 bg-slate-900 p-6 space-y-5 shadow-2xl relative"
             @click.outside="showCreateModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Create New Class</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="<?= url('classes') ?>" class="space-y-4">
                <?= \Core\View::csrf() ?>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-450">Class Name <span class="text-red-400">*</span></label>
                        <input type="text" name="class" required placeholder="e.g. Class C"
                               class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    </div>
                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-450">Section</label>
                        <input type="text" name="section" placeholder="e.g. S1 (optional)"
                               class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    </div>
                </div>

                <!-- Select Students list -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Assign Students</label>
                    <input type="text" x-model="searchStudent" placeholder="Search students..."
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500 transition-all mb-2">
                    
                    <div class="max-h-48 overflow-y-auto border border-slate-800 rounded-xl divide-y divide-slate-800 bg-slate-950/20">
                        <?php if (empty($students)): ?>
                            <div class="p-4 text-center text-xs text-slate-500">No active students found.</div>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
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
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-350 hover:bg-slate-750 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white shadow-md hover:opacity-95 transition-all"
                            style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                        Create & Assign
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
