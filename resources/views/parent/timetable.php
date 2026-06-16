<?php $layout = 'parent'; ?>

<div class="space-y-6" x-data="{ activeDay: 'Monday' }">

    <!-- Title and Day Switcher -->
    <div class="p-5 rounded-2xl border bg-slate-900/30 border-white/5 space-y-4">
        <div>
            <h3 class="text-base font-bold text-white">Class Timetable</h3>
            <p class="text-xs text-slate-500">Weekly schedule of learning therapy periods & class sessions</p>
        </div>

        <!-- Day Selector Tabs -->
        <div class="flex flex-wrap gap-2 p-1 rounded-xl bg-slate-950/60 border border-slate-900">
            <?php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            foreach ($days as $day) {
                echo "<button @click=\"activeDay = '$day'\"
                              :class=\"activeDay === '$day' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'\"
                              class=\"flex-1 min-w-[80px] px-3 py-2 rounded-lg text-xs font-semibold transition-all\">
                          $day
                      </button>";
            }
            ?>
        </div>
    </div>

    <!-- Timetable Periods -->
    <div class="space-y-4">
        <?php foreach ($days as $day): ?>
        <div x-show="activeDay === '<?= $day ?>'" x-transition class="space-y-4">
            <?php if (empty($timetableByDay[$day])): ?>
            <div class="p-10 rounded-2xl border border-dashed border-slate-800 bg-slate-900/10 text-center">
                <svg class="w-8 h-8 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-slate-500">No periods scheduled for this day.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($timetableByDay[$day] as $idx => $period): ?>
                <div class="rounded-xl border border-white/5 bg-slate-900/20 p-5 flex items-start gap-4 hover:border-slate-800 transition-colors">
                    <!-- Period Number Badge -->
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        <?= $idx + 1 ?>
                    </div>
                    <!-- Details -->
                    <div class="space-y-1 min-w-0 flex-1">
                        <h4 class="text-sm font-bold text-white leading-tight"><?= e($period['subject']) ?></h4>
                        <p class="text-xs text-slate-400 font-medium"><?= e($period['teacher_name']) ?></p>
                        <div class="flex items-center gap-4 pt-2 text-[10px] text-slate-500 font-semibold uppercase tracking-wider">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?= date('h:i A', strtotime($period['start_time'])) ?> - <?= date('h:i A', strtotime($period['end_time'])) ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <?= e($period['room']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php foreach ($timetableByDay as $dayName => $pList): ?>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

</div>
