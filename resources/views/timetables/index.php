<?php
$layout    = 'app';
$pageTitle = 'Class Timetables';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Timetables']];
ob_start();
?>

<div x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editId: null,
    editDay: '',
    editSubject: '',
    editTeacher: '',
    editStart: '',
    editEnd: '',
    openEdit(p) {
        this.editId = p.id;
        this.editDay = p.day_of_week;
        this.editSubject = p.subject;
        this.editTeacher = p.teacher_name || '';
        this.editStart = p.start_time.substring(0, 5);
        this.editEnd = p.end_time.substring(0, 5);
        this.showEditModal = true;
    }
}" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Class Timetables</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage weekly lecture and therapy schedules by class</p>
        </div>
        <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Schedule Slot
        </button>
    </div>

    <!-- Filter Card -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="GET" action="<?= url('academics/timetable') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
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
                    Load Timetable
                </button>
            </div>
        </form>
    </div>

    <!-- Timetable Weekly Columns (Monday to Saturday) -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <?php 
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day):
            $periods = $timetableByDay[$day] ?? [];
        ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-4 space-y-4 flex flex-col min-h-[400px]">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-850 pb-2 mb-1"><?= $day ?></h3>
            
            <?php if (empty($periods)): ?>
            <div class="flex-1 flex flex-col items-center justify-center text-center py-10 opacity-40">
                <svg class="w-6 h-6 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[10px] text-slate-550">No Classes</p>
            </div>
            <?php else: ?>
                <?php foreach ($periods as $p): ?>
                <div class="p-3 rounded-xl border border-slate-800/40 bg-slate-900/40 hover:border-brand-500/20 hover:bg-slate-900/60 transition-all duration-200 text-left space-y-2 relative group">
                    <div>
                        <p class="text-xs font-bold text-white leading-tight truncate"><?= e($p['subject']) ?></p>
                        <p class="text-[10px] text-slate-500 font-medium truncate mt-0.5"><?= e($p['teacher_name'] ?: 'No Teacher Assigned') ?></p>
                    </div>
                    
                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-850 text-[10px] font-semibold text-slate-450">
                        <span class="text-indigo-400 font-mono"><?= date('H:i', strtotime($p['start_time'])) ?> - <?= date('H:i', strtotime($p['end_time'])) ?></span>
                    </div>

                    <!-- Visible CRUD Actions on Hover/Always -->
                    <div class="flex items-center gap-1.5 pt-1.5 border-t border-slate-850 justify-end">
                        <button @click="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] bg-slate-850 text-indigo-400 hover:bg-slate-800 border border-indigo-500/10" title="Edit">
                            Edit
                        </button>
                        <form action="<?= url('academics/timetable/' . $p['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this schedule slot?')" class="inline">
                            <?= \Core\View::csrf() ?>
                            <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                            <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
                            <button type="submit" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] bg-slate-855 text-red-400 hover:bg-slate-800 border border-red-500/10" title="Delete">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Slot Modal Dialog -->
    <div x-show="showAddModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAddModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showAddModal" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Add Timetable Slot</h3>
                    <button @click="showAddModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="<?= url('academics/timetable/store') ?>" class="p-6 space-y-4 text-xs">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                    <input type="hidden" name="section" value="<?= e($selectedSection) ?>">

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Day of Week <span class="text-red-400">*</span></label>
                        <select name="day_of_week" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                            <?php foreach ($weekDays as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Subject / Activity Name <span class="text-red-400">*</span></label>
                        <input type="text" name="subject" required placeholder="e.g. Speech Therapy, Cognitive Math" class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Assign Teacher <span class="text-red-400">*</span></label>
                        <select name="teacher_name" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= e($teacher['name']) ?>"><?= e($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">Start Time <span class="text-red-400">*</span></label>
                            <input type="time" name="start_time" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">End Time <span class="text-red-400">*</span></label>
                            <input type="time" name="end_time" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-650 hover:bg-indigo-500 transition-all shadow-md">Create Slot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Slot Modal Dialog -->
    <div x-show="showEditModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showEditModal" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Edit Timetable Slot</h3>
                    <button @click="showEditModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/psnf/public/academics/timetable/' + editId + '/update'" class="p-6 space-y-4 text-xs">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                    <input type="hidden" name="section" value="<?= e($selectedSection) ?>">

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Day of Week <span class="text-red-400">*</span></label>
                        <select name="day_of_week" :value="editDay" @change="editDay = $event.target.value" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                            <?php foreach ($weekDays as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Subject / Activity Name <span class="text-red-400">*</span></label>
                        <input type="text" name="subject" :value="editSubject" @input="editSubject = $event.target.value" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Assign Teacher <span class="text-red-400">*</span></label>
                        <select name="teacher_name" :value="editTeacher" @change="editTeacher = $event.target.value" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= e($teacher['name']) ?>"><?= e($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">Start Time <span class="text-red-400">*</span></label>
                            <input type="time" name="start_time" :value="editStart" @input="editStart = $event.target.value" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">End Time <span class="text-red-400">*</span></label>
                            <input type="time" name="end_time" :value="editEnd" @input="editEnd = $event.target.value" required class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-3 focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-650 hover:bg-indigo-500 transition-all shadow-md">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
