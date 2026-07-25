<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Online Enrollment Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="min-h-screen py-10 px-4 sm:px-6">

    <div x-data="enrollmentForm()" class="max-w-4xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Student Enrollment Application</h1>
            <p class="text-slate-400 text-sm max-w-xl mx-auto">Please fill in the student, parents, and authorized pickup persons details carefully. Documents and photos can be captured live via camera or uploaded.</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-2xl bg-red-950/40 border border-red-500/30 text-red-300 text-sm space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Please correct the following highlighted errors:
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs text-red-400 pl-2">
                <?php foreach ($errors as $field => $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Form Progress Indicator -->
        <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <template x-for="(sName, idx) in ['1. Student Info', '2. Parents Info', '3. Pickup Persons']" :key="idx">
                <div class="flex items-center gap-2 cursor-pointer" @click="if(idx <= step) step = idx">
                    <div :class="step >= idx ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-800 text-slate-500'" class="w-8 h-8 rounded-xl flex items-center justify-center text-xs transition-all">
                        <span x-text="idx + 1"></span>
                    </div>
                    <span :class="step >= idx ? 'text-white font-semibold' : 'text-slate-500'" class="text-xs hidden md:inline" x-text="sName"></span>
                </div>
            </template>
        </div>

        <form method="POST" action="/forms/student-enrollment" enctype="multipart/form-data" class="space-y-6">
            
            <!-- SECTION 1: STUDENT INFO -->
            <div x-show="step === 0" class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
                <h3 class="text-lg font-bold text-white border-b border-slate-700/60 pb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Student Personal Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="sm:col-span-3 space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Student Full Name (First, Middle, Last) <span class="text-red-400">*</span></label>
                        <input type="text" name="student_full_name" required value="<?= e($old['student_full_name'] ?? '') ?>" class="w-full bg-slate-900/80 border border-slate-700 text-white rounded-xl py-3 px-4 text-sm focus:border-indigo-500 outline-none" placeholder="e.g. John Alexander Smith">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Date of Birth <span class="text-red-400">*</span></label>
                        <input type="date" name="dob" required value="<?= e($old['dob'] ?? '') ?>" class="w-full bg-slate-900/80 border border-slate-700 text-white rounded-xl py-3 px-4 text-sm focus:border-indigo-500 outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Gender <span class="text-red-400">*</span></label>
                        <select name="gender" required class="w-full bg-slate-900/80 border border-slate-700 text-white rounded-xl py-3 px-4 text-sm focus:border-indigo-500 outline-none">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Student Aadhar Card No. <span class="text-slate-500 font-normal">(Optional)</span></label>
                        <input type="text" name="student_aadhar" value="<?= e($old['student_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar Number" pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-900/80 border border-slate-700 text-white rounded-xl py-3 px-4 text-sm focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300">Full Residential Address</label>
                    <textarea name="address" rows="3" class="w-full bg-slate-900/80 border border-slate-700 text-white rounded-xl py-3 px-4 text-sm focus:border-indigo-500 outline-none" placeholder="House No, Street, Landmark, City, Pincode..."><?= e($old['address'] ?? '') ?></textarea>
                </div>

                <!-- Student Photo & Student Aadhar Upload -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-800 pt-5">
                    <!-- Photo -->
                    <div class="space-y-3 bg-slate-900/40 p-4 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Student Passport Photo <span class="text-red-400">*</span></label>
                            <input type="file" name="student_photo" accept="image/*" @change="previewImage($event, 'student_prev', 'student_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="mt-2">
                                <button type="button" @click="openModal('student_photo_cam', 'student_prev', 'student_placeholder', 'Student Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 rounded-xl border border-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <input type="hidden" name="student_photo_cam" id="student_photo_cam">
                            </div>
                        </div>

                        <div class="flex items-center justify-center pt-2">
                            <div class="w-28 h-32 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                <img id="student_prev" class="hidden w-full h-full object-cover">
                                <div id="student_placeholder" class="text-slate-500 text-center p-2">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-[10px]">Photo Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Aadhar Document Upload -->
                    <div class="space-y-3 bg-slate-900/40 p-4 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Student Aadhar Document</label>
                            <p class="text-[11px] text-slate-500 mb-1.5">Required if Student Aadhar No. is entered above</p>
                            <input type="file" name="student_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'student_doc_prev', 'student_doc_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="mt-2">
                                <button type="button" @click="openModal('student_aadhar_doc_cam', 'student_doc_prev', 'student_doc_placeholder', 'Student Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 rounded-xl border border-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <input type="hidden" name="student_aadhar_doc_cam" id="student_aadhar_doc_cam">
                            </div>
                        </div>

                        <div class="flex items-center justify-center pt-2">
                            <div class="w-36 h-24 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                <img id="student_doc_prev" class="hidden w-full h-full object-cover">
                                <div id="student_doc_placeholder" class="text-slate-500 text-center p-2">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                    <span class="text-[10px]">Document Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="step = 1" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-2xl transition-all shadow-lg shadow-indigo-600/30">Next: Parents Info →</button>
                </div>
            </div>

            <!-- SECTION 2: PARENTS INFO -->
            <div x-show="step === 1" class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
                <h3 class="text-lg font-bold text-white border-b border-slate-700/60 pb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Parents Information & Document Uploads
                </h3>

                <!-- Father Details -->
                <div class="space-y-4 bg-slate-900/40 p-5 rounded-2xl border border-slate-800">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-wider">Father Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Father's Full Name <span class="text-red-400">*</span></label>
                            <input type="text" name="father_name" required value="<?= e($old['father_name'] ?? '') ?>" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Father Mobile Phone <span class="text-red-400">*</span></label>
                            <input type="tel" name="father_phone" required value="<?= e($old['father_phone'] ?? '') ?>" placeholder="10 Digit Mobile No." pattern="[0-9]{10}" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Father Aadhar Card No. <span class="text-red-400">*</span></label>
                            <input type="text" name="father_aadhar" required value="<?= e($old['father_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar No." pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start pt-2 border-t border-slate-800/60">
                        <!-- Father Photo -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Father Passport Photo <span class="text-red-400">*</span></label>
                            <input type="file" name="father_photo" accept="image/*" @change="previewImage($event, 'father_prev', 'father_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('father_photo_cam', 'father_prev', 'father_placeholder', 'Father Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 text-indigo-400 rounded-lg border border-slate-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <div class="w-20 h-24 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                    <img id="father_prev" class="hidden w-full h-full object-cover">
                                    <div id="father_placeholder" class="text-slate-500 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[9px]">Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="father_photo_cam" id="father_photo_cam">
                        </div>

                        <!-- Father Aadhar Doc -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Father Aadhar Document <span class="text-red-400">*</span></label>
                            <input type="file" name="father_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'father_doc_prev', 'father_doc_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('father_aadhar_doc_cam', 'father_doc_prev', 'father_doc_placeholder', 'Father Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 text-indigo-400 rounded-lg border border-slate-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <div class="w-28 h-20 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                    <img id="father_doc_prev" class="hidden w-full h-full object-cover">
                                    <div id="father_doc_placeholder" class="text-slate-500 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]">Doc Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="father_aadhar_doc_cam" id="father_aadhar_doc_cam">
                        </div>
                    </div>
                </div>

                <!-- Mother Details -->
                <div class="space-y-4 bg-slate-900/40 p-5 rounded-2xl border border-slate-800">
                    <h4 class="text-sm font-bold text-pink-400 uppercase tracking-wider">Mother Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Mother's Full Name <span class="text-red-400">*</span></label>
                            <input type="text" name="mother_name" required value="<?= e($old['mother_name'] ?? '') ?>" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Mother Mobile Phone <span class="text-red-400">*</span></label>
                            <input type="tel" name="mother_phone" required value="<?= e($old['mother_phone'] ?? '') ?>" placeholder="10 Digit Mobile No." pattern="[0-9]{10}" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-300">Mother Aadhar Card No. <span class="text-red-400">*</span></label>
                            <input type="text" name="mother_aadhar" required value="<?= e($old['mother_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar No." pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:border-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start pt-2 border-t border-slate-800/60">
                        <!-- Mother Photo -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Mother Passport Photo <span class="text-red-400">*</span></label>
                            <input type="file" name="mother_photo" accept="image/*" @change="previewImage($event, 'mother_prev', 'mother_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('mother_photo_cam', 'mother_prev', 'mother_placeholder', 'Mother Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 text-indigo-400 rounded-lg border border-slate-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <div class="w-20 h-24 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                    <img id="mother_prev" class="hidden w-full h-full object-cover">
                                    <div id="mother_placeholder" class="text-slate-500 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[9px]">Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="mother_photo_cam" id="mother_photo_cam">
                        </div>

                        <!-- Mother Aadhar Doc -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Mother / Guardian Aadhar Document <span class="text-red-400">*</span></label>
                            <input type="file" name="mother_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'mother_doc_prev', 'mother_doc_placeholder')" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('mother_aadhar_doc_cam', 'mother_doc_prev', 'mother_doc_placeholder', 'Mother Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-800 text-indigo-400 rounded-lg border border-slate-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <div class="w-28 h-20 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                    <img id="mother_doc_prev" class="hidden w-full h-full object-cover">
                                    <div id="mother_doc_placeholder" class="text-slate-500 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]">Doc Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="mother_aadhar_doc_cam" id="mother_aadhar_doc_cam">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <button type="button" @click="step = 0" class="px-5 py-2.5 border border-slate-700 text-slate-300 hover:text-white rounded-xl text-sm">← Back</button>
                    <button type="button" @click="step = 2" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-2xl transition-all shadow-lg shadow-indigo-600/30">Next: Pickup Persons →</button>
                </div>
            </div>

            <!-- SECTION 3: AUTHORIZED PICKUP PERSONS (MAX 3) -->
            <div x-show="step === 2" class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Authorized Pickup Persons (Max 3)
                    </h3>
                    <button type="button" x-show="pickups.length < 3" @click="addPickup()" class="text-xs font-bold px-3 py-1.5 bg-indigo-600/20 text-indigo-400 rounded-xl border border-indigo-500/30 hover:bg-indigo-600/30">
                        + Add Pickup Person
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(p, index) in pickups" :key="index">
                        <div class="bg-slate-900/50 p-5 rounded-2xl border border-slate-800 space-y-4 relative">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <span class="text-xs font-bold text-indigo-400 uppercase" x-text="'Pickup Person #' + (index + 1)"></span>
                                <button type="button" @click="removePickup(index)" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-300">Full Name <span class="text-red-400">*</span></label>
                                    <input type="text" :name="'pickups[' + index + '][name]'" required x-model="p.name" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2 px-3 text-sm focus:border-indigo-500 outline-none" placeholder="Full Name">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-300">Relationship to Student</label>
                                    <input type="text" :name="'pickups[' + index + '][relationship]'" x-model="p.relationship" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2 px-3 text-sm focus:border-indigo-500 outline-none" placeholder="e.g. Grandfather, Driver, Uncle">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-300">Phone Number</label>
                                    <input type="tel" :name="'pickups[' + index + '][phone]'" x-model="p.phone" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2 px-3 text-sm focus:border-indigo-500 outline-none" placeholder="+91 XXXXX XXXXX">
                                </div>
                                <div class="space-y-1 flex items-center pt-5">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :name="'pickups[' + index + '][is_emergency]'" x-model="p.is_emergency" value="1" class="w-4 h-4 rounded accent-indigo-600">
                                        <span class="text-xs font-medium text-slate-300">Mark as Emergency Contact</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Pickup Person Photo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center border-t border-slate-800/80 pt-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Passport Photo <span class="text-red-400">*</span></label>
                                    <input type="file" :name="'pickup_photo_' + index" accept="image/*" @change="previewImage($event, 'pickup_prev_' + index, 'pickup_placeholder_' + index)" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-600/20 file:text-indigo-300 cursor-pointer">
                                    <button type="button" @click="openModal('pickup_photo_cam_' + index, 'pickup_prev_' + index, 'pickup_placeholder_' + index, 'Pickup Person Photo')" class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold px-3 py-1 bg-slate-800 text-indigo-400 rounded-lg border border-slate-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                    </button>
                                    <input type="hidden" :name="'pickup_photo_cam_' + index" :id="'pickup_photo_cam_' + index">
                                </div>
                                <div class="flex justify-start">
                                    <div class="w-20 h-24 rounded-xl border border-slate-700 bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative shadow-inner">
                                        <img :id="'pickup_prev_' + index" class="hidden w-full h-full object-cover">
                                        <div :id="'pickup_placeholder_' + index" class="text-slate-500 text-center p-1">
                                            <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="text-[9px]">Photo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-800">
                    <button type="button" @click="step = 1" class="px-5 py-2.5 border border-slate-700 text-slate-300 hover:text-white rounded-xl text-sm">← Back</button>
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-sm rounded-2xl transition-all shadow-xl shadow-indigo-600/40">
                        Submit Enrollment Application
                    </button>
                </div>
            </div>

        </form>

        <!-- LARGE CAMERA CAPTURE MODAL -->
        <div x-show="showCamModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4" x-cloak>
            <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-2xl w-full space-y-5 shadow-2xl border border-slate-700 relative">
                
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2 text-white font-bold text-base">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-text="modalTitle"></span>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Big Video Container -->
                <div class="w-full h-80 sm:h-96 bg-black rounded-2xl overflow-hidden relative border border-slate-800 flex items-center justify-center">
                    <video id="modal_video" class="w-full h-full object-cover" autoplay></video>
                    <canvas id="modal_canvas" class="hidden"></canvas>
                </div>

                <!-- Action Controls -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="button" @click="captureFromModal()" class="px-7 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Capture & Save Photo
                    </button>
                </div>

            </div>
        </div>

    </div>

    <script>
        function enrollmentForm() {
            return {
                step: 0,
                pickups: [{ name: '', relationship: '', phone: '', email: '', address: '', is_emergency: true }],
                showCamModal: false,
                modalTitle: 'Capture Photo',
                activeInputId: null,
                activeImgId: null,
                activePlaceholderId: null,
                mediaStream: null,

                addPickup() {
                    if (this.pickups.length < 3) {
                        this.pickups.push({ name: '', relationship: '', phone: '', email: '', address: '', is_emergency: false });
                    }
                },
                removePickup(idx) {
                    this.pickups.splice(idx, 1);
                },
                previewImage(event, targetImgId, placeholderId) {
                    const file = event.target.files[0];
                    if (file) {
                        const img = document.getElementById(targetImgId);
                        const placeholder = document.getElementById(placeholderId);
                        if (file.type === 'application/pdf') {
                            img.classList.add('hidden');
                            if (placeholder) {
                                placeholder.innerHTML = '<svg class="w-8 h-8 mx-auto text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg><span class="text-[10px] text-indigo-300 block font-semibold mt-1">PDF Selected</span>';
                                placeholder.classList.remove('hidden');
                            }
                        } else {
                            img.src = URL.createObjectURL(file);
                            img.classList.remove('hidden');
                            if (placeholder) placeholder.classList.add('hidden');
                        }
                    }
                },
                openModal(inputId, imgId, placeholderId, title) {
                    this.activeInputId = inputId;
                    this.activeImgId = imgId;
                    this.activePlaceholderId = placeholderId;
                    this.modalTitle = title || 'Camera Capture';

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
                            .then(stream => {
                                this.mediaStream = stream;
                                const video = document.getElementById('modal_video');
                                video.srcObject = stream;
                                this.showCamModal = true;
                            })
                            .catch(err => alert("Unable to access camera: " + err.message));
                    }
                },
                closeModal() {
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => track.stop());
                        this.mediaStream = null;
                    }
                    this.showCamModal = false;
                },
                captureFromModal() {
                    const video = document.getElementById('modal_video');
                    const canvas = document.getElementById('modal_canvas');
                    canvas.width = video.videoWidth || 1280;
                    canvas.height = video.videoHeight || 720;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg');
                    
                    // Save base64 to target hidden input
                    const hiddenInput = document.getElementById(this.activeInputId);
                    if (hiddenInput) hiddenInput.value = dataUrl;

                    // Update target preview image
                    const img = document.getElementById(this.activeImgId);
                    if (img) {
                        img.src = dataUrl;
                        img.classList.remove('hidden');
                    }

                    // Hide placeholder
                    const placeholder = document.getElementById(this.activePlaceholderId);
                    if (placeholder) placeholder.classList.add('hidden');

                    this.closeModal();
                }
            }
        }
    </script>
</body>
</html>
