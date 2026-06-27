<?php

declare(strict_types=1);

use App\Controllers\{AuthController, DashboardController, UserController, RoleController, StudentController, ParentPortalController, FeeController, TransportController, CertificateController, TeacherPortalController, AdmissionsController, ClassesController, AttendanceController, TimetablesController, ExamsController, ReceiptsController, ScholarshipController, MedicalController, SettingsController, ReportCardController};

// ─── Auth (Guest Only) ────────────────────────────────────────────────────────
$router->get('/login',           [AuthController::class, 'showLogin'],          ['guest']);
$router->post('/login',          [AuthController::class, 'login'],              ['rate.limit:10,1']);
$router->get('/logout',          [AuthController::class, 'logout']);
$router->post('/logout',         [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], ['guest']);
$router->post('/forgot-password',[AuthController::class, 'forgotPassword'],     ['rate.limit:5,1']);
$router->get('/reset-password',  [AuthController::class, 'showResetPassword'],  ['guest']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/otp',             [AuthController::class, 'showOtp']);
$router->post('/otp',            [AuthController::class, 'verifyOtp']);

// Root redirect
$router->get('/', function () {
    if (is_logged_in()) {
        \Core\Application::$app->response->redirect(dashboard_url());
    } else {
        \Core\Application::$app->response->redirect('/login');
    }
    exit();
});

// ─── Dashboard ────────────────────────────────────────────────────────────────
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'tenant']);
$router->get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard'], ['auth', 'role:teacher']);

// ─── Users ────────────────────────────────────────────────────────────────────
$router->get('/users/attendance',  [UserController::class, 'attendanceLog'],['auth', 'permission:view_users']);
$router->get('/users',             [UserController::class, 'index'],   ['auth', 'permission:view_users']);
$router->get('/users/create',      [UserController::class, 'create'],  ['auth', 'permission:create_users']);
$router->post('/users',            [UserController::class, 'store'],   ['auth', 'permission:create_users']);
$router->get('/users/{id}/edit',   [UserController::class, 'edit'],    ['auth', 'permission:edit_users']);
$router->post('/users/{id}',       [UserController::class, 'update'],  ['auth', 'permission:edit_users']);
$router->delete('/users/{id}',     [UserController::class, 'destroy'], ['auth', 'permission:delete_users']);

// ─── Settings ─────────────────────────────────────────────────────────────────
$router->get('/settings',          [SettingsController::class, 'index'],  ['auth', 'permission:view_settings']);
$router->post('/settings',         [SettingsController::class, 'update'], ['auth', 'permission:edit_settings']);

// ─── Roles ────────────────────────────────────────────────────────────────────
$router->get('/roles',             [RoleController::class, 'index'],   ['auth', 'permission:view_roles']);
$router->get('/roles/create',      [RoleController::class, 'create'],  ['auth', 'permission:create_roles']);
$router->post('/roles',            [RoleController::class, 'store'],   ['auth', 'permission:create_roles']);
$router->get('/roles/{id}/edit',   [RoleController::class, 'edit'],    ['auth', 'permission:edit_roles']);
$router->post('/roles/{id}',       [RoleController::class, 'update'],  ['auth', 'permission:edit_roles']);
$router->delete('/roles/{id}',     [RoleController::class, 'destroy'], ['auth', 'permission:delete_roles']);

// ─── Students — Module 2 ─────────────────────────────────────────────────────
$router->get('/students',                          [StudentController::class, 'index'],              ['auth', 'permission:view_students']);
$router->get('/students/create',                   [StudentController::class, 'create'],             ['auth', 'permission:create_students']);
$router->post('/students',                         [StudentController::class, 'store'],              ['auth', 'permission:create_students']);
$router->get('/students/search',                   [StudentController::class, 'search'],             ['auth']);
$router->get('/students/{id}',                     [StudentController::class, 'show'],               ['auth', 'permission:view_students']);
$router->get('/students/{id}/edit',                [StudentController::class, 'edit'],               ['auth', 'permission:edit_students']);
$router->post('/students/{id}',                    [StudentController::class, 'update'],             ['auth', 'permission:edit_students']);
$router->delete('/students/{id}',                  [StudentController::class, 'destroy'],            ['auth', 'permission:delete_students']);
$router->post('/students/{id}/medical',            [StudentController::class, 'updateMedical'],      ['auth', 'permission:edit_students']);
$router->post('/students/{id}/status',             [StudentController::class, 'updateStatus'],       ['auth', 'permission:approve_admissions']);
$router->post('/students/{id}/documents',          [StudentController::class, 'uploadDocument'],     ['auth', 'permission:upload_documents']);
$router->post('/students/{id}/guardians',          [StudentController::class, 'storeGuardian'],      ['auth', 'permission:edit_students']);
$router->post('/students/{id}/emergency-contacts', [StudentController::class, 'storeEmergencyContact'],['auth', 'permission:edit_students']);
$router->get('/students/{id}/timeline',            [StudentController::class, 'timeline'],           ['auth', 'permission:view_students']);
$router->get('/report-cards',                      [ReportCardController::class, 'index'],           ['auth', 'permission:edit_students']);
$router->get('/report-cards/settings',             [ReportCardController::class, 'settings'],        ['auth', 'permission:edit_students']);
$router->post('/report-cards/settings',            [ReportCardController::class, 'saveSettings'],    ['auth', 'permission:edit_students']);
$router->post('/report-cards/settings/upload-signature', [ReportCardController::class, 'uploadSignature'], ['auth', 'permission:edit_students']);
$router->get('/students/{id}/report-card/edit',    [ReportCardController::class, 'edit'],            ['auth', 'permission:edit_students']);
$router->post('/students/{id}/report-card',        [ReportCardController::class, 'store'],           ['auth', 'permission:edit_students']);
$router->get('/students/{id}/report-card/view',    [ReportCardController::class, 'show'],            ['auth', 'permission:view_students']);


// ─── Fees Management — Admin ──────────────────────────────────────────────────
$router->get('/fees',                       [FeeController::class, 'index'],          ['auth', 'tenant']);
$router->get('/fees/create',                [FeeController::class, 'create'],         ['auth', 'tenant']);
$router->post('/fees',                      [FeeController::class, 'store'],          ['auth', 'tenant']);
$router->post('/fees/{id}/pay',             [FeeController::class, 'recordPayment'],  ['auth', 'tenant']);
$router->post('/fees/{id}/delete',          [FeeController::class, 'destroy'],        ['auth', 'tenant']);

// ─── Receipts — Admin ─────────────────────────────────────────────────────────
$router->get('/receipts', [ReceiptsController::class, 'index'], ['auth', 'tenant']);
$router->get('/receipts/settings', [ReceiptsController::class, 'settings'], ['auth', 'tenant']);
$router->post('/receipts/settings', [ReceiptsController::class, 'saveSettings'], ['auth', 'tenant']);
$router->get('/receipts/{id}/view', [ReceiptsController::class, 'show'], ['auth', 'tenant']);

// ─── Scholarships — Admin ─────────────────────────────────────────────────────
$router->get('/scholarships', [ScholarshipController::class, 'index'], ['auth', 'tenant']);
$router->post('/scholarships/store', [ScholarshipController::class, 'store'], ['auth', 'tenant']);

// ─── Medical Logs & Care Profiles — Admin ──────────────────────────────────────
$router->get('/medical', [MedicalController::class, 'index'], ['auth', 'tenant']);
$router->post('/medical/{id}', [MedicalController::class, 'store'], ['auth', 'tenant']);

// ─── Transport Management — Admin ──────────────────────────────────────────────
$router->get('/transport',                  [TransportController::class, 'index'],    ['auth', 'tenant']);
$router->get('/transport/create',           [TransportController::class, 'create'],   ['auth', 'tenant']);
$router->get('/transport/tracking',         [TransportController::class, 'tracking'], ['auth', 'tenant']);
$router->get('/transport/live-data',        [TransportController::class, 'liveData'], ['auth', 'tenant']);
$router->post('/transport',                 [TransportController::class, 'store'],    ['auth', 'tenant']);
$router->get('/transport/{id}/edit',        [TransportController::class, 'edit'],     ['auth', 'tenant']);
$router->post('/transport/{id}',            [TransportController::class, 'update'],    ['auth', 'tenant']);
$router->post('/transport/{id}/assign',     [TransportController::class, 'assignStudent'],['auth', 'tenant']);
$router->post('/transport/{id}/location',   [TransportController::class, 'updateLocation'],['auth', 'tenant']);
$router->post('/transport/{id}/delete',     [TransportController::class, 'destroy'],   ['auth', 'tenant']);


// ─── Certificate Designer — Admin ──────────────────────────────────────────────
$router->get('/certificates',               [CertificateController::class, 'index'],  ['auth', 'tenant']);
$router->get('/certificates/create',        [CertificateController::class, 'create'], ['auth', 'tenant']);
$router->post('/certificates',              [CertificateController::class, 'store'],  ['auth', 'tenant']);
$router->get('/certificates/{id}/view',     [CertificateController::class, 'show'],   ['auth', 'tenant']);
$router->post('/certificates/{id}/delete',  [CertificateController::class, 'destroy'],['auth', 'tenant']);

// ─── Admissions ──────────────────────────────────────────────────────────────
$router->get('/admissions', [AdmissionsController::class, 'index'], ['auth', 'tenant']);
$router->post('/admissions/{id}/status', [AdmissionsController::class, 'updateStatus'], ['auth', 'tenant']);

// ─── Classes & Sections ───────────────────────────────────────────────────────
$router->get('/classes', [ClassesController::class, 'index'], ['auth', 'tenant']);
$router->post('/classes', [ClassesController::class, 'store'], ['auth', 'tenant']);
$router->get('/classes/{class}', [ClassesController::class, 'show'], ['auth', 'tenant']);

// ─── Attendance ──────────────────────────────────────────────────────────────
$router->get('/attendance', [AttendanceController::class, 'index'], ['auth', 'tenant']);
$router->post('/attendance/save', [AttendanceController::class, 'save'], ['auth', 'tenant']);

// ─── Timetables ──────────────────────────────────────────────────────────────
$router->get('/timetables', [TimetablesController::class, 'index'], ['auth', 'tenant']);
$router->post('/timetables/store', [TimetablesController::class, 'store'], ['auth', 'tenant']);

// ─── Exams & Grades ──────────────────────────────────────────────────────────
$router->get('/exams', [ExamsController::class, 'index'], ['auth', 'tenant']);
$router->post('/exams/store', [ExamsController::class, 'store'], ['auth', 'tenant']);

// ─── Migrations Helper (Web Run) ─────────────────────────────────────────────
$router->get('/migrate', function () {
    require_once CORE_PATH . '/Migration.php';
    $runner = new \Core\Migration();
    ob_start();
    $runner->run();
    $runner->seed();
    $output = ob_get_clean();
    return "<h1>Migration & Seed Output:</h1><pre>" . htmlspecialchars($output) . "</pre>";
});

// ─── Parent Portal — Module 3 ────────────────────────────────────────────────
$router->get('/parent/dashboard',                  [ParentPortalController::class, 'dashboard'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/attendance',   [ParentPortalController::class, 'attendance'],    ['auth', 'role:parent']);
$router->get('/parent/students/{id}/attendance/check-date', [ParentPortalController::class, 'checkDateAttendance'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/attendance/declare', [ParentPortalController::class, 'submitTomorrowAttendance'], ['auth', 'role:parent']);
$router->get('/parent/students/{id}/timetable',    [ParentPortalController::class, 'timetable'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/homework',     [ParentPortalController::class, 'homework'],      ['auth', 'role:parent']);
$router->get('/parent/students/{id}/exams',        [ParentPortalController::class, 'exams'],         ['auth', 'role:parent']);
$router->get('/parent/students/{id}/certificates', [ParentPortalController::class, 'certificates'],  ['auth', 'role:parent']);
$router->get('/parent/students/{id}/report-card',  [ReportCardController::class, 'parentShow'],      ['auth', 'role:parent']);

$router->get('/parent/students/{id}/medical',      [ParentPortalController::class, 'medical'],       ['auth', 'role:parent']);
$router->get('/parent/students/{id}/transport',    [ParentPortalController::class, 'transport'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/fees',         [ParentPortalController::class, 'fees'],          ['auth', 'role:parent']);
$router->post('/parent/students/{id}/fees/{invoice_id}/pay', [ParentPortalController::class, 'payFee'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/emergency',   [ParentPortalController::class, 'updateEmergency'],['auth', 'role:parent']);
$router->get('/parent/announcements',              [ParentPortalController::class, 'announcements'], ['auth', 'role:parent']);
$router->get('/parent/communication',              [ParentPortalController::class, 'communication'], ['auth', 'role:parent']);
$router->post('/parent/communication',             [ParentPortalController::class, 'sendMessage'],   ['auth', 'role:parent']);

$router->get('/uploads/certificates/{file}', function ($file) {
    $path = STORAGE_PATH . '/uploads/certificates/' . $file;
    if (!file_exists($path)) {
        $path = ROOT_PATH . '/test_cert.pdf';
    }
    if (file_exists($path)) {
        $mime = mime_content_type($path);
        header('Content-Type: ' . $mime);
        readfile($path);
        exit();
    }
    \Core\Application::$app->response->abort(404);
});
