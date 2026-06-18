<?php $layout = 'parent'; ?>

<div class="space-y-6">

    <!-- Header info -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white">Medical Information & Warnings</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Student healthcare profiles, allergen indexes, emergency protocols, and doctor details</p>
    </div>

    <!-- Alert Status Box -->
    <div class="p-5 rounded-2xl border border-red-200 dark:border-red-800/40 bg-red-50 dark:bg-red-955/20 text-red-750 dark:text-red-300 flex items-start gap-4 shadow-sm dark:shadow-xl">
        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-650 dark:text-red-400 border border-red-200 dark:border-red-800/30 flex-shrink-0 flex items-center justify-center">
            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="space-y-1">
            <h4 class="text-sm font-bold text-red-900 dark:text-white">Critical Care Warning & Protocol</h4>
            <p class="text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                <?= e($medical['emergency_protocols'] ?? 'No immediate critical response protocol defined.') ?>
            </p>
        </div>
    </div>

    <!-- Grid Detail Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Allergens and Triggers -->
        <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-2">
                <span class="w-1.5 h-3 bg-red-500 rounded"></span> Allergies & Physical Sensitivities
            </h4>
            <div class="space-y-4">
                <!-- Allergies -->
                <div class="bg-slate-50 dark:bg-slate-955/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
                    <p class="text-[10px] font-bold text-red-650 dark:text-red-400 uppercase tracking-wider mb-1">Identified Allergens</p>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= e($medical['allergies'] ?: 'No food or physical allergens registered.') ?></p>
                </div>
                <!-- Triggers -->
                <div class="bg-slate-50 dark:bg-slate-955/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
                    <p class="text-[10px] font-bold text-amber-600 dark:text-yellow-400 uppercase tracking-wider mb-1">Behavioral & Sensory Triggers</p>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= e($medical['triggers'] ?: 'No sensory triggers registered.') ?></p>
                </div>
            </div>
        </div>

        <!-- Medications & Doctor Contact Details -->
        <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 flex flex-col justify-between shadow-sm">
            <div class="space-y-5">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-3 bg-blue-500 rounded"></span> Medications & Doctor Contacts
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-955/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
                        <p class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Current Medications</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= e($medical['current_medications'] ?: 'None') ?></p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-955/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
                        <p class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Primary Hospital</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= e($medical['hospital'] ?: '—') ?></p>
                    </div>
                </div>
            </div>

            <!-- Doctor Profile Box -->
            <div class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/30 bg-blue-50 dark:bg-blue-955/20 flex items-center justify-between gap-4 mt-4">
                <div class="space-y-1">
                    <p class="text-[9px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Assigned Pediatrician</p>
                    <h5 class="text-xs font-bold text-slate-850 dark:text-white"><?= e($medical['doctor_name'] ?: 'No primary physician linked') ?></h5>
                    <p class="text-[10px] text-slate-600 dark:text-slate-400"><?= e($medical['doctor_phone'] ?: '') ?></p>
                </div>
                <?php if ($medical['doctor_phone']): ?>
                <a href="tel:<?= e($medical['doctor_phone']) ?>"
                   class="p-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white shadow-md transition-colors flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
