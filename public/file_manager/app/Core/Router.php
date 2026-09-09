<?php
namespace App\Core;

class Router {
  private array $routes = [];

  public function get(string $path, array $handler): void { $this->add('GET', $path, $handler); }
  public function post(string $path, array $handler): void { $this->add('POST', $path, $handler); }

  private function add(string $method, string $path, array $handler): void {
    $this->routes[$method][$this->normalizePath($path)] = $handler;
  }

  private function normalizePath(string $path): string {
    $path = trim($path);
    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
  }

  public function dispatch(string $method, string $uri): void {
    $app = require __DIR__ . '/../../config/app.php';
    $base = $this->normalizePath(urldecode($this->resolveBaseUrl($app)));
    $uriPath = $this->normalizePath(urldecode($uri));

    // Strip the base path from the URI
    if ($base !== '/' && $this->startsWith($uriPath, $base)) {
      $uriPath = substr($uriPath, strlen($base));
      $uriPath = $this->normalizePath($uriPath);
    }

    if ($uriPath === '/index.php') {
      header('Location: ' . $base, 301);
      exit;
    }

    $handler = $this->routes[$method][$uriPath] ?? null;
    if (!$handler) {
      http_response_code(404);
      echo '404 Not Found';
      return;
    }

    [$class, $action] = $handler;
    (new $class())->$action();
  }

  private function resolveBaseUrl(array $app): string {
    // Priority 1: Explicit base_path from config (e.g. 'file-manager' → '/file-manager')
    // Use it to detect the full base from REQUEST_URI (including project prefix like /psnf/erp)
    $configuredPath = (string)($app['base_path'] ?? '');
    if ($configuredPath !== '' && $configuredPath !== 'auto') {
      $basePath = '/' . trim($configuredPath, '/');
      $baseSeg  = $basePath . '/';

      $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
      if (($pos = strpos($requestUri, '?')) !== false) {
        $requestUri = substr($requestUri, 0, $pos);
      }

      // Standard: base path at start of URI (e.g. /file-manager/dashboard)
      if (str_starts_with($requestUri, $baseSeg) || rtrim($requestUri, '/') === $basePath) {
        return $basePath;
      }

      // Nested: base path embedded deeper (e.g. /psnf/erp/file-manager/dashboard)
      $pos = strpos($requestUri, $baseSeg);
      if ($pos !== false && $pos > 0) {
        $prefix = substr($requestUri, 0, $pos);
        return rtrim($prefix, '/') . $basePath;
      }

      // Exact match at non-zero position (e.g. /psnf/erp/file-manager)
      $pos = strrpos($requestUri, $basePath);
      if ($pos !== false && $pos > 0 && ($pos + strlen($basePath)) === strlen(rtrim($requestUri, '/'))) {
        $prefix = substr($requestUri, 0, $pos);
        return rtrim($prefix, '/') . $basePath;
      }

      return $basePath;
    }

    // Priority 2: Explicit base_url (full URL)
    $base = (string)($app['base_url'] ?? '');
    if ($base !== '' && $base !== 'auto') {
      $parsed = parse_url($base);
      if (!empty($parsed['path']) && $parsed['path'] !== '/') {
        return '/' . trim($parsed['path'], '/');
      }
      return '';
    }

    // Priority 3: Auto-detect from SCRIPT_NAME
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($base === '/') $base = '';

    // Strip /public suffix
    if (str_ends_with($base, '/public')) {
      $base = substr($base, 0, -7);
    }

    return str_replace(' ', '%20', $base);
  }

  private function startsWith(string $haystack, string $needle): bool {
    if ($needle === '') return true;
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}
