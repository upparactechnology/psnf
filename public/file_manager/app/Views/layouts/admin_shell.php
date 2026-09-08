<?php
/** @var array $app */
/** @var string $adminContent */
/** @var string $title */
/** @var array $dashboardData */
$activeNav = $activeNav ?? '';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$crumb = $title ?? 'Dashboard';
?>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div>
      <div class="brand-wrap">
        <div class="brand-mark">PS</div>
        <div>
          <div class="brand">PSNF Secure DRM</div>
          <small class="brand-sub">Pearl special needs</small>
        </div>
      </div>
      <nav class="nav-group mt-3">
        <a class="nav-item" href="<?= $app['erp_base_url'] ?>/dashboard" style="background: rgba(99,102,241,0.08); color: #6366f1; border: 1px solid rgba(99,102,241,0.15); margin-bottom: 12px;"><i class="bi bi-arrow-left-circle-fill"></i><span>Back to ERP</span></a>
        <a class="nav-item <?= $activeNav==='dashboard'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/dashboard"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>
        <a class="nav-item <?= $activeNav==='analytics'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/analytics"><i class="bi bi-graph-up"></i><span>Analytics</span></a>
        <a class="nav-item <?= $activeNav==='audit'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/audit"><i class="bi bi-activity"></i><span>Audit &amp; Activity</span></a>
        <a class="nav-item <?= $activeNav==='permissions'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/permissions"><i class="bi bi-shield-lock"></i><span>Permissions</span></a>
        <a class="nav-item <?= $activeNav==='storage'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/storage"><i class="bi bi-hdd-stack"></i><span>Storage</span></a>
        <a class="nav-item <?= $activeNav==='files'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/files"><i class="bi bi-file-earmark-lock2"></i><span>File Management</span></a>
        <a class="nav-item <?= $activeNav==='folders'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/folders"><i class="bi bi-folder2-open"></i><span>Folder Management</span></a>
        <a class="nav-item <?= $activeNav==='users'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/users"><i class="bi bi-people"></i><span>Users Management</span></a>
        <a class="nav-item <?= $activeNav==='settings'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/settings"><i class="bi bi-shield-check"></i><span>Settings</span></a>
        <a class="nav-item <?= $activeNav==='profile'?'active':'' ?>" href="<?= $app['base_url'] ?>/admin/profile"><i class="bi bi-person-circle"></i><span>Profile</span></a>
      </nav>
    </div>

    <div class="workspace-widget card-glass p-3">
      <small class="text-muted d-block mb-1">Workspace</small>
      <strong class="d-block">PSNF Main</strong>
      <small class="text-muted mono">Ctrl + K Quick Command</small>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-topbar">
      <div class="top-left d-flex align-items-center gap-2">
        <button class="btn btn-light btn-sm" id="sidebarToggle" type="button"><i class="bi bi-list"></i></button>
        <div>
          <h5 class="mb-0"><?= htmlspecialchars($title ?? 'Admin') ?></h5>
          <small class="text-muted">Home / <?= htmlspecialchars($crumb) ?></small>
        </div>
      </div>
      <div class="top-center"><input id="globalSearch" class="form-control top-search" placeholder="Search folders, files, users"></div>
      <div class="top-right">
        <button class="btn btn-light btn-sm" id="themeToggle" type="button" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
        <button class="btn btn-light btn-sm" id="cmdkToggle" type="button" title="Command palette"><i class="bi bi-command"></i></button>
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Quick Actions</button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/folders">Create Folder</a></li>
            <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/files">Upload File</a></li>
            <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/users">Add User</a></li>
          </ul>
        </div>
        <div class="dropdown">
          <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person"></i> <?= htmlspecialchars($adminName) ?></button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/profile">Profile</a></li>
            <li><a class="dropdown-item text-danger" href="<?= $app['base_url'] ?>/admin/logout">Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
    <section class="admin-content"><?= $adminContent ?? '' ?></section>
    <?php if (isset($dashboardData)): ?>
      <script>
      window.psnfDashboard = <?= json_encode([
        'fileTypes' => $dashboardData['file_types'] ?? [],
        'trends' => $dashboardData['trends'] ?? ['activity_days' => [], 'access_days' => []],
        'storageByFolder' => $dashboardData['storage']['by_folder'] ?? [],
        'searchIndex' => $dashboardData['search_index'] ?? [],
      ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
      </script>
    <?php endif; ?>
  </main>
</div>
