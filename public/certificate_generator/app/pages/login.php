<?php
declare(strict_types=1);

if (is_logged_in()) {
    redirect(url('dashboard'));
}

if (is_post_request()) {
    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('
        SELECT u.id, u.name, u.email, u.password, r.slug AS role
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        WHERE u.email = :email AND u.is_active = 1
        ORDER BY FIELD(r.slug, \'super_admin\', \'school_admin\', \'manager\', \'teacher\', \'therapist\', \'staff\', \'driver\', \'parent\', \'student\') ASC
        LIMIT 1
    ');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, (string) $user['password'])) {
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'full_name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) ($user['role'] ?? 'staff'),
        ];

        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect(url('dashboard'));
    }

    flash('error', 'Invalid login credentials.');
    redirect(url('login'));
}

render_view('auth/login.php', [
    'pageTitle' => 'Login',
], false);
