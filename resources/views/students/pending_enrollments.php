<?php
$layout    = 'app';
$pageTitle = 'Online Enrollment Applications';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Students', 'url' => '/academics/students'],
    ['label' => 'Applications'],
];
$enrollments   = $enrollments ?? [];
$currentStatus = $currentStatus ?? 'pending';
ob_start();
?>
<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Online Enrollment Applications</h1>
            <p class="text-xs text-slate-500">Applications submitted through the public enrollment form.</p>
        </div>
        <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 gap-1 text-xs font-semibold">
            <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
                <a href="<?= url('students/enrollments?status=' . $key) ?>"
                   class="px-3 py-1.5 rounded-lg <?= $currentStatus === $key ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
        <?php if (empty($enrollments)): ?>
            <p class="p-10 text-center text-xs text-slate-500">No <?= e($currentStatus) ?> applications.</p>
        <?php else: ?>
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-slate-400 text-2xs font-bold uppercase tracking-wider">
                    <th class="px-4 py-3">Application</th>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Date of Birth</th>
                    <th class="px-4 py-3">Parents</th>
                    <th class="px-4 py-3">Submitted</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                <?php foreach ($enrollments as $en): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="px-4 py-3 font-mono text-slate-500"><?= e($en['application_code'] ?? '—') ?></td>
                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white"><?= e($en['student_full_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-slate-500"><?= !empty($en['dob']) ? e(format_date($en['dob'])) : '—' ?></td>
                    <td class="px-4 py-3 text-slate-500">
                        <?= e(implode(' / ', array_filter([(string)($en['father_name'] ?? ''), (string)($en['mother_name'] ?? '')])) ?: '—') ?>
                    </td>
                    <td class="px-4 py-3 text-slate-500"><?= !empty($en['created_at']) ? e(date('d M Y H:i', strtotime($en['created_at']))) : '—' ?></td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?= url('students/enrollments/' . (int)$en['id']) ?>" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Review →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
?>
