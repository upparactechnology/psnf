<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class EnrollmentAdminController extends Controller
{
    public function index(): string
    {
        $db = Application::$app->db;
        $status = (string)($_GET['status'] ?? 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $enrollments = $db->select(
            "SELECT * FROM online_enrollments WHERE status = ? ORDER BY created_at DESC",
            [$status]
        );

        return $this->render('students/pending_enrollments', [
            'enrollments'   => $enrollments,
            'currentStatus' => $status,
        ]);
    }

    public function show(string $id): string
    {
        $db = Application::$app->db;
        $enrollment = $db->selectOne("SELECT * FROM online_enrollments WHERE id = ?", [(int)$id]);

        if (!$enrollment) {
            Session::flash('error', 'Enrollment record not found.');
            return $this->redirect(url('students/enrollments'));
        }

        $tenantId = \Core\Database::getTenantId();
        $schools  = $db->select("SELECT * FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY name ASC", [$tenantId]);
        $branches = $db->select("SELECT * FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY name ASC", [$tenantId]);

        return $this->render('students/show_enrollment', [
            'enrollment' => $enrollment,
            'schools'    => $schools,
            'branches'   => $branches,
        ]);
    }

    public function approve(string $id): string
    {
        $schoolId = (int)($_POST['school_id'] ?? 0);
        $branchId = (int)($_POST['branch_id'] ?? 0);

        if (!$schoolId || !$branchId) {
            Session::flash('error', 'Please select School and Branch before approving.');
            return $this->redirect(url('students/enrollments/' . (int)$id));
        }

        try {
            $result = $this->importEnrollment((int)$id, $schoolId, $branchId, false);
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to approve application: ' . $e->getMessage());
            return $this->redirect(url('students/enrollments/' . (int)$id));
        }

        if ($result === null) {
            Session::flash('error', 'Enrollment record not found or already processed.');
            return $this->redirect(url('students/enrollments'));
        }

        Session::flash('success', 'Student online application approved and imported into system successfully! Admission No: ' . $result['admission_number']);
        return $this->redirect(url('academics/students/' . $result['student_id']));
    }

    public function quickEnroll(string $id): string
    {
        $isAjax   = $this->request->isAjax();
        $schoolId = (int)($_POST['school_id'] ?? 0);
        $branchId = (int)($_POST['branch_id'] ?? 0);

        if (!$schoolId || !$branchId) {
            $msg = 'Please select School and Branch before enrolling.';
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => $msg], 422);
            }
            Session::flash('error', $msg);
            return $this->redirect(url('academics/students'));
        }

        try {
            $result = $this->importEnrollment((int)$id, $schoolId, $branchId, true);
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Enrollment failed: ' . $e->getMessage()], 500);
            }
            Session::flash('error', 'Enrollment failed: ' . $e->getMessage());
            return $this->redirect(url('academics/students'));
        }

        if ($result === null) {
            $msg = 'Enrollment record not found or already processed.';
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => $msg], 404);
            }
            Session::flash('error', $msg);
            return $this->redirect(url('academics/students'));
        }

        Session::flash('success', 'Student enrolled successfully! Admission No: ' . $result['admission_number']);

        if ($isAjax) {
            return $this->json(['success' => true, 'student_id' => $result['student_id'], 'admission_no' => $result['admission_number']]);
        }

        return $this->redirect(url('academics/students/' . $result['student_id']));
    }

    public function reject(string $id): string
    {
        $db = Application::$app->db;
        $user = \auth();
        $notes = trim((string)($_POST['admin_notes'] ?? '')) ?: 'Application rejected by school admin.';

        $affected = $db->query(
            "UPDATE online_enrollments SET status = 'rejected', admin_notes = ?, processed_by = ?, processed_at = NOW() WHERE id = ? AND status = 'pending'",
            [$notes, $user['id'] ?? null, (int)$id]
        )->rowCount();

        if ($affected > 0) {
            Session::flash('success', 'Application rejected.');
        } else {
            Session::flash('error', 'Enrollment record not found or already processed.');
        }
        return $this->redirect(url('students/enrollments'));
    }

    /**
     * Import a pending online enrollment as a student (plus guardians, pickup persons and
     * documents) in ONE transaction. The enrollment row is locked with SELECT ... FOR UPDATE
     * and re-checked for status = 'pending', so double clicks / concurrent approvals cannot
     * create duplicate students.
     *
     * @return array{student_id:int, admission_number:string}|null  null when not pending
     */
    private function importEnrollment(int $id, int $schoolId, int $branchId, bool $enrolled): ?array
    {
        $db = Application::$app->db;
        $user = \auth();
        $tenantId = (int)($user['tenant_id'] ?? \Core\Database::getTenantId() ?? 1);

        // School/branch must belong to the admin's tenant
        $okBranch = $db->selectOne(
            "SELECT b.id FROM branches b JOIN schools s ON s.id = b.school_id
             WHERE b.id = ? AND s.id = ? AND s.tenant_id = ? LIMIT 1",
            [$branchId, $schoolId, $tenantId]
        );
        if (!$okBranch) {
            throw new \RuntimeException('Selected school/branch is not valid.');
        }

        return $db->transaction(function ($db) use ($id, $schoolId, $branchId, $enrolled, $user, $tenantId) {
            $enrollment = $db->selectOne("SELECT * FROM online_enrollments WHERE id = ? FOR UPDATE", [$id]);
            if (!$enrollment || ($enrollment['status'] ?? '') !== 'pending') {
                return null;
            }

            // Split Full Name into First, Middle, Last
            $nameParts  = preg_split('/\s+/', trim((string)$enrollment['student_full_name']));
            $firstName  = array_shift($nameParts) ?: 'Student';
            $lastName   = array_pop($nameParts) ?: '';
            $middleName = implode(' ', $nameParts);

            $studentRow = [
                'uuid'          => \str_uuid(),
                'tenant_id'     => $tenantId,
                'school_id'     => $schoolId,
                'branch_id'     => $branchId,
                'first_name'    => $firstName,
                'middle_name'   => $middleName,
                'last_name'     => $lastName,
                'gender'        => $enrollment['gender'],
                'dob'           => $enrollment['dob'],
                'photo'         => $enrollment['student_photo'],
                'aadhar_number' => $enrollment['student_aadhar'],
                'address'       => $enrollment['address'],
                'created_by'    => $user['id'] ?? null,
                'created_at'    => date('Y-m-d H:i:s'),
            ];
            if ($enrolled) {
                $studentRow['admission_status'] = 'enrolled';
            }

            // 1. Insert student with a sequential, unique admission number (retry on collision)
            $studentId = 0;
            $admissionNo = '';
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $admissionNo = $this->nextAdmissionNumber($db);
                try {
                    $studentId = (int)$db->insert('students', $studentRow + ['admission_number' => $admissionNo]);
                    break;
                } catch (\PDOException $e) {
                    if (($e->errorInfo[1] ?? 0) !== 1062 || $attempt === 4) {
                        throw $e;
                    }
                }
            }

            // 2/3. Father & Mother
            $isPrimary = 1;
            foreach ([['father', 'Father'], ['mother', 'Mother']] as [$key, $relation]) {
                if (empty($enrollment[$key . '_name'])) {
                    continue;
                }
                $guardianId = (int)$db->insert('guardians', [
                    'tenant_id'    => $tenantId,
                    'name'         => $enrollment[$key . '_name'],
                    'relationship' => $relation,
                    'phone'        => (string)($enrollment[$key . '_phone'] ?? ''),
                    'aadhar'       => $enrollment[$key . '_aadhar'] ?: null,
                    'photo'        => $enrollment[$key . '_photo'] ?: null,
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
                $db->query(
                    "INSERT IGNORE INTO guardian_student (guardian_id, student_id, is_primary, can_pickup, created_at) VALUES (?, ?, ?, 1, NOW())",
                    [$guardianId, $studentId, $isPrimary]
                );
                $isPrimary = 0;
            }

            // 4. Pickup persons & emergency contacts
            $pickups = json_decode((string)($enrollment['pickup_persons_json'] ?? '[]'), true);
            if (is_array($pickups)) {
                foreach ($pickups as $p) {
                    if (!is_array($p) || empty($p['name'])) {
                        continue;
                    }
                    $pickupGuardianId = (int)$db->insert('guardians', [
                        'tenant_id'    => $tenantId,
                        'name'         => $p['name'],
                        'relationship' => ($p['relationship'] ?? '') ?: 'Pickup Person',
                        'phone'        => (string)($p['phone'] ?? ''),
                        'email'        => ($p['email'] ?? '') ?: null,
                        'address'      => ($p['address'] ?? '') ?: null,
                        'photo'        => ($p['photo'] ?? '') ?: null,
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                    $db->query(
                        "INSERT IGNORE INTO guardian_student (guardian_id, student_id, is_primary, can_pickup, created_at) VALUES (?, ?, 0, 1, NOW())",
                        [$pickupGuardianId, $studentId]
                    );

                    if (!empty($p['is_emergency'])) {
                        $db->insert('emergency_contacts', [
                            'student_id'   => $studentId,
                            'name'         => $p['name'],
                            'relationship' => ($p['relationship'] ?? '') ?: 'Guardian',
                            'phone'        => (string)($p['phone'] ?? ''),
                            'address'      => ($p['address'] ?? '') ?: null,
                            'created_at'   => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            // 5. Attach Aadhar documents
            foreach ([
                'father_aadhar_doc'  => 'Father Aadhar Card',
                'mother_aadhar_doc'  => 'Mother Aadhar Card',
                'student_aadhar_doc' => 'Student Aadhar Card',
            ] as $col => $title) {
                if (empty($enrollment[$col])) {
                    continue;
                }
                $db->insert('student_documents', [
                    'student_id'  => $studentId,
                    'type'        => 'aadhar',
                    'title'       => $title,
                    'file_name'   => $enrollment[$col],
                    'stored_name' => $enrollment[$col],
                    'mime_type'   => 'application/octet-stream',
                    'file_size'   => 0,
                    'status'      => 'verified',
                    'created_by'  => $user['id'] ?? null,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }

            // 6. Mark enrollment as approved
            $db->query(
                "UPDATE online_enrollments SET status = 'approved', processed_by = ?, processed_at = NOW() WHERE id = ?",
                [$user['id'] ?? null, $id]
            );

            return ['student_id' => $studentId, 'admission_number' => $admissionNo];
        });
    }

    /** Next sequential admission number: ADM-<year>-<0001…>. */
    private function nextAdmissionNumber($db): string
    {
        $prefix = 'ADM-' . date('Y') . '-';
        $row = $db->selectOne(
            "SELECT MAX(CAST(SUBSTRING(admission_number, ?) AS UNSIGNED)) AS mx
             FROM students WHERE admission_number LIKE ? AND admission_number REGEXP ?",
            [strlen($prefix) + 1, $prefix . '%', '^' . $prefix . '[0-9]+$']
        );
        $next = (int)($row['mx'] ?? 0) + 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
