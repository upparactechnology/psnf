<?php
$layout    = 'app';
$pageTitle = 'Promotion Wizard';
$breadcrumbs = [];
ob_start();
?>

<div class="max-w-6xl mx-auto space-y-6 py-2" x-data="{
    srcClassId: '<?= $srcClassId ?: '' ?>',
    destGroupId: '',
    srcGroupId: '<?= $srcGroupId ?: '' ?>',
    checkGroupWarning() {
        if (this.srcGroupId && this.destGroupId && this.srcGroupId != this.destGroupId) {
            alert('⚠️ Warning: You are promoting students to a different Main Group category. Their active curriculum, subjects, and report card layout will change. Do you want to continue?');
        }
    }
}">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Promotion Wizard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Roll over student enrollments to new Academic Years and Classes</p>
        </div>
    </div>

    <!-- Filters Selection Panel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Source Class Selection -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Source Configuration</h3>
            <form method="GET" action="<?= url('academics/promotion') ?>" class="space-y-4 text-xs">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-350">Select Source Class</label>
                    <select name="src_class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $srcClassId ? 'selected' : '' ?>><?= e($c['name']) ?> (Section: <?= e($c['section'] ?: '—') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Destination Class Selection -->
        <?php if ($srcClassId): ?>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Destination Configuration</h3>
            <form action="<?= url('academics/promotion/run') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="src_class_id" value="<?= $srcClassId ?>">

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-350">Destination Academic Year</label>
                    <select name="dest_year_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                        <option value="">-- Select Year --</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y['id'] ?>"><?= e($y['year_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-350">Destination Main Group</label>
                    <select name="dest_group_id" x-model="destGroupId" @change="checkGroupWarning()" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                        <option value="">-- Select Main Group --</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-350">Destination Class</label>
                    <select name="dest_class_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (Section: <?= e($c['section'] ?: '—') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Students Selection List inside Destination Box for layout convenience -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Select Students to Promote</h4>
                    
                    <?php if (empty($students)): ?>
                        <p class="text-3xs text-slate-400 text-center py-4">No active students in this class.</p>
                    <?php else: ?>
                        <div class="space-y-2 max-h-[250px] overflow-y-auto pr-1">
                            <?php foreach ($students as $s): ?>
                                <div class="flex items-center gap-3 p-2 rounded-xl border border-slate-100 dark:border-slate-850 bg-slate-50/20 dark:bg-slate-900/40">
                                    <input type="checkbox" name="actions[<?= $s['id'] ?>]" value="promote" checked class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-extrabold text-slate-900 dark:text-white truncate"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></p>
                                        <p class="text-[9px] text-slate-450 font-mono">Roll: <?= e($s['roll_number'] ?: '—') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-white shadow transition-all">
                                Execute Promotion &rarr;
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="p-6 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 text-center text-slate-400 text-xs py-12 flex flex-col items-center justify-center">
                <span>Select a Source Class from the left to start promoting students.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
