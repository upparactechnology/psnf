<?php
ob_start();
$formatBytes = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
$dd = $dashboardData ?? [];
$storage = $dd['storage'] ?? ['by_folder' => [], 'largest_files' => []];
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Storage</h2>
  <p class="text-muted mb-0">Monitor storage consumption and preview large resources.</p>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Storage Analytics</h5><span class="chip">Top Usage</span></div>
  <canvas id="storageByFolderChart" height="170"></canvas>
  <div class="rank-list mt-3">
    <?php foreach (($storage['largest_files'] ?? []) as $i => $item): ?>
      <div class="rank-item file-preview-trigger" data-preview='<?= htmlspecialchars(json_encode($item, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)) ?>'><span>#<?= $i + 1 ?> <?= htmlspecialchars((string)$item['file_name']) ?></span><small><?= $formatBytes((int)$item['size']) ?></small></div>
    <?php endforeach; ?>
  </div>
</div>

<div class="preview-drawer" id="filePreviewDrawer" aria-hidden="true">
  <div class="preview-head"><strong>File Preview</strong><button class="btn btn-sm btn-light" id="closePreviewDrawer"><i class="bi bi-x-lg"></i></button></div>
  <div class="preview-tabs">
    <button class="active" data-tab="preview">Preview</button>
    <button data-tab="metadata">Metadata</button>
    <button data-tab="permissions">Permissions</button>
    <button data-tab="activity">Activity</button>
  </div>
  <div class="preview-content" id="previewDrawerContent">Select a file from Largest Resources to preview metadata.</div>
</div>

<?php
$adminContent=ob_get_clean();
$title='Storage';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
