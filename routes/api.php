<?php

declare(strict_types=1);

// API v1 Routes
$router->get('/api/v1/health', function () {
    return json_encode(['status' => 'ok', 'version' => '1.0.0', 'time' => now()]);
});

$router->post('/api/v1/auth/login', [App\Controllers\AuthController::class, 'login'], ['rate.limit:10,1']);
$router->post('/api/v1/auth/logout', [App\Controllers\AuthController::class, 'logout'], ['auth']);

$router->get('/api/v1/students',      [App\Controllers\StudentController::class, 'index'],   ['auth', 'permission:view_students']);
$router->get('/api/v1/students/{id}', [App\Controllers\StudentController::class, 'show'],    ['auth', 'permission:view_students']);
$router->post('/api/v1/students',     [App\Controllers\StudentController::class, 'store'],   ['auth', 'permission:create_students']);
$router->post('/api/v1/students/{id}',[App\Controllers\StudentController::class, 'update'],  ['auth', 'permission:edit_students']);

// ─── Parent Portal API — Module 3 ───────────────────────────────────────────
$router->get('/api/v1/parent/students',                   [App\Controllers\ParentPortalController::class, 'apiStudents'],          ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/profile',        [App\Controllers\ParentPortalController::class, 'apiStudentProfile'],   ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/attendance',     [App\Controllers\ParentPortalController::class, 'apiAttendance'],       ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/timetable',      [App\Controllers\ParentPortalController::class, 'apiTimetable'],        ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/homework',       [App\Controllers\ParentPortalController::class, 'apiHomework'],         ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/exams',          [App\Controllers\ParentPortalController::class, 'apiExams'],            ['auth', 'role:parent']);
$router->get('/api/v1/parent/students/{id}/fees',           [App\Controllers\ParentPortalController::class, 'apiFees'],             ['auth', 'role:parent']);
$router->post('/api/v1/parent/students/{id}/fees/{invoice_id}/pay', [App\Controllers\ParentPortalController::class, 'apiPayFee'],   ['auth', 'role:parent']);
$router->post('/api/v1/parent/students/{id}/emergency',     [App\Controllers\ParentPortalController::class, 'apiUpdateEmergency'],  ['auth', 'role:parent']);
$router->get('/api/v1/parent/announcements',              [App\Controllers\ParentPortalController::class, 'apiAnnouncements'],    ['auth', 'role:parent']);
$router->get('/api/v1/parent/messages',                   [App\Controllers\ParentPortalController::class, 'apiMessages'],         ['auth', 'role:parent']);
$router->post('/api/v1/parent/messages',                  [App\Controllers\ParentPortalController::class, 'apiSendMessage'],      ['auth', 'role:parent']);
