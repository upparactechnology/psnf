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

        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        // Normalize: ensure leading slash, strip trailing slashes
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';

        // When Apache serves a directory index (e.g. /erpv2/public/), it internally
        // redirects to index.php, making REQUEST_URI = /erpv2/public/index.php.
        // After stripping the base, we get /index.php — treat that as root '/'.
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptFile = basename($scriptName);
        if ($scriptFile && $path === '/' . $scriptFile) {
            $path = '/';
        }

        return $path;
    }

    public function detectBasePath(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($qPos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $qPos);
        }
        $requestUri = rtrim($requestUri, '/');

        // 1. Use SCRIPT_NAME directory (most common on standard Apache/Nginx)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $dir = rtrim(dirname($scriptName), '/\\');

        // Only use SCRIPT_NAME dir if it's actually a prefix of REQUEST_URI
        if ($dir !== '' && $dir !== '/' && str_starts_with($requestUri, $dir)) {
            return $dir;
        }

        // 2. On some servers (CGI/FPM/proxy), SCRIPT_NAME may not include
        //    the full subdirectory prefix. Detect by looking for "public/" in REQUEST_URI.
        if (($pos = strpos($requestUri, '/public/')) !== false) {
            return substr($requestUri, 0, $pos + 8); // +8 for '/public/'
        }
        if (str_ends_with($requestUri, '/public')) {
            return substr($requestUri, 0, -7); // strip '/public'
        }

        // 3. Fallback: use SCRIPT_FILENAME minus DOCUMENT_ROOT
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($docRoot && $scriptFilename && str_starts_with($scriptFilename, $docRoot)) {
            $relative = dirname(substr($scriptFilename, strlen($docRoot)));
            $relative = rtrim($relative, '/\\');
            if ($relative !== '' && $relative !== '/') {
                // Verify this is a prefix of REQUEST_URI
                if (str_starts_with($requestUri, $relative)) {
                    return $relative;
                }
            }
        }

        return $dir !== '/' ? $dir : '';
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
