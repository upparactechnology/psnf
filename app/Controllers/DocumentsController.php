<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Database;
use App\Models\Student;
use App\Models\StudentDocument;

class DocumentsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function dashboard(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        // Count totals
        $studentDocsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM student_documents WHERE deleted_at IS NULL")['cnt'] ?? 0);
        $staffDocsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM staff_documents WHERE deleted_at IS NULL")['cnt'] ?? 0);
        $parentDocsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM parent_documents WHERE deleted_at IS NULL")['cnt'] ?? 0);
        $driverDocsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM driver_documents WHERE deleted_at IS NULL")['cnt'] ?? 0);
        
        $totalDocuments = $studentDocsCount + $staffDocsCount + $parentDocsCount + $driverDocsCount;
        
        $generatedCertsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM generated_certificates")['cnt'] ?? 0);
        $studentIdsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE deleted_at IS NULL AND is_active = 1")['cnt'] ?? 0);
        $receiptsCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM fee_payments")['cnt'] ?? 0);
        
        // Pending verification
        $pendingStudent = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM student_documents WHERE status = 'pending' AND deleted_at IS NULL")['cnt'] ?? 0);
        $pendingStaff = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM staff_documents WHERE status = 'pending' AND deleted_at IS NULL")['cnt'] ?? 0);
        $pendingParent = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM parent_documents WHERE status = 'pending' AND deleted_at IS NULL")['cnt'] ?? 0);
        $pendingDriver = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM driver_documents WHERE status = 'pending' AND deleted_at IS NULL")['cnt'] ?? 0);
        $pendingVerification = $pendingStudent + $pendingStaff + $pendingParent + $pendingDriver;

        // Dynamic expiry alerts — documents expiring within 30 days or already expired
        $expiryAlerts = [];

        // Staff documents expiring soon
        $staffExpiring = $db->select("
            SELECT sd.title, sd.type, sd.expiry_date, e.first_name, e.last_name, e.id as staff_id
            FROM staff_documents sd
            JOIN employees e ON e.id = sd.staff_id
            WHERE sd.expiry_date IS NOT NULL 
              AND sd.deleted_at IS NULL
              AND sd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY sd.expiry_date ASC
            LIMIT 5
        ");
        foreach ($staffExpiring as $doc) {
            $daysLeft = (int) (strtotime($doc['expiry_date']) - strtotime('now')) / 86400;
            $expiryAlerts[] = [
                'type' => $daysLeft < 0 ? 'expired' : 'expiring',
                'category' => 'staff',
                'title' => $doc['type'] . ' — ' . $doc['first_name'] . ' ' . $doc['last_name'],
                'detail' => $daysLeft < 0
                    ? 'Expired ' . abs($daysLeft) . ' days ago'
                    : 'Expires in ' . $daysLeft . ' days',
                'expiry_date' => $doc['expiry_date'],
                'link' => '/documents/staff-documents?staff_id=' . $doc['staff_id'],
                'link_text' => 'View Record',
            ];
        }

        // Driver documents expiring soon
        $driverExpiring = $db->select("
            SELECT dd.title, dd.type, dd.expiry_date, td.name as driver_name, td.id as driver_id
            FROM driver_documents dd
            JOIN transport_drivers td ON td.id = dd.driver_id
            WHERE dd.expiry_date IS NOT NULL 
              AND dd.deleted_at IS NULL
              AND dd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY dd.expiry_date ASC
            LIMIT 5
        ");
        foreach ($driverExpiring as $doc) {
            $daysLeft = (int) (strtotime($doc['expiry_date']) - strtotime('now')) / 86400;
            $expiryAlerts[] = [
                'type' => $daysLeft < 0 ? 'expired' : 'expiring',
                'category' => 'driver',
                'title' => $doc['type'] . ' — ' . $doc['driver_name'],
                'detail' => $daysLeft < 0
                    ? 'Expired ' . abs($daysLeft) . ' days ago'
                    : 'Expires in ' . $daysLeft . ' days',
                'expiry_date' => $doc['expiry_date'],
                'link' => '/documents/driver-documents?driver_id=' . $doc['driver_id'],
                'link_text' => 'Renew Record',
            ];
        }

        // Driver license expiry from transport_drivers
        $licenseExpiring = $db->select("
            SELECT license_number, license_expiry, name, id
            FROM transport_drivers
            WHERE license_expiry IS NOT NULL
              AND license_expiry <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              AND status = 'active'
            ORDER BY license_expiry ASC
            LIMIT 3
        ");
        foreach ($licenseExpiring as $drv) {
            $daysLeft = (int) (strtotime($drv['license_expiry']) - strtotime('now')) / 86400;
            $expiryAlerts[] = [
                'type' => $daysLeft < 0 ? 'expired' : 'expiring',
                'category' => 'driver',
                'title' => 'License — ' . $drv['name'],
                'detail' => $daysLeft < 0
                    ? 'License #' . $drv['license_number'] . ' expired ' . abs($daysLeft) . ' days ago'
                    : 'License #' . $drv['license_number'] . ' expires in ' . $daysLeft . ' days',
                'expiry_date' => $drv['license_expiry'],
                'link' => '/documents/driver-documents?driver_id=' . $drv['id'],
                'link_text' => 'Renew Record',
            ];
        }

        // Sort by expiry date, most urgent first
        usort($expiryAlerts, fn($a, $b) => strtotime($a['expiry_date']) - strtotime($b['expiry_date']));
        $expiryAlerts = array_slice($expiryAlerts, 0, 5);

        if (empty($expiryAlerts)) {
            $expiryAlerts[] = [
                'type' => 'none',
                'category' => 'system',
                'title' => 'All Clear',
                'detail' => 'No documents expiring in the next 30 days.',
                'expiry_date' => null,
                'link' => '#',
                'link_text' => '',
            ];
        }

        // Dynamic recent activity
        $recentUploads = $db->select("
            (SELECT title, status, created_at as activity_time, '📄' as icon FROM student_documents WHERE deleted_at IS NULL)
            UNION ALL
            (SELECT title, status, created_at as activity_time, '💼' as icon FROM staff_documents WHERE deleted_at IS NULL)
            UNION ALL
            (SELECT CONCAT('Receipt REC-', LPAD(id, 5, '0')) as title, 'Printed' as status, paid_at as activity_time, '🧾' as icon FROM fee_payments)
            ORDER BY activity_time DESC LIMIT 4
        ");
        
        $recentActivity = [];
        foreach ($recentUploads as $upload) {
            $diff = time() - strtotime($upload['activity_time']);
            if ($diff < 3600) {
                $timeLabel = 'Just now';
            } elseif ($diff < 86400) {
                $timeLabel = 'Today';
            } elseif ($diff < 172800) {
                $timeLabel = 'Yesterday';
            } else {
                $timeLabel = date('M d, Y', strtotime($upload['activity_time']));
            }
            $recentActivity[] = [
                'title' => $upload['title'],
                'status' => $upload['status'] === 'verified' ? 'Verified' : ($upload['status'] === 'rejected' ? 'Rejected' : 'Generated'),
                'time' => $timeLabel,
                'icon' => $upload['icon']
            ];
        }
        if (empty($recentActivity)) {
            $recentActivity = [
                ['title' => 'No recent activities', 'status' => 'System', 'time' => '—', 'icon' => '🛡️']
            ];
        }

        return $this->view('documents/dashboard', compact(
            'totalDocuments', 'studentDocsCount', 'staffDocsCount', 'parentDocsCount', 'driverDocsCount',
            'generatedCertsCount', 'studentIdsCount', 'receiptsCount', 'pendingVerification', 'recentActivity', 'expiryAlerts'
        ));
    }

    public function studentDocuments(): string
    {
        $db = $this->db();
        $tenantId = Database::getTenantId();
        
        // Get all students
        $students = $db->select("
            SELECT s.id, s.first_name, s.last_name, s.admission_number, s.class as class_name
            FROM students s
            WHERE s.tenant_id = ? AND s.deleted_at IS NULL
            ORDER BY s.first_name ASC
        ", [$tenantId]);

        // Get document mapping
        $studentId = (int) ($_GET['student_id'] ?? ($students[0]['id'] ?? 0));
        
        $documents = [];
        if ($studentId > 0) {
            $documents = $db->select("
                SELECT * FROM student_documents 
                WHERE student_id = ? AND deleted_at IS NULL
            ", [$studentId]);
        }

        // Available document types for students
        $docTypes = [
            'photo' => 'Student Photo',
            'aadhar' => 'Student Aadhar Card',
            'certificate' => 'Certificate',
        ];

        return $this->view('documents/student_documents', compact('students', 'studentId', 'documents', 'docTypes'));
    }

    public function uploadStudentDoc()
    {
        $db = $this->db();
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $docType = $_POST['type'] ?? 'other';
        $title = $_POST['title'] ?? 'Document';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

        if ($studentId <= 0 || empty($_FILES['file']['name'])) {
            $this->redirect(url('documents/student-documents?student_id=' . $studentId . '&error=Invalid parameters'));
            return;
        }

        $file = $_FILES['file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $storedName = md5(uniqid() . $file['name']) . '.' . $ext;
        
        $uploadDir = STORAGE_PATH . '/uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
            // Save version history before inserting/updating
            $existing = $db->selectOne("
                SELECT * FROM student_documents 
                WHERE student_id = ? AND type = ? AND deleted_at IS NULL
            ", [$studentId, $docType]);

            if ($existing) {
                // Save version
                $versionNum = (int) ($db->selectOne("SELECT MAX(version) as max_v FROM document_versions WHERE document_type='student' AND document_id=?", [$existing['id']])['max_v'] ?? 0) + 1;
                $db->query("
                    INSERT INTO document_versions (document_type, document_id, version, file_name, stored_name, file_size, uploaded_at)
                    VALUES ('student', ?, ?, ?, ?, ?, NOW())
                ", [$existing['id'], $versionNum, $existing['file_name'], $existing['stored_name'], $existing['file_size']]);

                // Update current document
                $db->query("
                    UPDATE student_documents 
                    SET title = ?, file_name = ?, stored_name = ?, mime_type = ?, file_size = ?, status = 'pending', updated_at = NOW()
                    WHERE id = ?
                ", [$title, $file['name'], $storedName, $file['type'], $file['size'], $existing['id']]);
            } else {
                // Insert new
                $db->query("
                    INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ", [$studentId, $docType, $title, $file['name'], $storedName, $file['type'], $file['size']]);
            }

            $this->redirect(url('documents/student-documents?student_id=' . $studentId . '&success=Document uploaded successfully'));
        } else {
            $this->redirect(url('documents/student-documents?student_id=' . $studentId . '&error=Failed to upload file'));
        }
    }

    public function staffDocuments(): string
    {
        $db = $this->db();
        $tenantId = Database::getTenantId();
        
        $staffList = $db->select("
            SELECT id, first_name, last_name, employee_code, department
            FROM employees
            WHERE tenant_id = ?
            ORDER BY first_name ASC
        ", [$tenantId]);

        $staffId = (int) ($_GET['staff_id'] ?? ($staffList[0]['id'] ?? 0));
        
        $documents = [];
        if ($staffId > 0) {
            $documents = $db->select("
                SELECT * FROM staff_documents 
                WHERE staff_id = ?
            ", [$staffId]);
        }

        $docTypes = [
            'resume' => 'Resume',
            'joining_letter' => 'Joining Letter',
            'appointment_letter' => 'Appointment Letter',
            'aadhar' => 'Aadhar Card',
            'pan' => 'PAN Card',
            'bank_passbook' => 'Bank Passbook',
            'edu_certificates' => 'Educational Certificates',
            'exp_certificates' => 'Experience Certificates',
            'police_verification' => 'Police Verification',
            'medical_certificate' => 'Medical Certificate',
            'salary_agreement' => 'Salary Agreement',
            'contract' => 'Contract'
        ];

        return $this->view('documents/staff_documents', compact('staffList', 'staffId', 'documents', 'docTypes'));
    }

    public function parentDocuments(): string
    {
        $db = $this->db();
        
        $tenantId = Database::getTenantId();
        $parents = $db->select("
            SELECT id, name, '' as email, phone as phone_number
            FROM guardians
            WHERE tenant_id = ?
            ORDER BY name ASC
        ", [$tenantId]);

        $parentId = (int) ($_GET['parent_id'] ?? ($parents[0]['id'] ?? 0));
        
        $documents = [];
        if ($parentId > 0) {
            $documents = $db->select("
                SELECT * FROM parent_documents 
                WHERE parent_id = ? AND deleted_at IS NULL
            ", [$parentId]);
        }

        $docTypes = [
            'photo' => 'Parent Photo',
            'aadhar' => 'Aadhar Card'
        ];

        return $this->view('documents/parent_documents', compact('parents', 'parentId', 'documents', 'docTypes'));
    }

    public function driverDocuments(): string
    {
        $db = $this->db();
        $tenantId = Database::getTenantId();
        
        $drivers = $db->select("
            SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as name, e.phone
            FROM employees e
            JOIN designations des ON e.designation_id = des.id
            WHERE e.tenant_id = ? AND des.title = 'Driver'
            ORDER BY name ASC
        ", [$tenantId]);

        $driverId = (int) ($_GET['driver_id'] ?? ($drivers[0]['id'] ?? 0));
        
        $documents = [];
        if ($driverId > 0) {
            $documents = $db->select("
                SELECT * FROM driver_documents 
                WHERE driver_id = ? AND deleted_at IS NULL
            ", [$driverId]);
        }

        $docTypes = [
            'photo' => 'Driver Photo',
            'aadhar' => 'Aadhar Card'
        ];

        return $this->view('documents/driver_documents', compact('drivers', 'driverId', 'documents', 'docTypes'));
    }

    public function generated(): string
    {
        $db = $this->db();
        $category = $_GET['category'] ?? 'certificates';

        // Select documents according to categories
        $records = [];
        if ($category === 'certificates') {
            $records = $db->select("
                SELECT gc.id, ct.name as doc_name, s.first_name, s.last_name, 'Certificate' as type,
                       gc.pdf_path as file_path, gc.generated_at as created_at
                FROM generated_certificates gc
                JOIN participants p ON p.id = gc.participant_id
                JOIN certificate_types ct ON ct.id = p.certificate_type_id
                JOIN students s ON s.id = p.student_id
                ORDER BY gc.generated_at DESC
            ");
        } elseif ($category === 'receipts') {
            $records = $db->select("
                SELECT fp.id, CONCAT('REC-', LPAD(fp.id, 5, '0')) as doc_name, s.first_name, s.last_name, 'Fee Receipt' as type,
                       '/' as file_path, fp.paid_at as created_at
                FROM fee_payments fp
                JOIN fee_invoices fi ON fi.id = fp.invoice_id
                JOIN students s ON s.id = fi.student_id
                ORDER BY fp.paid_at DESC
            ");
        } elseif ($category === 'student_ids') {
            $records = $db->select("
                SELECT id, CONCAT('Student ID - ', first_name, ' ', last_name) as doc_name,
                       first_name, last_name, 'Student ID Card' as type, '/' as file_path, created_at
                FROM students
                WHERE deleted_at IS NULL AND is_active = 1
                ORDER BY created_at DESC LIMIT 20
            ");
        } elseif ($category === 'staff_ids') {
            $records = $db->select("
                SELECT id, CONCAT('Staff ID - ', name) as doc_name,
                       name as first_name, '' as last_name, 'Staff ID Card' as type, '/' as file_path, created_at
                FROM staff
                WHERE is_active = 1
                ORDER BY created_at DESC LIMIT 20
            ");
        } elseif ($category === 'report_cards') {
            $records = $db->select("
                SELECT src.id, CONCAT('Report Card - ', src.semester, ' (', src.academic_year, ')') as doc_name,
                       s.first_name, s.last_name, 'Report Card' as type, '/' as file_path, src.created_at
                FROM student_report_cards src
                JOIN students s ON s.id = src.student_id
                ORDER BY src.created_at DESC LIMIT 20
            ");
        } else {
            $records = [];
        }

        return $this->view('documents/generated', compact('records', 'category'));
    }

    public function templates(): string
    {
        $db = $this->db();
        
        // Merge certificate_types from certificate generator and document_templates
        $templates = $db->select("
            SELECT id, name, 'certificate' as type, 1 as qr_enabled, CONCAT('/psnf/public/certificate_generator/index.php?page=field-editor&certificate_type_id=', id) as editor_url
            FROM certificate_types
            UNION ALL
            SELECT id, name, type, qr_enabled, '#' as editor_url
            FROM document_templates
            ORDER BY id DESC
        ");
        
        $tab = $_GET['tab'] ?? 'templates';
        
        return $this->view('documents/templates', compact('templates', 'tab'));
    }

    public function settings(): string
    {
        return $this->view('documents/settings');
    }
}
