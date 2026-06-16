<?php
$layout    = 'app';
$pageTitle = 'Students';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Students']];
ob_start();
$disabilityColors = [
    'ASD'                    => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-700/30',
    'ADHD'                   => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-700/30',
    'Down Syndrome'          => 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-900/30 dark:text-pink-400 dark:border-pink-700/30',
    'Cerebral Palsy'         => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-700/30',
    'Dyslexia'               => 'bg-yellow-50 text-yellow-705 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-700/30',
    'Intellectual Disability'=> 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-700/30',
    'Multiple Disabilities'  => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-700/30',
    'Other'                  => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/30',
];
$statusClasses = [
    'applied'    => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/30',
    'review'     => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-700/30',
    'assessment' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-700/30',
    'approved'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-700/30',
    'enrolled'   => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-700/30',
    'withdrawn'  => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-700/30',
    'graduated'  => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-700/30',
];
?>

<div x-data="studentsList()" class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Student Registry</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= number_format($total ?? 0) ?> students total</p>
        </div>
        <?php if (has_permission('create_students')): ?>
        <a href="<?= url('students/create') ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90"
           style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Student
        </a>
        <?php endif; ?>
    </div>

    <!-- Status Pipeline Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <?php
        $allStatuses = ['all' => 'All', 'applied' => 'Applied', 'review' => 'Review', 'assessment' => 'Assessment', 'approved' => 'Approved', 'enrolled' => 'Enrolled', 'withdrawn' => 'Withdrawn'];
        $currentStatus = $filters['status'] ?? '';
        foreach ($allStatuses as $key => $label):
            $count = $key === 'all' ? array_sum($statuses) : ($statuses[$key] ?? 0);
            $active = ($key === 'all' && !$currentStatus) || $key === $currentStatus;
        ?>
        <a href="<?= url('students?' . http_build_query(array_merge($filters, ['status' => $key === 'all' ? '' : $key, 'search' => $search ?? '']))) ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap transition-all <?= $active ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-600/20 dark:text-brand-400 border border-brand-500/20 dark:border-brand-500/30' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 border border-transparent' ?>">
            <?= $label ?>
            <span class="text-xs px-1.5 py-0.5 rounded-md <?= $active ? 'bg-brand-500/20 text-brand-600 dark:bg-brand-700/40' : 'bg-slate-100 dark:bg-slate-800' ?>"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" id="student_search" placeholder="Search by name, ADM number, or GR number..."
                   value="<?= e($search ?? '') ?>"
                   class="w-full bg-white dark:bg-slate-900/70 border border-slate-300 dark:border-slate-700/60 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"
                   hx-get="<?= url('students') ?>"
                   hx-target="#student-table"
                   hx-trigger="keyup changed delay:400ms"
                   hx-push-url="true"
                   name="search">

            <div class="htmx-indicator absolute inset-y-0 right-0 pr-3.5 flex items-center">
                <div class="w-4 h-4 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>

        <select name="disability" id="disability_filter"
                class="bg-white dark:bg-slate-900/70 border border-slate-300 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all"
                hx-get="<?= url('students') ?>"
                hx-target="#student-table"
                hx-trigger="change"
                hx-push-url="true">
            <option value="">All Disabilities</option>
            <?php foreach (['ASD','ADHD','Down Syndrome','Cerebral Palsy','Dyslexia','Intellectual Disability','Hearing Impairment','Visual Impairment','Multiple Disabilities','Other'] as $d): ?>
            <option value="<?= $d ?>" <?= ($filters['disability'] ?? '') === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Table -->
    <div id="student-table">
        <?php if (empty($data)): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-16 text-center shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No Students Found</h3>
            <p class="text-slate-500 text-sm mb-6">Get started by adding the first student application.</p>
            <?php if (has_permission('create_students')): ?>
            <a href="<?= url('students/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-md hover:opacity-95" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add First Student
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 overflow-hidden shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/20">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">ADM No.</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Disability</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden xl:table-cell">Branch</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                    <?php foreach ($data as $student): ?>
                    <?php
                    $sc  = $statusClasses[$student['admission_status']] ?? 'bg-slate-700/50 text-slate-300 border-slate-600/30';
                    $dc  = $disabilityColors[$student['disability_type']] ?? 'bg-slate-700/50 text-slate-300 border-slate-600/30';
                    ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <?php if ($student['photo']): ?>
                                <img src="<?= url('storage/uploads/students/' . $student['id'] . '/' . $student['photo']) ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                                <?php else: ?>
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <a href="<?= url('students/' . $student['id']) ?>" class="text-sm font-medium text-slate-900 dark:text-white hover:text-brand-650 dark:hover:text-brand-300 transition-colors">
                                        <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= age($student['dob']) ?> · <?= ucfirst($student['gender']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            <span class="text-xs font-mono text-slate-600 dark:text-slate-400"><?= e($student['admission_number'] ?? '—') ?></span>
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-medium border <?= $dc ?>">
                                <?= e($student['disability_type']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-medium border <?= $sc ?>">
                                <?= ucfirst($student['admission_status']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 hidden xl:table-cell">
                            <span class="text-xs text-slate-600 dark:text-slate-500"><?= e($student['branch_name'] ?? '—') ?></span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="<?= url('students/' . $student['id']) ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <?php if (has_permission('edit_students')): ?>
                                <a href="<?= url('students/' . $student['id'] . '/edit') ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-900/30 transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($last_page ?? 1) > 1): ?>
        <div class="flex items-center justify-between mt-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Showing <?= $from ?? 1 ?>–<?= $to ?? count($data) ?> of <?= number_format($total ?? 0) ?> students
            </p>
            <div class="flex items-center gap-2">
                <?php if (($current_page ?? 1) > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => ($current_page ?? 1) - 1])) ?>"
                   class="px-3 py-1.5 rounded-lg text-sm text-slate-700 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-all border border-slate-200 dark:border-slate-700/50">
                    ← Prev
                </a>
                <?php endif; ?>
                <?php for ($p = max(1, ($current_page??1) - 2); $p <= min($last_page??1, ($current_page??1) + 2); $p++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                   class="px-3 py-1.5 rounded-lg text-sm transition-all border <?= $p === ($current_page??1) ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-600/20 dark:text-brand-400 border border-brand-500/30' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/50' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>
                <?php if (($current_page??1) < ($last_page??1)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => ($current_page??1) + 1])) ?>"
                   class="px-3 py-1.5 rounded-lg text-sm text-slate-700 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-all border border-slate-200 dark:border-slate-700/50">
                    Next →
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function studentsList() { return {} }
</script>

<?php
$content = ob_get_clean();
?>
