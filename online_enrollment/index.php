<?php
session_start();

$config = require __DIR__ . '/config.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Ensure table exists automatically
$pdo->exec("CREATE TABLE IF NOT EXISTS `online_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_code` VARCHAR(50) NOT NULL UNIQUE,
    `student_full_name` VARCHAR(255) NOT NULL,
    `dob` DATE NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `student_aadhar` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `student_photo` VARCHAR(255) DEFAULT NULL,
    `student_aadhar_doc` VARCHAR(255) DEFAULT NULL,

    `father_name` VARCHAR(255) DEFAULT NULL,
    `father_phone` VARCHAR(30) DEFAULT NULL,
    `father_aadhar` VARCHAR(20) DEFAULT NULL,
    `father_photo` VARCHAR(255) DEFAULT NULL,
    `father_aadhar_doc` VARCHAR(255) DEFAULT NULL,

    `mother_name` VARCHAR(255) DEFAULT NULL,
    `mother_phone` VARCHAR(30) DEFAULT NULL,
    `mother_aadhar` VARCHAR(20) DEFAULT NULL,
    `mother_photo` VARCHAR(255) DEFAULT NULL,
    `mother_aadhar_doc` VARCHAR(255) DEFAULT NULL,

    `pickup_persons_json` LONGTEXT DEFAULT NULL,

    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `processed_by` INT DEFAULT NULL,
    `processed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`application_code`),
    INDEX (`status`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

function processUpload($fileKey, $camBase64Key = '') {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 1. File Upload
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
            $filename = uniqid('up_') . '.' . $ext;
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $filename)) {
                return 'uploads/' . $filename;
            }
        }
    }

    // 2. Base64 Camera Capture
    if (!empty($camBase64Key)) {
        $base64 = $_POST[$camBase64Key] ?? '';
        if (!empty($base64) && preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $data = base64_decode($data);
            if ($data !== false) {
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $filename = uniqid('cam_') . '.' . $ext;
                file_put_contents($uploadDir . $filename, $data);
                return 'uploads/' . $filename;
            }
        }
    }

    return null;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    // Validation
    $sName = trim($_POST['student_full_name'] ?? '');
    if (empty($sName)) {
        $errors['student_full_name'] = "Student full name is required.";
    } elseif (strlen($sName) < 3) {
        $errors['student_full_name'] = "Student name must be at least 3 characters.";
    }

    if (empty($_POST['dob'] ?? '')) {
        $errors['dob'] = "Date of birth is required.";
    } elseif (strtotime($_POST['dob']) > time()) {
        $errors['dob'] = "Date of birth cannot be in the future.";
    }

    if (empty($_POST['gender'] ?? '')) $errors['gender'] = "Gender is required.";

    // Student Aadhar Optional Check
    $sAadhar = preg_replace('/\D/', '', $_POST['student_aadhar'] ?? '');
    if (!empty($_POST['student_aadhar']) && strlen($sAadhar) !== 12) {
        $errors['student_aadhar'] = "Student Aadhar number must be exactly 12 digits.";
    }

    // Father Validation
    $fName = trim($_POST['father_name'] ?? '');
    if (empty($fName)) $errors['father_name'] = "Father's name is required.";

    $fPhone = preg_replace('/\D/', '', $_POST['father_phone'] ?? '');
    if (empty($fPhone)) {
        $errors['father_phone'] = "Father's phone number is required.";
    } elseif (strlen($fPhone) < 10 || strlen($fPhone) > 12) {
        $errors['father_phone'] = "Father's phone number must be a valid 10-digit number.";
    }

    $fAadhar = preg_replace('/\D/', '', $_POST['father_aadhar'] ?? '');
    if (empty($fAadhar)) {
        $errors['father_aadhar'] = "Father's Aadhar number is required.";
    } elseif (strlen($fAadhar) !== 12) {
        $errors['father_aadhar'] = "Father's Aadhar number must be exactly 12 digits.";
    }

    // Mother Validation
    $mName = trim($_POST['mother_name'] ?? '');
    if (empty($mName)) $errors['mother_name'] = "Mother's name is required.";

    $mPhone = preg_replace('/\D/', '', $_POST['mother_phone'] ?? '');
    if (empty($mPhone)) {
        $errors['mother_phone'] = "Mother's phone number is required.";
    } elseif (strlen($mPhone) < 10 || strlen($mPhone) > 12) {
        $errors['mother_phone'] = "Mother's phone number must be a valid 10-digit number.";
    }

    $mAadhar = preg_replace('/\D/', '', $_POST['mother_aadhar'] ?? '');
    if (empty($mAadhar)) {
        $errors['mother_aadhar'] = "Mother's Aadhar number is required.";
    } elseif (strlen($mAadhar) !== 12) {
        $errors['mother_aadhar'] = "Mother's Aadhar number must be exactly 12 digits.";
    }

    // Student Photos & Document
    $studentPhoto = processUpload('student_photo', 'student_photo_cam');
    if (!$studentPhoto) $errors['student_photo'] = "Student passport size photo is required.";

    $studentAadharDoc = processUpload('student_aadhar_doc', 'student_aadhar_doc_cam');
    $studentAadharNo = trim($_POST['student_aadhar'] ?? '');
    if (!empty($studentAadharNo) && !$studentAadharDoc) {
        $errors['student_aadhar_doc'] = "Student Aadhar Card document is required since Student Aadhar number was entered.";
    }

    // Father Photos & Document
    $fatherPhoto = processUpload('father_photo', 'father_photo_cam');
    if (!$fatherPhoto) $errors['father_photo'] = "Father's passport size photo is required.";

    $fatherAadharDoc = processUpload('father_aadhar_doc', 'father_aadhar_doc_cam');
    if (!$fatherAadharDoc) $errors['father_aadhar_doc'] = "Father's Aadhar card document is required.";

    // Mother Photos & Document
    $motherPhoto = processUpload('mother_photo', 'mother_photo_cam');
    if (!$motherPhoto) $errors['mother_photo'] = "Mother's passport size photo is required.";

    $motherAadharDoc = processUpload('mother_aadhar_doc', 'mother_aadhar_doc_cam');
    if (!$motherAadharDoc) $errors['mother_aadhar_doc'] = "Mother's Aadhar card document is required.";

    // Pickup Persons (Max 3)
    $rawPickups = $_POST['pickups'] ?? [];
    $processedPickups = [];
    $count = 0;
    if (is_array($rawPickups)) {
        foreach ($rawPickups as $idx => $p) {
            if ($count >= 3) break;
            $name = trim($p['name'] ?? '');
            if ($name === '') continue;

            $pPhoto = processUpload("pickup_photo_{$idx}", "pickup_photo_cam_{$idx}");
            if (!$pPhoto) {
                $errors["pickup_photo_{$idx}"] = "Photo for pickup person '{$name}' is required.";
            }

            $processedPickups[] = [
                'name'         => $name,
                'relationship' => trim($p['relationship'] ?? ''),
                'phone'        => trim($p['phone'] ?? ''),
                'email'        => trim($p['email'] ?? ''),
                'address'      => trim($p['address'] ?? ''),
                'is_emergency' => !empty($p['is_emergency']) ? 1 : 0,
                'photo'        => $pPhoto
            ];
            $count++;
        }
    }

    if (empty($errors)) {
        $appCode = 'APP-' . date('Y') . '-' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 6));

        $stmt = $pdo->prepare("INSERT INTO online_enrollments (
            application_code, student_full_name, dob, gender, student_aadhar, address, student_photo, student_aadhar_doc,
            father_name, father_phone, father_aadhar, father_photo, father_aadhar_doc,
            mother_name, mother_phone, mother_aadhar, mother_photo, mother_aadhar_doc,
            pickup_persons_json, status, created_at
        ) VALUES (
            :code, :sname, :dob, :gender, :saadhar, :address, :sphoto, :sadoc,
            :fname, :fphone, :faadhar, :fphoto, :fadoc,
            :mname, :mphone, :maadhar, :mphoto, :madoc,
            :pickups, 'pending', NOW()
        )");

        $stmt->execute([
            ':code'    => $appCode,
            ':sname'   => trim($_POST['student_full_name']),
            ':dob'     => $_POST['dob'],
            ':gender'  => $_POST['gender'],
            ':saadhar' => trim($_POST['student_aadhar'] ?? ''),
            ':address' => trim($_POST['address'] ?? ''),
            ':sphoto'  => $studentPhoto,
            ':sadoc'   => $studentAadharDoc,
            ':fname'   => trim($_POST['father_name']),
            ':fphone'  => trim($_POST['father_phone']),
            ':faadhar' => trim($_POST['father_aadhar']),
            ':fphoto'  => $fatherPhoto,
            ':fadoc'   => $fatherAadharDoc,
            ':mname'   => trim($_POST['mother_name']),
            ':mphone'  => trim($_POST['mother_phone']),
            ':maadhar' => trim($_POST['mother_aadhar']),
            ':mphoto'  => $motherPhoto,
            ':madoc'   => $motherAadharDoc,
            ':pickups' => json_encode($processedPickups)
        ]);

        header("Location: success.php?code=" . urlencode($appCode));
        exit;
    }
}
function e($val) { return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .white-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
    </style>
</head>
<body class="min-h-screen py-10 px-4 sm:px-6">

    <div x-data="enrollmentForm()" class="max-w-4xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="text-center space-y-3 relative">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 shadow-sm mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Student Online Enrollment</h1>
            <p class="text-slate-500 text-sm max-w-xl mx-auto">Please fill in student, parent, and authorized pickup details carefully. Photos and documents can be taken live via camera or uploaded.</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Please correct the following highlighted errors:
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs text-red-600 pl-2">
                <?php foreach ($errors as $field => $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Progress Indicator -->
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-4">
            <template x-for="(sName, idx) in ['1. Student Info', '2. Parents Info', '3. Pickup Persons']" :key="idx">
                <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(idx)">
                    <div :class="step >= idx ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-200 text-slate-500'" class="w-8 h-8 rounded-xl flex items-center justify-center text-xs transition-all">
                        <span x-text="idx + 1"></span>
                    </div>
                    <span :class="step >= idx ? 'text-slate-900 font-semibold' : 'text-slate-400'" class="text-xs hidden md:inline" x-text="sName"></span>
                </div>
            </template>
        </div>

        <form method="POST" action="index.php" enctype="multipart/form-data" class="space-y-6">
            
            <!-- SECTION 1: STUDENT INFO -->
            <div x-show="step === 0" class="white-card rounded-3xl p-6 sm:p-8 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Student Personal Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="sm:col-span-3 space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Student Full Name (First, Middle, Last) <span class="text-red-500">*</span></label>
                        <input type="text" name="student_full_name" required value="<?= e($old['student_full_name'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-3 px-4 text-sm focus:border-indigo-600 focus:bg-white outline-none" placeholder="e.g. Rahul Ramesh Patel">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Date of Birth <span class="text-red-500">*</span></label>
                        <input type="date" name="dob" required value="<?= e($old['dob'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-3 px-4 text-sm focus:border-indigo-600 focus:bg-white outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-3 px-4 text-sm focus:border-indigo-600 focus:bg-white outline-none">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Student Aadhar Card No. <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="text" name="student_aadhar" value="<?= e($old['student_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar Number" pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-3 px-4 text-sm focus:border-indigo-600 focus:bg-white outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">Full Residential Address</label>
                    <textarea name="address" rows="3" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-3 px-4 text-sm focus:border-indigo-600 focus:bg-white outline-none" placeholder="House No, Street, City, Pincode..."><?= e($old['address'] ?? '') ?></textarea>
                </div>

                <!-- Student Photo & Aadhar Document with Camera Capture -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-200 pt-5">
                    <!-- Student Photo -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Student Passport Photo <span class="text-red-500">*</span></label>
                            <input type="file" name="student_photo" accept="image/*" @change="previewImage($event, 'student_prev', 'student_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="mt-2">
                                <button type="button" @click="openModal('student_photo_cam', 'student_prev', 'student_placeholder', 'Student Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-xl border border-slate-300 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <input type="hidden" name="student_photo_cam" id="student_photo_cam">
                            </div>
                        </div>

                        <div class="flex items-center justify-center pt-2">
                            <div class="w-28 h-32 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                <img id="student_prev" class="hidden w-full h-full object-cover">
                                <div id="student_placeholder" class="text-slate-400 text-center p-2">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-[10px]">Photo Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Aadhar Document with Camera Capture -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Student Aadhar Document</label>
                            <p class="text-[11px] text-slate-500 mb-1.5">Required if Student Aadhar No. is entered above</p>
                            <input type="file" name="student_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'student_doc_prev', 'student_doc_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="mt-2">
                                <button type="button" @click="openModal('student_aadhar_doc_cam', 'student_doc_prev', 'student_doc_placeholder', 'Student Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-xl border border-slate-300 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <input type="hidden" name="student_aadhar_doc_cam" id="student_aadhar_doc_cam">
                            </div>
                        </div>

                        <div class="flex items-center justify-center pt-2">
                            <div class="w-36 h-24 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                <img id="student_doc_prev" class="hidden w-full h-full object-cover">
                                <div id="student_doc_placeholder" class="text-slate-400 text-center p-2">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                    <span class="text-[10px]">Document Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="goToStep(1)" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-2xl transition-all shadow-md shadow-indigo-600/20">Next: Parents Info →</button>
                </div>
            </div>

            <!-- SECTION 2: PARENTS INFO -->
            <div x-show="step === 1" class="white-card rounded-3xl p-6 sm:p-8 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Parents Information & Document Uploads
                </h3>

                <!-- Father Details -->
                <div class="space-y-4 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Father Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Father's Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="father_name" required value="<?= e($old['father_name'] ?? '') ?>" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Father Mobile Phone <span class="text-red-500">*</span></label>
                            <input type="tel" name="father_phone" required value="<?= e($old['father_phone'] ?? '') ?>" placeholder="10 Digit Mobile No." pattern="[0-9]{10}" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Father Aadhar Card No. <span class="text-red-500">*</span></label>
                            <input type="text" name="father_aadhar" required value="<?= e($old['father_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar No." pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start pt-2 border-t border-slate-200">
                        <!-- Father Photo -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Father Passport Photo <span class="text-red-500">*</span></label>
                            <input type="file" name="father_photo" accept="image/*" @change="previewImage($event, 'father_prev', 'father_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('father_photo_cam', 'father_prev', 'father_placeholder', 'Father Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-lg border border-slate-300 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <div class="w-20 h-24 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                    <img id="father_prev" class="hidden w-full h-full object-cover">
                                    <div id="father_placeholder" class="text-slate-400 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[9px]">Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="father_photo_cam" id="father_photo_cam">
                        </div>

                        <!-- Father Aadhar Doc -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Father Aadhar Document <span class="text-red-500">*</span></label>
                            <input type="file" name="father_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'father_doc_prev', 'father_doc_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('father_aadhar_doc_cam', 'father_doc_prev', 'father_doc_placeholder', 'Father Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-lg border border-slate-300 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <div class="w-28 h-20 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                    <img id="father_doc_prev" class="hidden w-full h-full object-cover">
                                    <div id="father_doc_placeholder" class="text-slate-400 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]">Doc Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="father_aadhar_doc_cam" id="father_aadhar_doc_cam">
                        </div>
                    </div>
                </div>

                <!-- Mother Details -->
                <div class="space-y-4 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <h4 class="text-xs font-bold text-pink-600 uppercase tracking-wider">Mother Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Mother's Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="mother_name" required value="<?= e($old['mother_name'] ?? '') ?>" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Mother Mobile Phone <span class="text-red-500">*</span></label>
                            <input type="tel" name="mother_phone" required value="<?= e($old['mother_phone'] ?? '') ?>" placeholder="10 Digit Mobile No." pattern="[0-9]{10}" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-700">Mother Aadhar Card No. <span class="text-red-500">*</span></label>
                            <input type="text" name="mother_aadhar" required value="<?= e($old['mother_aadhar'] ?? '') ?>" placeholder="12 Digit Aadhar No." pattern="[0-9]{12}" minlength="12" maxlength="12" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3 text-sm focus:border-indigo-600 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start pt-2 border-t border-slate-200">
                        <!-- Mother Photo -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mother Passport Photo <span class="text-red-500">*</span></label>
                            <input type="file" name="mother_photo" accept="image/*" @change="previewImage($event, 'mother_prev', 'mother_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('mother_photo_cam', 'mother_prev', 'mother_placeholder', 'Mother Passport Photo')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-lg border border-slate-300 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                </button>
                                <div class="w-20 h-24 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                    <img id="mother_prev" class="hidden w-full h-full object-cover">
                                    <div id="mother_placeholder" class="text-slate-400 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[9px]">Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="mother_photo_cam" id="mother_photo_cam">
                        </div>

                        <!-- Mother Aadhar Doc -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mother / Guardian Aadhar Document <span class="text-red-500">*</span></label>
                            <input type="file" name="mother_aadhar_doc" accept=".pdf,.jpg,.jpeg,.png" @change="previewImage($event, 'mother_doc_prev', 'mother_doc_placeholder')" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                            <div class="flex items-center gap-3 mt-2">
                                <button type="button" @click="openModal('mother_aadhar_doc_cam', 'mother_doc_prev', 'mother_doc_placeholder', 'Mother Aadhar Document')" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-white hover:bg-slate-100 text-indigo-600 rounded-lg border border-slate-300 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Snap Document
                                </button>
                                <div class="w-28 h-20 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                    <img id="mother_doc_prev" class="hidden w-full h-full object-cover">
                                    <div id="mother_doc_placeholder" class="text-slate-400 text-center p-1">
                                        <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]">Doc Preview</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="mother_aadhar_doc_cam" id="mother_aadhar_doc_cam">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <button type="button" @click="step = 0" class="px-5 py-2.5 border border-slate-300 text-slate-600 hover:text-slate-900 rounded-xl text-sm">← Back</button>
                    <button type="button" @click="goToStep(2)" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-2xl transition-all shadow-md shadow-indigo-600/20">Next: Pickup Persons →</button>
                </div>
            </div>

            <!-- SECTION 3: AUTHORIZED PICKUP PERSONS (MAX 3) -->
            <div x-show="step === 2" class="white-card rounded-3xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Authorized Pickup Persons (Max 3)
                    </h3>
                    <button type="button" x-show="pickups.length < 3" @click="addPickup()" class="text-xs font-bold px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-200 hover:bg-indigo-100">
                        + Add Pickup Person
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(p, index) in pickups" :key="index">
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4 relative">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="text-xs font-bold text-indigo-600 uppercase" x-text="'Pickup Person #' + (index + 1)"></span>
                                <button type="button" @click="removePickup(index)" class="text-xs text-red-600 hover:text-red-700">Remove</button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-700">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" :name="'pickups[' + index + '][name]'" required x-model="p.name" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2 px-3 text-sm focus:border-indigo-600 outline-none" placeholder="Full Name">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-700">Relationship to Student</label>
                                    <input type="text" :name="'pickups[' + index + '][relationship]'" x-model="p.relationship" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2 px-3 text-sm focus:border-indigo-600 outline-none" placeholder="e.g. Grandfather, Driver, Uncle">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-slate-700">Phone Number</label>
                                    <input type="tel" :name="'pickups[' + index + '][phone]'" x-model="p.phone" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl py-2 px-3 text-sm focus:border-indigo-600 outline-none" placeholder="+91 XXXXX XXXXX">
                                </div>
                                <div class="space-y-1 flex items-center pt-5">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :name="'pickups[' + index + '][is_emergency]'" x-model="p.is_emergency" value="1" class="w-4 h-4 rounded accent-indigo-600">
                                        <span class="text-xs font-medium text-slate-700">Mark as Emergency Contact</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Pickup Person Photo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center border-t border-slate-200 pt-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Passport Photo <span class="text-red-500">*</span></label>
                                    <input type="file" :name="'pickup_photo_' + index" accept="image/*" @change="previewImage($event, 'pickup_prev_' + index, 'pickup_placeholder_' + index)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 cursor-pointer">
                                    <button type="button" @click="openModal('pickup_photo_cam_' + index, 'pickup_prev_' + index, 'pickup_placeholder_' + index, 'Pickup Person Photo')" class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold px-3 py-1 bg-white text-indigo-600 rounded-lg border border-slate-300 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Open Camera
                                    </button>
                                    <input type="hidden" :name="'pickup_photo_cam_' + index" :id="'pickup_photo_cam_' + index">
                                </div>
                                <div class="flex justify-start">
                                    <div class="w-20 h-24 rounded-xl border border-slate-300 bg-white flex flex-col items-center justify-center overflow-hidden relative shadow-sm">
                                        <img :id="'pickup_prev_' + index" class="hidden w-full h-full object-cover">
                                        <div :id="'pickup_placeholder_' + index" class="text-slate-400 text-center p-1">
                                            <svg class="w-5 h-5 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="text-[9px]">Photo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <button type="button" @click="step = 1" class="px-5 py-2.5 border border-slate-300 text-slate-600 hover:text-slate-900 rounded-xl text-sm">← Back</button>
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-sm rounded-2xl transition-all shadow-lg shadow-indigo-600/30">
                        Submit Enrollment Application
                    </button>
                </div>
            </div>

        </form>

        <!-- LARGE CAMERA CAPTURE MODAL -->
        <div x-show="showCamModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-md p-4" x-cloak>
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full space-y-5 shadow-2xl border border-slate-200 relative">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div class="flex items-center gap-2.5 text-slate-900 font-bold text-lg">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span x-text="modalTitle"></span>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Big Video Container -->
                <div class="w-full h-80 sm:h-96 bg-slate-950 rounded-2xl overflow-hidden relative border-2 border-slate-800 flex items-center justify-center shadow-inner">
                    <video id="modal_video" class="w-full h-full object-cover" autoplay></video>
                    <canvas id="modal_canvas" class="hidden"></canvas>
                </div>

                <!-- Action Controls Bar -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="switchCamera()" class="px-5 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-2xl text-xs font-bold border border-indigo-200 inline-flex items-center gap-1.5 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Switch Camera</span>
                    </button>

                    <button type="button" @click="captureFromModal()" class="px-7 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-2xl text-sm font-bold shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Capture & Save Photo
                    </button>
                </div>

            </div>
        </div>

    </div>

    <script>
        function enrollmentForm() {
            return {
                step: 0,
                pickups: [{ name: '', relationship: '', phone: '', email: '', address: '', is_emergency: true }],
                showCamModal: false,
                modalTitle: 'Capture Photo',
                activeInputId: null,
                activeImgId: null,
                activePlaceholderId: null,
                mediaStream: null,

                goToStep(targetStep) {
                    if (targetStep <= this.step) {
                        this.step = targetStep;
                        return;
                    }

                    // Validate Current Step 0 (Student Info)
                    if (this.step === 0) {
                        const form = document.querySelector('form');
                        const sName = form.querySelector('[name="student_full_name"]');
                        const dob = form.querySelector('[name="dob"]');
                        const gender = form.querySelector('[name="gender"]');
                        const sAadhar = form.querySelector('[name="student_aadhar"]');
                        const sPhoto = form.querySelector('[name="student_photo"]');
                        const sPhotoCam = form.querySelector('[name="student_photo_cam"]');
                        const sDoc = form.querySelector('[name="student_aadhar_doc"]');
                        const sDocCam = form.querySelector('[name="student_aadhar_doc_cam"]');

                        if (!sName.checkValidity()) { sName.reportValidity(); return; }
                        if (!dob.checkValidity()) { dob.reportValidity(); return; }
                        if (!gender.checkValidity()) { gender.reportValidity(); return; }
                        if (sAadhar.value && !sAadhar.checkValidity()) { sAadhar.reportValidity(); return; }

                        if (!sPhoto.files.length && !sPhotoCam.value) {
                            alert("Please upload or capture Student Passport Photo before proceeding.");
                            return;
                        }

                        if (sAadhar.value.trim() !== '' && (!sDoc.files.length && !sDocCam.value)) {
                            alert("Student Aadhar Document is required since Student Aadhar No. was provided.");
                            return;
                        }
                    }

                    // Validate Current Step 1 (Parents Info)
                    if (this.step === 1 && targetStep > 1) {
                        const form = document.querySelector('form');
                        const fName = form.querySelector('[name="father_name"]');
                        const fPhone = form.querySelector('[name="father_phone"]');
                        const fAadhar = form.querySelector('[name="father_aadhar"]');
                        const fPhoto = form.querySelector('[name="father_photo"]');
                        const fPhotoCam = form.querySelector('[name="father_photo_cam"]');
                        const fDoc = form.querySelector('[name="father_aadhar_doc"]');
                        const fDocCam = form.querySelector('[name="father_aadhar_doc_cam"]');

                        const mName = form.querySelector('[name="mother_name"]');
                        const mPhone = form.querySelector('[name="mother_phone"]');
                        const mAadhar = form.querySelector('[name="mother_aadhar"]');
                        const mPhoto = form.querySelector('[name="mother_photo"]');
                        const mPhotoCam = form.querySelector('[name="mother_photo_cam"]');
                        const mDoc = form.querySelector('[name="mother_aadhar_doc"]');
                        const mDocCam = form.querySelector('[name="mother_aadhar_doc_cam"]');

                        if (!fName.checkValidity()) { fName.reportValidity(); return; }
                        if (!fPhone.checkValidity()) { fPhone.reportValidity(); return; }
                        if (!fAadhar.checkValidity()) { fAadhar.reportValidity(); return; }
                        if (!fPhoto.files.length && !fPhotoCam.value) {
                            alert("Please upload or capture Father Passport Photo.");
                            return;
                        }
                        if (!fDoc.files.length && !fDocCam.value) {
                            alert("Please upload or capture Father Aadhar Document.");
                            return;
                        }

                        if (!mName.checkValidity()) { mName.reportValidity(); return; }
                        if (!mPhone.checkValidity()) { mPhone.reportValidity(); return; }
                        if (!mAadhar.checkValidity()) { mAadhar.reportValidity(); return; }
                        if (!mPhoto.files.length && !mPhotoCam.value) {
                            alert("Please upload or capture Mother Passport Photo.");
                            return;
                        }
                        if (!mDoc.files.length && !mDocCam.value) {
                            alert("Please upload or capture Mother Aadhar Document.");
                            return;
                        }
                    }

                    this.step = targetStep;
                },

                addPickup() {
                    if (this.pickups.length < 3) {
                        this.pickups.push({ name: '', relationship: '', phone: '', email: '', address: '', is_emergency: false });
                    }
                },
                removePickup(idx) {
                    this.pickups.splice(idx, 1);
                },
                previewImage(event, targetImgId, placeholderId) {
                    const file = event.target.files[0];
                    if (file) {
                        const img = document.getElementById(targetImgId);
                        const placeholder = document.getElementById(placeholderId);
                        if (file.type === 'application/pdf') {
                            img.classList.add('hidden');
                            if (placeholder) {
                                placeholder.innerHTML = '<svg class="w-8 h-8 mx-auto text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg><span class="text-[10px] text-indigo-600 block font-semibold mt-1">PDF Selected</span>';
                                placeholder.classList.remove('hidden');
                            }
                        } else {
                            img.src = URL.createObjectURL(file);
                            img.classList.remove('hidden');
                            if (placeholder) placeholder.classList.add('hidden');
                        }
                    }
                },
                openModal(inputId, imgId, placeholderId, title) {
                    this.activeInputId = inputId;
                    this.activeImgId = imgId;
                    this.activePlaceholderId = placeholderId;
                    this.modalTitle = title || 'Camera Capture';

                    // Default to 'user' for photos, or 'environment' for documents if specified
                    if (title && title.toLowerCase().includes('document')) {
                        this.facingMode = 'environment';
                    } else {
                        this.facingMode = 'user';
                    }

                    this.startCamera();
                },
                async startCamera() {
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => track.stop());
                        this.mediaStream = null;
                    }

                    try {
                        let stream = null;
                        const devices = await navigator.mediaDevices.enumerateDevices().catch(() => []);
                        const videoDevices = devices.filter(d => d.kind === 'videoinput');

                        if (videoDevices.length > 1) {
                            // Find target camera by facingMode or label keyword
                            let targetDevice = videoDevices.find(d => {
                                const label = d.label.toLowerCase();
                                return this.facingMode === 'user'
                                    ? (label.includes('front') || label.includes('user') || label.includes('facing front'))
                                    : (label.includes('back') || label.includes('rear') || label.includes('environment'));
                            });

                            if (targetDevice) {
                                stream = await navigator.mediaDevices.getUserMedia({
                                    video: { deviceId: { exact: targetDevice.deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
                                }).catch(() => null);
                            }
                        }

                        if (!stream) {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: { exact: this.facingMode }, width: { ideal: 1280 }, height: { ideal: 720 } }
                            }).catch(() => null);
                        }

                        if (!stream) {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: this.facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }
                            });
                        }

                        this.mediaStream = stream;
                        const video = document.getElementById('modal_video');
                        if (video) {
                            video.srcObject = stream;
                            video.play().catch(() => {});
                        }
                        this.showCamModal = true;
                    } catch (err) {
                        alert("Unable to access camera: " + err.message);
                    }
                },
                switchCamera() {
                    this.facingMode = (this.facingMode === 'user') ? 'environment' : 'user';
                    this.startCamera();
                },
                closeModal() {
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => track.stop());
                        this.mediaStream = null;
                    }
                    this.showCamModal = false;
                },
                captureFromModal() {
                    const video = document.getElementById('modal_video');
                    const canvas = document.getElementById('modal_canvas');
                    canvas.width = video.videoWidth || 1280;
                    canvas.height = video.videoHeight || 720;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg');
                    
                    // Save base64 to target hidden input
                    const hiddenInput = document.getElementById(this.activeInputId);
                    if (hiddenInput) hiddenInput.value = dataUrl;

                    // Update target preview image
                    const img = document.getElementById(this.activeImgId);
                    if (img) {
                        img.src = dataUrl;
                        img.classList.remove('hidden');
                    }

                    // Hide placeholder
                    const placeholder = document.getElementById(this.activePlaceholderId);
                    if (placeholder) placeholder.classList.add('hidden');

                    this.closeModal();
                }
            }
        }
    </script>
</body>
</html>
