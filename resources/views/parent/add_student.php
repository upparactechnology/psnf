<?php
$layout = 'parent';
$pageTitle = 'Add Student Details';
$errors = \Core\Session::getFlash('errors') ?? [];
$old    = \Core\Session::getFlash('old') ?? [];
$fn     = fn($key) => $old[$key] ?? '';

// Generate input classes based on errors
$inputClass = function(string $field) use ($errors): string {
    $base = "w-full bg-white dark:bg-slate-905 border text-slate-850 dark:text-white rounded-xl py-3 px-4 text-sm focus:outline-none focus:ring-1 transition-all";
    return $base . (isset($errors[$field]) 
        ? " border-red-500 focus:border-red-500 focus:ring-red-500/20" 
        : " border-slate-200 dark:border-slate-800 focus:border-brand-500 focus:ring-brand-500/30");
};

$errorMsg = function(string $field) use ($errors): string {
    if (!isset($errors[$field])) return '';
    return '<p class="text-xs text-red-500 mt-1 flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' . htmlspecialchars($errors[$field]) . '</p>';
};
?>

<div x-data="addStudentForm()" class="max-w-3xl mx-auto space-y-6">

    <!-- Form Title & Back -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Register Student</h2>
            <p class="text-xs text-slate-500 mt-0.5">Please provide your child's basic care and academic mapping details.</p>
        </div>
        <a href="<?= url('parent/dashboard') ?>" class="text-xs font-semibold text-indigo-500 hover:text-indigo-650 transition-colors">← Dashboard</a>
    </div>

    <!-- Progress Steps Header -->
    <div class="flex items-center gap-0 border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/30 p-4 rounded-2xl shadow-sm">
        <?php
        $steps = ['Personal Details', 'School & Address', 'Care & Disability'];
        foreach ($steps as $i => $step):
        ?>
        <div class="flex items-center <?= $i < count($steps) - 1 ? 'flex-1' : '' ?>">
            <div class="flex items-center gap-2.5">
                <div :class="step >= <?= $i ?> ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-205 dark:bg-slate-800 text-slate-500 dark:text-slate-650'"
                     class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold transition-all">
                    <?= $i + 1 ?>
                </div>
                <span :class="step >= <?= $i ?> ? 'text-slate-800 dark:text-white font-bold' : 'text-slate-400 dark:text-slate-500'"
                      class="text-xs font-semibold hidden sm:block transition-colors"><?= $step ?></span>
            </div>
            <?php if ($i < count($steps) - 1): ?>
            <div class="flex-1 mx-3 h-0.5" :class="step > <?= $i ?> ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-800'"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Form -->
    <form method="POST" action="<?= url('parent/students/add') ?>" class="space-y-6" @submit="loading = true">
        <?= \Core\View::csrf() ?>

        <?php if (!empty($errors['db_error'])): ?>
            <div class="p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 text-xs font-semibold">
                <?= e($errors['db_error']) ?>
            </div>
        <?php endif; ?>

        <!-- Step 0: Personal Details -->
        <div x-show="step === 0" class="space-y-6">
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-850 dark:text-white border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-indigo-600 rounded-sm"></span> Personal Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="<?= e($fn('first_name')) ?>" required class="<?= $inputClass('first_name') ?>" placeholder="e.g. Aarav">
                        <?= $errorMsg('first_name') ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="<?= e($fn('last_name')) ?>" required class="<?= $inputClass('last_name') ?>" placeholder="e.g. Patel">
                        <?= $errorMsg('last_name') ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" required class="<?= $inputClass('gender') ?>">
                            <option value="">Select Gender</option>
                            <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $fn('gender') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $errorMsg('gender') ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date of Birth <span class="text-red-500">*</span></label>
                        <input type="date" name="dob" value="<?= e($fn('dob')) ?>" required class="<?= $inputClass('dob') ?>">
                        <?= $errorMsg('dob') ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Blood Group</label>
                        <select name="blood_group" class="<?= $inputClass('blood_group') ?>">
                            <option value="Unknown">Unknown</option>
                            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= $fn('blood_group') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $errorMsg('blood_group') ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aadhar Number (Optional)</label>
                        <input type="text" name="aadhar_number" value="<?= e($fn('aadhar_number')) ?>" class="<?= $inputClass('aadhar_number') ?>" placeholder="e.g. 1234 5678 9012" maxlength="14">
                        <?= $errorMsg('aadhar_number') ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mother Tongue</label>
                        <input type="text" name="mother_tongue" value="<?= e($fn('mother_tongue')) ?>" class="<?= $inputClass('mother_tongue') ?>" placeholder="e.g. Gujarati">
                        <?= $errorMsg('mother_tongue') ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" @click="validateStep(1)" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-xs font-bold text-white shadow-lg shadow-indigo-600/15 hover:opacity-95 transition-all">
                    Next: School & Location →
                </button>
            </div>
        </div>

        <!-- Step 1: Location & School -->
        <div x-show="step === 1" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-850 dark:text-white border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-indigo-600 rounded-sm"></span> School Mapping & Address
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target School <span class="text-red-500">*</span></label>
                        <select name="school_id" required class="<?= $inputClass('school_id') ?>" x-model="selectedSchool" @change="loadBranches($event.target.value)">
                            <option value="">Select School</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?= $school['id'] ?>"><?= e($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $errorMsg('school_id') ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target Branch <span class="text-red-500">*</span></label>
                        <select name="branch_id" required class="<?= $inputClass('branch_id') ?>" x-model="selectedBranch">
                            <option value="">Select Branch</option>
                            <template x-for="branch in filteredBranches" :key="branch.id">
                                <option :value="branch.id" x-text="branch.name"></option>
                            </template>
                        </select>
                        <?= $errorMsg('branch_id') ?>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Home Address</label>
                    <textarea name="address" rows="3" class="<?= $inputClass('address') ?>" placeholder="Residential address..."><?= e($fn('address')) ?></textarea>
                    <?= $errorMsg('address') ?>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 0" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    ← Back
                </button>
                <button type="button" @click="validateStep(2)" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-xs font-bold text-white shadow-lg shadow-indigo-600/15 hover:opacity-95 transition-all">
                    Next: Care Profile →
                </button>
            </div>
        </div>

        <!-- Step 2: Care & Disability -->
        <div x-show="step === 2" class="space-y-6" x-cloak>
            <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-6 space-y-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-850 dark:text-white border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-indigo-600 rounded-sm"></span> Disability & Transition Protocol
                </h3>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disability Type <span class="text-red-500">*</span></label>
                    <select name="disability_type" required class="<?= $inputClass('disability_type') ?>">
                        <option value="">Select Disability Classification</option>
                        <?php foreach (['ASD', 'ADHD', 'Down Syndrome', 'Cerebral Palsy', 'Dyslexia', 'Intellectual Disability', 'Hearing Impairment', 'Visual Impairment', 'Multiple Disabilities', 'Other'] as $d): ?>
                        <option value="<?= $d ?>" <?= $fn('disability_type') === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $errorMsg('disability_type') ?>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disability Detail & Diagnosis Remarks</label>
                    <textarea name="disability_detail" rows="2" class="<?= $inputClass('disability_detail') ?>" placeholder="Describe specific symptoms, medical history, or diagnosis remarks..."><?= e($fn('disability_detail')) ?></textarea>
                    <?= $errorMsg('disability_detail') ?>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Care & Behavior Transition Instructions</label>
                    <textarea name="care_instructions" rows="2" class="<?= $inputClass('care_instructions') ?>" placeholder="Specific instructions for calming, feeding, or physical requirements..."><?= e($fn('care_instructions')) ?></textarea>
                    <?= $errorMsg('care_instructions') ?>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Special Educational Needs Summary</label>
                    <textarea name="special_needs_summary" rows="2" class="<?= $inputClass('special_needs_summary') ?>" placeholder="Summary of cognitive assistance, communication cards, or physical therapist aids..."><?= e($fn('special_needs_summary')) ?></textarea>
                    <?= $errorMsg('special_needs_summary') ?>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 1" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    ← Back
                </button>
                <button type="submit" :disabled="loading"
                        class="px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl text-xs font-bold text-white shadow-lg hover:opacity-95 transition-all flex items-center gap-2">
                    <svg x-show="loading" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span>Submit Details</span>
                </button>
            </div>
        </div>

    </form>
</div>

<script>
function addStudentForm() {
    return {
        step: 0,
        loading: false,
        selectedSchool: '<?= $fn('school_id') ?>',
        selectedBranch: '<?= $fn('branch_id') ?>',
        allBranches: <?= json_encode($branches) ?>,
        filteredBranches: [],
        init() {
            if (this.selectedSchool) {
                this.loadBranches(this.selectedSchool);
            }
        },
        loadBranches(schoolId) {
            this.filteredBranches = this.allBranches.filter(b => b.school_id == schoolId);
            if (!this.filteredBranches.some(b => b.id == this.selectedBranch)) {
                this.selectedBranch = '';
            }
        },
        validateStep(targetStep) {
            const currentStepEl = document.querySelector(`[x-show="step === ${this.step}"]`);
            if (currentStepEl) {
                const inputs = currentStepEl.querySelectorAll('input, select, textarea');
                let isValid = true;
                for (let input of inputs) {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        input.reportValidity();
                        isValid = false;
                        break;
                    }
                }
                if (isValid) {
                    this.step = targetStep;
                }
            } else {
                this.step = targetStep;
            }
        }
    }
}
</script>
