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
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('/games',     [DashboardController::class, 'games'], ['auth']);
$router->get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard'], ['auth', 'role:teacher']);
$router->get('/teacher/attendance', [TeacherPortalController::class, 'markAttendance'], ['auth', 'role:teacher']);
$router->post('/teacher/attendance/save', [TeacherPortalController::class, 'saveTeacherAttendance'], ['auth', 'role:teacher']);

// ─── Staff Management Workspace (/staff/*) ──────────────────────────────────
$router->get('/staff',                  [App\Controllers\StaffWorkspaceController::class, 'overview'],    ['auth', 'permission:view_staff_overview']);
$router->get('/staff/overview',         [App\Controllers\StaffWorkspaceController::class, 'overview'],    ['auth', 'permission:view_staff_overview']);
$router->get('/staff/employees',        [App\Controllers\StaffWorkspaceController::class, 'employees'],   ['auth', 'permission:view_staff_directory']);
$router->post('/staff/employees',       [App\Controllers\StaffWorkspaceController::class, 'storeEmployee'],['auth', 'permission:create_staff_directory']);
$router->get('/staff/employees/{id}',   [App\Controllers\StaffWorkspaceController::class, 'showEmployee'],['auth', 'permission:view_staff_directory']);
$router->post('/staff/employees/{id}',  [App\Controllers\StaffWorkspaceController::class, 'updateEmployee'],['auth', 'permission:edit_staff_directory']);
$router->get('/staff/departments',      [App\Controllers\StaffWorkspaceController::class, 'departments'], ['auth', 'permission:view_departments']);
$router->post('/staff/departments',     [App\Controllers\StaffWorkspaceController::class, 'storeDepartment'],['auth', 'permission:create_departments']);
$router->post('/staff/departments/{id}/update', [App\Controllers\StaffWorkspaceController::class, 'updateDepartment'], ['auth', 'permission:edit_departments']);
$router->post('/staff/departments/{id}/delete', [App\Controllers\StaffWorkspaceController::class, 'deleteDepartment'], ['auth', 'permission:delete_departments']);
$router->get('/staff/designations',     [App\Controllers\StaffWorkspaceController::class, 'designations'],['auth', 'permission:view_designations']);
$router->post('/staff/designations',    [App\Controllers\StaffWorkspaceController::class, 'storeDesignation'],['auth', 'permission:create_designations']);
$router->post('/staff/designations/{id}/update', [App\Controllers\StaffWorkspaceController::class, 'updateDesignation'], ['auth', 'permission:edit_designations']);
$router->post('/staff/designations/{id}/delete', [App\Controllers\StaffWorkspaceController::class, 'deleteDesignation'], ['auth', 'permission:delete_designations']);
$router->get('/staff/attendance',       [App\Controllers\StaffWorkspaceController::class, 'attendance'],  ['auth', 'permission:view_staff_attendance']);
$router->get('/staff/attendance/lectures', [App\Controllers\StaffWorkspaceController::class, 'lectureAttendance'], ['auth', 'permission:view_staff_attendance']);
$router->post('/staff/attendance',      [App\Controllers\StaffWorkspaceController::class, 'storeAttendance'],['auth', 'permission:create_staff_attendance']);
$router->get('/staff/leaves',           [App\Controllers\StaffWorkspaceController::class, 'leaves'],      ['auth', 'permission:view_leave_management']);
$router->post('/staff/leaves',          [App\Controllers\StaffWorkspaceController::class, 'storeLeave'], ['auth', 'permission:create_leave_management']);
$router->get('/staff/payroll',          [App\Controllers\StaffWorkspaceController::class, 'payroll'],     ['auth', 'permission:view_payroll_runs']);
$router->post('/staff/payroll/run',     [App\Controllers\StaffWorkspaceController::class, 'runPayroll'],   ['auth', 'permission:create_payroll_runs']);
$router->get('/staff/payroll/{id}',     [App\Controllers\StaffWorkspaceController::class, 'payrollDetails'],['auth', 'permission:view_payroll_runs']);
$router->get('/staff/payroll/{id}/payslip/{empId}', [App\Controllers\StaffWorkspaceController::class, 'payslip'],['auth', 'permission:view_payroll_runs']);
$router->get('/staff/users',            [App\Controllers\StaffWorkspaceController::class, 'users'],       ['auth', 'permission:view_staff_user_accounts']);
$router->get('/staff/settings',         [App\Controllers\StaffWorkspaceController::class, 'settings'],    ['auth', 'permission:view_staff_settings']);
$router->post('/staff/settings',        [App\Controllers\StaffWorkspaceController::class, 'saveSettings'],['auth', 'permission:edit_staff_settings']);
$router->get('/staff/roles',            [App\Controllers\StaffWorkspaceController::class, 'roles'],       ['auth', 'permission:view_staff_roles']);
$router->post('/staff/roles',           [App\Controllers\StaffWorkspaceController::class, 'storeRole'],   ['auth', 'permission:create_staff_roles']);
$router->post('/staff/roles/{id}/update', [App\Controllers\StaffWorkspaceController::class, 'updateRole'], ['auth', 'permission:edit_staff_roles']);
$router->post('/staff/roles/{id}/delete', [App\Controllers\StaffWorkspaceController::class, 'deleteRole'], ['auth', 'permission:delete_staff_roles']);

// ─── Payroll Management Workspace (/payroll/*) ────────────────────────────────
$router->get('/payroll',                            [App\Controllers\PayrollController::class, 'runs'],         ['auth', 'permission:view_payroll_runs']);
$router->get('/payroll/runs',                       [App\Controllers\PayrollController::class, 'runs'],         ['auth', 'permission:view_payroll_runs']);
$router->post('/payroll/runs/run',                  [App\Controllers\PayrollController::class, 'runPayroll'],   ['auth', 'permission:create_payroll_runs']);
$router->get('/payroll/runs/{id}',                  [App\Controllers\PayrollController::class, 'runDetails'],   ['auth', 'permission:view_payroll_runs']);
$router->post('/payroll/runs/{id}/regenerate',       [App\Controllers\PayrollController::class, 'regenerate'],   ['auth', 'permission:edit_payroll_runs']);
$router->get('/payroll/runs/{id}/payslip/{empId}',  [App\Controllers\PayrollController::class, 'payslip'],   ['auth', 'permission:view_payroll_runs']);
$router->get('/payroll/holidays',                   [App\Controllers\PayrollController::class, 'holidays'],     ['auth', 'permission:view_holidays_calendar']);
$router->post('/payroll/holidays',                  [App\Controllers\PayrollController::class, 'storeHoliday'],  ['auth', 'permission:create_holidays_calendar']);
$router->post('/payroll/holidays/{id}/delete',      [App\Controllers\PayrollController::class, 'deleteHoliday'],['auth', 'permission:delete_holidays_calendar']);
$router->get('/payroll/settings',                   function() { header('Location: /staff/settings'); exit; },     ['auth', 'permission:view_staff_settings']);
$router->post('/payroll/settings/save',             function() { header('Location: /staff/settings'); exit; },  ['auth', 'permission:edit_staff_settings']);
$router->post('/payroll/attendance/exempt',         [App\Controllers\PayrollController::class, 'exemptLate'],    ['auth', 'permission:edit_payroll_attendance']);
$router->get('/payroll/attendance',                 [App\Controllers\PayrollController::class, 'attendance'],   ['auth', 'permission:view_payroll_attendance']);
$router->post('/payroll/attendance',                [App\Controllers\PayrollController::class, 'storeAttendance'], ['auth', 'permission:edit_payroll_attendance']);
$router->get('/payroll/audit',                      [App\Controllers\PayrollController::class, 'auditLogs'],    ['auth', 'permission:view_payroll_audit']);

// Legacy Users & Roles Fallbacks
$router->get('/users',                  [App\Controllers\StaffWorkspaceController::class, 'employees'],   ['auth', 'permission:view_staff_directory']);
$router->get('/users/attendance',       [App\Controllers\StaffWorkspaceController::class, 'attendance'],  ['auth', 'permission:view_staff_attendance']);
$router->get('/users/create',           [UserController::class, 'create'],  ['auth', 'permission:create_staff_user_accounts']);
$router->post('/users',                 [UserController::class, 'store'],   ['auth', 'permission:create_staff_user_accounts']);
$router->get('/users/{id}/edit',        [UserController::class, 'edit'],    ['auth', 'permission:edit_staff_user_accounts']);
$router->post('/users/{id}',            [UserController::class, 'update'],  ['auth', 'permission:edit_staff_user_accounts']);
$router->delete('/users/{id}',          [UserController::class, 'destroy'], ['auth', 'permission:delete_staff_user_accounts']);

// ─── Settings ─────────────────────────────────────────────────────────────────
$router->get('/settings',               [SettingsController::class, 'index'],  ['auth', 'permission:view_general_settings']);
$router->post('/settings',         [SettingsController::class, 'update'], ['auth', 'permission:edit_general_settings']);

// ─── Roles ────────────────────────────────────────────────────────────────────
$router->get('/roles',             [RoleController::class, 'index'],   ['auth', 'permission:view_staff_roles']);
$router->get('/roles/create',      [RoleController::class, 'create'],  ['auth', 'permission:create_staff_roles']);
$router->post('/roles',            [RoleController::class, 'store'],   ['auth', 'permission:create_staff_roles']);
$router->get('/roles/{id}/edit',   [RoleController::class, 'edit'],    ['auth', 'permission:edit_staff_roles']);
$router->post('/roles/{id}',       [RoleController::class, 'update'],  ['auth', 'permission:edit_staff_roles']);
$router->delete('/roles/{id}',     [RoleController::class, 'destroy'], ['auth', 'permission:delete_staff_roles']);

// ─── Students — Module 2 ─────────────────────────────────────────────────────
$router->get('/students/enrollments',              [EnrollmentAdminController::class, 'index'],    ['auth', 'permission:view_enrollments']);
$router->get('/students/enrollments/{id}',         [EnrollmentAdminController::class, 'show'],     ['auth', 'permission:view_enrollments']);
$router->post('/students/enrollments/{id}/approve', [EnrollmentAdminController::class, 'approve'],  ['auth', 'permission:approve_enrollments']);
$router->post('/students/enrollments/{id}/reject',  [EnrollmentAdminController::class, 'reject'],   ['auth', 'permission:reject_enrollments']);
$router->post('/students/enrollments/{id}/quick-enroll', [EnrollmentAdminController::class, 'quickEnroll'], ['auth', 'permission:approve_enrollments']);

// ─── Hierarchical Academics Module Routes (/academics/*) ──────────────────────
$router->get('/academics',                            [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth']);
$router->get('/academic',                             [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth']);
$router->get('/academics/students',                   [StudentController::class, 'index'],              ['auth', 'permission:view_students_list']);
$router->get('/academic/students',                    [StudentController::class, 'index'],              ['auth', 'permission:view_students_list']);
$router->get('/academics/students/create',            [StudentController::class, 'create'],             ['auth', 'permission:create_students_list']);
$router->get('/academic/students/create',             [StudentController::class, 'create'],             ['auth', 'permission:create_students_list']);
$router->post('/academics/students',                  [StudentController::class, 'store'],              ['auth', 'permission:create_students_list']);
$router->post('/academic/students',                   [StudentController::class, 'store'],              ['auth', 'permission:create_students_list']);
$router->get('/academics/students/{id}',              [StudentController::class, 'show'],               ['auth', 'permission:view_students_list']);
$router->get('/academic/students/{id}',               [StudentController::class, 'show'],               ['auth', 'permission:view_students_list']);
$router->get('/academics/students/{id}/edit',         [StudentController::class, 'edit'],               ['auth', 'permission:edit_students_list']);
$router->get('/academic/students/{id}/edit',          [StudentController::class, 'edit'],               ['auth', 'permission:edit_students_list']);
$router->post('/academics/students/{id}',             [StudentController::class, 'update'],             ['auth', 'permission:edit_students_list']);
$router->post('/academic/students/{id}',              [StudentController::class, 'update'],             ['auth', 'permission:edit_students_list']);
$router->post('/academics/students/{id}/subjects',    [StudentController::class, 'assignSubject'],      ['auth', 'permission:edit_students_list']);
$router->post('/academics/students/{id}/subjects/{subjectId}/delete', [StudentController::class, 'removeSubject'], ['auth', 'permission:edit_students_list']);
$router->get('/academics/admissions',                 [AdmissionsController::class, 'index'],           ['auth', 'permission:view_enrollments']);
$router->get('/academic/admissions',                  [AdmissionsController::class, 'index'],           ['auth', 'permission:view_enrollments']);
$router->get('/academics/classes',                    [ClassesController::class, 'index'],              ['auth', 'permission:view_classes']);
$router->post('/academics/classes',                   [ClassesController::class, 'store'],              ['auth', 'permission:create_classes']);
$router->get('/academics/classes/{id}',               [ClassesController::class, 'show'],               ['auth', 'permission:view_classes']);
$router->post('/academics/classes/{id}',              [ClassesController::class, 'update'],             ['auth', 'permission:edit_classes']);
$router->post('/academics/classes/{id}/delete',       [ClassesController::class, 'destroy'],            ['auth', 'permission:delete_classes']);
$router->post('/academics/classes/{id}/enroll',           [ClassesController::class, 'enrollStudents'],     ['auth', 'permission:edit_classes']);
$router->post('/academics/classes/{classId}/remove-student/{studentId}', [ClassesController::class, 'removeStudent'], ['auth', 'permission:edit_classes']);
$router->get('/academics/teachers',                   [UserController::class, 'index'],                 ['auth', 'permission:view_teachers']);
$router->get('/academics/attendance',                 [AttendanceController::class, 'index'],           ['auth', 'permission:view_acad_attendance']);
$router->get('/academics/attendance/leaves', [AttendanceController::class, 'leaves'], ['auth', 'permission:view_acad_attendance']);
$router->post('/academics/attendance/approve-leave/{id}', [AttendanceController::class, 'approveLeave'],    ['auth', 'permission:edit_acad_attendance']);
$router->post('/academics/attendance/reject-leave/{id}',  [AttendanceController::class, 'rejectLeave'],     ['auth', 'permission:edit_acad_attendance']);
$router->get('/academics/timetable',                  [TimetablesController::class, 'index'],           ['auth', 'permission:view_timetable']);
$router->post('/academics/timetable/store',           [TimetablesController::class, 'store'],           ['auth', 'permission:create_timetable']);
$router->post('/academics/timetable/{id}/update',      [TimetablesController::class, 'update'],          ['auth', 'permission:edit_timetable']);
$router->post('/academics/timetable/{id}/delete',      [TimetablesController::class, 'destroy'],         ['auth', 'permission:delete_timetable']);
$router->post('/academics/timetable/bulk-generate',   [TimetablesController::class, 'bulkGenerate'],    ['auth', 'permission:create_timetable']);
$router->post('/academics/timetable/clear',           [TimetablesController::class, 'clearTimetable'],  ['auth', 'permission:delete_timetable']);
$router->post('/academics/timetable/share',           [TimetablesController::class, 'share'],           ['auth', 'permission:edit_timetable']);
$router->get('/academics/subjects',                   [App\Controllers\SubjectsController::class, 'index'],              ['auth', 'permission:view_subjects']);
$router->post('/academics/subjects',                  [App\Controllers\SubjectsController::class, 'store'],              ['auth', 'permission:create_subjects']);
$router->post('/academics/subjects/{id}/delete',        [App\Controllers\SubjectsController::class, 'destroy'],            ['auth', 'permission:delete_subjects']);
$router->post('/academics/subjects/{id}',               [App\Controllers\SubjectsController::class, 'update'],             ['auth', 'permission:edit_subjects']);
$router->post('/academics/subject-types',              [App\Controllers\SubjectTypesController::class, 'store'],         ['auth', 'permission:create_subjects']);
$router->post('/academics/subject-types/{id}',         [App\Controllers\SubjectTypesController::class, 'update'],        ['auth', 'permission:edit_subjects']);
$router->post('/academics/subject-types/{id}/delete',  [App\Controllers\SubjectTypesController::class, 'destroy'],       ['auth', 'permission:delete_subjects']);

$router->get('/academics/parents',                    [App\Controllers\GuardianController::class, 'index'],              ['auth', 'permission:view_student_parents']);
$router->get('/academics/parents/create',             [App\Controllers\GuardianController::class, 'create'],             ['auth', 'permission:create_student_parents']);
$router->post('/academics/parents',                   [App\Controllers\GuardianController::class, 'store'],              ['auth', 'permission:create_student_parents']);
$router->get('/academics/parents/{id}/edit',          [App\Controllers\GuardianController::class, 'edit'],               ['auth', 'permission:edit_student_parents']);
$router->post('/academics/parents/{id}',              [App\Controllers\GuardianController::class, 'update'],             ['auth', 'permission:edit_student_parents']);
$router->post('/academics/parents/{id}/delete',       [App\Controllers\GuardianController::class, 'destroy'],            ['auth', 'permission:delete_student_parents']);

$router->get('/academics/assessments',                [ExamsController::class, 'bulkEntry'],                ['auth', 'permission:view_exams']);
$router->post('/academics/assessments/bulk-save',     [ExamsController::class, 'bulkSave'],                 ['auth', 'permission:edit_exams']);
$router->get('/academics/exams',                      [ExamsController::class, 'index'],                    ['auth', 'permission:view_exams']);
$router->post('/academics/exams/store',               [ExamsController::class, 'store'],                    ['auth', 'permission:create_exams']);
$router->get('/academics/exams/create',             [ExamsController::class, 'create'],                   ['auth', 'permission:create_exams']);
$router->get('/academics/exams/{id}/edit',            [ExamsController::class, 'edit'],                     ['auth', 'permission:edit_exams']);
$router->post('/academics/exams/{id}/update',         [ExamsController::class, 'update'],                   ['auth', 'permission:edit_exams']);
$router->post('/academics/exams/{id}/delete',         [ExamsController::class, 'destroy'],                  ['auth', 'permission:delete_exams']);
$router->get('/academics/exams/marksheet',            [ExamsController::class, 'marksheet'],                ['auth', 'permission:view_exams']);
$router->get('/academics/exams/subjects-by-group',    [ExamsController::class, 'getSubjectsByGroup'],       ['auth', 'permission:view_exams']);
$router->get('/academics/report-cards',               [ReportCardController::class, 'index'],           ['auth', 'permission:view_report_cards']);
$router->get('/academics/settings',                   [App\Controllers\AcademicSettingsController::class, 'index'],     ['auth', 'permission:view_academic_settings']);
$router->post('/academics/settings/years',            [App\Controllers\AcademicSettingsController::class, 'storeYear'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/years/{id}/lock',  [App\Controllers\AcademicSettingsController::class, 'lockYear'],  ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/years/{id}/archive', [App\Controllers\AcademicSettingsController::class, 'archiveYear'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/years/{id}/delete',  [App\Controllers\AcademicSettingsController::class, 'deleteYear'],  ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/years/{id}/copy',   [App\Controllers\AcademicSettingsController::class, 'copyPreviousYear'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/semesters',        [App\Controllers\AcademicSettingsController::class, 'storeSemester'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/semesters/{id}/delete', [App\Controllers\AcademicSettingsController::class, 'deleteSemester'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/wizard/close-year', [App\Controllers\AcademicSettingsController::class, 'runYearClosingWizard'], ['auth', 'permission:edit_academic_settings']);
$router->post('/academics/settings/save-attendance-settings', [App\Controllers\AcademicSettingsController::class, 'saveAttendanceSettings'], ['auth', 'permission:edit_academic_settings']);
$router->get('/academics/lecture-attendance',         [App\Controllers\AcademicSettingsController::class, 'lectureAttendance'], ['auth', 'permission:view_acad_attendance']);

// Main Groups & Subject Master Routes
$router->get('/academics/main-groups',                [App\Controllers\AcademicSettingsController::class, 'mainGroupsIndex'], ['auth', 'permission:view_main_groups']);
$router->post('/academics/main-groups',               [App\Controllers\AcademicSettingsController::class, 'storeMainGroup'], ['auth', 'permission:create_main_groups']);
$router->post('/academics/main-groups/{id}',          [App\Controllers\AcademicSettingsController::class, 'updateMainGroup'], ['auth', 'permission:edit_main_groups']);
$router->get('/academics/subject-master',             [App\Controllers\AcademicSettingsController::class, 'subjectMasterIndex'], ['auth', 'permission:view_subjects']);
$router->post('/academics/subject-master',            [App\Controllers\AcademicSettingsController::class, 'storeSubject'], ['auth', 'permission:create_subjects']);
$router->post('/academics/subject-master/{id}',       [App\Controllers\AcademicSettingsController::class, 'updateSubject'], ['auth', 'permission:edit_subjects']);
$router->post('/academics/subject-master/{id}/delete', [App\Controllers\AcademicSettingsController::class, 'destroySubject'], ['auth', 'permission:delete_subjects']);

// ─── Global System Settings & Reports ───────────────────────────────────────────
$router->get('/reports',                        [\App\Controllers\ReportsController::class, 'index'],     ['auth', 'permission:view_reports_overview']);
$router->get('/reports/finance',                [\App\Controllers\ReportsController::class, 'finance'],   ['auth', 'permission:view_financial_reports']);
$router->get('/reports/students',               [\App\Controllers\ReportsController::class, 'students'],  ['auth', 'permission:view_student_reports']);
$router->get('/reports/staff',                  [\App\Controllers\ReportsController::class, 'staff'],     ['auth', 'permission:view_staff_reports']);
$router->get('/reports/communication',           [\App\Controllers\ReportsController::class, 'communication'], ['auth', 'permission:view_whatsapp_logs']);
$router->get('/settings',                       [\App\Controllers\SettingsController::class, 'index'],    ['auth', 'permission:view_general_settings']);
$router->get('/settings/general',               [\App\Controllers\SettingsController::class, 'index'],    ['auth', 'permission:view_general_settings']);
$router->get('/settings/school',                function() { header('Location: /settings/general'); exit; },     ['auth', 'permission:view_general_settings']);
$router->get('/settings/integrations',          [\App\Controllers\SettingsController::class, 'integrations'],['auth', 'permission:view_integrations']);
$router->get('/settings/system',                [\App\Controllers\SettingsController::class, 'system'],   ['auth', 'permission:view_system_config']);
$router->post('/settings',                      [\App\Controllers\SettingsController::class, 'update'],   ['auth', 'permission:edit_general_settings']);
$router->post('/settings/update',               [\App\Controllers\SettingsController::class, 'update'],   ['auth', 'permission:edit_general_settings']);
$router->post('/settings/test-whatsapp',        [\App\Controllers\SettingsController::class, 'testWhatsApp'],['auth', 'permission:edit_general_settings']);

// Curriculum Templates Manager Routes
$router->get('/academics/curriculum',                 [App\Controllers\CurriculumManagerController::class, 'index'], ['auth', 'permission:view_curriculum']);
$router->post('/academics/curriculum/templates',      [App\Controllers\CurriculumManagerController::class, 'storeTemplate'], ['auth', 'permission:create_curriculum']);
$router->get('/academics/curriculum/templates/{id}',  [App\Controllers\CurriculumManagerController::class, 'showTemplate'], ['auth', 'permission:view_curriculum']);
$router->post('/academics/curriculum/templates/{id}/sections', [App\Controllers\CurriculumManagerController::class, 'storeSection'], ['auth', 'permission:create_curriculum']);
$router->post('/academics/curriculum/sections/update', [App\Controllers\CurriculumManagerController::class, 'updateSection'], ['auth', 'permission:edit_curriculum']);
$router->post('/academics/curriculum/subjects/add',             [App\Controllers\CurriculumManagerController::class, 'storeCurriculumSubject'], ['auth', 'permission:create_curriculum']);
$router->post('/academics/curriculum/subjects/{id}/delete',    [App\Controllers\CurriculumManagerController::class, 'destroyCurriculumSubject'], ['auth', 'permission:delete_curriculum']);
$router->post('/academics/curriculum/templates/{id}/delete',  [App\Controllers\CurriculumManagerController::class, 'destroyTemplate'], ['auth', 'permission:delete_curriculum']);
$router->post('/academics/curriculum/reorder',        [App\Controllers\CurriculumManagerController::class, 'reorderSubjects'], ['auth', 'permission:edit_curriculum']);

// Promotion Wizard Routes
$router->get('/academics/promotion',                  [App\Controllers\PromotionController::class, 'index'], ['auth', 'permission:view_promotion']);
$router->post('/academics/promotion/run',             [App\Controllers\PromotionController::class, 'promoteStudents'], ['auth', 'permission:create_promotion']);

// Legacy fallbacks
$router->get('/academic', [App\Controllers\AcademicWorkspaceController::class, 'index'], ['auth']);

$router->get('/students',                          [StudentController::class, 'index'],              ['auth', 'permission:view_students_list']);
$router->get('/students/create',                   [StudentController::class, 'create'],             ['auth', 'permission:create_students_list']);
$router->post('/students',                         [StudentController::class, 'store'],              ['auth', 'permission:create_students_list']);
$router->get('/students/search',                   [StudentController::class, 'search'],             ['auth']);
$router->get('/students/{id}',                     [StudentController::class, 'show'],               ['auth', 'permission:view_students_list']);
$router->get('/students/{id}/edit',                [StudentController::class, 'edit'],               ['auth', 'permission:edit_students_list']);
$router->post('/students/{id}',                    [StudentController::class, 'update'],             ['auth', 'permission:edit_students_list']);
$router->delete('/students/{id}',                  [StudentController::class, 'destroy'],            ['auth', 'permission:delete_students_list']);
$router->post('/students/{id}/medical',            [StudentController::class, 'updateMedical'],      ['auth', 'permission:edit_students_list']);
$router->post('/students/{id}/status',             [StudentController::class, 'updateStatus'],       ['auth', 'permission:approve_enrollments']);
$router->post('/students/{id}/documents',          [StudentController::class, 'uploadDocument'],     ['auth', 'permission:create_student_documents']);
$router->post('/students/{id}/guardians',          [StudentController::class, 'storeGuardian'],      ['auth', 'permission:edit_students_list']);
$router->post('/students/{id}/emergency-contacts', [StudentController::class, 'storeEmergencyContact'],['auth', 'permission:edit_students_list']);
$router->get('/students/{id}/timeline',            [StudentController::class, 'timeline'],           ['auth', 'permission:view_students_list']);
$router->get('/report-cards',                      [ReportCardController::class, 'index'],           ['auth', 'permission:edit_report_cards']);
$router->get('/report-cards/settings',             [ReportCardController::class, 'settings'],        ['auth', 'permission:edit_report_cards']);
$router->post('/report-cards/settings',            [ReportCardController::class, 'saveSettings'],    ['auth', 'permission:edit_report_cards']);
$router->post('/report-cards/settings/upload-signature', [ReportCardController::class, 'uploadSignature'], ['auth', 'permission:edit_report_cards']);
$router->get('/students/{id}/report-card/edit',    [ReportCardController::class, 'edit'],            ['auth', 'permission:edit_report_cards']);
$router->get('/academics/students/{id}/report-card/edit', [ReportCardController::class, 'edit'],            ['auth', 'permission:edit_report_cards']);
$router->post('/students/{id}/report-card',        [ReportCardController::class, 'store'],           ['auth', 'permission:edit_report_cards']);
$router->post('/academics/students/{id}/report-card', [ReportCardController::class, 'store'],           ['auth', 'permission:edit_report_cards']);
$router->get('/students/{id}/report-card/view',    [ReportCardController::class, 'show'],            ['auth', 'permission:view_report_cards']);
$router->get('/academics/students/{id}/report-card/view', [ReportCardController::class, 'show'],            ['auth', 'permission:view_report_cards']);


// ─── Fees Management — Admin ──────────────────────────────────────────────────
$router->get('/fees',                       [FeeController::class, 'index'],          ['auth', 'permission:view_fees_dashboard']);
$router->get('/fees/export',                [FeeController::class, 'exportCsv'],      ['auth', 'permission:view_fees_dashboard']);
$router->post('/fees/{id}/pay',             [FeeController::class, 'recordPayment'],  ['auth', 'permission:create_all_invoices']);

// Invoices CRUD
$router->get('/fees/invoices',              [\App\Controllers\FeeInvoiceController::class, 'index'],   ['auth', 'permission:view_all_invoices']);
$router->get('/fees/invoices/create',       [\App\Controllers\FeeInvoiceController::class, 'create'],  ['auth', 'permission:create_all_invoices']);
$router->post('/fees/invoices',             [\App\Controllers\FeeInvoiceController::class, 'store'],   ['auth', 'permission:create_all_invoices']);
$router->get('/fees/invoices/{id}',         [\App\Controllers\FeeInvoiceController::class, 'show'],    ['auth', 'permission:view_all_invoices']);
$router->get('/fees/invoices/{id}/edit',    [\App\Controllers\FeeInvoiceController::class, 'edit'],    ['auth', 'permission:edit_all_invoices']);
$router->post('/fees/invoices/{id}',        [\App\Controllers\FeeInvoiceController::class, 'update'],  ['auth', 'permission:edit_all_invoices']);
$router->post('/fees/invoices/{id}/delete', [\App\Controllers\FeeInvoiceController::class, 'destroy'], ['auth', 'permission:delete_all_invoices']);

// Fee Categories
$router->get('/fees/categories',            [\App\Controllers\FeeCategoryController::class, 'index'],   ['auth', 'permission:view_fee_categories']);
$router->post('/fees/categories',           [\App\Controllers\FeeCategoryController::class, 'store'],   ['auth', 'permission:create_fee_categories']);
$router->post('/fees/categories/{id}',      [\App\Controllers\FeeCategoryController::class, 'update'],  ['auth', 'permission:edit_fee_categories']);
$router->post('/fees/categories/{id}/delete',[\App\Controllers\FeeCategoryController::class, 'destroy'],['auth', 'permission:delete_fee_categories']);

// Late Fee Policies
$router->get('/fees/late-fee-policies',            [\App\Controllers\LateFeePolicyController::class, 'index'],   ['auth', 'permission:view_late_fee_policies']);
$router->post('/fees/late-fee-policies',           [\App\Controllers\LateFeePolicyController::class, 'store'],   ['auth', 'permission:create_late_fee_policies']);
$router->post('/fees/late-fee-policies/{id}',      [\App\Controllers\LateFeePolicyController::class, 'update'],  ['auth', 'permission:edit_late_fee_policies']);
$router->post('/fees/late-fee-policies/{id}/delete',[\App\Controllers\LateFeePolicyController::class, 'destroy'],['auth', 'permission:delete_late_fee_policies']);

// Fee Structures & Items
$router->get('/fees/structures',                   [\App\Controllers\FeeStructureController::class, 'index'],    ['auth', 'permission:view_fee_structures']);
$router->post('/fees/structures',                  [\App\Controllers\FeeStructureController::class, 'store'],    ['auth', 'permission:create_fee_structures']);
$router->get('/fees/structures/{id}',              [\App\Controllers\FeeStructureController::class, 'show'],     ['auth', 'permission:view_fee_structures']);
$router->post('/fees/structures/{id}/items',       [\App\Controllers\FeeStructureController::class, 'addItem'],  ['auth', 'permission:edit_fee_structures']);
$router->post('/fees/structures/{id}/items/{itemId}/delete', [\App\Controllers\FeeStructureController::class, 'destroyItem'], ['auth', 'permission:delete_fee_structures']);

// Batch Fee Generator
$router->get('/fees/batch-generator',              [\App\Controllers\BatchFeeGeneratorController::class, 'index'], ['auth', 'permission:view_batch_generator']);
$router->post('/fees/batch-generator',             [\App\Controllers\BatchFeeGeneratorController::class, 'generate'], ['auth', 'permission:create_batch_generator']);

// ─── Receipts — Admin ─────────────────────────────────────────────────────────
$router->get('/receipts', [ReceiptsController::class, 'index'], ['auth', 'permission:view_receipts_log']);
$router->get('/receipts/settings', [ReceiptsController::class, 'settings'], ['auth', 'permission:view_receipts_log']);
$router->post('/receipts/settings', [ReceiptsController::class, 'saveSettings'], ['auth', 'permission:edit_receipts_log']);
$router->get('/receipts/{id}/view', [ReceiptsController::class, 'show'], ['auth', 'permission:view_receipts_log']);

// ─── Scholarships — Admin ─────────────────────────────────────────────────────



// ─── Transport Management Workspace (/transport/*) ───────────────────────────
$router->get('/transport',                      [TransportController::class, 'overview'],            ['auth', 'permission:view_transport_overview']);
$router->get('/transport/overview',             [TransportController::class, 'overview'],            ['auth', 'permission:view_transport_overview']);
$router->get('/transport/routes',               [TransportController::class, 'routes'],              ['auth', 'permission:view_transport_drivers']);
$router->get('/transport/vehicles',             [TransportController::class, 'vehicles'],            ['auth', 'permission:view_transport_drivers']);
$router->get('/transport/drivers',              [TransportController::class, 'drivers'],             ['auth', 'permission:view_transport_drivers']);
$router->get('/transport/student-assignments',  [TransportController::class, 'studentAssignments'],   ['auth', 'permission:view_student_transport']);
$router->post('/transport/{id}/assign',     [TransportController::class, 'assignStudent'],['auth', 'permission:create_student_transport']);
$router->post('/transport/assignments/{id}/update', [TransportController::class, 'updateAssignment'], ['auth', 'permission:edit_student_transport']);
$router->post('/transport/assignments/{id}/remove', [TransportController::class, 'removeAssignment'], ['auth', 'permission:delete_student_transport']);
$router->get('/transport/live-tracking',        [TransportController::class, 'tracking'],           ['auth', 'permission:view_live_tracking']);
$router->get('/transport/logs',                 [TransportController::class, 'viewLogs'],           ['auth', 'permission:view_transport_logs']);
$router->get('/transport/settings',             [TransportController::class, 'settings'],           ['auth', 'permission:view_transport_settings']);
$router->post('/transport/settings',            [TransportController::class, 'storeSettings'],      ['auth', 'permission:edit_transport_settings']);

$router->get('/transport/create',           [TransportController::class, 'create'],   ['auth', 'permission:create_transport_drivers']);
$router->get('/transport/tracking',         [TransportController::class, 'tracking'], ['auth', 'permission:view_live_tracking']);
$router->get('/transport/live-data',        [TransportController::class, 'liveData'], ['auth', 'permission:view_live_tracking']);
$router->post('/transport',                 [TransportController::class, 'store'],    ['auth', 'permission:create_transport_drivers']);
$router->get('/transport/{id}/edit',        [TransportController::class, 'edit'],     ['auth', 'permission:edit_transport_drivers']);
$router->post('/transport/{id}',            [TransportController::class, 'update'],    ['auth', 'permission:edit_transport_drivers']);
$router->post('/transport/{id}/delete',     [TransportController::class, 'destroy'],   ['auth', 'permission:delete_transport_drivers']);


// ─── Certificate Designer — Admin ──────────────────────────────────────────────
$router->get('/certificates',               [CertificateController::class, 'index'],  ['auth', 'permission:view_certificates']);
$router->get('/certificates/create',        [CertificateController::class, 'create'], ['auth', 'permission:create_certificates']);
$router->post('/certificates',              [CertificateController::class, 'store'],  ['auth', 'permission:create_certificates']);
$router->get('/certificates/{id}/view',     [CertificateController::class, 'show'],   ['auth', 'permission:view_certificates']);
$router->post('/certificates/{id}/delete',  [CertificateController::class, 'destroy'],['auth', 'permission:delete_certificates']);

// ─── Finance & Fee Management ────────────────────────────────────────────────
$router->get('/fees',                         [FeeController::class, 'index'],            ['auth', 'permission:view_fees_dashboard']);
$router->get('/fees/create',                  [FeeController::class, 'create'],           ['auth', 'permission:create_all_invoices']);
$router->post('/fees',                        [FeeController::class, 'store'],            ['auth', 'permission:create_all_invoices']);
$router->get('/fees/export-csv',              [FeeController::class, 'exportCsv'],        ['auth', 'permission:view_fees_dashboard']);
$router->post('/fees/{id}/pay',               [FeeController::class, 'recordPayment'],    ['auth', 'permission:create_all_invoices']);
$router->post('/fees/{id}/delete',            [FeeController::class, 'destroy'],          ['auth', 'permission:delete_all_invoices']);

$router->get('/fees/structures',              [App\Controllers\FeeStructureController::class, 'index'], ['auth', 'permission:view_fee_structures']);
$router->post('/fees/structures',             [App\Controllers\FeeStructureController::class, 'store'], ['auth', 'permission:create_fee_structures']);

$router->get('/fees/categories',              [App\Controllers\FeeCategoryController::class, 'index'],  ['auth', 'permission:view_fee_categories']);
$router->post('/fees/categories',             [App\Controllers\FeeCategoryController::class, 'store'],  ['auth', 'permission:create_fee_categories']);

$router->get('/fees/batch-generator',         [App\Controllers\BatchFeeGeneratorController::class, 'index'], ['auth', 'permission:view_batch_generator']);
$router->post('/fees/batch-generator',        [App\Controllers\BatchFeeGeneratorController::class, 'generate'], ['auth', 'permission:create_batch_generator']);

$router->get('/fees/late-fee-policies',       [App\Controllers\LateFeePolicyController::class, 'index'], ['auth', 'permission:view_late_fee_policies']);
$router->post('/fees/late-fee-policies',      [App\Controllers\LateFeePolicyController::class, 'store'], ['auth', 'permission:create_late_fee_policies']);

$router->get('/receipts',                     [ReceiptsController::class, 'index'],          ['auth', 'permission:view_receipts_log']);
$router->get('/receipts/settings',            [ReceiptsController::class, 'settings'],       ['auth', 'permission:view_receipts_log']);
$router->post('/receipts/settings',           [ReceiptsController::class, 'saveSettings'],   ['auth', 'permission:edit_receipts_log']);
$router->get('/receipts/{id}/view',           [ReceiptsController::class, 'show'],           ['auth', 'permission:view_receipts_log']);

// ─── Admissions ──────────────────────────────────────────────────────────────
$router->get('/admissions', [AdmissionsController::class, 'index'], ['auth', 'permission:view_enrollments']);
$router->post('/admissions/{id}/status', [AdmissionsController::class, 'updateStatus'], ['auth', 'permission:approve_enrollments']);

// ─── Announcements ───────────────────────────────────────────────────────────
$router->get('/academics/announcements', [AnnouncementController::class, 'index'], ['auth', 'permission:view_announcements']);
$router->post('/academics/announcements', [AnnouncementController::class, 'store'], ['auth', 'permission:create_announcements']);
$router->post('/academics/announcements/{id}/delete', [AnnouncementController::class, 'destroy'], ['auth', 'permission:delete_announcements']);

// ─── Classes & Sections ───────────────────────────────────────────────────────
$router->get('/classes', [ClassesController::class, 'index'], ['auth', 'permission:view_classes']);
$router->post('/classes', [ClassesController::class, 'store'], ['auth', 'permission:create_classes']);
$router->post('/classes/delete', [ClassesController::class, 'destroy'], ['auth', 'permission:delete_classes']);
$router->get('/classes/{class}', [ClassesController::class, 'show'], ['auth', 'permission:view_classes']);

// ─── Attendance ──────────────────────────────────────────────────────────────
$router->get('/student-attendance', [AttendanceController::class, 'index'], ['auth', 'permission:view_acad_attendance']);
$router->post('/student-attendance/save', [AttendanceController::class, 'save'], ['auth', 'permission:edit_acad_attendance']);
$router->get('/attendance', [AttendanceController::class, 'index'], ['auth', 'permission:view_staff_attendance']);
$router->post('/attendance/save', [AttendanceController::class, 'save'], ['auth', 'permission:edit_staff_attendance']);
$router->get('/attendance/face-kiosk',    [App\Controllers\FaceRecognitionController::class, 'kioskView'], ['auth', 'permission:view_face_kiosk']);
$router->post('/attendance/face-kiosk',   [App\Controllers\FaceRecognitionController::class, 'kioskView'], ['auth', 'permission:view_face_kiosk']);
$router->get('/attendance/face-register', [App\Controllers\FaceRecognitionController::class, 'registerView'], ['auth', 'permission:view_face_register']);
$router->post('/attendance/face-register',[App\Controllers\FaceRecognitionController::class, 'registerView'], ['auth', 'permission:view_face_register']);
$router->get('/attendance/face-history',  [App\Controllers\FaceRecognitionController::class, 'historyView']);

// ─── Timetables ──────────────────────────────────────────────────────────────
$router->get('/timetables', [TimetablesController::class, 'index'], ['auth', 'permission:view_timetable']);
$router->post('/timetables/store', [TimetablesController::class, 'store'], ['auth', 'permission:create_timetable']);

// ─── Exams & Grades ──────────────────────────────────────────────────────────
$router->get('/exams', [ExamsController::class, 'index'], ['auth', 'permission:view_exams']);
$router->get('/exams/bulk-entry', [ExamsController::class, 'bulkEntry'], ['auth', 'permission:view_exams']);
$router->post('/exams/bulk-save', [ExamsController::class, 'bulkSave'], ['auth', 'permission:edit_exams']);
$router->get('/exams/marksheet', [ExamsController::class, 'marksheet'], ['auth', 'permission:view_exams']);
$router->get('/exams/subjects-by-group', [ExamsController::class, 'getSubjectsByGroup'], ['auth', 'permission:view_exams']);

// ─── Migrations Helper (Web Run) ─────────────────────────────────────────────
$router->get('/migrate', function () {
    require_once CORE_PATH . '/Migration.php';
    $runner = new \Core\Migration();
    ob_start();
    $runner->run();
    $output = ob_get_clean();
    return "<h1>Migration Output:</h1><pre>" . htmlspecialchars($output) . "</pre>";
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

$router->get('/transport/driver/create',    [TransportController::class, 'createDriver'], ['auth', 'permission:create_transport_drivers']);
$router->post('/transport/driver',          [TransportController::class, 'storeDriver'],  ['auth', 'permission:create_transport_drivers']);

// ─── Documents Workspace ──────────────────────────────────────────────────────
$router->get('/documents',                     [App\Controllers\DocumentsController::class, 'dashboard'],         ['auth', 'permission:view_documents_overview']);
$router->get('/documents/dashboard',           [App\Controllers\DocumentsController::class, 'dashboard'],         ['auth', 'permission:view_documents_overview']);
$router->get('/documents/student-documents',   [App\Controllers\DocumentsController::class, 'studentDocuments'],  ['auth', 'permission:view_student_documents']);
$router->post('/documents/student-documents/upload', [App\Controllers\DocumentsController::class, 'uploadStudentDoc'],['auth', 'permission:create_student_documents']);
$router->get('/documents/staff-documents',     [App\Controllers\DocumentsController::class, 'staffDocuments'],    ['auth', 'permission:view_staff_user_accounts']);
$router->get('/documents/parent-documents',    [App\Controllers\DocumentsController::class, 'parentDocuments'],   ['auth', 'permission:view_parent_documents']);
$router->get('/documents/driver-documents',    [App\Controllers\DocumentsController::class, 'driverDocuments'],   ['auth', 'permission:view_driver_documents']);
$router->get('/documents/generated',           [App\Controllers\DocumentsController::class, 'generated'],          ['auth', 'permission:view_certificates']);
$router->get('/documents/templates',           [App\Controllers\DocumentsController::class, 'templates'],          ['auth', 'permission:view_certificates']);
$router->get('/documents/settings',            [App\Controllers\DocumentsController::class, 'settings'],           ['auth', 'permission:view_documents_overview']);
$router->post('/transport/{id}/location', [TransportController::class, 'updateLocation'],['auth', 'permission:view_live_tracking']);
