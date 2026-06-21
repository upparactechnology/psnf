<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{Student, StudentMedical, EmergencyContact, StudentDocument, StudentTimeline, Guardian};
use Core\Database;

class StudentService
{
    public function create(array $data, array $files = []): int
    {
        return \Core\Application::$app->db->transaction(function (Database $db) use ($data, $files) {
            // Extract guardian fields
            $guardianData = [];
            if (!empty($data['guardian_name'])) {
                $guardianData = [
                    'name'         => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                    'phone'        => $data['guardian_phone'] ?? '',
                    'email'        => $data['guardian_email'] ?: null,
                    'aadhar'       => $data['guardian_aadhar'] ?: null,
                ];
            }

            // Extract medical fields
            $medicalData = [
                'allergies'           => $data['allergies'] ?: null,
                'triggers'            => $data['triggers'] ?: null,
                'current_medications' => $data['medications'] ?: null,
                'care_instructions'   => $data['care_instructions'] ?: null,
            ];

            // Filter student table fields
            $studentKeys = [
                'uuid', 'tenant_id', 'school_id', 'branch_id', 'admission_number', 'gr_number',
                'first_name', 'middle_name', 'last_name', 'gender', 'dob', 'photo', 'blood_group',
                'nationality', 'religion', 'mother_tongue', 'aadhar_number', 'disability_type',
                'disability_detail', 'disability_certificate', 'care_instructions',
                'special_needs_summary', 'address', 'city', 'state', 'pincode', 'admission_status',
                'admission_date', 'enrolled_date', 'class', 'section', 'academic_year',
                'is_active', 'notes', 'created_by', 'updated_by'
            ];
            $studentData = array_intersect_key($data, array_flip($studentKeys));

            $studentData['uuid']             = str_uuid();
            $studentData['admission_number'] = admission_number((int)($data['school_id'] ?? 1));
            $studentData['gr_number']        = gr_number((int)($data['school_id'] ?? 1));
            $studentData['created_by']       = auth_id();

            $studentId = (int) Student::create($studentData);

            // Handle student image photo upload if present
            if (!empty($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = storage_path('uploads/students/' . $studentId);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $storedName = bin2hex(random_bytes(16)) . '.' . pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($files['photo']['tmp_name'], $uploadDir . '/' . $storedName)) {
                    Student::update($studentId, ['photo' => $storedName]);
                }
            }

            // Create medical record with initial data
            $medicalData['student_id'] = $studentId;
            $medicalData['created_by'] = auth_id();
            StudentMedical::create($medicalData);

            // Save guardian details
            if (!empty($guardianData['name']) && !empty($guardianData['phone'])) {
                // Check if guardian with phone already exists
                $existing = $db->selectOne(
                    "SELECT id, email, aadhar FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                    [$guardianData['phone'], Database::getTenantId()]
                );

                if ($existing) {
                    $guardianId = (int)$existing['id'];
                    $updateFields = [];
                    if (empty($existing['aadhar']) && !empty($guardianData['aadhar'])) $updateFields['aadhar'] = $guardianData['aadhar'];
                    if (empty($existing['email']) && !empty($guardianData['email'])) $updateFields['email'] = $guardianData['email'];
                    if (!empty($updateFields)) {
                        Guardian::update($guardianId, $updateFields);
                    }
                } else {
                    $guardianData['tenant_id']  = Database::getTenantId();
                    $guardianData['created_by'] = auth_id();
                    $guardianId = (int) Guardian::create($guardianData);
                }

                // Link guardian to student if not linked
                $linkExists = $db->selectOne("SELECT 1 FROM guardian_student WHERE guardian_id = ? AND student_id = ?", [$guardianId, $studentId]);
                if (!$linkExists) {
                    $db->insert('guardian_student', [
                        'guardian_id' => $guardianId,
                        'student_id'  => $studentId,
                        'is_primary'  => 1,
                        'can_pickup'  => 1,
                    ]);
                }
            }

            // Log timeline
            StudentTimeline::logEvent($studentId, 'admission', 'Application submitted', ['status' => 'applied'], auth_id(), 'purple', 'user-plus');

            \App\Models\ActivityLog::log('student_created', auth_id(), ['student_id' => $studentId]);

            return $studentId;
        });
    }

    public function update(int $studentId, array $data, array $files = []): bool
    {
        $student = Student::find($studentId);
        if (!$student) return false;

        return \Core\Application::$app->db->transaction(function (Database $db) use ($studentId, $student, $data, $files) {
            $data['updated_by'] = auth_id();

            // Track status change
            if (!empty($data['admission_status']) && $data['admission_status'] !== $student['admission_status']) {
                Student::updateStatus($studentId, $data['admission_status'], auth_id());
            }

            // Extract guardian fields
            $guardianData = [];
            if (!empty($data['guardian_name'])) {
                $guardianData = [
                    'name'         => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                    'phone'        => $data['guardian_phone'] ?? '',
                    'email'        => $data['guardian_email'] ?: null,
                    'aadhar'       => $data['guardian_aadhar'] ?: null,
                ];
            }

            // Extract medical fields
            $medicalData = [
                'allergies'           => $data['allergies'] ?: null,
                'triggers'            => $data['triggers'] ?: null,
                'current_medications' => $data['medications'] ?: null,
                'care_instructions'   => $data['care_instructions'] ?: null,
            ];

            // Filter student table fields
            $studentKeys = [
                'school_id', 'branch_id', 'first_name', 'middle_name', 'last_name', 'gender', 'dob',
                'blood_group', 'nationality', 'religion', 'mother_tongue', 'aadhar_number',
                'disability_type', 'disability_detail', 'care_instructions', 'special_needs_summary',
                'address', 'city', 'state', 'pincode', 'admission_status', 'admission_date',
                'enrolled_date', 'class', 'section', 'academic_year', 'is_active', 'notes', 'updated_by'
            ];
            $studentData = array_intersect_key($data, array_flip($studentKeys));

            // Handle student photo image upload if present
            if (!empty($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = storage_path('uploads/students/' . $studentId);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $storedName = bin2hex(random_bytes(16)) . '.' . pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($files['photo']['tmp_name'], $uploadDir . '/' . $storedName)) {
                    $studentData['photo'] = $storedName;
                    // Delete old photo if it exists
                    if (!empty($student['photo']) && file_exists($uploadDir . '/' . $student['photo'])) {
                        @unlink($uploadDir . '/' . $student['photo']);
                    }
                }
            }

            // Update student
            Student::update($studentId, $studentData);

            // Update medical record
            $existingMedical = StudentMedical::findBy('student_id', $studentId);
            $medicalData['updated_by'] = auth_id();
            if ($existingMedical) {
                StudentMedical::update((int)$existingMedical['id'], $medicalData);
            } else {
                $medicalData['student_id'] = $studentId;
                $medicalData['created_by'] = auth_id();
                StudentMedical::create($medicalData);
            }

            // Update or link guardian details
            if (!empty($guardianData['name']) && !empty($guardianData['phone'])) {
                // Get existing primary guardian linked to this student
                $existingLink = $db->selectOne(
                    "SELECT g.* FROM guardians g
                     JOIN guardian_student gs ON gs.guardian_id = g.id
                     WHERE gs.student_id = ? AND gs.is_primary = 1 AND g.deleted_at IS NULL LIMIT 1",
                    [$studentId]
                );

                if ($existingLink) {
                    $guardianData['updated_by'] = auth_id();
                    Guardian::update((int)$existingLink['id'], $guardianData);
                } else {
                    // Check if guardian with this phone already exists in DB
                    $existingGuardian = $db->selectOne(
                        "SELECT id FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                        [$guardianData['phone'], Database::getTenantId()]
                    );

                    if ($existingGuardian) {
                        $guardianId = (int)$existingGuardian['id'];
                        $guardianData['updated_by'] = auth_id();
                        Guardian::update($guardianId, $guardianData);
                    } else {
                        $guardianData['tenant_id']  = Database::getTenantId();
                        $guardianData['created_by'] = auth_id();
                        $guardianId = (int) Guardian::create($guardianData);
                    }

                    // Link to student
                    $linkExists = $db->selectOne("SELECT 1 FROM guardian_student WHERE guardian_id = ? AND student_id = ?", [$guardianId, $studentId]);
                    if (!$linkExists) {
                        $db->insert('guardian_student', [
                            'guardian_id' => $guardianId,
                            'student_id'  => $studentId,
                            'is_primary'  => 1,
                            'can_pickup'  => 1,
                        ]);
                    }
                }
            }

            \App\Models\ActivityLog::log('student_updated', auth_id(), ['student_id' => $studentId]);
            return true;
        });
    }

    public function updateMedical(int $studentId, array $data): bool
    {
        $existing = StudentMedical::findBy('student_id', $studentId);
        $data['updated_by'] = auth_id();

        if ($existing) {
            StudentMedical::update($existing['id'], $data);
        } else {
            $data['student_id']  = $studentId;
            $data['created_by']  = auth_id();
            StudentMedical::create($data);
        }

        StudentTimeline::logEvent($studentId, 'medical_update', 'Medical information updated', [], auth_id(), 'red', 'heart');
        return true;
    }

    public function addEmergencyContact(int $studentId, array $data): int
    {
        $data['student_id'] = $studentId;
        $data['created_by'] = auth_id();
        return (int) EmergencyContact::create($data);
    }

    public function uploadDocument(int $studentId, array $fileData): int
    {
        $uploadDir = storage_path('uploads/students/' . $studentId);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . pathinfo($fileData['name'], PATHINFO_EXTENSION);
        move_uploaded_file($fileData['tmp_name'], $uploadDir . '/' . $storedName);

        $docId = (int) StudentDocument::create([
            'student_id'  => $studentId,
            'type'        => $fileData['type_label'] ?? 'other',
            'title'       => $fileData['title'] ?? $fileData['name'],
            'file_name'   => $fileData['name'],
            'stored_name' => $storedName,
            'mime_type'   => $fileData['type'],
            'file_size'   => $fileData['size'],
            'created_by'  => auth_id(),
        ]);

        StudentTimeline::logEvent($studentId, 'document_upload', 'Document uploaded: ' . ($fileData['title'] ?? $fileData['name']), [], auth_id(), 'green', 'document');
        return $docId;
    }

    public function addGuardian(int $studentId, array $data, bool $isPrimary = false): int
    {
        $db = \Core\Application::$app->db;

        // Check if guardian with phone already exists
        $existing = $db->selectOne(
            "SELECT id FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
            [$data['phone'], Database::getTenantId()]
        );

        if ($existing) {
            $guardianId = $existing['id'];
        } else {
            $data['tenant_id']  = Database::getTenantId();
            $data['created_by'] = auth_id();
            $guardianId = (int) Guardian::create($data);
        }

        // Link
        $linkExists = $db->selectOne("SELECT 1 FROM guardian_student WHERE guardian_id = ? AND student_id = ?", [$guardianId, $studentId]);
        if (!$linkExists) {
            $db->insert('guardian_student', [
                'guardian_id' => $guardianId,
                'student_id'  => $studentId,
                'is_primary'  => $isPrimary ? 1 : 0,
                'can_pickup'  => $data['can_pickup'] ?? 1,
            ]);
        }

        StudentTimeline::logEvent($studentId, 'guardian_added', 'Guardian added: ' . $data['name'], [], auth_id(), 'yellow', 'users');
        return $guardianId;
    }

    public function delete(int $studentId): bool
    {
        $student = Student::find($studentId);
        if (!$student) return false;

        Student::delete($studentId);
        \App\Models\ActivityLog::log('student_deleted', auth_id(), ['student_id' => $studentId, 'name' => $student['first_name'] . ' ' . $student['last_name']]);
        return true;
    }
}
