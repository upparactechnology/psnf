<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=psnf_drm;charset=utf8mb4', 'root', '');
$records = $pdo->query("
    SELECT s.*, b.name as branch_name, sc.name as school_name
    FROM students s
    LEFT JOIN branches b ON s.branch_id = b.id
    LEFT JOIN schools sc ON s.school_id = sc.id
    WHERE s.tenant_id = 1 AND s.deleted_at IS NULL
    ORDER BY s.first_name ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

print_r($records);
