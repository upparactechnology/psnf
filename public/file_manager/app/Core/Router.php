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
    $base = (string)($app['base_url'] ?? '');
    if ($base === '' || $base === 'auto') {
      $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
      $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
      if ($base === '/') $base = '';
    }
    return str_replace(' ', '%20', $base);
  }

  private function startsWith(string $haystack, string $needle): bool {
    if ($needle === '') return true;
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}
