<?php
declare(strict_types=1);

$user = current_user();

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $year = (int) ($_POST['year'] ?? 0);
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($name === '' || $year < 2000) {
            flash('error', 'Conference name and valid year are required.');
            redirect(url('conferences'));
        }

        $checkStmt = db()->prepare('SELECT COUNT(*) FROM conferences WHERE name = :name AND year = :year');
        $checkStmt->execute(['name' => $name, 'year' => $year]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            flash('error', 'Conference already exists for this year.');
            redirect(url('conferences'));
        }

        $insertStmt = db()->prepare(
            'INSERT INTO conferences (name, year, description, created_by, created_at)
             VALUES (:name, :year, :description, :created_by, NOW())'
        );
        $insertStmt->execute([
            'name' => $name,
            'year' => $year,
            'description' => $description,
            'created_by' => (int) ($user['id'] ?? 0),
        ]);

        $conferenceId = (int) db()->lastInsertId();

        $defaultCategories = [
            'Poster',
            'Best Poster',
            'Paper',
            'Best Paper',
            'Volunteer',
            'Host',
        ];

        $typeStmt = db()->prepare(
            'INSERT INTO certificate_types (conference_id, name, slug, is_custom, created_at)
             VALUES (:conference_id, :name, :slug, :is_custom, NOW())'
        );
        foreach ($defaultCategories as $categoryName) {
            $typeStmt->execute([
                'conference_id' => $conferenceId,
                'name' => $categoryName,
                'slug' => slugify($categoryName),
                'is_custom' => 0,
            ]);
        }

        $assignStmt = db()->prepare(
            'INSERT IGNORE INTO conference_admins (conference_id, user_id, created_at)
             VALUES (:conference_id, :user_id, NOW())'
        );

        if (($user['role'] ?? '') === 'super_admin') {
            $selectedAdmins = $_POST['admin_ids'] ?? [];
            if (!is_array($selectedAdmins) || $selectedAdmins === []) {
                $selectedAdmins = [];
            }

            foreach ($selectedAdmins as $adminId) {
                $assignStmt->execute([
                    'conference_id' => $conferenceId,
                    'user_id' => (int) $adminId,
                ]);
            }
        } else {
            $assignStmt->execute([
                'conference_id' => $conferenceId,
                'user_id' => (int) $user['id'],
            ]);
        }

        flash('success', 'Conference created with default certificate categories.');
        redirect(url('conferences'));
    }

    if ($action === 'assign_admin' && ($user['role'] ?? '') === 'super_admin') {
        $conferenceId = (int) ($_POST['conference_id'] ?? 0);
        $adminId = (int) ($_POST['admin_id'] ?? 0);

        if ($conferenceId > 0 && $adminId > 0) {
            $assignStmt = db()->prepare(
                'INSERT IGNORE INTO conference_admins (conference_id, user_id, created_at)
                 VALUES (:conference_id, :user_id, NOW())'
            );
            $assignStmt->execute([
                'conference_id' => $conferenceId,
                'user_id' => $adminId,
            ]);
            flash('success', 'Admin assigned to conference.');
        }

        redirect(url('conferences'));
    }
}

if (($user['role'] ?? '') === 'super_admin' && isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    if ($deleteId > 0) {
        $stmt = db()->prepare('DELETE FROM conferences WHERE id = :id');
        $stmt->execute(['id' => $deleteId]);
        flash('success', 'Conference deleted. Related rows were removed by foreign keys.');
    }

    redirect(url('conferences'));
}

$scope = conference_scope_sql();
$listStmt = db()->prepare(
    'SELECT c.*,
            (SELECT COUNT(*) FROM certificate_types ct WHERE ct.conference_id = c.id) AS certificate_type_count,
            (SELECT COUNT(*) FROM participants p WHERE p.conference_id = c.id) AS participant_count
     FROM conferences c
     WHERE ' . $scope['sql'] . '
     ORDER BY c.year DESC, c.name ASC'
);
$listStmt->execute($scope['params']);
$conferences = $listStmt->fetchAll();

$admins = [];
if (($user['role'] ?? '') === 'super_admin') {
    $adminStmt = db()->query("SELECT id, full_name, email FROM users WHERE role IN ('admin', 'sub_admin') AND is_active = 1 ORDER BY full_name ASC");
    $admins = $adminStmt->fetchAll();
}

$assignedAdminsByConference = [];
if (($user['role'] ?? '') === 'super_admin' && $conferences !== []) {
    $conferenceIds = implode(',', array_map(static fn (array $row): string => (string) (int) $row['id'], $conferences));
    $assignmentStmt = db()->query(
        'SELECT ca.conference_id, u.full_name, u.email
         FROM conference_admins ca
         INNER JOIN users u ON u.id = ca.user_id
         WHERE ca.conference_id IN (' . $conferenceIds . ')
         ORDER BY u.full_name ASC'
    );

    foreach ($assignmentStmt->fetchAll() as $row) {
        $assignedAdminsByConference[(int) $row['conference_id']][] = $row;
    }
}

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $fileName = 'conferences_' . date('Ymd_His') . '.csv';
    stream_csv_download($fileName, ['ID', 'Name', 'Year', 'Description', 'Certificate Types', 'Participants', 'Assigned Admins', 'Created At'], $conferences, static function (array $row) use ($assignedAdminsByConference): array {
        $conferenceId = (int) ($row['id'] ?? 0);
        return [
            (string) $conferenceId,
            (string) ($row['name'] ?? ''),
            (string) ($row['year'] ?? ''),
            (string) ($row['description'] ?? ''),
            (string) ($row['certificate_type_count'] ?? '0'),
            (string) ($row['participant_count'] ?? '0'),
            (string) count($assignedAdminsByConference[$conferenceId] ?? []),
            (string) ($row['created_at'] ?? ''),
        ];
    });
}

render_view('conferences.php', [
    'pageTitle' => 'Conference Management',
    'conferences' => $conferences,
    'admins' => $admins,
    'assignedAdminsByConference' => $assignedAdminsByConference,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('conferences', ['export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
