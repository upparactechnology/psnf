<?php
$layout    = 'app';
$pageTitle = 'Submit Student Application';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Academics', 'url' => '/academics'], ['label' => 'New Application']];
ob_start();
$errors = \Core\Session::getFlash('errors') ?? [];
$old    = \Core\Session::getFlash('old') ?? [];
$fn     = fn($k) => $old[$k] ?? '';
?>

<div class="max-w-3xl mx-auto" x-data="studentForm()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Student Admission Application</h2>
            <p class="text-xs text-slate-550 mt-0.5">Step <span x-text="step + 1"></span> of 2</p>
        </div>
        <a href="<?= url('academics/admissions') ?>" class="text-xs text-slate-400 hover:text-slate-300">← Cancel</a>
    </div>

    <!-- Stepper Indicator -->
    <div class="flex items-center gap-2 mb-6">
        <div class="h-1.5 flex-1 rounded-full transition-all duration-350" :class="step >= 0 ? 'bg-indigo-650' : 'bg-slate-800'"></div>
        <div class="h-1.5 flex-1 rounded-full transition-all duration-350" :class="step >= 1 ? 'bg-indigo-650' : 'bg-slate-800'"></div>
    </div>

    <form method="POST" action="<?= url('students') ?>" enctype="multipart/form-data" @submit="loading = true" class="space-y-6">
        <?= \Core\View::csrf() ?>

        <!-- Step 0: Basic Details -->
        <div x-show="step === 0" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Basic Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">First Name <span class="text-red-400">*</span></label>
                        <input type="text" name="first_name" value="<?= e($fn('first_name')) ?>" required class="<?= inputClass('first_name', $errors) ?>" placeholder="First name">
                        <?= errorMsg('first_name', $errors) ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Middle Name</label>
                        <input type="text" name="middle_name" value="<?= e($fn('middle_name')) ?>" class="<?= inputClass('middle_name', $errors) ?>" placeholder="Middle name">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Last Name <span class="text-red-400">*</span></label>
                        <input type="text" name="last_name" value="<?= e($fn('last_name')) ?>" required class="<?= inputClass('last_name', $errors) ?>" placeholder="Last name">
                        <?= errorMsg('last_name', $errors) ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Date of Birth <span class="text-red-400">*</span></label>
                        <input type="date" name="dob" value="<?= e($fn('dob')) ?>" required class="<?= inputClass('dob', $errors) ?>">
                        <?= errorMsg('dob', $errors) ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Roll Number</label>
                        <input type="text" name="roll_number" value="<?= e($fn('roll_number')) ?>" class="<?= inputClass('roll_number', $errors) ?>" placeholder="e.g. 101">
                        <?= errorMsg('roll_number', $errors) ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Gender <span class="text-red-400">*</span></label>
                        <select name="gender" required class="<?= selectClass('gender', $errors) ?>">
                            <option value="">Select</option>
                            <option value="male" <?= $fn('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $fn('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $fn('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                        <?= errorMsg('gender', $errors) ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Blood Group</label>
                        <select name="blood_group" class="<?= selectClass('blood_group', $errors) ?>">
                            <option value="Unknown" <?= $fn('blood_group') === 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                            <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= $fn('blood_group') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">School <span class="text-red-400">*</span></label>
                        <select name="school_id" required class="<?= selectClass('school_id', $errors) ?>">
                            <?php foreach ($schools as $school): ?>
                            <option value="<?= $school['id'] ?>" <?= $fn('school_id') == $school['id'] ? 'selected' : '' ?>><?= e($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                        <select name="branch_id" required class="<?= selectClass('branch_id', $errors) ?>">
                            <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['id'] ?>" <?= $fn('branch_id') == $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Enrollment Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Class</label>
                        <input type="text" name="class" value="<?= e($fn('class')) ?>" list="class-list" class="<?= inputClass('class', $errors) ?>" placeholder="e.g. 10">
                        <datalist id="class-list">
                            <?php foreach ($classes as $cls): ?>
                            <option value="<?= e($cls['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Section</label>
                        <input type="text" name="section" value="<?= e($fn('section')) ?>" class="<?= inputClass('section', $errors) ?>" placeholder="e.g. A">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Academic Year</label>
                        <input type="text" name="academic_year" value="<?= e($fn('academic_year')) ?>" list="year-list" class="<?= inputClass('academic_year', $errors) ?>" placeholder="e.g. 2026-2027">
                        <datalist id="year-list">
                            <?php foreach ($academicYears as $yr): ?>
                            <option value="<?= e($yr['year_name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Enrolled Date</label>
                        <input type="date" name="enrolled_date" value="<?= e($fn('enrolled_date') ?: date('Y-m-d')) ?>" class="<?= inputClass('enrolled_date', $errors) ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Aadhar Number</label>
                        <input type="text" name="aadhar_number" value="<?= e($fn('aadhar_number')) ?>" class="<?= inputClass('aadhar_number', $errors) ?>" placeholder="XXXX-XXXX-XXXX" maxlength="14"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\d{4})(?=\d)/g, '$1-')">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Mother Tongue</label>
                        <input type="text" name="mother_tongue" value="<?= e($fn('mother_tongue')) ?>" class="<?= inputClass('mother_tongue', $errors) ?>" placeholder="e.g. Gujarati">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Address</label>
                    <textarea name="address" rows="2" class="<?= inputClass('address', $errors) ?>" placeholder="Home address"><?= e($fn('address')) ?></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" @click="validateStep(1)" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #ffffff !important;">
                    Next: Guardians <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 1: Guardians -->
        <div x-show="step === 1" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-semibold text-slate-300">Guardian / Parent Details</h3>
                    <button type="button" @click="addGuardian()" class="text-xs text-indigo-400 font-bold hover:underline">+ Add Another Guardian</button>
                </div>
                <p class="text-xs text-slate-500 font-normal">Add parent details. At least one guardian is required.</p>

                <div class="space-y-4">
                    <template x-for="(g, idx) in guardians" :key="idx">
                        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/60 space-y-4 relative">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-400" x-text="'Guardian #' + (idx + 1) + (idx === 0 ? ' (Primary)' : '')"></span>
                                <button type="button" x-show="idx > 0" @click="removeGuardian(idx)" class="text-xs text-red-400 font-semibold hover:underline">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-medium text-slate-400">Guardian Name</label>
                                    <input type="text" :name="'guardians['+idx+'][name]'" x-model="g.name" required class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2 px-3 text-xs focus:outline-none" placeholder="Full name">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-medium text-slate-400">Relationship</label>
                                    <select :name="'guardians['+idx+'][relationship]'" x-model="g.relationship" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none">
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Grandparent">Grandparent</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-medium text-slate-400">Phone Number</label>
                                    <input type="tel" :name="'guardians['+idx+'][phone]'" x-model="g.phone" required class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2 px-3 text-xs focus:outline-none" placeholder="10-digit phone" maxlength="10">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-medium text-slate-400">Email</label>
                                    <input type="email" :name="'guardians['+idx+'][email]'" x-model="g.email" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2 px-3 text-xs focus:outline-none" placeholder="parent@email.com">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-medium text-slate-400">Aadhaar Card Number</label>
                                    <input type="text" :name="'guardians['+idx+'][aadhar]'" x-model="g.aadhar" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2 px-3 text-xs focus:outline-none" placeholder="XXXX-XXXX-XXXX" maxlength="14"
                                           @input="g.aadhar = $event.target.value.replace(/[^0-9]/g, '').replace(/(\d{4})(?=\d)/g, '$1-')">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 0" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">← Back</button>
                <button type="submit" :disabled="loading"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90"
                        style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #ffffff !important;">
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Submit Application
                </button>
            </div>
        </div>

    </form>
</div>

<?php
function inputClass(string $field, array $errors): string {
    $base = 'w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all';
    return $base . (isset($errors[$field]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30');
}
function selectClass(string $field, array $errors): string {
    $base = 'w-full bg-slate-900/70 border text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all';
    return $base . (isset($errors[$field]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30');
}
function errorMsg(string $field, array $errors): string {
    if (!isset($errors[$field])) return '';
    return '<p class="text-xs text-red-400 mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' . htmlspecialchars($errors[$field]) . '</p>';
}
?>

<script>
function studentForm() {
    return {
        step: 0,
        loading: false,
        guardians: [{ name: '', relationship: 'Father', phone: '', email: '', aadhar: '' }],
        addGuardian() {
            this.guardians.push({ name: '', relationship: 'Guardian', phone: '', email: '', aadhar: '' });
        },
        removeGuardian(idx) {
            this.guardians.splice(idx, 1);
        },
        validateStep(targetStep) {
            const stepEl = document.querySelector(`[x-show="step === ${this.step}"]`);
            if (stepEl) {
                const inputs = stepEl.querySelectorAll('input, select, textarea');
                let isValid = true;
                for (let input of inputs) {
                    if (!input.checkValidity()) {
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

<?php
$content = ob_get_clean();
?>
