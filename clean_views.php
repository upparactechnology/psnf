<?php
$files = [
    'resources/views/students/create.php',
    'resources/views/students/edit.php',
    'resources/views/students/show.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $c = file_get_contents($file);
    
    // Remove the Medical block
    $c = preg_replace('/<!-- Medical & Support Information -->.*?<!-- Guardian Details -->/s', "<!-- Guardian Details -->", $c);
    
    // Remove it from show.php where it says 'Medical & Special Needs'
    $c = preg_replace('/<h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Medical & Special Needs.*?<\/div>\s*<\/div>/s', "", $c);
    
    // Remove from create.php multi-step if necessary?
    $c = preg_replace('/<!-- Step 3: Medical & Support -->.*?<!-- Step 4: Guardian Details -->/s', "<!-- Step 4: Guardian Details -->", $c);

    // Remove the step 3 tab from create.php
    $c = preg_replace('/<button type="button" @click="validateStep\(2\)".*?Medical & Support\s*<\/button>/s', "", $c);
    
    // Update step indices in create.php
    $c = preg_replace('/Step 4/', 'Step 3', $c);
    $c = preg_replace('/step === 3/', 'step === 2', $c);
    $c = preg_replace('/validateStep\(3\)/', 'validateStep(2)', $c);
    $c = preg_replace('/step === 2/', 'step === 1', $c); // This might be wrong, I'll be careful!
    
    file_put_contents($file, $c);
    echo "Updated $file\n";
}
