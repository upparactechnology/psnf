<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Services\UploadGuard;

class PublicEnrollmentController extends Controller
{
    public function showForm(): string
    {
        return $this->render('public/enrollment_form', [
            'errors'  => Session::getFlash('errors') ?? [],
            'old'     => Session::getFlash('old') ?? [],
            'success' => Session::getFlash('success'),
        ]);
    }

    public function submitForm(): string
    {
        $req = $this->request->all();

        // 1. Mandatory Fields Validation
        $errors = [];
        if (empty(trim($req['student_full_name'] ?? ''))) {
            $errors['student_full_name'] = 'Student full name is required.';
        }
        if (empty($req['dob'] ?? '')) {
            $errors['dob'] = 'Date of birth is required.';
        } elseif (strtotime($req['dob']) > time()) {
            $errors['dob'] = 'Date of birth cannot be in the future.';
        }

        if (empty($req['gender'] ?? '')) {
            $errors['gender'] = 'Gender is required.';
        }

        // Student Aadhar Optional Check
        $sAadhar = preg_replace('/\D/', '', $req['student_aadhar'] ?? '');
        if (!empty($req['student_aadhar']) && strlen($sAadhar) !== 12) {
            $errors['student_aadhar'] = 'Student Aadhar number must be exactly 12 digits.';
        }

        // Father Validation
        if (empty(trim($req['father_name'] ?? ''))) {
            $errors['father_name'] = "Father's name is required.";
        }
        $fPhone = preg_replace('/\D/', '', $req['father_phone'] ?? '');
        if (empty($fPhone)) {
            $errors['father_phone'] = "Father's phone number is required.";
        } elseif (strlen($fPhone) < 10 || strlen($fPhone) > 12) {
            $errors['father_phone'] = "Father's phone number must be a valid 10-digit number.";
        }

        $fAadhar = preg_replace('/\D/', '', $req['father_aadhar'] ?? '');
        if (empty($fAadhar)) {
            $errors['father_aadhar'] = "Father's Aadhar number is required.";
        } elseif (strlen($fAadhar) !== 12) {
            $errors['father_aadhar'] = "Father's Aadhar number must be exactly 12 digits.";
        }

        // Mother Validation
        if (empty(trim($req['mother_name'] ?? ''))) {
            $errors['mother_name'] = "Mother's name is required.";
        }
        $mPhone = preg_replace('/\D/', '', $req['mother_phone'] ?? '');
        if (empty($mPhone)) {
            $errors['mother_phone'] = "Mother's phone number is required.";
        } elseif (strlen($mPhone) < 10 || strlen($mPhone) > 12) {
            $errors['mother_phone'] = "Mother's phone number must be a valid 10-digit number.";
        }

        $mAadhar = preg_replace('/\D/', '', $req['mother_aadhar'] ?? '');
        if (empty($mAadhar)) {
            $errors['mother_aadhar'] = "Mother's Aadhar number is required.";
        } elseif (strlen($mAadhar) !== 12) {
            $errors['mother_aadhar'] = "Mother's Aadhar number must be exactly 12 digits.";
        }

        // Check photos (Upload or WebCam Base64)
        $studentPhoto = $this->processImageUpload('student_photo', 'student_photo_cam');
        if (!$studentPhoto) {
            $errors['student_photo'] = 'Student passport size photo is required.';
        }

        $fatherPhoto = $this->processImageUpload('father_photo', 'father_photo_cam');
        if (!$fatherPhoto) {
            $errors['father_photo'] = "Father's passport size photo is required.";
        }

        $motherPhoto = $this->processImageUpload('mother_photo', 'mother_photo_cam');
        if (!$motherPhoto) {
            $errors['mother_photo'] = "Mother's passport size photo is required.";
        }

        // Aadhar Documents Validation
        $fatherAadharDoc = $this->processFileUpload('father_aadhar_doc');
        if (!$fatherAadharDoc) {
            $errors['father_aadhar_doc'] = "Father's Aadhar card document is required.";
        }

        $motherAadharDoc = $this->processFileUpload('mother_aadhar_doc');
        if (!$motherAadharDoc) {
            $errors['mother_aadhar_doc'] = "Mother's Aadhar card document is required.";
        }

        $studentAadharDoc = $this->processFileUpload('student_aadhar_doc'); // Required if Aadhar No is provided
        $studentAadharNo  = trim($req['student_aadhar'] ?? '');
        if (!empty($studentAadharNo) && !$studentAadharDoc) {
            $errors['student_aadhar_doc'] = 'Student Aadhar Card document is required since Student Aadhar number was entered.';
        }

        // Pickup Persons Validation & Processing (Max 3)
        $rawPickups = $req['pickups'] ?? [];
        $processedPickups = [];
        $pickupCount = 0;

        if (is_array($rawPickups)) {
            foreach ($rawPickups as $index => $p) {
                if ($pickupCount >= 3) break;
                $pName = trim($p['name'] ?? '');
                if ($pName === '') continue;

                $pPhoto = $this->processImageUpload("pickup_photo_{$index}", "pickup_photo_cam_{$index}");
                if (!$pPhoto) {
                    $errors["pickup_photo_{$index}"] = "Photo for pickup person '{$pName}' is required.";
                }

                $processedPickups[] = [
                    'name'         => $pName,
                    'relationship' => trim($p['relationship'] ?? ''),
                    'phone'        => trim($p['phone'] ?? ''),
                    'email'        => trim($p['email'] ?? ''),
                    'address'      => trim($p['address'] ?? ''),
                    'is_emergency' => !empty($p['is_emergency']) ? 1 : 0,
                    'photo'        => $pPhoto
                ];
                $pickupCount++;
            }
        }

        if (!in_array($req['gender'] ?? '', ['male', 'female', 'other'], true)) {
            $errors['gender'] = 'Please select a valid gender.';
        }
        if (!empty($this->uploadErrors)) {
            $errors = array_merge($errors, $this->uploadErrors);
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            // never echo back base64 camera payloads / tokens as "old" input
            Session::flash('old', array_filter($req, fn($v, $k) => is_string($v) && !str_ends_with((string)$k, '_cam') && $k !== '_csrf', ARRAY_FILTER_USE_BOTH));
            return $this->redirect(url('forms/student-enrollment'));
        }

        // 2. Generate Unique Application Code (e.g. APP-2026-X89A)
        $appCode = 'APP-' . date('Y') . '-' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 6));

        // 3. Save into `online_enrollments` staging table
        $db = Application::$app->db;
        $db->query("INSERT INTO online_enrollments (
            application_code, student_full_name, dob, gender, student_aadhar, address, student_photo, student_aadhar_doc,
            father_name, father_phone, father_aadhar, father_photo, father_aadhar_doc,
            mother_name, mother_phone, mother_aadhar, mother_photo, mother_aadhar_doc,
            pickup_persons_json, status, created_at
        ) VALUES (
            :code, :sname, :dob, :gender, :saadhar, :address, :sphoto, :sadoc,
            :fname, :fphone, :faadhar, :fphoto, :fadoc,
            :mname, :mphone, :maadhar, :mphoto, :madoc,
            :pickups, 'pending', NOW()
        )", [
            'code'     => $appCode,
            'sname'    => trim($req['student_full_name']),
            'dob'      => $req['dob'],
            'gender'   => $req['gender'],
            'saadhar'  => trim($req['student_aadhar'] ?? ''),
            'address'  => trim($req['address'] ?? ''),
            'sphoto'   => $studentPhoto,
            'sadoc'    => $studentAadharDoc,
            'fname'    => trim($req['father_name']),
            'fphone'   => trim($req['father_phone']),
            'faadhar'  => trim($req['father_aadhar']),
            'fphoto'   => $fatherPhoto,
            'fadoc'    => $fatherAadharDoc,
            'mname'    => trim($req['mother_name']),
            'mphone'   => trim($req['mother_phone']),
            'maadhar'  => trim($req['mother_aadhar']),
            'mphoto'   => $motherPhoto,
            'madoc'    => $motherAadharDoc,
            'pickups'  => json_encode($processedPickups)
        ]);

        Session::flash('app_code', $appCode);
        return $this->redirect(url('forms/student-enrollment/success'));
    }

    public function success(): string
    {
        $appCode = Session::getFlash('app_code');
        if (!$appCode) {
            return $this->redirect(url('forms/student-enrollment'));
        }
        return $this->render('public/enrollment_success', [
            'appCode' => $appCode,
        ]);
    }

    /** Field => message for rejected uploads (wrong type, bad content, too large). */
    private array $uploadErrors = [];

    private function uploadDir(): string
    {
        $uploadDir = ROOT_PATH . '/public/uploads/enrollments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        return $uploadDir;
    }

    /** Uploaded image file, or a webcam capture (base64 data URL). Returns the public path or null. */
    private function processImageUpload(string $fileKey, string $camBase64Key): ?string
    {
        if (isset($_FILES[$fileKey]) && ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $ext = UploadGuard::check($_FILES[$fileKey], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024);
                $filename = UploadGuard::storedName($ext, 'img_');
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $this->uploadDir() . $filename)) {
                    return '/uploads/enrollments/' . $filename;
                }
            } catch (\RuntimeException $e) {
                $this->uploadErrors[$fileKey] = ucfirst(str_replace('_', ' ', $fileKey)) . ': ' . $e->getMessage();
                return null;
            }
        }

        return $this->processCameraImage($camBase64Key);
    }

    /** Aadhar document: uploaded PDF/image, or a webcam capture. */
    private function processFileUpload(string $fileKey): ?string
    {
        if (isset($_FILES[$fileKey]) && ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $ext = UploadGuard::check($_FILES[$fileKey], ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
                $filename = UploadGuard::storedName($ext, 'doc_');
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $this->uploadDir() . $filename)) {
                    return '/uploads/enrollments/' . $filename;
                }
            } catch (\RuntimeException $e) {
                $this->uploadErrors[$fileKey] = ucfirst(str_replace('_', ' ', $fileKey)) . ': ' . $e->getMessage();
                return null;
            }
        }

        return $this->processCameraImage($fileKey . '_cam');
    }

    /** Decode and verify a base64 webcam capture; only real JPEG/PNG/WEBP images are stored. */
    private function processCameraImage(string $camBase64Key): ?string
    {
        $base64 = (string)($_POST[$camBase64Key] ?? '');
        if ($base64 === '' || !preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64)) {
            return null;
        }
        $data = base64_decode(substr($base64, strpos($base64, ',') + 1), true);
        if ($data === false || strlen($data) === 0 || strlen($data) > 5 * 1024 * 1024) {
            return null;
        }
        $info = @getimagesizefromstring($data);
        $map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if ($info === false || !isset($map[$info[2]])) {
            return null;
        }
        $filename = UploadGuard::storedName($map[$info[2]], 'cam_');
        if (file_put_contents($this->uploadDir() . $filename, $data) === false) {
            return null;
        }
        return '/uploads/enrollments/' . $filename;
    }
}