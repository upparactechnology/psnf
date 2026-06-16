<?php
/** @var array $app */
/** @var array $staff */
ob_start();
?>
<div class="panel card-glass p-3 mb-3">
  <h5 class="mb-3">Create User</h5>
  <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/create">
    <div class="row g-2">
      <div class="col-md-4"><input class="form-control" name="name" placeholder="Full name" required></div>
      <div class="col-md-4"><input class="form-control" name="email" type="email" placeholder="Email" required></div>
      <div class="col-md-3"><input class="form-control" name="password" placeholder="Password" required></div>
      <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div>
    </div>
  </form>
</div>
<div class="panel card-glass p-3">
  <h5 class="mb-3">Users List</h5>
  <div class="table-toolbar mb-2">
    <input class="form-control form-control-sm js-local-search" data-filter-group="staff" placeholder="Search users" style="max-width:240px">
    <select class="form-select form-select-sm js-filter" data-filter-group="staff" data-filter-key="status" style="max-width:180px">
      <option value="all">All statuses</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
  <div class="table-responsive table-wrap">
    <table class="table table-modern align-middle">
      <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Edit</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($staff as $s): ?>
        <?php $status = $s['is_active'] ? 'active' : 'inactive'; ?>
        <tr data-filter-group="staff" data-status="<?= $status ?>" data-search="<?= htmlspecialchars(strtolower($s['name'].' '.$s['email'].' '.$status)) ?>">
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td class="mono"><?= htmlspecialchars($s['email']) ?></td>
          <td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
          <td>
            <form class="ajax-form d-flex gap-2" method="post" action="<?= $app['base_url'] ?>/admin/staff/update">
              <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
              <input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($s['name']) ?>" style="min-width:160px" required>
              <input class="form-control form-control-sm" name="email" type="email" value="<?= htmlspecialchars($s['email']) ?>" style="min-width:200px" required>
              <button class="btn btn-sm btn-outline-primary">Update</button>
            </form>
          </td>
          <td class="d-flex gap-2">
            <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/toggle">
              <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
              <input type="hidden" name="is_active" value="<?= $s['is_active'] ? 0 : 1 ?>">
              <button class="btn btn-sm <?= $s['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
            </form>
            <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/reset-password">
              <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
              <input class="form-control form-control-sm" style="width:170px" name="new_password" placeholder="New password" required>
              <button class="btn btn-sm btn-warning">Reset</button>
            </form>
            <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/delete" onsubmit="return confirm('Delete this user?')">
              <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$adminContent=ob_get_clean();
$title='Users Management';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
