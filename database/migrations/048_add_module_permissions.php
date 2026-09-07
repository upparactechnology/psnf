<?php

declare(strict_types=1);

class AddModulePermissions
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        // Define all module-level permissions: [name, slug, module]
        $permissions = [
            // ── ACADEMIC ──
            ['Academic Years – View',       'view_academic_years',       'Academic'],
            ['Academic Years – Create',     'create_academic_years',     'Academic'],
            ['Academic Years – Edit',       'edit_academic_years',       'Academic'],
            ['Academic Years – Delete',     'delete_academic_years',     'Academic'],

            ['Main Groups – View',          'view_main_groups',          'Academic'],
            ['Main Groups – Create',        'create_main_groups',        'Academic'],
            ['Main Groups – Edit',          'edit_main_groups',          'Academic'],
            ['Main Groups – Delete',        'delete_main_groups',        'Academic'],

            ['Curriculum – View',           'view_curriculum',           'Academic'],
            ['Curriculum – Create',         'create_curriculum',         'Academic'],
            ['Curriculum – Edit',           'edit_curriculum',           'Academic'],
            ['Curriculum – Delete',         'delete_curriculum',         'Academic'],

            ['Subjects – View',             'view_subjects',             'Academic'],
            ['Subjects – Create',           'create_subjects',           'Academic'],
            ['Subjects – Edit',             'edit_subjects',             'Academic'],
            ['Subjects – Delete',           'delete_subjects',           'Academic'],

            ['Classes – View',              'view_classes',              'Academic'],
            ['Classes – Create',            'create_classes',            'Academic'],
            ['Classes – Edit',              'edit_classes',              'Academic'],
            ['Classes – Delete',            'delete_classes',            'Academic'],

            ['Students – View',             'view_students_list',        'Academic'],
            ['Students – Create',           'create_students_list',      'Academic'],
            ['Students – Edit',             'edit_students_list',        'Academic'],
            ['Students – Delete',           'delete_students_list',      'Academic'],

            ['Teachers – View',             'view_teachers',             'Academic'],
            ['Teachers – Create',           'create_teachers',           'Academic'],
            ['Teachers – Edit',             'edit_teachers',             'Academic'],
            ['Teachers – Delete',           'delete_teachers',           'Academic'],

            ['Attendance – View',           'view_acad_attendance',      'Academic'],
            ['Attendance – Create',         'create_acad_attendance',    'Academic'],
            ['Attendance – Edit',           'edit_acad_attendance',      'Academic'],
            ['Attendance – Delete',         'delete_acad_attendance',    'Academic'],

            ['Timetable – View',            'view_timetable',            'Academic'],
            ['Timetable – Create',          'create_timetable',          'Academic'],
            ['Timetable – Edit',            'edit_timetable',            'Academic'],
            ['Timetable – Delete',          'delete_timetable',          'Academic'],

            ['Assessments – View',          'view_assessments',          'Academic'],
            ['Assessments – Create',        'create_assessments',        'Academic'],
            ['Assessments – Edit',          'edit_assessments',          'Academic'],
            ['Assessments – Delete',        'delete_assessments',        'Academic'],

            ['Exams Setup – View',          'view_exams',                'Academic'],
            ['Exams Setup – Create',        'create_exams',              'Academic'],
            ['Exams Setup – Edit',          'edit_exams',                'Academic'],
            ['Exams Setup – Delete',        'delete_exams',              'Academic'],

            ['Report Cards – View',         'view_report_cards',         'Academic'],
            ['Report Cards – Create',       'create_report_cards',       'Academic'],
            ['Report Cards – Edit',         'edit_report_cards',         'Academic'],
            ['Report Cards – Delete',       'delete_report_cards',       'Academic'],

            ['Promotion – View',            'view_promotion',            'Academic'],
            ['Promotion – Create',          'create_promotion',          'Academic'],
            ['Promotion – Edit',            'edit_promotion',            'Academic'],
            ['Promotion – Delete',          'delete_promotion',          'Academic'],

            ['Announcements – View',        'view_announcements',        'Academic'],
            ['Announcements – Create',      'create_announcements',      'Academic'],
            ['Announcements – Edit',        'edit_announcements',        'Academic'],
            ['Announcements – Delete',      'delete_announcements',      'Academic'],

            ['Academic Settings – View',    'view_academic_settings',    'Academic'],
            ['Academic Settings – Edit',    'edit_academic_settings',    'Academic'],

            // ── STAFF ──
            ['Staff Overview – View',       'view_staff_overview',       'Staff'],

            ['Staff Directory – View',      'view_staff_directory',      'Staff'],
            ['Staff Directory – Create',    'create_staff_directory',    'Staff'],
            ['Staff Directory – Edit',      'edit_staff_directory',      'Staff'],
            ['Staff Directory – Delete',    'delete_staff_directory',    'Staff'],

            ['Departments – View',          'view_departments',          'Staff'],
            ['Departments – Create',        'create_departments',        'Staff'],
            ['Departments – Edit',          'edit_departments',          'Staff'],
            ['Departments – Delete',        'delete_departments',        'Staff'],

            ['Designations – View',         'view_designations',         'Staff'],
            ['Designations – Create',       'create_designations',       'Staff'],
            ['Designations – Edit',         'edit_designations',         'Staff'],
            ['Designations – Delete',       'delete_designations',       'Staff'],

            ['Staff Attendance – View',     'view_staff_attendance',     'Staff'],
            ['Staff Attendance – Create',   'create_staff_attendance',   'Staff'],
            ['Staff Attendance – Edit',     'edit_staff_attendance',     'Staff'],
            ['Staff Attendance – Delete',   'delete_staff_attendance',   'Staff'],

            ['Face Kiosk – View',           'view_face_kiosk',           'Staff'],
            ['Face Kiosk – Create',         'create_face_kiosk',         'Staff'],

            ['Face Register – View',        'view_face_register',        'Staff'],
            ['Face Register – Create',      'create_face_register',      'Staff'],
            ['Face Register – Edit',        'edit_face_register',        'Staff'],
            ['Face Register – Delete',      'delete_face_register',      'Staff'],

            ['Leave Management – View',     'view_leave_management',     'Staff'],
            ['Leave Management – Create',   'create_leave_management',   'Staff'],
            ['Leave Management – Edit',     'edit_leave_management',     'Staff'],
            ['Leave Management – Delete',   'delete_leave_management',   'Staff'],

            ['Staff Roles & Permissions – View',   'view_staff_roles',   'Staff'],
            ['Staff Roles & Permissions – Create', 'create_staff_roles', 'Staff'],
            ['Staff Roles & Permissions – Edit',   'edit_staff_roles',   'Staff'],
            ['Staff Roles & Permissions – Delete', 'delete_staff_roles', 'Staff'],

            ['Staff User Accounts – View',   'view_staff_user_accounts',   'Staff'],
            ['Staff User Accounts – Create', 'create_staff_user_accounts', 'Staff'],
            ['Staff User Accounts – Edit',   'edit_staff_user_accounts',   'Staff'],
            ['Staff User Accounts – Delete', 'delete_staff_user_accounts', 'Staff'],

            ['Staff Settings – View',       'view_staff_settings',       'Staff'],
            ['Staff Settings – Edit',       'edit_staff_settings',       'Staff'],

            // ── FEES ──
            ['Fees Dashboard – View',       'view_fees_dashboard',       'Fees'],

            ['All Invoices – View',         'view_all_invoices',         'Fees'],
            ['All Invoices – Create',       'create_all_invoices',       'Fees'],
            ['All Invoices – Edit',         'edit_all_invoices',         'Fees'],
            ['All Invoices – Delete',       'delete_all_invoices',       'Fees'],

            ['Fee Structures – View',       'view_fee_structures',       'Fees'],
            ['Fee Structures – Create',     'create_fee_structures',     'Fees'],
            ['Fee Structures – Edit',       'edit_fee_structures',       'Fees'],
            ['Fee Structures – Delete',     'delete_fee_structures',     'Fees'],

            ['Batch Generator – View',      'view_batch_generator',      'Fees'],
            ['Batch Generator – Create',    'create_batch_generator',    'Fees'],

            ['Receipts Log – View',         'view_receipts_log',         'Fees'],
            ['Receipts Log – Create',       'create_receipts_log',       'Fees'],

            ['Fee Categories – View',       'view_fee_categories',       'Fees'],
            ['Fee Categories – Create',     'create_fee_categories',     'Fees'],
            ['Fee Categories – Edit',       'edit_fee_categories',       'Fees'],
            ['Fee Categories – Delete',     'delete_fee_categories',     'Fees'],

            ['Late Fee Policies – View',    'view_late_fee_policies',    'Fees'],
            ['Late Fee Policies – Create',  'create_late_fee_policies',  'Fees'],
            ['Late Fee Policies – Edit',    'edit_late_fee_policies',    'Fees'],
            ['Late Fee Policies – Delete',  'delete_late_fee_policies',  'Fees'],

            // ── TRANSPORT ──
            ['Transport Overview – View',   'view_transport_overview',   'Transport'],

            ['Drivers – View',              'view_transport_drivers',    'Transport'],
            ['Drivers – Create',            'create_transport_drivers',  'Transport'],
            ['Drivers – Edit',              'edit_transport_drivers',    'Transport'],
            ['Drivers – Delete',            'delete_transport_drivers',  'Transport'],

            ['Student Assignments – View',  'view_student_transport',    'Transport'],
            ['Student Assignments – Create','create_student_transport',  'Transport'],
            ['Student Assignments – Edit',  'edit_student_transport',    'Transport'],
            ['Student Assignments – Delete','delete_student_transport',  'Transport'],

            ['Live Tracking – View',        'view_live_tracking',        'Transport'],

            ['Transport Settings – View',   'view_transport_settings',   'Transport'],
            ['Transport Settings – Edit',   'edit_transport_settings',   'Transport'],

            ['Transport Logs – View',       'view_transport_logs',       'Transport'],

            // ── PAYROLL ──
            ['Payroll Runs – View',         'view_payroll_runs',         'Payroll'],
            ['Payroll Runs – Create',       'create_payroll_runs',       'Payroll'],
            ['Payroll Runs – Edit',         'edit_payroll_runs',         'Payroll'],
            ['Payroll Runs – Delete',       'delete_payroll_runs',       'Payroll'],

            ['Detailed Attendance – View',  'view_payroll_attendance',   'Payroll'],

            ['Holidays Calendar – View',    'view_holidays_calendar',    'Payroll'],
            ['Holidays Calendar – Create',  'create_holidays_calendar',  'Payroll'],
            ['Holidays Calendar – Edit',    'edit_holidays_calendar',    'Payroll'],
            ['Holidays Calendar – Delete',  'delete_holidays_calendar',  'Payroll'],

            ['Audit History Logs – View',   'view_payroll_audit',        'Payroll'],

            // ── DOCUMENTS ──
            ['Documents Overview – View',   'view_documents_overview',   'Documents'],

            ['Student Documents – View',    'view_student_documents',    'Documents'],
            ['Student Documents – Create',  'create_student_documents',  'Documents'],
            ['Student Documents – Edit',    'edit_student_documents',    'Documents'],
            ['Student Documents – Delete',  'delete_student_documents',  'Documents'],

            ['Parent Documents – View',     'view_parent_documents',     'Documents'],
            ['Parent Documents – Create',   'create_parent_documents',   'Documents'],
            ['Parent Documents – Edit',     'edit_parent_documents',     'Documents'],
            ['Parent Documents – Delete',   'delete_parent_documents',   'Documents'],

            ['Driver Documents – View',     'view_driver_documents',     'Documents'],
            ['Driver Documents – Create',   'create_driver_documents',   'Documents'],
            ['Driver Documents – Edit',     'edit_driver_documents',     'Documents'],
            ['Driver Documents – Delete',   'delete_driver_documents',   'Documents'],

            ['Certificates – View',         'view_certificates',         'Documents'],
            ['Certificates – Create',       'create_certificates',       'Documents'],
            ['Certificates – Edit',         'edit_certificates',         'Documents'],
            ['Certificates – Delete',       'delete_certificates',       'Documents'],

            // ── REPORTS ──
            ['Reports Overview – View',     'view_reports_overview',     'Reports'],

            ['Financial Reports – View',    'view_financial_reports',    'Reports'],
            ['Financial Reports – Export',  'export_financial_reports',  'Reports'],

            ['Student Reports – View',      'view_student_reports',      'Reports'],
            ['Student Reports – Export',    'export_student_reports',    'Reports'],

            ['Staff & HR Reports – View',   'view_staff_reports',        'Reports'],
            ['Staff & HR Reports – Export', 'export_staff_reports',      'Reports'],

            ['WhatsApp Logs – View',        'view_whatsapp_logs',        'Reports'],

            // ── SETTINGS ──
            ['General Settings – View',     'view_general_settings',     'Settings'],
            ['General Settings – Edit',     'edit_general_settings',     'Settings'],

            ['Integrations & APIs – View',  'view_integrations',         'Settings'],
            ['Integrations & APIs – Edit',  'edit_integrations',         'Settings'],

            ['System Configurations – View','view_system_config',        'Settings'],
            ['System Configurations – Edit','edit_system_config',        'Settings'],

            // ── ONLINE ENROLLMENT ──
            ['Enrollments – View',          'view_enrollments',          'Online Enrollment'],
            ['Enrollments – Approve',       'approve_enrollments',       'Online Enrollment'],
            ['Enrollments – Reject',        'reject_enrollments',        'Online Enrollment'],
        ];

        // Insert permissions (skip duplicates)
        $ins = $this->db->prepare("INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, ?, NOW())");
        foreach ($permissions as [$name, $slug, $module]) {
            $ins->execute([$name, $slug, $module]);
        }

        // Now assign permissions to roles based on sensible defaults

        // Super Admin (id=1): gets ALL new permissions
        $allNew = $this->db->query("SELECT id FROM permissions WHERE slug LIKE 'view_%' OR slug LIKE 'create_%' OR slug LIKE 'edit_%' OR slug LIKE 'delete_%' OR slug LIKE 'approve_%' OR slug LIKE 'reject_%' OR slug LIKE 'export_%'")->fetchAll(PDO::FETCH_ASSOC);
        $insRp = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($allNew as $perm) {
            $insRp->execute([1, (int)$perm['id']]);
        }

        // School Admin (id=2): same as super admin for module permissions
        foreach ($allNew as $perm) {
            $insRp->execute([2, (int)$perm['id']]);
        }

        // Teacher (id=4): Academic view + some create
        $teacherPerms = [
            'view_academic_years', 'view_main_groups', 'view_curriculum', 'view_subjects', 'view_classes',
            'view_students_list', 'create_students_list', 'edit_students_list',
            'view_teachers',
            'view_acad_attendance', 'create_acad_attendance', 'edit_acad_attendance',
            'view_timetable',
            'view_assessments', 'create_assessments', 'edit_assessments',
            'view_exams', 'create_exams', 'edit_exams',
            'view_report_cards', 'create_report_cards', 'edit_report_cards',
            'view_announcements',
            'view_academic_settings',
            'view_student_reports',
        ];
        $findPerm = $this->db->prepare("SELECT id FROM permissions WHERE slug = ?");
        $insRpTeacher = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($teacherPerms as $slug) {
            $findPerm->execute([$slug]);
            $perm = $findPerm->fetch(PDO::FETCH_ASSOC);
            if ($perm) {
                $insRpTeacher->execute([4, (int)$perm['id']]);
            }
        }

        // Staff (id=6): basic staff permissions
        $staffPerms = [
            'view_staff_overview', 'view_staff_directory',
            'view_staff_attendance', 'create_staff_attendance',
            'view_leave_management', 'create_leave_management',
            'view_student_documents', 'create_student_documents',
            'view_enrollments',
        ];
        $insRpStaff = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($staffPerms as $slug) {
            $findPerm->execute([$slug]);
            $perm = $findPerm->fetch(PDO::FETCH_ASSOC);
            if ($perm) {
                $insRpStaff->execute([6, (int)$perm['id']]);
            }
        }
    }

    public function down(): void
    {
        $moduleSlugs = [
            'view_academic_years', 'create_academic_years', 'edit_academic_years', 'delete_academic_years',
            'view_main_groups', 'create_main_groups', 'edit_main_groups', 'delete_main_groups',
            'view_curriculum', 'create_curriculum', 'edit_curriculum', 'delete_curriculum',
            'view_subjects', 'create_subjects', 'edit_subjects', 'delete_subjects',
            'view_classes', 'create_classes', 'edit_classes', 'delete_classes',
            'view_students_list', 'create_students_list', 'edit_students_list', 'delete_students_list',
            'view_teachers', 'create_teachers', 'edit_teachers', 'delete_teachers',
            'view_acad_attendance', 'create_acad_attendance', 'edit_acad_attendance', 'delete_acad_attendance',
            'view_timetable', 'create_timetable', 'edit_timetable', 'delete_timetable',
            'view_assessments', 'create_assessments', 'edit_assessments', 'delete_assessments',
            'view_exams', 'create_exams', 'edit_exams', 'delete_exams',
            'view_report_cards', 'create_report_cards', 'edit_report_cards', 'delete_report_cards',
            'view_promotion', 'create_promotion', 'edit_promotion', 'delete_promotion',
            'view_announcements', 'create_announcements', 'edit_announcements', 'delete_announcements',
            'view_academic_settings', 'edit_academic_settings',
            'view_staff_overview',
            'view_staff_directory', 'create_staff_directory', 'edit_staff_directory', 'delete_staff_directory',
            'view_departments', 'create_departments', 'edit_departments', 'delete_departments',
            'view_designations', 'create_designations', 'edit_designations', 'delete_designations',
            'view_staff_attendance', 'create_staff_attendance', 'edit_staff_attendance', 'delete_staff_attendance',
            'view_face_kiosk', 'create_face_kiosk',
            'view_face_register', 'create_face_register', 'edit_face_register', 'delete_face_register',
            'view_leave_management', 'create_leave_management', 'edit_leave_management', 'delete_leave_management',
            'view_staff_roles', 'create_staff_roles', 'edit_staff_roles', 'delete_staff_roles',
            'view_staff_user_accounts', 'create_staff_user_accounts', 'edit_staff_user_accounts', 'delete_staff_user_accounts',
            'view_staff_settings', 'edit_staff_settings',
            'view_fees_dashboard',
            'view_all_invoices', 'create_all_invoices', 'edit_all_invoices', 'delete_all_invoices',
            'view_fee_structures', 'create_fee_structures', 'edit_fee_structures', 'delete_fee_structures',
            'view_batch_generator', 'create_batch_generator',
            'view_receipts_log', 'create_receipts_log',
            'view_fee_categories', 'create_fee_categories', 'edit_fee_categories', 'delete_fee_categories',
            'view_late_fee_policies', 'create_late_fee_policies', 'edit_late_fee_policies', 'delete_late_fee_policies',
            'view_transport_overview',
            'view_transport_drivers', 'create_transport_drivers', 'edit_transport_drivers', 'delete_transport_drivers',
            'view_student_transport', 'create_student_transport', 'edit_student_transport', 'delete_student_transport',
            'view_live_tracking',
            'view_transport_settings', 'edit_transport_settings',
            'view_transport_logs',
            'view_payroll_runs', 'create_payroll_runs', 'edit_payroll_runs', 'delete_payroll_runs',
            'view_payroll_attendance',
            'view_holidays_calendar', 'create_holidays_calendar', 'edit_holidays_calendar', 'delete_holidays_calendar',
            'view_payroll_audit',
            'view_documents_overview',
            'view_student_documents', 'create_student_documents', 'edit_student_documents', 'delete_student_documents',
            'view_parent_documents', 'create_parent_documents', 'edit_parent_documents', 'delete_parent_documents',
            'view_driver_documents', 'create_driver_documents', 'edit_driver_documents', 'delete_driver_documents',
            'view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates',
            'view_reports_overview',
            'view_financial_reports', 'export_financial_reports',
            'view_student_reports', 'export_student_reports',
            'view_staff_reports', 'export_staff_reports',
            'view_whatsapp_logs',
            'view_general_settings', 'edit_general_settings',
            'view_integrations', 'edit_integrations',
            'view_system_config', 'edit_system_config',
            'view_enrollments', 'approve_enrollments', 'reject_enrollments',
        ];

        $placeholders = implode(',', array_fill(0, count($moduleSlugs), '?'));
        $this->db->prepare("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))")->execute($moduleSlugs);
        $this->db->prepare("DELETE FROM `permissions` WHERE slug IN ($placeholders)")->execute($moduleSlugs);
    }
}
