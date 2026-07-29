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
        
        $activeYearRow = $db->selectOne("SELECT year_name FROM academic_years WHERE status = 'current' AND tenant_id = ? LIMIT 1", [$tenantId]);
        $academicYear = $activeYearRow['year_name'] ?? '2025-26';
        
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

        $yearsList = $db->select("SELECT year_name FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);
        $semestersList = $db->select("SELECT name FROM academic_semesters WHERE tenant_id = ? GROUP BY name ORDER BY id ASC", [$tenantId]);

        // Fallbacks if empty
        if (empty($yearsList)) {
            $yearsList = [['year_name' => '2024-25'], ['year_name' => '2025-26'], ['year_name' => '2026-27']];
        }
        if (empty($semestersList)) {
            $semestersList = [['name' => 'Semester 1'], ['name' => 'Semester 2']];
        }

        return $this->view('report-cards/index', compact('students', 'semester', 'academicYear', 'yearsList', 'semestersList'));
    }

    public function edit(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        list($defaultYear, $defaultSem) = $this->getDefaults();
        $semester = $this->request->get('semester', $defaultSem);
        $academicYear = $this->request->get('academic_year', (!empty($student['academic_year']) ? $student['academic_year'] : $defaultYear));

        $db = $this->db();
        $reportCardRow = $db->selectOne(
            "SELECT * FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
            [(int) $studentId, $academicYear, $semester]
        );

        $reportCard = null;
        if ($reportCardRow) {
            $reportCard = [
                'id' => $reportCardRow['id'],
                'academic_year' => $reportCardRow['academic_year'],
                'semester' => $reportCardRow['semester'],
                'routine_profile' => json_decode($reportCardRow['routine_profile'], true) ?: [],
                'learning_skills' => json_decode($reportCardRow['learning_skills'], true) ?: [],
                'academic_profile' => json_decode($reportCardRow['academic_profile'], true) ?: [],
                'cocurriculum_profile' => json_decode($reportCardRow['cocurriculum_profile'], true) ?: [],
                'attendance_profile' => json_decode($reportCardRow['attendance_profile'], true) ?: [],
                'feedback_text' => $reportCardRow['feedback_text'] ?? '',
                'authorized_by' => json_decode($reportCardRow['authorized_by'] ?? '[]', true) ?: [],
            ];
        }

        $settings = $db->selectOne("SELECT * FROM report_card_settings WHERE tenant_id = ?", [$student['tenant_id'] ?? \Core\Database::getTenantId()]);
        if ($settings) {
            $settings['trustees_config'] = json_decode($settings['trustees_config'] ?? '[]', true) ?: [];
        }

        return $this->view('report-cards/edit', compact('student', 'reportCard', 'semester', 'academicYear', 'settings'));
    }

    public function store(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        $db = $this->db();
        list($defaultYear, $defaultSem) = $this->getDefaults();
        $academicYear = $this->request->input('academic_year', $defaultYear);
        $semester = $this->request->input('semester', $defaultSem);

        $routineProfile = $this->request->input('routine_profile', []);
        $learningSkills = $this->request->input('learning_skills', []);
        $academicProfile = $this->request->input('academic_profile', []);
        $cocurriculumProfile = $this->request->input('cocurriculum_profile', []);
        $attendanceProfile = $this->request->input('attendance_profile', []);
        $feedbackText = $this->request->input('feedback_text', '');
        
        $authorizedBy = $this->request->input('authorized_by', []);

        // Process Class Teacher signature upload
        $ctFile = $this->request->file('class_teacher_sig');
        if ($ctFile && $ctFile['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($ctFile['tmp_name']);
            $base64 = 'data:' . mime_content_type($ctFile['tmp_name']) . ';base64,' . base64_encode($imageData);
            $authorizedBy['class_teacher_sig'] = $base64;
        } else {
            $authorizedBy['class_teacher_sig'] = $this->request->input('old_class_teacher_sig', '');
        }

        // Process Coordinator signature upload
        $coFile = $this->request->file('coordinator_sig');
        if ($coFile && $coFile['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($coFile['tmp_name']);
            $base64 = 'data:' . mime_content_type($coFile['tmp_name']) . ';base64,' . base64_encode($imageData);
            $authorizedBy['coordinator_sig'] = $base64;
        } else {
            $authorizedBy['coordinator_sig'] = $this->request->input('old_coordinator_sig', '');
        }

        $routineJson = json_encode($routineProfile, JSON_UNESCAPED_UNICODE);
        $learningJson = json_encode($learningSkills, JSON_UNESCAPED_UNICODE);
        $academicJson = json_encode($academicProfile, JSON_UNESCAPED_UNICODE);
        $cocurriculumJson = json_encode($cocurriculumProfile, JSON_UNESCAPED_UNICODE);
        $attendanceJson = json_encode($attendanceProfile, JSON_UNESCAPED_UNICODE);
        $authorizedJson = json_encode($authorizedBy, JSON_UNESCAPED_UNICODE);

        $exists = $db->selectOne(
            "SELECT id FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
            [(int) $studentId, $academicYear, $semester]
        );

        try {
            if ($exists) {
                $db->query(
                    "UPDATE student_report_cards SET 
                        routine_profile = ?,
                        learning_skills = ?,
                        academic_profile = ?,
                        cocurriculum_profile = ?,
                        attendance_profile = ?,
                        feedback_text = ?,
                        authorized_by = ?
                     WHERE id = ?",
                    [
                        $routineJson,
                        $learningJson,
                        $academicJson,
                        $cocurriculumJson,
                        $attendanceJson,
                        $feedbackText,
                        $authorizedJson,
                        $exists['id']
                    ]
                );
            } else {
                $db->insert('student_report_cards', [
                    'tenant_id' => $student['tenant_id'] ?? \Core\Database::getTenantId(),
                    'school_id' => $student['school_id'] ?? 1,
                    'branch_id' => $student['branch_id'] ?? 1,
                    'student_id' => (int) $studentId,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                    'routine_profile' => $routineJson,
                    'learning_skills' => $learningJson,
                    'academic_profile' => $academicJson,
                    'cocurriculum_profile' => $cocurriculumJson,
                    'attendance_profile' => $attendanceJson,
                    'feedback_text' => $feedbackText,
                    'authorized_by' => $authorizedJson,
                ]);
            }

            $this->flash('success', 'Report Card saved successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save Report Card: ' . $e->getMessage());
        }

        return $this->redirect("/students/$studentId/report-card/edit?semester=" . urlencode($semester) . "&academic_year=" . urlencode($academicYear));
    }

    public function show(string $studentId): string
    {
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        list($defaultYear, $defaultSem) = $this->getDefaults();
        $semester = $this->request->get('semester', $defaultSem);
        $academicYear = $this->request->get('academic_year', (!empty($student['academic_year']) ? $student['academic_year'] : $defaultYear));

        $db = $this->db();
        $reportCardRow = $db->selectOne(
            "SELECT * FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
            [(int) $studentId, $academicYear, $semester]
        );

        if (!$reportCardRow) {
            return "<h3>No report card found for " . e($student['first_name']) . " " . e($student['last_name']) . " for $semester ($academicYear). Please generate one first.</h3>";
        }

        $reportCard = [
            'id' => $reportCardRow['id'],
            'academic_year' => $reportCardRow['academic_year'],
            'semester' => $reportCardRow['semester'],
            'routine_profile' => json_decode($reportCardRow['routine_profile'], true) ?: [],
            'learning_skills' => json_decode($reportCardRow['learning_skills'], true) ?: [],
            'academic_profile' => json_decode($reportCardRow['academic_profile'], true) ?: [],
            'cocurriculum_profile' => json_decode($reportCardRow['cocurriculum_profile'], true) ?: [],
            'attendance_profile' => json_decode($reportCardRow['attendance_profile'], true) ?: [],
            'feedback_text' => $reportCardRow['feedback_text'] ?? '',
            'authorized_by' => json_decode($reportCardRow['authorized_by'] ?? '[]', true) ?: [],
        ];

        $settings = $db->selectOne("SELECT * FROM report_card_settings WHERE tenant_id = ?", [$student['tenant_id']]);
        if (!$settings) {
            $defaultTrustees = [
                ['name' => 'Dr Griva Shah', 'title' => 'MT-PSNF', 'sig' => ''],
                ['name' => 'Bijal Fadia', 'title' => 'Trustee-PSNF', 'sig' => ''],
                ['name' => 'Sonia Parikh', 'title' => 'Trustee-PSNF', 'sig' => '']
            ];
            
            $settings = [
                'school_name' => 'Pearl Special Needs Foundation',
                'school_subtitle' => 'Center for Special Education & Care',
                'school_address' => 'Ahmedabad, Gujarat, India · contact@pearlspecialneeds.org',
                'stamp_text' => 'Pearl Special Needs Foundation',
                'stamp_image' => '',
                'pdf_font' => 'Inter',
                'primary_color' => '#0d3827',
                'class_teacher_name' => 'Class Teacher',
                'class_teacher_sig' => '',
                'trustees_config' => json_encode($defaultTrustees)
            ];
        }
        $settings['trustees_config'] = json_decode($settings['trustees_config'] ?? '[]', true) ?: [];

        if ($this->request->get('pdf') === '1') {
            $composerAutoload = ROOT_PATH . '/certificate_generator/vendor/autoload.php';
            if (file_exists($composerAutoload)) {
                require_once $composerAutoload;
            }

            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);

            $isPdf = true;
            $name = $student['first_name'] . ' ' . $student['last_name'];
            ob_start();
            extract(compact('student', 'reportCard', 'semester', 'academicYear', 'isPdf', 'name', 'settings'));
            include VIEWS_PATH . '/report-cards/view.php';
            $html = ob_get_clean();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = "ReportCard_" . str_replace(' ', '_', $student['first_name'] . '_' . $student['last_name']) . "_" . str_replace(' ', '_', $semester) . ".pdf";
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        }

        return $this->view('report-cards/view', compact('student', 'reportCard', 'semester', 'academicYear', 'settings'));
    }

    public function parentShow(string $studentId): string
    {
        // For parent role, check if they are authorized to view this student
        // Typically, we check if the logged in user is the guardian of this student
        $authId = $this->authId();
        $db = $this->db();

        $guardian = $db->selectOne(
            "SELECT g.id FROM guardians g 
             JOIN users u ON g.user_id = u.id 
             JOIN students s ON s.id = ? 
             WHERE u.id = ? AND s.tenant_id = g.tenant_id",
            [(int) $studentId, $authId]
        );

        // Allow access if user is parent role and linked to student
        $student = Student::withDetails((int) $studentId);
        if (!$student) {
            $this->response->abort(404);
            exit();
        }

        // Fetch semesters for which report cards exist
        $reportCards = $db->select(
            "SELECT id, academic_year, semester, created_at FROM student_report_cards 
             WHERE student_id = ? ORDER BY academic_year DESC, semester DESC",
            [(int) $studentId]
        );

        $selectedId = (int) $this->request->get('report_card_id', 0);
        $selectedReportCard = null;

        if ($selectedId > 0) {
            foreach ($reportCards as $rc) {
                if ((int) $rc['id'] === $selectedId) {
                    $row = $db->selectOne("SELECT * FROM student_report_cards WHERE id = ?", [$selectedId]);
                    if ($row) {
                        $selectedReportCard = [
                            'id' => $row['id'],
                            'academic_year' => $row['academic_year'],
                            'semester' => $row['semester'],
                            'routine_profile' => json_decode($row['routine_profile'], true) ?: [],
                            'learning_skills' => json_decode($row['learning_skills'], true) ?: [],
                            'academic_profile' => json_decode($row['academic_profile'], true) ?: [],
                            'cocurriculum_profile' => json_decode($row['cocurriculum_profile'], true) ?: [],
                            'attendance_profile' => json_decode($row['attendance_profile'], true) ?: [],
                            'feedback_text' => $row['feedback_text'] ?? '',
                            'authorized_by' => json_decode($row['authorized_by'] ?? '[]', true) ?: [],
                        ];
                    }
                    break;
                }
            }
        } elseif (!empty($reportCards)) {
            // default to first one
            $first = $reportCards[0];
            $row = $db->selectOne("SELECT * FROM student_report_cards WHERE id = ?", [(int)$first['id']]);
            if ($row) {
                $selectedReportCard = [
                    'id' => $row['id'],
                    'academic_year' => $row['academic_year'],
                    'semester' => $row['semester'],
                    'routine_profile' => json_decode($row['routine_profile'], true) ?: [],
                    'learning_skills' => json_decode($row['learning_skills'], true) ?: [],
                    'academic_profile' => json_decode($row['academic_profile'], true) ?: [],
                    'cocurriculum_profile' => json_decode($row['cocurriculum_profile'], true) ?: [],
                    'attendance_profile' => json_decode($row['attendance_profile'], true) ?: [],
                    'feedback_text' => $row['feedback_text'] ?? '',
                    'authorized_by' => json_decode($row['authorized_by'] ?? '[]', true) ?: [],
                ];
            }
        }

        return $this->view('parent/report-card', compact('student', 'reportCards', 'selectedReportCard'));
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

    private function handleFileUpload(string $key, ?array $fileInput = null): ?string
    {
        $file = $fileInput ?? ($_FILES[$key] ?? null);
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $targetDir = ROOT_PATH . '/public/uploads/signatures';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $mimeType = mime_content_type($file['tmp_name']);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = str_replace('image/', '', $mimeType);
        }
        $filename = 'sig_' . uniqid() . '_' . time() . '.' . $extension;
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
            'message' => 'Failed to move uploaded file.'
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
        
        return $this->redirect('/report-cards/settings');
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
