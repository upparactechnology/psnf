<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Student, StudentMedical, EmergencyContact, StudentDocument, StudentTimeline};
use App\Services\StudentService;

class StudentController extends Controller
{
    private StudentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentService();
    }

    public function index(): string
    {
        $search   = $this->request->get('search', '');
        $page     = (int) $this->request->get('page', 1);
        $status   = $this->request->get('status', '');
        $disability = $this->request->get('disability', '');

        $filters  = array_filter(compact('status', 'disability'));
        $result   = Student::search($search, $filters, 15, $page);

        if (str_starts_with($this->request->getPath(), '/api/')) {
            return $this->json(array_merge(['success' => true], $result));
        }

        $statuses = Student::statusCounts();

        if ($this->isHtmx() && ($this->request->get('search') !== null || $this->request->get('status') !== null || $this->request->get('disability') !== null)) {
            if (file_exists(VIEWS_PATH . '/students/_table.php')) {
                return $this->view('students/_table', array_merge($result, ['search' => $search, 'statuses' => $statuses]));
            }
        }

        return $this->view('students/index', array_merge($result, ['search' => $search, 'statuses' => $statuses, 'filters' => $filters]));
    }

    public function create(): string
    {
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name, school_id FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        return $this->view('students/create', compact('schools', 'branches'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        if (!isset($data['full_name']) && isset($data['first_name'])) {
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }
        $rules = [
            'full_name'             => 'required|min:2|max:200',
            'gender'                 => 'required|in:male,female,other',
            'dob'                    => 'required|date',
            'blood_group'            => 'nullable|in:Unknown,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'aadhar_number'          => 'nullable|max:20',
            'roll_number'            => 'nullable|max:50',
            'gr_number'              => 'nullable|max:50',
            'mother_tongue'          => 'nullable|max:100',
            'address'                => 'nullable',
            'school_id'              => 'required|exists:schools,id',
            'branch_id'              => 'required|exists:branches,id',
            'guardian_name'          => 'nullable|min:2',
            'guardian_relationship'  => 'nullable',
            'guardian_phone'         => 'nullable',
            'guardian_email'         => 'nullable',
            'guardian_aadhar'        => 'nullable|max:20',
            'allergies'              => 'nullable',
            'triggers'               => 'nullable',
            'medications'            => 'nullable',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            if ($this->request->wantsJson()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }
            \Core\Session::flash('errors', $validator->errors());
            \Core\Session::flash('old', $data);
            return $this->redirect('/students/create');
        }

        $validated = $validator->validated();
        
        // Pass un-validated arrays through to the service
        $validated['guardians'] = $data['guardians'] ?? [];
        $validated['emergency_contacts'] = $data['emergency_contacts'] ?? [];

        // Split Full Name
        $parts = preg_split('/\s+/', trim($validated['full_name']));
        if (count($parts) === 1) {
            $validated['first_name'] = $parts[0];
            $validated['middle_name'] = '';
            $validated['last_name'] = '';
        } elseif (count($parts) === 2) {
            $validated['first_name'] = $parts[0];
            $validated['middle_name'] = '';
            $validated['last_name'] = $parts[1];
        } else {
            $validated['first_name'] = $parts[0];
            $validated['last_name'] = array_pop($parts);
            $validated['middle_name'] = implode(' ', array_slice($parts, 1));
        }

        try {
            $photoFile = $this->request->file('photo');
            $filesData = [];
            if ($photoFile && $photoFile['error'] === UPLOAD_ERR_OK) {
                $filesData['photo'] = $photoFile;
            }

            $studentId = $this->service->create($validated, $filesData);
            $this->flash('success', 'Student admission application submitted successfully.');

            if ($this->request->wantsJson()) {
                return $this->successResponse('Student created.', ['id' => $studentId], 201);
            }

            return $this->redirect("/academics/students/$studentId");
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create student. ' . $e->getMessage());
            return $this->redirect('/academics/students/create');
        }
    }

    public function show(string $id): string
    {
        $student = Student::withDetails((int) $id);

        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        $student['ledgers'] = \Core\Application::$app->db->select("
            SELECT * FROM student_ledgers 
            WHERE student_id = ? 
            ORDER BY created_at DESC
        ", [(int)$id]);
        
        $student['ledger_summary'] = \Core\Application::$app->db->selectOne("
            SELECT 
                COALESCE(SUM(debit), 0) as total_debit, 
                COALESCE(SUM(credit), 0) as total_credit,
                (COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0)) as current_balance
            FROM student_ledgers 
            WHERE student_id = ?
        ", [(int)$id]);

        $db = \Core\Application::$app->db;
        $tenantId = \Core\Database::getTenantId();
        
        $classes = $db->select(
            "SELECT DISTINCT class 
             FROM students 
             WHERE tenant_id = ? AND class IS NOT NULL AND class != '' AND deleted_at IS NULL
             ORDER BY class ASC",
            [$tenantId]
        );
        $sections = $db->select(
            "SELECT DISTINCT section 
             FROM students 
             WHERE tenant_id = ? AND section IS NOT NULL AND section != '' AND deleted_at IS NULL
             ORDER BY section ASC",
            [$tenantId]
        );
        $academicYears = $db->select(
            "SELECT DISTINCT academic_year 
             FROM students 
             WHERE tenant_id = ? AND academic_year IS NOT NULL AND academic_year != '' AND deleted_at IS NULL
             ORDER BY academic_year DESC",
            [$tenantId]
        );

        $inheritedSubjects = [];
        if (!empty($student['class_id'])) {
            $classId = (int)$student['class_id'];
            $classRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
            if ($classRow) {
                $curriculum = $db->selectOne("
                    SELECT id FROM curriculum_templates 
                    WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ? LIMIT 1
                ", [$classRow['academic_year_id'], $classRow['main_group_id'], $tenantId]);
                
                if ($curriculum) {
                    $inheritedSubjects = $db->select("
                        SELECT s.*, cs.assessment_type, cs.is_required
                        FROM curriculum_subjects cs
                        JOIN subjects s ON cs.subject_id = s.id
                        JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                        WHERE sec.curriculum_template_id = ?
                        ORDER BY sec.sort_order ASC, cs.sequence ASC
                    ", [$curriculum['id']]);
                }
            }
        }

        return $this->view('students/show', compact('student', 'classes', 'sections', 'academicYears', 'inheritedSubjects'));
    }

    public function edit(string $id): string
    {
        $student  = Student::withDetails((int) $id);
        if (!$student) { $this->response->abort(404); exit(); }

        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name, school_id FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);

        $guardians = \Core\Application::$app->db->select(
            "SELECT g.name, g.relationship, g.phone, g.email, g.aadhar 
             FROM guardians g 
             JOIN guardian_student gs ON gs.guardian_id = g.id 
             WHERE gs.student_id = ? AND g.deleted_at IS NULL",
            [(int)$id]
        );

        return $this->view('students/edit', compact('student', 'schools', 'branches', 'guardians'));
    }
    public function update(string $id): string
    {
        $data = $this->request->getBody();
        if (!isset($data['full_name']) && isset($data['first_name'])) {
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        $student = Student::find((int) $id);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        if (empty($data['school_id'])) {
            $data['school_id'] = $student['school_id'];
        }
        if (empty($data['branch_id'])) {
            $data['branch_id'] = $student['branch_id'];
        }

        $rules = [
            'full_name'             => 'required|min:2|max:200',
            'gender'                 => 'required|in:male,female,other',
            'dob'                    => 'required|date',
            'blood_group'            => 'nullable|in:Unknown,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'aadhar_number'          => 'nullable|max:20',
            'roll_number'            => 'nullable|max:50',
            'gr_number'              => 'nullable|max:50',
            'mother_tongue'          => 'nullable|max:100',
            'address'                => 'nullable',
            'school_id'              => 'required|exists:schools,id',
            'branch_id'              => 'required|exists:branches,id',
            'class'                  => 'nullable|max:50',
            'section'                => 'nullable|max:20',
            'academic_year'          => 'nullable|max:20',
            'notes'                  => 'nullable',
            'guardian_name'          => 'nullable|min:2',
            'guardian_relationship'  => 'nullable',
            'guardian_phone'         => 'nullable',
            'guardian_email'         => 'nullable',
            'guardian_aadhar'        => 'nullable|max:20',
            'allergies'              => 'nullable',
            'triggers'               => 'nullable',
            'medications'            => 'nullable',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            \Core\Session::flash('errors', $validator->errors());
            \Core\Session::flash('old', $data);
            return $this->redirect("/students/$id/edit");
        }

        $validated = $validator->validated();

        // Pass un-validated arrays through to the service
        $validated['guardians'] = $data['guardians'] ?? [];
        $validated['emergency_contacts'] = $data['emergency_contacts'] ?? [];

        // Split Full Name
        $parts = preg_split('/\s+/', trim($validated['full_name']));
        if (count($parts) === 1) {
            $validated['first_name'] = $parts[0];
            $validated['middle_name'] = '';
            $validated['last_name'] = '';
        } elseif (count($parts) === 2) {
            $validated['first_name'] = $parts[0];
            $validated['middle_name'] = '';
            $validated['last_name'] = $parts[1];
        } else {
            $validated['first_name'] = $parts[0];
            $validated['last_name'] = array_pop($parts);
            $validated['middle_name'] = implode(' ', array_slice($parts, 1));
        }

        try {
            $photoFile = $this->request->file('photo');
            $filesData = [];
            if ($photoFile && $photoFile['error'] === UPLOAD_ERR_OK) {
                $filesData['photo'] = $photoFile;
            }

            $this->service->update((int) $id, $validated, $filesData);
            $this->flash('success', 'Student profile updated successfully.');
            return $this->redirect("/academics/students/$id");
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update student. ' . $e->getMessage());
            return $this->redirect("/academics/students/$id/edit");
        }
    }

    public function updateMedical(string $id): string
    {
        $data = $this->request->getBody();
        $this->service->updateMedical((int) $id, $data);

        if ($this->request->isHtmx()) {
            return '<div class="text-green-400 text-sm font-medium flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Medical records saved.</div>';
        }

        $this->flash('success', 'Medical information updated.');
        return $this->redirect("/academics/students/$id");
    }

    public function updateStatus(string $id): string
    {
        $status = $this->request->input('admission_status');
        if (!$status) {
            return $this->errorResponse('Status is required.', null, 422);
        }

        Student::updateStatus((int) $id, $status, auth_id());

        if ($status === 'enrolled') {
            $classId = (int)$this->request->input('class_id');
            $db = \Core\Application::$app->db;
            $classRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
            if ($classRow) {
                $db->query(
                    "UPDATE students 
                     SET class_id = ?, main_group_id = ?, class = ?, section = ?, enrolled_date = ? 
                     WHERE id = ?",
                    [$classRow['id'], $classRow['main_group_id'], $classRow['name'], $classRow['section'], date('Y-m-d'), (int)$id]
                );
            }
        }

        if ($this->request->wantsJson() || $this->isHtmx()) {
            return $this->successResponse('Status updated.');
        }

        $this->flash('success', 'Admission status updated.');
        return $this->redirect("/academics/students/$id");
    }

    public function uploadDocument(string $id): string
    {
        $file = $this->request->file('document');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->errorResponse('No valid file uploaded.', null, 422);
        }

        $fileData = [
            'name'       => $file['name'],
            'tmp_name'   => $file['tmp_name'],
            'type'       => $file['type'],
            'size'       => $file['size'],
            'title'      => $this->request->input('title', $file['name']),
            'type_label' => $this->request->input('document_type', 'other'),
        ];

        $docId = $this->service->uploadDocument((int) $id, $fileData);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Document uploaded.', ['id' => $docId], 201);
        }

        $this->flash('success', 'Document uploaded successfully.');
        return $this->redirect("/academics/students/$id");
    }

    public function storeGuardian(string $id): string
    {
        $data = $this->request->getBody();

        $rules = [
            'name'         => 'required|min:2',
            'relationship' => 'required',
            'phone'        => 'required|phone',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', $validator->errors(), 422);
        }

        $guardianId = $this->service->addGuardian((int) $id, $data);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Guardian added.', ['id' => $guardianId], 201);
        }

        $this->flash('success', 'Guardian/parent added successfully.');
        return $this->redirect("/academics/students/$id");
    }

    public function storeEmergencyContact(string $id): string
    {
        $data      = $this->request->getBody();
        $contactId = $this->service->addEmergencyContact((int) $id, $data);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Emergency contact added.', ['id' => $contactId], 201);
        }

        $this->flash('success', 'Emergency contact added.');
        return $this->redirect("/academics/students/$id");
    }

    public function timeline(string $id): string
    {
        $student  = Student::find((int) $id);
        if (!$student) { $this->response->abort(404); exit(); }

        $timeline = StudentTimeline::where('student_id = ?', [(int) $id], 'occurred_at DESC');
        return $this->view('students/timeline', compact('student', 'timeline'));
    }

    public function destroy(string $id): string
    {
        $this->service->delete((int) $id);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Student deleted.');
        }

        $this->flash('success', 'Student record deleted.');
        return $this->redirect('/students');
    }

    public function search(): string
    {
        $q       = $this->request->get('q', '');
        $results = Student::search($q, [], 10);
        return $this->json($results);
    }
}
