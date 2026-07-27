<?php
ob_start();
$formatBytes = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
$dd = $dashboardData ?? [];
$fileTypes = $dd['file_types'] ?? [];
$userAccess = $dd['user_access'] ?? [];
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Analytics</h2>
  <p class="text-muted mb-0">File distribution and access trends across your secure workspace.</p>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>File Type Analytics</h5><span class="chip">Distribution</span></div>
  <div class="row g-3">
    <div class="col-lg-5"><canvas id="fileTypeChart" height="180"></canvas></div>
    <div class="col-lg-7">
      <div class="type-card-grid">
        <?php foreach ($fileTypes as $type => $stat): ?>
          <div class="type-card">
            <strong><?= htmlspecialchars($type) ?></strong>
            <div class="metric-sm"><?= (int)$stat['count'] ?></div>
            <small><?= $formatBytes((int)$stat['bytes']) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <canvas id="fileTypeTrendChart" height="100" class="mt-3"></canvas>
    </div>
  </div>
</div>

<div class="panel card-glass p-3">
  <div class="panel-head"><h5>User Access Analytics</h5><span class="chip">Behavior</span></div>
  <div class="mini-kpi-list">
    <div class="mini-kpi"><span>Most Active User</span><strong><?= htmlspecialchars((string)($userAccess['most_active_user'] ?? 'N/A')) ?></strong></div>
    <div class="mini-kpi"><span>Files Accessed Today</span><strong><?= (int)($userAccess['files_accessed_today'] ?? 0) ?></strong></div>
    <div class="mini-kpi"><span>Folders Accessed Today</span><strong><?= (int)($userAccess['folders_accessed_today'] ?? 0) ?></strong></div>
    <div class="mini-kpi"><span>Failed Logins</span><strong class="text-danger"><?= (int)($userAccess['failed_login_attempts'] ?? 0) ?></strong></div>
    <div class="mini-kpi"><span>New Users Added</span><strong><?= (int)($userAccess['new_users'] ?? 0) ?></strong></div>
  </div>
  <canvas id="loginTrendChart" height="110" class="mt-3"></canvas>
</div>

<?php
$adminContent=ob_get_clean();
$title='Analytics';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
