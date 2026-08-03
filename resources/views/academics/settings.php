<?php
$layout    = 'app';
$pageTitle = 'Academic Settings & Configuration';
$breadcrumbs = [];
$activeTab = $_GET['tab'] ?? 'years';
ob_start();
?>

<div x-data="{ tab: '<?= e($activeTab) ?>', createModal: false, closingWizard: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Year Closing Quick Launcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Academic Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage Academic Years, Semesters, Calendar Schedule & Session Lock Controls</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="closingWizard = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-amber-600 to-indigo-600 hover:opacity-95 transition-all shadow-md">
                🔒 Close Academic Year (Wizard)
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-1 text-xs">
        <button @click.prevent="tab = 'years'; window.history.replaceState({}, '', '<?= url('academics/settings?tab=years') ?>')" :class="tab === 'years' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Academic Years</button>
        <button @click.prevent="tab = 'semesters'; window.history.replaceState({}, '', '<?= url('academics/settings?tab=semesters') ?>')" :class="tab === 'semesters' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Semesters</button>
        <button @click.prevent="tab = 'calendar'; window.history.replaceState({}, '', '<?= url('academics/settings?tab=calendar') ?>')" :class="tab === 'calendar' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Academic Calendar</button>
        <button @click.prevent="tab = 'lock'; window.history.replaceState({}, '', '<?= url('academics/settings?tab=lock') ?>')" :class="tab === 'lock' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Academic Session Lock</button>
    </div>

    <!-- Tab 1: Academic Years -->
    <div x-show="tab === 'years'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Academic Years Directory</h3>
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">+ Create New Academic Year</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($years as $y): ?>
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-mono font-bold text-indigo-500"><?= e($y['year_name']) ?></span>
                        <?php if ($y['status'] === 'current'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-emerald-500/10 text-emerald-500">Current (Active)</span>
                        <?php elseif ($y['status'] === 'locked'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-amber-500/10 text-amber-500">🔒 Locked</span>
                        <?php elseif ($y['status'] === 'archived'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-slate-500/10 text-slate-400">Archived</span>
                        <?php else: ?>
                            <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-slate-500/10 text-slate-400"><?= ucfirst($y['status']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500">Contains curriculum mapping, active classes, enrolled students, assessment records & report cards.</p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <?php if ($y['status'] !== 'locked' && $y['status'] !== 'archived'): ?>
                            <form action="<?= url('academics/settings/years/' . $y['id'] . '/lock') ?>" method="POST" class="inline" onsubmit="return confirm('Lock this academic year? Marks and attendance will be frozen.')">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="tab" value="years">
                                <button type="submit" class="text-3xs text-amber-500 font-bold hover:underline">Lock</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($y['status'] !== 'archived'): ?>
                            <form action="<?= url('academics/settings/years/' . $y['id'] . '/archive') ?>" method="POST" class="inline" onsubmit="return confirm('Archive this academic year? It will be hidden from default selections.')">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="tab" value="years">
                                <button type="submit" class="text-3xs text-slate-500 font-bold hover:underline">Archive</button>
                            </form>
                        <?php endif; ?>

                        <form action="<?= url('academics/settings/years/' . $y['id'] . '/copy') ?>" method="POST" class="inline" onsubmit="return confirm('Copy curriculum templates and subjects from the previous academic year to this year?')">
                            <?= \Core\View::csrf() ?>
                            <input type="hidden" name="tab" value="years">
                            <button type="submit" class="text-3xs text-indigo-500 font-bold hover:underline">Copy Prev Year Data</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab 2: Semesters -->
    <div x-show="tab === 'semesters'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Semester Configuration</h3>
                <p class="text-xs text-slate-500 mt-0.5">Define semesters/terms for evaluation scheduling and reporting milestones.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-medium">
                <?php if (empty($semesters)): ?>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">Semester 1 (First Term)</h4>
                            <p class="text-slate-450 text-2xs mt-0.5">June – November</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-emerald-500/10 text-emerald-500">OPEN</span>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">Semester 2 (Second Term)</h4>
                            <p class="text-slate-450 text-2xs mt-0.5">December – April</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold bg-slate-500/10 text-slate-450">UPCOMING</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($semesters as $sem): ?>
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white"><?= e($sem['name']) ?></h4>
                                <p class="text-slate-450 text-2xs mt-0.5"><?= e($sem['date_range'] ?: 'No dates configured') ?></p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold <?= $sem['status'] === 'OPEN' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-400' ?>">
                                <?= e($sem['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 3: Calendar & Holidays -->
    <div x-show="tab === 'calendar'" x-cloak class="space-y-4" x-data="{
        viewMode: 'month',
        selectedDate: '2026-07-28',
        addEventModal: false,
        newEvent: { title: '', type: 'event', time: '09:00 AM' },
        events: [
            { date: '2026-07-15', title: 'Parent Teacher Meeting', type: 'meeting', time: '10:00 AM' },
            { date: '2026-07-28', title: 'Mid-Term Evaluation Exams', type: 'exam', time: '09:00 AM' },
            { date: '2026-07-28', title: 'Sensory Integration Workshop', type: 'workshop', time: '02:00 PM' },
            { date: '2026-08-15', title: 'Independence Day Holiday', type: 'holiday', time: 'All Day' },
            { date: '2026-08-20', title: 'IEP Milestone Progress Review', type: 'review', time: '11:30 AM' }
        ],
        openDateBox(dateStr) {
            this.selectedDate = dateStr;
            this.addEventModal = true;
        },
        saveEvent() {
            if (this.newEvent.title.trim()) {
                this.events.push({
                    date: this.selectedDate,
                    title: this.newEvent.title,
                    type: this.newEvent.type,
                    time: this.newEvent.time
                });
                this.newEvent.title = '';
                this.addEventModal = false;
            }
        }
    }">
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-5">
            <!-- Calendar Header & View Mode Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Academic Calendar & Events Schedule</h3>
                    <p class="text-2xs text-slate-500 mt-0.5">Click any date box to add an event or view day details</p>
                </div>
                
                <!-- View Mode Pills (Day, Week, Month, Year) -->
                <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs">
                    <button @click="viewMode = 'day'" :class="viewMode === 'day' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Day</button>
                    <button @click="viewMode = 'week'" :class="viewMode === 'week' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Week</button>
                    <button @click="viewMode = 'month'" :class="viewMode === 'month' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Month</button>
                    <button @click="viewMode = 'year'" :class="viewMode === 'year' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Year</button>
                </div>
            </div>

            <!-- VIEW 1: MONTH VIEW GRID -->
            <div x-show="viewMode === 'month'" class="space-y-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>July 2026</span>
                    <span class="text-2xs font-normal text-slate-400">Click any date box to add / view events</span>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-2xs font-bold text-slate-400 uppercase py-1 border-b border-slate-100 dark:border-slate-800">
                    <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                </div>
                <div class="grid grid-cols-7 gap-1.5 text-xs font-medium">
                    <?php for ($d = 1; $d <= 31; $d++): 
                        $dateStr = sprintf('2026-07-%02d', $d);
                        $isToday = ($d == 28);
                    ?>
                    <div @click="openDateBox('<?= $dateStr ?>')" 
                         class="min-h-[64px] p-1.5 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/20 hover:border-indigo-500/50 transition-all cursor-pointer flex flex-col justify-between <?= $isToday ? 'ring-2 ring-indigo-500 bg-indigo-500/10' : '' ?>">
                        <span class="font-mono text-2xs font-bold <?= $isToday ? 'text-indigo-500' : 'text-slate-700 dark:text-slate-300' ?>"><?= $d ?></span>
                        <div class="space-y-0.5">
                            <template x-for="ev in events.filter(e => e.date === '<?= $dateStr ?>')">
                                <span class="block text-[9px] px-1 py-0.5 rounded bg-indigo-500/20 text-indigo-400 font-bold truncate" x-text="ev.title"></span>
                            </template>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- VIEW 2: DAY VIEW -->
            <div x-show="viewMode === 'day'" class="space-y-4" x-cloak>
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Day Schedule: <span class="font-mono text-indigo-500" x-text="selectedDate"></span></h4>
                        <p class="text-2xs text-slate-400">Showing all events and exams scheduled for this date</p>
                    </div>
                    <button @click="addEventModal = true" class="px-3 py-1.5 rounded-xl text-2xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">+ Add Event</button>
                </div>
                <div class="space-y-2 text-xs">
                    <template x-for="ev in events.filter(e => e.date === selectedDate)">
                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white" x-text="ev.title"></p>
                                    <span class="text-2xs font-mono text-slate-400" x-text="ev.time"></span>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500 uppercase" x-text="ev.type"></span>
                        </div>
                    </template>
                    <div x-show="events.filter(e => e.date === selectedDate).length === 0" class="p-6 text-center text-slate-400 text-xs">
                        No events scheduled for this day. Click "+ Add Event" to schedule.
                    </div>
                </div>
            </div>

            <!-- VIEW 3: WEEK VIEW -->
            <div x-show="viewMode === 'week'" class="space-y-4" x-cloak>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs">Week Overview (July 26 – Aug 01, 2026)</h4>
                <div class="grid grid-cols-7 gap-2 text-xs">
                    <?php 
                    $weekDays = ['2026-07-26', '2026-07-27', '2026-07-28', '2026-07-29', '2026-07-30', '2026-07-31', '2026-08-01'];
                    foreach ($weekDays as $idx => $wdStr):
                        $isTodayWd = str_contains($wdStr, '28');
                    ?>
                    <div @click="openDateBox('<?= $wdStr ?>')" class="min-h-[80px] p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30 space-y-1.5 cursor-pointer hover:border-indigo-500/50 <?= $isTodayWd ? 'ring-2 ring-indigo-500 bg-indigo-500/10' : '' ?>">
                        <span class="font-bold text-2xs text-slate-500"><?= date('D d', strtotime($wdStr)) ?></span>
                        <template x-for="ev in events.filter(e => e.date === '<?= $wdStr ?>')">
                            <span class="block text-[9px] p-1 rounded bg-indigo-500/20 text-indigo-400 font-bold truncate" x-text="ev.title"></span>
                        </template>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- VIEW 4: YEAR VIEW -->
            <div x-show="viewMode === 'year'" class="space-y-4" x-cloak>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs">Academic Year 2026-2027 Overview</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <?php 
                    $months = ['June 2026', 'July 2026', 'August 2026', 'September 2026', 'October 2026', 'November 2026', 'December 2026', 'January 2027', 'February 2027', 'March 2027', 'April 2027', 'May 2027'];
                    foreach ($months as $m):
                    ?>
                    <div @click="viewMode = 'month'" class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-center space-y-1.5 cursor-pointer hover:border-indigo-500 transition-all hover:shadow-md">
                        <span class="font-bold text-slate-800 dark:text-white"><?= $m ?></span>
                        <p class="text-2xs text-indigo-500 font-medium">Open Month &rarr;</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Add Event Modal -->
        <div x-show="addEventModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="addEventModal = false"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Event Details & Scheduling</h3>
                    <button @click="addEventModal = false" class="text-slate-400 hover:text-slate-650">✕</button>
                </div>

                <div class="text-xs space-y-3">
                    <div>
                        <span class="text-slate-450">Selected Date:</span>
                        <span class="font-bold text-indigo-500 font-mono" x-text="selectedDate"></span>
                    </div>

                    <div x-show="events.filter(e => e.date === selectedDate).length > 0" class="space-y-2">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Scheduled Events:</span>
                        <template x-for="ev in events.filter(e => e.date === selectedDate)">
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between">
                                <span class="font-bold text-slate-900 dark:text-white" x-text="ev.title"></span>
                                <span class="text-2xs font-mono text-slate-450" x-text="ev.time"></span>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <span class="font-bold text-slate-700 dark:text-slate-300">+ Add New Event</span>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Event Title</label>
                            <input type="text" x-model="newEvent.title" placeholder="e.g. Science Fair & Exhibition" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Type</label>
                                <select x-model="newEvent.type" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                                    <option value="exam">Exam</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Time</label>
                                <input type="text" x-model="newEvent.time" placeholder="09:00 AM" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="addEventModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Close</button>
                    <button type="button" @click="saveEvent()" class="px-4 py-2 rounded-xl text-xs bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Event</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Academic Session Lock Policy -->
    <div x-show="tab === 'lock'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 space-y-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🔒</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Academic Session Lock Control</h3>
                    <p class="text-xs text-slate-500">Locking a semester or academic year prevents teachers and staff from modifying past attendance, marks, or report cards.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500">Lock Active Semester (Semester 1)</button>
            </div>
        </div>

        <!-- Lecture wise attendance settings -->
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Lecture-Wise Attendance Policy Settings</h3>
            <p class="text-xs text-slate-500">Configure grace timing for classroom lecture-based student/teacher tracking (does not reflect in salary).</p>
            
            <form action="<?= url('academics/settings/save-attendance-settings') ?>" method="POST" class="space-y-4 max-w-sm">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Lecture-Wise Grace Window (Minutes)</label>
                    <input type="number" name="lec_grace_minutes" value="<?= (int)($lecGraceMinutes ?? 5) ?>" 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 font-mono">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">Save Attendance Policy</button>
            </form>
        </div>
    </div>

    <!-- Year Closing Wizard Modal -->
    <div x-show="closingWizard" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="closingWizard = false"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5 z-10">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">End-of-Year Rollover & Close Year</h3>
                <button @click="closingWizard = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <p class="text-xs text-slate-500">This automated wizard executes the end-of-year batch operations cleanly:</p>

            <div class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-350">
                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                    <span class="text-emerald-500 font-bold">✓</span> Lock Attendance Log Records
                </div>
                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                    <span class="text-emerald-500 font-bold">✓</span> Lock Exam & Mark Sheets
                </div>
                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                    <span class="text-emerald-500 font-bold">✓</span> Freeze and Publish Final Report Cards
                </div>
            </div>

            <form action="<?= url('academics/settings/wizard/close-year') ?>" method="POST" class="pt-3 flex items-center justify-end gap-2">
                <?= \Core\View::csrf() ?>
                <button type="button" @click="closingWizard = false" class="px-4 py-2 rounded-xl text-xs text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">[ Finish & Close Year ]</button>
            </form>
        </div>
    </div>

    <!-- Create Year Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="createModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create Academic Year</h3>
            
            <form action="<?= url('academics/settings/years') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Academic Year Name</label>
                    <input type="text" name="year_name" placeholder="e.g. 2027-28" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Year</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
