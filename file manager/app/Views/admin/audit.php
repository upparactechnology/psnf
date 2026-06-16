<?php
ob_start();
$dd = $dashboardData ?? [];
$timeline = $dd['timeline'] ?? [];
$events = $dd['events'] ?? [];
?>
<div class="dashboard-head mb-3">
  <h2 class="mb-1">Audit &amp; Activity</h2>
  <p class="text-muted mb-0">Track security events and compliance logs in one place.</p>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Activity Timeline</h5><span class="chip">Security Aware</span></div>
  <div class="timeline-list">
    <?php foreach ($timeline as $t): ?>
      <article class="timeline-item severity-<?= htmlspecialchars($t['severity']) ?>" data-search="<?= htmlspecialchars(strtolower($t['event'] . ' ' . $t['meta'] . ' ' . $t['ip'])) ?>">
        <div class="timeline-dot"><i class="bi bi-shield-check"></i></div>
        <div>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <strong><?= htmlspecialchars((string)$t['event']) ?></strong>
            <span class="sev-badge sev-<?= htmlspecialchars((string)$t['severity']) ?>"><?= ucfirst((string)$t['severity']) ?></span>
            <small class="text-muted"><?= date('H:i', strtotime((string)$t['created_at'])) ?></small>
          </div>
          <div class="text-muted small"><?= htmlspecialchars((string)$t['meta']) ?> | IP: <?= htmlspecialchars((string)$t['ip']) ?></div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>

<div class="panel card-glass p-3 mb-3">
  <div class="panel-head"><h5>Audit Logs Module</h5><span class="chip">Enterprise Compliance</span></div>
  <div class="table-toolbar mb-2">
    <input class="form-control form-control-sm js-local-search" placeholder="Search user, IP, action, severity" style="max-width:300px">
    <button class="btn btn-sm btn-light" id="exportAuditBtn"><i class="bi bi-download"></i> Export Logs</button>
  </div>
  <div class="table-wrap">
    <table class="table table-modern table-sm" id="auditLogTable">
      <thead><tr><th>Date</th><th>Action</th><th>Severity</th><th>Meta</th><th>IP</th><th>Details</th></tr></thead>
      <tbody>
      <?php foreach ($events as $e): ?>
        <tr data-search="<?= htmlspecialchars(strtolower($e['event'] . ' ' . $e['meta'] . ' ' . $e['ip'] . ' ' . $e['severity'])) ?>">
          <td><?= date('Y-m-d H:i', strtotime((string)$e['created_at'])) ?></td>
          <td><?= htmlspecialchars((string)$e['event']) ?></td>
          <td><span class="sev-badge sev-<?= htmlspecialchars((string)$e['severity']) ?>"><?= ucfirst((string)$e['severity']) ?></span></td>
          <td><?= htmlspecialchars((string)$e['meta']) ?></td>
          <td class="mono"><?= htmlspecialchars((string)$e['ip']) ?></td>
          <td><button class="btn btn-sm btn-light audit-detail-btn" data-event='<?= htmlspecialchars(json_encode($e, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)) ?>'>View</button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel card-glass p-3">
  <div class="panel-head"><h5>Security Activity Feed</h5><span class="chip">Info / Warning / Critical</span></div>
  <div class="security-feed">
    <?php foreach (array_slice($events, 0, 12) as $event): ?>
      <div class="feed-item sev-<?= htmlspecialchars((string)$event['severity']) ?>">
        <div><strong><?= htmlspecialchars((string)$event['event']) ?></strong><div class="small text-muted"><?= htmlspecialchars((string)$event['meta']) ?></div></div>
        <div class="text-end"><span class="sev-badge sev-<?= htmlspecialchars((string)$event['severity']) ?>"><?= ucfirst((string)$event['severity']) ?></span><div class="small text-muted mono"><?= htmlspecialchars((string)$event['ip']) ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Audit Detail</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="auditDetailBody"></div>
    </div>
  </div>
</div>

<?php
$adminContent=ob_get_clean();
$title='Audit & Activity';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
