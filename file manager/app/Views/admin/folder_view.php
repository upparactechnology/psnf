<?php
/** @var array $app */
/** @var array $folder */
/** @var array $subfolders */
/** @var array $resources */
/** @var array $staff */
/** @var array $resourceAssignments */
/** @var array $folders */
ob_start();
$formatSize = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0 section-title">Folder: <?= htmlspecialchars($folder['name']) ?></h6>
  <a class="btn btn-sm btn-outline-secondary" href="<?= $app['base_url'] ?>/admin/folders">Back to Folders</a>
</div>

<div class="row g-3">
  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Edit Folder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/update" method="post">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <input type="hidden" name="parent_id" value="<?= (int)($folder['parent_id'] ?? 0) ?>">
      <input class="form-control mb-2" name="name" value="<?= htmlspecialchars($folder['name']) ?>" required>
      <button class="btn btn-outline-primary">Update</button>
    </form>
    <form class="ajax-form mt-2" action="<?= $app['base_url'] ?>/admin/folders/delete" method="post" onsubmit="return confirm('Delete this folder?')">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <button class="btn btn-outline-danger">Delete</button>
    </form>
  </div></div>

  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Create Subfolder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/create" method="post">
      <input type="hidden" name="parent_id" value="<?= (int)$folder['id'] ?>">
      <input class="form-control mb-2" name="name" placeholder="Folder name" required>
      <button class="btn btn-primary">Create Subfolder</button>
    </form>
  </div></div>

  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Upload to This Folder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/upload" method="post" enctype="multipart/form-data">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <input class="form-control mb-2" name="files[]" type="file" multiple required>
      <button class="btn btn-success">Upload Files</button>
    </form>
  </div></div>
</div>

<?php if (!empty($subfolders)): ?>
<div class="card-glass p-3 mt-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 section-title">Subfolders</h6>
    <span class="badge rounded-pill text-bg-secondary"><?= count($subfolders) ?> total</span>
  </div>
  <div class="table-responsive">
    <table class="table table-modern">
      <tr><th>ID</th><th>Name</th><th>Edit</th><th>Actions</th></tr>
      <?php foreach($subfolders as $sf): ?>
      <tr data-search="<?= htmlspecialchars(strtolower($sf['name'].' '.$sf['id'])) ?>">
        <td><?= (int)$sf['id'] ?></td>
        <td><?= htmlspecialchars($sf['name']) ?></td>
        <td>
          <form class="ajax-form d-flex gap-2" action="<?= $app['base_url'] ?>/admin/folders/update" method="post">
            <input type="hidden" name="folder_id" value="<?= (int)$sf['id'] ?>">
            <input type="hidden" name="parent_id" value="<?= (int)($sf['parent_id'] ?? 0) ?>">
            <input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($sf['name']) ?>" style="min-width:160px" required>
            <button class="btn btn-sm btn-outline-primary">Update</button>
          </form>
        </td>
        <td class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-primary" href="<?= $app['base_url'] ?>/admin/folders/view?id=<?= (int)$sf['id'] ?>">View</a>
          <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/delete" method="post" onsubmit="return confirm('Delete this folder?')">
            <input type="hidden" name="folder_id" value="<?= (int)$sf['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card-glass p-3 mt-3">
  <h6 class="section-title">Files in Folder</h6>
  <div class="table-responsive">
    <table class="table table-modern">
      <tr><th>Title</th><th>Type</th><th>Size</th><th>Assign File</th><th>Edit</th><th>Actions</th></tr>
      <?php foreach($resources as $r): ?>
      <?php $assigned = $resourceAssignments[(int)$r['id']] ?? []; ?>
      <tr data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['mime_type'])) ?>">
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><span class="chip"><?= htmlspecialchars($r['mime_type']) ?></span></td>
        <td><?= $formatSize((int)$r['file_size']) ?></td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignFileModal<?= (int)$r['id'] ?>">
            <i class="bi bi-person-fill-gear"></i> Assign (<?= count($assigned) ?>)
          </button>
        </td>
        <td>
          <form class="ajax-form d-flex gap-2" action="<?= $app['base_url'] ?>/admin/resources/update" method="post">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <input class="form-control form-control-sm" name="title" value="<?= htmlspecialchars($r['title']) ?>" style="min-width:160px" required>
            <select class="form-select form-select-sm" name="folder_id" style="min-width:160px">
              <option value="0" <?= empty($r['folder_id']) ? 'selected' : '' ?>>Unfiled</option>
              <?php foreach ($folders as $f): ?>
                <option value="<?= (int)$f['id'] ?>" <?= (int)$f['id'] === (int)($r['folder_id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-outline-primary">Update</button>
          </form>
        </td>
        <td>
          <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/delete" method="post" onsubmit="return confirm('Delete this file?')">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($resources)): ?><tr><td colspan="6" class="text-muted">No files in this folder.</td></tr><?php endif; ?>
    </table>
  </div>
</div>

<?php foreach($resources as $r): ?>
  <?php $assigned = $resourceAssignments[(int)$r['id']] ?? []; ?>
  <!-- Modal -->
  <div class="modal fade" id="assignFileModal<?= (int)$r['id'] ?>" tabindex="-1" aria-labelledby="assignFileModalLabel<?= (int)$r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="assignFileModalLabel<?= (int)$r['id'] ?>">Assign File: <?= htmlspecialchars($r['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/assignments/save" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <p class="text-muted small mb-3">Select staff members allowed to access this file:</p>
            <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
              <?php foreach($staff as $s): ?>
                <div class="form-check text-start">
                  <input class="form-check-input" type="checkbox" name="staff_ids[]" value="<?= (int)$s['id'] ?>" id="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>" <?= isset($assigned[(int)$s['id']]) ? 'checked' : '' ?>>
                  <label class="form-check-label text-start" for="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>">
                    <strong><?= htmlspecialchars($s['name']) ?></strong> <span class="text-muted small">(<?= htmlspecialchars($s['email']) ?>)</span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Assignments</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php
$adminContent=ob_get_clean();
$title='View Folder';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
