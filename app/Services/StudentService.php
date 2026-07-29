<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{Student, EmergencyContact, StudentDocument, StudentTimeline, Guardian};
use Core\Database;

class StudentService
{
    public function create(array $data, array $files = []): int
    {
        return \Core\Application::$app->db->transaction(function (Database $db) use ($data, $files) {
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

            // Save guardian details (multiple supported)
            $guardiansList = $data['guardians'] ?? [];
            if (empty($guardiansList) && !empty($data['guardian_name'])) {
                $guardiansList[] = [
                    'name'         => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                    'phone'        => $data['guardian_phone'] ?? '',
                    'email'        => $data['guardian_email'] ?? '',
                    'aadhar'       => $data['guardian_aadhar'] ?? '',
                ];
            }

            foreach ($guardiansList as $idx => $gData) {
                if (empty($gData['name']) || empty($gData['phone'])) continue;

                // Check if guardian with phone already exists
                $existing = $db->selectOne(
                    "SELECT id, email, aadhar FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                    [$gData['phone'], Database::getTenantId()]
                );

                if ($existing) {
                    $guardianId = (int)$existing['id'];
                    $updateFields = [];
                    if (empty($existing['aadhar']) && !empty($gData['aadhar'])) $updateFields['aadhar'] = $gData['aadhar'];
                    if (empty($existing['email']) && !empty($gData['email'])) $updateFields['email'] = $gData['email'];
                    if (!empty($updateFields)) {
                        Guardian::update($guardianId, $updateFields);
                    }
                } else {
                    $newGuardian = [
                        'tenant_id'    => Database::getTenantId(),
                        'name'         => $gData['name'],
                        'relationship' => $gData['relationship'] ?? 'Guardian',
                        'phone'        => $gData['phone'],
                        'email'        => $gData['email'] ?: null,
                        'aadhar'       => $gData['aadhar'] ?: null,
                        'created_by'   => auth_id()
                    ];
                    $guardianId = (int) Guardian::create($newGuardian);
                }

                // Auto-create Parent User for Portal Login ONLY if Father or Mother
                $rel = strtolower($gData['relationship'] ?? '');
                if (in_array($rel, ['father', 'mother'])) {
                    $userEmail = !empty($gData['email']) ? $gData['email'] : 'parent_' . $gData['phone'] . '@psnf.edu';
                    $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", [$userEmail, $gData['phone']]);
                    
                    $userId = null;
                    if ($existingUser) {
                        $userId = (int)$existingUser['id'];
                    } else {
                        $userData = [
                            'uuid' => str_uuid(),
                            'tenant_id' => Database::getTenantId(),
                            'name' => $gData['name'],
                            'email' => $userEmail,
                            'phone' => $gData['phone'],
                            'password' => password_hash($gData['phone'], PASSWORD_BCRYPT, ['cost' => 12]),
                            'created_by' => auth_id()
                        ];
                        $userId = (int) \App\Models\User::create($userData);
                        
                        $parentRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'parent' LIMIT 1");
                        if ($parentRole) {
                            \App\Models\User::syncRoles($userId, [(int)$parentRole['id']]);
                        }
                    }
                    Guardian::update($guardianId, ['user_id' => $userId]);
                }

                // Link guardian to student
                $db->insert('guardian_student', [
                    'guardian_id' => $guardianId,
                    'student_id'  => $studentId,
                    'is_primary'  => ($idx === 0) ? 1 : 0,
                    'can_pickup'  => 1,
                ]);
            }

            // Log timeline
            StudentTimeline::logEvent($studentId, 'admission', 'Application submitted', ['status' => 'applied'], auth_id(), 'purple', 'user-plus');

            \App\Models\ActivityLog::log('student_created', auth_id(), ['student_id' => $studentId]);

            return $studentId;
        });
    }

    public function update(int $studentId, array $data, array $files = []): bool
    {
        return \Core\Application::$app->db->transaction(function (Database $db) use ($studentId, $data, $files) {
            $student = Student::find($studentId);
            if (!$student) return false;

            $data['updated_by'] = auth_id();

            // Track status change
            if (!empty($data['admission_status']) && $data['admission_status'] !== $student['admission_status']) {
                Student::updateStatus($studentId, $data['admission_status'], auth_id());
            }

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

            // Sync multiple guardians
            $guardiansList = $data['guardians'] ?? [];
            if (empty($guardiansList) && !empty($data['guardian_name'])) {
                $guardiansList[] = [
                    'name'         => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                    'phone'        => $data['guardian_phone'] ?? '',
                    'email'        => $data['guardian_email'] ?? '',
                    'aadhar'       => $data['guardian_aadhar'] ?? '',
                ];
            }

            if (!empty($guardiansList)) {
                $db->query("DELETE FROM guardian_student WHERE student_id = ?", [$studentId]);
                
                foreach ($guardiansList as $idx => $gData) {
                    if (empty($gData['name']) || empty($gData['phone'])) continue;

                    // Check if guardian with this phone already exists in DB
                    $existing = $db->selectOne(
                        "SELECT id FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                        [$gData['phone'], Database::getTenantId()]
                    );

                    if ($existing) {
                        $guardianId = (int)$existing['id'];
                        $updateFields = [
                            'name'         => $gData['name'],
                            'relationship' => $gData['relationship'] ?? 'Guardian',
                            'email'        => $gData['email'] ?: null,
                            'aadhar'       => $gData['aadhar'] ?: null,
                            'updated_by'   => auth_id()
                        ];
                        Guardian::update($guardianId, $updateFields);
                    } else {
                        $newGuardian = [
                            'tenant_id'    => Database::getTenantId(),
                            'name'         => $gData['name'],
                            'relationship' => $gData['relationship'] ?? 'Guardian',
                            'phone'        => $gData['phone'],
                            'email'        => $gData['email'] ?: null,
                            'aadhar'       => $gData['aadhar'] ?: null,
                            'created_by'   => auth_id()
                        ];
                        $guardianId = (int) Guardian::create($newGuardian);
                    }

                    // Auto-create Parent User for Portal Login ONLY if Father or Mother
                    $rel = strtolower($gData['relationship'] ?? '');
                    if (in_array($rel, ['father', 'mother'])) {
                        $userEmail = !empty($gData['email']) ? $gData['email'] : 'parent_' . $gData['phone'] . '@psnf.edu';
                        $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", [$userEmail, $gData['phone']]);
                        
                        $userId = null;
                        if ($existingUser) {
                            $userId = (int)$existingUser['id'];
                        } else {
                            $userData = [
                                'uuid' => str_uuid(),
                                'tenant_id' => Database::getTenantId(),
                                'name' => $gData['name'],
                                'email' => $userEmail,
                                'phone' => $gData['phone'],
                                'password' => password_hash($gData['phone'], PASSWORD_BCRYPT, ['cost' => 12]),
                                'created_by' => auth_id()
                            ];
                            $userId = (int) \App\Models\User::create($userData);
                            
                            $parentRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'parent' LIMIT 1");
                            if ($parentRole) {
                                \App\Models\User::syncRoles($userId, [(int)$parentRole['id']]);
                            }
                        }
                        Guardian::update($guardianId, ['user_id' => $userId]);
                    }

                    // Link to student
                    $db->insert('guardian_student', [
                        'guardian_id' => $guardianId,
                        'student_id'  => $studentId,
                        'is_primary'  => ($idx === 0) ? 1 : 0,
                        'can_pickup'  => 1,
                    ]);
                }
            }

            \App\Models\ActivityLog::log('student_updated', auth_id(), ['student_id' => $studentId]);
            return true;
        });
    }

    public function updateMedical(int $studentId, array $data): bool
    {
        // student_medical has been removed from the system. Returning true safely.
        StudentTimeline::logEvent($studentId, 'medical_update', 'Medical information update requested', [], auth_id(), 'red', 'heart');
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
