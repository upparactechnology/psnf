<?php
namespace App\Core;

class Controller {
  protected function view(string $file, array $data = []): void {
    extract($data);
    $app = require __DIR__ . '/../../config/app.php';
    $app['base_url'] = $this->resolveBaseUrl($app);
    require __DIR__ . '/../Views/' . $file . '.php';
  }

  protected function redirect(string $path): void {
    $app = require __DIR__ . '/../../config/app.php';
    $baseUrl = $this->resolveBaseUrl($app);
    header('Location: ' . rtrim($baseUrl, '/') . $path);
    exit;
  }

  protected function json(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
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
}
