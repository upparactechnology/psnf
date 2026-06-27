<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.2.1">
</head>
<body>
<div class="guest-shell">
    <form class="auth-card auth-slim" method="post" action="<?= e(url('login')) ?>">
        <span class="auth-brand">CertFlow Admin</span>
        <h1>Sign In</h1>
        <p>Manage conferences, templates, recipients, and certificate delivery from one place.</p>

        <?php foreach (pull_flashes() as $message): ?>
            <div class="flash flash-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
        <?php endforeach; ?>

        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>
            Email
            <input type="email" name="email" required autocomplete="username">
        </label>

        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <button type="submit">Sign In</button>
    </form>
</div>
</body>
</html>
