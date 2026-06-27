<?php
$layout    = 'app';
$pageTitle = 'Receipt Settings';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Receipts', 'url' => '/receipts'], ['label' => 'Receipt Designer']];

$tSettings = json_decode($tenant['settings'] ?? '{}', true) ?: [];
$receipt = $tSettings['receipt'] ?? [];

$headerTitle = $receipt['header_title'] ?? 'Official Fee Payment Receipt';
$footerNotes = $receipt['footer_notes'] ?? 'This receipt is automatically generated and serves as official proof of payment.';
$showWatermark = ($receipt['show_watermark'] ?? '1') == '1';
$accentColor = $receipt['accent_color'] ?? '#6366f1';
$fontFamily = $receipt['font_family'] ?? 'Inter';
$bgImage = $receipt['bg_image'] ?? '';
$savedMappings = $receipt['mappings'] ?? [];

$defaultMappings = [
    ['key' => 'tenant_name',     'label' => 'Organization Name', 'x' => 5,  'y' => 5,  'font_size' => 16, 'font_weight' => 'bold'],
    ['key' => 'header_title',    'label' => 'Receipt Title',     'x' => 5,  'y' => 12, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'receipt_number',  'label' => 'Voucher Number',    'x' => 70, 'y' => 5,  'font_size' => 16, 'font_weight' => 'bold'],
    ['key' => 'paid_at',         'label' => 'Paid Date',         'x' => 70, 'y' => 12, 'font_size' => 10, 'font_weight' => 'normal'],
    ['key' => 'student_name',    'label' => 'Student Name',      'x' => 5,  'y' => 24, 'font_size' => 14, 'font_weight' => 'bold'],
    ['key' => 'admission_num',   'label' => 'Admission Number',  'x' => 5,  'y' => 30, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'class_section',   'label' => 'Class & Section',   'x' => 5,  'y' => 35, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'payment_method',  'label' => 'Payment Method',    'x' => 55, 'y' => 24, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'payment_ref',     'label' => 'Transaction ID',    'x' => 55, 'y' => 30, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'invoice_number',  'label' => 'Invoice Number',    'x' => 55, 'y' => 35, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'account_items',   'label' => 'Account Items',     'x' => 5,  'y' => 45, 'font_size' => 11, 'font_weight' => 'normal'],
    ['key' => 'amount',          'label' => 'Total Amount',      'x' => 60, 'y' => 75, 'font_size' => 14, 'font_weight' => 'bold'],
    ['key' => 'footer_notes',    'label' => 'Footer Notes',      'x' => 5,  'y' => 90, 'font_size' => 9,  'font_weight' => 'normal'],
];

// Merge saved mappings with defaults if some keys are missing
$mappingsMap = [];
foreach ($savedMappings as $map) {
    if (isset($map['key'])) {
        $mappingsMap[$map['key']] = $map;
    }
}

$finalMappings = [];
foreach ($defaultMappings as $def) {
    if (isset($mappingsMap[$def['key']])) {
        // Merge coordinate changes and options
        $finalMappings[] = array_merge($def, $mappingsMap[$def['key']]);
    } else {
        $finalMappings[] = $def;
    }
}

ob_start();
?>

<style>
.bg-grid {
    background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px);
    background-size: 20px 20px;
}
</style>

<div x-data="{
    header_title: '<?= e(addslashes($headerTitle)) ?>',
    footer_notes: '<?= e(addslashes($footerNotes)) ?>',
    show_watermark: <?= $showWatermark ? 'true' : 'false' ?>,
    accent_color: '<?= e($accentColor) ?>',
    font_family: '<?= e($fontFamily) ?>',
    bg_preview: '<?= !empty($bgImage) ? url('storage/uploads/receipt_templates/' . $bgImage) : '' ?>',
    selected_index: null,
    
    mappings: <?= json_encode($finalMappings) ?>,
    
    handleBgChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.bg_preview = URL.createObjectURL(file);
        }
    },
    
    startDrag(event, index) {
        event.preventDefault();
        this.selected_index = index;
        const item = this.mappings[index];
        const canvas = this.$refs.canvas;
        const rect = canvas.getBoundingClientRect();
        
        const startMouseX = event.clientX;
        const startMouseY = event.clientY;
        const startElemX = item.x;
        const startElemY = item.y;
        
        const onMouseMove = (moveEvent) => {
            const deltaX = ((moveEvent.clientX - startMouseX) / rect.width) * 100;
            const deltaY = ((moveEvent.clientY - startMouseY) / rect.height) * 100;
            
            // Constrain values within canvas boundaries (0-100)
            item.x = Math.max(0, Math.min(100, Math.round(startElemX + deltaX)));
            item.y = Math.max(0, Math.min(100, Math.round(startElemY + deltaY)));
        };
        
        const onMouseUp = () => {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }
}" class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Canva Receipt Layout Designer</h2>
            <p class="text-sm text-slate-500 mt-0.5">Drag and drop details below to customize exactly where values render on your printable receipt template</p>
        </div>
        <a href="<?= url('receipts') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">← Back to Registry</a>
    </div>

    <!-- Designer Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Controls (5 cols wide) -->
        <div class="lg:col-span-5 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl space-y-6">
            <h3 class="text-base font-bold text-slate-200 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Design Parameters
            </h3>

            <form action="<?= url('receipts/settings') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?= \Core\View::csrf() ?>
                
                <input type="hidden" name="mappings_json" :value="JSON.stringify(mappings)">

                <!-- Background Template Upload -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Receipt Canvas Template (JPG/PNG)</label>
                    <input type="file" name="receipt_bg" accept=".png,.jpg,.jpeg" @change="handleBgChange($event)"
                           class="w-full bg-slate-950/70 border border-slate-800 text-slate-400 placeholder-slate-650 rounded-xl py-2 px-3 text-xs focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-brand-600/20 file:text-brand-400 hover:file:bg-brand-600/30 cursor-pointer">
                    <p class="text-3xs text-slate-500 italic mt-0.5">Upload a blank template background. Drag-and-drop elements on the right to position content.</p>
                </div>

                <!-- Text Header Options -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Receipt Title</label>
                        <input type="text" name="receipt_header_title" x-model="header_title" required
                               class="w-full bg-slate-950/70 border border-slate-800 text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Paid Stamp</label>
                        <select name="receipt_show_watermark" x-model="show_watermark"
                                class="w-full bg-slate-950/70 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="1">Show</option>
                            <option value="0">Hide</option>
                        </select>
                    </div>
                </div>

                <!-- Design Fonts / Accent Colors -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Font Family</label>
                        <select name="receipt_font_family" x-model="font_family"
                                class="w-full bg-slate-950/70 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="Inter">Sans-Serif (Modern)</option>
                            <option value="Playfair Display">Serif (Elegant)</option>
                            <option value="Georgia">Georgia (Classic)</option>
                            <option value="monospace">Monospace</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Accent Color</label>
                        <div class="flex items-center gap-2 bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3">
                            <input type="color" name="receipt_accent_color" x-model="accent_color"
                                   class="w-7 h-7 bg-transparent border-0 cursor-pointer focus:ring-0 rounded">
                            <span class="text-[10px] font-mono text-slate-400" x-text="accent_color"></span>
                        </div>
                    </div>
                </div>

                <!-- Selected Element Settings -->
                <div class="rounded-xl bg-slate-950/30 border border-slate-800 p-4 space-y-3" x-show="selected_index !== null">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-1.5">
                        <span class="text-3xs font-bold text-indigo-400 uppercase tracking-wider">Selected Field Details</span>
                        <span class="text-[10px] text-slate-500" x-text="mappings[selected_index]?.label"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="space-y-1">
                            <label class="block text-4xs text-slate-400 font-semibold uppercase">Font Size (px)</label>
                            <input type="number" min="8" max="48" x-model.number="mappings[selected_index].font_size"
                                   class="w-full bg-slate-900 border border-slate-800 text-white rounded-lg py-1 px-2 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-4xs text-slate-400 font-semibold uppercase">Font Weight</label>
                            <select x-model="mappings[selected_index].font_weight"
                                    class="w-full bg-slate-900 border border-slate-800 text-slate-300 rounded-lg py-1 px-2 focus:outline-none">
                                <option value="normal">Normal</option>
                                <option value="bold">Bold</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer Notes -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Footer Notes</label>
                    <textarea name="receipt_footer_notes" x-model="footer_notes" rows="2"
                              class="w-full bg-slate-950/70 border border-slate-800 text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex gap-3">
                    <a href="<?= url('receipts') ?>"
                       class="flex-1 py-2 border border-slate-700 text-slate-350 hover:bg-slate-800 font-semibold rounded-xl text-xs transition-all text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 py-2 text-white font-semibold rounded-xl text-xs transition-all text-center shadow-lg hover:opacity-95"
                            style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #ffffff !important;">
                        Save Template
                    </button>
                </div>
            </form>

        </div>

        <!-- Right Column: Visual Board Canvas (7 cols wide) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Live Canva Workspace</h3>
                <span class="text-3xs text-slate-400 italic">Drag text fields to reposition them on the page template</span>
            </div>

            <!-- Canva Visual Board Canvas -->
            <div x-ref="canvas"
                 class="w-full border border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative select-none bg-white text-black aspect-[1.414/1] bg-grid"
                 style="background-size: 100% 100%; background-repeat: no-repeat; min-height: 480px;"
                 :style="{ 
                     backgroundImage: bg_preview ? 'url(' + bg_preview + ')' : 'none',
                     fontFamily: font_family
                 }">
                 
                 <!-- Watermark -->
                 <div x-show="show_watermark" class="absolute right-8 top-1/4 select-none pointer-events-none opacity-20 rotate-12 border-4 border-emerald-500 text-emerald-500 font-extrabold uppercase rounded-2xl px-6 py-2 text-xl tracking-widest">
                     Paid
                 </div>

                 <!-- Render draggable mappings -->
                 <template x-for="(item, index) in mappings" :key="item.key">
                     <div class="absolute cursor-move select-none p-1 border hover:border-indigo-500/40 hover:bg-indigo-500/5 rounded transition-all group"
                          :class="selected_index === index ? 'border-brand-500 bg-brand-500/5 ring-1 ring-brand-500/20' : 'border-transparent'"
                          :style="{ 
                              left: item.x + '%', 
                              top: item.y + '%', 
                              fontSize: item.font_size + 'px', 
                              fontWeight: item.font_weight,
                              color: ['amount', 'receipt_number'].includes(item.key) ? accent_color : '#1e293b'
                          }"
                          @mousedown="startDrag($event, index)"
                          @click="selected_index = index">
                          
                          <!-- Dynamic Values Preview -->
                          <span x-show="item.key === 'tenant_name'"><?= e($tenant['name']) ?></span>
                          <span x-show="item.key === 'header_title'" x-text="header_title"></span>
                          <span x-show="item.key === 'receipt_number'">REC-00004</span>
                          <span x-show="item.key === 'paid_at'">Paid on: 23 Jun 2026, 03:47 PM</span>
                          <span x-show="item.key === 'student_name'">Aarav Kumar</span>
                          <span x-show="item.key === 'admission_num'">Admission: ADM-2026-1-4112</span>
                          <span x-show="item.key === 'class_section'">Class & Section: Class A - S1</span>
                          <span x-show="item.key === 'payment_method'">Method: Credit/Debit Card</span>
                          <span x-show="item.key === 'payment_ref'">Reference / Txn: SIM-B1F34C0GC4</span>
                          <span x-show="item.key === 'invoice_number'">Linked Invoice: INV-2026-1-02</span>
                          <span x-show="item.key === 'footer_notes'" x-text="footer_notes"></span>
                          <span x-show="item.key === 'amount'">2,500.00 INR</span>
                          
                          <!-- Table grid element -->
                          <div x-show="item.key === 'account_items'" class="border border-slate-300 rounded bg-slate-50 p-2 mt-1 min-w-[260px] text-[9px] pointer-events-none">
                              <div class="flex justify-between font-bold border-b border-slate-300 pb-0.5 text-slate-500">
                                  <span>Item Description</span>
                                  <span>Paid Balance</span>
                              </div>
                              <div class="flex justify-between pt-1">
                                  <span>Monthly Transport & Bus Fee</span>
                                  <span class="font-mono">2,500.00 INR</span>
                              </div>
                          </div>

                          <!-- Selection highlight tooltip -->
                          <div class="hidden group-hover:block absolute -top-5 left-0 bg-slate-900 text-white text-[8px] font-bold py-0.5 px-1.5 rounded shadow whitespace-nowrap pointer-events-none">
                              <span x-text="item.label"></span>
                          </div>
                     </div>
                 </template>

            </div>
        </div>

    </div>

</div>

<!-- Load Custom Fonts dynamically for preview -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap');
</style>

<?php
$content = ob_get_clean();
?>
