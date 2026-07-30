<?php
// Variables received: $active_student, $tomorrowAtt, $canEdit, $remainingSeconds, $lockedReason, $date
?>

<div class="space-y-6">

    <!-- Status Message Alert / Lock Notice -->
    <?php if (!$canEdit): ?>
        <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-800 dark:text-rose-300 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <span class="font-bold block text-sm text-slate-800 dark:text-white mb-0.5">Declaration Locked</span>
                <?php if ($lockedReason === 'past_date'): ?>
                    You cannot declare or modify attendance for past dates.
                <?php elseif ($lockedReason === 'time_expired'): ?>
                    The deadline to declare or modify attendance for this date has passed (11:00 PM on the day prior).
                <?php elseif ($lockedReason === 'school_managed'): ?>
                    This attendance was marked by the school teacher and cannot be modified by parents.
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($tomorrowAtt): // Show edit window countdown only after attendance has been declared ?>
        <div class="p-4 rounded-xl border border-yellow-500/20 bg-yellow-500/10 text-amber-850 dark:text-yellow-300 text-xs flex items-start gap-3"
             x-data="{ 
                 secondsLeft: <?= $remainingSeconds ?>,
                 formatTime(seconds) {
                     const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
                     const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
                     const s = String(seconds % 60).padStart(2, '0');
                     return `${h}:${m}:${s}`;
                 }
             }"
             x-init="const interval = setInterval(() => { if (secondsLeft > 0) { secondsLeft--; } else { clearInterval(interval); location.reload(); } }, 1000);">
            <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <span class="font-bold block text-sm text-slate-800 dark:text-white mb-0.5">Edit Window Active</span>
                You can update this attendance declaration until 11:00 PM on the day prior to the target date.
                <span class="block mt-1 font-semibold text-slate-800 dark:text-white">
                    Time Remaining: <span x-text="formatTime(secondsLeft)"></span>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- The Form itself -->
    <form action="<?= url('parent/students/' . $active_student['id'] . '/attendance/declare') ?>" method="POST" enctype="multipart/form-data" 
          x-data="{ 
              status: <?= json_encode($tomorrowAtt['status'] ?? '') ?>,
              remarks: <?= json_encode($tomorrowAtt['remarks'] ?? '') ?>,
              isMedical: <?= (!empty($tomorrowAtt['medical_certificate']) ? 'true' : 'false') ?>,
              useBus: <?= (!empty($tomorrowAtt['use_bus_transport']) ? 'true' : 'false') ?>,
              isMedicalRequired() {
                  return this.isMedical || this.remarks.toLowerCase().includes('medical');
              }
          }" 
          x-init="$watch('remarks', value => { if (value.toLowerCase().includes('medical')) { isMedical = true; } })"
          class="space-y-6">
        
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="date" value="<?= e($date) ?>">

        <!-- Option Selectors -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Present Card -->
            <div class="relative flex flex-col p-4 rounded-xl border transition-all duration-200 <?= $canEdit ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' ?>"
                 @click="<?= $canEdit ? "status = 'present'" : "" ?>"
                 :class="status === 'present' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-800 dark:text-white font-semibold' : 'bg-white dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:border-slate-350 dark:hover:border-slate-700 shadow-sm'">
                <input type="radio" name="status" value="present" x-model="status" <?php if (!$canEdit) echo 'disabled'; ?> class="sr-only">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                             :class="status === 'present' ? 'bg-emerald-500/20 text-emerald-500 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-900 text-slate-400 dark:text-slate-500'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm">Attending School</p>
                            <p class="text-[10px] opacity-80 mt-0.5">My child will attend classes</p>
                        </div>
                    </div>
                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                         :class="status === 'present' ? 'border-emerald-500 dark:border-emerald-400 bg-emerald-500/20' : 'border-slate-300 dark:border-slate-750'">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400" x-show="status === 'present'"></span>
                    </div>
                </div>
            </div>

            <!-- Absent Card -->
            <div class="relative flex flex-col p-4 rounded-xl border transition-all duration-200 <?= $canEdit ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' ?>"
                 @click="<?= $canEdit ? "status = 'absent'" : "" ?>"
                 :class="status === 'absent' ? 'bg-rose-500/10 border-rose-500/40 text-rose-800 dark:text-white font-semibold' : 'bg-white dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:border-slate-350 dark:hover:border-slate-700 shadow-sm'">
                <input type="radio" name="status" value="absent" x-model="status" <?php if (!$canEdit) echo 'disabled'; ?> class="sr-only">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                             :class="status === 'absent' ? 'bg-rose-500/20 text-rose-500 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-900 text-slate-400 dark:text-slate-500'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm">Not Attending</p>
                            <p class="text-[10px] opacity-80 mt-0.5">My child will be absent</p>
                        </div>
                    </div>
                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                         :class="status === 'absent' ? 'border-rose-500 dark:border-rose-400 bg-rose-500/20' : 'border-slate-300 dark:border-slate-750'">
                        <span class="w-2 h-2 rounded-full bg-rose-500 dark:bg-rose-400" x-show="status === 'absent'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Present Details Form (appears only when status is present) -->
        <div x-show="status === 'present'" x-cloak x-transition class="space-y-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800/80 shadow-inner">
            <div class="flex items-center gap-3">
                <input type="checkbox" name="use_bus_transport" value="1" id="use_bus_transport" x-model="useBus" <?php if (!$canEdit) echo 'disabled'; ?>
                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-emerald-500 focus:ring-0 focus:ring-offset-0 disabled:opacity-50">
                <label for="use_bus_transport" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none disabled:opacity-50">
                    Will the student use school bus transport tomorrow?
                </label>
            </div>
        </div>

        <!-- Absent Details Form (appears only when status is absent) -->
        <div x-show="status === 'absent'" x-cloak x-transition class="space-y-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800/80 shadow-inner">
            <!-- Reason Field -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Reason for Absence <span class="text-rose-500">*</span></label>
                <textarea name="remarks" rows="2" placeholder="Please specify the reason (e.g., family event, sickness)..." x-model="remarks" <?php if (!$canEdit) echo 'disabled'; ?>
                          class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-rose-500 placeholder-slate-400 dark:placeholder-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"></textarea>
            </div>

            <!-- Medical emergency/illness check -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_medical" value="1" id="is_medical" x-model="isMedical" <?php if (!$canEdit) echo 'disabled'; ?>
                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-rose-500 focus:ring-0 focus:ring-offset-0 disabled:opacity-50">
                <label for="is_medical" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none disabled:opacity-50">
                    This absence is due to a medical issue / emergency
                    <span class="text-rose-500 dark:text-rose-400 text-[10px] font-semibold ml-1" x-show="remarks.toLowerCase().includes('medical')">(Automatically checked: 'medical' detected in reason)</span>
                </label>
            </div>

            <!-- Medical Certificate Upload -->
            <div x-show="isMedicalRequired()" x-cloak x-transition class="space-y-2 border-t border-slate-200 dark:border-slate-800/60 pt-3">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Upload Medical Certificate <span class="text-rose-500 dark:text-rose-405">*</span>
                    <span class="text-rose-500 dark:text-rose-400 text-[10px] font-semibold ml-1" x-show="remarks.toLowerCase().includes('medical')">(Required because reason contains 'medical')</span>
                </label>
                <?php if ($canEdit): ?>
                    <input type="file" name="medical_certificate" 
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-900 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-800 file:cursor-pointer cursor-pointer">
                    <p class="text-[10px] text-slate-450 dark:text-slate-500">Allowed formats: PDF, PNG, JPG, JPEG. Max size: 5MB.</p>
                <?php endif; ?>
                
                <?php if (!empty($tomorrowAtt['medical_certificate'])): ?>
                    <div class="flex items-center gap-2 mt-2 bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 px-3 py-2 rounded-lg w-fit shadow-sm">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[10px] text-slate-700 dark:text-slate-300">Medical Certificate Uploaded</span>
                        <a href="<?= url('storage/uploads/attendance/' . $active_student['id'] . '/' . $tomorrowAtt['medical_certificate']) ?>" target="_blank" 
                           class="text-[10px] text-brand-600 dark:text-brand-400 hover:underline font-semibold ml-2">View Certificate</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submit Button -->
        <?php if ($canEdit): ?>
            <div class="flex justify-end pt-2">
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-xs font-bold text-white transition-all bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-brand-500/10">
                    Save Attendance Declaration
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>
