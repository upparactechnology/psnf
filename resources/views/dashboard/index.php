<?php
$layout    = 'app';
$pageTitle = 'Dashboard';
$breadcrumbs = [['label' => 'Dashboard']];
ob_start();
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <?php
    $cards = [
        ['label' => 'Total Students', 'value' => $stats['total_students'], 'sub' => 'All admissions', 'color' => 'from-indigo-500 to-purple-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
        ['label' => 'Enrolled',       'value' => $stats['enrolled'],       'sub' => 'Active students', 'color' => 'from-emerald-500 to-teal-600',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'New Applications','value'=> $stats['applied'],        'sub' => 'Awaiting review', 'color' => 'from-amber-500 to-orange-600',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
        ['label' => 'Staff Members',  'value' => $stats['total_users'],    'sub' => 'Active accounts', 'color' => 'from-blue-500 to-cyan-600',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    ];
    foreach ($cards as $card):
    ?>
    <div class="rounded-2xl p-5 border border-slate-800/60 bg-slate-900/40 hover:border-slate-700/60 transition-all duration-300 group">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br <?= $card['color'] ?> shadow-lg group-hover:scale-105 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $card['icon'] ?></svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-white mb-1"><?= number_format($card['value']) ?></div>
        <div class="text-sm font-semibold text-slate-300"><?= $card['label'] ?></div>
        <div class="text-xs text-slate-500 mt-0.5"><?= $card['sub'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- Admission Pipeline -->
    <div class="xl:col-span-1 rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6">
        <h3 class="text-base font-semibold text-white mb-5">Admission Pipeline</h3>
        <?php
        $pipeline = [
            ['key' => 'applied',    'label' => 'Applied',    'color' => 'bg-slate-500'],
            ['key' => 'review',     'label' => 'Under Review','color' => 'bg-yellow-500'],
            ['key' => 'assessment', 'label' => 'Assessment', 'color' => 'bg-blue-500'],
            ['key' => 'approved',   'label' => 'Approved',   'color' => 'bg-emerald-500'],
            ['key' => 'enrolled',   'label' => 'Enrolled',   'color' => 'bg-brand-500'],
        ];
        $total = array_sum($statusCounts) ?: 1;
        foreach ($pipeline as $stage):
            $count = $statusCounts[$stage['key']] ?? 0;
            $pct   = round(($count / $total) * 100);
        ?>
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm text-slate-300"><?= $stage['label'] ?></span>
                <span class="text-sm font-semibold text-white"><?= $count ?></span>
            </div>
            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-2 <?= $stage['color'] ?> rounded-full transition-all duration-700" style="width: <?= $pct ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Students -->
    <div class="xl:col-span-2 rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-white">Recent Applications</h3>
            <a href="<?= url('students') ?>" class="text-xs text-brand-400 hover:text-brand-300 font-medium transition-colors">View All →</a>
        </div>
        <?php if (empty($recentStudents)): ?>
        <div class="text-center py-10">
            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <p class="text-sm text-slate-500">No students yet. <a href="<?= url('students/create') ?>" class="text-brand-400 hover:underline">Add one →</a></p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($recentStudents as $s): ?>
            <?php
            $statusClasses = [
                'applied'    => 'bg-slate-700/50 text-slate-300',
                'review'     => 'bg-yellow-900/30 text-yellow-400',
                'assessment' => 'bg-blue-900/30 text-blue-400',
                'approved'   => 'bg-emerald-900/30 text-emerald-400',
                'enrolled'   => 'bg-indigo-900/30 text-indigo-400',
                'withdrawn'  => 'bg-red-900/30 text-red-400',
            ];
            $sc = $statusClasses[$s['admission_status']] ?? 'bg-slate-700/50 text-slate-300';
            ?>
            <a href="<?= url('students/' . $s['id']) ?>" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-800/40 transition-colors group">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0"
                     style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate group-hover:text-brand-300 transition-colors">
                        <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                    </p>
                    <p class="text-xs text-slate-500"><?= e($s['admission_number'] ?? 'No ADM#') ?> · <?= e($s['disability_type']) ?></p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium <?= $sc ?>">
                    <?= ucfirst($s['admission_status']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Activity Log -->
<?php if (!empty($recentLogs)): ?>
<div class="mt-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6">
    <h3 class="text-base font-semibold text-white mb-5">Recent Activity</h3>
    <div class="space-y-2">
        <?php foreach (array_slice($recentLogs, 0, 8) as $log): ?>
        <div class="flex items-center gap-4 py-2 border-b border-slate-800/40 last:border-0">
            <div class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0"></div>
            <span class="text-sm text-slate-300 font-medium min-w-0 flex-1 truncate"><?= e(str_replace('_', ' ', $log['event'])) ?></span>
            <span class="text-xs text-slate-500"><?= e($log['user_name'] ?? '—') ?></span>
            <span class="text-xs text-slate-600"><?= format_date($log['created_at'], 'd M H:i') ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/app.php';
?>
