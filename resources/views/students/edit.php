<?php
$layout    = 'app';
$pageTitle = 'Edit Student';
$s = $student;
$name = $s['first_name'] . ' ' . $s['last_name'];
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Students','url'=>'/students'],['label'=>$name,'url'=>'/students/'.$s['id']],['label'=>'Edit']];
ob_start();

$errors = flash('errors') ?? [];

function inputClassE(string $field): string {
    global $errors;
    $base = 'w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all';
    return $base . (isset($errors[$field]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30');
}
function selectClassE(string $field): string {
    global $errors;
    $base = 'w-full bg-slate-900/70 border text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all';
    return $base . (isset($errors[$field]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30');
}
?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Edit Student</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= e($name) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('students/'.$s['id']) ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← View Profile</a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4 text-sm text-red-400">
            <strong class="font-bold">Please fix the following errors:</strong>
            <ul class="list-disc ml-5 mt-2 space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('students/'.$s['id']) ?>" 
          x-data="{ 
              loading: false,
              guardians: <?= htmlspecialchars(json_encode(!empty($guardians) ? $guardians : [['name' => '', 'relationship' => 'Father', 'phone' => '', 'email' => '', 'aadhar' => '']]), ENT_QUOTES, 'UTF-8') ?>,
              addGuardian() {
                  this.guardians.push({ name: '', relationship: 'Guardian', phone: '', email: '', aadhar: '' });
              },
              removeGuardian(idx) {
                  this.guardians.splice(idx, 1);
              }
          }" 
          @submit="loading = true" enctype="multipart/form-data">
        <?= \Core\View::csrf() ?>

        <!-- Personal Information -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Personal Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">First Name <span class="text-red-400">*</span></label>
                    <input type="text" name="first_name" value="<?= e($s['first_name']??'') ?>" required class="<?= inputClassE('first_name') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Last Name <span class="text-red-400">*</span></label>
                    <input type="text" name="last_name" value="<?= e($s['last_name']??'') ?>" required class="<?= inputClassE('last_name') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Date of Birth <span class="text-red-400">*</span></label>
                    <input type="date" name="dob" value="<?= e($s['dob']??'') ?>" required class="<?= inputClassE('dob') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Roll Number</label>
                    <input type="text" name="roll_number" value="<?= e($s['roll_number']??'') ?>" class="<?= inputClassE('roll_number') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Gender <span class="text-red-400">*</span></label>
                    <select name="gender" required class="<?= selectClassE('gender') ?>">
                        <option value="male" <?= ($s['gender']??'') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($s['gender']??'') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($s['gender']??'') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Blood Group</label>
                    <select name="blood_group" class="<?= selectClassE('blood_group') ?>">
                        <option value="Unknown" <?= ($s['blood_group']??'') === 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                        <option value="<?= $bg ?>" <?= ($s['blood_group']??'') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School <span class="text-red-400">*</span></label>
                    <select name="school_id" required class="<?= selectClassE('school_id') ?>">
                        <?php foreach ($schools as $school): ?>
                        <option value="<?= $school['id'] ?>" <?= ($s['school_id']??0) == $school['id'] ? 'selected' : '' ?>><?= e($school['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                    <select name="branch_id" required class="<?= selectClassE('branch_id') ?>">
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>" <?= ($s['branch_id']??0) == $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Student Aadhaar Number</label>
                    <input type="text" name="aadhar_number" value="<?= e($s['aadhar_number']??'') ?>" class="<?= inputClassE('aadhar_number') ?>" placeholder="XXXX-XXXX-XXXX" maxlength="14"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\d{4})(?=\d)/g, '$1-')">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Address</label>
                    <textarea name="address" rows="2" class="<?= inputClassE('address') ?>"><?= e($s['address']??'') ?></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Mother Tongue</label>
                    <input type="text" name="mother_tongue" value="<?= e($s['mother_tongue']??'') ?>" class="<?= inputClassE('mother_tongue') ?>">
                </div>
            </div>
        </div>

                <!-- Guardian Details -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-semibold text-slate-300">Guardians / Parents Details</h3>
                <button type="button" @click="addGuardian()" class="text-xs text-indigo-400 font-bold hover:underline">+ Add Another Guardian</button>
            </div>

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

        <!-- Notes -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 mb-4">Internal Notes</h3>
            <textarea name="notes" rows="3" class="<?= inputClassE('notes') ?>" placeholder="Internal notes visible to staff only..."><?= e($s['notes']??'') ?></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="<?= url('students/'.$s['id']) ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">Cancel</a>
            <button type="submit" :disabled="loading"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90"
                    style="background: linear-gradient(135deg, #6366f1, #a855f7);"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
?>
