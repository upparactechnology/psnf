<?php
/**
 * Student list (grid + table) — the inner content of #student-container.
 * Rendered inside students/index.php and returned alone for HTMX search/filter requests.
 * Expects: $data (rows from Student::search()).
 */
$data = $data ?? [];
?>
    <?php $canSeeDetails = !(has_role('teacher') && !has_role('super_admin') && !has_role('school_admin')); ?>
    <?php $canDeleteStudent = (has_role('super_admin') || has_role('school_admin')) && has_permission('delete_students_list'); ?>
        <?php if (empty($data)): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500">No students found matching your criteria.</p>
        </div>
        <?php else: ?>

        <!-- ENHANCED STUDENT CARD GRID -->
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($data as $student): ?>
            <div class="relative">
            <<?= $canSeeDetails ? 'a href="' . url('academics/students/' . $student['id']) . '"' : 'div' ?> class="group p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm hover:shadow-md flex flex-col justify-between space-y-4">
                
                <!-- Avatar & Identifiers -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-slate-200 dark:border-slate-700/50">
                        👤 <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h3>
                        <p class="text-2xs font-mono text-slate-400 mt-0.5"><?= e(($student['admission_number'] ?? '') !== '' ? $student['admission_number'] : '—') ?></p>
                    </div>
                </div>

                <!-- Demographics & Attributes -->
                <div class="space-y-1.5 border-t border-b border-slate-100 dark:border-slate-800/60 py-3 text-2xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span>Class <?= e(($student['class'] ?? '') !== '' ? $student['class'] . (!empty($student['section']) ? '-' . $student['section'] : '') : '—') ?></span>
                        <span><?= e(!empty($student['gender']) ? ucfirst($student['gender']) : '—') ?> • <?= !empty($student['dob']) ? age($student['dob']) : '—' ?></span>
                    </div>
                    <?php $cardAddr = implode(', ', array_filter(array_map('trim', [(string)($student['address'] ?? ''), (string)($student['city'] ?? '')]))); ?>
                    <?php if ($cardAddr !== '' && $canSeeDetails): ?>
                    <div class="text-slate-500 truncate" title="<?= e($cardAddr) ?>">📍 <?= e($cardAddr) ?></div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <?php if (!empty($student['disability_type'])): ?>
                        <span class="px-2 py-0.5 rounded-full font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            <?= e($student['disability_type']) ?>
                        </span>
                        <?php else: ?><span></span><?php endif; ?>
                        <span class="flex items-center gap-1 font-semibold text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <?= e(!empty($student['admission_status']) ? ucfirst(str_replace('_', ' ', $student['admission_status'])) : '—') ?>
                        </span>
                    </div>
                </div>

                <?php if ($canSeeDetails): ?>
                <!-- Card Footer Action -->
                <div class="flex items-center justify-between text-2xs text-slate-400 font-medium">
                    <span>View Profile</span>
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </div>
                <?php endif; ?>

            </<?= $canSeeDetails ? 'a' : 'div' ?>>
            <?php if ($canDeleteStudent): ?>
            <form method="POST" action="<?= url('students/' . $student['id']) ?>" onsubmit="return confirm('Delete student <?= e(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>? This cannot be undone from this screen.')" class="absolute top-3 right-3">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" title="Delete student" class="p-1.5 rounded-lg text-red-500 bg-white/80 dark:bg-slate-900/80 border border-red-500/20 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </form>
            <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- COMPACT TABLE -->
        <div x-show="viewMode === 'table'" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-slate-400 text-2xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Class</th>
                        <?php if ($canSeeDetails): ?><th class="px-4 py-3">Address</th><?php endif; ?>
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
                        <?php if ($canSeeDetails): ?><td class="px-4 py-3 text-slate-500 max-w-xs truncate"><?= e(implode(', ', array_filter(array_map('trim', [(string)($student['address'] ?? ''), (string)($student['city'] ?? '')]))) ?: '—') ?></td><?php endif; ?>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-2xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                <?= e(!empty($student['admission_status']) ? ucfirst(str_replace('_', ' ', $student['admission_status'])) : '—') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($canSeeDetails): ?><a href="<?= url('academics/students/' . $student['id']) ?>" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View →</a><?php endif; ?>
                            <?php if ($canDeleteStudent): ?>
            <form method="POST" action="<?= url('students/' . $student['id']) ?>" onsubmit="return confirm('Delete student <?= e(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>? This cannot be undone from this screen.')" class="inline-block ml-3">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" title="Delete student" class="inline-flex items-center gap-1 text-xs text-red-500 font-semibold hover:underline"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
