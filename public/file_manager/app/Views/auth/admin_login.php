<?php ob_start(); ?>
<div class="auth-shell">
  <section class="auth-hero">
    <div>
      <div class="chip mb-3">PSNF Secure DRM</div>
      <h1>Enterprise-Grade Digital Resource Protection & Controlled Distribution</h1>
      <p class="mt-3" style="max-width:560px;color:#c6d3ea">Zero-trust access policies, secure streaming, assignment controls, and audit-ready visibility in one premium platform.</p>
    </div>
    <small class="mono" style="color:#b6c7e6">Security Layer: Enforced • Watermarking • Access Window</small>
  </section>
  <section class="auth-panel">
    <div class="card-glass p-4 auth-card">
      <h4 class="mb-1">Admin Sign In</h4>
      <p class="text-muted mb-3">Use your admin credentials to access the control center.</p>
      <form method="POST" action="<?= $app['base_url'] ?>/admin/login">
        <label class="form-label">Email</label>
        <input class="form-control mb-3" name="email" type="email" placeholder="admin@psnf.local" required>
        <label class="form-label">Password</label>
        <input class="form-control mb-3" name="password" type="password" placeholder="••••••••" required>
        <button class="btn btn-primary w-100">Login to Dashboard</button>
      </form>
      <div class="mt-3 text-muted small">Staff access? <a href="<?= $app['base_url'] ?>/staff-login">Open staff portal</a></div>
    </div>
  </section>
</div>
<?php $content=ob_get_clean(); $title='Admin Login'; require __DIR__ . '/../layouts/main.php'; ?>
