<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Audit;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Setting;
use App\Models\User;

class AdminController extends Controller {
  public function dashboard(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/dashboard', array_merge($data, ['activeNav' => 'dashboard']));
  }

  public function analytics(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/analytics', array_merge($data, ['activeNav' => 'analytics']));
  }

  public function audit(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/audit', array_merge($data, ['activeNav' => 'audit']));
  }

  public function permissions(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/permissions', array_merge($data, [
      'activeNav' => 'permissions',
      'folders' => Folder::all(),
      'resources' => Resource::all(),
      'staff' => User::allStaff(),
    ]));
  }

  public function storage(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/storage', array_merge($data, ['activeNav' => 'storage']));
  }

  public function folderInsights(): void {
    Auth::guardAdmin();
    $this->redirect('/admin/folders');
  }

  private function dashboardBaseData(): array {
    $folders = Folder::all();
    $resources = Resource::all();
    $staff = User::allStaff();
    $logs = Audit::recent(250);

    $folderAssignments = $this->folderAssignmentsMap();
    $resourceAssignments = $this->resourceAssignmentsMap();
    $storageUsedBytes = array_reduce($resources, fn($sum, $r) => $sum + (int)($r['file_size'] ?? 0), 0);
    $storageLimitBytes = 2 * 1024 * 1024 * 1024;

    $dashboardData = $this->buildDashboardData($folders, $resources, $staff, $logs, $folderAssignments, $resourceAssignments);

    return [
      'storageUsedBytes' => $storageUsedBytes,
      'storageLimitBytes' => $storageLimitBytes,
      'dashboardData' => $dashboardData,
    ];
  }

  private function buildDashboardData(array $folders, array $resources, array $staff, array $logs, array $folderAssignments, array $resourceAssignments): array {
    $folderById = [];
    $rootFolders = [];
    $children = [];
    $resourceCountByFolder = [];
    $storageByFolder = [];
    $duplicateMap = [];

    foreach ($folders as $folder) {
      $id = (int)$folder['id'];
      $pid = (int)($folder['parent_id'] ?? 0);
      $folderById[$id] = $folder;
      $resourceCountByFolder[$id] = 0;
      $storageByFolder[$id] = 0;
      if ($pid > 0) {
        $children[$pid][] = $id;
      } else {
        $rootFolders[] = $id;
      }
    }

    $fileTypes = [
      'PDFs' => ['count' => 0, 'bytes' => 0],
      'Images' => ['count' => 0, 'bytes' => 0],
      'Videos' => ['count' => 0, 'bytes' => 0],
      'Documents' => ['count' => 0, 'bytes' => 0],
      'Archives' => ['count' => 0, 'bytes' => 0],
      'Others' => ['count' => 0, 'bytes' => 0],
    ];

    $largestFiles = [];
    $orphanFiles = 0;

    foreach ($resources as $resource) {
      $rid = (int)$resource['id'];
      $fid = (int)($resource['folder_id'] ?? 0);
      $size = (int)($resource['file_size'] ?? 0);
      $name = (string)($resource['file_name'] ?? '');
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

      if ($fid <= 0 || !isset($folderById[$fid])) {
        $orphanFiles++;
      } else {
        $resourceCountByFolder[$fid]++;
        $storageByFolder[$fid] += $size;
      }

      $type = $this->resolveFileType($ext, (string)($resource['mime_type'] ?? ''));
      $fileTypes[$type]['count']++;
      $fileTypes[$type]['bytes'] += $size;

      $key = strtolower(trim($name));
      if ($key !== '') {
        $duplicateMap[$key] = ($duplicateMap[$key] ?? 0) + 1;
      }

      $largestFiles[] = [
        'id' => $rid,
        'title' => (string)($resource['title'] ?? 'Untitled'),
        'file_name' => $name,
        'folder_name' => (string)($resource['folder_name'] ?? 'Unfiled'),
        'size' => $size,
      ];
    }

    usort($largestFiles, fn($a, $b) => $b['size'] <=> $a['size']);
    $largestFiles = array_slice($largestFiles, 0, 10);

    $emptyFolderIds = [];
    foreach ($resourceCountByFolder as $folderId => $count) {
      if ($count === 0) {
        $emptyFolderIds[] = $folderId;
      }
    }

    $duplicateFiles = 0;
    foreach ($duplicateMap as $dupCount) {
      if ($dupCount > 1) {
        $duplicateFiles += ($dupCount - 1);
      }
    }

    $subfolderCount = 0;
    foreach ($folders as $folder) {
      if ((int)($folder['parent_id'] ?? 0) > 0) {
        $subfolderCount++;
      }
    }

    $assignedFiles = count($resourceAssignments);
    $assignedFolders = count($folderAssignments);

    $permissionOverview = [
      'assigned_files' => $assignedFiles,
      'assigned_folders' => $assignedFolders,
      'unassigned_files' => max(0, count($resources) - $assignedFiles),
      'restricted_files' => max(0, count($resources) - $assignedFiles),
      'public_files' => $assignedFiles,
      'shared_resources' => $assignedFiles + $assignedFolders,
      'admin_only_resources' => max(0, count($resources) - $assignedFiles),
    ];

    $folderUserMap = [];
    foreach ($folders as $folder) {
      $id = (int)$folder['id'];
      $folderUserMap[$id] = [];
      if (isset($folderAssignments[$id])) {
        foreach ($folderAssignments[$id] as $sid => $val) {
          $folderUserMap[$id][$sid] = true;
        }
      }
    }
    foreach ($resources as $resource) {
      $rid = (int)$resource['id'];
      $fid = (int)($resource['folder_id'] ?? 0);
      if ($fid > 0 && isset($folderUserMap[$fid]) && isset($resourceAssignments[$rid])) {
        foreach ($resourceAssignments[$rid] as $sid => $val) {
          $folderUserMap[$fid][$sid] = true;
        }
      }
    }

    $tree = [];
    $folderSecurityCards = [];
    $buildNode = function(int $folderId, int $depth = 0) use (&$buildNode, &$folderById, &$children, &$resourceCountByFolder, &$folderUserMap, &$storageByFolder, &$folderSecurityCards): array {
      $folder = $folderById[$folderId] ?? ['name' => 'Unknown', 'created_at' => date('Y-m-d H:i:s')];
      
      $nodes = [];
      $accumulatedFiles = $resourceCountByFolder[$folderId] ?? 0;
      $accumulatedStorage = $storageByFolder[$folderId] ?? 0;
      
      $directUserIds = isset($folderUserMap[$folderId]) ? array_keys($folderUserMap[$folderId]) : [];
      $accumulatedUserIds = $directUserIds;

      foreach ($children[$folderId] ?? [] as $childId) {
        $childRes = $buildNode((int)$childId, $depth + 1);
        $nodes[] = $childRes['node'];
        $accumulatedFiles += $childRes['accumulated_files'];
        $accumulatedStorage += $childRes['accumulated_storage'];
        $accumulatedUserIds = array_merge($accumulatedUserIds, $childRes['accumulated_user_ids']);
      }
      
      $accumulatedUserIds = array_values(array_unique($accumulatedUserIds));
      $totalUserCount = count($accumulatedUserIds);
      
      $status = $totalUserCount === 0 ? 'Private' : ($totalUserCount > 4 ? 'Shared' : 'Restricted');
      $risk = $totalUserCount === 0 && $accumulatedFiles > 0 ? 'warning' : ($accumulatedFiles > 25 ? 'critical' : 'info');
      $lastUpdated = strtotime((string)($folder['created_at'] ?? 'now'));

      $folderSecurityCards[] = [
        'id' => $folderId,
        'name' => (string)($folder['name'] ?? 'Untitled'),
        'file_count' => $accumulatedFiles,
        'user_count' => $totalUserCount,
        'access' => $status,
        'risk' => $risk,
        'last_accessed' => $this->agoLabel($lastUpdated),
        'storage' => $accumulatedStorage,
      ];

      return [
        'accumulated_files' => $accumulatedFiles,
        'accumulated_storage' => $accumulatedStorage,
        'accumulated_user_ids' => $accumulatedUserIds,
        'node' => [
          'id' => $folderId,
          'name' => (string)($folder['name'] ?? 'Untitled'),
          'file_count' => $accumulatedFiles,
          'user_count' => $totalUserCount,
          'status' => strtolower($status),
          'last_updated' => $this->agoLabel($lastUpdated),
          'depth' => $depth,
          'children' => $nodes,
        ]
      ];
    };

    foreach ($rootFolders as $rootId) {
      $res = $buildNode((int)$rootId);
      $tree[] = $res['node'];
    }

    usort($folderSecurityCards, fn($a, $b) => $b['storage'] <=> $a['storage']);
    $folderSecurityCards = array_slice($folderSecurityCards, 0, 6);

    $staffById = [];
    foreach ($staff as $s) {
      $staffById[(int)$s['id']] = $s;
    }

    $activityByDay = [];
    $accessByDay = [];
    $userActivity = [];
    $failedLogins = 0;
    $filesAccessedToday = 0;
    $foldersAccessedToday = 0;
    $today = date('Y-m-d');

    foreach ($logs as $log) {
      $event = strtolower((string)($log['event'] ?? ''));
      $createdAt = strtotime((string)($log['created_at'] ?? 'now'));
      $day = date('Y-m-d', $createdAt);
      $activityByDay[$day] = ($activityByDay[$day] ?? 0) + 1;

      $sid = (int)($log['staff_id'] ?? 0);
      if ($sid > 0 && isset($staffById[$sid])) {
        $userActivity[$sid] = ($userActivity[$sid] ?? 0) + 1;
      }

      if (str_contains($event, 'login_failed') || str_contains($event, 'failed')) {
        $failedLogins++;
      }
      if (str_contains($event, 'view') || str_contains($event, 'open') || str_contains($event, 'assign')) {
        $accessByDay[$day] = ($accessByDay[$day] ?? 0) + 1;
      }
      if ($day === $today && str_contains($event, 'resource')) {
        $filesAccessedToday++;
      }
      if ($day === $today && str_contains($event, 'folder')) {
        $foldersAccessedToday++;
      }
    }

    arsort($userActivity);
    $topUserId = (int)(array_key_first($userActivity) ?? 0);
    $mostActiveUser = $topUserId > 0 && isset($staffById[$topUserId]) ? $staffById[$topUserId]['name'] : 'No activity yet';

    $lastLoginUsers = [];
    foreach ($logs as $log) {
      $event = strtolower((string)($log['event'] ?? ''));
      $sid = (int)($log['staff_id'] ?? 0);
      if ($sid > 0 && isset($staffById[$sid]) && (str_contains($event, 'login') || str_contains($event, 'auth'))) {
        $lastLoginUsers[$sid] = [
          'name' => $staffById[$sid]['name'],
          'at' => (string)$log['created_at'],
        ];
      }
      if (count($lastLoginUsers) >= 5) break;
    }

    $events = [];
    foreach (array_slice($logs, 0, 60) as $log) {
      $eventName = (string)($log['event'] ?? 'event');
      $severity = $this->resolveSeverity($eventName);
      $events[] = [
        'event' => $eventName,
        'meta' => (string)($log['meta'] ?? ''),
        'ip' => (string)($log['ip'] ?? ''),
        'user' => (int)($log['staff_id'] ?? 0),
        'created_at' => (string)($log['created_at'] ?? ''),
        'severity' => $severity,
      ];
    }

    $storageByFolderCards = [];
    foreach ($storageByFolder as $folderId => $bytes) {
      $storageByFolderCards[] = [
        'folder' => (string)($folderById[$folderId]['name'] ?? 'Unknown'),
        'bytes' => $bytes,
        'file_count' => $resourceCountByFolder[$folderId] ?? 0,
      ];
    }
    usort($storageByFolderCards, fn($a, $b) => $b['bytes'] <=> $a['bytes']);

    $permissionMatrix = [];
    foreach ($staff as $member) {
      $uid = (int)$member['id'];
      $filesOwned = 0;
      $foldersOwned = 0;
      foreach ($resourceAssignments as $rid => $userMap) {
        if (isset($userMap[$uid])) $filesOwned++;
      }
      foreach ($folderAssignments as $fid => $userMap) {
        if (isset($userMap[$uid])) $foldersOwned++;
      }
      $role = $foldersOwned > 5 ? 'Manager' : ($filesOwned > 10 ? 'Contributor' : 'Viewer');
      $access = $member['is_active'] ? 'Active' : 'Disabled';
      $permissionMatrix[] = [
        'id' => $uid,
        'name' => (string)$member['name'],
        'email' => (string)$member['email'],
        'files' => $filesOwned,
        'folders' => $foldersOwned,
        'role' => $role,
        'access' => $access,
        'read' => true,
        'upload' => $filesOwned > 0,
        'delete' => $filesOwned > 2,
        'manage' => $foldersOwned > 2,
        'admin' => $role === 'Manager',
      ];
    }

    $newUsers = 0;
    foreach ($staff as $member) {
      if (isset($member['id']) && (int)$member['id'] > max(1, count($staff) - 3)) {
        $newUsers++;
      }
    }

    return [
      'top_kpi' => [
        'total_files' => count($resources),
        'total_folders' => count($folders),
        'total_subfolders' => $subfolderCount,
        'assigned_files' => $assignedFiles,
        'assigned_folders' => $assignedFolders,
        'users' => count($staff),
        'storage_used_bytes' => array_sum(array_column($storageByFolderCards, 'bytes')),
      ],
      'folder_tree' => $tree,
      'file_types' => $fileTypes,
      'permission_overview' => $permissionOverview,
      'user_access' => [
        'most_active_user' => (string)$mostActiveUser,
        'last_login_users' => array_values($lastLoginUsers),
        'files_accessed_today' => $filesAccessedToday,
        'folders_accessed_today' => $foldersAccessedToday,
        'failed_login_attempts' => $failedLogins,
        'new_users' => $newUsers,
      ],
      'events' => $events,
      'folder_security_cards' => $folderSecurityCards,
      'permission_matrix' => $permissionMatrix,
      'storage' => [
        'by_folder' => array_slice($storageByFolderCards, 0, 8),
        'largest_files' => $largestFiles,
      ],
      'search_index' => $this->searchIndex($folders, $resources, $staff, $events),
      'timeline' => array_slice($events, 0, 25),
      'top_active_resources' => [
        'files' => array_slice($largestFiles, 0, 5),
        'folders' => array_slice($storageByFolderCards, 0, 5),
      ],
      'insights' => [
        'empty_folders' => count($emptyFolderIds),
        'orphan_files' => $orphanFiles,
        'duplicate_files' => $duplicateFiles,
      ],
      'trends' => [
        'activity_days' => array_slice($this->sortAssocByDate($activityByDay), -14, null, true),
        'access_days' => array_slice($this->sortAssocByDate($accessByDay), -14, null, true),
      ],
    ];
  }

  private function searchIndex(array $folders, array $resources, array $staff, array $events): array {
    $index = [];
    foreach ($folders as $folder) {
      $index[] = ['type' => 'folder', 'title' => (string)$folder['name'], 'meta' => 'Folder', 'url' => '/admin/folders'];
    }
    foreach ($resources as $resource) {
      $index[] = ['type' => 'file', 'title' => (string)$resource['title'], 'meta' => (string)($resource['folder_name'] ?? 'Unfiled'), 'url' => '/admin/files'];
    }
    foreach ($staff as $member) {
      $index[] = ['type' => 'user', 'title' => (string)$member['name'], 'meta' => (string)$member['email'], 'url' => '/admin/users'];
    }
    foreach (array_slice($events, 0, 30) as $event) {
      $index[] = ['type' => 'permission', 'title' => (string)$event['event'], 'meta' => (string)$event['severity'], 'url' => '/admin/audit'];
    }
    $index[] = ['type' => 'action', 'title' => 'Analytics', 'meta' => 'Module', 'url' => '/admin/analytics'];
    $index[] = ['type' => 'action', 'title' => 'Audit & Activity', 'meta' => 'Module', 'url' => '/admin/audit'];
    $index[] = ['type' => 'action', 'title' => 'Permissions', 'meta' => 'Module', 'url' => '/admin/permissions'];
    $index[] = ['type' => 'action', 'title' => 'Storage', 'meta' => 'Module', 'url' => '/admin/storage'];
    $index[] = ['type' => 'action', 'title' => 'Folder Management', 'meta' => 'Module', 'url' => '/admin/folders'];
    $index[] = ['type' => 'action', 'title' => 'Create Folder', 'meta' => 'Quick Action', 'url' => '/admin/folders'];
    $index[] = ['type' => 'action', 'title' => 'Upload File', 'meta' => 'Quick Action', 'url' => '/admin/files'];
    $index[] = ['type' => 'action', 'title' => 'Manage User', 'meta' => 'Quick Action', 'url' => '/admin/users'];
    return $index;
  }

  private function sortAssocByDate(array $series): array {
    ksort($series);
    return $series;
  }

  private function resolveSeverity(string $event): string {
    $e = strtolower($event);
    if (str_contains($e, 'failed') || str_contains($e, 'delete') || str_contains($e, 'revoke')) return 'critical';
    if (str_contains($e, 'permission') || str_contains($e, 'role') || str_contains($e, 'assign')) return 'warning';
    return 'info';
  }

  private function agoLabel(int $unix): string {
    $diff = max(0, time() - $unix);
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
  }

  private function resolveFileType(string $ext, string $mime): string {
    $documents = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];
    $images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $videos = ['mp4', 'webm', 'mov', 'avi', 'mkv'];
    $archives = ['zip', 'rar', '7z', 'tar', 'gz'];
    if ($ext === 'pdf' || str_contains($mime, 'pdf')) return 'PDFs';
    if (in_array($ext, $images, true) || str_starts_with($mime, 'image/')) return 'Images';
    if (in_array($ext, $videos, true) || str_starts_with($mime, 'video/')) return 'Videos';
    if (in_array($ext, $documents, true)) return 'Documents';
    if (in_array($ext, $archives, true)) return 'Archives';
    return 'Others';
  }

  private function resourceAssignmentsMap(): array {
    $rows = Database::conn()->query('SELECT resource_id, staff_id FROM resource_staff')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
      $rid = (int)$row['resource_id'];
      $sid = (int)$row['staff_id'];
      $map[$rid] ??= [];
      $map[$rid][$sid] = true;
    }
    return $map;
  }

  private function folderSeries(array $folders, array $resources): array {
    $counts = [];
    foreach ($folders as $f) {
      $counts[(int)$f['id']] = ['name' => $f['name'], 'count' => 0];
    }
    foreach ($resources as $r) {
      $fid = (int)($r['folder_id'] ?? 0);
      if (isset($counts[$fid])) $counts[$fid]['count']++;
    }
    return array_values($counts);
  }

  public function files(): void {
    Auth::guardAdmin();
    $this->view('admin/files', [
      'resources' => Resource::all(),
      'folders' => Folder::all(),
      'staff' => User::allStaff(),
      'resourceAssignments' => $this->resourceAssignmentsMap(),
      'activeNav' => 'files',
    ]);
  }

  public function folders(): void {
    Auth::guardAdmin();
    $data = $this->dashboardBaseData();
    $this->view('admin/folders', [
      'folders' => Folder::all(),
      'staff' => User::allStaff(),
      'folderAssignments' => $this->folderAssignmentsMap(),
      'dashboardData' => $data['dashboardData'],
      'activeNav' => 'folders'
    ]);
  }

  public function folderView(): void {
    Auth::guardAdmin();
    $folderId = (int)($_GET['id'] ?? 0);
    $folder = Database::conn()->prepare('SELECT * FROM folders WHERE id=? LIMIT 1');
    $folder->execute([$folderId]);
    $folderRow = $folder->fetch();
    if (!$folderRow) {
      $this->redirect('/admin/folders');
    }

    $folderStaffStmt = Database::conn()->prepare('SELECT staff_id FROM folder_staff WHERE folder_id = ?');
    $folderStaffStmt->execute([$folderId]);
    $folderStaffList = array_map('intval', $folderStaffStmt->fetchAll(\PDO::FETCH_COLUMN));

    $this->view('admin/folder_view', [
      'folder' => $folderRow,
      'subfolders' => Folder::childrenOf($folderId),
      'resources' => Resource::byFolder($folderId),
      'folders' => Folder::all(),
      'staff' => User::allStaff(),
      'folderStaffList' => $folderStaffList,
      'resourceAssignments' => $this->resourceAssignmentsMapByFolder($folderId),
      'activeNav' => 'folders',
    ]);
  }

  private function folderAssignmentsMap(): array {
    $rows = Database::conn()->query('SELECT folder_id, staff_id FROM folder_staff')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
      $fid = (int)$row['folder_id'];
      $sid = (int)$row['staff_id'];
      $map[$fid] ??= [];
      $map[$fid][$sid] = true;
    }
    return $map;
  }

  private function resourceAssignmentsMapByFolder(int $folderId): array {
    $stmt = Database::conn()->prepare('SELECT rs.resource_id, rs.staff_id
      FROM resource_staff rs
      INNER JOIN resources r ON r.id = rs.resource_id
      WHERE r.folder_id = ?');
    $stmt->execute([$folderId]);
    $rows = $stmt->fetchAll();
    $map = [];
    foreach ($rows as $row) {
      $rid = (int)$row['resource_id'];
      $sid = (int)$row['staff_id'];
      $map[$rid] ??= [];
      $map[$rid][$sid] = true;
    }
    return $map;
  }

  public function createFolder(): void {
    Auth::guardAdmin();
    try {
      $name = trim((string)($_POST['name'] ?? ''));
      $parentId = (int)($_POST['parent_id'] ?? 0);
      if ($name === '') $this->json(['ok'=>false,'message'=>'Folder name is required'],422);
      Folder::create($name, $parentId > 0 ? $parentId : null);
      Audit::log($_SESSION['admin_id'],null,'folder_created',$name);
      $this->json(['ok'=>true,'message'=>'Folder created successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to create folder'],500); }
  }

  public function updateFolder(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['folder_id'] ?? 0);
      $name = trim((string)($_POST['name'] ?? ''));
      $parentId = (int)($_POST['parent_id'] ?? 0);
      if ($id <= 0 || $name === '') $this->json(['ok'=>false,'message'=>'Folder and name are required'],422);
      if ($parentId === $id) $this->json(['ok'=>false,'message'=>'Folder cannot be its own parent'],422);
      Folder::update($id, $name, $parentId > 0 ? $parentId : null);
      Audit::log($_SESSION['admin_id'],null,'folder_updated',(string)$id);
      $this->json(['ok'=>true,'message'=>'Folder updated successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to update folder'],500); }
  }

  public function deleteFolder(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['folder_id'] ?? 0);
      if ($id <= 0) $this->json(['ok'=>false,'message'=>'Folder is required'],422);
      Folder::delete($id);
      Audit::log($_SESSION['admin_id'],null,'folder_deleted',(string)$id);
      $this->json(['ok'=>true,'message'=>'Folder deleted successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to delete folder'],500); }
  }

  public function upload(): void {
    Auth::guardAdmin();
    try {
      if (empty($_FILES['files'])) throw new \Exception('No files');
      $allowed = array_map('trim', explode(',', Setting::get('allowed_file_types', 'pdf,jpg,jpeg,png,mp4,mov,webm,ppt,pptx,doc,docx,xls,xlsx,txt,zip')));
      $folderId = (int)($_POST['folder_id'] ?? 0);
      $maxUploadMb = (int)Setting::get('max_upload_mb', '200');
      $maxBytes = $maxUploadMb * 1024 * 1024;
      $savedCount = 0;
      $errors = [];

      foreach ($_FILES['files']['name'] as $i => $name) {
        $errCode = (int)($_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($errCode !== UPLOAD_ERR_OK) {
          $errors[] = match ($errCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "$name is too large for server limits.",
            UPLOAD_ERR_PARTIAL => "$name was only partially uploaded.",
            UPLOAD_ERR_NO_FILE => "No file selected in one slot.",
            default => "Upload error for $name.",
          };
          continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
          $errors[] = "$name has unsupported type ($ext).";
          continue;
        }
        if ((int)$_FILES['files']['size'][$i] > $maxBytes) {
          $errors[] = "$name exceeds max upload {$maxUploadMb}MB.";
          continue;
        }
        $tmp = $_FILES['files']['tmp_name'][$i];
        $stored = bin2hex(random_bytes(18)) . '.' . $ext;
        $target = (require __DIR__ . '/../../config/app.php')['storage_path'] . '/' . $stored;
        if (!move_uploaded_file($tmp, $target)) {
          $errors[] = "Could not move uploaded file $name.";
          continue;
        }

        $mime = $_FILES['files']['type'][$i] ?? '';
        if ($mime === '' || $mime === 'application/octet-stream') {
          $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream'
          };
        }

        Resource::create([
          'folder_id'=>$folderId ?: null,
          'title'=>pathinfo($name, PATHINFO_FILENAME),
          'file_name'=>$name,
          'stored_name'=>$stored,
          'mime_type'=>$mime,
          'file_size'=>$_FILES['files']['size'][$i],
        ]);
        $savedCount++;
      }
      Audit::log($_SESSION['admin_id'],null,'resource_uploaded');
      if ($savedCount === 0) {
        $this->json(['ok'=>false,'message'=>count($errors) ? implode(' ', $errors) : 'No file uploaded'],422);
      }
      $msg = "Uploaded {$savedCount} file(s) successfully.";
      if (count($errors)) $msg .= ' Some files skipped: ' . implode(' ', $errors);
      $this->json(['ok'=>true,'message'=>$msg]);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Upload failed'],500); }
  }

  public function updateResource(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['resource_id'] ?? 0);
      $title = trim((string)($_POST['title'] ?? ''));
      $folderId = (int)($_POST['folder_id'] ?? 0);
      if ($id <= 0 || $title === '') $this->json(['ok'=>false,'message'=>'File and title are required'],422);
      Resource::updateMeta($id, $title, $folderId > 0 ? $folderId : null);
      Audit::log($_SESSION['admin_id'],null,'resource_updated',(string)$id);
      $this->json(['ok'=>true,'message'=>'File updated successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to update file'],500); }
  }

  public function deleteResource(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['resource_id'] ?? 0);
      if ($id <= 0) $this->json(['ok'=>false,'message'=>'File is required'],422);
      $resource = Resource::find($id);
      if ($resource && !empty($resource['stored_name'])) {
        $file = (require __DIR__ . '/../../config/app.php')['storage_path'] . '/' . $resource['stored_name'];
        if (is_file($file)) @unlink($file);
      }
      Resource::delete($id);
      Audit::log($_SESSION['admin_id'],null,'resource_deleted',(string)$id);
      $this->json(['ok'=>true,'message'=>'File deleted successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to delete file'],500); }
  }

  public function copyResource(): void {
    Auth::guardAdmin();
    try {
      $folderId = (int)($_POST['folder_id'] ?? 0);
      $ids = $_POST['resource_ids'] ?? [];
      if (empty($ids) && isset($_POST['resource_id'])) {
        $ids = [$_POST['resource_id']];
      }
      
      if (empty($ids)) $this->json(['ok'=>false,'message'=>'No files selected'],422);
      
      $storagePath = (require __DIR__ . '/../../config/app.php')['storage_path'];
      $copiedCount = 0;
      
      foreach ($ids as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;
        
        $resource = Resource::find($id);
        if (!$resource) continue;
        
        $oldFile = $storagePath . '/' . $resource['stored_name'];
        if (!is_file($oldFile)) continue;
        
        $ext = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION));
        $stored = bin2hex(random_bytes(18)) . '.' . $ext;
        $newFile = $storagePath . '/' . $stored;
        
        if (copy($oldFile, $newFile)) {
          $newTitle = $resource['title'] . ' - Copy';
          $newId = Resource::create([
            'folder_id' => $folderId > 0 ? $folderId : null,
            'title' => $newTitle,
            'file_name' => $resource['file_name'],
            'stored_name' => $stored,
            'mime_type' => $resource['mime_type'],
            'file_size' => $resource['file_size'],
          ]);
          Audit::log($_SESSION['admin_id'],null,'resource_copied',(string)$newId);
          $copiedCount++;
        }
      }
      
      if ($copiedCount === 0) {
        $this->json(['ok'=>false,'message'=>'No files were successfully copied'],500);
      }
      
      $this->json(['ok'=>true,'message'=>"Successfully copied {$copiedCount} file(s)."]);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to copy file(s)'],500); }
  }

  public function moveResource(): void {
    Auth::guardAdmin();
    try {
      $folderId = (int)($_POST['folder_id'] ?? 0);
      $ids = $_POST['resource_ids'] ?? [];
      if (empty($ids) && isset($_POST['resource_id'])) {
        $ids = [$_POST['resource_id']];
      }
      
      if (empty($ids)) $this->json(['ok'=>false,'message'=>'No files selected'],422);
      
      $movedCount = 0;
      foreach ($ids as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;
        
        $resource = Resource::find($id);
        if (!$resource) continue;
        
        Resource::updateMeta($id, $resource['title'], $folderId > 0 ? $folderId : null);
        Audit::log($_SESSION['admin_id'],null,'resource_moved',(string)$id);
        $movedCount++;
      }
      
      if ($movedCount === 0) {
        $this->json(['ok'=>false,'message'=>'No files were successfully moved'],500);
      }
      
      $this->json(['ok'=>true,'message'=>"Successfully moved {$movedCount} file(s)."]);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to move file(s)'],500); }
  }

  public function reorderResources(): void {
    Auth::guardAdmin();
    try {
      $ids = $_POST['ids'] ?? [];
      if (empty($ids) || !is_array($ids)) {
        $this->json(['ok'=>false,'message'=>'Invalid resource order payload'],422);
      }
      
      $db = Database::conn();
      $db->beginTransaction();
      $stmt = $db->prepare('UPDATE resources SET sort_order = ? WHERE id = ?');
      foreach ($ids as $index => $id) {
        $stmt->execute([(int)$index, (int)$id]);
      }
      $db->commit();
      
      Audit::log($_SESSION['admin_id'],null,'resources_reordered');
      $this->json(['ok'=>true,'message'=>'Resources reordered successfully']);
    } catch (\Throwable $e) {
      if (Database::conn()->inTransaction()) {
        Database::conn()->rollBack();
      }
      $this->json(['ok'=>false,'message'=>'Failed to reorder files'],500);
    }
  }

  public function saveAssignment(): void {
    Auth::guardAdmin();
    try {
      $staffIds = array_filter((array)($_POST['staff_ids'] ?? []), static fn($v) => (string)$v !== '');
      $staffIds = array_values(array_unique(array_map('intval', $staffIds)));
      $folderId = (int)($_POST['folder_id'] ?? 0);
      $resourceId = (int)($_POST['resource_id'] ?? 0);
      if ($folderId) {
        Database::conn()->prepare('DELETE FROM folder_staff WHERE folder_id=?')->execute([$folderId]);
      }
      if ($resourceId) {
        Database::conn()->prepare('DELETE FROM resource_staff WHERE resource_id=?')->execute([$resourceId]);
      }
      foreach ($staffIds as $sid) {
        if ($folderId) Database::conn()->prepare('INSERT INTO folder_staff (folder_id,staff_id) VALUES (?,?)')->execute([$folderId,(int)$sid]);
        if ($resourceId) Database::conn()->prepare('INSERT INTO resource_staff (resource_id,staff_id) VALUES (?,?)')->execute([$resourceId,(int)$sid]);
      }
      Audit::log($_SESSION['admin_id'],null,'assignment_saved');
      $this->json(['ok'=>true,'message'=>'Assignment saved successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Assignment failed'],500); }
  }

  public function settings(): void {
    Auth::guardAdmin();
    $start24 = Setting::get('global_access_start', '01:00');
    $end24 = Setting::get('global_access_end', '17:00');
    $days = [
      'mon' => 'Monday',
      'tue' => 'Tuesday',
      'wed' => 'Wednesday',
      'thu' => 'Thursday',
      'fri' => 'Friday',
      'sat' => 'Saturday',
      'sun' => 'Sunday',
    ];
    $accessSchedule = [];
    foreach ($days as $key => $label) {
      $dayStart = Setting::get("access_{$key}_start", '');
      $dayEnd = Setting::get("access_{$key}_end", '');
      $dayStatus = Setting::get("access_{$key}_status", '1');
      if ($dayStart === '' || $dayEnd === '') {
        $dayStart = $start24;
        $dayEnd = $end24;
      }
      $accessSchedule[] = [
        'key' => $key,
        'label' => $label,
        'start' => $dayStart !== '' ? date('h:i A', strtotime($dayStart)) : '',
        'end' => $dayEnd !== '' ? date('h:i A', strtotime($dayEnd)) : '',
        'status' => $dayStatus,
      ];
    }
    $this->view('admin/settings', ['settings' => [
      'deployment_mode' => Setting::get('deployment_mode', 'local'),
      'global_access_start' => date('h:i A', strtotime($start24)),
      'global_access_end' => date('h:i A', strtotime($end24)),
      'block_mobile' => Setting::get('block_mobile', '1'),
      'block_laptop' => Setting::get('block_laptop', '0'),
      'allow_smartboard' => Setting::get('allow_smartboard', '0'),
      'smartboard_min_width' => Setting::get('smartboard_min_width', '1600'),
      'smartboard_min_height' => Setting::get('smartboard_min_height', '900'),
      'debug_mode' => Setting::get('debug_mode', '0'),
      'download_restriction' => Setting::get('download_restriction', '1'),
      'allowed_file_types' => Setting::get('allowed_file_types', 'pdf,jpg,jpeg,png,mp4,mov,webm,ppt,pptx,doc,docx,xls,xlsx,txt,zip'),
      'max_upload_mb' => Setting::get('max_upload_mb', '200'),
      'screenshot_protection' => Setting::get('screenshot_protection', '1'),
    ], 'accessSchedule' => $accessSchedule, 'activeNav' => 'settings']);
  }

  public function saveSettings(): void {
    Auth::guardAdmin();
    try {
      foreach ($_POST as $k => $v) {
        $val = is_array($v) ? implode(',', $v) : (string)$v;
        if ($k === 'global_access_start' || $k === 'global_access_end' || preg_match('/^access_(mon|tue|wed|thu|fri|sat|sun)_(start|end)$/', $k)) {
          $val = trim($val);
          if ($val !== '') {
            $ts = strtotime($val);
            if ($ts === false) {
              $this->json(['ok'=>false,'message'=>'Invalid time format. Use hh:mm AM/PM'],422);
            }
            $val = date('H:i', $ts);
          }
        }
        Setting::set($k, $val);
      }
      Audit::log($_SESSION['admin_id'],null,'settings_updated');
      $this->json(['ok'=>true,'message'=>'Settings saved']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Save failed'],500); }
  }

  public function staff(): void {
    Auth::guardAdmin();
    $this->view('admin/staff', ['staff' => User::allStaff(), 'activeNav' => 'users']);
  }

  public function createStaff(): void {
    Auth::guardAdmin();
    try {
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = (string)($_POST['password'] ?? '');
      if ($name === '' || $email === '' || $password === '') $this->json(['ok'=>false,'message'=>'Name, email, and password are required'],422);
      User::createStaff($name, $email, $password);
      Audit::log((int)$_SESSION['admin_id'], null, 'staff_created', $email);
      $this->json(['ok'=>true,'message'=>'Staff created successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to create staff'],500); }
  }

  public function updateStaff(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['staff_id'] ?? 0);
      $name = trim((string)($_POST['name'] ?? ''));
      $email = trim((string)($_POST['email'] ?? ''));
      if ($id <= 0 || $name === '' || $email === '') $this->json(['ok'=>false,'message'=>'Staff, name, and email are required'],422);
      User::updateStaff($id, $name, $email);
      Audit::log((int)$_SESSION['admin_id'], null, 'staff_updated', (string)$id);
      $this->json(['ok'=>true,'message'=>'Staff updated successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to update staff'],500); }
  }

  public function deleteStaff(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['staff_id'] ?? 0);
      if ($id <= 0) $this->json(['ok'=>false,'message'=>'Staff is required'],422);
      User::deleteStaff($id);
      Audit::log((int)$_SESSION['admin_id'], null, 'staff_deleted', (string)$id);
      $this->json(['ok'=>true,'message'=>'Staff deleted successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to delete staff'],500); }
  }

  public function toggleStaff(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['staff_id'] ?? 0);
      $active = (int)($_POST['is_active'] ?? 0);
      User::setStaffStatus($id, $active);
      Audit::log((int)$_SESSION['admin_id'], null, 'staff_status_changed', "staff_id={$id};active={$active}");
      $this->json(['ok'=>true,'message'=>'Staff status updated']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to update status'],500); }
  }

  public function resetStaffPassword(): void {
    Auth::guardAdmin();
    try {
      $id = (int)($_POST['staff_id'] ?? 0);
      $password = (string)($_POST['new_password'] ?? '');
      if ($id <= 0 || $password === '') $this->json(['ok'=>false,'message'=>'Staff and password are required'],422);
      User::resetStaffPassword($id, $password);
      Audit::log((int)$_SESSION['admin_id'], null, 'staff_password_reset', (string)$id);
      $this->json(['ok'=>true,'message'=>'Staff password reset successfully']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Failed to reset password'],500); }
  }

  private function detectType(array $resource): string {
    $ext = strtolower(pathinfo((string)$resource['file_name'], PATHINFO_EXTENSION));
    return match ($ext) {
      'pdf' => 'application/pdf',
      'mp4' => 'video/mp4',
      'webm' => 'video/webm',
      'mov' => 'video/quicktime',
      'png' => 'image/png',
      'jpg', 'jpeg' => 'image/jpeg',
      default => (string)($resource['mime_type'] ?? 'application/octet-stream')
    };
  }

  public function viewResource(): void {
    Auth::guardAdmin();
    $id = (int)($_GET['id'] ?? 0);
    $resource = Resource::find($id);
    if (!$resource) {
      $this->redirect('/admin/folders');
    }
    $this->view('admin/viewer', [
      'resource' => $resource,
      'resourceType' => $this->detectType($resource),
    ]);
  }

  public function streamResource(): void {
    Auth::guardAdmin();
    $id = (int)($_GET['id'] ?? 0);
    $r = Resource::find($id);
    if (!$r) {
      http_response_code(404);
      exit;
    }

    $file = (require __DIR__ . '/../../config/app.php')['storage_path'] . '/' . $r['stored_name'];
    if (!file_exists($file)) {
      http_response_code(404);
      exit;
    }

    Audit::log((int)$_SESSION['admin_id'], null, 'resource_viewed', (string)$id);
    header('Content-Type: ' . $this->detectType($r));
    header('Content-Length: ' . filesize($file));
    $disposition = (isset($_GET['download']) && $_GET['download'] === '1') ? 'attachment' : 'inline';
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($r['file_name']) . '"');
    readfile($file);
    exit;
  }

  public function profile(): void {
    Auth::guardAdmin();
    $this->view('admin/profile', ['activeNav' => 'profile']);
  }

  public function saveProfile(): void {
    Auth::guardAdmin();
    try {
      $name = trim($_POST['name'] ?? '');
      $password = trim($_POST['password'] ?? '');
      $id = (int)$_SESSION['admin_id'];
      if ($name !== '') Database::conn()->prepare('UPDATE admins SET name=? WHERE id=?')->execute([$name, $id]);
      if ($password !== '') Database::conn()->prepare('UPDATE admins SET password=? WHERE id=?')->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
      $_SESSION['admin_name'] = $name !== '' ? $name : $_SESSION['admin_name'];
      Audit::log($id, null, 'admin_profile_updated');
      $this->json(['ok'=>true,'message'=>'Profile updated']);
    } catch (\Throwable $e) {
      $this->json(['ok'=>false,'message'=>'Profile update failed'],500);
    }
  }
}
