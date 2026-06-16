<?php ob_start(); ?>
<div class="panel card-glass p-4" style="max-width:980px">
  <h5 class="mb-3">System Settings</h5>
  <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/settings/save">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Mode</label><select class="form-select" name="deployment_mode"><option value="local" <?= $settings['deployment_mode']==='local'?'selected':'' ?>>Local (XAMPP)</option><option value="vps" <?= $settings['deployment_mode']==='vps'?'selected':'' ?>>VPS</option></select></div>
      <div class="col-12">
        <label class="form-label">Staff Access Schedule (per day)</label>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th style="width:25%">Day</th>
                <th style="width:25%">Access</th>
                <th style="width:25%">Start (12h)</th>
                <th style="width:25%">End (12h)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($accessSchedule as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['label']) ?></td>
                  <td>
                    <select class="form-select form-select-sm day-status-select" name="access_<?= htmlspecialchars($row['key']) ?>_status" data-day="<?= htmlspecialchars($row['key']) ?>">
                      <option value="1" <?= $row['status']==='1'?'selected':'' ?>>Enabled</option>
                      <option value="0" <?= $row['status']==='0'?'selected':'' ?>>Disabled</option>
                    </select>
                  </td>
                  <td><input type="text" class="form-control form-control-sm day-time-input day-start-<?= htmlspecialchars($row['key']) ?>" name="access_<?= htmlspecialchars($row['key']) ?>_start" value="<?= htmlspecialchars($row['start']) ?>" placeholder="09:00 AM" <?= $row['status']==='0'?'disabled style="opacity:0.5;"':'' ?>></td>
                  <td><input type="text" class="form-control form-control-sm day-time-input day-end-<?= htmlspecialchars($row['key']) ?>" name="access_<?= htmlspecialchars($row['key']) ?>_end" value="<?= htmlspecialchars($row['end']) ?>" placeholder="05:00 PM" <?= $row['status']==='0'?'disabled style="opacity:0.5;"':'' ?>></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="form-text">Use 12h time like 09:00 AM. Blank uses fallback window: <?= htmlspecialchars($settings['global_access_start']) ?> - <?= htmlspecialchars($settings['global_access_end']) ?>.</div>
      </div>
      <div class="col-md-3"><label class="form-label">Block Mobile</label><select class="form-select" name="block_mobile"><option value="1" <?= $settings['block_mobile']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['block_mobile']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-3"><label class="form-label">Block Laptop/Desktop</label><select class="form-select" name="block_laptop"><option value="1" <?= $settings['block_laptop']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['block_laptop']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-3"><label class="form-label">Allow Smartboard (Auto)</label><select class="form-select" name="allow_smartboard"><option value="1" <?= $settings['allow_smartboard']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['allow_smartboard']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-3"><label class="form-label">Screenshot &amp; Print Protection (Staff)</label><select class="form-select" name="screenshot_protection"><option value="1" <?= $settings['screenshot_protection']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['screenshot_protection']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-6"><label class="form-label">Smartboard Min Width (px)</label><input type="number" min="0" class="form-control" name="smartboard_min_width" value="<?= htmlspecialchars($settings['smartboard_min_width']) ?>" placeholder="1600"></div>
      <div class="col-md-6"><label class="form-label">Smartboard Min Height (px)</label><input type="number" min="0" class="form-control" name="smartboard_min_height" value="<?= htmlspecialchars($settings['smartboard_min_height']) ?>" placeholder="900"></div>
      <div class="col-12"><div class="form-text">Smartboard is detected by screen size and allowed even if laptop/mobile is blocked.</div></div>
      <div class="col-md-4"><label class="form-label">Debug Mode</label><select class="form-select" name="debug_mode"><option value="1" <?= $settings['debug_mode']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['debug_mode']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-4"><label class="form-label">Download Restriction</label><select class="form-select" name="download_restriction"><option value="1" <?= $settings['download_restriction']==='1'?'selected':'' ?>>ON</option><option value="0" <?= $settings['download_restriction']==='0'?'selected':'' ?>>OFF</option></select></div>
      <div class="col-md-6"><label class="form-label">Max Upload (MB)</label><input class="form-control" name="max_upload_mb" value="<?= $settings['max_upload_mb'] ?>"></div>
      <div class="col-md-6"><label class="form-label">Allowed Types</label><input class="form-control" name="allowed_file_types" value="<?= htmlspecialchars($settings['allowed_file_types']) ?>"></div>
    </div>
    <button class="btn btn-primary mt-4">Save Settings</button>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.day-status-select');
        selects.forEach(select => {
          select.addEventListener('change', function() {
            const day = this.getAttribute('data-day');
            const start = document.querySelector('.day-start-' + day);
            const end = document.querySelector('.day-end-' + day);
            if (start && end) {
              const disabled = this.value === '0';
              start.disabled = disabled;
              start.style.opacity = disabled ? '0.5' : '1';
              end.disabled = disabled;
              end.style.opacity = disabled ? '0.5' : '1';
            }
          });
        });
      });
    </script>
  </form>
</div>
<?php
$adminContent=ob_get_clean();
$title='Settings';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
