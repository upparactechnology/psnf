<?php
$layout    = 'app';
$pageTitle = 'Teacher Attendance Log';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Users','url'=>'/users'],['label'=>'Teacher Attendance Log']];
ob_start();
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Teacher Attendance Log</h2>
            <p class="text-sm text-slate-500 mt-0.5">Daily dashboard check-ins by teachers</p>
        </div>
        <a href="<?= url('users') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back to User Management</a>
    </div>

    <!-- Search -->
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <form method="GET" action="<?= url('users/attendance') ?>">
            <input type="text" name="search" value="<?= e($search ?? '') ?>"
                   placeholder="Search by teacher name or status (late, on_time)..."
                   class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
        </form>
    </div>

    <!-- Table -->
    <?php if (empty($records)): ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-650" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-slate-500 text-sm">No teacher attendance records found.</p>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Teacher</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Check-in Date</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Opened At</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Scheduled Lecture</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                <?php foreach ($records as $rec): ?>
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                <?= strtoupper(substr($rec['teacher_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white"><?= e($rec['teacher_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($rec['teacher_email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-300">
                        <?= format_date($rec['attendance_date'], 'D, d M Y') ?>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-300">
                        <?= date('h:i:s A', strtotime($rec['opened_at'])) ?>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-350">
                        <?= date('h:i A', strtotime($rec['lecture_time'])) ?> <span class="text-xs text-slate-500">(+<?= $rec['grace_period'] ?>m grace)</span>
                    </td>
                    <td class="px-5 py-4">
                        <?php if ($rec['status'] === 'late'): ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-red-950/45 text-red-500 border border-red-900/50">
                            Late
                        </span>
                        <?php else: ?>
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-950/45 text-emerald-400 border border-emerald-900/50">
                            On Time
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
?>
