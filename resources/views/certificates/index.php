<?php
$layout    = 'app';
$pageTitle = 'Certificate Registry';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Certificates']];
ob_start();
?>

<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Issued Certificates</h2>
            <p class="text-sm text-slate-500 mt-0.5">Design and print customized student achievement certificates.</p>
        </div>
        <a href="<?= url('certificates/create') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90"
           style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Design Certificate
        </a>
    </div>

    <!-- Certificates Grid -->
    <?php if (empty($certificates)): ?>
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 p-16 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400 dark:text-slate-650" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No Certificates Issued</h3>
        <p class="text-slate-500 text-sm mb-6">Create outstanding achievements and visual certificates for students.</p>
        <a href="<?= url('certificates/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-md hover:opacity-95" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            Launch Designer
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($certificates as $c): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden relative group">
            
            <!-- Thumbnail Visual Representation -->
            <div class="h-32 bg-slate-50 dark:bg-slate-950/40 flex items-center justify-center border-b border-slate-100 dark:border-slate-800/60 relative">
                <svg class="w-10 h-10 text-slate-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222m3.935 2.193L15 20v-7.5"/></svg>
                <div class="absolute inset-0 bg-brand-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>

            <div class="p-5">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" title="<?= e($c['title']) ?>"><?= e($c['title']) ?></h4>
                <p class="text-xs text-slate-500 mt-1 font-medium">Recipient: <?= e($c['first_name'] . ' ' . $c['last_name']) ?></p>
                <p class="text-3xs text-slate-400 font-mono mt-0.5">ADM: <?= e($c['admission_number']) ?></p>

                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 mt-4 pt-3.5">
                    <span class="text-3xs text-slate-400">Issued: <?= e($c['issued_at']) ?></span>
                    
                    <div class="flex items-center gap-2">
                        <a href="<?= url("certificates/{$c['id']}/view") ?>" target="_blank"
                           class="px-2.5 py-1 text-2xs font-semibold text-brand-650 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/20 hover:bg-brand-100 dark:hover:bg-brand-900/30 rounded-lg transition-all"
                           title="View Certificate">
                            Print / View
                        </a>
                        <form action="<?= url("certificates/{$c['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this certificate?')">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="p-1 rounded-md text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
