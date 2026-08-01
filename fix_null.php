<?php
$f = 'resources/views/attendance/index.php';
$c = file_get_contents($f);

// Replace "null" text with "Not Marked" and use light theme colors since the rest of the table is light
$c = preg_replace(
    '/<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">null<\/span>/',
    '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">Not Marked</span>',
    $c
);

file_put_contents($f, $c);
echo "Fixed null text\n";
