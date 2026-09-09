<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{Student, StudentMedical, StudentTimeline, ActivityLog};

class MedicalController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $search      = $this->request->get('search', '');
        $page        = (int) $this->request->get('page', 1);
        $disability  = $this->request->get('disability', '');
        $classFilter = $this->request->get('class', '');
        $bloodFilter = $this->request->get('blood_group', '');

        $perPage  = 15;
        $offset   = ($page - 1) * $perPage;
        $tenantId = \Core\Database::getTenantId();

        $where  = ['s.tenant_id = ?', 's.deleted_at IS NULL'];
        $params = [$tenantId];

        if ($search) {
            $like     = "%$search%";
            $where[]  = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }


        if ($classFilter) {
            $where[]  = 's.class = ?';
            $params[] = $classFilter;
        }

        if ($bloodFilter) {
            $where[]  = 's.blood_group = ?';
            $params[] = $bloodFilter;
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) ($this->db()->selectOne("
            SELECT COUNT(*) as cnt
            FROM students s
            WHERE $whereClause
        ", $params)['cnt'] ?? 0);

        $students = $this->db()->select("
            SELECT s.id, s.first_name, s.last_name, s.dob, s.gender, s.admission_number,
                   s.blood_group, 'Other' AS disability_type, s.photo, s.class,
                   sm.allergies, sm.allergy_severity, sm.triggers, sm.current_medications,
                   sm.medical_conditions, sm.care_instructions, sm.emergency_protocols,
                   sm.doctor_name, sm.doctor_phone, sm.hospital_name,
                   sm.insurance_provider, sm.insurance_number,
                   sm.blood_pressure, sm.weight_kg, sm.height_cm, sm.dietary_restrictions,
                   sm.updated_at AS medical_updated_at
            FROM students s
            LEFT JOIN student_medical sm ON s.id = sm.student_id
            WHERE $whereClause
            ORDER BY s.first_name ASC, s.last_name ASC
            LIMIT $perPage OFFSET $offset
        ", $params);

        $lastPage = (int) ceil($total / $perPage);

        // ── Live Stats ─────────────────────────────────────────────────────────
        $stats = $this->db()->selectOne("
            SELECT
                COUNT(DISTINCT s.id)                                          AS total_students,
                COUNT(DISTINCT sm.student_id)                                 AS with_profile,
                COUNT(DISTINCT CASE WHEN sm.allergies IS NOT NULL AND sm.allergies != '' THEN sm.student_id END) AS with_allergies,
                COUNT(DISTINCT CASE WHEN sm.allergy_severity = 'severe' THEN sm.student_id END)                 AS severe_allergy,
                COUNT(DISTINCT CASE WHEN sm.current_medications IS NOT NULL AND sm.current_medications != '' THEN sm.student_id END) AS on_medications,
                COUNT(DISTINCT CASE WHEN sm.emergency_protocols IS NOT NULL AND sm.emergency_protocols != '' THEN sm.student_id END) AS has_emergency_plan
            FROM students s
            LEFT JOIN student_medical sm ON s.id = sm.student_id
            WHERE s.tenant_id = ? AND s.deleted_at IS NULL
        ", [$tenantId]);

        // Blood group distribution
        $bloodGroups = $this->db()->select("
            SELECT blood_group, COUNT(*) as cnt
            FROM students
            WHERE tenant_id = ? AND deleted_at IS NULL
              AND blood_group IS NOT NULL AND blood_group != '' AND blood_group != 'Unknown'
            GROUP BY blood_group
            ORDER BY cnt DESC
        ", [$tenantId]);

        // Recent medical profile updates
        $recentUpdates = $this->db()->select("
            SELECT s.first_name, s.last_name, s.admission_number, s.class, sm.updated_at
            FROM student_medical sm
            JOIN students s ON s.id = sm.student_id
            WHERE s.tenant_id = ? AND s.deleted_at IS NULL AND sm.updated_at IS NOT NULL
            ORDER BY sm.updated_at DESC
            LIMIT 5
        ", [$tenantId]);

        // Disability types for filter
        $disabilities = ['ASD', 'ADHD', 'Down Syndrome', 'Cerebral Palsy', 'Dyslexia', 'Intellectual Disability', 'Hearing Impairment', 'Visual Impairment', 'Multiple Disabilities', 'Other'];

        // Distinct classes
        $classList = $this->db()->select("
            SELECT DISTINCT class
            FROM students
            WHERE tenant_id = ? AND deleted_at IS NULL AND class IS NOT NULL AND class != ''
            ORDER BY class ASC
        ", [$tenantId]);
        $classes = array_column($classList, 'class');

        return $this->view('medical/index', compact(
            'students', 'total', 'page', 'lastPage', 'search', 'disability',
            'disabilities', 'offset', 'perPage', 'classFilter', 'classes',
            'stats', 'bloodGroups', 'recentUpdates', 'bloodFilter'
        ));
    }


    public function store(string $id): string
    {
        $studentId = (int)$id;
        $db = $this->db();

        $student = Student::find($studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        $data = $this->request->getBody();
        unset($data['_csrf']);

        // Format numeric fields
        if (isset($data['weight_kg']) && $data['weight_kg'] !== '') {
            $data['weight_kg'] = (float)$data['weight_kg'];
        } else {
            $data['weight_kg'] = null;
        }
        if (isset($data['height_cm']) && $data['height_cm'] !== '') {
            $data['height_cm'] = (float)$data['height_cm'];
        } else {
            $data['height_cm'] = null;
        }

        $existing = $db->selectOne("SELECT id FROM student_medical WHERE student_id = ?", [$studentId]);
        
        $data['updated_by'] = $this->authId();
        $data['updated_at'] = now();

        try {
            if ($existing) {
                $db->update('student_medical', $data, 'id = ?', [$existing['id']]);
            } else {
                $data['student_id'] = $studentId;
                $data['created_by'] = $this->authId();
                $data['created_at'] = now();
                $db->insert('student_medical', $data);
            }

            // Also update blood group on student table
            if (isset($data['blood_group'])) {
                $db->update('students', ['blood_group' => $data['blood_group']], 'id = ?', [$studentId]);
            }

            StudentTimeline::logEvent($studentId, 'medical_update', 'Medical records updated by administration', [], $this->authId(), 'red', 'heart');
            ActivityLog::log('student_medical_updated', $this->authId(), ['student_id' => $studentId]);

            $this->flash('success', 'Medical record updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update medical record: ' . $e->getMessage());
        }

        return $this->redirect(url('medical'));
    }
}
