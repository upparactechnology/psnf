<?php
namespace App\Core;

class Auth {
  public static function guardAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
      $app = require __DIR__ . '/../../config/app.php';
      $baseUrl = self::resolveBaseUrl($app);
      header('Location: ' . rtrim($baseUrl, '/') . '/admin/login');
      exit;
    }
  }

  public static function guardStaff(): void {
    if (empty($_SESSION['staff_id'])) {
      $app = require __DIR__ . '/../../config/app.php';
      $baseUrl = self::resolveBaseUrl($app);
      header('Location: ' . rtrim($baseUrl, '/') . '/staff-login');
      exit;
    }
  }

  private static function resolveBaseUrl(array $app): string {
    $base = (string)($app['base_url'] ?? '');
    if ($base === '' || $base === 'auto') {
      $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
      $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
      if ($base === '/') $base = '';
    }
    return str_replace(' ', '%20', $base);
  }
}
