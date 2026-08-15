<?php
$layout    = 'app';
$pageTitle = 'Class Timetables';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Timetables']];
ob_start();

$lectureLogsJson = json_encode($lectureAttendance ?? []);
?>

<script>
window.timetableLectureLogs = <?= $lectureLogsJson ?>;
</script>

<div x-data="{ 
    viewMode: 'week', // 'week', 'day', 'month'
    selectedDayFilter: 'Monday',
    selectedMonthDate: '<?= date('Y-m-d') ?>',
    showAddModal: false, 
    showEditModal: false,
    showBulkModal: false,
    showSlotDetailsModal: false,
    
    // Details Modal Data
    detailsSlot: {},
    detailsDate: '<?= date('Y-m-d') ?>',
    detailsAttendance: null,
    teacherAllAttendance: [],

    lectureLogs: window.timetableLectureLogs || [],

    formatTime12(timeStr) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        if (parts.length < 2) return timeStr;
        let hours = parseInt(parts[0], 10);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return (hours < 10 ? '0' + hours : hours) + ':' + minutes + ' ' + ampm;
    },

    formatDateTime12(dateTimeStr) {
        if (!dateTimeStr) return '';
        const parts = dateTimeStr.split(' ');
        const datePart = parts[0];
        const timePart = parts[1] || '';
        return datePart + ' ' + this.formatTime12(timePart);
    },

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
    },

    openSlotDetails(p) {
        this.detailsSlot = p;
        this.checkAttendance();
        this.showSlotDetailsModal = true;
    },

    checkAttendance() {
        if (!this.detailsSlot.teacher_name) {
            this.detailsAttendance = null;
            this.teacherAllAttendance = [];
            return;
        }

        // Find attendance matching teacher, time and date
        const slotStart = this.detailsSlot.start_time.substring(0, 5);
        
        // Match specific slot log
        const match = this.lectureLogs.find(log => {
            return log.teacher_name === this.detailsSlot.teacher_name &&
                   log.attendance_date === this.detailsDate &&
                   log.lecture_time.substring(0, 5) === slotStart;
        });

        this.detailsAttendance = match || null;

        // All attendance logs for this teacher on this date
        this.teacherAllAttendance = this.lectureLogs.filter(log => {
            return log.teacher_name === this.detailsSlot.teacher_name &&
                   log.attendance_date === this.detailsDate;
        });
    }
}" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Class Timetables</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage weekly lecture and therapy schedules by class</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="<?= url('academics/timetable/clear') ?>" method="POST" onsubmit="return confirm('Are you sure you want to completely clear the timetable for this class and section?')" class="inline">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-red-400 border border-red-900/30 hover:bg-red-950/20 transition-all">
                    🗑️ Clear Timetable
                </button>
            </form>
            <button @click="showBulkModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 border border-slate-800 hover:bg-slate-850 transition-all">
                ⚙️ Bulk Generator
            </button>
            <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Slot
            </button>
        </div>
    </div>

    <!-- Filter & View Switcher Card -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="<?= url('academics/timetable') ?>" class="flex flex-wrap items-end gap-3 text-xs">
            <div class="space-y-1">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Class & Section</label>
                <select name="class_section" required onchange="
                    const val = this.value.split('|');
                    document.getElementById('class_input').value = val[0] || '';
                    document.getElementById('section_input').value = val[1] || '';
                " class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-brand-500">
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
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/50 shadow-md">
                Load Timetable
            </button>
        </form>

        <!-- View Mode Switcher -->
        <div class="flex items-center gap-1.5 bg-slate-950 p-1 rounded-xl border border-slate-850 text-xs">
            <button @click="viewMode = 'day'" :class="viewMode === 'day' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3.5 py-1.5 rounded-lg font-medium transition-all">Day</button>
            <button @click="viewMode = 'week'" :class="viewMode === 'week' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3.5 py-1.5 rounded-lg font-medium transition-all">Week</button>
            <button @click="viewMode = 'month'" :class="viewMode === 'month' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3.5 py-1.5 rounded-lg font-medium transition-all">Month</button>
        </div>
    </div>

    <!-- VIEW 1: WEEKLY VIEW -->
    <div x-show="viewMode === 'week'" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <?php 
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day):
            $periods = $timetableByDay[$day] ?? [];
        ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 p-4 space-y-3 flex flex-col min-h-[380px]">
            <h3 class="text-2xs font-bold text-slate-450 uppercase tracking-widest border-b border-slate-850 pb-2 mb-1"><?= $day ?></h3>
            
            <?php if (empty($periods)): ?>
            <div class="flex-1 flex flex-col items-center justify-center text-center py-10 opacity-30">
                <p class="text-[10px] text-slate-550">No Slots</p>
            </div>
            <?php else: ?>
                <?php foreach ($periods as $p): ?>
                <div class="p-3 rounded-xl border border-slate-800/40 bg-slate-900/40 hover:border-brand-500/20 hover:bg-slate-900/60 transition-all duration-200 text-left space-y-2 relative group cursor-pointer" @click="openSlotDetails(<?= htmlspecialchars(json_encode($p)) ?>)">
                    <div>
                        <p class="text-xs font-bold text-white leading-tight truncate"><?= e($p['subject']) ?></p>
                        <p class="text-[10px] text-slate-500 font-medium truncate mt-0.5"><?= e($p['teacher_name'] ?: 'No Teacher') ?></p>
                    </div>
                    
                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-850 text-[10px] font-semibold text-slate-450">
                        <span class="text-indigo-400 font-mono"><?= date('h:i A', strtotime($p['start_time'])) ?> - <?= date('h:i A', strtotime($p['end_time'])) ?></span>
                    </div>

                    <!-- CRUD Hover Actions -->
                    <div class="flex items-center gap-1.5 pt-1.5 border-t border-slate-850 justify-end" @click.stop>
                        <button @click="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] bg-slate-850 text-indigo-400 hover:bg-slate-800 border border-indigo-500/10">
                            Edit
                        </button>
                        <form action="<?= url('academics/timetable/' . $p['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this schedule slot?')" class="inline">
                            <?= \Core\View::csrf() ?>
                            <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                            <input type="hidden" name="section" value="<?= e($selectedSection) ?>">
                            <button type="submit" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] bg-slate-855 text-red-400 hover:bg-slate-800 border border-red-500/10">
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

    <!-- VIEW 2: DAY VIEW -->
    <div x-show="viewMode === 'day'" class="space-y-4 max-w-xl mx-auto" x-cloak>
        <div class="flex items-center justify-between gap-3 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60">
            <span class="text-xs font-semibold text-slate-400">Select Day:</span>
            <select x-model="selectedDayFilter" class="bg-slate-950 border border-slate-800 text-slate-300 text-xs rounded-xl px-3 py-1.5">
                <?php foreach ($weekDays as $day): ?>
                    <option value="<?= $day ?>"><?= $day ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-3 bg-slate-900/20 p-6 rounded-2xl border border-slate-800/60 min-h-[250px]">
            <h3 class="text-sm font-bold text-white mb-4"><span x-text="selectedDayFilter"></span> Schedule</h3>
            
            <?php foreach ($weekDays as $day): ?>
            <div x-show="selectedDayFilter === '<?= $day ?>'" class="space-y-3">
                <?php 
                $periods = $timetableByDay[$day] ?? [];
                if (empty($periods)): 
                ?>
                <p class="text-xs text-slate-500 text-center py-10">No classes scheduled for <?= $day ?></p>
                <?php else: ?>
                    <?php foreach ($periods as $p): ?>
                    <div class="p-4 rounded-xl border border-slate-800/40 bg-slate-900/40 flex items-center justify-between hover:border-indigo-500/30 transition-all cursor-pointer" @click="openSlotDetails(<?= htmlspecialchars(json_encode($p)) ?>)">
                        <div class="space-y-1">
                            <span class="text-indigo-400 font-mono text-xs font-semibold"><?= date('h:i A', strtotime($p['start_time'])) ?> - <?= date('h:i A', strtotime($p['end_time'])) ?></span>
                            <h4 class="text-xs font-bold text-white"><?= e($p['subject']) ?></h4>
                            <p class="text-[10px] text-slate-550"><?= e($p['teacher_name'] ?: 'No Teacher Assigned') ?></p>
                        </div>
                        <span class="text-2xs bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-full px-3 py-1">Room <?= e($p['room']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- VIEW 3: MONTH VIEW (CALENDAR STYLE) -->
    <div x-show="viewMode === 'month'" class="max-w-4xl mx-auto space-y-4" x-cloak>
        <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-2xl border border-slate-800/60 text-xs">
            <span class="text-slate-400 font-medium">Monthly Reference:</span>
            <input type="date" x-model="selectedMonthDate" class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl px-3 py-1.5 font-mono">
        </div>

        <div class="grid grid-cols-7 gap-2 bg-slate-900/20 p-6 rounded-2xl border border-slate-800/60">
            <!-- Headers -->
            <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $sh): ?>
                <div class="text-center font-bold text-slate-500 text-[10px] uppercase py-1 border-b border-slate-850"><?= $sh ?></div>
            <?php endforeach; ?>

            <!-- Dummy/Actual grid mapping for reference month -->
            <?php
            $firstDayOfMonth = date('w', strtotime(date('Y-m-01')));
            $daysInMonth = date('t');
            
            // Dummy prefix items
            for ($i = 0; $i < $firstDayOfMonth; $i++) {
                echo '<div class="min-h-[70px] p-2 bg-transparent opacity-10"></div>';
            }

            for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++) {
                $dateStr = date('Y-m-') . sprintf('%02d', $dayNum);
                $dayName = date('l', strtotime($dateStr));
                
                // Get slots for this day name
                $daySlots = $timetableByDay[$dayName] ?? [];
                $hasSlots = !empty($daySlots) && $dayName !== 'Sunday';
                ?>
                <div class="min-h-[75px] p-2 rounded-xl border border-slate-800/60 bg-slate-900/40 space-y-1 text-left flex flex-col justify-between">
                    <span class="text-2xs font-mono font-bold text-slate-500"><?= $dayNum ?></span>
                    <?php if ($hasSlots): ?>
                        <div class="space-y-0.5">
                            <span class="block text-[8px] bg-indigo-500/20 text-indigo-400 font-bold px-1.5 py-0.5 rounded text-center truncate"><?= count($daySlots) ?> Classes</span>
                        </div>
                    <?php else: ?>
                        <span class="block text-[8px] text-slate-600 text-center italic">Off</span>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Lecture Details & Teacher Attendance Overlay Modal -->
    <div x-show="showSlotDetailsModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showSlotDetailsModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showSlotDetailsModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showSlotDetailsModal" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Lecture Slot Details</h3>
                    <button @click="showSlotDetailsModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 text-xs">
                    <!-- General Slot Info -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-950 p-4 rounded-xl border border-slate-850">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase tracking-wider font-semibold">Subject / Activity</span>
                            <span class="font-bold text-white text-sm" x-text="detailsSlot.subject"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase tracking-wider font-semibold">Assigned Teacher</span>
                            <span class="font-bold text-indigo-400 text-sm" x-text="detailsSlot.teacher_name || 'Unassigned'"></span>
                        </div>
                        <div class="col-span-2 grid grid-cols-2 gap-2 pt-2 border-t border-slate-900">
                            <div>
                                <span class="text-[10px] text-slate-500 block">Class Room</span>
                                <span class="font-semibold text-slate-350" x-text="'Room ' + detailsSlot.room"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-550 block">Schedule Timing</span>
                                <span class="font-semibold text-indigo-400 font-mono" x-text="formatTime12(detailsSlot.start_time) + ' - ' + formatTime12(detailsSlot.end_time)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Live Attendance Checker -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-300 text-xs border-b border-slate-800 pb-1.5">🎓 Teacher Attendance Verification</h4>
                        
                        <div class="flex items-center gap-3">
                            <span class="text-slate-450 font-semibold">Select Check-in Date:</span>
                            <input type="date" x-model="detailsDate" @change="checkAttendance()" class="bg-slate-950 border border-slate-800 text-slate-300 rounded-xl px-3 py-1 font-mono text-xs">
                        </div>

                        <!-- Match Status -->
                        <div class="p-4 rounded-xl border border-slate-850 bg-slate-950/50 space-y-2">
                            <template x-if="detailsAttendance">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-slate-300 font-bold">Status: 
                                            <span :class="detailsAttendance.status === 'on_time' ? 'text-emerald-500 bg-emerald-500/10' : 'text-amber-500 bg-amber-500/10'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" x-text="detailsAttendance.status"></span>
                                        </p>
                                        <p class="text-[10px] text-slate-550 mt-1">Checked in at <b x-text="formatDateTime12(detailsAttendance.opened_at)"></b></p>
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-mono" x-text="'Grace Applied: ' + detailsAttendance.grace_period + 'm'"></span>
                                </div>
                            </template>

                            <template x-if="!detailsAttendance">
                                <p class="text-red-500 font-bold italic py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Teacher Absent / Not Checked-In for this lecture slot on selected date.</p>
                            </template>
                        </div>
                    </div>

                    <!-- List all attendance for teacher -->
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-300 text-xs">Teacher's All Lectures Checked-In Today</h4>
                        <div class="space-y-1.5 max-h-[120px] overflow-y-auto pr-1">
                            <template x-for="log in teacherAllAttendance">
                                <div class="flex items-center justify-between p-2 rounded bg-slate-950 border border-slate-900 text-[10px]">
                                    <span class="text-indigo-400 font-mono font-bold" x-text="formatTime12(log.lecture_time)"></span>
                                    <span :class="log.status === 'on_time' ? 'text-emerald-500' : 'text-amber-500'" class="font-bold uppercase" x-text="log.status"></span>
                                    <span class="text-slate-500" x-text="formatDateTime12(log.opened_at)"></span>
                                </div>
                            </template>
                            <template x-if="teacherAllAttendance.length === 0">
                                <p class="text-slate-550 text-[10px] italic">No checks recorded for this teacher on this day.</p>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 flex justify-end">
                    <button type="button" @click="showSlotDetailsModal = false" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-750">Close Details</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal Dialog -->
    <div x-show="showBulkModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showBulkModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showBulkModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showBulkModal" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Bulk Generate Timetable</h3>
                    <button @click="showBulkModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="<?= url('academics/timetable/bulk-generate') ?>" class="p-6 space-y-4 text-xs">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="class" value="<?= e($selectedClass) ?>">
                    <input type="hidden" name="section" value="<?= e($selectedSection) ?>">

                    <p class="text-slate-400">Generate a timetable slot across multiple weekdays for <b class="text-white"><?= e($selectedClass) ?> - <?= e($selectedSection ?: 'Default') ?></b>.</p>

                    <!-- Days -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Select Weekdays <span class="text-red-400">*</span></label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-950 p-3 rounded-xl border border-slate-850">
                            <?php foreach ($weekDays as $day): ?>
                                <label class="flex items-center gap-2 text-slate-350 cursor-pointer">
                                    <input type="checkbox" name="days[]" value="<?= $day ?>" checked class="rounded border-slate-800 bg-slate-950 text-indigo-500 focus:ring-indigo-500">
                                    <span><?= $day ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Subject Dropdown -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Subject / Activity Name <span class="text-red-400">*</span></label>
                        <select name="subject" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 focus:outline-none focus:border-indigo-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= e($sub['name']) ?>"><?= e($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Teacher Dropdown -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Assign Teacher <span class="text-red-400">*</span></label>
                        <select name="teacher_name" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 focus:outline-none focus:border-indigo-500">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= e($teacher['name']) ?>"><?= e($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Timings -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">Start Time <span class="text-red-400">*</span></label>
                            <input type="time" name="start_time" value="09:00" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-400 font-medium">End Time <span class="text-red-400">*</span></label>
                            <input type="time" name="end_time" value="10:00" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Room -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-400 font-medium">Class Room</label>
                        <select name="room" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 focus:outline-none focus:border-indigo-500">
                            <?php foreach ($classes as $c): ?>
                                <?php $roomVal = $c['class'] . ($c['section'] ? ' - ' . $c['section'] : ''); ?>
                                <option value="<?= e($roomVal) ?>" <?= $selectedClass === $c['class'] ? 'selected' : '' ?>><?= e($roomVal) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showBulkModal = false" class="px-4 py-2 rounded-xl font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-550 transition-all shadow-md">Generate Slots</button>
                    </div>
                </form>
            </div>
        </div>
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

                <form method="POST" :action="'<?= url('academics/timetable') ?>/' + editId + '/update'" class="p-6 space-y-4 text-xs">
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
