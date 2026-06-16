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

            $data['uuid']             = str_uuid();
            $data['admission_number'] = admission_number($data['school_id']);
            $data['gr_number']        = gr_number($data['school_id']);
            $data['created_by']       = auth_id();

            $studentId = Student::create($data);

            // Create empty medical record
            StudentMedical::create(['student_id' => $studentId, 'created_by' => auth_id()]);

            // Log timeline
            StudentTimeline::logEvent($studentId, 'admission', 'Application submitted', ['status' => 'applied'], auth_id(), 'purple', 'user-plus');

            \App\Models\ActivityLog::log('student_created', auth_id(), ['student_id' => $studentId]);

            return (int) $studentId;
        });
    }

    public function update(int $studentId, array $data): bool
    {
        $student = Student::find($studentId);
        if (!$student) return false;

        $data['updated_by'] = auth_id();

        // Track status change
        if (!empty($data['admission_status']) && $data['admission_status'] !== $student['admission_status']) {
            Student::updateStatus($studentId, $data['admission_status'], auth_id());
        }

        Student::update($studentId, $data);
        \App\Models\ActivityLog::log('student_updated', auth_id(), ['student_id' => $studentId]);
        return true;
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
