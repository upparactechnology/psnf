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
        $statuses = Student::statusCounts();

        if ($this->isHtmx()) {
            return $this->view('students/_table', array_merge($result, ['search' => $search, 'statuses' => $statuses]));
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

        $rules = [
            'full_name'             => 'required|min:2|max:200',
            'gender'                 => 'required|in:male,female,other',
            'dob'                    => 'required|date',
            'blood_group'            => 'nullable|in:Unknown,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'aadhar_number'          => 'nullable|max:20',
            'mother_tongue'          => 'nullable|max:100',
            'address'                => 'nullable',
            'disability_type'        => 'required',
            'disability_detail'      => 'nullable',
            'care_instructions'      => 'nullable',
            'special_needs_summary'  => 'nullable',
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

            return $this->redirect("/students/$studentId");
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create student. ' . $e->getMessage());
            return $this->redirect('/students/create');
        }
    }

    public function show(string $id): string
    {
        $student = Student::withDetails((int) $id);

        if (!$student) {
            $this->response->abort(404);
            exit();
        }

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

        return $this->view('students/show', compact('student', 'classes', 'sections', 'academicYears'));
    }

    public function edit(string $id): string
    {
        $student  = Student::withDetails((int) $id);
        if (!$student) { $this->response->abort(404); exit(); }

        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name, school_id FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);

        return $this->view('students/edit', compact('student', 'schools', 'branches'));
    }

    public function update(string $id): string
    {
        $data = $this->request->getBody();

        $rules = [
            'full_name'             => 'required|min:2|max:200',
            'gender'                 => 'required|in:male,female,other',
            'dob'                    => 'required|date',
            'blood_group'            => 'nullable|in:Unknown,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'aadhar_number'          => 'nullable|max:20',
            'mother_tongue'          => 'nullable|max:100',
            'address'                => 'nullable',
            'disability_type'        => 'required',
            'disability_detail'      => 'nullable',
            'care_instructions'      => 'nullable',
            'special_needs_summary'  => 'nullable',
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
            return $this->redirect("/students/$id");
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update student. ' . $e->getMessage());
            return $this->redirect("/students/$id/edit");
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
        return $this->redirect("/students/$id");
    }

    public function updateStatus(string $id): string
    {
        $status = $this->request->input('admission_status');
        if (!$status) {
            return $this->errorResponse('Status is required.', null, 422);
        }

        Student::updateStatus((int) $id, $status, auth_id());

        if ($status === 'enrolled') {
            $class = trim($this->request->input('class') ?? '');
            $section = trim($this->request->input('section') ?? '');
            $academicYear = trim($this->request->input('academic_year') ?? '');
            
            $db = \Core\Application::$app->db;
            $db->query(
                "UPDATE students 
                 SET class = ?, section = ?, academic_year = ?, enrolled_date = ? 
                 WHERE id = ?",
                [$class, $section, $academicYear, date('Y-m-d'), (int)$id]
            );
        }

        if ($this->request->wantsJson() || $this->isHtmx()) {
            return $this->successResponse('Status updated.');
        }

        $this->flash('success', 'Admission status updated.');
        return $this->redirect("/students/$id");
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
        return $this->redirect("/students/$id");
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
        return $this->redirect("/students/$id");
    }

    public function storeEmergencyContact(string $id): string
    {
        $data      = $this->request->getBody();
        $contactId = $this->service->addEmergencyContact((int) $id, $data);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Emergency contact added.', ['id' => $contactId], 201);
        }

        $this->flash('success', 'Emergency contact added.');
        return $this->redirect("/students/$id");
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
