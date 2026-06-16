<?php
$layout    = 'app';
$pageTitle = 'New Student Application';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Students', 'url' => '/students'], ['label' => 'New Application']];
ob_start();
$errors  = \Core\Session::getFlash('errors') ?? [];
$old     = \Core\Session::getFlash('old') ?? [];
$fn      = fn($key) => $old[$key] ?? '';
?>

<div x-data="studentForm()" class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">New Student Application</h2>
            <p class="text-sm text-slate-500 mt-0.5">Fill in the student admission details</p>
        </div>
        <a href="<?= url('students') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <!-- Progress Steps -->
    <div class="flex items-center gap-0 mb-2">
        <?php
        $steps = ['Personal Info', 'Disability & Care', 'Guardian', 'Medical'];
        foreach ($steps as $i => $step):
        ?>
        <div class="flex items-center <?= $i < count($steps) - 1 ? 'flex-1' : '' ?>">
            <div class="flex items-center gap-2">
                <div :class="step >= <?= $i ?> ? 'bg-brand-600 text-white' : 'bg-slate-800 text-slate-500'"
                     class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all">
                    <?= $i + 1 ?>
                </div>
                <span :class="step >= <?= $i ?> ? 'text-white' : 'text-slate-500'"
                      class="text-xs font-medium hidden sm:block transition-colors"><?= $step ?></span>
            </div>
            <?php if ($i < count($steps) - 1): ?>
            <div class="flex-1 mx-3 h-0.5" :class="step > <?= $i ?> ? 'bg-brand-600' : 'bg-slate-800'"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= url('students') ?>" enctype="multipart/form-data" class="space-y-5">
        <?= \Core\View::csrf() ?>

        <!-- Step 0: Personal Info -->
        <div x-show="step === 0" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Personal Information</h3>

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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Gender <span class="text-red-400">*</span></label>
                        <select name="gender" required class="<?= selectClass('gender', $errors) ?>">
                            <option value="">Select gender</option>
                            <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $fn('gender') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Date of Birth <span class="text-red-400">*</span></label>
                        <input type="date" name="dob" value="<?= e($fn('dob')) ?>" required class="<?= inputClass('dob', $errors) ?>">
                        <?= errorMsg('dob', $errors) ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Blood Group</label>
                        <select name="blood_group" class="<?= selectClass('blood_group', $errors) ?>">
                            <option value="Unknown">Unknown</option>
                            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= $fn('blood_group') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">School <span class="text-red-400">*</span></label>
                        <select name="school_id" required class="<?= selectClass('school_id', $errors) ?>" @change="loadBranches($event.target.value)">
                            <option value="">Select school</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?= $school['id'] ?>" <?= $fn('school_id') == $school['id'] ? 'selected' : '' ?>><?= e($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                        <select name="branch_id" required class="<?= selectClass('branch_id', $errors) ?>" x-model="selectedBranch">
                            <option value="">Select branch</option>
                            <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['id'] ?>" <?= $fn('branch_id') == $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Aadhar Number</label>
                        <input type="text" name="aadhar_number" value="<?= e($fn('aadhar_number')) ?>" class="<?= inputClass('aadhar_number', $errors) ?>" placeholder="XXXX XXXX XXXX" maxlength="14">
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
                <button type="button" @click="step = 1" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    Next: Disability & Care <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 1: Disability -->
        <div x-show="step === 1" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Disability & Special Needs</h3>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Primary Disability <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php
                        $disabilities = ['ASD', 'ADHD', 'Down Syndrome', 'Cerebral Palsy', 'Dyslexia', 'Intellectual Disability', 'Hearing Impairment', 'Visual Impairment', 'Multiple Disabilities', 'Other'];
                        foreach ($disabilities as $d):
                        ?>
                        <label class="relative flex cursor-pointer">
                            <input type="radio" name="disability_type" value="<?= $d ?>" class="peer sr-only" <?= $fn('disability_type') === $d ? 'checked' : '' ?> required>
                            <span class="w-full text-center text-xs py-2.5 px-3 rounded-xl border border-slate-700/50 text-slate-400 peer-checked:border-brand-500/50 peer-checked:bg-brand-900/30 peer-checked:text-brand-400 hover:border-slate-600 transition-all">
                                <?= $d ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?= errorMsg('disability_type', $errors) ?>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Disability Details</label>
                    <textarea name="disability_detail" rows="3" class="<?= inputClass('disability_detail', $errors) ?>" placeholder="Describe the disability in detail, any specific challenges..."><?= e($fn('disability_detail')) ?></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Care Instructions</label>
                    <textarea name="care_instructions" rows="3" class="<?= inputClass('care_instructions', $errors) ?>" placeholder="Special care requirements, behavior management strategies..."><?= e($fn('care_instructions')) ?></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Special Needs Summary</label>
                    <textarea name="special_needs_summary" rows="2" class="<?= inputClass('special_needs_summary', $errors) ?>" placeholder="Brief summary for quick reference"><?= e($fn('special_needs_summary')) ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 0" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">← Back</button>
                <button type="button" @click="step = 2" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    Next: Guardian <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Guardian -->
        <div x-show="step === 2" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Primary Guardian / Parent</h3>
                <p class="text-xs text-slate-500">Guardian can be linked after admission. Providing now is optional.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Guardian Name</label>
                        <input type="text" name="guardian_name" value="<?= e($fn('guardian_name')) ?>" class="<?= inputClass('guardian_name', $errors) ?>" placeholder="Full name">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Relationship</label>
                        <select name="guardian_relationship" class="<?= selectClass('guardian_relationship', $errors) ?>">
                            <option value="">Select</option>
                            <?php foreach (['Father', 'Mother', 'Guardian', 'Sibling', 'Grandparent', 'Other'] as $r): ?>
                            <option value="<?= $r ?>" <?= $fn('guardian_relationship') === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Phone Number</label>
                        <input type="tel" name="guardian_phone" value="<?= e($fn('guardian_phone')) ?>" class="<?= inputClass('guardian_phone', $errors) ?>" placeholder="+91 XXXXX XXXXX">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Email</label>
                        <input type="email" name="guardian_email" value="<?= e($fn('guardian_email')) ?>" class="<?= inputClass('guardian_email', $errors) ?>" placeholder="parent@email.com">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 1" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">← Back</button>
                <button type="button" @click="step = 3" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    Next: Medical <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Medical -->
        <div x-show="step === 3" class="space-y-5">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Medical Information <span class="text-xs text-slate-500 font-normal">(can be added later)</span></h3>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Known Allergies</label>
                    <textarea name="allergies" rows="2" class="<?= inputClass('allergies', $errors) ?>" placeholder="List known allergies..."><?= e($fn('allergies')) ?></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Triggers / Sensitivities</label>
                    <textarea name="triggers" rows="2" class="<?= inputClass('triggers', $errors) ?>" placeholder="Known behavioral triggers, sensory sensitivities..."><?= e($fn('triggers')) ?></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Current Medications</label>
                    <textarea name="medications" rows="2" class="<?= inputClass('medications', $errors) ?>" placeholder="Medication name, dosage, frequency..."><?= e($fn('medications')) ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="step = 2" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">← Back</button>
                <button type="submit" x-data="{ loading: false }" @click="loading = true" :disabled="loading"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90"
                        style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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
        step: <?= empty($errors) ? 0 : 0 ?>,
        selectedBranch: '',
        allBranches: <?= json_encode($branches) ?>,
        loadBranches(schoolId) {
            // Filter branches by school (already loaded)
        }
    }
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/app.php';
?>
