<?php
namespace App\Core;

class Auth {
  public static function guardAdmin(): void {
    if (!empty($_SESSION['user'])) {
      $user = $_SESSION['user'];
      $isAdmin = false;
      if (!empty($user['role_names']) && (in_array('super_admin', $user['role_names']) || in_array('admin', $user['role_names']))) {
        $isAdmin = true;
      }
      if (($user['email'] ?? '') === 'admin@psnf.edu') {
        $isAdmin = true;
      }
      
      if ($isAdmin) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = $user['name'] ?? 'Super Admin';
        $_SESSION['admin_email'] = $user['email'] ?? 'admin@psnf.edu';
      }
    }

    if (empty($_SESSION['admin_id'])) {
      header('Location: /psnf/public/login');
      exit;
    }
  }

  public static function guardStaff(): void {
    if (!empty($_SESSION['user']) && empty($_SESSION['staff_id'])) {
      $email = $_SESSION['user']['email'] ?? '';
      if ($email) {
        $stmt = Database::conn()->prepare("SELECT * FROM staff WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $staff = $stmt->fetch();
        if ($staff) {
          $_SESSION['staff_id'] = $staff['id'];
          $_SESSION['staff_name'] = $staff['name'];
          $_SESSION['staff_email'] = $staff['email'];
        }
      }
    }

    if (empty($_SESSION['staff_id'])) {
      header('Location: /psnf/public/login');
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
