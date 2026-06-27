<?php
declare(strict_types=1);

function db(): PDO
{
    return $GLOBALS['app_pdo'];
}

function initialize_storage_paths(): void
{
    $paths = [TEMPLATES_ROOT, GENERATED_ROOT, UPLOADS_ROOT, CSV_UPLOAD_ROOT, FONTS_ROOT];
    foreach ($paths as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}

function setting(string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $GLOBALS['app_settings_cache'])) {
        return $GLOBALS['app_settings_cache'][$key];
    }

    $stmt = db()->prepare('SELECT setting_value FROM cert_settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        $GLOBALS['app_settings_cache'][$key] = $default;
        return $default;
    }

    $GLOBALS['app_settings_cache'][$key] = (string) $value;
    return (string) $value;
}

function set_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO cert_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([
        'setting_key' => $key,
        'setting_value' => $value,
    ]);

    $GLOBALS['app_settings_cache'][$key] = $value;
}

function default_email_templates(): array
{
    return [
        [
            'key' => 'standard-certificate',
            'name' => 'Standard Certificate',
            'subject_template' => 'Your {{certificate_type}} Certificate - {{conference}} {{year}}',
            'message_template' => "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team",
            'cc_template' => '',
            'bcc_template' => '',
        ],
        [
            'key' => 'friendly-follow-up',
            'name' => 'Friendly Follow-up',
            'subject_template' => 'Your {{certificate_type}} Certificate Is Ready',
            'message_template' => "Hello {{name}},\n\nYour {{certificate_type}} certificate for {{conference}} {{year}} is ready. Please review the attached file.\n\nBest regards,\nConference Team",
            'cc_template' => '',
            'bcc_template' => '',
        ],
        [
            'key' => 'brief-notice',
            'name' => 'Brief Notice',
            'subject_template' => '{{conference}} {{year}} Certificate Delivery',
            'message_template' => "Hi {{name}},\n\nAttached is your {{certificate_type}} certificate.\n\nThank you,\nConference Team",
            'cc_template' => '',
            'bcc_template' => '',
        ],
    ];
}

function normalize_email_template(array $template, int $fallbackIndex = 0): ?array
{
    $name = trim((string) ($template['name'] ?? ''));
    $subjectTemplate = trim((string) ($template['subject_template'] ?? ''));
    $messageTemplate = trim((string) ($template['message_template'] ?? ''));
    $key = trim((string) ($template['key'] ?? ''));

    if ($name === '' || $subjectTemplate === '' || $messageTemplate === '') {
        return null;
    }

    if ($key === '') {
        $key = slugify($name);
    }

    if ($key === '') {
        $key = 'template-' . ($fallbackIndex + 1);
    }

    return [
        'key' => $key,
        'name' => $name,
        'subject_template' => $subjectTemplate,
        'message_template' => $messageTemplate,
        'cc_template' => trim((string) ($template['cc_template'] ?? '')),
        'bcc_template' => trim((string) ($template['bcc_template'] ?? '')),
    ];
}

function load_email_templates(): array
{
    $raw = trim((string) (setting('email_templates_json', '') ?? ''));
    if ($raw === '') {
        return default_email_templates();
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return default_email_templates();
    }

    $templates = [];
    foreach (array_values($decoded) as $index => $template) {
        if (!is_array($template)) {
            continue;
        }

        $normalized = normalize_email_template($template, $index);
        if ($normalized !== null) {
            $templates[] = $normalized;
        }
    }

    return $templates !== [] ? $templates : default_email_templates();
}

function save_email_templates(array $templates): void
{
    $normalizedTemplates = [];
    foreach (array_values($templates) as $index => $template) {
        if (!is_array($template)) {
            continue;
        }

        $normalized = normalize_email_template($template, $index);
        if ($normalized !== null) {
            $normalizedTemplates[] = $normalized;
        }
    }

    if ($normalizedTemplates === []) {
        $normalizedTemplates = default_email_templates();
    }

    set_setting(
        'email_templates_json',
        (string) json_encode($normalizedTemplates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

function find_email_template(array $templates, string $templateKey): ?array
{
    $templateKey = trim($templateKey);
    if ($templateKey === '') {
        return null;
    }

    foreach ($templates as $template) {
        if (!is_array($template)) {
            continue;
        }

        if ((string) ($template['key'] ?? '') === $templateKey) {
            return $template;
        }
    }

    return null;
}

function stream_csv_download(string $fileName, array $headers, iterable $rows, callable $rowMapper): void
{
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        exit;
    }

    fputcsv($output, $headers);

    foreach ($rows as $row) {
        $mappedRow = $rowMapper($row);
        if (!is_array($mappedRow)) {
            continue;
        }

        fputcsv($output, array_map(static fn ($value): string => (string) $value, $mappedRow));
    }

    fclose($output);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'item';
}

function safe_filename(string $value): string
{
    $value = preg_replace('/[^A-Za-z0-9._-]/', '_', $value) ?? '';
    $value = preg_replace('/_+/', '_', $value) ?? '';
    return trim($value, '_') ?: 'file';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    if (isset($_SESSION['auth_user'])) {
        return $_SESSION['auth_user'];
    }

    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        $erpUser = $_SESSION['user'];
        $roles = $erpUser['roles'] ?? [];
        
        $role = 'staff';
        $priorityOrder = ['super_admin', 'school_admin', 'manager', 'teacher', 'therapist', 'staff', 'driver', 'parent', 'student'];
        foreach ($priorityOrder as $pRole) {
            if (in_array($pRole, $roles, true)) {
                $role = $pRole;
                break;
            }
        }

        $_SESSION['auth_user'] = [
            'id' => (int) ($erpUser['id'] ?? 0),
            'full_name' => (string) ($erpUser['name'] ?? 'User'),
            'email' => (string) ($erpUser['email'] ?? ''),
            'role' => $role,
        ];
        return $_SESSION['auth_user'];
    }

    return null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function user_role(): ?string
{
    $user = current_user();
    return $user['role'] ?? null;
}

function has_role(array $roles): bool
{
    $role = user_role();
    return $role !== null && in_array($role, $roles, true);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect(url('login'));
    }
}

function require_roles(array $roles): void
{
    require_login();
    if (!has_role($roles)) {
        http_response_code(403);
        include APP_ROOT . '/app/views/403.php';
        exit;
    }
}

function user_has_conference_access(int $conferenceId): bool
{
    $user = current_user();
    if ($user === null) {
        return false;
    }

    if ($user['role'] === 'super_admin') {
        return true;
    }

    $stmt = db()->prepare('SELECT COUNT(*) FROM conference_admins WHERE conference_id = :conference_id AND user_id = :user_id');
    $stmt->execute([
        'conference_id' => $conferenceId,
        'user_id' => (int) $user['id'],
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function url(string $page, array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return 'index.php?' . http_build_query($query);
}

function flash(string $type, string $message): void
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }

    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function render_messages(): string
{
    $messages = $_SESSION['flash'] ?? [];
    // Do not consume here; use pull_flashes when layout handling wants to consume.
    if ($messages === []) {
        return '';
    }

    $out = '';
    foreach ($messages as $m) {
        $type = htmlspecialchars((string) ($m['type'] ?? 'info'));
        $msg = htmlspecialchars((string) ($m['message'] ?? ''));
        $out .= "<div class=\"flash flash-{$type}\">{$msg}</div>\n";
    }

    return $out;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        echo 'Invalid CSRF token.';
        exit;
    }
}

function conference_scope_sql(): array
{
    $user = current_user();
    if ($user === null) {
        return ['sql' => '1=0', 'params' => []];
    }

    if ($user['role'] === 'super_admin') {
        return ['sql' => '1=1', 'params' => []];
    }

    return [
        'sql' => 'c.id IN (SELECT conference_id FROM conference_admins WHERE user_id = :scope_user_id)',
        'params' => ['scope_user_id' => (int) $user['id']],
    ];
}

function fetch_conferences_for_user(): array
{
    $scope = conference_scope_sql();
    $stmt = db()->prepare('SELECT c.* FROM conferences c WHERE ' . $scope['sql'] . ' ORDER BY c.year DESC, c.name ASC');
    $stmt->execute($scope['params']);
    return $stmt->fetchAll();
}

function field_default_keys(): array
{
    return ['name', 'institute', 'title', 'date', 'conference', 'year', 'category', 'verify_code'];
}

function normalize_hex_color(string $value): string
{
    $value = trim($value);
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
        return '#000000';
    }
    return strtoupper($value);
}

function is_post_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function render_view(string $view, array $data = [], bool $withLayout = true): void
{
    $viewFile = APP_ROOT . '/app/views/' . ltrim($view, '/');
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'View not found: ' . e($view);
        return;
    }

    extract($data, EXTR_SKIP);

    if ($withLayout) {
        $flashes = pull_flashes();
        include APP_ROOT . '/app/views/layout/header.php';
        include $viewFile;
        include APP_ROOT . '/app/views/layout/footer.php';
        return;
    }

    include $viewFile;
}

function builtin_font_choices(): array
{
    return [
        [
            'value' => 'default',
            'label' => 'Default',
            'preview_family' => 'Segoe UI, Trebuchet MS, sans-serif',
            'canvas_family' => 'Segoe UI',
            'preview_url' => '',
        ],
        [
            'value' => 'arial',
            'label' => 'Arial',
            'preview_family' => 'Arial, sans-serif',
            'canvas_family' => 'Arial',
            'preview_url' => '',
        ],
        [
            'value' => 'times',
            'label' => 'Times New Roman',
            'preview_family' => 'Times New Roman, serif',
            'canvas_family' => 'Times New Roman',
            'preview_url' => '',
        ],
        [
            'value' => 'georgia',
            'label' => 'Georgia',
            'preview_family' => 'Georgia, serif',
            'canvas_family' => 'Georgia',
            'preview_url' => '',
        ],
        [
            'value' => 'calibri',
            'label' => 'Calibri',
            'preview_family' => 'Calibri, Arial, sans-serif',
            'canvas_family' => 'Calibri',
            'preview_url' => '',
        ],
    ];
}

function load_custom_fonts(): array
{
    $raw = setting('custom_fonts_json', '[]') ?? '[]';
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    $fonts = [];
    foreach ($decoded as $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = slugify((string) ($item['slug'] ?? ''));
        $name = trim((string) ($item['name'] ?? ''));
        $files = is_array($item['files'] ?? null) ? $item['files'] : [];

        if ($slug === '' || $name === '') {
            continue;
        }

        $normalizedFiles = [];
        foreach (['regular', 'bold', 'italic', 'bold_italic'] as $styleKey) {
            $value = trim((string) ($files[$styleKey] ?? ''));
            if ($value !== '') {
                $normalizedFiles[$styleKey] = str_replace('\\', '/', $value);
            }
        }

        if (!isset($normalizedFiles['regular'])) {
            continue;
        }

        $fonts[] = [
            'slug' => $slug,
            'name' => $name,
            'files' => $normalizedFiles,
        ];
    }

    return $fonts;
}

function available_font_choices(): array
{
    $choices = builtin_font_choices();

    foreach (load_custom_fonts() as $font) {
        $slug = (string) $font['slug'];
        $name = (string) $font['name'];
        $regularPath = (string) ($font['files']['regular'] ?? '');

        $previewUrl = '';
        if ($regularPath !== '') {
            $previewUrl = ltrim(str_replace('\\', '/', $regularPath), '/');
        }

        $familyName = 'custom_' . preg_replace('/[^a-z0-9_]+/', '_', strtolower($slug));

        $choices[] = [
            'value' => 'custom:' . $slug,
            'label' => $name . ' (Custom)',
            'preview_family' => $familyName . ', Segoe UI, Trebuchet MS, sans-serif',
            'canvas_family' => $familyName,
            'preview_url' => $previewUrl,
        ];
    }

    return $choices;
}
