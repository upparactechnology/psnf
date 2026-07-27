<?php ob_start(); ?>
<div class="panel card-glass p-4" style="max-width:980px">
  <h5 class="mb-3">System Settings</h5>
  <form class="ajax-form" method="post" action="<?= $app['base_url'] ?>/admin/settings/save">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Mode</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="deploymentModeBtn">
            <?= $settings['deployment_mode']==='vps'?'VPS':'Local (XAMPP)' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('deploymentMode', 'local', 'Local (XAMPP)'); return false;">Local (XAMPP)</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('deploymentMode', 'vps', 'VPS'); return false;">VPS</a></li>
          </ul>
          <input type="hidden" name="deployment_mode" id="deploymentModeInput" value="<?= htmlspecialchars($settings['deployment_mode']) ?>">
        </div>
      </div>
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
                    <div class="dropdown">
                      <button class="form-select form-select-sm text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="access_<?= htmlspecialchars($row['key']) ?>_statusBtn">
                        <?= $row['status']==='1'?'Enabled':'Disabled' ?>
                      </button>
                      <ul class="dropdown-menu custom-select-dropdown">
                        <li><a class="dropdown-item" href="#" onclick="setDayStatus('<?= htmlspecialchars($row['key']) ?>', '1', 'Enabled'); return false;">Enabled</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setDayStatus('<?= htmlspecialchars($row['key']) ?>', '0', 'Disabled'); return false;">Disabled</a></li>
                      </ul>
                      <input type="hidden" name="access_<?= htmlspecialchars($row['key']) ?>_status" id="access_<?= htmlspecialchars($row['key']) ?>_statusInput" value="<?= htmlspecialchars($row['status']) ?>">
                    </div>
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
      <div class="col-md-3">
        <label class="form-label">Block Mobile</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="blockMobileBtn">
            <?= $settings['block_mobile']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('blockMobile', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('blockMobile', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="block_mobile" id="blockMobileInput" value="<?= htmlspecialchars($settings['block_mobile']) ?>">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Block Laptop/Desktop</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="blockLaptopBtn">
            <?= $settings['block_laptop']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('blockLaptop', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('blockLaptop', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="block_laptop" id="blockLaptopInput" value="<?= htmlspecialchars($settings['block_laptop']) ?>">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Allow Smartboard (Auto)</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="allowSmartboardBtn">
            <?= $settings['allow_smartboard']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('allowSmartboard', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('allowSmartboard', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="allow_smartboard" id="allowSmartboardInput" value="<?= htmlspecialchars($settings['allow_smartboard']) ?>">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Screenshot &amp; Print Protection (Staff)</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="screenshotProtectionBtn">
            <?= $settings['screenshot_protection']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('screenshotProtection', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('screenshotProtection', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="screenshot_protection" id="screenshotProtectionInput" value="<?= htmlspecialchars($settings['screenshot_protection']) ?>">
        </div>
      </div>
      <div class="col-md-6"><label class="form-label">Smartboard Min Width (px)</label><input type="number" min="0" class="form-control" name="smartboard_min_width" value="<?= htmlspecialchars($settings['smartboard_min_width']) ?>" placeholder="1600"></div>
      <div class="col-md-6"><label class="form-label">Smartboard Min Height (px)</label><input type="number" min="0" class="form-control" name="smartboard_min_height" value="<?= htmlspecialchars($settings['smartboard_min_height']) ?>" placeholder="900"></div>
      <div class="col-12"><div class="form-text">Smartboard is detected by screen size and allowed even if laptop/mobile is blocked.</div></div>
      <div class="col-md-4">
        <label class="form-label">Debug Mode</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="debugModeBtn">
            <?= $settings['debug_mode']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('debugMode', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('debugMode', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="debug_mode" id="debugModeInput" value="<?= htmlspecialchars($settings['debug_mode']) ?>">
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Download Restriction</label>
        <div class="dropdown">
          <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="downloadRestrictionBtn">
            <?= $settings['download_restriction']==='1'?'ON':'OFF' ?>
          </button>
          <ul class="dropdown-menu w-100 custom-select-dropdown">
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('downloadRestriction', '1', 'ON'); return false;">ON</a></li>
            <li><a class="dropdown-item" href="#" onclick="setSelectVal('downloadRestriction', '0', 'OFF'); return false;">OFF</a></li>
          </ul>
          <input type="hidden" name="download_restriction" id="downloadRestrictionInput" value="<?= htmlspecialchars($settings['download_restriction']) ?>">
        </div>
      </div>
      <div class="col-md-6"><label class="form-label">Max Upload (MB)</label><input class="form-control" name="max_upload_mb" value="<?= $settings['max_upload_mb'] ?>"></div>
      <div class="col-md-6"><label class="form-label">Allowed Types</label><input class="form-control" name="allowed_file_types" value="<?= htmlspecialchars($settings['allowed_file_types']) ?>"></div>
    </div>
    <button class="btn btn-primary mt-4">Save Settings</button>
    <script>
      function setSelectVal(prefix, val, label) {
        document.getElementById(prefix + 'Input').value = val;
        document.getElementById(prefix + 'Btn').innerText = label;
      }
      function setDayStatus(day, val, label) {
        document.getElementById('access_' + day + '_statusInput').value = val;
        const btn = document.getElementById('access_' + day + '_statusBtn');
        btn.innerText = label;
        
        const start = document.querySelector('.day-start-' + day);
        const end = document.querySelector('.day-end-' + day);
        if (start && end) {
          const disabled = val === '0';
          start.disabled = disabled;
          start.style.opacity = disabled ? '0.5' : '1';
          end.disabled = disabled;
          end.style.opacity = disabled ? '0.5' : '1';
        }
      }
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
