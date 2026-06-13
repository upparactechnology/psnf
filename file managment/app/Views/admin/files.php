<?php
/** @var array $app */
/** @var array $resources */
/** @var array $folders */
ob_start();
$formatSize = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
?>

<div class="row g-3">
  <div class="col-xl-4">
    <div class="panel card-glass p-3">
      <h5 class="mb-3">Upload Files</h5>
      <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/upload" method="post" enctype="multipart/form-data">
        <label class="form-label">Folder</label>
        <select class="form-select mb-2" name="folder_id">
          <option value="0">Unfiled</option>
          <?php foreach ($folders as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label class="form-label">Files</label>
        <input class="form-control mb-2" name="files[]" type="file" multiple required>
        <button class="btn btn-primary">Upload</button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="panel card-glass p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">All Files</h5>
        <div class="d-flex gap-2">
          <input class="form-control form-control-sm js-local-search" data-filter-group="files" style="max-width:220px" placeholder="Search files">
          <select class="form-select form-select-sm js-filter" data-filter-group="files" data-filter-key="folder" style="max-width:180px">
            <option value="all">All folders</option>
            <option value="0">Unfiled</option>
            <?php foreach ($folders as $f): ?>
              <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select form-select-sm js-filter" data-filter-group="files" data-filter-key="type" style="max-width:160px">
            <option value="all">All types</option>
            <option value="pdf">PDF</option>
            <option value="jpg">JPG</option>
            <option value="jpeg">JPEG</option>
            <option value="png">PNG</option>
            <option value="mp4">MP4</option>
            <option value="webm">WEBM</option>
            <option value="mov">MOV</option>
          </select>
        </div>
      </div>
      <div class="table-responsive table-wrap">
        <table class="table table-modern">
          <thead><tr><th>Title</th><th>Folder</th><th>Type</th><th>Size</th><th>Assign File</th><th>Edit</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($resources as $r): ?>
            <?php $ext = strtolower(pathinfo((string)$r['file_name'], PATHINFO_EXTENSION)); ?>
            <?php $assigned = $resourceAssignments[(int)$r['id']] ?? []; ?>
            <tr data-filter-group="files" data-folder="<?= (int)($r['folder_id'] ?? 0) ?>" data-type="<?= htmlspecialchars($ext) ?>" data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['folder_name'].' '.$r['mime_type'])) ?>">
              <td><?= htmlspecialchars($r['title']) ?><div class="text-muted small"><?= htmlspecialchars($r['file_name']) ?></div></td>
              <td><?= htmlspecialchars((string)$r['folder_name']) ?></td>
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
                  <input class="form-control form-control-sm" name="title" value="<?= htmlspecialchars($r['title']) ?>" style="min-width:180px" required>
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
          </tbody>
        </table>
      </div>
    </div>
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
$title='File Management';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
