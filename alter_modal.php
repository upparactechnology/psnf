<?php
$file = 'resources/views/parent/dashboard.php';
$content = file_get_contents($file);

$absentModalHtml = <<<HTML
    <!-- Mark Absent Modal -->
    <div x-show="absentModal"
         style="display: none;"
         class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center">
        <!-- Backdrop -->
        <div x-show="absentModal"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="absentModal = false"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <!-- Panel -->
        <div x-show="absentModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-8 sm:scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-8 sm:scale-95 opacity-0"
             class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden mt-10">

            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-xl">❌</span>
                    Notify Next-Day Absence
                </h3>
                <button @click="absentModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6">
                <form hx-post="<?= url('parent/students/' . \$active_student['id'] . '/attendance/mark-absent') ?>"
                      hx-target="#absent-status"
                      @submit="setTimeout(() => { absentModal = false }, 500)"
                      class="space-y-5">
                    
                    <div id="absent-status"></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Please let the school know if your child will not be attending tomorrow.</p>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date of Absence</label>
                        <select name="absence_date" required
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500 appearance-none">
                            <option value="<?= date('Y-m-d', strtotime('+1 day')) ?>">Tomorrow, <?= date('d M Y', strtotime('+1 day')) ?></option>
                            <option value="<?= date('Y-m-d', strtotime('+2 days')) ?>"><?= date('l, d M Y', strtotime('+2 days')) ?></option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Reason (Optional)</label>
                        <input type="text" name="remarks"
                               class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500"
                               placeholder="e.g. Sick, Family event">
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-red-500 to-rose-600 rounded-xl text-xs font-bold text-white shadow-lg hover:shadow-red-500/20 transition-all mt-2">
                        Submit Absence
                    </button>
                </form>
            </div>
        </div>
    </div>
HTML;

// Append if not exists
if (strpos($content, 'absentModal') === false) {
    // Add absentModal state to x-data at the top
    $content = preg_replace('/x-data=\"\{ emergencyModal: false \}\"/', 'x-data="{ emergencyModal: false, absentModal: false }"', $content);
    
    // Add the button logic
    $content = preg_replace('/<button onclick=\"window.location.href=\'\<\?\= url\(\'parent\/students\/\' . \$active_student\[\'id\'\] . \'\/attendance\'\) \?\>\'\"/m', '<button @click="absentModal = true"', $content);
    
    // Append the modal before the final closing div
    $content = str_replace('<?php endif; ?>
</div>', '<?php endif; ?>' . "\n" . $absentModalHtml . "\n" . '</div>', $content);
} else {
    // Replace the modal content
    $content = preg_replace('/<!-- Mark Absent Modal -->.*?<\/div>\s*<\/div>\s*<\/div>/s', $absentModalHtml, $content);
}

file_put_contents($file, $content);
echo "Modal updated.";
