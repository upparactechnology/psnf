<?php
declare(strict_types=1);

$settingDefinitions = [
    'default_font_path' => [
        'label' => 'Default TTF Font Path',
        'type' => 'text',
        'default' => DEFAULT_FONT_PATH,
    ],
    'file_name_format' => [
        'label' => 'File Naming Format',
        'type' => 'text',
        'default' => DEFAULT_FILE_NAME_FORMAT,
    ],
    'download_format' => [
        'label' => 'Default Download Format',
        'type' => 'select',
        'default' => DEFAULT_DOWNLOAD_FORMAT,
        'options' => ['pdf' => 'PDF', 'jpg' => 'JPG'],
    ],
    'smtp_host' => [
        'label' => 'SMTP Host',
        'type' => 'text',
        'default' => '',
    ],
    'smtp_port' => [
        'label' => 'SMTP Port',
        'type' => 'number',
        'default' => '587',
    ],
    'smtp_secure' => [
        'label' => 'SMTP Security',
        'type' => 'select',
        'default' => 'tls',
        'options' => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'],
    ],
    'smtp_username' => [
        'label' => 'SMTP Username',
        'type' => 'text',
        'default' => '',
    ],
    'smtp_password' => [
        'label' => 'SMTP Password',
        'type' => 'password',
        'default' => '',
    ],
    'smtp_from_email' => [
        'label' => 'From Email',
        'type' => 'email',
        'default' => 'no-reply@example.com',
    ],
    'smtp_from_name' => [
        'label' => 'From Name',
        'type' => 'text',
        'default' => APP_NAME,
    ],
    'email_send_attachment' => [
        'label' => 'Send Attachment In Email',
        'type' => 'select',
        'default' => '1',
        'options' => ['1' => 'Yes', '0' => 'No'],
    ],
    'email_subject_template' => [
        'label' => 'Email Subject Template',
        'type' => 'text',
        'default' => 'Your {{certificate_type}} Certificate - {{conference}} {{year}}',
    ],
    'email_message_template' => [
        'label' => 'Email Message Template',
        'type' => 'textarea',
        'default' => "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team",
    ],
        'bulk_email_delay_min_seconds' => [
            'label' => 'Bulk Email Delay Minimum Seconds',
            'type' => 'number',
            'default' => '1',
        ],
        'bulk_email_delay_max_seconds' => [
            'label' => 'Bulk Email Delay Maximum Seconds',
            'type' => 'number',
            'default' => '3',
        ],
];

$fontUploadStyles = [
    'regular' => 'font_file_regular',
    'bold' => 'font_file_bold',
    'italic' => 'font_file_italic',
    'bold_italic' => 'font_file_bold_italic',
];

$storeUploadedFontFile = static function (array $file, string $slug, string $style): ?string {
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed for ' . $style . ' font file.');
    }

    $originalName = (string) ($file['name'] ?? 'font.ttf');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, ['ttf', 'otf'], true)) {
        throw new RuntimeException('Only TTF and OTF files are allowed.');
    }

    $safeName = $slug . '-' . $style . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $extension;
    $absoluteTarget = FONTS_ROOT . '/' . $safeName;

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Invalid uploaded file for ' . $style . ' style.');
    }

    if (!move_uploaded_file($tmpName, $absoluteTarget)) {
        throw new RuntimeException('Unable to move uploaded font file.');
    }

    return 'uploads/fonts/' . $safeName;
};

if (is_post_request()) {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? 'save_settings');

    if ($action === 'upload_custom_font') {
        $fontName = trim((string) ($_POST['custom_font_name'] ?? ''));
        $requestedSlug = trim((string) ($_POST['custom_font_slug'] ?? ''));

        if ($fontName === '') {
            flash('error', 'Font display name is required.');
            redirect(url('settings'));
        }

        $fontSlug = slugify($requestedSlug !== '' ? $requestedSlug : $fontName);
        $customFonts = load_custom_fonts();

        $fontIndex = null;
        $existingFiles = [];
        foreach ($customFonts as $index => $font) {
            if ((string) ($font['slug'] ?? '') === $fontSlug) {
                $fontIndex = $index;
                $existingFiles = is_array($font['files'] ?? null) ? $font['files'] : [];
                break;
            }
        }

        try {
            $newFiles = $existingFiles;

            foreach ($fontUploadStyles as $style => $fieldName) {
                $uploadedFile = $_FILES[$fieldName] ?? null;
                if (!is_array($uploadedFile)) {
                    continue;
                }

                $storedPath = $storeUploadedFontFile($uploadedFile, $fontSlug, $style);
                if ($storedPath !== null) {
                    $newFiles[$style] = $storedPath;
                }
            }

            if (!isset($newFiles['regular']) || trim((string) $newFiles['regular']) === '') {
                throw new RuntimeException('Regular font file is required for each custom font family.');
            }

            $record = [
                'slug' => $fontSlug,
                'name' => $fontName,
                'files' => $newFiles,
            ];

            if ($fontIndex === null) {
                $customFonts[] = $record;
            } else {
                $customFonts[$fontIndex] = $record;
            }

            set_setting(
                'custom_fonts_json',
                (string) json_encode(array_values($customFonts), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            flash('success', 'Custom font saved successfully. You can now use it in field editor.');
        } catch (\Throwable $exception) {
            flash('error', 'Custom font upload failed: ' . $exception->getMessage());
        }

        redirect(url('settings'));
    }

    if ($action === 'remove_custom_font') {
        $fontSlug = slugify((string) ($_POST['custom_font_slug'] ?? ''));
        $customFonts = load_custom_fonts();

        if ($fontSlug === '') {
            flash('error', 'Invalid font selected for removal.');
            redirect(url('settings'));
        }

        $kept = [];
        $removed = null;

        foreach ($customFonts as $font) {
            if ((string) ($font['slug'] ?? '') === $fontSlug) {
                $removed = $font;
                continue;
            }

            $kept[] = $font;
        }

        if ($removed === null) {
            flash('error', 'Custom font not found.');
            redirect(url('settings'));
        }

        $removedFiles = is_array($removed['files'] ?? null) ? $removed['files'] : [];
        foreach ($removedFiles as $path) {
            $relative = ltrim(str_replace('\\', '/', (string) $path), '/');
            if (!str_starts_with($relative, 'uploads/fonts/')) {
                continue;
            }

            $absolute = APP_ROOT . '/' . $relative;
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        set_setting(
            'custom_fonts_json',
            (string) json_encode(array_values($kept), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        flash('success', 'Custom font removed.');
        redirect(url('settings'));
    }

    foreach ($settingDefinitions as $key => $definition) {
        $value = $_POST[$key] ?? $definition['default'];
        if (is_array($value)) {
            continue;
        }

        $trimmed = trim((string) $value);
        set_setting($key, $trimmed);
    }

    flash('success', 'System settings saved successfully.');
    redirect(url('settings'));
}

$settings = [];
foreach ($settingDefinitions as $key => $definition) {
    $settings[$key] = setting($key, (string) $definition['default']) ?? (string) $definition['default'];
}

$customFonts = load_custom_fonts();
$availableFonts = available_font_choices();

$configuredFontPath = str_replace('\\', '/', (string) $settings['default_font_path']);
$fontExists = $configuredFontPath !== '' && (
    is_file($configuredFontPath)
    || is_file(APP_ROOT . '/' . ltrim($configuredFontPath, '/'))
);

render_view('settings.php', [
    'pageTitle' => 'System Settings',
    'settingDefinitions' => $settingDefinitions,
    'settings' => $settings,
    'fontExists' => $fontExists,
    'customFonts' => $customFonts,
    'availableFonts' => $availableFonts,
]);
