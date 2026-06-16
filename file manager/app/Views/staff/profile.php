<?php
/** @var array $app */
ob_start();
$staffName = $_SESSION['staff_name'] ?? 'Staff';
?>

<div class="staff-shell">
  <div class="staff-topbar">
    <div>
      <h4 class="mb-1">Profile</h4>
      <div class="text-muted small">Manage your name and password.</div>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <button class="btn btn-light btn-sm" id="themeToggle" type="button" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
      <a class="btn btn-outline-secondary btn-sm" href="<?= $app['base_url'] ?>/staff/dashboard">Back to Assignments</a>
      <a class="btn btn-outline-secondary btn-sm" href="<?= $app['base_url'] ?>/staff/logout">Logout</a>
    </div>
  </div>

  <div class="container pb-4">
    <div class="panel card-glass p-3">
      <div class="panel-head"><h5>Profile Details</h5><span class="chip">Staff</span></div>
      <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/profile/save" method="post">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars((string)($staff['name'] ?? $staffName)) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" value="<?= htmlspecialchars((string)($staff['email'] ?? '')) ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">New Password</label>
            <input class="form-control" name="password" type="password" placeholder="Leave blank to keep">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-primary">Save Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $content=ob_get_clean(); $title='Staff Profile'; require __DIR__ . '/../layouts/main.php'; ?>
