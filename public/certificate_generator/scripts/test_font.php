<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

function out(string $s): void { echo $s . PHP_EOL; }

out('PHP Version: ' . PHP_VERSION);
out('imagettftext exists: ' . (function_exists('imagettftext') ? 'yes' : 'no'));
if (function_exists('gd_info')) {
    $info = gd_info();
    out('GD Info: ' . json_encode($info));
    out('FreeType Support: ' . (!empty($info['FreeType Support']) ? 'yes' : 'no'));
}

$candidates = [];
$configuredRegular = setting('default_font_regular_path', null);
$configuredDefault = setting('default_font_path', null);
if (is_string($configuredRegular) && trim($configuredRegular) !== '') $candidates[] = $configuredRegular;
if (is_string($configuredDefault) && trim($configuredDefault) !== '') $candidates[] = $configuredDefault;

$raw = setting('custom_fonts_json', '[]') ?? '[]';
$decoded = json_decode((string)$raw, true);
if (is_array($decoded) && !empty($decoded)) {
    $first = $decoded[0] ?? null;
    if (is_array($first) && isset($first['files']['regular'])) {
        $candidates[] = (string)$first['files']['regular'];
    }
}

// normalize candidates: try absolute or APP_ROOT relative
foreach ($candidates as $cand) {
    $norm = str_replace('\\', '/', trim((string)$cand));
    if ($norm === '') continue;
    if (is_file($norm)) {
        out('Found font file (raw): ' . $norm);
        $fontPath = $norm;
        break;
    }
    $abs = APP_ROOT . '/' . ltrim($norm, '/');
    if (is_file($abs)) {
        out('Found font file (abs): ' . $abs);
        $fontPath = str_replace('\\', '/', $abs);
        break;
    }
}

if (!isset($fontPath)) {
    out('No candidate font file found. Tried: ' . implode(', ', $candidates));
    exit(1);
}

out('Using font: ' . $fontPath);

// Try imagettfbbox to ensure font loads
try {
    $bbox = imagettfbbox(20, 0, $fontPath, 'Test');
    if ($bbox === false) {
        out('imagettfbbox failed for font');
    } else {
        out('imagettfbbox OK: ' . json_encode($bbox));
    }
} catch (Throwable $e) {
    out('imagettfbbox exception: ' . $e->getMessage());
}

// Render a small PNG
$img = imagecreatetruecolor(400, 120);
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefilledrectangle($img, 0, 0, 400, 120, $white);

try {
    $ret = imagettftext($img, 20, 0, 10, 50, $black, $fontPath, 'BaiJamjuree Test');
    if ($ret === false) {
        out('imagettftext returned false');
    } else {
        out('imagettftext succeeded');
    }
} catch (Throwable $e) {
    out('imagettftext exception: ' . $e->getMessage());
}

$outFile = sys_get_temp_dir() . '/font_test.png';
imagepng($img, $outFile);
imagedestroy($img);
out('Wrote test PNG to: ' . $outFile);

exit(0);
