<?php

declare(strict_types=1);

use App\Controllers\{AuthController, DashboardController, UserController, RoleController, StudentController, ParentPortalController};

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
        \Core\Application::$app->response->redirect('/dashboard');
    } else {
        \Core\Application::$app->response->redirect('/login');
    }
    exit();
});

// ─── Dashboard ────────────────────────────────────────────────────────────────
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'tenant']);

// ─── Users ────────────────────────────────────────────────────────────────────
$router->get('/users',             [UserController::class, 'index'],   ['auth', 'permission:view_users']);
$router->get('/users/create',      [UserController::class, 'create'],  ['auth', 'permission:create_users']);
$router->post('/users',            [UserController::class, 'store'],   ['auth', 'permission:create_users']);
$router->get('/users/{id}/edit',   [UserController::class, 'edit'],    ['auth', 'permission:edit_users']);
$router->post('/users/{id}',       [UserController::class, 'update'],  ['auth', 'permission:edit_users']);
$router->delete('/users/{id}',     [UserController::class, 'destroy'], ['auth', 'permission:delete_users']);

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

// ─── Migrations Helper (Web Run) ─────────────────────────────────────────────
$router->get('/migrate', function () {
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
$router->get('/parent/students/{id}/timetable',    [ParentPortalController::class, 'timetable'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/homework',     [ParentPortalController::class, 'homework'],      ['auth', 'role:parent']);
$router->get('/parent/students/{id}/exams',        [ParentPortalController::class, 'exams'],         ['auth', 'role:parent']);
$router->get('/parent/students/{id}/medical',      [ParentPortalController::class, 'medical'],       ['auth', 'role:parent']);
$router->get('/parent/students/{id}/transport',    [ParentPortalController::class, 'transport'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/fees',         [ParentPortalController::class, 'fees'],          ['auth', 'role:parent']);
$router->post('/parent/students/{id}/fees/{invoice_id}/pay', [ParentPortalController::class, 'payFee'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/emergency',   [ParentPortalController::class, 'updateEmergency'],['auth', 'role:parent']);
$router->get('/parent/announcements',              [ParentPortalController::class, 'announcements'], ['auth', 'role:parent']);
$router->get('/parent/communication',              [ParentPortalController::class, 'communication'], ['auth', 'role:parent']);
$router->post('/parent/communication',             [ParentPortalController::class, 'sendMessage'],   ['auth', 'role:parent']);
