<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Audit;
use App\Models\Favorite;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Setting;
use App\Models\User;

class StaffController extends Controller {
  private function isMobileUserAgent(): bool {
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    return $ua !== '' && preg_match('/Android|iPhone|iPad|iPod|Mobile|Tablet/i', $ua) === 1;
  }

  private function isSmartboardRequest(): bool {
    if (Setting::get('allow_smartboard', '0') !== '1') return false;
    return (string)($_COOKIE['psnf_smartboard'] ?? '') === '1';
  }

  private function resolveAccessWindow(): array {
    $dayKeyMap = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'];
    $dayLabels = [
      'mon' => 'Monday',
      'tue' => 'Tuesday',
      'wed' => 'Wednesday',
      'thu' => 'Thursday',
      'fri' => 'Friday',
      'sat' => 'Saturday',
      'sun' => 'Sunday',
    ];
    $dayKey = $dayKeyMap[(int)date('N')] ?? 'mon';
    $status = Setting::get("access_{$dayKey}_status", '1');
    $start = Setting::get("access_{$dayKey}_start", '');
    $end = Setting::get("access_{$dayKey}_end", '');
    if ($start === '' || $end === '') {
      $start = Setting::get('global_access_start', '01:00');
      $end = Setting::get('global_access_end', '17:00');
    }
    return [
      'status' => $status,
      'start' => $start,
      'end' => $end,
      'dayLabel' => $dayLabels[$dayKey] ?? 'Today',
    ];
  }

  private function enforcePortalPolicy(): bool {
    Auth::guardStaff();
    $window = $this->resolveAccessWindow();
    
    if ($window['status'] === '0') {
      $this->view('staff/locked', [
        'message' => "Access is disabled on {$window['dayLabel']} by administrator policy.",
      ]);
      return false;
    }

    $now = date('H:i');
    if (!($now >= $window['start'] && $now <= $window['end'])) {
      $this->view('staff/locked', [
        'start' => date('h:i A', strtotime($window['start'])),
        'end' => date('h:i A', strtotime($window['end'])),
        'dayLabel' => $window['dayLabel'],
      ]);
      return false;
    }

    $isSmartboard = $this->isSmartboardRequest();

    if (!$isSmartboard && Setting::get('block_mobile', '1') === '1' && $this->isMobileUserAgent()) {
      http_response_code(403);
      echo 'Mobile access blocked by admin policy.';
      exit;
    }

    if (!$isSmartboard && Setting::get('block_laptop', '0') === '1' && !$this->isMobileUserAgent()) {
      http_response_code(403);
      echo 'Laptop/desktop access blocked by admin policy.';
      exit;
    }
    return true;
  }

  public function dashboard(): void {
    if (!$this->enforcePortalPolicy()) return;

    $sid = (int)$_SESSION['staff_id'];
    $folders = Folder::assignedToStaff($sid);
    $resources = Resource::assignedToStaff($sid);
    $favoriteFolderIds = Favorite::folderIdsForStaff($sid);
    $favoriteResourceIds = Favorite::resourceIdsForStaff($sid);

    $favoriteFolders = array_values(array_filter($folders, static function($f) use ($favoriteFolderIds) {
      return isset($favoriteFolderIds[(int)$f['id']]);
    }));
    $favoriteResources = array_values(array_filter($resources, static function($r) use ($favoriteResourceIds) {
      return isset($favoriteResourceIds[(int)$r['id']]);
    }));

    $window = $this->resolveAccessWindow();
    $this->view('staff/dashboard', [
      'folders' => $folders,
      'resources' => $resources,
      'favoriteFolders' => $favoriteFolders,
      'favoriteResources' => $favoriteResources,
      'favoriteFolderIds' => $favoriteFolderIds,
      'favoriteResourceIds' => $favoriteResourceIds,
      'recentActivity' => Audit::recentByStaff($sid, 20),
      'assignmentCounts' => [
        'folders' => count($folders),
        'files' => count($resources),
        'favorites' => count($favoriteFolderIds) + count($favoriteResourceIds),
      ],
      'accessWindow' => [
        'start' => date('h:i A', strtotime($window['start'])),
        'end' => date('h:i A', strtotime($window['end'])),
        'label' => $window['dayLabel'],
      ],
    ]);
  }

  public function folder(): void {
    if (!$this->enforcePortalPolicy()) return;
    $sid = (int)$_SESSION['staff_id'];
    $folderId = (int)($_GET['id'] ?? 0);

    $allowedFolders = Folder::assignedToStaff($sid);
    $selected = null;
    foreach ($allowedFolders as $f) {
      if ((int)$f['id'] === $folderId) {
        $selected = $f;
        break;
      }
    }
    if (!$selected) {
      http_response_code(403);
      echo 'Folder access denied';
      return;
    }

    $favoriteFolderIds = Favorite::folderIdsForStaff($sid);
    $favoriteResourceIds = Favorite::resourceIdsForStaff($sid);

    $this->view('staff/folder', [
      'folder' => $selected,
      'resources' => Resource::assignedToStaffByFolder($sid, $folderId),
      'favoriteFolder' => isset($favoriteFolderIds[$folderId]),
      'favoriteResourceIds' => $favoriteResourceIds,
    ]);
  }

  public function profile(): void {
    if (!$this->enforcePortalPolicy()) return;
    $sid = (int)$_SESSION['staff_id'];
    $staff = User::staffById($sid);
    if (!$staff) {
      http_response_code(404);
      echo 'Profile not found';
      return;
    }
    $this->view('staff/profile', [
      'staff' => $staff,
    ]);
  }

  public function saveProfile(): void {
    Auth::guardStaff();
    try {
      $sid = (int)$_SESSION['staff_id'];
      $name = trim((string)($_POST['name'] ?? ''));
      $password = trim((string)($_POST['password'] ?? ''));
      if ($name === '') $this->json(['ok'=>false,'message'=>'Name is required'],422);
      User::updateStaffProfile($sid, $name, $password);
      $_SESSION['staff_name'] = $name;
      Audit::log(null, $sid, 'staff_profile_updated');
      $this->json(['ok'=>true,'message'=>'Profile updated']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Profile update failed'],500); }
  }

  public function toggleFavorite(): void {
    Auth::guardStaff();
    try {
      $sid = (int)$_SESSION['staff_id'];
      $type = trim((string)($_POST['type'] ?? ''));
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0 || ($type !== 'folder' && $type !== 'resource')) {
        $this->json(['ok'=>false,'message'=>'Invalid favorite'],422);
      }

      if ($type === 'folder') {
        if (!Folder::canStaffAccess($sid, $id)) $this->json(['ok'=>false,'message'=>'Folder access denied'],403);
        $active = Favorite::toggleFolder($sid, $id);
      } else {
        if (!Resource::canStaffAccess($sid, $id)) $this->json(['ok'=>false,'message'=>'File access denied'],403);
        $active = Favorite::toggleResource($sid, $id);
      }

      Audit::log(null, $sid, $active ? 'favorite_added' : 'favorite_removed', $type . ':' . $id);
      $this->json(['ok'=>true,'active'=>$active,'message'=> $active ? 'Added to favorites' : 'Removed from favorites']);
    } catch (\Throwable $e) { $this->json(['ok'=>false,'message'=>'Favorite update failed'],500); }
  }
}
