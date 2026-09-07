<?php
$layout    = 'app';
$pageTitle = 'Academic Settings & Configuration';
$breadcrumbs = [];
$activeTab = $_GET['tab'] ?? 'years';
ob_start();
?>

<div x-data="{ tab: '<?= e($activeTab) ?>', createModal: false, closingWizard: false, addSemesterModal: false }" class="space-y-6 max-w-6xl mx-auto">

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

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <?php if ($y['status'] !== 'locked' && $y['status'] !== 'archived'): ?>
                            <form action="<?= url('academics/settings/years/' . $y['id'] . '/lock') ?>" method="POST" class="inline" onsubmit="return confirm('Lock this academic year? Marks and attendance will be frozen.')">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="tab" value="years">
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all" title="Lock this year">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Lock
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($y['status'] !== 'archived'): ?>
                            <form action="<?= url('academics/settings/years/' . $y['id'] . '/archive') ?>" method="POST" class="inline" onsubmit="return confirm('Archive this academic year? It will be hidden from default selections.')">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="tab" value="years">
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" title="Archive this year">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    Archive
                                </button>
                            </form>
                        <?php endif; ?>

                        <form action="<?= url('academics/settings/years/' . $y['id'] . '/copy') ?>" method="POST" class="inline" onsubmit="return confirm('Copy curriculum templates and subjects from the previous academic year to this year?')">
                            <?= \Core\View::csrf() ?>
                            <input type="hidden" name="tab" value="years">
                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all" title="Copy data from previous year">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Copy Data
                            </button>
                        </form>

                        <a href="<?= url('academics/report-cards?academic_year=' . urlencode($y['year_name'])) ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all" title="View report cards">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Report
                        </a>

                        <form action="<?= url('academics/settings/years/' . $y['id'] . '/delete') ?>" method="POST" class="inline" onsubmit="return confirm('WARNING: Are you sure you want to delete this academic year? This will delete all associated student promotion records, classes, and exams.')">
                            <?= \Core\View::csrf() ?>
                            <input type="hidden" name="tab" value="years">
                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all" title="Delete this year">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab 2: Semesters -->
    <div x-show="tab === 'semesters'" class="space-y-4" x-cloak>
        <!-- Year Selector for Semesters -->
        <div class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Academic Year:</label>
            <select onchange="window.location.href='<?= url('academics/settings?tab=semesters&year_id=') ?>' + this.value" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y['id'] ?>" <?= (int)$y['id'] === (int)$selectedYearId ? 'selected' : '' ?>><?= e($y['year_name']) ?> <?= $y['status'] === 'current' ? '(Current)' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Semesters for <?= e($selectedYear['year_name'] ?? '') ?></h3>
                    <p class="text-xs text-slate-500 mt-0.5">Define semesters/terms for this academic year.</p>
                </div>
                <button @click="addSemesterModal = true" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">+ Add Semester</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-medium">
                <?php if (!empty($semesters)): ?>
                    <?php foreach ($semesters as $sem): ?>
                            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white"><?= e($sem['name']) ?></h4>
                                <p class="text-slate-450 text-2xs mt-0.5"><?= e($sem['date_range'] ?: 'No dates configured') ?></p>
                                <?php if (!empty($sem['start_date']) && !empty($sem['end_date'])): ?>
                                    <p class="text-slate-450 text-2xs mt-0.5"><?= e($sem['start_date']) ?> to <?= e($sem['end_date']) ?> · <?= (int)($sem['total_working_days'] ?? 0) ?> working days</p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-3xs font-bold <?= $sem['status'] === 'OPEN' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-400' ?>">
                                    <?= e($sem['status']) ?>
                                </span>
                                <form action="<?= url('academics/settings/semesters/' . $sem['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this semester?')" class="inline">
                                    <?= \Core\View::csrf() ?>
                                    <input type="hidden" name="academic_year_id" value="<?= (int)$selectedYearId ?>">
                                    <button type="submit" class="text-red-500 font-bold hover:underline text-2xs">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-2 p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-400 text-xs">
                        No semesters configured for this year. Click "+ Add Semester" to create one.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Semester Modal -->
        <div x-show="addSemesterModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="addSemesterModal = false"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Semester</h3>
                <form action="<?= url('academics/settings/semesters') ?>" method="POST" class="space-y-4 text-xs">
                    <?= \Core\View::csrf() ?>
                    <input type="hidden" name="academic_year_id" value="<?= (int)$selectedYearId ?>">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Semester Name</label>
                        <input type="text" name="name" placeholder="e.g. Semester 1" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Date Range</label>
                        <input type="text" name="date_range" placeholder="e.g. June – November 2026" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Working Days</label>
                        <input type="number" name="total_working_days" min="0" max="365" placeholder="e.g. 120" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="addSemesterModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Semester</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 3: Calendar & Holidays -->
    <?php 
    // Check if dates are valid (not zero dates like 0001-11-00)
    $hasValidDates = $selectedYear 
        && !empty($selectedYear['start_date']) 
        && !empty($selectedYear['end_date'])
        && $selectedYear['start_date'] !== '0000-00-00'
        && $selectedYear['start_date'] !== '0001-11-00'
        && $selectedYear['end_date'] !== '0000-00-00'
        && $selectedYear['end_date'] !== '0001-11-00';

    if ($hasValidDates) {
        $calStart = new DateTime($selectedYear['start_date']);
        $calEnd   = new DateTime($selectedYear['end_date']);
    } elseif ($selectedYear && preg_match('/(\d{4})/', $selectedYear['year_name'] ?? '', $m)) {
        $calStart = new DateTime($m[0] . '-06-01');
        $calEnd   = new DateTime(($m[0] + 1) . '-05-31');
    } else {
        $calStart = new DateTime('2026-06-01');
        $calEnd   = new DateTime('2027-05-31');
    }
    $today = new DateTime();
    $calMonths = [];
    $iter = new DateTime($calStart->format('Y-m-01'));
    while ($iter <= $calEnd) {
        $calMonths[] = $iter->format('Y-m');
        $iter->modify('+1 month');
    }
    $todayYM = $today->format('Y-m');
    $defaultMonth = (in_array($todayYM, $calMonths)) ? $todayYM : ($calMonths[0] ?? $today->format('Y-m'));
    $todayStr = $today->format('Y-m-d');
    if (empty($calMonths)) {
        $calMonths[] = $todayYM;
        $defaultMonth = $todayYM;
    }
    $calDataJson = json_encode([
        'defaultMonth' => $defaultMonth,
        'todayStr' => $todayStr,
        'months' => $calMonths,
        'yearName' => $selectedYear['year_name'] ?? 'N/A',
        'rangeLabel' => $calStart->format('M Y') . ' – ' . $calEnd->format('M Y'),
    ]);
    ?>
    <div x-show="tab === 'calendar'" x-cloak class="space-y-4" x-data="calendarApp()" x-init="$nextTick(() => init(<?= htmlspecialchars($calDataJson, ENT_QUOTES) ?>))">
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-5">
            <!-- Calendar Header & View Mode Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Academic Calendar & Events</h3>
                    <p class="text-2xs text-slate-500 mt-0.5">Based on <span class="font-semibold text-indigo-500" x-text="yearName"></span> (<span x-text="rangeLabel"></span>)</p>
                </div>
                
                <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs">
                    <button @click="viewMode = 'day'" :class="viewMode === 'day' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Day</button>
                    <button @click="viewMode = 'week'" :class="viewMode === 'week' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Week</button>
                    <button @click="viewMode = 'month'" :class="viewMode === 'month' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Month</button>
                    <button @click="viewMode = 'year'" :class="viewMode === 'year' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:text-slate-850 dark:hover:text-slate-200'" class="px-3 py-1.5 rounded-lg transition-all">Year</button>
                </div>
            </div>

            <!-- VIEW 1: MONTH VIEW GRID (dynamic) -->
            <div x-show="viewMode === 'month'" class="space-y-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                    <div class="flex items-center gap-3">
                        <button @click="let [y,m]=calMonth.split('-').map(Number); m--; if(m<1){m=12;y--;} calMonth=y+'-'+String(m).padStart(2,'0');" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition-all">&larr;</button>
                        <span x-text="monthName(calMonth)" class="min-w-[140px] text-center"></span>
                        <button @click="let [y,m]=calMonth.split('-').map(Number); m++; if(m>12){m=1;y++;} calMonth=y+'-'+String(m).padStart(2,'0');" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition-all">&rarr;</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="goToday()" x-show="!isCurrentMonth()" class="px-3 py-1.5 rounded-lg text-2xs font-bold border border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all">Today</button>
                        <span class="text-2xs font-normal text-slate-400">Click any date to add events</span>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-2xs font-bold text-slate-400 uppercase py-1 border-b border-slate-100 dark:border-slate-800">
                    <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                </div>
                <div class="grid grid-cols-7 gap-1.5 text-xs font-medium">
                    <template x-for="blank in firstDayOfMonth(calMonth)" :key="'b'+blank">
                        <div></div>
                    </template>
                    <template x-for="d in daysInMonth(calMonth)" :key="d">
                        <div @click="selectedDate = dateStr(calMonth, d); addEventModal = true"
                             :class="isToday(d) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-400/40' : 'border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/20 hover:border-indigo-500/50'"
                             class="min-h-[64px] p-1.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between">
                            <span :class="isToday(d) ? 'bg-indigo-600 text-white w-5 h-5 rounded-full flex items-center justify-center' : ''" class="font-mono text-2xs font-bold text-slate-700 dark:text-slate-300" x-text="d"></span>
                            <div class="space-y-0.5">
                                <template x-for="(ev, i) in events.filter(e => e.date === dateStr(calMonth, d))" :key="i">
                                    <span class="block text-[9px] px-1 py-0.5 rounded bg-indigo-500/20 text-indigo-400 font-bold truncate" x-text="ev.title"></span>
                                </template>
                            </div>
                        </div>
                    </template>
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

            <!-- VIEW 3: WEEK VIEW (dynamic) -->
            <div x-show="viewMode === 'week'" class="space-y-4" x-cloak>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs" x-text="'Week of ' + selectedDate"></h4>
                <div class="grid grid-cols-7 gap-2 text-xs">
                    <template x-for="wd in (() => {
                        let base = new Date(selectedDate + 'T00:00:00');
                        let day = base.getDay();
                        let start = new Date(base); start.setDate(base.getDate() - day);
                        return Array.from({length: 7}, (_, i) => {
                            let d = new Date(start); d.setDate(start.getDate() + i);
                            return d.toISOString().slice(0,10);
                        });
                    })()" :key="wd">
                        <div @click="selectedDate = wd; addEventModal = true" 
                             :class="wd === todayStr ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-400/40' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30'"
                             class="min-h-[80px] p-2.5 rounded-xl border space-y-1.5 cursor-pointer hover:border-indigo-500/50">
                            <span :class="wd === todayStr ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500'" class="font-bold text-2xs" x-text="new Date(wd+'T00:00:00').toLocaleDateString('en-US', {weekday:'short', day:'numeric'})"></span>
                            <template x-for="(ev, i) in events.filter(e => e.date === wd)" :key="i">
                                <span class="block text-[9px] p-1 rounded bg-indigo-500/20 text-indigo-400 font-bold truncate" x-text="ev.title"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- VIEW 4: YEAR VIEW (dynamic months) -->
            <div x-show="viewMode === 'year'" class="space-y-4" x-cloak>
                <h4 class="font-bold text-slate-900 dark:text-white text-xs" x-text="'Academic Year ' + yearName"></h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <template x-for="cm in academicMonths" :key="cm">
                        <div @click="calMonth = cm; viewMode = 'month'" 
                             :class="cm === todayStr.substring(0,7) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-400/40' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 hover:border-indigo-500'"
                             class="p-4 rounded-xl border text-center space-y-1.5 cursor-pointer transition-all hover:shadow-md">
                            <span :class="cm === todayStr.substring(0,7) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-800 dark:text-white'" class="font-bold" x-text="monthName(cm)"></span>
                            <template x-if="cm === todayStr.substring(0,7)">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">Current</span>
                            </template>
                            <template x-if="cm !== todayStr.substring(0,7)">
                                <p class="text-2xs text-indigo-500 font-medium">Open Month &rarr;</p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Add Event Modal -->
        <div x-show="addEventModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="addEventModal = false"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Event</h3>
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
                    <button @click="addEventModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button @click="saveEvent()" class="px-4 py-2 rounded-xl text-xs bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Event</button>
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

<script>
function calendarApp() {
    return {
        viewMode: 'month',
        calMonth: '',
        selectedDate: '',
        todayStr: '',
        academicMonths: [],
        yearName: '',
        rangeLabel: '',
        addEventModal: false,
        newEvent: { title: '', type: 'event', time: '09:00 AM' },
        events: [],
        init(data) {
            this.calMonth = data.defaultMonth;
            this.selectedDate = data.todayStr;
            this.todayStr = data.todayStr;
            this.academicMonths = data.months;
            this.yearName = data.yearName;
            this.rangeLabel = data.rangeLabel;
        },
        goToday() {
            const now = new Date();
            this.calMonth = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
            this.selectedDate = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
        },
        isToday(d) {
            return this.dateStr(this.calMonth, d) === this.todayStr;
        },
        isCurrentMonth() {
            const now = new Date();
            return this.calMonth === now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
        },
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
        },
        monthName(ym) {
            const parts = ym.split('-');
            const names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            return names[parseInt(parts[1])-1] + ' ' + parts[0];
        },
        daysInMonth(ym) {
            const parts = ym.split('-').map(Number);
            return new Date(parts[0], parts[1], 0).getDate();
        },
        firstDayOfMonth(ym) {
            const parts = ym.split('-').map(Number);
            return new Date(parts[0], parts[1]-1, 1).getDay();
        },
        dateStr(ym, d) {
            const parts = ym.split('-');
            return parts[0] + '-' + parts[1] + '-' + String(d).padStart(2, '0');
        }
    }
}
</script>

<?php
$content = ob_get_clean();
?>
