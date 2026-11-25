<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{Student, EmergencyContact, StudentDocument, StudentTimeline, Guardian};
use Core\Database;

class StudentService
{
    public function create(array $data, array $files = []): int
    {
        // Validate the photo before anything is written
        $photoExt = null;
        if (!empty($files['photo']) && ($files['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $photoExt = UploadGuard::check($files['photo'], UploadGuard::IMAGES, 5 * 1024 * 1024);
        }

        return \Core\Application::$app->db->transaction(function (Database $db) use ($data, $files, $photoExt) {
            $studentKeys = [
                'uuid', 'tenant_id', 'school_id', 'branch_id', 'admission_number', 'roll_number', 'gr_number',
                'first_name', 'middle_name', 'last_name', 'gender', 'dob', 'photo', 'blood_group',
                'nationality', 'religion', 'mother_tongue', 'aadhar_number',
                'address', 'city', 'state', 'pincode', 'admission_status',
                'admission_date', 'enrolled_date', 'class', 'section', 'academic_year',
                'is_active', 'notes', 'created_by', 'updated_by'
            ];
            $studentData = array_intersect_key($data, array_flip($studentKeys));
            // New admissions created by staff are enrolled directly (status is only set on create)
            $studentData['admission_status'] = 'enrolled';

            $studentData['uuid']             = str_uuid();
            $studentData['admission_number'] = admission_number((int)($data['school_id'] ?? 1));
            $studentData['gr_number']        = gr_number((int)($data['school_id'] ?? 1));
            $studentData['created_by']       = auth_id();

            $studentId = (int) Student::create($studentData);

            // Handle student image photo upload if present (already validated above)
            if ($photoExt !== null) {
                $uploadDir = storage_path('uploads/students/' . $studentId);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $storedName = UploadGuard::storedName($photoExt);
                if (move_uploaded_file($files['photo']['tmp_name'], $uploadDir . '/' . $storedName)) {
                    Student::update($studentId, ['photo' => $storedName]);
                }
            }

            // Save guardian details (multiple supported)
            $this->syncGuardians($db, $studentId, $this->guardianList($data), false);

            // Log timeline
            StudentTimeline::logEvent($studentId, 'admission', 'Application submitted', ['status' => 'applied'], auth_id(), 'purple', 'user-plus');

            \App\Models\ActivityLog::log('student_created', auth_id(), ['student_id' => $studentId]);

            return $studentId;
        });
    }

    public function update(int $studentId, array $data, array $files = []): bool
    {
        $photoExt = null;
        if (!empty($files['photo']) && ($files['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $photoExt = UploadGuard::check($files['photo'], UploadGuard::IMAGES, 5 * 1024 * 1024);
        }

        return \Core\Application::$app->db->transaction(function (Database $db) use ($studentId, $data, $files, $photoExt) {
            $student = Student::find($studentId);
            if (!$student) return false;

            $data['updated_by'] = auth_id();

            // Track status change (only when a status is explicitly posted)
            if (!empty($data['admission_status']) && $data['admission_status'] !== $student['admission_status']) {
                Student::updateStatus($studentId, $data['admission_status'], auth_id());
            }

            // Filter student table fields
            $studentKeys = [
                'school_id', 'branch_id', 'roll_number', 'first_name', 'middle_name', 'last_name', 'gender', 'dob',
                'blood_group', 'nationality', 'religion', 'mother_tongue', 'aadhar_number',
                'address', 'city', 'state', 'pincode', 'admission_status', 'admission_date',
                'enrolled_date', 'class', 'section', 'academic_year', 'is_active', 'notes', 'updated_by'
            ];
            $studentData = array_intersect_key($data, array_flip($studentKeys));
            // Never force a status on update (a withdrawn/graduated student must stay so)
            if (empty($studentData['admission_status'])) {
                unset($studentData['admission_status']);
            }

            // Handle student photo image upload if present (already validated above)
            if ($photoExt !== null) {
                $uploadDir = storage_path('uploads/students/' . $studentId);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $storedName = UploadGuard::storedName($photoExt);
                if (move_uploaded_file($files['photo']['tmp_name'], $uploadDir . '/' . $storedName)) {
                    $studentData['photo'] = $storedName;
                    // Delete old photo if it exists
                    $old = basename((string)($student['photo'] ?? ''));
                    if ($old !== '' && file_exists($uploadDir . '/' . $old)) {
                        @unlink($uploadDir . '/' . $old);
                    }
                }
            }

            // Update student
            Student::update($studentId, $studentData);

            // Sync multiple guardians
            $guardiansList = $this->guardianList($data);
            if (!empty($guardiansList)) {
                $db->query("DELETE FROM guardian_student WHERE student_id = ?", [$studentId]);
                $this->syncGuardians($db, $studentId, $guardiansList, true);
            }

            \App\Models\ActivityLog::log('student_updated', auth_id(), ['student_id' => $studentId]);
            return true;
        });
    }

    /** Guardians posted as guardians[] or the legacy single guardian_* fields. */
    private function guardianList(array $data): array
    {
        $guardiansList = is_array($data['guardians'] ?? null) ? $data['guardians'] : [];
        if (empty($guardiansList) && !empty($data['guardian_name'])) {
            $guardiansList[] = [
                'name'         => $data['guardian_name'],
                'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                'phone'        => $data['guardian_phone'] ?? '',
                'email'        => $data['guardian_email'] ?? '',
                'aadhar'       => $data['guardian_aadhar'] ?? '',
            ];
        }
        return $guardiansList;
    }

    /**
     * Create/reuse guardians and link them to the student.
     *  - A guardian is reused only when phone AND name match (a father and mother sharing
     *    one phone become two guardians; a sibling's guardian is never renamed).
     *  - The same guardian is linked to a student at most once.
     *  - Portal logins are only reused when the existing user already has the parent role.
     */
    private function syncGuardians(Database $db, int $studentId, array $guardiansList, bool $isUpdate): void
    {
        $tenantId = Database::getTenantId();
        $linked = [];
        $idx = 0;

        foreach ($guardiansList as $gData) {
            if (!is_array($gData)) continue;
            $name  = trim((string)($gData['name'] ?? ''));
            $phone = trim((string)($gData['phone'] ?? ''));
            if ($name === '' || $phone === '') continue;

            $email  = trim((string)($gData['email'] ?? ''));
            $aadhar = trim((string)($gData['aadhar'] ?? ''));
            $relationship = trim((string)($gData['relationship'] ?? '')) ?: 'Guardian';

            // Guardians in this tenant with the same phone; reuse only the one with the same name
            $candidates = $db->select(
                "SELECT id, name, email, aadhar FROM guardians WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL ORDER BY id ASC",
                [$phone, $tenantId]
            );
            $existing = null;
            foreach ($candidates as $c) {
                if (mb_strtolower(trim((string)$c['name'])) === mb_strtolower($name)) {
                    $existing = $c;
                    break;
                }
            }

            if ($existing) {
                $guardianId = (int)$existing['id'];
                $updateFields = [];
                if ($email !== '' && ($isUpdate || empty($existing['email']))) $updateFields['email'] = $email;
                if ($aadhar !== '' && ($isUpdate || empty($existing['aadhar']))) $updateFields['aadhar'] = $aadhar;
                if ($isUpdate) {
                    $updateFields['relationship'] = $relationship;
                    $updateFields['updated_by']   = auth_id();
                }
                if (!empty($updateFields)) {
                    Guardian::update($guardianId, $updateFields);
                }
            } else {
                $guardianId = (int) Guardian::create([
                    'tenant_id'    => $tenantId,
                    'name'         => $name,
                    'relationship' => $relationship,
                    'phone'        => $phone,
                    'email'        => $email ?: null,
                    'aadhar'       => $aadhar ?: null,
                    'created_by'   => auth_id(),
                ]);
            }

            // Auto-create Parent User for Portal Login ONLY if Father or Mother
            $rel = strtolower($relationship);
            if (in_array($rel, ['father', 'mother'], true)) {
                $userId = $this->resolveParentUser($db, $tenantId, $name, $phone, $email);
                if ($userId) {
                    Guardian::update($guardianId, ['user_id' => $userId]);
                }
            }

            // Link guardian to student (once per guardian)
            if (isset($linked[$guardianId])) {
                continue;
            }
            $linked[$guardianId] = true;
            $db->query(
                "INSERT IGNORE INTO guardian_student (guardian_id, student_id, is_primary, can_pickup) VALUES (?, ?, ?, 1)",
                [$guardianId, $studentId, $idx === 0 ? 1 : 0]
            );
            $idx++;
        }
    }

    /** Find an existing PARENT user for this phone/email, or create one. Never attaches staff/admin accounts. */
    private function resolveParentUser(Database $db, ?int $tenantId, string $name, string $phone, string $email): ?int
    {
        $defaultEmail = 'parent_' . preg_replace('/[^0-9+]/', '', $phone) . '@psnf.edu';
        $userEmail = $email !== '' ? $email : $defaultEmail;

        $parentUser = $db->selectOne(
            "SELECT u.id FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id AND r.slug = 'parent'
             WHERE (u.email = ? OR u.phone = ?) AND u.deleted_at IS NULL
             LIMIT 1",
            [$userEmail, $phone]
        );
        if ($parentUser) {
            return (int)$parentUser['id'];
        }

        // The e-mail may already belong to a non-parent account — fall back to the generated address
        foreach (array_unique([$userEmail, $defaultEmail]) as $candidateEmail) {
            $taken = $db->selectOne("SELECT id FROM users WHERE email = ? AND tenant_id <=> ? LIMIT 1", [$candidateEmail, $tenantId]);
            if ($taken) {
                continue;
            }
            $userId = (int) \App\Models\User::create([
                'uuid'       => str_uuid(),
                'tenant_id'  => $tenantId,
                'name'       => $name,
                'email'      => $candidateEmail,
                'phone'      => $phone,
                'password'   => password_hash($phone, PASSWORD_BCRYPT, ['cost' => 12]),
                'created_by' => auth_id(),
            ]);
            $parentRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'parent' LIMIT 1");
            if ($parentRole) {
                \App\Models\User::syncRoles($userId, [(int)$parentRole['id']]);
            }
            return $userId;
        }

        return null; // no safe login could be created; guardian is still saved and linked
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

    /** @throws \RuntimeException when the file is rejected by UploadGuard */
    public function uploadDocument(int $studentId, array $fileData): int
    {
        $ext = UploadGuard::check($fileData, UploadGuard::DOCUMENTS);

        $uploadDir = storage_path('uploads/students/' . $studentId);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = UploadGuard::storedName($ext);
        if (!move_uploaded_file($fileData['tmp_name'], $uploadDir . '/' . $storedName)) {
            throw new \RuntimeException('Failed to store the uploaded file.');
        }

        $docId = (int) StudentDocument::create([
            'student_id'  => $studentId,
            'type'        => $fileData['type_label'] ?? 'other',
            'title'       => $fileData['title'] ?? $fileData['name'],
            'file_name'   => basename((string)$fileData['name']),
            'stored_name' => $storedName,
            'mime_type'   => (new \finfo(FILEINFO_MIME_TYPE))->file($uploadDir . '/' . $storedName) ?: ($fileData['type'] ?? ''),
            'file_size'   => $fileData['size'],
            'created_by'  => auth_id(),
            'status'      => 'verified',
        ]);

        StudentTimeline::logEvent($studentId, 'document_upload', 'Document uploaded: ' . ($fileData['title'] ?? $fileData['name']), [], auth_id(), 'green', 'document');
        return $docId;
    }

    public function addGuardian(int $studentId, array $data, bool $isPrimary = false): int
    {
        $db = \Core\Application::$app->db;

        // Reuse a guardian only when both phone and name match
        $existing = $db->selectOne(
            "SELECT id FROM guardians WHERE phone = ? AND LOWER(TRIM(name)) = LOWER(TRIM(?)) AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
            [$data['phone'], $data['name'] ?? '', Database::getTenantId()]
        );

        if ($existing) {
            $guardianId = $existing['id'];
        } else {
            $allowed = array_intersect_key($data, array_flip(['name', 'relationship', 'phone', 'email', 'aadhar', 'occupation', 'address']));
            $allowed['tenant_id']  = Database::getTenantId();
            $allowed['created_by'] = auth_id();
            $guardianId = (int) Guardian::create($allowed);
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
        return (int) $guardianId;
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
