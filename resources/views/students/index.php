<?php
$layout    = 'app';
$pageTitle = 'Students';
$breadcrumbs = [];
ob_start();
?>

<div x-data="{ viewMode: 'grid' }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Top Header & Quick Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Students Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5"><?= number_format($total ?? 0) ?> registered students</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50">
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Grid Cards View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Compact Table View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <?php if (has_permission('create_students')): ?>
            <a href="<?= url('students/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white transition-all shadow-sm hover:opacity-90 bg-brand-600 hover:bg-brand-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Student
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <?php
        $essentialFilters = ['all' => 'All', 'enrolled' => 'Enrolled', 'applied' => 'Admissions', 'assessment' => 'Assessment', 'withdrawn' => 'Withdrawn'];
        $currentStatus = $filters['status'] ?? '';
        foreach ($essentialFilters as $key => $label):
            $active = ($key === 'all' && !$currentStatus) || $key === $currentStatus;
        ?>
        <a href="<?= url('students?' . http_build_query(array_merge($filters, ['status' => $key === 'all' ? '' : $key, 'search' => $search ?? '']))) ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all <?= $active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input type="text" placeholder="Search student name or ADM number..."
               value="<?= e($search ?? '') ?>"
               class="w-full bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 rounded-xl py-2.5 pl-10 pr-4 text-xs focus:outline-none focus:border-brand-500 transition-all"
               hx-get="<?= url('students') ?>"
               hx-target="#student-container"
               hx-trigger="keyup changed delay:400ms"
               name="search">
    </div>

    <!-- Student Cards Container -->
    <div id="student-container">
        <?php if (empty($data)): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500">No students found matching your criteria.</p>
        </div>
        <?php else: ?>

        <!-- ENHANCED STUDENT CARD GRID -->
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($data as $student): ?>
            <a href="<?= url('students/' . $student['id']) ?>" class="group p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm hover:shadow-md flex flex-col justify-between space-y-4">
                
                <!-- Avatar & Identifiers -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-slate-200 dark:border-slate-700/50">
                        👤 <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h3>
                        <p class="text-2xs font-mono text-slate-400 mt-0.5"><?= e($student['admission_number'] ?? 'ADM-2026-111') ?></p>
                    </div>
                </div>

                <!-- Demographics & Attributes -->
                <div class="space-y-1.5 border-t border-b border-slate-100 dark:border-slate-800/60 py-3 text-2xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span>Class <?= e($student['class'] ?? '2-A') ?></span>
                        <span><?= ucfirst($student['gender'] ?? 'Male') ?> • <?= age($student['dob']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-full font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            <?= e($student['disability_type'] ?? 'ADHD') ?>
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <?= ucfirst($student['admission_status'] ?? 'Enrolled') ?>
                        </span>
                    </div>
                </div>

                <!-- Card Footer Action -->
                <div class="flex items-center justify-between text-2xs text-slate-400 font-medium">
                    <span>View Profile</span>
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

        <!-- COMPACT TABLE -->
        <div x-show="viewMode === 'table'" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-slate-400 text-2xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Class</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-xs">
                    <?php foreach ($data as $student): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                            <span class="block text-2xs text-slate-400 font-normal font-mono"><?= e($student['admission_number'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-slate-500"><?= e($student['class'] ?? '—') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-2xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                <?= ucfirst($student['admission_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?= url('students/' . $student['id']) ?>" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View →</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
