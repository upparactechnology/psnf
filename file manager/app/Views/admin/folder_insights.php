<?php
ob_start();
$dd = $dashboardData ?? [];
$tree = $dd['folder_tree'] ?? [];
$folderCards = $dd['folder_security_cards'] ?? [];

$baseUrl = $app['base_url'] ?? '';

$renderTree = function(array $nodes, int $depth = 0) use (&$renderTree, $baseUrl): string {
  $html = '';
  foreach ($nodes as $node) {
    $status = strtolower((string)($node['status'] ?? 'private'));
    $icon = $status === 'shared' ? 'bi-people-fill' : ($status === 'restricted' ? 'bi-shield-lock-fill' : 'bi-lock-fill');
    $children = $node['children'] ?? [];
    $hasChildren = count($children) > 0;
    $folderId = (int)($node['id'] ?? 0);
    $href = $folderId > 0 ? $baseUrl . '/admin/folders/view?id=' . $folderId : $baseUrl . '/admin/folders';
    $html .= '<div class="tree-node" data-depth="' . (int)$depth . '" data-search="' . htmlspecialchars(strtolower((string)$node['name'])) . '">';
    $html .= '<div class="tree-row">';
    $html .= $hasChildren ? '<button class="tree-toggle" type="button" aria-label="Expand"><i class="bi bi-chevron-right"></i></button>' : '<span class="tree-toggle-placeholder"></span>';
    $html .= '<i class="bi bi-folder2-open tree-folder-icon"></i>';
    $html .= '<a href="' . $href . '" class="tree-name">' . htmlspecialchars((string)$node['name']) . '</a>';
    $html .= '<span class="tree-badge">' . (int)($node['file_count'] ?? 0) . ' files</span>';
    $html .= '<span class="tree-badge">' . (int)($node['user_count'] ?? 0) . ' users</span>';
    $html .= '<span class="tree-status status-' . htmlspecialchars($status) . '"><i class="bi ' . $icon . '"></i>' . ucfirst($status) . '</span>';
    $html .= '<small class="text-muted ms-auto">' . htmlspecialchars((string)($node['last_updated'] ?? '')) . '</small>';
    $html .= '</div>';
    if ($hasChildren) {
      $html .= '<div class="tree-children">' . $renderTree($children, $depth + 1) . '</div>';
    }
    $html .= '</div>';
  }
  return $html;
};
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Folder Insights</h2>
  <p class="text-muted mb-0">Review folder hierarchy and security posture at a glance.</p>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-6">
    <div class="panel card-glass p-3 h-100">
      <div class="panel-head"><h5>Folder Hierarchy</h5><span class="chip">Drag/Drop Ready</span></div>
      <div class="folder-tree-board"><?= $renderTree($tree) ?></div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="panel card-glass p-3 h-100">
      <div class="panel-head"><h5>Folder Security Cards</h5><span class="chip">Risk Indicators</span></div>
      <div class="folder-sec-grid">
      <?php foreach ($folderCards as $card): ?>
        <article class="folder-sec-card risk-<?= htmlspecialchars((string)$card['risk']) ?>">
          <div class="d-flex align-items-start gap-2"><i class="bi bi-folder-fill mt-1" style="flex-shrink:0;"></i><strong style="word-break:break-word;"><?= htmlspecialchars((string)$card['name']) ?></strong></div>
          <div class="small text-muted mt-1">Files: <?= (int)$card['file_count'] ?> | Users: <?= (int)$card['user_count'] ?></div>
          <div class="small">Access: <span class="chip"><?= htmlspecialchars((string)$card['access']) ?></span></div>
          <div class="small text-muted">Last Accessed: <?= htmlspecialchars((string)$card['last_accessed']) ?></div>
          <div class="mt-2 d-flex gap-2">
            <a class="btn btn-sm btn-light" href="<?= $app['base_url'] ?>/admin/folders/view?id=<?= (int)$card['id'] ?>">Open</a>
            <a class="btn btn-sm btn-light" href="<?= $app['base_url'] ?>/admin/permissions">Policy</a>
          </div>
        </article>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$adminContent=ob_get_clean();
$title='Folder Insights';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
