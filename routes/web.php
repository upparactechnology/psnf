<?php

declare(strict_types=1);

use App\Controllers\{AuthController, DashboardController, UserController, RoleController, StudentController, EnrollmentAdminController, ParentPortalController, FeeController, TransportController, CertificateController, TeacherPortalController, AdmissionsController, ClassesController, AttendanceController, TimetablesController, ExamsController, ReceiptsController, ScholarshipController, MedicalController, SettingsController, ReportCardController, AnnouncementController};

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

// Parent Portal Auth
$router->get('/parent-login',          [\App\Controllers\ParentPortalLoginController::class, 'showLogin'], ['guest']);
$router->post('/parent-login',         [\App\Controllers\ParentPortalLoginController::class, 'login'], ['rate.limit:10,1']);
$router->get('/parent/logout',         [\App\Controllers\ParentPortalLoginController::class, 'logout']);
$router->post('/parent/logout',        [\App\Controllers\ParentPortalLoginController::class, 'logout']);
$router->get('/parent/change-password', [\App\Controllers\ParentPortalLoginController::class, 'showChangePassword'], ['auth', 'role:parent']);
$router->post('/parent/change-password',[\App\Controllers\ParentPortalLoginController::class, 'changePassword'], ['auth', 'role:parent']);

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
$router->get('/games',     [DashboardController::class, 'games'], ['auth', 'tenant']);
$router->get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard'], ['auth', 'role:teacher']);

// ─── Staff Management Workspace (/staff/*) ──────────────────────────────────
$router->get('/staff',                  [App\Controllers\StaffWorkspaceController::class, 'overview'],    ['auth', 'tenant']);
$router->get('/staff/overview',         [App\Controllers\StaffWorkspaceController::class, 'overview'],    ['auth', 'tenant']);
$router->get('/staff/employees',        [App\Controllers\StaffWorkspaceController::class, 'employees'],   ['auth', 'tenant']);
$router->post('/staff/employees',       [App\Controllers\StaffWorkspaceController::class, 'storeEmployee'],['auth', 'tenant']);
$router->get('/staff/employees/{id}',   [App\Controllers\StaffWorkspaceController::class, 'showEmployee'],['auth', 'tenant']);
$router->post('/staff/employees/{id}',  [App\Controllers\StaffWorkspaceController::class, 'updateEmployee'],['auth', 'tenant']);
$router->get('/staff/departments',      [App\Controllers\StaffWorkspaceController::class, 'departments'], ['auth', 'tenant']);
$router->post('/staff/departments',     [App\Controllers\StaffWorkspaceController::class, 'storeDepartment'],['auth', 'tenant']);
$router->get('/staff/designations',     [App\Controllers\StaffWorkspaceController::class, 'designations'],['auth', 'tenant']);
$router->post('/staff/designations',    [App\Controllers\StaffWorkspaceController::class, 'storeDesignation'],['auth', 'tenant']);
$router->get('/staff/attendance',       [App\Controllers\StaffWorkspaceController::class, 'attendance'],  ['auth', 'tenant']);
$router->post('/staff/attendance',      [App\Controllers\StaffWorkspaceController::class, 'storeAttendance'],['auth', 'tenant']);
$router->get('/staff/leaves',           [App\Controllers\StaffWorkspaceController::class, 'leaves'],      ['auth', 'tenant']);
$router->post('/staff/leaves',          [App\Controllers\StaffWorkspaceController::class, 'storeLeave'], ['auth', 'tenant']);
$router->get('/staff/payroll',          [App\Controllers\StaffWorkspaceController::class, 'payroll'],     ['auth', 'tenant']);
$router->post('/staff/payroll/run',     [App\Controllers\StaffWorkspaceController::class, 'runPayroll'],   ['auth', 'tenant']);
$router->get('/staff/payroll/{id}',     [App\Controllers\StaffWorkspaceController::class, 'payrollDetails'],['auth', 'tenant']);
$router->get('/staff/payroll/{id}/payslip/{empId}', [App\Controllers\StaffWorkspaceController::class, 'payslip'],['auth', 'tenant']);
$router->get('/staff/roles',            [RoleController::class, 'index'],       ['auth', 'permission:view_roles']);
$router->get('/staff/users',            [App\Controllers\StaffWorkspaceController::class, 'users'],       ['auth', 'tenant']);
$router->get('/staff/settings',         [App\Controllers\StaffWorkspaceController::class, 'settings'],    ['auth', 'tenant']);
$router->post('/staff/settings',        [App\Controllers\StaffWorkspaceController::class, 'saveSettings'],['auth', 'tenant']);

// ─── Payroll Management Workspace (/payroll/*) ────────────────────────────────
$router->get('/payroll',                            [App\Controllers\PayrollController::class, 'runs'],         ['auth', 'tenant']);
$router->get('/payroll/runs',                       [App\Controllers\PayrollController::class, 'runs'],         ['auth', 'tenant']);
$router->post('/payroll/runs/run',                  [App\Controllers\PayrollController::class, 'runPayroll'],   ['auth', 'tenant']);
$router->get('/payroll/runs/{id}',                  [App\Controllers\PayrollController::class, 'runDetails'],   ['auth', 'tenant']);
$router->post('/payroll/runs/{id}/regenerate',       [App\Controllers\PayrollController::class, 'regenerate'],   ['auth', 'tenant']);
$router->get('/payroll/runs/{id}/payslip/{empId}',  [App\Controllers\PayrollController::class, 'payslip'],   ['auth', 'tenant']);
$router->get('/payroll/holidays',                   [App\Controllers\PayrollController::class, 'holidays'],     ['auth', 'tenant']);
$router->post('/payroll/holidays',                  [App\Controllers\PayrollController::class, 'storeHoliday'],  ['auth', 'tenant']);
$router->post('/payroll/holidays/{id}/delete',      [App\Controllers\PayrollController::class, 'deleteHoliday'],['auth', 'tenant']);
$router->get('/payroll/settings',                   [App\Controllers\PayrollController::class, 'settings'],     ['auth', 'tenant']);
$router->post('/payroll/settings/save',             [App\Controllers\PayrollController::class, 'saveSettings'],  ['auth', 'tenant']);
$router->post('/payroll/attendance/exempt',         [App\Controllers\PayrollController::class, 'exemptLate'],    ['auth', 'tenant']);
$router->get('/payroll/attendance',                 [App\Controllers\PayrollController::class, 'attendance'],   ['auth', 'tenant']);
$router->post('/payroll/attendance',                [App\Controllers\PayrollController::class, 'storeAttendance'], ['auth', 'tenant']);
$router->get('/payroll/audit',                      [App\Controllers\PayrollController::class, 'auditLogs'],    ['auth', 'tenant']);

// Legacy Users & Roles Fallbacks
$router->get('/users',                  [App\Controllers\StaffWorkspaceController::class, 'employees'],   ['auth', 'tenant']);
$router->get('/users/attendance',       [App\Controllers\StaffWorkspaceController::class, 'attendance'],  ['auth', 'tenant']);
$router->get('/users/create',           [UserController::class, 'create'],  ['auth', 'permission:create_users']);
$router->post('/users',                 [UserController::class, 'store'],   ['auth', 'permission:create_users']);
$router->get('/users/{id}/edit',        [UserController::class, 'edit'],    ['auth', 'permission:edit_users']);
$router->post('/users/{id}',            [UserController::class, 'update'],  ['auth', 'permission:edit_users']);
$router->delete('/users/{id}',          [UserController::class, 'destroy'], ['auth', 'permission:delete_users']);

// ─── Settings ─────────────────────────────────────────────────────────────────
$router->get('/settings',               [SettingsController::class, 'index'],  ['auth', 'permission:view_settings']);
$router->post('/settings',         [SettingsController::class, 'update'], ['auth', 'permission:edit_settings']);

// ─── Roles ────────────────────────────────────────────────────────────────────
$router->get('/roles',             [RoleController::class, 'index'],   ['auth', 'permission:view_roles']);
$router->get('/roles/create',      [RoleController::class, 'create'],  ['auth', 'permission:create_roles']);
$router->post('/roles',            [RoleController::class, 'store'],   ['auth', 'permission:create_roles']);
$router->get('/roles/{id}/edit',   [RoleController::class, 'edit'],    ['auth', 'permission:edit_roles']);
$router->post('/roles/{id}',       [RoleController::class, 'update'],  ['auth', 'permission:edit_roles']);
$router->delete('/roles/{id}',     [RoleController::class, 'destroy'], ['auth', 'permission:delete_roles']);

// ─── Students — Module 2 ─────────────────────────────────────────────────────
$router->get('/students/enrollments',              [EnrollmentAdminController::class, 'index'],    ['auth', 'permission:view_students']);
$router->get('/students/enrollments/{id}',         [EnrollmentAdminController::class, 'show'],     ['auth', 'permission:view_students']);
$router->post('/students/enrollments/{id}/approve', [EnrollmentAdminController::class, 'approve'],  ['auth', 'permission:create_students']);
$router->post('/students/enrollments/{id}/reject',  [EnrollmentAdminController::class, 'reject'],   ['auth', 'permission:create_students']);

// ─── Hierarchical Academics Module Routes (/academics/*) ──────────────────────
$router->get('/academics',                            [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth', 'tenant']);
$router->get('/academic',                             [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth', 'tenant']);
$router->get('/academics/students',                   [StudentController::class, 'index'],              ['auth', 'permission:view_students']);
$router->get('/academic/students',                    [StudentController::class, 'index'],              ['auth', 'permission:view_students']);
$router->get('/academics/students/create',            [StudentController::class, 'create'],             ['auth', 'permission:create_students']);
$router->get('/academic/students/create',             [StudentController::class, 'create'],             ['auth', 'permission:create_students']);
$router->post('/academics/students',                  [StudentController::class, 'store'],              ['auth', 'permission:create_students']);
$router->post('/academic/students',                   [StudentController::class, 'store'],              ['auth', 'permission:create_students']);
$router->get('/academics/students/{id}',              [StudentController::class, 'show'],               ['auth', 'permission:view_students']);
$router->get('/academic/students/{id}',               [StudentController::class, 'show'],               ['auth', 'permission:view_students']);
$router->get('/academics/students/{id}/edit',         [StudentController::class, 'edit'],               ['auth', 'permission:edit_students']);
$router->get('/academic/students/{id}/edit',          [StudentController::class, 'edit'],               ['auth', 'permission:edit_students']);
$router->post('/academics/students/{id}',             [StudentController::class, 'update'],             ['auth', 'permission:edit_students']);
$router->post('/academic/students/{id}',              [StudentController::class, 'update'],             ['auth', 'permission:edit_students']);
$router->post('/academics/students/{id}/subjects',    [StudentController::class, 'assignSubject'],      ['auth', 'permission:edit_students']);
$router->post('/academics/students/{id}/subjects/{subjectId}/delete', [StudentController::class, 'removeSubject'], ['auth', 'permission:edit_students']);
$router->get('/academics/admissions',                 [AdmissionsController::class, 'index'],           ['auth', 'tenant']);
$router->get('/academic/admissions',                  [AdmissionsController::class, 'index'],           ['auth', 'tenant']);
$router->get('/academics/classes',                    [ClassesController::class, 'index'],              ['auth', 'tenant']);
$router->post('/academics/classes',                   [ClassesController::class, 'store'],              ['auth', 'tenant']);
$router->get('/academics/classes/{id}',               [ClassesController::class, 'show'],               ['auth', 'tenant']);
$router->post('/academics/classes/{id}',              [ClassesController::class, 'update'],             ['auth', 'tenant']);
$router->post('/academics/classes/{id}/delete',       [ClassesController::class, 'destroy'],            ['auth', 'tenant']);
$router->post('/academics/classes/{id}/enroll',           [ClassesController::class, 'enrollStudents'],     ['auth', 'tenant']);
$router->post('/academics/classes/{classId}/remove-student/{studentId}', [ClassesController::class, 'removeStudent'], ['auth', 'tenant']);
$router->get('/academics/teachers',                   [UserController::class, 'index'],                 ['auth', 'permission:view_users']);
$router->get('/academics/attendance',                 [AttendanceController::class, 'index'],           ['auth', 'tenant']);
$router->get('/academics/attendance/leaves', [AttendanceController::class, 'leaves'], ['auth', 'tenant']);
$router->post('/academics/attendance/approve-leave/{id}', [AttendanceController::class, 'approveLeave'],    ['auth', 'tenant']);
$router->post('/academics/attendance/reject-leave/{id}',  [AttendanceController::class, 'rejectLeave'],     ['auth', 'tenant']);
$router->get('/academics/timetable',                  [TimetablesController::class, 'index'],           ['auth', 'tenant']);
$router->post('/academics/timetable/store',           [TimetablesController::class, 'store'],           ['auth', 'tenant']);
$router->post('/academics/timetable/{id}/update',      [TimetablesController::class, 'update'],          ['auth', 'tenant']);
$router->post('/academics/timetable/{id}/delete',      [TimetablesController::class, 'destroy'],         ['auth', 'tenant']);
$router->post('/academics/timetable/bulk-generate',   [TimetablesController::class, 'bulkGenerate'],    ['auth', 'tenant']);
$router->post('/academics/timetable/clear',           [TimetablesController::class, 'clearTimetable'],  ['auth', 'tenant']);
$router->get('/academics/subjects',                   [App\Controllers\SubjectsController::class, 'index'],              ['auth', 'tenant']);
$router->post('/academics/subjects',                  [App\Controllers\SubjectsController::class, 'store'],              ['auth', 'tenant']);
$router->post('/academics/subjects/{id}/delete',        [App\Controllers\SubjectsController::class, 'destroy'],            ['auth', 'tenant']);
$router->post('/academics/subjects/{id}',               [App\Controllers\SubjectsController::class, 'update'],             ['auth', 'tenant']);

$router->get('/academics/parents',                    [App\Controllers\GuardianController::class, 'index'],              ['auth', 'tenant']);
$router->get('/academics/parents/create',             [App\Controllers\GuardianController::class, 'create'],             ['auth', 'tenant']);
$router->post('/academics/parents',                   [App\Controllers\GuardianController::class, 'store'],              ['auth', 'tenant']);
$router->get('/academics/parents/{id}/edit',          [App\Controllers\GuardianController::class, 'edit'],               ['auth', 'tenant']);
$router->post('/academics/parents/{id}',              [App\Controllers\GuardianController::class, 'update'],             ['auth', 'tenant']);
$router->post('/academics/parents/{id}/delete',       [App\Controllers\GuardianController::class, 'destroy'],            ['auth', 'tenant']);

$router->get('/academics/assessments',                [ExamsController::class, 'bulkEntry'],                ['auth', 'tenant']);
$router->get('/academics/report-cards',               [ReportCardController::class, 'index'],           ['auth', 'permission:edit_students']);
$router->get('/academics/settings',                   [App\Controllers\AcademicSettingsController::class, 'index'],     ['auth', 'permission:view_settings']);
$router->post('/academics/settings/years',            [App\Controllers\AcademicSettingsController::class, 'storeYear'], ['auth', 'permission:edit_settings']);
$router->post('/academics/settings/years/{id}/lock',  [App\Controllers\AcademicSettingsController::class, 'lockYear'],  ['auth', 'permission:edit_settings']);
$router->post('/academics/settings/years/{id}/archive', [App\Controllers\AcademicSettingsController::class, 'archiveYear'], ['auth', 'permission:edit_settings']);
$router->post('/academics/settings/years/{id}/copy',   [App\Controllers\AcademicSettingsController::class, 'copyPreviousYear'], ['auth', 'permission:edit_settings']);
$router->post('/academics/settings/wizard/close-year', [App\Controllers\AcademicSettingsController::class, 'runYearClosingWizard'], ['auth', 'permission:edit_settings']);
$router->post('/academics/settings/save-attendance-settings', [App\Controllers\AcademicSettingsController::class, 'saveAttendanceSettings'], ['auth', 'permission:edit_settings']);
$router->get('/academics/lecture-attendance',         [App\Controllers\AcademicSettingsController::class, 'lectureAttendance'], ['auth', 'tenant']);

// Main Groups & Subject Master Routes
$router->get('/academics/main-groups',                [App\Controllers\AcademicSettingsController::class, 'mainGroupsIndex'], ['auth', 'tenant']);
$router->post('/academics/main-groups',               [App\Controllers\AcademicSettingsController::class, 'storeMainGroup'], ['auth', 'tenant']);
$router->post('/academics/main-groups/{id}',          [App\Controllers\AcademicSettingsController::class, 'updateMainGroup'], ['auth', 'tenant']);
$router->get('/academics/subject-master',             [App\Controllers\AcademicSettingsController::class, 'subjectMasterIndex'], ['auth', 'tenant']);
$router->post('/academics/subject-master',            [App\Controllers\AcademicSettingsController::class, 'storeSubject'], ['auth', 'tenant']);
$router->post('/academics/subject-master/{id}',       [App\Controllers\AcademicSettingsController::class, 'updateSubject'], ['auth', 'tenant']);
$router->post('/academics/subject-master/{id}/delete', [App\Controllers\AcademicSettingsController::class, 'destroySubject'], ['auth', 'tenant']);

// ─── Global System Settings & Reports ───────────────────────────────────────────
$router->get('/reports',                        [\App\Controllers\ReportsController::class, 'index'],     ['auth', 'tenant']);
$router->get('/reports/finance',                [\App\Controllers\ReportsController::class, 'finance'],   ['auth', 'tenant']);
$router->get('/reports/students',               [\App\Controllers\ReportsController::class, 'students'],  ['auth', 'tenant']);
$router->get('/reports/staff',                  [\App\Controllers\ReportsController::class, 'staff'],     ['auth', 'tenant']);
$router->get('/reports/communication',           [\App\Controllers\ReportsController::class, 'communication'], ['auth', 'tenant']);
$router->get('/settings',                       [\App\Controllers\SettingsController::class, 'index'],    ['auth', 'tenant']);
$router->get('/settings/general',               [\App\Controllers\SettingsController::class, 'index'],    ['auth', 'tenant']);
$router->get('/settings/school',                [\App\Controllers\SettingsController::class, 'school'],   ['auth', 'tenant']);
$router->get('/settings/integrations',          [\App\Controllers\SettingsController::class, 'integrations'],['auth', 'tenant']);
$router->get('/settings/system',                [\App\Controllers\SettingsController::class, 'system'],   ['auth', 'tenant']);
$router->post('/settings',                      [\App\Controllers\SettingsController::class, 'update'],   ['auth', 'tenant']);
$router->post('/settings/update',               [\App\Controllers\SettingsController::class, 'update'],   ['auth', 'tenant']);
$router->post('/settings/test-whatsapp',        [\App\Controllers\SettingsController::class, 'testWhatsApp'],['auth', 'tenant']);

// Curriculum Templates Manager Routes
$router->get('/academics/curriculum',                 [App\Controllers\CurriculumManagerController::class, 'index'], ['auth', 'tenant']);
$router->post('/academics/curriculum/templates',      [App\Controllers\CurriculumManagerController::class, 'storeTemplate'], ['auth', 'tenant']);
$router->get('/academics/curriculum/templates/{id}',  [App\Controllers\CurriculumManagerController::class, 'showTemplate'], ['auth', 'tenant']);
$router->post('/academics/curriculum/templates/{id}/sections', [App\Controllers\CurriculumManagerController::class, 'storeSection'], ['auth', 'tenant']);
$router->post('/academics/curriculum/subjects/add',             [App\Controllers\CurriculumManagerController::class, 'storeCurriculumSubject'], ['auth', 'tenant']);
$router->post('/academics/curriculum/subjects/{id}/delete',    [App\Controllers\CurriculumManagerController::class, 'destroyCurriculumSubject'], ['auth', 'tenant']);
$router->post('/academics/curriculum/reorder',        [App\Controllers\CurriculumManagerController::class, 'reorderSubjects'], ['auth', 'tenant']);

// Promotion Wizard Routes
$router->get('/academics/promotion',                  [App\Controllers\PromotionController::class, 'index'], ['auth', 'tenant']);
$router->post('/academics/promotion/run',             [App\Controllers\PromotionController::class, 'promoteStudents'], ['auth', 'tenant']);

// Legacy fallbacks
$router->get('/academic', [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth', 'tenant']);

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
$router->get('/fees/export',                [FeeController::class, 'exportCsv'],      ['auth', 'tenant']);
$router->post('/fees/{id}/pay',             [FeeController::class, 'recordPayment'],  ['auth', 'tenant']);

// Invoices CRUD
$router->get('/fees/invoices',              [\App\Controllers\FeeInvoiceController::class, 'index'],   ['auth', 'tenant']);
$router->get('/fees/invoices/create',       [\App\Controllers\FeeInvoiceController::class, 'create'],  ['auth', 'tenant']);
$router->post('/fees/invoices',             [\App\Controllers\FeeInvoiceController::class, 'store'],   ['auth', 'tenant']);
$router->get('/fees/invoices/{id}',         [\App\Controllers\FeeInvoiceController::class, 'show'],    ['auth', 'tenant']);
$router->get('/fees/invoices/{id}/edit',    [\App\Controllers\FeeInvoiceController::class, 'edit'],    ['auth', 'tenant']);
$router->post('/fees/invoices/{id}',        [\App\Controllers\FeeInvoiceController::class, 'update'],  ['auth', 'tenant']);
$router->post('/fees/invoices/{id}/delete', [\App\Controllers\FeeInvoiceController::class, 'destroy'], ['auth', 'tenant']);

// Fee Categories
$router->get('/fees/categories',            [\App\Controllers\FeeCategoryController::class, 'index'],   ['auth', 'tenant']);
$router->post('/fees/categories',           [\App\Controllers\FeeCategoryController::class, 'store'],   ['auth', 'tenant']);
$router->post('/fees/categories/{id}',      [\App\Controllers\FeeCategoryController::class, 'update'],  ['auth', 'tenant']);
$router->post('/fees/categories/{id}/delete',[\App\Controllers\FeeCategoryController::class, 'destroy'],['auth', 'tenant']);

// Late Fee Policies
$router->get('/fees/late-fee-policies',            [\App\Controllers\LateFeePolicyController::class, 'index'],   ['auth', 'tenant']);
$router->post('/fees/late-fee-policies',           [\App\Controllers\LateFeePolicyController::class, 'store'],   ['auth', 'tenant']);
$router->post('/fees/late-fee-policies/{id}',      [\App\Controllers\LateFeePolicyController::class, 'update'],  ['auth', 'tenant']);
$router->post('/fees/late-fee-policies/{id}/delete',[\App\Controllers\LateFeePolicyController::class, 'destroy'],['auth', 'tenant']);

// Fee Structures & Items
$router->get('/fees/structures',                   [\App\Controllers\FeeStructureController::class, 'index'],    ['auth', 'tenant']);
$router->post('/fees/structures',                  [\App\Controllers\FeeStructureController::class, 'store'],    ['auth', 'tenant']);
$router->get('/fees/structures/{id}',              [\App\Controllers\FeeStructureController::class, 'show'],     ['auth', 'tenant']);
$router->post('/fees/structures/{id}/items',       [\App\Controllers\FeeStructureController::class, 'addItem'],  ['auth', 'tenant']);
$router->post('/fees/structures/{id}/items/{itemId}/delete', [\App\Controllers\FeeStructureController::class, 'destroyItem'], ['auth', 'tenant']);

// Batch Fee Generator
$router->get('/fees/batch-generator',              [\App\Controllers\BatchFeeGeneratorController::class, 'index'], ['auth', 'tenant']);
$router->post('/fees/batch-generator',             [\App\Controllers\BatchFeeGeneratorController::class, 'generate'], ['auth', 'tenant']);

// ─── Receipts — Admin ─────────────────────────────────────────────────────────
$router->get('/receipts', [ReceiptsController::class, 'index'], ['auth', 'tenant']);
$router->get('/receipts/settings', [ReceiptsController::class, 'settings'], ['auth', 'tenant']);
$router->post('/receipts/settings', [ReceiptsController::class, 'saveSettings'], ['auth', 'tenant']);
$router->get('/receipts/{id}/view', [ReceiptsController::class, 'show'], ['auth', 'tenant']);

// ─── Scholarships — Admin ─────────────────────────────────────────────────────



// ─── Transport Management Workspace (/transport/*) ───────────────────────────
$router->get('/transport',                      [TransportController::class, 'overview'],            ['auth', 'tenant']);
$router->get('/transport/overview',             [TransportController::class, 'overview'],            ['auth', 'tenant']);
$router->get('/transport/routes',               [TransportController::class, 'routes'],              ['auth', 'tenant']);
$router->get('/transport/vehicles',             [TransportController::class, 'vehicles'],            ['auth', 'tenant']);
$router->get('/transport/drivers',              [TransportController::class, 'drivers'],             ['auth', 'tenant']);
$router->get('/transport/student-assignments',  [TransportController::class, 'studentAssignments'],   ['auth', 'tenant']);
$router->post('/transport/{id}/assign',     [TransportController::class, 'assignStudent'],['auth', 'tenant']);
$router->post('/transport/assignments/{id}/update', [TransportController::class, 'updateAssignment'], ['auth', 'tenant']);
$router->post('/transport/assignments/{id}/remove', [TransportController::class, 'removeAssignment'], ['auth', 'tenant']);
$router->get('/transport/live-tracking',        [TransportController::class, 'tracking'],           ['auth', 'tenant']);
$router->get('/transport/logs',                 [TransportController::class, 'viewLogs'],           ['auth', 'tenant']);
$router->get('/transport/settings',             [TransportController::class, 'settings'],           ['auth', 'tenant']);
$router->post('/transport/settings',            [TransportController::class, 'storeSettings'],      ['auth', 'tenant']);

$router->get('/transport/create',           [TransportController::class, 'create'],   ['auth', 'tenant']);
$router->get('/transport/tracking',         [TransportController::class, 'tracking'], ['auth', 'tenant']);
$router->get('/transport/live-data',        [TransportController::class, 'liveData'], ['auth', 'tenant']);
$router->post('/transport',                 [TransportController::class, 'store'],    ['auth', 'tenant']);
$router->get('/transport/{id}/edit',        [TransportController::class, 'edit'],     ['auth', 'tenant']);
$router->post('/transport/{id}',            [TransportController::class, 'update'],    ['auth', 'tenant']);
$router->post('/transport/{id}/assign',     [TransportController::class, 'assignStudent'],['auth', 'tenant']);
$router->post('/transport/{id}/delete',     [TransportController::class, 'destroy'],   ['auth', 'tenant']);


// ─── Certificate Designer — Admin ──────────────────────────────────────────────
$router->get('/certificates',               [CertificateController::class, 'index'],  ['auth', 'tenant']);
$router->get('/certificates/create',        [CertificateController::class, 'create'], ['auth', 'tenant']);
$router->post('/certificates',              [CertificateController::class, 'store'],  ['auth', 'tenant']);
$router->get('/certificates/{id}/view',     [CertificateController::class, 'show'],   ['auth', 'tenant']);
$router->post('/certificates/{id}/delete',  [CertificateController::class, 'destroy'],['auth', 'tenant']);

// ─── Finance & Fee Management ────────────────────────────────────────────────
$router->get('/fees',                         [FeeController::class, 'index'],            ['auth', 'tenant']);
$router->get('/fees/create',                  [FeeController::class, 'create'],           ['auth', 'tenant']);
$router->post('/fees',                        [FeeController::class, 'store'],            ['auth', 'tenant']);
$router->get('/fees/export-csv',              [FeeController::class, 'exportCsv'],        ['auth', 'tenant']);
$router->post('/fees/{id}/pay',               [FeeController::class, 'recordPayment'],    ['auth', 'tenant']);
$router->post('/fees/{id}/delete',            [FeeController::class, 'destroy'],          ['auth', 'tenant']);

$router->get('/fees/structures',              [App\Controllers\FeeStructureController::class, 'index'], ['auth', 'tenant']);
$router->post('/fees/structures',             [App\Controllers\FeeStructureController::class, 'store'], ['auth', 'tenant']);

$router->get('/fees/categories',              [App\Controllers\FeeCategoryController::class, 'index'],  ['auth', 'tenant']);
$router->post('/fees/categories',             [App\Controllers\FeeCategoryController::class, 'store'],  ['auth', 'tenant']);

$router->get('/fees/batch-generator',         [App\Controllers\BatchFeeGeneratorController::class, 'index'], ['auth', 'tenant']);
$router->post('/fees/batch-generator',        [App\Controllers\BatchFeeGeneratorController::class, 'generate'], ['auth', 'tenant']);

$router->get('/fees/late-fee-policies',       [App\Controllers\LateFeePolicyController::class, 'index'], ['auth', 'tenant']);
$router->post('/fees/late-fee-policies',      [App\Controllers\LateFeePolicyController::class, 'store'], ['auth', 'tenant']);

$router->get('/receipts',                     [ReceiptsController::class, 'index'],          ['auth', 'tenant']);
$router->get('/receipts/settings',            [ReceiptsController::class, 'settings'],       ['auth', 'tenant']);
$router->post('/receipts/settings',           [ReceiptsController::class, 'saveSettings'],   ['auth', 'tenant']);
$router->get('/receipts/{id}/view',           [ReceiptsController::class, 'show'],           ['auth', 'tenant']);

// ─── Admissions ──────────────────────────────────────────────────────────────
$router->get('/admissions', [AdmissionsController::class, 'index'], ['auth', 'tenant']);
$router->post('/admissions/{id}/status', [AdmissionsController::class, 'updateStatus'], ['auth', 'tenant']);

// ─── Announcements ───────────────────────────────────────────────────────────
$router->get('/academics/announcements', [AnnouncementController::class, 'index'], ['auth', 'tenant']);
$router->post('/academics/announcements', [AnnouncementController::class, 'store'], ['auth', 'tenant']);
$router->post('/academics/announcements/{id}/delete', [AnnouncementController::class, 'destroy'], ['auth', 'tenant']);

// ─── Classes & Sections ───────────────────────────────────────────────────────
$router->get('/classes', [ClassesController::class, 'index'], ['auth', 'tenant']);
$router->post('/classes', [ClassesController::class, 'store'], ['auth', 'tenant']);
$router->post('/classes/delete', [ClassesController::class, 'destroy'], ['auth', 'tenant']);
$router->get('/classes/{class}', [ClassesController::class, 'show'], ['auth', 'tenant']);

// ─── Attendance ──────────────────────────────────────────────────────────────
$router->get('/student-attendance', [AttendanceController::class, 'index'], ['auth', 'tenant']);
$router->post('/student-attendance/save', [AttendanceController::class, 'save'], ['auth', 'tenant']);
$router->get('/attendance', [AttendanceController::class, 'index'], ['auth', 'tenant']);
$router->post('/attendance/save', [AttendanceController::class, 'save'], ['auth', 'tenant']);
$router->get('/attendance/face-kiosk',    [App\Controllers\FaceRecognitionController::class, 'kioskView']);
$router->post('/attendance/face-kiosk',   [App\Controllers\FaceRecognitionController::class, 'kioskView']);
$router->get('/attendance/face-register', [App\Controllers\FaceRecognitionController::class, 'registerView']);
$router->post('/attendance/face-register',[App\Controllers\FaceRecognitionController::class, 'registerView']);
$router->get('/attendance/face-history',  [App\Controllers\FaceRecognitionController::class, 'historyView']);

// ─── Timetables ──────────────────────────────────────────────────────────────
$router->get('/timetables', [TimetablesController::class, 'index'], ['auth', 'tenant']);
$router->post('/timetables/store', [TimetablesController::class, 'store'], ['auth', 'tenant']);

// ─── Exams & Grades ──────────────────────────────────────────────────────────
$router->get('/exams', [ExamsController::class, 'index'], ['auth', 'tenant']);
$router->get('/exams/bulk-entry', [ExamsController::class, 'bulkEntry'], ['auth', 'tenant']);
$router->post('/exams/bulk-save', [ExamsController::class, 'bulkSave'], ['auth', 'tenant']);
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
$router->get('/parent/impersonate/{id}',           [ParentPortalController::class, 'impersonate'],   ['auth']);
$router->get('/parent/dashboard',                  [ParentPortalController::class, 'dashboard'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/attendance',   [ParentPortalController::class, 'attendance'],    ['auth', 'role:parent']);
$router->get('/parent/students/{id}/attendance/check-date', [ParentPortalController::class, 'checkDateAttendance'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/attendance/declare', [ParentPortalController::class, 'submitTomorrowAttendance'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/attendance/mark-absent', [ParentPortalController::class, 'markAbsent'], ['auth', 'role:parent']);
$router->post('/parent/students/{id}/attendance/request-leave', [ParentPortalController::class, 'requestLeave'], ['auth', 'role:parent']);
$router->get('/parent/students/{id}/timetable',    [ParentPortalController::class, 'timetable'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/exams',        [ParentPortalController::class, 'exams'],         ['auth', 'role:parent']);
$router->get('/parent/students/{id}/certificates', [ParentPortalController::class, 'certificates'],  ['auth', 'role:parent']);
$router->get('/parent/certificates/{id}/download',   [ParentPortalController::class, 'downloadCertificate'], ['auth']);
$router->get('/parent/students/{id}/report-card',  [ReportCardController::class, 'parentShow'],      ['auth', 'role:parent']);

$router->get('/parent/students/{id}/transport',    [ParentPortalController::class, 'transport'],     ['auth', 'role:parent']);
$router->get('/parent/students/{id}/live-eta',     [ParentPortalController::class, 'liveEta'],       ['auth', 'role:parent']);
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

$router->get('/uploads/homework/{file}', function ($file) {
    $path = STORAGE_PATH . '/uploads/homework/' . $file;
    if (!file_exists($path)) {
        $path = ROOT_PATH . '/test_cert.pdf';
    }
    if (file_exists($path)) {
        $mime = mime_content_type($path);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($path);
        exit();
    }
    \Core\Application::$app->response->abort(404);
});

$router->get('/transport/driver/create',    [TransportController::class, 'createDriver'], ['auth', 'tenant']);
$router->post('/transport/driver',          [TransportController::class, 'storeDriver'],  ['auth', 'tenant']);

// ─── Documents Workspace ──────────────────────────────────────────────────────
$router->get('/documents',                     [App\Controllers\DocumentsController::class, 'dashboard'],         ['auth', 'tenant']);
$router->get('/documents/dashboard',           [App\Controllers\DocumentsController::class, 'dashboard'],         ['auth', 'tenant']);
$router->get('/documents/student-documents',   [App\Controllers\DocumentsController::class, 'studentDocuments'],  ['auth', 'tenant']);
$router->post('/documents/student-documents/upload', [App\Controllers\DocumentsController::class, 'uploadStudentDoc'],['auth', 'tenant']);
$router->get('/documents/staff-documents',     [App\Controllers\DocumentsController::class, 'staffDocuments'],    ['auth', 'tenant']);
$router->get('/documents/parent-documents',    [App\Controllers\DocumentsController::class, 'parentDocuments'],   ['auth', 'tenant']);
$router->get('/documents/driver-documents',    [App\Controllers\DocumentsController::class, 'driverDocuments'],   ['auth', 'tenant']);
$router->get('/documents/generated',           [App\Controllers\DocumentsController::class, 'generated'],          ['auth', 'tenant']);
$router->get('/documents/templates',           [App\Controllers\DocumentsController::class, 'templates'],          ['auth', 'tenant']);
$router->get('/documents/settings',            [App\Controllers\DocumentsController::class, 'settings'],           ['auth', 'tenant']);
$router->post('/transport/{id}/location', [TransportController::class, 'updateLocation'],['auth', 'tenant']);
