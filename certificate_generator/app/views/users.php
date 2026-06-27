<section class="grid-2">
    <article class="panel">
        <h3>Create User</h3>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_user">

            <label>
                Full Name
                <input type="text" name="full_name" required>
            </label>

            <label>
                Email
                <input type="email" name="email" required>
            </label>

            <label>
                Password
                <input type="password" name="password" minlength="6" required>
            </label>

            <label>
                Role
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="sub_admin">Sub Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </label>

            <button type="submit">Create User</button>
        </form>
    </article>

    <article class="panel">
        <h3>Role Permissions</h3>
        <ul>
            <li>Super Admin: full system access, settings, and user management.</li>
            <li>Admin: assigned conference operations.</li>
            <li>Sub Admin: restricted operational role for delegated work.</li>
        </ul>
    </article>
</section>

<section class="panel">
    <h3>Users</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Assigned Conferences</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users === []): ?>
                <tr>
                    <td colspan="7">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e((string) $user['id']) ?></td>
                        <td><?= e($user['full_name']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['role']) ?></td>
                        <td><?= e((string) $user['assigned_conferences']) ?></td>
                        <td>
                            <?php if ((int) $user['is_active'] === 1): ?>
                                <span class="badge badge-success">active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="form-grid compact-grid">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                    <button type="submit" class="button button-warning">
                                        <?= (int) $user['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="post" class="form-grid compact-grid-sm">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                    <input type="password" name="new_password" placeholder="New password" minlength="6" required>
                                    <button type="submit" class="button button-muted">Reset Password</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
