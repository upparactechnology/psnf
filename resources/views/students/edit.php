<?php
$layout    = 'app';
$pageTitle = 'Edit Student';
$s = $student;
$name = $s['first_name'] . ' ' . $s['last_name'];
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Students','url'=>'/students'],['label'=>$name,'url'=>'/students/'.$s['id']],['label'=>'Edit']];
ob_start();

function inputClassE(string $field, array $errors = []): string {
    $base = 'w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all';
    return $base . (isset($errors[$field]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30');
}
function selectClassE(string $field, array $errors = []): string {
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

    <form method="POST" action="<?= url('students/'.$s['id']) ?>" x-data="{ loading: false }" @submit="loading = true" enctype="multipart/form-data">
        <?= \Core\View::csrf() ?>

        <!-- Personal Information -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Personal Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">First Name <span class="text-red-400">*</span></label>
                    <input type="text" name="first_name" value="<?= e($s['first_name']) ?>" required class="<?= inputClassE('first_name') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Middle Name</label>
                    <input type="text" name="middle_name" value="<?= e($s['middle_name'] ?? '') ?>" class="<?= inputClassE('middle_name') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Last Name <span class="text-red-400">*</span></label>
                    <input type="text" name="last_name" value="<?= e($s['last_name']) ?>" required class="<?= inputClassE('last_name') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Gender</label>
                    <select name="gender" class="<?= selectClassE('gender') ?>">
                        <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $s['gender']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Date of Birth</label>
                    <input type="date" name="dob" value="<?= e($s['dob']) ?>" class="<?= inputClassE('dob') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Blood Group</label>
                    <select name="blood_group" class="<?= selectClassE('blood_group') ?>">
                        <?php foreach (['Unknown','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                        <option value="<?= $bg ?>" <?= ($s['blood_group']??'')===$bg?'selected':'' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School</label>
                    <select name="school_id" class="<?= selectClassE('school_id') ?>">
                        <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= $s['school_id']==$sc['id']?'selected':'' ?>><?= e($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Branch</label>
                    <select name="branch_id" class="<?= selectClassE('branch_id') ?>">
                        <?php foreach ($branches as $br): ?>
                        <option value="<?= $br['id'] ?>" <?= $s['branch_id']==$br['id']?'selected':'' ?>><?= e($br['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Class</label>
                    <input type="text" name="class" value="<?= e($s['class']??'') ?>" class="<?= inputClassE('class') ?>" placeholder="e.g. Grade 3">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Section</label>
                    <input type="text" name="section" value="<?= e($s['section']??'') ?>" class="<?= inputClassE('section') ?>" placeholder="e.g. A">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Academic Year</label>
                    <input type="text" name="academic_year" value="<?= e($s['academic_year']??'') ?>" class="<?= inputClassE('academic_year') ?>" placeholder="e.g. 2025-2026">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Aadhar Number</label>
                    <input type="text" name="aadhar_number" value="<?= e($s['aadhar_number']??'') ?>" class="<?= inputClassE('aadhar_number') ?>" placeholder="XXXX XXXX XXXX">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Mother Tongue</label>
                    <input type="text" name="mother_tongue" value="<?= e($s['mother_tongue']??'') ?>" class="<?= inputClassE('mother_tongue') ?>">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Address</label>
                <textarea name="address" rows="2" class="<?= inputClassE('address') ?>"><?= e($s['address']??'') ?></textarea>
            </div>
        </div>

        <!-- Disability -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Disability & Special Needs</h3>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Primary Disability</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <?php foreach (['ASD','ADHD','Down Syndrome','Cerebral Palsy','Dyslexia','Intellectual Disability','Hearing Impairment','Visual Impairment','Multiple Disabilities','Other'] as $d): ?>
                    <label class="relative flex cursor-pointer">
                        <input type="radio" name="disability_type" value="<?= $d ?>" class="peer sr-only" <?= $s['disability_type']===$d?'checked':'' ?>>
                        <span class="w-full text-center text-xs py-2 px-2 rounded-lg border border-slate-700/50 text-slate-400 peer-checked:border-brand-500/50 peer-checked:bg-brand-900/30 peer-checked:text-brand-400 hover:border-slate-600 transition-all"><?= $d ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Disability Detail</label>
                <textarea name="disability_detail" rows="2" class="<?= inputClassE('disability_detail') ?>"><?= e($s['disability_detail']??'') ?></textarea>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Care Instructions</label>
                <textarea name="care_instructions" rows="2" class="<?= inputClassE('care_instructions') ?>"><?= e($s['care_instructions']??'') ?></textarea>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Special Needs Summary</label>
                <textarea name="special_needs_summary" rows="2" class="<?= inputClassE('special_needs_summary') ?>"><?= e($s['special_needs_summary']??'') ?></textarea>
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
include VIEWS_PATH . '/layouts/app.php';
?>
