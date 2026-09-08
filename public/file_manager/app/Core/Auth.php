<?php
namespace App\Core;

class Auth {
  public static function guardAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
      // If ERP-bridged staff user, redirect to staff portal
      if (!empty($_SESSION['staff_id']) && !empty($_SESSION['erp_bridged'])) {
        $app = require __DIR__ . '/../../config/app.php';
        $baseUrl = self::resolveBaseUrl($app);
        header('Location: ' . rtrim($baseUrl, '/') . '/staff/dashboard');
        exit;
      }
      // Redirect to PSNF ERP login instead of local login
      $app = require __DIR__ . '/../../config/app.php';
      $baseUrl = self::resolveBaseUrl($app);
      header('Location: ' . rtrim($baseUrl, '/') . '/login?redirect=file_manager');
      exit;
    }
  }

  public static function guardStaff(): void {
    if (empty($_SESSION['staff_id'])) {
      // If ERP-bridged admin user, redirect to admin portal
      if (!empty($_SESSION['admin_id']) && !empty($_SESSION['erp_bridged'])) {
        $app = require __DIR__ . '/../../config/app.php';
        $baseUrl = self::resolveBaseUrl($app);
        header('Location: ' . rtrim($baseUrl, '/') . '/admin/dashboard');
        exit;
      }
      $app = require __DIR__ . '/../../config/app.php';
      $baseUrl = self::resolveBaseUrl($app);
      header('Location: ' . rtrim($baseUrl, '/') . '/login?redirect=file_manager');
      exit;
    }
  }

  private static function resolveBaseUrl(array $app): string {
    $base = (string)($app['base_url'] ?? '');
    if ($base !== '' && $base !== 'auto') {
      return str_replace(' ', '%20', $base);
    }

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    if (($qPos = strpos($requestUri, '?')) !== false) {
      $requestUri = substr($requestUri, 0, $qPos);
    }
    $requestUri = '/' . ltrim($requestUri, '/');

    if (preg_match('#^(.*?/file_manager/public)(?:/|$)#i', $requestUri, $m)) {
      return $m[1];
    }

    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($base === '/') $base = '';

    return str_replace(' ', '%20', $base);
  }
}
