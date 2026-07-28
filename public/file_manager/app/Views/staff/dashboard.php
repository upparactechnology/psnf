<?php
/** @var array $app */
/** @var array $folders */
/** @var array $resources */
/** @var array $favoriteFolders */
/** @var array $favoriteResources */
/** @var array $favoriteFolderIds */
/** @var array $favoriteResourceIds */
/** @var array $recentActivity */
/** @var array $assignmentCounts */
/** @var array $accessWindow */
ob_start();
$staffName = $_SESSION['staff_name'] ?? 'Staff';
$counts = $assignmentCounts ?? ['folders' => 0, 'files' => 0, 'favorites' => 0];
$favoriteFolders = $favoriteFolders ?? [];
$favoriteResources = $favoriteResources ?? [];
$favoriteFolderIds = $favoriteFolderIds ?? [];
$favoriteResourceIds = $favoriteResourceIds ?? [];
$access = $accessWindow ?? ['start' => '', 'end' => '', 'label' => 'Today'];
?>

<div class="staff-shell">
  <div class="staff-topbar">
    <div>
      <h4 class="mb-1">My Assignments</h4>
      <div class="text-muted small">Welcome, <?= htmlspecialchars($staffName) ?>. Access window: <?= htmlspecialchars($access['label']) ?> <?= htmlspecialchars($access['start']) ?> - <?= htmlspecialchars($access['end']) ?></div>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <a class="btn btn-outline-secondary btn-sm" href="/psnf/public/dashboard"><i class="bi bi-arrow-left-short"></i> Back to ERP</a>
      <div id="staffClock" class="me-2 px-2 py-1 rounded card-glass text-muted small fw-semibold" style="letter-spacing: 0.5px;">--:--:--</div>
      <button class="btn btn-light btn-sm" id="themeToggle" type="button" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
      <a class="btn btn-outline-primary btn-sm" href="<?= $app['base_url'] ?>/staff/profile">Profile</a>
      <a class="btn btn-outline-secondary btn-sm" href="<?= $app['base_url'] ?>/staff/logout">Logout</a>
    </div>
  </div>

  <div class="container pb-4">
    <div class="staff-kpi-grid">
      <div class="staff-kpi-card card-glass">
        <div class="kpi-icon bg-indigo"><i class="bi bi-folder2-open"></i></div>
        <div><small>Assigned Folders</small><div class="metric-sm"><?= (int)$counts['folders'] ?></div></div>
      </div>
      <div class="staff-kpi-card card-glass">
        <div class="kpi-icon bg-blue"><i class="bi bi-file-earmark-lock2"></i></div>
        <div><small>Assigned Files</small><div class="metric-sm"><?= (int)$counts['files'] ?></div></div>
      </div>
      <div class="staff-kpi-card card-glass">
        <div class="kpi-icon bg-gold"><i class="bi bi-star-fill"></i></div>
        <div><small>Favorites</small><div class="metric-sm"><?= (int)$counts['favorites'] ?></div></div>
      </div>
      <div class="staff-kpi-card card-glass">
        <div class="kpi-icon bg-cyan"><i class="bi bi-clock"></i></div>
        <div><small>Access Window</small><div class="metric-sm"><?= htmlspecialchars($access['start']) ?> - <?= htmlspecialchars($access['end']) ?></div></div>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-xl-6">
        <div class="panel card-glass p-3 h-100">
          <div class="panel-head"><h5>Favorites / Pins</h5><span class="chip">Quick access</span></div>
          <div class="favorites-block">
            <div class="mb-3">
              <strong class="d-block mb-2">Folders</strong>
              <?php if (!empty($favoriteFolders)): ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($favoriteFolders as $f): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                      <div><?= htmlspecialchars($f['name']) ?></div>
                      <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $app['base_url'] ?>/staff/folder?id=<?= (int)$f['id'] ?>">Open</a>
                        <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/favorites/toggle" method="post">
                          <input type="hidden" name="type" value="folder">
                          <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                          <button class="btn btn-sm btn-outline-warning"><i class="bi bi-star-fill"></i></button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-muted small">No favorite folders yet.</div>
              <?php endif; ?>
            </div>

            <div>
              <strong class="d-block mb-2">Files</strong>
              <?php if (!empty($favoriteResources)): ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($favoriteResources as $r): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <?= htmlspecialchars($r['title']) ?>
                        <div class="text-muted small"><?= htmlspecialchars((string)($r['folder_name'] ?? 'Unfiled')) ?></div>
                      </div>
                      <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $app['base_url'] ?>/staff/resource/view?id=<?= (int)$r['id'] ?>">Open</a>
                        <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/favorites/toggle" method="post">
                          <input type="hidden" name="type" value="resource">
                          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                          <button class="btn btn-sm btn-outline-warning"><i class="bi bi-star-fill"></i></button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-muted small">No favorite files yet.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="panel card-glass py-2 px-3 d-flex flex-column h-100" style="min-height: 580px;">
          <div class="panel-head mb-1">
            <h5 class="mb-0">Assigned Files</h5>
            <div class="d-flex gap-2">
              <input class="form-control form-control-sm js-local-search" data-filter-group="staff-files" style="max-width:220px" placeholder="Search files">
              <select class="form-select form-select-sm js-filter" data-filter-group="staff-files" data-filter-key="type" style="max-width:160px">
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
                <tr data-filter-group="staff-files" data-type="<?= htmlspecialchars($ext) ?>" data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['mime_type'])) ?>">
                  <td>
                    <?= htmlspecialchars($r['title']) ?>
                    <div class="text-muted small"><?= htmlspecialchars((string)($r['folder_name'] ?? 'Unfiled')) ?></div>
                  </td>
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
                  <td><a class="btn btn-sm btn-primary" href="<?= $app['base_url'] ?>/staff/resource/view?id=<?= (int)$r['id'] ?>">Open</a></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($resources)): ?>
                <tr><td colspan="4" class="text-muted">No assigned files.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-xl-6">
        <div class="panel card-glass p-3" style="min-height: 500px;">
          <div class="panel-head mb-1">
            <h5 class="mb-0">Assigned Folders</h5>
            <input class="form-control form-control-sm js-local-search" data-filter-group="staff-folders" style="max-width:220px" placeholder="Search folders">
          </div>
          <div class="table-responsive table-wrap">
            <table class="table table-modern">
              <thead><tr><th>Folder</th><th>Favorite</th><th>Action</th></tr></thead>
              <tbody>
              <?php foreach($folders as $f): ?>
                <tr data-filter-group="staff-folders" data-search="<?= htmlspecialchars(strtolower($f['name'].' '.$f['id'])) ?>">
                  <td><?= htmlspecialchars($f['name']) ?></td>
                  <td>
                    <form class="ajax-form" action="<?= $app['base_url'] ?>/staff/favorites/toggle" method="post">
                      <input type="hidden" name="type" value="folder">
                      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                      <button class="btn btn-sm <?= isset($favoriteFolderIds[(int)$f['id']]) ? 'btn-warning' : 'btn-outline-warning' ?>">
                        <i class="bi <?= isset($favoriteFolderIds[(int)$f['id']]) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                      </button>
                    </form>
                  </td>
                  <td><a class="btn btn-sm btn-primary" href="<?= $app['base_url'] ?>/staff/folder?id=<?= (int)$f['id'] ?>">Open Folder</a></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($folders)): ?>
                <tr><td colspan="3" class="text-muted">No assigned folders.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="panel card-glass p-3" style="height: auto !important;">
          <div class="panel-head"><h5>Recent Activity</h5><span class="chip">Last 20</span></div>
          <div class="timeline-list" style="max-height: 280px !important;">
            <?php foreach (($recentActivity ?? []) as $event): ?>
              <article class="timeline-item">
                <div class="timeline-dot"><i class="bi bi-shield-check"></i></div>
                <div>
                  <div class="d-flex gap-2 align-items-center flex-wrap">
                    <strong><?= htmlspecialchars((string)$event['event']) ?></strong>
                    <small class="text-muted"><?= date('M d, H:i', strtotime((string)$event['created_at'] . ' UTC')) ?></small>
                  </div>
                  <div class="text-muted small"><?= htmlspecialchars((string)($event['meta'] ?? '')) ?></div>
                </div>
              </article>
            <?php endforeach; ?>
            <?php if (empty($recentActivity)): ?>
              <div class="text-muted small">No activity yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content=ob_get_clean(); $title='Staff Dashboard'; require __DIR__ . '/../layouts/main.php'; ?>
