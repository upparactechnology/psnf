<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Audit;

class StaffAuthController extends Controller {
  public function loginView(): void { $this->view('auth/staff_login'); }

  public function login(): void {
    try {
      $user = User::staffByEmail($_POST['email'] ?? '');
      if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
        Audit::log(null,null,'staff_login_failed',$_POST['email'] ?? '');
        $this->redirect('/staff-login?e=Invalid credentials');
      }
      $_SESSION['staff_id'] = $user['id'];
      $_SESSION['staff_name'] = $user['name'];
      Audit::log(null,(int)$user['id'],'staff_login_success');
      $this->redirect('/staff/dashboard');
    } catch (\Throwable $e) { $this->redirect('/staff-login?e=System error'); }
  }

  public function logout(): void {
    Audit::log(null,$_SESSION['staff_id'] ?? null,'staff_logout');
    session_destroy();
    $this->redirect('/staff-login');
  }
}
