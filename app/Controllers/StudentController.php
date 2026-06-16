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
            'first_name'     => 'required|min:2|max:100',
            'last_name'      => 'required|min:2|max:100',
            'gender'         => 'required|in:male,female,other',
            'dob'            => 'required|date',
            'disability_type'=> 'required',
            'school_id'      => 'required|exists:schools,id',
            'branch_id'      => 'required|exists:branches,id',
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

        try {
            $studentId = $this->service->create($validator->validated());
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

        return $this->view('students/show', ['student' => $student]);
    }

    public function edit(string $id): string
    {
        $student  = Student::find((int) $id);
        if (!$student) { $this->response->abort(404); exit(); }

        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name, school_id FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);

        return $this->view('students/edit', compact('student', 'schools', 'branches'));
    }

    public function update(string $id): string
    {
        $data = $this->request->getBody();
        $this->service->update((int) $id, $data);

        $this->flash('success', 'Student updated successfully.');
        return $this->redirect("/students/$id");
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
