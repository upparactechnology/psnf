<?php

declare(strict_types=1);

// ─── Global Helper Functions ─────────────────────────────────────────────────

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $keys  = explode('.', $key);
        $data  = $GLOBALS['config'] ?? [];
        foreach ($keys as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return $default;
            }
            $data = $data[$k];
        }
        return $data;
    }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$args): void
    {
        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;border-radius:8px;">';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
        exit();
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$args): void
    {
        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;border-radius:8px;">';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('session')) {
    function session(string $key, mixed $default = null): mixed
    {
        return \Core\Session::get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return \Core\Session::get('user');
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return \Core\Session::get('user.id');
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return \Core\Session::has('user');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        \Core\Application::$app->response->redirect($url);
        exit();
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            \Core\Session::flash($key, $value);
            return null;
        }
        return \Core\Session::getFlash($key);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.base_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Core\View::csrfToken();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return \Core\View::old($key, $default);
    }
}

if (!function_exists('str_uuid')) {
    function str_uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('admission_number')) {
    function admission_number(int $schoolId = 1): string
    {
        $year = date('Y');
        $rand = str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "ADM-{$year}-{$schoolId}-{$rand}";
    }
}

if (!function_exists('gr_number')) {
    function gr_number(int $schoolId = 1): string
    {
        $rand = str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return "GR-{$schoolId}-{$rand}";
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '—';
        return date($format, strtotime($date));
    }
}

if (!function_exists('age')) {
    function age(?string $dob): string
    {
        if (!$dob) return '—';
        $diff = (new \DateTime())->diff(new \DateTime($dob));
        return $diff->y . ' yrs ' . $diff->m . ' mos';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100): string
    {
        return strlen($text) > $length ? substr($text, 0, $length) . '…' : $text;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        $user = auth();
        if (!$user) return false;
        if (in_array('super_admin', $user['roles'] ?? [])) return true;
        return in_array($permission, $user['permissions'] ?? []);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        $user = auth();
        if (!$user) return false;
        return in_array($role, $user['roles'] ?? []);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return STORAGE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)   return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)      return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
