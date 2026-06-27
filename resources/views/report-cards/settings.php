<?php
$layout    = 'app';
$pageTitle = 'Report Cards PDF Customization Settings';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Report Cards', 'url' => '/report-cards'],
    ['label' => 'Settings']
];
ob_start();

$trusteesList = is_string($settings['trustees_config']) ? $settings['trustees_config'] : json_encode($settings['trustees_config']);
$defaultFieldsJson = json_encode(\App\Controllers\ReportCardController::getDefaultFields());
$fieldsConfigVal = is_string($settings['fields_config'] ?? '') && !empty($settings['fields_config'] ?? '') ? $settings['fields_config'] : json_encode($settings['fields_config'] ?? new stdClass());
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="reportCardSettings()">
    <!-- Header -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Report Card Layout Settings</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Customize default designations, dynamic signature stamp, fonts, colors, and branding details.</p>
        </div>
        <a href="<?= url('report-cards') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/50 transition-all shadow-2xs">
            Back to Registry
        </a>
    </div>

    <!-- Form -->
    <form method="POST" action="<?= url('report-cards/settings') ?>" enctype="multipart/form-data" class="space-y-6" @submit="document.getElementById('fields_config_input').value = JSON.stringify(fieldsConfig)">
        <?= \Core\View::csrf() ?>

        <!-- Section 1: School Branding & Theme Customizer -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm space-y-4">
            <h3 class="text-md font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800/60 pb-3">School Branding & Layout Customizations</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">School Name (Heading)</label>
                    <input type="text" name="school_name" value="<?= e($settings['school_name'] ?? 'Pearl Special Needs Foundation') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">School Subtitle</label>
                    <input type="text" name="school_subtitle" value="<?= e($settings['school_subtitle'] ?? 'Center for Special Education & Care') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">School Address (Cover & Footer Details)</label>
                    <input type="text" name="school_address" value="<?= e($settings['school_address'] ?? 'Ahmedabad, Gujarat, India · contact@pearlspecialneeds.org') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">PDF Font Style Choice</label>
                    <select name="pdf_font" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                        <?php foreach (['Inter' => 'Standard (Inter)', 'Cinzel' => 'Elegant serif (Cinzel)', 'Playfair Display' => 'Classic serif (Playfair Display)'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($settings['pdf_font'] ?? 'Inter') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Primary Accent Theme Color</label>
                    <div class="flex gap-3 items-center">
                        <input type="color" name="primary_color" value="<?= e($settings['primary_color'] ?? '#0d3827') ?>" class="w-12 h-10 border border-slate-200 dark:border-slate-800 bg-transparent rounded-lg cursor-pointer">
                        <input type="text" name="primary_color_hex" :value="primaryColor" @input="primaryColor = $event.target.value" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm font-mono shadow-2xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Stamp Details (Text & Image) -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm space-y-4">
            <h3 class="text-md font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800/60 pb-3">Official Stamp Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Custom Stamp Text</label>
                    <input type="text" name="stamp_text" value="<?= e($settings['stamp_text'] ?? 'Pearl Special Needs Foundation') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="e.g. Pearl Special Needs Foundation">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 uppercase">Official Stamp Image (PNG/JPG)</label>
                    <div class="flex gap-2 items-center">
                        <input type="file" id="stamp_file_input" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-450 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 dark:file:bg-brand-600/10 file:text-brand-600 dark:file:text-brand-400 cursor-pointer">
                        <button type="button" @click="uploadIndividualSignature('stamp_file_input', 'stamp_image')" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-md transition-all">
                            Upload
                        </button>
                    </div>
                    <input type="hidden" name="stamp_image_path" x-model="stampImage">
                    <input type="hidden" name="old_stamp_image" value="<?= e($settings['stamp_image'] ?? '') ?>">
                    
                    <div class="mt-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 flex items-center gap-3">
                        <span class="text-xs text-slate-450 dark:text-slate-500">Preview:</span>
                        <div class="h-10 w-28 bg-white border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded flex items-center justify-center">
                            <template x-if="stampImage">
                                <img :src="stampImage" class="max-h-full max-w-full object-contain">
                            </template>
                            <template x-if="!stampImage">
                                <span class="text-3xs text-slate-350 italic">None Uploaded</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Class Teacher Default Signature -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm space-y-4">
            <h3 class="text-md font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800/60 pb-3">Class Teacher Default Signature</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Class Teacher Designation/Name</label>
                    <input type="text" name="class_teacher_name" value="<?= e($settings['class_teacher_name'] ?? 'Class Teacher') ?>" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 shadow-2xs">
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 uppercase">Signature Image (PNG/JPG)</label>
                    <div class="flex gap-2 items-center">
                        <input type="file" id="ct_file_input" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-450 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 dark:file:bg-brand-600/10 file:text-brand-600 dark:file:text-brand-400 cursor-pointer">
                        <button type="button" @click="uploadIndividualSignature('ct_file_input', 'class_teacher_sig')" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-md transition-all">
                            Upload
                        </button>
                    </div>
                    <input type="hidden" name="class_teacher_sig_path" x-model="classTeacherSig">
                    <input type="hidden" name="old_class_teacher_sig" value="<?= e($settings['class_teacher_sig'] ?? '') ?>">
                    
                    <div class="mt-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 flex items-center gap-3">
                        <span class="text-xs text-slate-450 dark:text-slate-500">Preview:</span>
                        <div class="h-10 w-28 bg-white border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded flex items-center justify-center">
                            <template x-if="classTeacherSig">
                                <img :src="classTeacherSig" class="max-h-full max-w-full object-contain">
                            </template>
                            <template x-if="!classTeacherSig">
                                <span class="text-3xs text-slate-350 italic">None Uploaded</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Dynamic Trustees / Coordinators list -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/60 pb-3">
                <h3 class="text-md font-bold text-slate-800 dark:text-white">Dynamic Trustees & Authorized Signatories</h3>
                <button type="button" @click="addTrustee()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 transition-all shadow-md">
                    + Add Signatory
                </button>
            </div>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Add authorized trustees, managers, or coordinators. These will render in a balanced row in the PDF footer.</p>

            <div class="space-y-4">
                <template x-for="(trustee, index) in trustees" :key="index">
                    <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-950/10 space-y-4 relative shadow-2xs">
                        <button type="button" @click="removeTrustee(index)" class="absolute top-4 right-4 text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-6">
                            <div>
                                <label class="block text-2xs font-bold text-slate-500 uppercase mb-1">Official Name</label>
                                <input type="text" :name="'trustees[' + index + '][name]'" x-model="trustee.name" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 shadow-2xs">
                            </div>
                            <div>
                                <label class="block text-2xs font-bold text-slate-500 uppercase mb-1">Designation Title / Role</label>
                                <input type="text" :name="'trustees[' + index + '][title]'" x-model="trustee.title" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="e.g. Trustee-PSNF or MT-PSNF">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            <div>
                                <label class="block text-2xs font-bold text-slate-500 uppercase mb-1">Upload Signature File (PNG/JPG)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="file" :id="'trustee_file_input_' + index" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-2xs file:font-semibold file:bg-brand-50 dark:file:bg-brand-600/10 file:text-brand-600 dark:file:text-brand-400 cursor-pointer">
                                    <button type="button" @click="uploadIndividualSignature('trustee_file_input_' + index, index)" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-2xs font-semibold shadow-md transition-all">
                                        Upload
                                    </button>
                                </div>
                                <input type="hidden" :name="'trustees[' + index + '][sig]'" x-model="trustee.sig">
                                <input type="hidden" :name="'trustees[' + index + '][old_sig]'" x-model="trustee.sig">
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-2xs text-slate-450 dark:text-slate-500">Current Signature Preview:</span>
                                <div class="h-10 w-28 bg-white border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded flex items-center justify-center">
                                    <template x-if="trustee.sig">
                                        <img :src="trustee.sig" class="max-h-full max-w-full object-contain">
                                    </template>
                                    <template x-if="!trustee.sig">
                                        <span class="text-3xs text-slate-350 italic">None Uploaded</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="trustees.length === 0">
                    <div class="text-center py-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                        <p class="text-xs text-slate-500">No authorized signatories added. Click '+ Add Signatory' above to configure.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section 5: Customizable Fields & Groups -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/60 pb-3">
                <div>
                    <h3 class="text-md font-bold text-slate-800 dark:text-white">Report Card Fields & Parameters</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Configure custom evaluation parameters, subject profiles, and co-curricular lists per Academic Year & Semester.</p>
                </div>
                <button type="button" @click="loadDefaults()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 dark:bg-brand-600/10 dark:text-brand-400 transition-all shadow-2xs">
                    Load Defaults
                </button>
            </div>

            <!-- Select Year & Semester -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Academic Year</label>
                    <select x-model="currentYear" @change="updateActiveFieldsList()" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        <option value="2023-24">2023-24</option>
                        <option value="2024-25">2024-25</option>
                        <option value="2025-26">2025-26</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Semester</label>
                    <select x-model="currentSem" @change="updateActiveFieldsList()" class="w-full bg-white dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        <option value="Semester 1">Semester 1</option>
                        <option value="Semester 2">Semester 2</option>
                    </select>
                </div>
            </div>

            <input type="hidden" id="fields_config_input" name="fields_config" value="">

            <!-- Parameter Editor Groups -->
            <div class="space-y-6 pt-4 border-t border-slate-100 dark:border-slate-800/60">
                <!-- Tabs -->
                <div class="flex gap-2 border-b border-slate-200 dark:border-slate-850 pb-2">
                    <button type="button" @click="activeGroup = 'routine'; updateActiveFieldsList()" :class="activeGroup === 'routine' ? 'bg-brand-500 text-white' : 'text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        Routine Profile
                    </button>
                    <button type="button" @click="activeGroup = 'skills'; updateActiveFieldsList()" :class="activeGroup === 'skills' ? 'bg-brand-500 text-white' : 'text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        Learning Skills
                    </button>
                    <button type="button" @click="activeGroup = 'academics'; updateActiveFieldsList()" :class="activeGroup === 'academics' ? 'bg-brand-500 text-white' : 'text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        Academic Subjects
                    </button>
                    <button type="button" @click="activeGroup = 'cocurricular'; updateActiveFieldsList()" :class="activeGroup === 'cocurricular' ? 'bg-brand-500 text-white' : 'text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        Co-curricular
                    </button>
                </div>

                <!-- Parameters List -->
                <div class="space-y-3">
                    <template x-for="(item, index) in activeFieldsList" :key="item.key">
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20">
                            <span class="text-xs font-mono bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1 rounded-md" x-text="item.key"></span>
                            <input type="text" x-model="item.label" @input="syncFieldsConfig()" class="flex-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <div class="flex items-center gap-1.5">
                                <template x-if="confirmDeleteKey === item.key">
                                    <button type="button" @click="deleteField(item.key)" class="px-2 py-1 rounded-lg text-3xs font-bold bg-red-500 hover:bg-red-650 text-white shadow-xs transition-all">
                                        Delete?
                                    </button>
                                </template>
                                <template x-if="confirmDeleteKey === item.key">
                                    <button type="button" @click="confirmDeleteKey = null" class="px-2 py-1 rounded-lg text-3xs font-semibold bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-all">
                                        Cancel
                                    </button>
                                </template>
                                <template x-if="confirmDeleteKey !== item.key">
                                    <button type="button" @click="confirmDeleteKey = item.key" class="text-slate-400 hover:text-red-500 transition-colors p-1.5" title="Delete Parameter">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Add New Field Form -->
                    <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/20 dark:bg-slate-950/10 space-y-3">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">+ Add New Parameter</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="text" x-model="newFieldKey" placeholder="Parameter Key (e.g. motor_skills)" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <input type="text" x-model="newFieldLabel" placeholder="Parameter Display Label (e.g. Fine Motor Skills)" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="addField()" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-md transition-all">
                                Add Parameter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-end gap-3 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 shadow-sm">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all shadow-xl hover:opacity-95" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save Settings Configuration
            </button>
        </div>
    </form>
</div>

<script>
function reportCardSettings() {
    return {
        trustees: <?= $trusteesList ?>,
        primaryColor: '<?= $settings['primary_color'] ?? '#0d3827' ?>',
        classTeacherSig: '<?= $settings['class_teacher_sig'] ?? '' ?>',
        stampImage: '<?= $settings['stamp_image'] ?? '' ?>',
        fieldsConfig: <?= $fieldsConfigVal ?>,
        defaultFields: <?= $defaultFieldsJson ?>,
        confirmDeleteKey: null,
        currentYear: '2023-24',
        currentSem: 'Semester 2',
        activeGroup: 'routine',
        activeFieldsList: [],
        newFieldKey: '',
        newFieldLabel: '',
        init() {
            if (!this.fieldsConfig) this.fieldsConfig = {};
            const years = ['2023-24', '2024-25', '2025-26'];
            const semesters = ['Semester 1', 'Semester 2'];
            years.forEach(y => {
                if (!this.fieldsConfig[y]) this.fieldsConfig[y] = {};
                semesters.forEach(s => {
                    if (!this.fieldsConfig[y][s]) {
                        this.fieldsConfig[y][s] = JSON.parse(JSON.stringify(this.defaultFields));
                    }
                });
            });
            this.updateActiveFieldsList();
        },
        updateActiveFieldsList() {
            if (!this.fieldsConfig[this.currentYear]) this.fieldsConfig[this.currentYear] = {};
            if (!this.fieldsConfig[this.currentYear][this.currentSem]) {
                this.fieldsConfig[this.currentYear][this.currentSem] = JSON.parse(JSON.stringify(this.defaultFields));
            }
            const groupData = this.fieldsConfig[this.currentYear][this.currentSem][this.activeGroup] || {};
            this.activeFieldsList = Object.keys(groupData).map(k => ({ key: k, label: groupData[k] }));
        },
        syncFieldsConfig() {
            if (!this.fieldsConfig[this.currentYear]) this.fieldsConfig[this.currentYear] = {};
            if (!this.fieldsConfig[this.currentYear][this.currentSem]) {
                this.fieldsConfig[this.currentYear][this.currentSem] = {};
            }
            
            const groupData = {};
            this.activeFieldsList.forEach(item => {
                groupData[item.key] = item.label;
            });
            
            this.fieldsConfig[this.currentYear][this.currentSem][this.activeGroup] = groupData;
        },
        loadDefaults() {
            if (confirm('Are you sure you want to load the default parameters for ' + this.currentYear + ' ' + this.currentSem + '? This will overwrite current changes for this semester.')) {
                if (!this.fieldsConfig) this.fieldsConfig = {};
                if (!this.fieldsConfig[this.currentYear]) this.fieldsConfig[this.currentYear] = {};
                this.fieldsConfig[this.currentYear][this.currentSem] = JSON.parse(JSON.stringify(this.defaultFields));
                this.updateActiveFieldsList();
            }
        },
        addField() {
            if (!this.newFieldKey || !this.newFieldLabel) {
                alert('Key and Label are required.');
                return;
            }
            const key = this.newFieldKey.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
            const label = this.newFieldLabel.trim();
            
            if (this.activeFieldsList.some(item => item.key === key)) {
                alert('Parameter key already exists.');
                return;
            }
            
            this.activeFieldsList.push({ key: key, label: label });
            this.newFieldKey = '';
            this.newFieldLabel = '';
            this.syncFieldsConfig();
        },
        deleteField(key) {
            this.activeFieldsList = this.activeFieldsList.filter(item => item.key !== key);
            this.syncFieldsConfig();
            this.confirmDeleteKey = null;
        },
        addTrustee() {
            this.trustees.push({name: '', title: '', sig: ''});
        },
        removeTrustee(index) {
            this.trustees.splice(index, 1);
        },
        async uploadIndividualSignature(inputId, target) {
            const input = document.getElementById(inputId);
            if (!input || !input.files || input.files.length === 0) {
                alert('Please select a file first.');
                return;
            }
            
            const file = input.files[0];
            const formData = new FormData();
            formData.append('signature', file);
            
            const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value || document.querySelector('input[name="_csrf"]')?.value;
            if (csrfToken) {
                formData.append('_csrf', csrfToken);
            }
            
            try {
                const response = await fetch('<?= url("report-cards/settings/upload-signature") ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    if (target === 'class_teacher_sig') {
                        this.classTeacherSig = result.path;
                    } else if (target === 'stamp_image') {
                        this.stampImage = result.path;
                    } else {
                        this.trustees[target].sig = result.path;
                    }
                    alert('Signature uploaded successfully!');
                } else {
                    alert('Upload failed: ' + result.message);
                }
            } catch (err) {
                alert('Error uploading file: ' + err.message);
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
?>
