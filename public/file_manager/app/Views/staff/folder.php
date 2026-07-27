<?php
/** @var array $app */
/** @var array $folder */
/** @var array $resources */
/** @var array $favoriteResourceIds */
/** @var bool $favoriteFolder */
ob_start();
$favoriteResourceIds = $favoriteResourceIds ?? [];
?>

<div class="staff-shell">
  <div class="staff-topbar">
    <div>
      <h4 class="mb-1">Folder: <?= htmlspecialchars($folder['name']) ?></h4>
      <div class="text-muted small">Browse assigned files securely.</div>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <div id="staffClock" class="me-2 px-2 py-1 rounded card-glass text-muted small fw-semibold" style="letter-spacing: 0.5px;">--:--:--</div>
      <button class="btn btn-light btn-sm" id="themeToggle" type="button" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
      <a class="btn btn-outline-primary btn-sm" href="<?= $app['base_url'] ?>/staff/profile">Profile</a>
      <a class="btn btn-outline-secondary btn-sm" href="<?= $app['base_url'] ?>/staff/dashboard">Back to Folders</a>
    </div>
  </div>

  <div class="container pb-4">
    <div class="panel card-glass p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <strong><?= htmlspecialchars($folder['name']) ?></strong>
          <div class="text-muted small">Folder access</div>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/favorites/toggle" method="post">
          <input type="hidden" name="type" value="folder">
          <input type="hidden" name="id" value="<?= (int)$folder['id'] ?>">
          <button class="btn btn-sm <?= !empty($favoriteFolder) ? 'btn-warning' : 'btn-outline-warning' ?>">
            <i class="bi <?= !empty($favoriteFolder) ? 'bi-star-fill' : 'bi-star' ?>"></i>
            <?= !empty($favoriteFolder) ? 'Pinned' : 'Pin Folder' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="panel card-glass py-2 px-3">
      <div class="panel-head mb-1">
        <h5 class="mb-0">Files</h5>
        <div class="d-flex gap-2">
          <input class="form-control form-control-sm js-local-search" data-filter-group="staff-folder-files" style="width: 320px;" placeholder="Search files">
          <select class="form-select form-select-sm js-filter" data-filter-group="staff-folder-files" data-filter-key="type" style="max-width:160px">
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
          <thead><tr><th>Title</th><th>Type</th><th>Favorite</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach($resources as $r): ?>
            <?php $ext = strtolower(pathinfo((string)$r['file_name'], PATHINFO_EXTENSION)); ?>
            <tr class="clickable-row" data-url="<?= $app['base_url'] ?>/staff/resource/view?id=<?= (int)$r['id'] ?>" data-filter-group="staff-folder-files" data-type="<?= htmlspecialchars($ext) ?>" data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['mime_type'])) ?>">
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['mime_type']) ?></td>
              <td>
                <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/favorites/toggle" method="post">
                  <input type="hidden" name="type" value="resource">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm <?= isset($favoriteResourceIds[(int)$r['id']]) ? 'btn-warning' : 'btn-outline-warning' ?>">
                    <i class="bi <?= isset($favoriteResourceIds[(int)$r['id']]) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                  </button>
                </form>
              </td>
              <td><a class="btn btn-sm btn-primary" href="<?= $app['base_url'] ?>/staff/resource/view?id=<?= (int)$r['id'] ?>">Open Secure Viewer</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($resources)): ?>
            <tr><td colspan="4" class="text-muted">No files assigned in this folder.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $content=ob_get_clean(); $title='Staff Folder'; require __DIR__ . '/../layouts/main.php'; ?>
