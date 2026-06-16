<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Audit;

class StaffAuthController extends Controller {
  public function loginView(): void {
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
    Audit::log(null, $_SESSION['staff_id'] ?? null, 'staff_logout');
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_email']);
    header('Location: /psnf/public/logout');
    exit;
  }
}
