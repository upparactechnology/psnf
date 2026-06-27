<?php
declare(strict_types=1);

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create_user') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'admin');

        if ($fullName === '' || $email === '' || $password === '') {
            flash('error', 'Name, email and password are required.');
            redirect(url('users'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email address.');
            redirect(url('users'));
        }

        if (!in_array($role, ['super_admin', 'admin', 'sub_admin'], true)) {
            $role = 'admin';
        }

        $existsStmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $existsStmt->execute(['email' => $email]);
        if ((int) $existsStmt->fetchColumn() > 0) {
            flash('error', 'A user with this email already exists.');
            redirect(url('users'));
        }

        $insertStmt = db()->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, is_active, created_at)
             VALUES (:full_name, :email, :password_hash, :role, :is_active, NOW())'
        );
        $insertStmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => 1,
        ]);

        flash('success', 'User created successfully.');
        redirect(url('users'));
    }

    if ($action === 'toggle_active') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $stmt = db()->prepare(
                'UPDATE users
                 SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END
                 WHERE id = :id'
            );
            $stmt->execute(['id' => $userId]);
            flash('success', 'User status updated.');
        }

        redirect(url('users'));
    }

    if ($action === 'reset_password') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = (string) ($_POST['new_password'] ?? '');

        if ($userId <= 0 || strlen($newPassword) < 6) {
            flash('error', 'Provide a valid user and password (min 6 chars).');
            redirect(url('users'));
        }

        $stmt = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);

        flash('success', 'Password reset successfully.');
        redirect(url('users'));
    }
}

$listStmt = db()->query(
    'SELECT u.*, (SELECT COUNT(*) FROM conference_admins ca WHERE ca.user_id = u.id) AS assigned_conferences
     FROM users u
     ORDER BY u.created_at DESC'
);
$users = $listStmt->fetchAll();

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $fileName = 'users_' . date('Ymd_His') . '.csv';
    stream_csv_download($fileName, ['ID', 'Name', 'Email', 'Role', 'Status', 'Assigned Conferences', 'Created At'], $users, static function (array $row): array {
        return [
            (string) ($row['id'] ?? ''),
            (string) ($row['full_name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['role'] ?? ''),
            (string) ((int) ($row['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive'),
            (string) ($row['assigned_conferences'] ?? '0'),
            (string) ($row['created_at'] ?? ''),
        ];
    });
}

render_view('users.php', [
    'pageTitle' => 'User Management',
    'users' => $users,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('users', ['export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
