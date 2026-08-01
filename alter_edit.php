<?php
$file = 'resources/views/students/edit.php';
$content = file_get_contents($file);

$medicalSectionHtml = <<<HTML
        <!-- Medical & Support Information -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Medical & Support Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Disability Type <span class="text-red-400">*</span></label>
                    <select name="disability_type" required class="<?= selectClassE('disability_type') ?>">
                        <option value="Autism Spectrum Disorder" <?= (\$s['disability_type']??'') === 'Autism Spectrum Disorder' ? 'selected' : '' ?>>Autism Spectrum Disorder (ASD)</option>
                        <option value="ADHD" <?= (\$s['disability_type']??'') === 'ADHD' ? 'selected' : '' ?>>ADHD</option>
                        <option value="Cerebral Palsy" <?= (\$s['disability_type']??'') === 'Cerebral Palsy' ? 'selected' : '' ?>>Cerebral Palsy</option>
                        <option value="Down Syndrome" <?= (\$s['disability_type']??'') === 'Down Syndrome' ? 'selected' : '' ?>>Down Syndrome</option>
                        <option value="Learning Disability" <?= (\$s['disability_type']??'') === 'Learning Disability' ? 'selected' : '' ?>>Learning Disability</option>
                        <option value="Hearing Impairment" <?= (\$s['disability_type']??'') === 'Hearing Impairment' ? 'selected' : '' ?>>Hearing Impairment</option>
                        <option value="Visual Impairment" <?= (\$s['disability_type']??'') === 'Visual Impairment' ? 'selected' : '' ?>>Visual Impairment</option>
                        <option value="Other" <?= (\$s['disability_type']??'') === 'Other' ? 'selected' : '' ?>>Other / Multiple</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Disability Detail / Diagnosis</label>
                    <input type="text" name="disability_detail" value="<?= e(\$s['disability_detail']??'') ?>" class="<?= inputClassE('disability_detail') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Care Instructions</label>
                    <textarea name="care_instructions" rows="2" class="<?= inputClassE('care_instructions') ?>"><?= e(\$s['care_instructions']??'') ?></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Special Needs Summary</label>
                    <textarea name="special_needs_summary" rows="2" class="<?= inputClassE('special_needs_summary') ?>"><?= e(\$s['special_needs_summary']??'') ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Allergies</label>
                    <input type="text" name="allergies" value="<?= e(\$s['allergies']??'') ?>" class="<?= inputClassE('allergies') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Triggers</label>
                    <input type="text" name="triggers" value="<?= e(\$s['triggers']??'') ?>" class="<?= inputClassE('triggers') ?>">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Medications</label>
                    <input type="text" name="medications" value="<?= e(\$s['medications']??'') ?>" class="<?= inputClassE('medications') ?>">
                </div>
            </div>
        </div>

HTML;

// Inject before Guardian Details
$content = preg_replace('/<!-- Guardian Details -->/', $medicalSectionHtml . "        <!-- Guardian Details -->", $content);

file_put_contents($file, $content);
echo "Added medical section to edit.php\n";
