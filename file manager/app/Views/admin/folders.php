<?php
/** @var array $app */
/** @var array $folders */
/** @var array $staff */
/** @var array $folderAssignments */
/** @var array $dashboardData */
ob_start();
$dd = $dashboardData ?? [];
$tree = $dd['folder_tree'] ?? [];
$folderCards = $dd['folder_security_cards'] ?? [];
$insights = $dd['insights'] ?? [];
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
  <h2 class="mb-1">Folder Management</h2>
  <p class="text-muted mb-0">Manage folders and review access insights together.</p>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Folder Insights</h5><span class="chip">Dashboard</span></div>
  <div class="mini-kpi-list">
    <div class="mini-kpi"><span>Empty Folders</span><strong><?= (int)($insights['empty_folders'] ?? 0) ?></strong></div>
    <div class="mini-kpi"><span>Orphan Files</span><strong><?= (int)($insights['orphan_files'] ?? 0) ?></strong></div>
    <div class="mini-kpi"><span>Duplicate Names</span><strong><?= (int)($insights['duplicate_files'] ?? 0) ?></strong></div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-6">
    <div class="panel card-glass p-3 h-100">
      <div class="panel-head"><h5>Folder Hierarchy</h5><span class="chip">Tree</span></div>
      <div class="folder-tree-board"><?= $renderTree($tree) ?></div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="panel card-glass p-3 h-100">
      <div class="panel-head"><h5>Folder Security Cards</h5><span class="chip">Access</span></div>
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

<div class="row g-3">
  <div class="col-xl-4"><div class="panel card-glass p-3">
    <h5 class="mb-3">Create Folder</h5>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/create" method="post">
      <input class="form-control mb-2" name="name" placeholder="Folder name" required>
      <button class="btn btn-primary">Create Folder</button>
    </form>
  </div></div>

  <div class="col-xl-8"><div class="panel card-glass p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Folders</h5>
      <div class="d-flex gap-2">
        <input class="form-control form-control-sm js-local-search" data-filter-group="folders" style="max-width:220px" placeholder="Search folders">
        <select class="form-select form-select-sm js-filter" data-filter-group="folders" data-filter-key="assigned" style="max-width:180px">
          <option value="all">All assignments</option>
          <option value="assigned">Assigned</option>
          <option value="unassigned">Unassigned</option>
        </select>
      </div>
    </div>
    <div class="table-responsive table-wrap"><table class="table table-modern"><thead><tr><th>ID</th><th>Name</th><th>Assign Users</th><th>Edit</th><th>Actions</th></tr></thead><tbody>
      <?php foreach($folders as $f): ?>
      <?php $assigned = $folderAssignments[(int)$f['id']] ?? []; ?>
      <?php $assignedState = count($assigned) ? 'assigned' : 'unassigned'; ?>
      <tr data-filter-group="folders" data-assigned="<?= $assignedState ?>" data-search="<?= htmlspecialchars(strtolower($f['name'].' '.$f['id'])) ?>">
        <td><?= (int)$f['id'] ?></td>
        <td><?= htmlspecialchars($f['name']) ?></td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#assignFolderModal<?= (int)$f['id'] ?>">
            <i class="bi bi-person-fill-gear"></i> Assign (<?= count($assigned) ?>)
          </button>
        </td>
        <td>
          <form class="ajax-form d-flex gap-2" action="<?= $app['base_url'] ?>/admin/folders/update" method="post">
            <input type="hidden" name="folder_id" value="<?= (int)$f['id'] ?>">
            <input type="hidden" name="parent_id" value="<?= (int)($f['parent_id'] ?? 0) ?>">
            <input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($f['name']) ?>" style="min-width:180px" required>
            <button class="btn btn-sm btn-outline-primary">Update</button>
          </form>
        </td>
        <td class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-primary" href="<?= $app['base_url'] ?>/admin/folders/view?id=<?= (int)$f['id'] ?>">View</a>
          <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/delete" method="post" onsubmit="return confirm('Delete this folder?')">
            <input type="hidden" name="folder_id" value="<?= (int)$f['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody></table></div>
  </div></div>
</div>

<?php foreach($folders as $f): ?>
  <?php $assigned = $folderAssignments[(int)$f['id']] ?? []; ?>
  <!-- Modal -->
  <div class="modal fade" id="assignFolderModal<?= (int)$f['id'] ?>" tabindex="-1" aria-labelledby="assignFolderModalLabel<?= (int)$f['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="assignFolderModalLabel<?= (int)$f['id'] ?>">Assign Folder: <?= htmlspecialchars($f['name']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/assignments/save" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="folder_id" value="<?= (int)$f['id'] ?>">
            <p class="text-muted small mb-3">Select staff members allowed to access this folder:</p>
            <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
              <?php foreach($staff as $s): ?>
                <div class="form-check text-start">
                  <input class="form-check-input" type="checkbox" name="staff_ids[]" value="<?= (int)$s['id'] ?>" id="folder_<?= (int)$f['id'] ?>_staff_<?= (int)$s['id'] ?>" <?= isset($assigned[(int)$s['id']]) ? 'checked' : '' ?>>
                  <label class="form-check-label text-start" for="folder_<?= (int)$f['id'] ?>_staff_<?= (int)$s['id'] ?>">
                    <strong><?= htmlspecialchars($s['name']) ?></strong> <span class="text-muted small">(<?= htmlspecialchars($s['email']) ?>)</span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-warning">Save Assignments</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php
$adminContent=ob_get_clean();
$title='Folder Management';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
