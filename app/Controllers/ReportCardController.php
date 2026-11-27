<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\Student;
use App\Models\ActivityLog;

class ReportCardController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    private function getDefaults(): array
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $activeYearRow = $db->selectOne("SELECT year_name FROM academic_years WHERE status = 'current' AND tenant_id = ? LIMIT 1", [$tenantId])
            ?: $db->selectOne("SELECT year_name FROM academic_years WHERE tenant_id = ? ORDER BY id DESC LIMIT 1", [$tenantId]);
        $academicYear = $activeYearRow['year_name'] ?? '2026-27';
        
        $activeSemRow = $db->selectOne("SELECT name FROM academic_semesters WHERE status = 'OPEN' AND tenant_id = ? ORDER BY id ASC LIMIT 1", [$tenantId]);
        $semester = $activeSemRow['name'] ?? 'Semester 1';

        return [$academicYear, $semester];
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        list($defaultYear, $defaultSem) = $this->getDefaults();
        $semester = $this->request->get('semester', $defaultSem);
        $academicYear = $this->request->get('academic_year', $defaultYear);

        $students = $db->select(
            "SELECT s.id, s.first_name, s.last_name, s.class, s.section, s.gr_number, s.admission_number,
                    rc.id as report_card_id
             FROM students s
             LEFT JOIN student_report_cards rc ON rc.student_id = s.id AND rc.academic_year = ? AND rc.semester = ?
             WHERE s.tenant_id = ? AND s.deleted_at IS NULL AND s.admission_status = 'enrolled'
             ORDER BY s.class ASC, s.first_name ASC",
            [$academicYear, $semester, $tenantId]
        );

        $yearsList = $db->select("SELECT id, year_name FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);

        // Get selected year ID for semester filtering
        $selectedYearRow = $db->selectOne("SELECT id FROM academic_years WHERE tenant_id = ? AND year_name = ?", [$tenantId, $academicYear]);
        $selectedYearId = $selectedYearRow['id'] ?? 0;

        $semestersList = $db->select("SELECT name FROM academic_semesters WHERE tenant_id = ? AND academic_year_id = ? GROUP BY name ORDER BY id ASC", [$tenantId, $selectedYearId]);

        // Fallbacks if empty
        if (empty($yearsList)) {
            $yearsList = [['id' => 0, 'year_name' => $defaultYear]];
        }
        if (empty($semestersList)) {
            $semestersList = [['name' => 'Semester 1'], ['name' => 'Semester 2']];
        }

        return $this->view('report-cards/index', compact('students', 'semester', 'academicYear', 'yearsList', 'semestersList'));
    }

    // ─── Pearl School report card (fixed 11-page format) ──────────────────

    /** Decoded report card row, or an empty card when none is saved yet. */
    private function loadCard(int $studentId, string $academicYear, string $semester): array
    {
        $row = $this->db()->selectOne(
            "SELECT * FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
            [$studentId, $academicYear, $semester]
        );
        $json = static fn($v) => is_string($v) ? (json_decode($v, true) ?: []) : [];
        return [
            'id'                   => $row ? (int) $row['id'] : 0,
            'academic_year'        => $academicYear,
            'semester'             => $semester,
            'routine_profile'      => $json($row['routine_profile'] ?? null),
            'learning_skills'      => $json($row['learning_skills'] ?? null),
            'academic_profile'     => $json($row['academic_profile'] ?? null),
            'cocurriculum_profile' => $json($row['cocurriculum_profile'] ?? null),
            'attendance_profile'   => $json($row['attendance_profile'] ?? null),
            'feedback_text'        => (string) ($row['feedback_text'] ?? ''),
            'authorized_by'        => $json($row['authorized_by'] ?? null),
            'semester_range'       => $this->semesterRange($semester),
        ];
    }

    private function semesterRow(string $semester): ?array
    {
        $row = $this->db()->selectOne(
            "SELECT * FROM academic_semesters WHERE tenant_id = ? AND name = ? ORDER BY id DESC LIMIT 1",
            [\Core\Database::getTenantId(), $semester]
        );
        return $row ?: null;
    }

    /** "JUN. to NOV." from the semester dates (falls back to the usual school terms). */
    private function semesterRange(string $semester): string
    {
        $row = $this->semesterRow($semester);
        if ($row && !empty($row['start_date']) && !empty($row['end_date'])) {
            return strtoupper(date('M', strtotime($row['start_date']))) . '. to ' . strtoupper(date('M', strtotime($row['end_date']))) . '.';
        }
        return str_contains($semester, '2') ? 'DEC. to APR.' : 'JUN. to NOV.';
    }

    private function cardSettings(int $tenantId): array
    {
        $settings = $this->db()->selectOne("SELECT * FROM report_card_settings WHERE tenant_id = ?", [$tenantId]) ?: [];
        $settings['trustees_config'] = json_decode((string) ($settings['trustees_config'] ?? '[]'), true) ?: [];
        return $settings;
    }

    /** Present / working days from the semester dates and student attendance. */
    private function attendanceAuto(int $studentId, string $semester): array
    {
        $row = $this->semesterRow($semester);
        if (!$row || empty($row['start_date']) || empty($row['end_date'])) {
            return ['working_days' => '', 'present_days' => ''];
        }
        $present = $this->db()->selectOne(
            "SELECT COUNT(DISTINCT date) AS c FROM attendance
             WHERE student_id = ? AND date BETWEEN ? AND ? AND status IN ('present', 'late', 'half_day')",
            [$studentId, $row['start_date'], $row['end_date']]
        );
        return [
            'working_days' => !empty($row['total_working_days']) ? (string) (int) $row['total_working_days'] : '',
            'present_days' => (string) (int) ($present['c'] ?? 0),
        ];
    }

    private function yearAndSemester(array $student): array
    {
        [$defaultYear, $defaultSem] = $this->getDefaults();
        $semester = (string) $this->request->get('semester', $defaultSem);
        $academicYear = (string) $this->request->get('academic_year', !empty($student['academic_year']) ? $student['academic_year'] : $defaultYear);
        return [$academicYear, $semester];
    }

    public function edit(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }
        [$academicYear, $semester] = $this->yearAndSemester($student);
        $tenantId = (int) ($student['tenant_id'] ?? \Core\Database::getTenantId());

        $reportCard = $this->loadCard((int) $studentId, $academicYear, $semester);
        $attendanceAuto = $this->attendanceAuto((int) $studentId, $semester);
        $yearsList = $this->db()->select("SELECT year_name FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);
        $standard = \App\Services\PearlReportCard::standardFor($student);

        return $this->view('report-cards/edit', compact('student', 'reportCard', 'semester', 'academicYear', 'attendanceAuto', 'yearsList', 'standard'));
    }

    public function store(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }
        [$defaultYear, $defaultSem] = $this->getDefaults();
        $academicYear = (string) $this->request->input('academic_year', $defaultYear);
        $semester = (string) $this->request->input('semester', $defaultSem);
        $back = url("students/$studentId/report-card/edit?semester=" . urlencode($semester) . "&academic_year=" . urlencode($academicYear));

        if (is_year_locked($academicYear)) {
            $this->flash('error', 'This academic year is locked. Report card updates are frozen.');
            return $this->redirect($back);
        }

        // Raw POST: every field is whitelisted/normalised below and escaped on output
        // (getBody() would HTML-encode the feedback text, e.g. "He&#039;s").
        $post = $_POST;
        $data = \App\Services\PearlReportCard::normalizeInput($post);

        // Keep anything else stored in authorized_by (e.g. legacy signatures)
        $existing = $this->db()->selectOne(
            "SELECT id, authorized_by FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
            [(int) $studentId, $academicYear, $semester]
        );
        $meta = $existing ? (json_decode((string) $existing['authorized_by'], true) ?: []) : [];
        $date = trim((string) ($post['feedback_date'] ?? ''));
        $ts = $date !== '' ? strtotime($date) : false;
        $meta['feedback_date'] = $ts ? date('d/m/Y', $ts) : '';
        $meta['format'] = \App\Services\PearlReportCard::FORMAT;

        $enc = static fn($v) => json_encode($v, JSON_UNESCAPED_UNICODE);
        $row = [
            'routine_profile'      => $enc($data['routine_profile']),
            'learning_skills'      => $enc($data['learning_skills']),
            'academic_profile'     => $enc($data['academic_profile']),
            'cocurriculum_profile' => $enc($data['cocurriculum_profile']),
            'attendance_profile'   => $enc($data['attendance_profile']),
            'feedback_text'        => mb_substr(trim(strip_tags((string) ($post['feedback_text'] ?? ''))), 0, 2000),
            'authorized_by'        => $enc($meta),
        ];

        try {
            if ($existing) {
                $this->db()->update('student_report_cards', $row, 'id = ?', [(int) $existing['id']]);
            } else {
                $this->db()->insert('student_report_cards', $row + [
                    'tenant_id'     => $student['tenant_id'] ?? \Core\Database::getTenantId(),
                    'school_id'     => $student['school_id'] ?? 1,
                    'branch_id'     => $student['branch_id'] ?? 1,
                    'student_id'    => (int) $studentId,
                    'academic_year' => $academicYear,
                    'semester'      => $semester,
                ]);
            }
            ActivityLog::log('report_card_saved', auth_id(), ['student_id' => (int) $studentId, 'semester' => $semester, 'academic_year' => $academicYear]);
            $this->flash('success', 'Report card saved.');
        } catch (\Throwable $e) {
            error_log('[ReportCard] save failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to save the report card. Please try again.');
        }

        if (($post['after_save'] ?? '') === 'preview') {
            return $this->redirect(url("students/$studentId/report-card/view?semester=" . urlencode($semester) . "&academic_year=" . urlencode($academicYear)));
        }
        return $this->redirect($back);
    }

    /** Printable preview (HTML) or the PDF download (?pdf=1). */
    public function show(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }
        [$academicYear, $semester] = $this->yearAndSemester($student);
        $reportCard = $this->loadCard((int) $studentId, $academicYear, $semester);
        if (!$reportCard['id']) {
            $this->flash('error', "No report card saved for $semester ($academicYear) yet. Fill it in first.");
            return $this->redirect(url("students/$studentId/report-card/edit?semester=" . urlencode($semester) . "&academic_year=" . urlencode($academicYear)));
        }

        $q = 'semester=' . urlencode($semester) . '&academic_year=' . urlencode($academicYear);
        return $this->renderCard($student, $reportCard, $semester, $academicYear, [
            ['label' => '← Edit', 'url' => url("students/$studentId/report-card/edit?$q")],
            ['label' => 'Print', 'type' => 'print'],
            ['label' => 'Download PDF', 'url' => url("students/$studentId/report-card/view?$q&pdf=1"), 'primary' => true],
        ]);
    }

    /** Preview page, or stream the PDF when ?pdf=1. */
    private function renderCard(array $student, array $reportCard, string $semester, string $academicYear, array $toolbar): string
    {
        $settings = $this->cardSettings((int) ($student['tenant_id'] ?? \Core\Database::getTenantId()));
        $rc = \App\Services\PearlReportCard::class;

        if ($this->request->get('pdf') === '1') {
            $pdf = $rc::renderPdf($rc::renderHtml($student, $reportCard, $semester, $academicYear, $settings, true));
            $name = $rc::fileName($student, $semester, $academicYear);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
            exit();
        }
        return $rc::renderHtml($student, $reportCard, $semester, $academicYear, $settings, false, $toolbar);
    }

    public function parentShow(string $studentId): string
    {
        $authId = $this->authId();
        $db = $this->db();

        // Build parent context for sidebar layout
        $guardian = null;
        $isAdminOrStaff = has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher');

        if ($isAdminOrStaff) {
            $sessGuardianId = \Core\Session::get('parent.impersonated_guardian_id');
            if ($sessGuardianId) {
                $guardian = $db->selectOne("SELECT * FROM guardians WHERE id = ? AND deleted_at IS NULL LIMIT 1", [(int)$sessGuardianId]);
            }
            if (!$guardian) {
                $guardian = $db->selectOne("SELECT * FROM guardians WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 1");
                if ($guardian) \Core\Session::set('parent.impersonated_guardian_id', $guardian['id']);
            }
        } else {
            $guardian = $db->selectOne("SELECT * FROM guardians WHERE user_id = ? AND deleted_at IS NULL LIMIT 1", [$authId]);
        }

        $allStudents = [];
        $activeStudent = null;
        if ($guardian) {
            $allStudents = $db->select(
                "SELECT s.* FROM students s JOIN guardian_student gs ON gs.student_id = s.id WHERE gs.guardian_id = ? AND s.deleted_at IS NULL",
                [$guardian['id']]
            );
            foreach ($allStudents as $s) {
                if ((int)$s['id'] === (int)$studentId) {
                    $activeStudent = $s;
                    break;
                }
            }
        }

        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }
        // A parent may only see their own child's report cards
        if (!$isAdminOrStaff && !$activeStudent) {
            $this->response->abort(404);
            exit();
        }

        // Semesters for which report cards exist
        $reportCards = $db->select(
            "SELECT id, academic_year, semester, created_at FROM student_report_cards
             WHERE student_id = ? ORDER BY academic_year DESC, semester DESC",
            [(int) $studentId]
        );

        $selectedId = (int) $this->request->get('report_card_id', 0);
        $selected = null;
        foreach ($reportCards as $rc) {
            if ($selectedId === 0 || (int) $rc['id'] === $selectedId) {
                $selected = $rc;
                break;
            }
        }
        $selectedReportCard = $selected
            ? $this->loadCard((int) $studentId, (string) $selected['academic_year'], (string) $selected['semester'])
            : null;

        if ($selectedReportCard && $this->request->get('pdf') === '1') {
            return $this->renderCard($student, $selectedReportCard, $selectedReportCard['semester'], $selectedReportCard['academic_year'], []);
        }

        return $this->view('parent/report-card', [
            'student'             => $student,
            'reportCards'         => $reportCards,
            'selectedReportCard'  => $selectedReportCard,
            'guardian'            => $guardian,
            'all_students'        => $allStudents,
            'active_student'      => $activeStudent ?: $student,
        ]);
    }

    public function settings(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $settings = $db->selectOne("SELECT * FROM report_card_settings WHERE tenant_id = ?", [$tenantId]);
        
        if (!$settings) {
            $defaultTrustees = [
                ['name' => 'Dr Griva Shah', 'title' => 'MT-PSNF', 'sig' => ''],
                ['name' => 'Bijal Fadia', 'title' => 'Trustee-PSNF', 'sig' => ''],
                ['name' => 'Sonia Parikh', 'title' => 'Trustee-PSNF', 'sig' => '']
            ];
            
            $settings = [
                'tenant_id' => $tenantId,
                'school_name' => 'Pearl Special Needs Foundation',
                'school_subtitle' => 'Center for Special Education & Care',
                'school_address' => 'Ahmedabad, Gujarat, India · contact@pearlspecialneeds.org',
                'stamp_text' => 'Pearl Special Needs Foundation',
                'stamp_image' => '',
                'pdf_font' => 'Inter',
                'primary_color' => '#0d3827',
                'class_teacher_name' => 'Class Teacher',
                'class_teacher_sig' => '',
                'trustees_config' => json_encode($defaultTrustees),
                'fields_config' => null
            ];
        }
        
        return $this->view('report-cards/settings', compact('settings'));
    }

    private ?string $lastUploadError = null;

    private function handleFileUpload(string $key, ?array $fileInput = null): ?string
    {
        $this->lastUploadError = null;
        $file = $fileInput ?? ($_FILES[$key] ?? null);
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $targetDir = ROOT_PATH . '/public/uploads/signatures';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        try {
            $extension = \App\Services\UploadGuard::check($file, \App\Services\UploadGuard::IMAGES);
        } catch (\RuntimeException $e) {
            $this->lastUploadError = $e->getMessage();
            return null;
        }
        $filename = \App\Services\UploadGuard::storedName($extension, 'sig_');
        $targetPath = $targetDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return url('uploads/signatures/' . $filename);
        }
        return null;
    }

    public function uploadSignature(): string
    {
        $file = $_FILES['signature'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $errCode = $file ? $file['error'] : 'no file object';
            return $this->json([
                'success' => false,
                'message' => 'No file uploaded or upload error occurred. Error: ' . $errCode
            ]);
        }
        
        // Validate MIME type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid file type. Only PNG, JPG, GIF, and WEBP are allowed.'
            ]);
        }
        
        $path = $this->handleFileUpload('signature');
        if ($path) {
            return $this->json([
                'success' => true,
                'path' => $path
            ]);
        }
        
        return $this->json([
            'success' => false,
            'message' => $this->lastUploadError ?? 'Failed to move uploaded file.'
        ]);
    }

    public function saveSettings(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $decode = function(?string $str): string {
            if (!$str) return '';
            $decoded = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            while ($decoded !== $str) {
                $str = $decoded;
                $decoded = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            }
            return $decoded;
        };

        $schoolName = $decode($this->request->input('school_name', 'Pearl Special Needs Foundation'));
        $schoolSubtitle = $decode($this->request->input('school_subtitle', 'Center for Special Education & Care'));
        $schoolAddress = $decode($this->request->input('school_address', ''));
        $stampText = $decode($this->request->input('stamp_text', 'Pearl Special Needs Foundation'));
        $pdfFont = $this->request->input('pdf_font', 'Inter');
        $primaryColor = $this->request->input('primary_color', '#0d3827');
        $classTeacherName = $decode($this->request->input('class_teacher_name', 'Class Teacher'));
        
        // Handle Class Teacher signature image upload (if uploaded via normal form post)
        $classTeacherSig = $this->handleFileUpload('class_teacher_sig');
        if (!$classTeacherSig) {
            $classTeacherSig = $this->request->input('class_teacher_sig_path', '');
            if (empty($classTeacherSig)) {
                $classTeacherSig = $this->request->input('old_class_teacher_sig', '');
            }
        }
        
        // Handle Stamp image upload
        $stampImage = $this->handleFileUpload('stamp_image');
        if (!$stampImage) {
            $stampImage = $this->request->input('stamp_image_path', '');
            if (empty($stampImage)) {
                $stampImage = $this->request->input('old_stamp_image', '');
            }
        }
        
        // Process trustees array from input
        $trusteesInput = $this->request->input('trustees', []);
        $trusteesConfig = [];
        
        foreach ($trusteesInput as $index => $t) {
            $name = $decode($t['name'] ?? '');
            $title = $decode($t['title'] ?? '');
            
            // Check file upload under trustee_sig_X
            $fileKey = "trustee_sig_" . $index;
            $sig = $this->handleFileUpload($fileKey);
            if (!$sig) {
                $sig = $t['sig'] ?? '';
                if (empty($sig)) {
                    $sig = $t['old_sig'] ?? '';
                }
            }
            
            if (!empty($name)) {
                $trusteesConfig[] = [
                    'name' => $name,
                    'title' => $title,
                    'sig' => $sig
                ];
            }
        }
        
        $trusteesJson = json_encode($trusteesConfig, JSON_UNESCAPED_UNICODE);
        
        // Handle fields_config JSON
        $fieldsConfigInput = $this->request->post('fields_config', '');
        $fieldsConfig = json_decode($fieldsConfigInput, true);
        $fieldsConfigJson = !empty($fieldsConfig) ? json_encode($fieldsConfig, JSON_UNESCAPED_UNICODE) : null;
        
        $exists = $db->selectOne("SELECT tenant_id FROM report_card_settings WHERE tenant_id = ?", [$tenantId]);
        
        try {
            if ($exists) {
                $db->query(
                    "UPDATE report_card_settings SET 
                        school_name = ?,
                        school_subtitle = ?,
                        school_address = ?,
                        stamp_text = ?,
                        stamp_image = ?,
                        pdf_font = ?,
                        primary_color = ?,
                        class_teacher_name = ?,
                        class_teacher_sig = ?,
                        trustees_config = ?,
                        fields_config = ?
                     WHERE tenant_id = ?",
                    [
                        $schoolName,
                        $schoolSubtitle,
                        $schoolAddress,
                        $stampText,
                        $stampImage,
                        $pdfFont,
                        $primaryColor,
                        $classTeacherName,
                        $classTeacherSig,
                        $trusteesJson,
                        $fieldsConfigJson,
                        $tenantId
                    ]
                );
            } else {
                $db->insert('report_card_settings', [
                    'tenant_id' => $tenantId,
                    'school_name' => $schoolName,
                    'school_subtitle' => $schoolSubtitle,
                    'school_address' => $schoolAddress,
                    'stamp_text' => $stampText,
                    'stamp_image' => $stampImage,
                    'pdf_font' => $pdfFont,
                    'primary_color' => $primaryColor,
                    'class_teacher_name' => $classTeacherName,
                    'class_teacher_sig' => $classTeacherSig,
                    'trustees_config' => $trusteesJson,
                    'fields_config' => $fieldsConfigJson
                ]);
            }
            $this->flash('success', 'Report Card Settings updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update settings: ' . $e->getMessage());
        }
        
        return $this->redirect(url('report-cards/settings'));
    }

    public static function getDefaultFields(): array
    {
        return [
            'routine' => [
                'class_work' => 'Class Work Attentiveness',
                'home_work' => 'Home Work Completion',
                'book_care' => 'Book & Material Care',
                'uniform' => 'Neatness of School Uniform',
                'grooming' => 'Personal Grooming & Cleanliness',
                'peer_relationship' => 'Peer Relationships & Social Behavior',
                'eating_habits' => 'Self-Help: Eating Habits',
                'toileting_habits' => 'Self-Help: Toileting Independence',
                'dressing_habits' => 'Self-Help: Dressing Independence',
                'hygiene' => 'Self-Help: Personal Hygiene Habits'
            ],
            'skills' => [
                'reading_phonics' => 'Reading & Phonics identification',
                'writing_motor' => 'Writing & Fine Motor grip',
                'speaking_expressive' => 'Speaking & Expressive Language abilities',
                'listening_receptive' => 'Listening & Receptive instructions',
                'number_counting' => 'Number Recognition & Counting up to 20/50',
                'matching_categorization' => 'Concept Matching & Categorization',
                'visual_discrimination' => 'Visual Discrimination (Avaz sheet, puzzles)'
            ],
            'academics' => [
                'english' => 'English Language',
                'maths' => 'Mathematics',
                'evs_gk' => 'GK / EVS (Env. Studies)'
            ],
            'cocurricular' => [
                'computer' => 'Computer / Digital Literacy',
                'music' => 'Music / Rhythm Therapy',
                'pe_gross_motor' => 'Physical Education & Gross Motor Activities',
                'structured_games' => 'Structured Games & Play Therapy',
                'diy_kits' => 'DIY Kits & Fine Motor Assemblies',
                'cognitive_activities' => 'Cognitive & Critical Puzzles'
            ]
        ];
    }
}
