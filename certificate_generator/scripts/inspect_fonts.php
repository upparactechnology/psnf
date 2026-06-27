<?php
declare(strict_types=1);

// Small inspector to dump font-related settings and field mappings
require_once __DIR__ . '/../app/bootstrap.php';

function out(string $text): void {
    echo $text . PHP_EOL;
}

try {
    out('--- SETTINGS ---');
    $keys = ['default_font_path', 'default_font_regular_path', 'custom_fonts_json'];
    foreach ($keys as $k) {
        $v = setting($k, null);
        out((string) $k . ': ' . var_export($v, true));
    }

    out('');
    out('--- CUSTOM FONTS (decoded) ---');
    $raw = setting('custom_fonts_json', '[]') ?? '[]';
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        out('custom_fonts_json: INVALID JSON');
        out((string) $raw);
    } else {
        out(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    out('');
    out('--- FIELD MAPPINGS (rows mentioning font or custom:) ---');
    $stmt = db()->prepare('SELECT id, certificate_type_id, field_key, label, options_json FROM field_mappings ORDER BY id DESC LIMIT 500');
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $found = 0;
    foreach ($rows as $r) {
        $label = (string) ($r['label'] ?? '');
        $opts = (string) ($r['options_json'] ?? '');
        if (stripos($label, '[font=') !== false || stripos($opts, 'font') !== false || stripos($opts, 'custom:') !== false) {
            $found++;
            out('---');
            out('id: ' . $r['id'] . ' certificate_type_id: ' . $r['certificate_type_id'] . ' field_key: ' . $r['field_key']);
            out('label: ' . $label);
            out('options_json: ' . $opts);
        }
    }

    if ($found === 0) {
        out('No recent field_mappings rows reference fonts explicitly.');
    }

    out('');
    out('--- CHECK: uploaded fonts directory ---');
    $fontsDir = APP_ROOT . '/uploads/fonts';
    if (is_dir($fontsDir)) {
        $files = scandir($fontsDir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            out($f);
        }
    } else {
        out('Uploads fonts directory not found: ' . $fontsDir);
    }

    out('');
    out('--- DONE ---');
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

exit(0);
