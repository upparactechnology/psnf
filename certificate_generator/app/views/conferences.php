<section class="grid-2">
    <article class="panel">
        <h3>Create Conference</h3>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <label>
                Conference Name
                <input type="text" name="name" placeholder="e.g. 3rd ICMOTARSS" required>
            </label>

            <label>
                Year
                <input type="number" name="year" min="2000" max="2100" value="<?= e(date('Y')) ?>" required>
            </label>

            <label>
                Description
                <textarea name="description" rows="3" placeholder="Optional details"></textarea>
            </label>

            <?php if (has_role(['super_admin'])): ?>
                <label>
                    Assign Admin(s)
                    <select name="admin_ids[]" multiple size="5">
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?= e((string) $admin['id']) ?>">
                                <?= e($admin['full_name']) ?> (<?= e($admin['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>

            <button type="submit">Create Conference</button>
        </form>
    </article>

    <article class="panel">
        <h3>Notes</h3>
        <ul>
            <li>Conference year controls output folders under templates/year/category and generated/year/category.</li>
            <li>Default categories are auto-created: Poster, Best Poster, Paper, Best Paper, Volunteer, Host.</li>
            <li>Use Certificate Types page to upload template JPG for each category and add custom types.</li>
        </ul>
    </article>
</section>

<section class="panel">
    <h3>All Conferences</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Types</th>
                    <th>Participants</th>
                    <th>Admins</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($conferences === []): ?>
                <tr>
                    <td colspan="6">No conferences created yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($conferences as $conference): ?>
                    <tr>
                        <td><?= e($conference['name']) ?></td>
                        <td><?= e((string) $conference['year']) ?></td>
                        <td><?= e((string) $conference['certificate_type_count']) ?></td>
                        <td><?= e((string) $conference['participant_count']) ?></td>
                        <td>
                            <?php if (has_role(['super_admin'])): ?>
                                <?php $assigned = $assignedAdminsByConference[(int) $conference['id']] ?? []; ?>
                                <?php if ($assigned === []): ?>
                                    <span class="small">No admin assigned</span>
                                <?php else: ?>
                                    <?php foreach ($assigned as $adminRow): ?>
                                        <div class="small"><?= e($adminRow['full_name']) ?> (<?= e($adminRow['email']) ?>)</div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="small">Scoped by your role</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="inline-actions">
                                <a class="button button-muted" href="<?= e(url('certificate-types', ['conference_id' => (int) $conference['id']])) ?>">Types</a>
                                <a class="button button-muted" href="<?= e(url('participants', ['conference_id' => (int) $conference['id']])) ?>">Participants</a>
                                <?php if (has_role(['super_admin'])): ?>
                                    <a class="button button-danger" data-confirm="Delete this conference and all related data?"
                                       href="<?= e(url('conferences', ['delete_id' => (int) $conference['id']])) ?>">Delete</a>
                                <?php endif; ?>
                            </div>

                            <?php if (has_role(['super_admin']) && $admins !== []): ?>
                                <form method="post" class="form-grid top-gap-sm">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="assign_admin">
                                    <input type="hidden" name="conference_id" value="<?= e((string) $conference['id']) ?>">
                                    <label>
                                        Assign More Admin
                                        <select name="admin_id" required>
                                            <option value="">Select admin</option>
                                            <?php foreach ($admins as $admin): ?>
                                                <option value="<?= e((string) $admin['id']) ?>"><?= e($admin['full_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <button type="submit" class="button button-muted">Assign</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
