<?php
/** @var array $app */
/** @var array $staff */
ob_start();
?>
<div class="panel card-glass p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Users List</h5>
    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="bi bi-person-plus"></i> Create User</button>
  </div>
  <div class="table-toolbar mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex gap-2 ms-auto">
      <input class="form-control form-control-sm js-local-search" data-filter-group="staff" placeholder="Search users" style="width: 320px;">
      <select class="form-select form-select-sm js-filter" data-filter-group="staff" data-filter-key="status" style="max-width:180px">
        <option value="all">All statuses</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
  </div>
  <div class="table-responsive table-wrap">
    <table class="table table-modern align-middle js-paginate">
      <thead><tr><th>Name</th><th>Email</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach($staff as $s): ?>
        <?php $status = $s['is_active'] ? 'active' : 'inactive'; ?>
        <tr data-filter-group="staff" data-status="<?= $status ?>" data-search="<?= htmlspecialchars(strtolower($s['name'].' '.$s['email'].' '.$status)) ?>">
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td class="mono"><?= htmlspecialchars($s['email']) ?></td>
          <td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
          <td class="text-end">
            <div class="dropdown d-inline-block">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                Actions
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUserModal<?= (int)$s['id'] ?>"><i class="bi bi-pencil"></i> Edit Details</button></li>
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resetPasswordModal<?= (int)$s['id'] ?>"><i class="bi bi-key"></i> Reset Password</button></li>
                <li>
                  <form class="ajax-form m-0" method="post" action="<?= $app['base_url'] ?>/admin/staff/toggle">
                    <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
                    <input type="hidden" name="is_active" value="<?= $s['is_active'] ? 0 : 1 ?>">
                    <button type="submit" class="dropdown-item"><i class="bi bi-power"></i> <?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                  </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form class="ajax-form m-0" method="post" action="<?= $app['base_url'] ?>/admin/staff/delete" onsubmit="return confirm('Delete this user?')">
                    <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </li>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card-glass">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title" id="createUserModalLabel">Create User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/create">
        <div class="modal-body text-start">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input class="form-control" name="name" placeholder="Full name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" placeholder="Email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" name="password" placeholder="Password" required>
          </div>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-sm btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach($staff as $s): ?>
  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal<?= (int)$s['id'] ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?= (int)$s['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="editUserModalLabel<?= (int)$s['id'] ?>">Edit User: <?= htmlspecialchars($s['name']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/update">
          <div class="modal-body text-start">
            <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" name="email" type="email" value="<?= htmlspecialchars($s['email']) ?>" required>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reset Password Modal -->
  <div class="modal fade" id="resetPasswordModal<?= (int)$s['id'] ?>" tabindex="-1" aria-labelledby="resetPasswordModalLabel<?= (int)$s['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="resetPasswordModalLabel<?= (int)$s['id'] ?>">Reset Password: <?= htmlspecialchars($s['name']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/staff/reset-password">
          <div class="modal-body text-start">
            <input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input class="form-control" name="new_password" placeholder="Enter new password" required>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-warning">Reset Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php
$adminContent=ob_get_clean();
$title='Users Management';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
