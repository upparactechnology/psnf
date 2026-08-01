<?php
$file = 'resources/views/parent/attendance.php';
$content = file_get_contents($file);

$leaveButtonHtml = <<<HTML
    <!-- Header & Filter -->
    <div x-data="{ leaveModal: false, startDate: '', endDate: '', get needsDoc() { return this.startDate && this.endDate && (new Date(this.endDate) > new Date(this.startDate)); } }" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Attendance Log</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track class attendance details and reviews</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="leaveModal = true" class="px-4 py-2 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Request Leave
            </button>
            <form method="GET" class="flex gap-2">
HTML;

$modalHtml = <<<HTML
        <!-- Leave Request Modal -->
        <div x-show="leaveModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center">
            <div x-show="leaveModal" @click="leaveModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
            <div x-show="leaveModal" class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden mt-10 transition-transform transform">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">📅</span>
                        Request Leave
                    </h3>
                    <button @click="leaveModal = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <form action="<?= url('parent/students/' . \$active_student['id'] . '/attendance/request-leave') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Start Date</label>
                                <input type="date" name="start_date" x-model="startDate" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">End Date</label>
                                <input type="date" name="end_date" x-model="endDate" required :min="startDate || '<?= date('Y-m-d', strtotime('+1 day')) ?>'" class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Leave Type</label>
                            <select name="leave_type" required class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500">
                                <option value="Medical">Medical Leave</option>
                                <option value="Personal">Personal Leave</option>
                                <option value="Family">Family Event</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Reason</label>
                            <textarea name="reason" rows="2" required class="w-full bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500" placeholder="Please provide a brief reason"></textarea>
                        </div>
                        <div x-show="needsDoc" x-transition class="space-y-1.5 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 rounded-xl">
                            <label class="block text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Document Required (Multi-day Leave)
                            </label>
                            <p class="text-[10px] text-amber-700 dark:text-amber-500 mb-2">Since you selected more than 1 day, please upload a supporting document (e.g. Medical Certificate).</p>
                            <input type="file" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png" :required="needsDoc" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200">
                        </div>
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-brand-600 to-indigo-600 rounded-xl text-xs font-bold text-white shadow-lg hover:shadow-brand-600/20 transition-all mt-4">
                            Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
HTML;

// 1. Replace the header section to inject Alpine x-data and the button
$content = preg_replace('/<!-- Header & Filter -->\s*<div class="flex flex-col.*?<form method="GET" class="flex gap-2">/s', $leaveButtonHtml, $content);

// 2. Add the modal and close the div
$content = preg_replace('/<\/form>\s*<\/div>/', '</form>' . "\n        </div>\n" . $modalHtml, $content);

file_put_contents($file, $content);
echo "Attendance page updated.";
