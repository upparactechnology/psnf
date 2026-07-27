<?php ob_start(); ?>
<div class="auth-shell">
  <section class="auth-hero">
    <div>
      <div class="chip mb-3">PSNF Staff Workspace</div>
      <h1>Secure Staff Resource Access</h1>
      <p class="mt-3" style="max-width:560px;color:#c6d3ea">View only assigned folders and files through policy-enforced secure viewers with activity tracking.</p>
    </div>
    <small class="mono" style="color:#b6c7e6">Access Controlled • Logged • Time-Gated</small>
  </section>
  <section class="auth-panel">
    <div class="card-glass p-4 auth-card">
      <h4 class="mb-1">Staff Sign In</h4>
      <p class="text-muted mb-3">Sign in to access your assigned resources.</p>
      <form method="POST" action="<?= $app['base_url'] ?>/staff-login">
        <label class="form-label">Email</label>
        <input class="form-control mb-3" name="email" type="email" placeholder="staff@psnf.local" required>
        <label class="form-label">Password</label>
        <input class="form-control mb-3" name="password" type="password" placeholder="••••••••" required>
        <button class="btn btn-primary w-100">Login to Staff Portal</button>
      </form>
      <div class="mt-3 text-muted small">Admin? <a href="<?= $app['base_url'] ?>/admin/login">Open admin login</a></div>
    </div>
  </section>
</div>
<?php $content=ob_get_clean(); $title='Staff Login'; require __DIR__ . '/../layouts/main.php'; ?>
