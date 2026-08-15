<?php $layout = 'parent'; ?>

<div class="space-y-6" x-data="{ emergencyModal: false }">

    <?php if (!$active_student): ?>
    <!-- Empty State for new Parent (No Children Enrolled yet) -->
    <div class="p-8 md:p-12 rounded-2xl border bg-white dark:bg-slate-900/40 border-slate-200 dark:border-white/5 shadow-sm text-center max-w-2xl mx-auto space-y-6 my-10 relative overflow-hidden">
        <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 w-80 h-80 rounded-full bg-purple-500/5 blur-3xl pointer-events-none"></div>
        
        <div class="w-20 h-20 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 flex items-center justify-center mx-auto shadow-sm">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        
        <div class="space-y-2">
            <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">Welcome, <?= e($guardian['name']) ?>!</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">It looks like you don't have any children linked to your parent portal account yet. To connect with the database and track attendance, progress, and reports, please submit your child's details.</p>
        </div>
        
        <div>
            <p class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
                Please contact school administration to link your child's record to this account.
            </p>
        </div>
    </div>
    <?php else: ?>

    <!-- Top Summary Banner -->
    <div class="p-6 rounded-2xl border bg-white dark:bg-slate-900/40 border-slate-200 dark:border-white/5 shadow-sm dark:shadow-2xl relative overflow-hidden">
        <!-- Background accents -->
        <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 w-80 h-80 rounded-full bg-purple-500/5 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">Hello, <?= e($guardian['name']) ?> 👋</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Here is a quick overview of your child, <strong class="text-indigo-650 dark:text-indigo-400 font-semibold"><?= e($active_student['first_name'] . ' ' . $active_student['last_name']) ?></strong>.</p>
            </div>
            <div class="flex flex-wrap gap-3.5">
                
                <button @click="emergencyModal = true"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 dark:border-slate-700/60 text-xs font-semibold text-slate-700 dark:text-white transition-all shadow-sm">
                    🚨 Update Emergency Contact
                </button>
                <a href="<?= url('parent/students/' . $active_student['id'] . '/fees') ?>"
                   class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:opacity-95 text-xs font-semibold text-white transition-all shadow-lg hover:shadow-indigo-600/25">
                    💳 Pay Fees
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Dashboard Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Attendance Widget -->
        <div class="rounded-2xl p-5 border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 flex items-center justify-between shadow-sm">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Attendance Rate</span>
                <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?= $attendanceRate ?>%</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Current semester</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-500/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Pending Fees Widget -->
        <div class="rounded-2xl p-5 border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 flex items-center justify-between shadow-sm">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Pending Dues</span>
                <h3 class="text-2xl font-bold <?= $unpaidTotal > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-300' ?>"><?= number_format($unpaidTotal, 2) ?> INR</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Invoices awaiting payment</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-500/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>


        <!-- Bus Route Widget -->
        <div class="rounded-2xl p-5 border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 flex items-center justify-between shadow-sm">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Bus Transit</span>
                <h3 class="text-lg font-bold text-purple-600 dark:text-purple-400 truncate max-w-[150px]">
                    <?= $transport ? e($transport['name']) : 'No Transit' ?>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <?php 
                    if ($transport) {
                        if (!empty($transport['trip_status'])) {
                            echo e($transport['trip_status']);
                        } else {
                            echo e(ucfirst(str_replace('_', ' ', $transport['route_status'])));
                        }
                    } else {
                        echo 'Not mapped';
                    }
                    ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-650 dark:text-purple-400 border border-purple-500/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>
            </div>
        </div>
    </div>

    <!-- Main View Section Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Child Profile Overview and Recent Activity Logs (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Child Profile Overview Card -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="w-1 h-4 bg-indigo-500 rounded"></span> Student Profile Summary
                </h3>
                <div class="flex flex-col sm:flex-row gap-5">
                    <div class="w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 flex-shrink-0 shadow border border-slate-200 dark:border-white/5">
                        <?= strtoupper(substr($active_student['first_name'], 0, 1)) ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                        <div>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500 font-bold uppercase tracking-wider">Admission Number</p>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($active_student['admission_number']) ?></p>
                        </div>
                        <div>
                            
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500 font-bold uppercase tracking-wider">Class Room / Section</p>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($active_student['class'] . ' (' . $active_student['section'] . ')') ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500 font-bold uppercase tracking-wider">Primary Blood Group</p>
                            <p class="text-sm font-semibold text-red-650 dark:text-red-300"><?= e($active_student['blood_group'] ?? 'Unknown') ?></p>
                        </div>
                    </div>
                </div>
                <!-- Special Instructions Box -->
                <div class="bg-slate-50 dark:bg-slate-950/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Care & Transition Instructions</p>
                    <p class="text-xs text-slate-750 dark:text-slate-300 leading-relaxed"><?= e($active_student['care_instructions'] ?? 'No custom transition protocols provided.') ?></p>
                </div>
            </div>

            <!-- Recent Attendance & History Logs -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-4 bg-indigo-500 rounded"></span> Recent Attendance History
                    </h3>
                    <a href="<?= url('parent/students/' . $active_student['id'] . '/attendance') ?>" class="text-xs text-brand-650 dark:text-brand-400 hover:underline">View Full Log</a>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-950/30">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800/60 text-slate-500 dark:text-slate-400">
                                <th class="p-3.5 font-semibold">Date</th>
                                <th class="p-3.5 font-semibold">Status</th>
                                <th class="p-3.5 font-semibold">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                            <?php if (empty($recentAttendance)): ?>
                            <tr>
                                <td colspan="3" class="p-4 text-center text-slate-500">No attendance records found.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentAttendance as $att): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="p-3.5 font-medium text-slate-700 dark:text-slate-300"><?= date('d M Y', strtotime($att['date'])) ?></td>
                                <td class="p-3.5">
                                    <?php
                                    $st = $att['status'];
                                    $col = $st === 'present' ? 'text-emerald-650 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/10' : ($st === 'absent' ? 'text-rose-650 dark:text-rose-400 bg-rose-500/10 border-rose-500/10' : 'text-yellow-650 dark:text-yellow-400 bg-yellow-500/10 border-yellow-500/10');
                                    ?>
                                    <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-semibold <?= $col ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-400 truncate max-w-[200px]" title="<?= e($att['remarks']) ?>"><?= e($att['remarks'] ?: '—') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Announcements & Homework Updates (1 col wide) -->
        <div class="space-y-6">

            <!-- Announcements Widget -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-4 bg-indigo-500 rounded"></span> Announcements
                    </h3>
                    <a href="<?= url('parent/announcements') ?>" class="text-xs text-brand-655 dark:text-brand-400 hover:underline">View All</a>
                </div>
                <div class="space-y-3.5">
                    <?php if (empty($announcements)): ?>
                    <p class="text-xs text-slate-500 text-center py-4">No recent announcements.</p>
                    <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                    <div class="p-3.5 rounded-xl border <?php if (($ann['priority'] ?? 'normal') === 'critical'): ?>border-red-300 dark:border-red-800/40 bg-red-50/50 dark:bg-red-950/20<?php elseif (($ann['priority'] ?? 'normal') === 'urgent'): ?>border-amber-300 dark:border-amber-800/40 bg-amber-50/50 dark:bg-amber-950/20<?php else: ?>border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20<?php endif; ?> hover:border-slate-300 dark:hover:border-slate-700 transition-colors space-y-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <?php if (($ann['priority'] ?? 'normal') === 'critical'): ?>
                                <span class="px-1.5 py-0.5 rounded bg-red-500/10 text-red-600 dark:text-red-400 text-[8px] font-bold uppercase">Critical</span>
                            <?php elseif (($ann['priority'] ?? 'normal') === 'urgent'): ?>
                                <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[8px] font-bold uppercase">Urgent</span>
                            <?php endif; ?>
                            <?php if (!empty($ann['class_name'])): ?>
                                <span class="px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 text-[8px] font-bold uppercase"><?= e($ann['class_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white leading-tight"><?= e($ann['title']) ?></h4>
                        <p class="text-[10px] text-slate-450 dark:text-slate-500"><?= date('d M Y, h:i A', strtotime($ann['published_at'])) ?></p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 pt-1.5"><?= e($ann['content']) ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Today's Timetable -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-4 bg-indigo-500 rounded"></span> Today's Timetable
                    </h3>
                    <a href="<?= url('parent/students/' . $active_student['id'] . '/timetable') ?>" class="text-xs text-brand-655 dark:text-brand-400 hover:underline">Full Schedule</a>
                </div>
                <div class="space-y-3.5">
                    <?php if (empty($timetable)): ?>
                    <p class="text-xs text-slate-500 text-center py-4">No classes scheduled for today.</p>
                    <?php else: ?>
                    <?php foreach ($timetable as $class): ?>
                    <div class="flex flex-col p-3.5 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= e($class['subject']) ?></h4>
                        <p class="text-[10px] text-slate-450 dark:text-slate-500"><?= date('h:i A', strtotime($class['start_time'])) ?> - <?= date('h:i A', strtotime($class['end_time'])) ?></p>
                        <?php if (!empty($class['teacher_name'])): ?>
                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-1"><?= e($class['teacher_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Exam Results -->
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-4 bg-indigo-500 rounded"></span> Recent Exam Results
                    </h3>
                    <a href="<?= url('parent/students/' . $active_student['id'] . '/exams') ?>" class="text-xs text-brand-655 dark:text-brand-400 hover:underline">All Exams</a>
                </div>
                <div class="space-y-3.5">
                    <?php if (empty($exams)): ?>
                    <p class="text-xs text-slate-500 text-center py-4">No recent exam results.</p>
                    <?php else: ?>
                    <?php foreach ($exams as $exam): ?>
                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= e($exam['exam_name']) ?></h4>
                            <p class="text-[10px] text-slate-450 dark:text-slate-500"><?= e($exam['subject']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?= e((float)$exam['marks_obtained']) ?> / <?= e((float)$exam['max_marks']) ?></p>
                            <?php if (!empty($exam['grade'])): ?>
                                <p class="text-[10px] text-slate-500 font-bold mt-0.5">Grade: <?= e($exam['grade']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Communication Shortcut Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-indigo-50 dark:from-slate-900 dark:to-indigo-950 border border-indigo-100 dark:border-indigo-900/30 flex items-center justify-between gap-4 shadow-sm">
                <div class="space-y-1 max-w-[70%]">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white">Need to text a teacher?</h4>
                    <p class="text-[10px] text-slate-605 dark:text-slate-400 leading-snug">Send visual concerns or transport adjustments directly through staff communication.</p>
                </div>
                <a href="<?= url('parent/communication') ?>"
                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white shadow-md transition-all">
                    Chat
                </a>
            </div>

            <!-- Interactive Learning Games Shortcut Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-fuchsia-50 to-pink-50 dark:from-fuchsia-900 dark:to-pink-950 border border-pink-100 dark:border-pink-900/30 flex items-center justify-between gap-4 shadow-sm">
                <div class="space-y-1 max-w-[70%]">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white">Interactive Learning Games</h4>
                    <p class="text-[10px] text-slate-605 dark:text-slate-400 leading-snug">Fun, accessible educational games for sentence building, money counting, and safety skills.</p>
                </div>
                <a href="<?= url('game/index.html') ?>"
                   class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-500 text-xs font-bold text-white shadow-md transition-all whitespace-nowrap">
                    Play Games
                </a>
            </div>

        </div>

    </div>

    <!-- Slide-over Modal for Emergency Contact Update -->
    <div x-show="emergencyModal" class="fixed inset-0 z-[9999] overflow-hidden" x-cloak>
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="emergencyModal = false"></div>
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800/80 shadow-2xl flex flex-col"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">

                <!-- Title header -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">🚨 Update Emergency Contact</h3>
                    <button @click="emergencyModal = false" class="text-slate-500 hover:text-slate-350 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form container -->
                <form hx-post="<?= url('parent/students/' . $active_student['id'] . '/emergency') ?>"
                      hx-swap="innerHTML"
                      hx-target="#emergency-form-result"
                      @submit="setTimeout(() => { emergencyModal = false; }, 2000)"
                      class="flex-1 overflow-y-auto p-6 space-y-5">

                    <div id="emergency-form-result"></div>

                    <!-- Contact Name -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Contact Name</label>
                        <input type="text" name="name" required
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. Priya Kumar">
                    </div>

                    <!-- Relationship -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Relationship to Child</label>
                        <input type="text" name="relationship" required
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. Mother, Uncle">
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Phone Number</label>
                        <input type="tel" name="phone" required
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. +91-9876543210">
                    </div>

                    <!-- Alternate Phone -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Alt Phone (Optional)</label>
                        <input type="tel" name="phone_alt"
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. +91-9876543211">
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Email Address</label>
                        <input type="email" name="email"
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. mother@example.com">
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl text-xs font-bold text-white shadow-lg hover:shadow-indigo-600/20 transition-all">
                        Save Contact
                    </button>
                </form>

            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
