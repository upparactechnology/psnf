<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Audit;

class AdminAuthController extends Controller {
  public function loginView(): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_name('PSNF_SESSION');
      session_start();
    }

    $user = $_SESSION['user'] ?? null;
    if ($user) {
      $isAdmin = (!empty($user['role_names']) && (in_array('super_admin', $user['role_names']) || in_array('admin', $user['role_names']))) || (($user['email'] ?? '') === 'admin@psnf.edu');
      if ($isAdmin) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = $user['name'] ?? 'Super Admin';
        $_SESSION['admin_email'] = $user['email'] ?? 'admin@psnf.edu';
        $this->redirect('/admin/dashboard');
      }

      $email = $user['email'] ?? '';
      if ($email) {
        $stmt = \App\Core\Database::conn()->prepare("SELECT * FROM staff WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $staff = $stmt->fetch();
        if ($staff) {
          $_SESSION['staff_id'] = $staff['id'];
          $_SESSION['staff_name'] = $staff['name'];
          $_SESSION['staff_email'] = $staff['email'];
          $this->redirect('/staff/dashboard');
        }
      }
    }

    header('Location: /psnf/public/login');
    exit;
  }

  public function login(): void {
    header('Location: /psnf/public/login');
    exit;
  }

  public function logout(): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_name('PSNF_SESSION');
      session_start();
    }
    Audit::log($_SESSION['admin_id'] ?? null, null, 'admin_logout');
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_email']);
    header('Location: /psnf/public/logout');
    exit;
  }
}
