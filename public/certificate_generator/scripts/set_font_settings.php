<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$relative = 'uploads/fonts/baijamjuree-regular-20260410234824-373231.ttf';
$prefixed = '/' . ltrim($relative, '/');

try {
    set_setting('default_font_regular_path', $relative);
    set_setting('default_font_path', $prefixed);
    echo "Set default_font_regular_path={$relative}\n";
    echo "Set default_font_path={$prefixed}\n";
} catch (Throwable $e) {
    echo 'Error updating settings: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Verify
echo "Verification:\n";
echo 'default_font_regular_path => ' . var_export(setting('default_font_regular_path', null), true) . PHP_EOL;
echo 'default_font_path => ' . var_export(setting('default_font_path', null), true) . PHP_EOL;

exit(0);
