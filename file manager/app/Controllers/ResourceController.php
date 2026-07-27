<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Resource;
use App\Models\Audit;
use App\Models\Setting;

class ResourceController extends Controller {
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

  public function viewer(): void {
    if (!$this->enforcePortalPolicy()) return;
    $id=(int)($_GET['id'] ?? 0);
    if (!Resource::canStaffAccess((int)$_SESSION['staff_id'], $id)) { http_response_code(403); echo 'Access denied'; return; }
    $resource = Resource::find($id);
    $this->view('staff/viewer', [
      'resource'=>$resource,
      'resourceType'=>$this->detectType($resource),
      'downloadRestricted'=>Setting::get('download_restriction', '1') === '1'
    ]);
  }

  public function stream(): void {
    if (!$this->enforcePortalPolicy()) return;
    $id=(int)($_GET['id'] ?? 0);
    if (!Resource::canStaffAccess((int)$_SESSION['staff_id'], $id)) {
      Audit::log(null,(int)$_SESSION['staff_id'],'resource_access_denied',(string)$id);
      http_response_code(403); exit;
    }
    $r=Resource::find($id);
    if (!$r) { http_response_code(404); exit; }

    $file = (require __DIR__ . '/../../config/app.php')['storage_path'] . '/' . $r['stored_name'];
    if (!file_exists($file)) { http_response_code(404); exit; }

    Audit::log(null,(int)$_SESSION['staff_id'],'resource_viewed',(string)$id);
    $downloadRestricted = Setting::get('download_restriction', '1') === '1';
    $isDownload = isset($_GET['download']) && $_GET['download'] === '1' && !$downloadRestricted;
    $disposition = $isDownload ? 'attachment' : 'inline';

    header('Content-Type: ' . $this->detectType($r));
    header('Content-Length: ' . filesize($file));
    header('Content-Disposition: ' . $disposition . '; filename="' . basename((string)$r['file_name']) . '"');
    readfile($file);
    exit;
  }
}
