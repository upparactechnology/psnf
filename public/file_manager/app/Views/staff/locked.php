<?php ob_start(); ?>
<?php
if (isset($message)) {
  $detail = htmlspecialchars($message);
} else {
  $label = isset($dayLabel) && $dayLabel !== '' ? ' on ' . $dayLabel : '';
  $detail = 'Access allowed only' . $label . ' between ' . htmlspecialchars($start) . ' and ' . htmlspecialchars($end) . '.';
}
?>
<div class="container py-5">
  <div class="card-glass p-5 text-center shadow-lg" style="max-width: 580px; margin: 80px auto; border-radius: 20px; border: 1px solid var(--line);">
    <div class="mb-4 text-warning">
      <i class="bi bi-shield-lock-fill" style="font-size: 4.5rem; filter: drop-shadow(0 4px 10px rgba(245, 158, 11, 0.2));"></i>
    </div>
    <h3 class="mb-3 font-weight-bold text-warning">Portal Secure Lockout</h3>
    <p class="mb-4 fs-5 text-muted" style="line-height: 1.6;"><?= $detail ?></p>
    <div class="text-muted small">
      <i class="bi bi-info-circle"></i> If this timing schedule conflicts with your assignment shift, please contact the System Administrator.
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); $title='Locked'; require __DIR__ . '/../layouts/main.php'; ?>
