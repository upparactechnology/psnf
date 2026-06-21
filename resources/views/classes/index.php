<?php
$layout    = 'app';
$pageTitle = 'Classes & Sections';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Classes']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Classes & Sections</h2>
            <p class="text-sm text-slate-500 mt-0.5">Directory of active classes, section configurations, and enrolled students</p>
        </div>
    </div>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (empty($classes)): ?>
        <div class="col-span-full rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-650 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="text-sm font-semibold text-white">No active classes found</h3>
            <p class="text-xs text-slate-500 mt-1">Enroll students and assign them classes to populate this directory.</p>
        </div>
        <?php else: ?>
            <?php foreach ($classes as $c): ?>
            <?php 
                $displayClass = $c['class'] ?: 'Unassigned';
                $classUrl = url('classes/' . urlencode((string)$displayClass));
            ?>
            <a href="<?= $classUrl ?>" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 hover:border-indigo-500/30 hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between shadow-sm hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors"><?= e($displayClass) ?></h4>
                        <p class="text-xs text-slate-500">Section: <span class="text-slate-300 font-semibold font-mono"><?= e($c['section'] ?: 'Default') ?></span></p>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-between border-t border-slate-800/60 pt-3">
                    <span class="text-xs text-slate-400"><?= $c['student_count'] ?> Enrolled</span>
                    <span class="text-xs text-brand-400 hover:text-brand-300 font-medium flex items-center gap-0.5">
                        Students
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
