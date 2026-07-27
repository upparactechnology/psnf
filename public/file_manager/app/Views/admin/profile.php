<?php ob_start(); ?>
<div class="panel card-glass p-4" style="max-width:760px">
  <h5 class="mb-3">Admin Profile</h5>
  <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/profile/save">
    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">New Password</label><input class="form-control" name="password" type="password" placeholder="Leave blank to keep current"></div>
    <button class="btn btn-primary">Update Profile</button>
  </form>
</div>
<?php
$adminContent=ob_get_clean();
$title='Profile';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
