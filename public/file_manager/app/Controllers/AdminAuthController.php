<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Audit;

class AdminAuthController extends Controller {
  public function loginView(): void { $this->view('auth/admin_login'); }

  public function login(): void {
    try {
      $user = User::adminByEmail($_POST['email'] ?? '');
      if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
        Audit::log(null,null,'admin_login_failed',$_POST['email'] ?? '');
        $this->redirect('/admin/login?e=Invalid credentials');
      }
      $_SESSION['admin_id'] = $user['id'];
      $_SESSION['admin_name'] = $user['name'];
      Audit::log((int)$user['id'],null,'admin_login_success');
      $this->redirect('/admin/dashboard');
    } catch (\Throwable $e) { $this->redirect('/admin/login?e=System error'); }
  }

  public function logout(): void {
    Audit::log($_SESSION['admin_id'] ?? null,null,'admin_logout');
    session_destroy();
    $this->redirect('/admin/login');
  }
}
