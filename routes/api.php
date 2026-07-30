<?php

declare(strict_types=1);

// API v1 Routes
$router->get('/api/v1/health', function () {
    return json_encode(['status' => 'ok', 'version' => '1.0.0', 'time' => now()]);
});

$router->post('/api/v1/auth/login', [App\Controllers\AuthController::class, 'login'], ['rate.limit:10,1']);
$router->post('/api/v1/auth/logout', [App\Controllers\AuthController::class, 'logout'], ['auth']);
$router->post('/api/v1/auth/driver-login', [App\Controllers\AuthController::class, 'apiDriverLogin'], ['rate.limit:10,1']);
$router->post('/api/v1/auth/driver-change-password', [App\Controllers\AuthController::class, 'apiDriverChangePassword'], ['auth']);

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

// ─── Staff Portal API ────────────────────────────────────────────────────────
$router->get('/api/v1/staff/attendance/today',  [App\Controllers\StaffAppController::class, 'getTodayAttendance'], ['auth']);
$router->post('/api/v1/staff/attendance/check-in', [App\Controllers\StaffAppController::class, 'checkIn'], ['auth']);
$router->post('/api/v1/staff/attendance/check-out', [App\Controllers\StaffAppController::class, 'checkOut'], ['auth']);
$router->get('/api/v1/staff/attendance/history', [App\Controllers\StaffAppController::class, 'getAttendanceHistory'], ['auth']);
$router->get('/api/v1/staff/early-students',    [App\Controllers\StaffAppController::class, 'getEarlyStudents'], ['auth']);
$router->get('/api/v1/staff/summary',           [App\Controllers\StaffAppController::class, 'getSummary'], ['auth']);
$router->get('/api/v1/staff/guardians',         [App\Controllers\StaffAppController::class, 'getGuardians'], ['auth']);


$router->get('/api/v1/driver/my-route', [App\Controllers\TransportController::class, 'driverRouteData'], ['auth', 'role:driver']);
$router->post('/api/v1/driver/start-trip', [App\Controllers\TransportController::class, 'apiStartTrip'], ['auth', 'role:driver']);
$router->post('/api/v1/driver/update-status', [App\Controllers\TransportController::class, 'apiUpdateStudentStatus'], ['auth', 'role:driver']);
$router->post('/api/v1/driver/complete-trip', [App\Controllers\TransportController::class, 'apiCompleteTrip'], ['auth', 'role:driver']);
$router->post('/api/v1/driver/{id}/location', [App\Controllers\TransportController::class, 'apiUpdateLocation'], ['auth', 'role:driver']);
