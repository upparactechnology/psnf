<?php

return [
    'title' => 'Pending Student Enrollment Applications',
    'view'  => 'students/pending_enrollments',

    'handler' => function() {
        $db = \Core\Database::getInstance();
        $status = $_GET['status'] ?? 'pending';

        $enrollments = $db->query(
            "SELECT * FROM online_enrollments WHERE status = :status ORDER BY created_at DESC",
            ['status' => $status]
        )->fetchAll();

        return [
            'status'      => $status,
            'enrollments' => $enrollments
        ];
    }
];
