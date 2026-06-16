<?php
$layout    = 'app';
$pageTitle = 'Certificate Designer';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Certificates', 'url' => '/certificates'],
    ['label' => 'Visual Designer']
];
ob_start();
?>

<!-- Alpine.js is already globally loaded in layouts/app.php -->
<div x-data="{
    student_id: '',
    title: 'Certificate of Excellence',
    recipient_name: 'Ananya Sharma',
    description: 'for demonstrating outstanding performance, dedication, and exceptional progress in sensory and motor development milestones.',
    template: 'academic',
    border_style: 'gold',
    primary_color: '#d97706',
    font_family: 'Playfair Display',
    issuer_name: 'Pearl Special Needs Foundation',
    issuer_title: 'Authorized Director',
    
    // Auto-fill student name in live preview
    onStudentChange(event) {
        const select = event.target;
        const selectedText = select.options[select.selectedIndex].text;
        if (selectedText && this.student_id !== '') {
            // Extract student name, e.g. 'Ananya Sharma (GIS/2026/1234)'
            this.recipient_name = selectedText.split(' (')[0];
        }
    }
}" class="space-y-6">

    <!-- Designer Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Controls (5 cols wide) -->
        <div class="lg:col-span-5 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 p-5 shadow-sm space-y-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Design Options</h3>

            <form action="<?= url('certificates') ?>" method="POST" class="space-y-4">
                <?= \Core\View::csrf() ?>

                <input type="hidden" name="recipient_name" :value="recipient_name">

                <!-- 1. Student Select -->
                <div>
                    <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Select Recipient</label>
                    <select name="student_id" x-model="student_id" @change="onStudentChange($event)" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        <option value="">Select Student...</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?= $student['id'] ?>">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['admission_number']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Certificate Category / Type -->
                <div>
                    <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Certificate Type</label>
                    <select name="certificate_type" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        <option value="academic">Academic / Milestone Development</option>
                        <option value="sports">Sports / Activity Meet</option>
                        <option value="arts">Arts & Creativity</option>
                        <option value="participation">Special Participation</option>
                        <option value="custom">Custom Achievement</option>
                    </select>
                </div>

                <!-- 3. Title Input -->
                <div>
                    <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Certificate Title</label>
                    <input type="text" name="title" x-model="title" required placeholder="e.g. Certificate of Excellence"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                </div>

                <!-- 4. Description Textarea -->
                <div>
                    <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Description / Achievement Text</label>
                    <textarea name="description" x-model="description" rows="3" placeholder="Describe the achievement..."
                              class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <!-- 5. Split design tools -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Template Style</label>
                        <select name="template" x-model="template"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-1.5 px-2.5 text-xs focus:outline-none">
                            <option value="academic">Academic Gold</option>
                            <option value="creative">Creative Purple</option>
                            <option value="vibrant">Vibrant Sporty</option>
                            <option value="minimalist">Minimalist Navy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Border Style</label>
                        <select name="border_style" x-model="border_style"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-1.5 px-2.5 text-xs focus:outline-none">
                            <option value="gold">Ornate Gold</option>
                            <option value="classic">Classic Line</option>
                            <option value="double">Double Border</option>
                            <option value="none">No Border</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Font Family</label>
                        <select name="font_family" x-model="font_family"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-1.5 px-2.5 text-xs focus:outline-none">
                            <option value="Playfair Display">Serif (Elegant)</option>
                            <option value="Inter">Sans-Serif (Modern)</option>
                            <option value="Georgia">Georgia (Classic)</option>
                            <option value="monospace">Monospace</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Theme Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" x-model="primary_color"
                                   class="w-8 h-8 bg-transparent border-0 cursor-pointer focus:ring-0">
                            <span class="text-xs font-mono text-slate-500" x-text="primary_color"></span>
                        </div>
                    </div>
                </div>

                <!-- 6. Issuer Names -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Issuer Authority</label>
                        <input type="text" name="issuer_name" x-model="issuer_name" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Issuer Title</label>
                        <input type="text" name="issuer_title" x-model="issuer_title" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none">
                    </div>
                </div>

                <!-- Form Submit -->
                <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex gap-3">
                    <a href="<?= url('certificates') ?>"
                       class="flex-1 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-xs transition-all text-center">
                        Back
                    </a>
                    <button type="submit" :disabled="student_id === ''"
                            class="flex-1 py-2 text-white font-semibold rounded-xl text-xs transition-all text-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg, #10b981, #059669);">
                        Issue & Publish
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Live Canva Preview Canvas (7 cols wide) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Live Canva Preview</h3>
                <span class="text-3xs text-slate-400 italic">Adjust options to see real-time updates</span>
            </div>

            <!-- Canva Visual Board Canvas -->
            <div class="w-full border border-slate-200 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-xl p-8 bg-slate-100 dark:bg-slate-950/60 flex items-center justify-center min-h-[420px] transition-colors">
                
                <!-- The Dynamic Certificate layout template -->
                <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border-8 p-8 flex flex-col justify-between aspect-[1.414/1] relative select-none"
                     :class="{
                        'border-double border-amber-600': border_style === 'gold',
                        'border-indigo-600': border_style === 'classic' && template === 'creative',
                        'border-slate-700': border_style === 'classic' && template !== 'creative',
                        'border-[12px] border-double border-slate-500': border_style === 'double',
                        'border-0': border_style === 'none'
                     }"
                     :style="{ 
                        fontFamily: font_family,
                        borderColor: border_style !== 'gold' ? primary_color : '#d97706'
                     }">
                    
                    <!-- Background Template Overlay Effects -->
                    <div x-show="template === 'academic'" class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#d97706_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
                    <div x-show="template === 'creative'" class="absolute inset-0 opacity-[0.04] bg-gradient-to-br from-indigo-500 via-transparent to-pink-500 pointer-events-none"></div>
                    
                    <!-- Top Badge Graphics -->
                    <div class="flex flex-col items-center text-center mt-2">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br flex items-center justify-center shadow-md mb-2"
                             :class="{
                                'from-amber-400 to-yellow-600': template === 'academic',
                                'from-indigo-500 to-purple-600': template === 'creative',
                                'from-orange-500 to-red-600': template === 'vibrant',
                                'from-slate-700 to-slate-900': template === 'minimalist',
                             }">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11.57M12 11c0 3.517 1.009 6.799 2.753 9.571m3.44-2.04l-.054-.09a13.916 13.916 0 00-2.5-4.96M12 11a5 5 0 10-10 0 5 5 0 0010 0zm0 0a5 5 0 1010 0 5 5 0 00-10 0z"/></svg>
                        </div>
                        <h2 class="text-xl font-extrabold uppercase tracking-widest text-slate-800 dark:text-white"
                            :style="{ color: primary_color }"
                            x-text="title"></h2>
                        <p class="text-3xs text-slate-400 font-semibold uppercase tracking-widest mt-1">Award of Recognition</p>
                    </div>

                    <!-- Recipient & Body -->
                    <div class="text-center my-6 space-y-3">
                        <p class="text-2xs italic text-slate-500">This certifies that the student</p>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-wide"
                            x-text="recipient_name"></h3>
                        <p class="text-xs text-slate-650 dark:text-slate-350 max-w-md mx-auto leading-relaxed"
                           x-text="description"></p>
                    </div>

                    <!-- Footer / Signatures -->
                    <div class="flex items-end justify-between border-t border-slate-100 dark:border-slate-800/80 pt-4 px-6 mb-2">
                        <div class="text-left">
                            <span class="block text-4xs text-slate-400 font-mono">Date Issued</span>
                            <span class="text-2xs font-semibold text-slate-700 dark:text-slate-300"><?= date('M d, Y') ?></span>
                        </div>
                        
                        <!-- Signature Graphic Placeholder -->
                        <div class="text-center">
                            <span class="font-signature text-xs italic text-brand-600 dark:text-brand-400 block mb-1">Het Shah</span>
                            <div class="w-24 border-b border-slate-200 dark:border-slate-800 mx-auto"></div>
                            <span class="block text-4xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mt-1" x-text="issuer_name"></span>
                            <span class="block text-4xs text-slate-400" x-text="issuer_title"></span>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>

<!-- Simple Google Font import dynamically to preview visual styling changes -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap');
.font-signature {
    font-family: 'Playfair Display', cursive;
}
</style>

<?php
$content = ob_get_clean();
?>
