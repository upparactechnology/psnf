<?php
/** @var array $app */
/** @var array $dashboardData */
/** @var int $storageLimitBytes */
/** @var int $storageUsedBytes */
ob_start();
$formatBytes = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
$dd = $dashboardData ?? [];
$kpi = $dd['top_kpi'] ?? [];
$insights = $dd['insights'] ?? [];
$topResources = $dd['top_active_resources'] ?? ['files' => [], 'folders' => []];

$usagePct = $storageLimitBytes > 0 ? min(100, round(($storageUsedBytes / $storageLimitBytes) * 100, 2)) : 0;
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Admin Overview</h2>
  <p class="text-muted mb-0">High level visibility across files, folders, users, and storage.</p>
</div>

<div class="kpi-grid mb-3">
  <div class="kpi-card card-glass"><div class="kpi-icon bg-blue"><i class="bi bi-file-earmark-lock2"></i></div><div><small>Total Files</small><div class="metric"><?= (int)($kpi['total_files'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="kpi-icon bg-indigo"><i class="bi bi-folder2-open"></i></div><div><small>Total Folders</small><div class="metric"><?= (int)($kpi['total_folders'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="kpi-icon bg-cyan"><i class="bi bi-diagram-3"></i></div><div><small>Total Subfolders</small><div class="metric"><?= (int)($kpi['total_subfolders'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="kpi-icon bg-green"><i class="bi bi-link-45deg"></i></div><div><small>Assigned Files</small><div class="metric"><?= (int)($kpi['assigned_files'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="kpi-icon bg-gold"><i class="bi bi-folder-check"></i></div><div><small>Assigned Folders</small><div class="metric"><?= (int)($kpi['assigned_folders'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="kpi-icon bg-purple"><i class="bi bi-people"></i></div><div><small>Users</small><div class="metric"><?= (int)($kpi['users'] ?? 0) ?></div></div></div>
  <div class="kpi-card card-glass"><div class="storage-ring" style="--p:<?= $usagePct ?>"><span><?= $usagePct ?>%</span></div><div><small>Storage Used</small><div class="metric-sm"><?= number_format((int)($kpi['storage_used_bytes'] ?? 0) / (1024 * 1024 * 1024), 2) . ' GB' ?></div></div></div>
</div>

<div class="dash-layout mb-3">
  <section class="dash-main">
    <div class="panel card-glass p-3 mb-3">
      <div class="panel-head"><h5>Module Shortcuts</h5><span class="chip">Jump</span></div>
      <div class="quick-link-list">
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/analytics"><i class="bi bi-graph-up"></i><span>Analytics</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/audit"><i class="bi bi-activity"></i><span>Audit &amp; Activity</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/permissions"><i class="bi bi-shield-lock"></i><span>Permissions</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/storage"><i class="bi bi-hdd-stack"></i><span>Storage</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/folders"><i class="bi bi-diagram-3"></i><span>Folder Management</span><i class="bi bi-chevron-right"></i></a>
      </div>
    </div>

    <div class="panel card-glass p-3">
      <div class="panel-head"><h5>Top Active Resources</h5><span class="chip">Files and folders</span></div>
      <div class="row g-3">
        <div class="col-md-6">
          <small class="text-muted d-block mb-2">Top files</small>
          <div class="rank-list">
            <?php foreach (($topResources['files'] ?? []) as $i => $f): ?>
              <div class="rank-item"><span>#<?= $i + 1 ?> <?= htmlspecialchars((string)$f['title']) ?></span><small><?= $formatBytes((int)$f['size']) ?></small></div>
            <?php endforeach; ?>
            <?php if (empty($topResources['files'])): ?>
              <div class="text-muted small">No file activity yet.</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block mb-2">Top folders</small>
          <div class="rank-list">
            <?php foreach (($topResources['folders'] ?? []) as $i => $f): ?>
              <div class="rank-item"><span>#<?= $i + 1 ?> <?= htmlspecialchars((string)$f['folder']) ?></span><small><?= $formatBytes((int)$f['bytes']) ?></small></div>
            <?php endforeach; ?>
            <?php if (empty($topResources['folders'])): ?>
              <div class="text-muted small">No folder activity yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <aside class="dash-right">
    <div class="panel card-glass p-3 mb-3">
      <div class="panel-head"><h5>Quick Actions</h5><span class="chip">Ctrl + K</span></div>
      <div class="quick-link-list">
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/folders"><i class="bi bi-folder-plus"></i><span>Create Folder</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/files"><i class="bi bi-cloud-upload"></i><span>Upload File</span><i class="bi bi-chevron-right"></i></a>
        <a class="quick-link-item" href="<?= $app['base_url'] ?>/admin/users"><i class="bi bi-person-plus"></i><span>Add User</span><i class="bi bi-chevron-right"></i></a>
      </div>
    </div>

    <div class="panel card-glass p-3">
      <div class="panel-head"><h5>Smart Insights</h5><span class="chip">Cleanup</span></div>
      <div class="mini-kpi-list">
        <div class="mini-kpi"><span>Empty Folders</span><strong><?= (int)($insights['empty_folders'] ?? 0) ?></strong></div>
        <div class="mini-kpi"><span>Orphan Files</span><strong><?= (int)($insights['orphan_files'] ?? 0) ?></strong></div>
        <div class="mini-kpi"><span>Duplicate Names</span><strong class="text-warning"><?= (int)($insights['duplicate_files'] ?? 0) ?></strong></div>
      </div>
      <div class="d-grid gap-2 mt-3">
        <button class="btn btn-sm btn-light js-cleanup">Quick Cleanup</button>
      </div>
    </div>
  </aside>
</div>

<?php
$adminContent=ob_get_clean();
$title='Dashboard';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
