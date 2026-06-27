<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Models\{Certificate, Student, ActivityLog};

class CertificateController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $tenantId = \Core\Database::getTenantId();

        $certificates = $this->db()->select("
            SELECT 
                gc.id,
                ct.name AS title,
                ct.name AS certificate_type,
                gc.pdf_path AS file_path,
                DATE(gc.generated_at) AS issued_at,
                'generated' AS source,
                s.first_name,
                s.last_name,
                s.admission_number,
                p.id AS participant_id
            FROM generated_certificates gc
            JOIN participants p ON p.id = gc.participant_id
            JOIN certificate_types ct ON ct.id = p.certificate_type_id
            JOIN students s ON s.id = p.student_id
            WHERE s.tenant_id = ?
            ORDER BY issued_at DESC
        ", [$tenantId]);

        return $this->view('certificates/index', compact('certificates'));
    }

    public function create(): string
    {
        $students = $this->db()->select("
            SELECT id, first_name, last_name, admission_number 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL 
            ORDER BY first_name ASC
        ", [\Core\Database::getTenantId()]);

        return $this->view('certificates/create', compact('students'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        unset($data['_csrf']);

        $rules = [
            'student_id'       => 'required',
            'title'            => 'required|min:3',
            'certificate_type' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect('/certificates/create');
        }

        // Pack the design parameters as a JSON string to store in `file_path`
        $designParams = [
            'title'         => $data['title'],
            'recipient'     => $data['recipient_name'] ?? 'Student',
            'description'   => $data['description'] ?? '',
            'template'      => $data['template'] ?? 'academic',
            'border_style'  => $data['border_style'] ?? 'gold',
            'primary_color' => $data['primary_color'] ?? '#6366f1',
            'font_family'   => $data['font_family'] ?? 'Inter',
            'issuer_name'   => $data['issuer_name'] ?? 'Pearl Special Needs Foundation',
            'issuer_title'  => $data['issuer_title'] ?? 'Authorized Director',
        ];

        $dbData = [
            'tenant_id'        => \Core\Database::getTenantId(),
            'school_id'        => \Core\Database::getSchoolId() ?: 1,
            'branch_id'        => \Core\Database::getBranchId() ?: 1,
            'student_id'       => (int)$data['student_id'],
            'title'            => $data['title'],
            'certificate_type' => $data['certificate_type'],
            'file_path'        => json_encode($designParams),
            'issued_at'        => date('Y-m-d'),
        ];

        $certId = Certificate::create($dbData);

        // Add timeline record
        $student = $this->db()->selectOne("SELECT first_name, last_name, tenant_id, school_id, branch_id FROM students WHERE id = ?", [(int)$data['student_id']]);
        if ($student) {
            $this->db()->insert('student_timeline', [
                'student_id'  => (int)$data['student_id'],
                'event_type'  => 'document',
                'title'       => 'Certificate Issued',
                'description' => "Issued certificate '{$data['title']}' via Visual Designer.",
                'color'       => 'purple',
                'icon'        => 'doc',
                'actor_name'  => auth()['name'] ?? 'Staff',
                'occurred_at' => now(),
            ]);
        }

        ActivityLog::log('certificate_issued', auth_id(), ['certificate_id' => $certId]);
        Session::flash('success', "Certificate '{$data['title']}' issued successfully.");
        return $this->redirect('/certificates');
    }

    public function show(string $id): string
    {
        $certificate = Certificate::find((int)$id);
        if (!$certificate) {
            Session::flash('error', 'Certificate not found.');
            return $this->redirect('/certificates');
        }

        $student = $this->db()->selectOne("
            SELECT first_name, last_name, admission_number 
            FROM students 
            WHERE id = ?
        ", [$certificate['student_id']]);

        // Attempt to parse Canva parameters, otherwise create fallback
        $design = [];
        $filePath = $certificate['file_path'];
        if (str_starts_with($filePath, '{') && str_ends_with($filePath, '}')) {
            $design = json_decode($filePath, true);
        }

        if (empty($design)) {
            // Fallback for seeded PDF/Legacy certificates
            $design = [
                'title'         => $certificate['title'],
                'recipient'     => $student ? ($student['first_name'] . ' ' . $student['last_name']) : 'Student',
                'description'   => 'For excellent progress and achievements.',
                'template'      => 'academic',
                'border_style'  => 'classic',
                'primary_color' => '#3b82f6',
                'font_family'   => 'Inter',
                'issuer_name'   => 'Pearl Special Needs Foundation',
                'issuer_title'  => 'School Administrator',
                'is_legacy'     => true,
                'file_path'     => $filePath
            ];
        }

        return $this->view('certificates/view', compact('certificate', 'design', 'student'));
    }

    public function destroy(string $id): string
    {
        $certificate = Certificate::find((int)$id);
        if ($certificate) {
            $this->db()->query("DELETE FROM certificates WHERE id = ?", [$certificate['id']]);
            ActivityLog::log('certificate_deleted', auth_id(), ['certificate_id' => $id]);
            Session::flash('success', 'Certificate deleted.');
        }

        return $this->redirect('/certificates');
    }
}
