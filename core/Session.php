<?php

declare(strict_types=1);

namespace Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            $cfg = config('app.session', []);
            $https = function_exists('is_https_request')
                ? is_https_request()
                : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $samesite = $cfg['samesite'] ?? 'Lax';
            if (!in_array($samesite, ['Lax', 'Strict'], true)) {
                $samesite = 'Lax';
            }
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'lifetime' => $cfg['lifetime']  ?? 7200,
                'path'     => '/',
                'secure'   => (bool) ($cfg['secure'] ?? $https),
                'httponly' => true,
                'samesite' => $samesite,
            ]);
            session_name('PSNF_SESSION');
            session_start();
            self::$started = true;

            // Regenerate to prevent fixation
            if (!isset($_SESSION['_initialized'])) {
                session_regenerate_id(true);
                $_SESSION['_initialized'] = true;
            }
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $keys = explode('.', $key);
        $data = &$_SESSION;
        foreach ($keys as $k) {
            if (!isset($data[$k]) || !is_array($data[$k])) {
                $data[$k] = [];
            }
            $data = &$data[$k];
        }
        $data = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        $keys = explode('.', $key);
        $data = $_SESSION;
        foreach ($keys as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return $default;
            }
            $data = $data[$k];
        }
        return $data;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function forget(string $key): void
    {
        self::start();
        $keys   = explode('.', $key);
        $last   = array_pop($keys);
        $data   = &$_SESSION;
        foreach ($keys as $k) {
            $data = &$data[$k];
        }
        unset($data[$last]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function flush(): void
    {
        self::start();
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function getId(): string
    {
        self::start();
        return session_id();
    }

    public static function lastActivity(): int
    {
        return (int) ($_SESSION['_last_activity'] ?? 0);
    }

    public static function updateActivity(): void
    {
        $_SESSION['_last_activity'] = time();
    }
}

// Auto-start session
\Core\Session::start();
