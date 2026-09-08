<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

$root = dirname(__DIR__);

echo "=== PSNF LIVE SERVER PATCH ===\n";
echo "Root: $root\n\n";

// ─── PATCH 1: config/app.php ──────────────────────────────────────────────────
$configFile = $root . '/config/app.php';
$configContent = <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name'        => 'PSNF ERP',
    'version'     => '1.0.0',
    'debug'       => false,
    'base_url'    => '',
    'base_path'   => 'erp',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'uploads_dir' => ROOT_PATH . '/storage/uploads',

    'session' => [
        'lifetime' => 86400,
        'timeout'  => 86400,
        'secure'   => true,
        'samesite' => 'Lax',
    ],

    'mail' => [
        'driver'     => 'smtp',
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'username'   => '',
        'password'   => '',
        'from_email' => 'noreply@psnf.edu',
        'from_name'  => 'PSNF ERP System',
    ],
];
PHP;

$r = file_put_contents($configFile, $configContent);
echo "[" . ($r !== false ? "OK" : "FAIL") . "] config/app.php ($r bytes)\n";

// ─── PATCH 2: core/Request.php ───────────────────────────────────────────────
$requestFile = $root . '/core/Request.php';
$requestContent = <<<'PHP'
<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $params = [];

    public function getMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        return $method;
    }

    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        $base = $this->detectBasePath();

        if ($base !== '' && $base !== '/') {
            if ($path === $base || $path === $base . '/') {
                $path = '/';
            } elseif (str_starts_with($path, $base . '/')) {
                $path = substr($path, strlen($base));
            } elseif (str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
            }
        }

        $path = '/' . trim($path, '/');

        if ($path === '/index.php') {
            $path = '/';
        } elseif (str_starts_with($path, '/index.php/')) {
            $path = '/' . trim(substr($path, 10), '/');
        }

        return $path ?: '/';
    }

    public function detectBasePath(): string
    {
        // Priority 1: Explicit base_path from config (e.g. 'erp' => '/erp')
        $configuredPath = config('app.base_path', '');
        if (!empty($configuredPath) && $configuredPath !== 'auto') {
            return '/' . trim($configuredPath, '/');
        }

        // Priority 2: base_url path component (full URL like 'https://host/erp')
        $configuredUrl = config('app.base_url', '');
        if (!empty($configuredUrl) && $configuredUrl !== 'auto') {
            $parsed = parse_url($configuredUrl);
            if (!empty($parsed['path']) && $parsed['path'] !== '/') {
                return '/' . trim($parsed['path'], '/');
            }
        }

        // Priority 3: Compute from ROOT_PATH vs DOCUMENT_ROOT
        if (defined('ROOT_PATH')) {
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $rootPath = rtrim(str_replace('\\', '/', ROOT_PATH), '/');
            if ($docRoot !== '' && str_starts_with($rootPath, $docRoot)) {
                $rel = substr($rootPath, strlen($docRoot));
                $rel = '/' . trim($rel, '/');
                if ($rel !== '/' && $rel !== '') {
                    return $rel;
                }
            }
        }

        // Priority 4: SCRIPT_NAME directory
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $dir = '/' . trim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($dir === '/') $dir = '';

        // Strip '/public' suffix so /erp/public => /erp
        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
        }

        return $dir;
    }

    public function getBody(): array
    {
        $body   = [];
        $method = $this->getMethod();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            foreach ($_POST as $key => $value) {
                $body[$key] = $this->sanitize($value);
            }
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true) ?? [];
                foreach ($data as $key => $value) {
                    $body[$key] = $this->sanitize($value);
                }
            }
        }

        if ($method === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = $this->sanitize($value);
            }
        }

        return $body;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->getBody()[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function file(string $key): array|null
    {
        return $_FILES[$key] ?? null;
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function isHtmx(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']);
    }

    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    public function ip(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        if (is_string($value)) {
            return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }
}
PHP;

$r = file_put_contents($requestFile, $requestContent);
echo "[" . ($r !== false ? "OK" : "FAIL") . "] core/Request.php ($r bytes)\n";

// ─── PATCH 3: core/helpers.php (get_dynamic_base_url + url functions only) ──
// We'll patch by replacing the functions in place
$helpersFile = $root . '/core/helpers.php';
$helpersContent = file_get_contents($helpersFile);

$newGetDynamic = <<<'PHP'
if (!function_exists('get_dynamic_base_url')) {
    function get_dynamic_base_url(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // If base_url is a full URL (e.g. 'https://example.com/erp'), extract path only
        $configuredBase = config('app.base_url', '');
        if (!empty($configuredBase) && $configuredBase !== 'auto') {
            $parsed = parse_url($configuredBase);
            $path = rtrim($parsed['path'] ?? '', '/');
            return $scheme . '://' . $host . $path;
        }

        // If only base_path is set (e.g. 'erp'), build from that
        $configuredPath = config('app.base_path', '');
        if (!empty($configuredPath) && $configuredPath !== 'auto') {
            return $scheme . '://' . $host . '/' . trim($configuredPath, '/');
        }

        // Auto-detect from request
        $request = \Core\Application::$app->request ?? null;
        $dir = $request ? $request->detectBasePath() : '';

        if (empty($dir) || $dir === '/') {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
            $dir = '/' . trim(str_replace('\\', '/', dirname($scriptName)), '/');
            if ($dir === '/') $dir = '';
        }

        return $scheme . '://' . $host . $dir;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $base = rtrim(get_dynamic_base_url(), '/');
        $cleanPath = ltrim($path, '/');

        if ($cleanPath === '') {
            return $base;
        }

        return $base . '/' . $cleanPath;
    }
}
PHP;

// Replace the two functions using a regex
$patched = preg_replace(
    '/if\s*\(!function_exists\(\'get_dynamic_base_url\'\)\).*?^}/ms',
    '',
    $helpersContent
);
$patched = preg_replace(
    '/if\s*\(!function_exists\(\'url\'\)\).*?^}/ms',
    '',
    $patched
);

// Append new versions before the asset() function
$patched = str_replace(
    "if (!function_exists('asset'))",
    $newGetDynamic . "\n\nif (!function_exists('asset'))",
    $patched,
    1
);

$r = file_put_contents($helpersFile, $patched);
echo "[" . ($r !== false ? "OK" : "FAIL") . "] core/helpers.php ($r bytes)\n";

// ─── Reset OPcache ───────────────────────────────────────────────────────────
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[OK] OPcache reset\n";
}

// ─── VERIFY ──────────────────────────────────────────────────────────────────
echo "\n=== VERIFY ===\n";

define('ROOT_PATH', $root);
define('APP_PATH', $root . '/app');
define('CORE_PATH', $root . '/core');
define('CONFIG_PATH', $root . '/config');
define('VIEWS_PATH', $root . '/resources/views');
define('STORAGE_PATH', $root . '/storage');

require_once $root . '/core/Application.php';

try {
    $app = new \Core\Application();
    echo "base_path config:     " . config('app.base_path') . "\n";
    echo "base_url config:      " . (config('app.base_url') ?: '(empty)') . "\n";
    echo "detectBasePath():     " . $app->request->detectBasePath() . "\n";
    echo "getPath() for /erp/:  ";
    $_SERVER['REQUEST_URI'] = '/erp/';
    $req2 = new \Core\Request();
    echo $req2->getPath() . "\n";
    echo "get_dynamic_base_url(): " . get_dynamic_base_url() . "\n";
    echo "url('/dashboard'):      " . url('/dashboard') . "\n";
    echo "url('/login'):          " . url('/login') . "\n";
    echo "asset('js/app.js'):     " . asset('js/app.js') . "\n";
    echo "\n=== ALL GOOD! Visit https://psnf.upparac.com/erp/ ===\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
