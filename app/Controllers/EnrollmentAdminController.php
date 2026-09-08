<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class EnrollmentAdminController extends Controller
{
    public function index(): void
    {
        $db = Application::$app->db;
        $status = $_GET['status'] ?? 'pending';

        $enrollments = $db->query(
            "SELECT * FROM online_enrollments WHERE status = :status ORDER BY created_at DESC",
            ['status' => $status]
        );

        $this->render('students/pending_enrollments', [
            'enrollments' => $enrollments,
            'currentStatus' => $status
        ]);
    }

    public function show(string $id): void
    {
        $db = Application::$app->db;
        $enrollment = $db->query("SELECT * FROM online_enrollments WHERE id = :id", ['id' => $id])[0] ?? null;

        if (!$enrollment) {
            $this->redirect('/students/enrollments');
            return;
        }

        $schools = $db->query("SELECT * FROM schools WHERE status = 'active' ORDER BY name ASC");
        $branches = $db->query("SELECT * FROM branches WHERE status = 'active' ORDER BY name ASC");

        $this->render('students/show_enrollment', [
            'enrollment' => $enrollment,
            'schools'    => $schools,
            'branches'   => $branches
        ]);
    }

    public function approve(string $id): void
    {
        $db = Application::$app->db;
        $enrollment = $db->query("SELECT * FROM online_enrollments WHERE id = :id AND status = 'pending'", ['id' => $id])[0] ?? null;

        if (!$enrollment) {
            \Core\Session::setFlash('errors', ['enrollment' => 'Enrollment record not found or already processed.']);
            $this->redirect('/students/enrollments');
            return;
        }

        $schoolId = $_POST['school_id'] ?? null;
        $branchId = $_POST['branch_id'] ?? null;

        if (!$schoolId || !$branchId) {
            \Core\Session::setFlash('errors', ['school_id' => 'Please select School and Branch before approving.']);
            $this->redirect('/students/enrollments/' . $id);
            return;
        }

        $user = \auth();
        $tenantId = $user['tenant_id'] ?? 1;

        // Split Full Name into First, Middle, Last
        $nameParts = explode(' ', trim($enrollment['student_full_name']));
        $firstName = array_shift($nameParts) ?: 'Student';
        $lastName  = array_pop($nameParts) ?: '';
        $middleName = implode(' ', $nameParts);

        // Generate Admission Number
        $admissionNo = 'ADM-' . date('Y') . '-' . rand(1000, 9999);
        $uuid = \str_uuid();

        // 1. Insert into `students` table
        $db->query("INSERT INTO students (
            uuid, tenant_id, school_id, branch_id, admission_number, first_name, middle_name, last_name,
            gender, dob, photo, aadhar_number, address, created_at
        ) VALUES (
            :uuid, :tenant_id, :school_id, :branch_id, :adm, :fname, :mname, :lname,
            :gender, :dob, :photo, :aadhar, :address, NOW()
        )", [
            'uuid'       => $uuid,
            'tenant_id'  => $tenantId,
            'school_id'  => $schoolId,
            'branch_id'  => $branchId,
            'adm'        => $admissionNo,
            'fname'      => $firstName,
            'mname'      => $middleName,
            'lname'      => $lastName,
            'gender'     => $enrollment['gender'],
            'dob'        => $enrollment['dob'],
            'photo'      => $enrollment['student_photo'],
            'aadhar'     => $enrollment['student_aadhar'],
            'address'    => $enrollment['address']
        ]);

        $studentId = $db->getLastInsertId();

        // 2. Insert Father Guardian
        if (!empty($enrollment['father_name'])) {
            $db->query("INSERT INTO guardians (
                tenant_id, name, relationship, phone, aadhar, photo, created_at
            ) VALUES (
                :tenant_id, :name, 'Father', :phone, :aadhar, :photo, NOW()
            )", [
                'tenant_id'  => $tenantId,
                'name'       => $enrollment['father_name'],
                'phone'      => $enrollment['father_phone'],
                'aadhar'     => $enrollment['father_aadhar'],
                'photo'      => $enrollment['father_photo']
            ]);
            $guardianId = $db->getLastInsertId();
            $db->insert('guardian_student', [
                'guardian_id' => $guardianId,
                'student_id' => $studentId,
                'is_primary' => 1,
                'can_pickup' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 3. Insert Mother Guardian
        if (!empty($enrollment['mother_name'])) {
            $db->query("INSERT INTO guardians (
                tenant_id, name, relationship, phone, aadhar, photo, created_at
            ) VALUES (
                :tenant_id, :name, 'Mother', :phone, :aadhar, :photo, NOW()
            )", [
                'tenant_id'  => $tenantId,
                'name'       => $enrollment['mother_name'],
                'phone'      => $enrollment['mother_phone'],
                'aadhar'     => $enrollment['mother_aadhar'],
                'photo'      => $enrollment['mother_photo']
            ]);
            $guardianId = $db->getLastInsertId();
            $db->insert('guardian_student', [
                'guardian_id' => $guardianId,
                'student_id' => $studentId,
                'is_primary' => 0,
                'can_pickup' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 4. Insert Pickup Persons & Emergency Contacts
        $pickups = json_decode($enrollment['pickup_persons_json'] ?? '[]', true);
        if (is_array($pickups)) {
            foreach ($pickups as $p) {
                $db->query("INSERT INTO guardians (
                    tenant_id, name, relationship, phone, email, address, photo, created_at
                ) VALUES (
                    :tenant_id, :name, :relation, :phone, :email, :address, :photo, NOW()
                )", [
                    'tenant_id'  => $tenantId,
                    'name'       => $p['name'],
                    'relation'   => $p['relationship'] ?: 'Pickup Person',
                    'phone'      => $p['phone'] ?: null,
                    'email'      => $p['email'] ?: null,
                    'address'    => $p['address'] ?: null,
                    'photo'      => $p['photo'] ?: null
                ]);
                $pickupGuardianId = $db->getLastInsertId();

                $db->insert('guardian_student', [
                    'guardian_id' => $pickupGuardianId,
                    'student_id'  => $studentId,
                    'is_primary'  => 0,
                    'can_pickup'  => 1,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);

                if (!empty($p['is_emergency'])) {
                    $db->query("INSERT INTO emergency_contacts (
                        student_id, name, relationship, phone, address, created_at
                    ) VALUES (
                        :student_id, :name, :relation, :phone, :address, NOW()
                    )", [
                        'student_id' => $studentId,
                        'name'       => $p['name'],
                        'relation'   => $p['relationship'] ?: 'Guardian',
                        'phone'      => $p['phone'] ?: '',
                        'address'    => $p['address'] ?: null
                    ]);
                }
            }
        }

        // 5. Attach Aadhar Documents to `student_documents`
        if (!empty($enrollment['father_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Father Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['father_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }
        if (!empty($enrollment['mother_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Mother Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['mother_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }
        if (!empty($enrollment['student_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Student Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['student_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }

        // 6. Mark enrollment as Approved
        $db->query("UPDATE online_enrollments SET status = 'approved', processed_by = :user, processed_at = NOW() WHERE id = :id", [
            'user' => $user['id'] ?? null,
            'id'   => $id
        ]);

        \Core\Session::setFlash('success', 'Student online application approved and imported into system successfully!');
        $this->redirect('/students/' . $studentId);
    }

    public function quickEnroll(string $id): void
    {
        $db = Application::$app->db;
        $enrollment = $db->selectOne("SELECT * FROM online_enrollments WHERE id = :id AND status = 'pending'", ['id' => $id]);

        if (!$enrollment) {
            \Core\Session::setFlash('errors', ['enrollment' => 'Enrollment record not found or already processed.']);
            $this->redirect('/academics/students');
            return;
        }

        $schoolId = $_POST['school_id'] ?? null;
        $branchId = $_POST['branch_id'] ?? null;

        if (!$schoolId || !$branchId) {
            \Core\Session::setFlash('errors', ['school_id' => 'Please select School and Branch before enrolling.']);
            $this->redirect('/academics/students');
            return;
        }

        $user = \auth();
        $tenantId = $user['tenant_id'] ?? 1;

        // Split Full Name into First, Middle, Last
        $nameParts = explode(' ', trim($enrollment['student_full_name']));
        $firstName = array_shift($nameParts) ?: 'Student';
        $lastName  = array_pop($nameParts) ?: '';
        $middleName = implode(' ', $nameParts);

        // Generate Admission Number
        $admissionNo = 'ADM-' . date('Y') . '-' . rand(1000, 9999);
        $uuid = \str_uuid();

        // 1. Insert into `students` table
        $db->query("INSERT INTO students (
            uuid, tenant_id, school_id, branch_id, admission_number, first_name, middle_name, last_name,
            gender, dob, photo, aadhar_number, address, admission_status, created_at
        ) VALUES (
            :uuid, :tenant_id, :school_id, :branch_id, :adm, :fname, :mname, :lname,
            :gender, :dob, :photo, :aadhar, :address, 'enrolled', NOW()
        )", [
            'uuid'       => $uuid,
            'tenant_id'  => $tenantId,
            'school_id'  => $schoolId,
            'branch_id'  => $branchId,
            'adm'        => $admissionNo,
            'fname'      => $firstName,
            'mname'      => $middleName,
            'lname'      => $lastName,
            'gender'     => $enrollment['gender'],
            'dob'        => $enrollment['dob'],
            'photo'      => $enrollment['student_photo'],
            'aadhar'     => $enrollment['student_aadhar'],
            'address'    => $enrollment['address']
        ]);

        $studentId = $db->getLastInsertId();

        // 2. Insert Father Guardian
        if (!empty($enrollment['father_name'])) {
            $db->query("INSERT INTO guardians (
                tenant_id, name, relationship, phone, aadhar, photo, created_at
            ) VALUES (
                :tenant_id, :name, 'Father', :phone, :aadhar, :photo, NOW()
            )", [
                'tenant_id'  => $tenantId,
                'name'       => $enrollment['father_name'],
                'phone'      => $enrollment['father_phone'],
                'aadhar'     => $enrollment['father_aadhar'],
                'photo'      => $enrollment['father_photo']
            ]);
            $guardianId = $db->getLastInsertId();
            $db->insert('guardian_student', [
                'guardian_id' => $guardianId,
                'student_id' => $studentId,
                'is_primary' => 1,
                'can_pickup' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 3. Insert Mother Guardian
        if (!empty($enrollment['mother_name'])) {
            $db->query("INSERT INTO guardians (
                tenant_id, name, relationship, phone, aadhar, photo, created_at
            ) VALUES (
                :tenant_id, :name, 'Mother', :phone, :aadhar, :photo, NOW()
            )", [
                'tenant_id'  => $tenantId,
                'name'       => $enrollment['mother_name'],
                'phone'      => $enrollment['mother_phone'],
                'aadhar'     => $enrollment['mother_aadhar'],
                'photo'      => $enrollment['mother_photo']
            ]);
            $guardianId = $db->getLastInsertId();
            $db->insert('guardian_student', [
                'guardian_id' => $guardianId,
                'student_id' => $studentId,
                'is_primary' => 0,
                'can_pickup' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 4. Insert Pickup Persons & Emergency Contacts
        $pickups = json_decode($enrollment['pickup_persons_json'] ?? '[]', true);
        if (is_array($pickups)) {
            foreach ($pickups as $p) {
                $db->query("INSERT INTO guardians (
                    tenant_id, name, relationship, phone, email, address, photo, created_at
                ) VALUES (
                    :tenant_id, :name, :relation, :phone, :email, :address, :photo, NOW()
                )", [
                    'tenant_id'  => $tenantId,
                    'name'       => $p['name'],
                    'relation'   => $p['relationship'] ?: 'Pickup Person',
                    'phone'      => $p['phone'] ?: null,
                    'email'      => $p['email'] ?: null,
                    'address'    => $p['address'] ?: null,
                    'photo'      => $p['photo'] ?: null
                ]);
                $pickupGuardianId = $db->getLastInsertId();

                $db->insert('guardian_student', [
                    'guardian_id' => $pickupGuardianId,
                    'student_id'  => $studentId,
                    'is_primary'  => 0,
                    'can_pickup'  => 1,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);

                if (!empty($p['is_emergency'])) {
                    $db->query("INSERT INTO emergency_contacts (
                        student_id, name, relationship, phone, address, created_at
                    ) VALUES (
                        :student_id, :name, :relation, :phone, :address, NOW()
                    )", [
                        'student_id' => $studentId,
                        'name'       => $p['name'],
                        'relation'   => $p['relationship'] ?: 'Guardian',
                        'phone'      => $p['phone'] ?: '',
                        'address'    => $p['address'] ?: null
                    ]);
                }
            }
        }

        // 5. Attach Aadhar Documents
        if (!empty($enrollment['father_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Father Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['father_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }
        if (!empty($enrollment['mother_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Mother Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['mother_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }
        if (!empty($enrollment['student_aadhar_doc'])) {
            $db->query("INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_by, created_at) VALUES (:s, 'aadhar', 'Student Aadhar Card', :name, :name, 'application/octet-stream', 0, 'verified', :user, NOW())", [
                's' => $studentId, 'name' => $enrollment['student_aadhar_doc'], 'user' => $user['id'] ?? null
            ]);
        }

        // 6. Mark enrollment as Approved + processed
        $db->query("UPDATE online_enrollments SET status = 'approved', processed_by = :user, processed_at = NOW() WHERE id = :id", [
            'user' => $user['id'] ?? null,
            'id'   => $id
        ]);

        \Core\Session::setFlash('success', 'Student enrolled successfully! Admission No: ' . $admissionNo);
        $this->redirect('/academics/students/' . $studentId);
    }

    public function reject(string $id): void
    {
        $db = Application::$app->db;
        $user = \auth();
        $notes = $_POST['admin_notes'] ?? 'Application rejected by school admin.';

        $db->query("UPDATE online_enrollments SET status = 'rejected', admin_notes = :notes, processed_by = :user, processed_at = NOW() WHERE id = :id", [
            'notes' => $notes,
            'user'  => $user['id'] ?? null,
            'id'    => $id
        ]);

        \Core\Session::setFlash('success', 'Application rejected.');
        $this->redirect('/students/enrollments');
    }
}
