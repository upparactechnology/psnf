<?php
use App\Models\Setting;
/** @var array $app */
/** @var string $content */
/** @var string $title */
if (!isset($app) || !is_array($app)) {
  $app = require __DIR__ . '/../../config/app.php';
  $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
  $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
  if ($base === '/') $base = '';
  $app['base_url'] = str_replace(' ', '%20', $base);
}
$smartboardEnabled = Setting::get('allow_smartboard', '0');
$smartboardMinWidth = (int)Setting::get('smartboard_min_width', '1600');
$smartboardMinHeight = (int)Setting::get('smartboard_min_height', '900');
$isStaff = !empty($_SESSION['staff_id']) || str_contains($_SERVER['REQUEST_URI'] ?? '', '/staff/');
$isAdmin = !empty($_SESSION['admin_id']);
$screenshotProtection = Setting::get('screenshot_protection', '1');
$isStaffLogin = str_contains($_SERVER['REQUEST_URI'] ?? '', 'staff-login') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'staff_login');
$isStaffLayout = $isStaff || $isStaffLogin;
?>
<!doctype html>
<html lang="en" data-theme="light"<?= $isStaffLayout ? ' class="staff-layout"' : '' ?>>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'PSNF Secure DRM') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/video.js@8.17.4/dist/video-js.min.css" rel="stylesheet">
  <link href="<?= $app['base_url'] ?>/assets/css/app.css" rel="stylesheet">
  <script>
    (function(){
      let saved = null;
      try { saved = localStorage.getItem('psnf_theme'); } catch (e) { saved = null; }
      const initial = saved || 'light';
      document.documentElement.setAttribute('data-theme', initial);
    })();
  </script>
</head>
<body<?= $isStaffLayout ? ' class="staff-layout"' : '' ?> data-smartboard-enabled="<?= htmlspecialchars($smartboardEnabled) ?>" data-smartboard-min-width="<?= $smartboardMinWidth ?>" data-smartboard-min-height="<?= $smartboardMinHeight ?>" data-screenshot-protection="<?= ($isStaff && $screenshotProtection === '1') ? '1' : '0' ?>">
<?= $content ?>

<div id="cmdPalette" class="cmdk-backdrop d-none" aria-hidden="true">
  <div class="cmdk-panel">
    <div class="cmdk-head"><i class="bi bi-search"></i><input id="cmdkInput" placeholder="Jump to module..." autocomplete="off"></div>
    <div class="cmdk-list">
      <?php if ($isStaff): ?>
        <a href="<?= $app['base_url'] ?>/staff/dashboard" class="cmdk-item">My Assignments</a>
        <a href="<?= $app['base_url'] ?>/staff/profile" class="cmdk-item">Profile</a>
      <?php elseif ($isAdmin): ?>
        <a href="<?= $app['base_url'] ?>/admin/dashboard" class="cmdk-item">Dashboard</a>
        <a href="<?= $app['base_url'] ?>/admin/analytics" class="cmdk-item">Analytics</a>
        <a href="<?= $app['base_url'] ?>/admin/audit" class="cmdk-item">Audit &amp; Activity</a>
        <a href="<?= $app['base_url'] ?>/admin/permissions" class="cmdk-item">Permissions</a>
        <a href="<?= $app['base_url'] ?>/admin/storage" class="cmdk-item">Storage</a>
        <a href="<?= $app['base_url'] ?>/admin/folders" class="cmdk-item">Folder Management</a>
        <a href="<?= $app['base_url'] ?>/admin/files" class="cmdk-item">File Management</a>
        <a href="<?= $app['base_url'] ?>/admin/users" class="cmdk-item">Users Management</a>
        <a href="<?= $app['base_url'] ?>/admin/settings" class="cmdk-item">Settings</a>
        <a href="<?= $app['base_url'] ?>/admin/profile" class="cmdk-item">Profile</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@4.5.136/legacy/build/pdf.min.mjs" type="module"></script>
<script src="https://cdn.jsdelivr.net/npm/video.js@8.17.4/dist/video.min.js"></script>
<script src="<?= $app['base_url'] ?>/assets/js/app.js"></script>

<?php if ($isStaff && $screenshotProtection === '1'): ?>
<div id="securityShield" class="security-shield-overlay d-none">
  <div class="security-shield-card">
    <div class="security-shield-icon">
      <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="security-shield-title">Security Shield Active</h3>
    <div class="security-shield-badge">BANK-GRADE SECURITY ENFORCED</div>
    <p class="security-shield-message">
      This area contains highly confidential and sensitive customer records.
      To prevent unauthorized data exposure, screen captures, printing, and clipboard sharing have been restricted.
    </p>
    <div class="security-shield-footer">
      <i class="bi bi-info-circle-fill"></i> Refocus or click anywhere to restore your workspace.
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  (function() {
    function updateClock() {
      const clock = document.getElementById('staffClock');
      if (!clock) return;
      const now = new Date();
      let hours = now.getHours();
      let minutes = now.getMinutes();
      let seconds = now.getSeconds();
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      minutes = minutes < 10 ? '0'+minutes : minutes;
      seconds = seconds < 10 ? '0'+seconds : seconds;
      clock.textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    }
    setInterval(updateClock, 1000);
    updateClock();
  })();
</script>
</body>
</html>
