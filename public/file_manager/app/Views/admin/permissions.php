<?php
/** @var array $app */
/** @var array $dashboardData */
/** @var array $folders */
/** @var array $resources */
/** @var array $staff */
ob_start();
$dd = $dashboardData ?? [];
$permOverview = $dd['permission_overview'] ?? [];
$matrix = $dd['permission_matrix'] ?? [];
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Permissions</h2>
  <p class="text-muted mb-0">Manage access rules and bulk updates for your team.</p>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Permission Overview</h5><span class="chip">Governance</span></div>
  <div class="mini-kpi-list">
    <?php foreach ($permOverview as $label => $value): ?>
      <div class="mini-kpi"><span><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$label))) ?></span><strong><?= (int)$value ?></strong></div>
    <?php endforeach; ?>
  </div>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Assign Access</h5><span class="chip">CRUD</span></div>
  <div class="row g-3">
    <div class="col-lg-6">
      <h6 class="mb-2">Folder Access</h6>
      <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/assignments/save" method="post">
        <label class="form-label">Folder</label>
        <select class="form-select mb-2" name="folder_id" required>
          <?php foreach ($folders as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label class="form-label">Users (leave empty to clear)</label>
        <select class="form-select mb-2" name="staff_ids[]" multiple>
          <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-primary">Save Access</button>
      </form>
    </div>
    <div class="col-lg-6">
      <h6 class="mb-2">File Access</h6>
      <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/assignments/save" method="post">
        <label class="form-label">File</label>
        <select class="form-select mb-2" name="resource_id" required>
          <?php foreach ($resources as $r): ?>
            <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars((string)$r['folder_name']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <label class="form-label">Users (leave empty to clear)</label>
        <select class="form-select mb-2" name="staff_ids[]" multiple>
          <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-primary">Save Access</button>
      </form>
    </div>
  </div>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>User Permission Matrix</h5><span class="chip">Bulk Update Ready</span></div>
  <div class="table-toolbar mb-2">
    <input class="form-control form-control-sm" id="permissionSearch" placeholder="Search user, role, access" style="max-width:320px">
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-light" id="bulkGrantBtn">Bulk Grant</button>
      <button class="btn btn-sm btn-light" id="bulkRevokeBtn">Bulk Revoke</button>
      <button class="btn btn-sm btn-outline-primary" id="exportMatrixBtn">Export</button>
    </div>
  </div>
  <div class="table-wrap">
    <table class="table table-modern table-sm" id="permissionMatrix">
      <thead><tr><th><input type="checkbox" id="permSelectAll"></th><th>User</th><th>Files</th><th>Folders</th><th>Role</th><th>Access</th><th>Read</th><th>Upload</th><th>Delete</th><th>Manage</th><th>Admin</th></tr></thead>
      <tbody>
      <?php foreach ($matrix as $m): ?>
        <tr data-search="<?= htmlspecialchars(strtolower($m['name'] . ' ' . $m['role'] . ' ' . $m['access'])) ?>">
          <td><input type="checkbox" class="perm-row-check"></td>
          <td><div><strong><?= htmlspecialchars((string)$m['name']) ?></strong><div class="text-muted small"><?= htmlspecialchars((string)$m['email']) ?></div></div></td>
          <td><?= (int)$m['files'] ?></td>
          <td><?= (int)$m['folders'] ?></td>
          <td><span class="chip"><?= htmlspecialchars((string)$m['role']) ?></span></td>
          <td><span class="chip"><?= htmlspecialchars((string)$m['access']) ?></span></td>
          <td><input type="checkbox" <?= !empty($m['read']) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" <?= !empty($m['upload']) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" <?= !empty($m['delete']) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" <?= !empty($m['manage']) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" <?= !empty($m['admin']) ? 'checked' : '' ?>></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="bulk-toolbar card-glass d-none" id="bulkActionToolbar">
  <div><strong id="bulkCount">0</strong> selected</div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-sm btn-light js-bulk-action" data-action="assign-files">Assign Files</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="assign-folders">Assign Folders</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="move">Move</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="delete">Delete</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="change-permissions">Change Permissions</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="revoke-access">Revoke Access</button>
    <button class="btn btn-sm btn-light js-bulk-action" data-action="export-logs">Export Logs</button>
  </div>
</div>

<?php
$adminContent=ob_get_clean();
$title='Permissions';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
