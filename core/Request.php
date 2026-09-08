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
        // Priority 1: Explicit base_path from config (e.g. 'erp' → '/erp')
        $configuredPath = config('app.base_path', '');
        if (!empty($configuredPath) && $configuredPath !== 'auto') {
            return '/' . trim($configuredPath, '/');
        }

        // Priority 2: base_url path component (full URLs like 'https://host/erp')
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

        // Priority 4: SCRIPT_NAME directory (e.g. /erp/public/index.php → /erp/public)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $dir = '/' . trim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($dir === '/') $dir = '';

        // Strip '/public' suffix so /erp/public → /erp
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
