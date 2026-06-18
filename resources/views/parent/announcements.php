<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white">School Announcements</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Read news letters, event notices, and updates published by school administrators</p>
    </div>

    <!-- Feed List -->
    <div class="space-y-4 max-w-3xl">
        <?php if (empty($announcements)): ?>
        <div class="p-10 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/10 text-center">
            <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-xs text-slate-500">No school announcements currently published.</p>
        </div>
        <?php else: ?>
        <?php foreach ($announcements as $ann): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-3 hover:border-slate-300 dark:hover:border-slate-800 transition-colors shadow-sm">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full bg-brand-500/10 border border-brand-500/10 text-brand-655 dark:text-brand-400 text-[9px] font-bold uppercase tracking-wider">
                    Notice
                </span>
                <h4 class="text-base font-bold text-slate-800 dark:text-white mt-1"><?= e($ann['title']) ?></h4>
                <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-semibold"><?= date('l, d F Y — h:i A', strtotime($ann['published_at'])) ?></span>
            </div>
            <p class="text-xs text-slate-650 dark:text-slate-300 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-850"><?= nl2br(htmlspecialchars($ann['content'])) ?></p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
