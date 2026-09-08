<?php
declare(strict_types=1);

header('Content-Type: text/plain');

$root = dirname(__DIR__);
$reqFile = $root . '/core/Request.php';

echo "=== PSNF CORE FIX UPDATER ===\n";
echo "Target: $reqFile\n\n";

$newContent = <<<'PHP_CODE'
<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $params = [];

    public function getMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing via _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return $method;
    }

    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        // Strip query string
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

        // Normalize: ensure leading slash, strip trailing slashes
        $path = '/' . trim($path, '/');

        // When Apache serves a directory index (e.g. /erpv2/public/ or /index.php),
        // REQUEST_URI may include index.php. Normalize to root or clean subpath.
        if ($path === '/index.php') {
            $path = '/';
        } elseif (str_starts_with($path, '/index.php/')) {
            $path = '/' . trim(substr($path, 10), '/');
        }

        return $path;
    }

    public function detectBasePath(): string
    {
        // 0. Explicit configuration in config/app.php
        $configuredPath = config('app.base_path', '');
        if (!empty($configuredPath)) {
            return '/' . trim($configuredPath, '/');
        }
        $configuredUrl = config('app.base_url', '');
        if (!empty($configuredUrl) && $configuredUrl !== 'auto') {
            $parsed = parse_url($configuredUrl);
            if (!empty($parsed['path'])) {
                return '/' . trim($parsed['path'], '/');
            }
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($qPos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $qPos);
        }
        $requestUri = '/' . trim($requestUri, '/');

        // 1. SCRIPT_NAME directory (e.g. /erpv2/public/index.php -> /erpv2/public)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        $dir = '/' . trim($dir, '/');
        if ($dir === '/') $dir = '';

        if ($dir !== '' && ($requestUri === $dir || str_starts_with($requestUri . '/', $dir . '/'))) {
            return $dir;
        }

        // 2. Look for "/public" in REQUEST_URI
        // e.g. /erpv2/public or /erpv2/public/login -> /erpv2/public
        if (preg_match('#^(.*?/public)(?:/.*)?$#i', $requestUri, $matches)) {
            return '/' . trim($matches[1], '/');
        }

        // 3. Fallback: SCRIPT_FILENAME relative to DOCUMENT_ROOT
        $scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        if ($docRoot !== '' && $scriptFilename !== '' && str_starts_with($scriptFilename, $docRoot)) {
            $relative = dirname(substr($scriptFilename, strlen($docRoot)));
            $relative = '/' . trim(str_replace('\\', '/', $relative), '/');
            if ($relative === '/') $relative = '';
            if ($relative !== '' && ($requestUri === $relative || str_starts_with($requestUri . '/', $relative . '/'))) {
                return $relative;
            }
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

            // JSON body
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
PHP_CODE;

$written = @file_put_contents($reqFile, $newContent);

if ($written !== false) {
    echo "[SUCCESS] core/Request.php successfully updated! ($written bytes written)\n";
} else {
    echo "[ERROR] Failed to write to $reqFile. Check file permissions on the server!\n";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[SUCCESS] OPcache reset successfully!\n";
}

echo "\n=== NOW VERIFYING ROUTING ===\n";
require_once $reqFile;
$req = new \Core\Request();
$_SERVER['REQUEST_URI'] = '/erpv2/public/';
echo "detectBasePath(): " . var_export($req->detectBasePath(), true) . "\n";
echo "getPath():        " . var_export($req->getPath(), true) . "\n";
echo "\nDONE! Now visit: https://psnf.upparac.com/erpv2/public/\n";
