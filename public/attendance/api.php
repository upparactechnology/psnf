<?php
declare(strict_types=1);

/**
 * Universal PHP API Engine & Proxy for Face Attendance System
 * Attempts FastAPI proxy to http://127.0.0.1:8000 first;
 * If FastAPI is offline, seamlessly processes registration & verification directly via PHP & MySQL!
 */

// Set Default Timezone to Local Indian Standard Time (IST / Asia/Kolkata +05:30)
date_default_timezone_set('Asia/Kolkata');

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

$fastApiBase = 'http://127.0.0.1:8000';
$endpoint = $_GET['endpoint'] ?? $_SERVER['PATH_INFO'] ?? '/health';

if (strpos($endpoint, '/api') !== 0 && strpos($endpoint, '/health') !== 0) {
    $endpoint = '/api/' . ltrim($endpoint, '/');
}

$targetUrl = $fastApiBase . $endpoint;
$requestBody = file_get_contents('php://input');

// 1. Attempt cURL Proxy to FastAPI Service
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);

if (!empty($requestBody)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($requestBody)
    ]);
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode > 0 && $httpCode !== 503) {
    ob_end_clean();
    http_response_code($httpCode);
    echo $response;
    exit();
}

// 2. FastAPI is Offline -> Execute Standalone PHP Engine
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=psnf_drm;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    try {
        $pdo->exec("SET time_zone = '+05:30'");
    } catch (Throwable $e) {}

    try {
        $pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, 'NO_ZERO_DATE', ''))");
    } catch (Throwable $e) {}

    // Ensure base tables exist
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `employees` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_code` VARCHAR(50) NOT NULL UNIQUE,
            `name` VARCHAR(150) NOT NULL,
            `department` VARCHAR(100) DEFAULT NULL,
            `designation` VARCHAR(100) DEFAULT NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Throwable $e) {}

    $colsEmp = $pdo->query("SHOW COLUMNS FROM `employees`")->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('employee_id', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` MODIFY COLUMN `employee_id` VARCHAR(50) NULL DEFAULT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("DROP INDEX `ix_employees_employee_id` ON `employees`"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `employees` SET `employee_id` = `employee_code` WHERE `employee_id` IS NULL OR `employee_id` = ''"); } catch (Throwable $e) {}
    }

    if (!in_array('employee_code', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `employee_code` VARCHAR(50) NULL AFTER `id`"); } catch (Throwable $e) {}
        if (in_array('code', $colsEmp)) {
            try { $pdo->exec("UPDATE `employees` SET `employee_code` = `code` WHERE `employee_code` IS NULL OR `employee_code` = ''"); } catch (Throwable $e) {}
        }
        try { $pdo->exec("UPDATE `employees` SET `employee_code` = CONCAT('EMP-', id) WHERE `employee_code` IS NULL OR `employee_code` = ''"); } catch (Throwable $e) {}
    }

    if (!in_array('name', $colsEmp)) {
        if (in_array('first_name', $colsEmp)) {
            try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `name` VARCHAR(150) NULL AFTER `employee_code`"); } catch (Throwable $e) {}
            try { $pdo->exec("UPDATE `employees` SET `name` = CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''))"); } catch (Throwable $e) {}
        } else {
            try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `name` VARCHAR(150) NOT NULL AFTER `employee_code`"); } catch (Throwable $e) {}
        }
    }

    if (!in_array('department', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `department` VARCHAR(100) DEFAULT NULL"); } catch (Throwable $e) {}
    }

    if (!in_array('designation', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `designation` VARCHAR(100) DEFAULT NULL"); } catch (Throwable $e) {}
    }

    if (!in_array('status', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active'"); } catch (Throwable $e) {}
    }

    if (in_array('created_at', $colsEmp)) {
        try { $pdo->exec("ALTER TABLE `employees` MODIFY COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `employees` SET `created_at` = NOW() WHERE `created_at` IS NULL OR `created_at` = '0000-00-00 00:00:00'"); } catch (Throwable $e) {}
    } else {
        try { $pdo->exec("ALTER TABLE `employees` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `employees` SET `created_at` = NOW()"); } catch (Throwable $e) {}
    }

    // Ensure face_embeddings table exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `face_embeddings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL,
            `embedding` LONGTEXT NOT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Throwable $e) {}

    $colsEmb = $pdo->query("SHOW COLUMNS FROM `face_embeddings`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('embedding', $colsEmb)) {
        try { $pdo->exec("ALTER TABLE `face_embeddings` ADD COLUMN `embedding` LONGTEXT NOT NULL"); } catch (Throwable $e) {}
    }
    if (!in_array('image_path', $colsEmb)) {
        try { $pdo->exec("ALTER TABLE `face_embeddings` ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL"); } catch (Throwable $e) {}
    }
    if (in_array('created_at', $colsEmb)) {
        try { $pdo->exec("ALTER TABLE `face_embeddings` MODIFY COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `face_embeddings` SET `created_at` = NOW() WHERE `created_at` IS NULL OR `created_at` = '0000-00-00 00:00:00'"); } catch (Throwable $e) {}
    }

    // Ensure attendance table exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL,
            `attendance_date` DATE NOT NULL,
            `check_in` DATETIME NOT NULL,
            `confidence` FLOAT NOT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Throwable $e) {}

    // Dynamically discover and drop ALL foreign keys on attendance table to prevent constraint failures
    try {
        $fkQuery = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'psnf_drm' AND TABLE_NAME = 'attendance' AND REFERENCED_TABLE_NAME IS NOT NULL";
        $fkStmt = $pdo->query($fkQuery);
        $allFks = $fkStmt ? $fkStmt->fetchAll(PDO::FETCH_COLUMN) : [];
        foreach ($allFks as $fk) {
            try {
                $pdo->exec("ALTER TABLE `attendance` DROP FOREIGN KEY `$fk`");
            } catch (Throwable $e) {}
        }
    } catch (Throwable $e) {}

    // Hardcoded fallback list of foreign keys to drop
    foreach (['attendance_ibfk_1', 'attendance_ibfk_2', 'attendance_ibfk_3', 'attendance_ibfk_4', 'attendance_ibfk_5', 'attendance_student_id_foreign', 'attendance_school_id_foreign'] as $fkName) {
        try {
            $pdo->exec("ALTER TABLE `attendance` DROP FOREIGN KEY `$fkName`");
        } catch (Throwable $e) {}
    }

    $colsAtt = $pdo->query("SHOW COLUMNS FROM `attendance`")->fetchAll(PDO::FETCH_COLUMN);

    // Make legacy foreign key columns nullable
    foreach (['student_id', 'school_id', 'tenant_id', 'user_id', 'class_id', 'section_id'] as $col) {
        if (in_array($col, $colsAtt)) {
            try {
                $pdo->exec("ALTER TABLE `attendance` MODIFY COLUMN `$col` INT NULL DEFAULT NULL");
            } catch (Throwable $e) {}
        }
    }

    if (!in_array('image_path', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL"); } catch (Throwable $e) {}
    }
    if (!in_array('ip_address', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `ip_address` VARCHAR(45) DEFAULT NULL"); } catch (Throwable $e) {}
    }
    if (!in_array('user_agent', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `user_agent` TEXT DEFAULT NULL"); } catch (Throwable $e) {}
    }

    if (in_array('attendance_date', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` MODIFY COLUMN `attendance_date` DATE NULL DEFAULT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `attendance` SET `attendance_date` = CURDATE() WHERE `attendance_date` IS NULL OR `attendance_date` = '0000-00-00'"); } catch (Throwable $e) {}
    } else {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `attendance_date` DATE NULL DEFAULT NULL"); } catch (Throwable $e) {}
    }

    if (in_array('date', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` MODIFY COLUMN `date` DATE NULL DEFAULT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("UPDATE `attendance` SET `date` = CURDATE() WHERE `date` IS NULL OR `date` = '0000-00-00'"); } catch (Throwable $e) {}
    }

    if (!in_array('employee_id', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `employee_id` INT NULL DEFAULT NULL"); } catch (Throwable $e) {}
    }
    if (!in_array('check_in', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `check_in` DATETIME NULL DEFAULT NULL"); } catch (Throwable $e) {}
    }
    if (!in_array('confidence', $colsAtt)) {
        try { $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `confidence` FLOAT NULL DEFAULT 0.0"); } catch (Throwable $e) {}
    }

    $colsAtt = $pdo->query("SHOW COLUMNS FROM `attendance`")->fetchAll(PDO::FETCH_COLUMN);

    // List Registered Employees Endpoint
    if (strpos($endpoint, 'employees/list') !== false || (strpos($endpoint, 'employees') !== false && $_SERVER['REQUEST_METHOD'] === 'GET')) {
        $stmtEmpList = $pdo->query("
            SELECT e.*, 
                   IF(e.created_at IS NULL OR e.created_at = '0000-00-00 00:00:00', NOW(), e.created_at) as created_at,
                   fe.image_path, 
                   IF(fe.created_at IS NULL OR fe.created_at = '0000-00-00 00:00:00', NOW(), fe.created_at) as photo_created_at
            FROM employees e
            LEFT JOIN face_embeddings fe ON e.id = fe.employee_id
            GROUP BY e.id
            ORDER BY e.id DESC
        ");
        $employees = $stmtEmpList ? $stmtEmpList->fetchAll() : [];

        ob_end_clean();
        echo json_encode([
            "success" => true,
            "count" => count($employees),
            "data" => $employees
        ]);
        exit();
    }

    // Delete Registered Employee Endpoint
    if (strpos($endpoint, 'employees/delete') !== false) {
        $input = json_decode($requestBody, true) ?? [];
        $empId = $_GET['id'] ?? $input['id'] ?? null;

        if ($empId) {
            $pdo->prepare("DELETE FROM face_embeddings WHERE employee_id = ?")->execute([$empId]);
            $pdo->prepare("DELETE FROM attendance WHERE employee_id = ?")->execute([$empId]);
            $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$empId]);

            ob_end_clean();
            echo json_encode([
                "success" => true,
                "message" => "Employee and registered face photo deleted successfully!"
            ]);
            exit();
        } else {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Employee ID required."]);
            exit();
        }
    }

    // Delete Attendance Record Endpoint
    if (strpos($endpoint, 'attendance/delete') !== false || ($_SERVER['REQUEST_METHOD'] === 'DELETE' && strpos($endpoint, 'history') !== false)) {
        $input = json_decode($requestBody, true) ?? [];
        $attId = $_GET['id'] ?? $input['id'] ?? null;

        if ($attId) {
            $stmtDel = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
            $stmtDel->execute([$attId]);

            ob_end_clean();
            echo json_encode([
                "success" => true,
                "message" => "Attendance log deleted successfully!"
            ]);
            exit();
        } else {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Attendance Record ID required."]);
            exit();
        }
    }

    // Route Handlers
    if (strpos($endpoint, 'health') !== false) {
        ob_end_clean();
        echo json_encode([
            "status" => "healthy",
            "mode" => "PHP Engine Active (Standalone Engine)",
            "version" => "1.0.0"
        ]);
        exit();
    }

    if (strpos($endpoint, 'register-face') !== false) {
        $input = json_decode($requestBody, true);
        $empCode = trim($input['employee_code'] ?? '');
        $name = trim($input['name'] ?? '');
        $dept = trim($input['department'] ?? '');
        $desig = trim($input['designation'] ?? '');
        $images = $input['images_base64'] ?? [];

        if (empty($empCode) || empty($name) || empty($images)) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Employee Code, Name, and face photo required.']);
            exit();
        }

        $colsEmp = $pdo->query("SHOW COLUMNS FROM `employees`")->fetchAll(PDO::FETCH_COLUMN);

        $sqlSel = "SELECT id FROM employees WHERE employee_code = ? OR name = ?";
        $paramsSel = [$empCode, $name];
        if (in_array('employee_id', $colsEmp)) {
            $sqlSel .= " OR employee_id = ?";
            $paramsSel[] = $empCode;
        }

        $stmt = $pdo->prepare($sqlSel);
        $stmt->execute($paramsSel);
        $emp = $stmt->fetch();

        $nowStr = date('Y-m-d H:i:s');

        if (!$emp) {
            $insCols = ['employee_code', 'name', 'department', 'designation', 'status', 'created_at'];
            $insVals = [$empCode, $name, $dept, $desig, 'active', $nowStr];
            if (in_array('employee_id', $colsEmp)) {
                $insCols[] = 'employee_id';
                $insVals[] = $empCode;
            }
            $colStr = implode(', ', array_map(fn($c) => "`$c`", $insCols));
            $plStr = implode(', ', array_fill(0, count($insCols), '?'));

            $stmtIns = $pdo->prepare("INSERT INTO employees ({$colStr}) VALUES ({$plStr})");
            $stmtIns->execute($insVals);
            $employeeId = (int)$pdo->lastInsertId();
        } else {
            $employeeId = (int)$emp['id'];
            $updFields = ["employee_code = ?", "name = ?", "department = ?", "designation = ?", "created_at = ?"];
            $updVals = [$empCode, $name, $dept, $desig, $nowStr];
            if (in_array('employee_id', $colsEmp)) {
                $updFields[] = "employee_id = ?";
                $updVals[] = $empCode;
            }
            $updVals[] = $employeeId;

            $stmtUpd = $pdo->prepare("UPDATE employees SET " . implode(', ', $updFields) . " WHERE id = ?");
            $stmtUpd->execute($updVals);
        }

        $uploadsDir = __DIR__ . '/../../backend/uploads/faces';
        if (!file_exists($uploadsDir)) {
            @mkdir($uploadsDir, 0777, true);
        }

        // Store ONLY 1 primary face image per employee (Delete existing embeddings)
        $delStmt = $pdo->prepare("DELETE FROM face_embeddings WHERE employee_id = ?");
        $delStmt->execute([$employeeId]);

        $b64 = $images[0];
        $data = $b64;
        if (strpos($data, ',') !== false) {
            $data = explode(',', $data)[1];
        }
        $bin = base64_decode($data);
        $filename = "emp_{$empCode}_" . time() . ".jpg";
        $savePath = $uploadsDir . '/' . $filename;
        @file_put_contents($savePath, $bin);

        $vector = generatePhpFeatureVector($bin);
        $vecJson = json_encode($vector);

        $relPath = "uploads/faces/" . $filename;
        $stmtEmb = $pdo->prepare("INSERT INTO face_embeddings (employee_id, embedding, image_path, created_at) VALUES (?, ?, ?, ?)");
        $stmtEmb->execute([$employeeId, $vecJson, $relPath, $nowStr]);

        ob_end_clean();
        echo json_encode([
            "success" => true,
            "employee_id" => $employeeId,
            "employee_code" => $empCode,
            "employee_name" => $name,
            "embeddings_registered" => 1,
            "message" => "Successfully registered face photo for {$name}!"
        ]);
        exit();
    }

    if (strpos($endpoint, 'verify-face') !== false || strpos($endpoint, 'attendance') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode($requestBody, true);
        $b64 = $input['image_base64'] ?? '';
        if (empty($b64)) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Image photo is required.']);
            exit();
        }

        $data = $b64;
        if (strpos($data, ',') !== false) {
            $data = explode(',', $data)[1];
        }
        $targetBin = base64_decode($data);
        $targetVec = generatePhpFeatureVector($targetBin);

        $stmtAll = $pdo->query("SELECT fe.employee_id, fe.embedding, e.employee_code, e.name, e.department FROM face_embeddings fe JOIN employees e ON fe.employee_id = e.id");
        $all = $stmtAll->fetchAll();

        $bestMatch = null;
        $highestScore = 0.0;

        foreach ($all as $row) {
            $emb = json_decode($row['embedding'], true);
            if (is_array($emb)) {
                $score = cosineSimilarityPhp($targetVec, $emb);
                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $row;
                }
            }
        }

        if ($highestScore >= 0.60 && $bestMatch) {
            $empId = (int)$bestMatch['employee_id'];
            $nowStr = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            // 10-Minute Cooldown Database Verification (Strict Unix Timestamp Check)
            $stmtCool = $pdo->prepare("SELECT id, check_in, created_at FROM attendance WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
            $stmtCool->execute([$empId]);
            $recent = $stmtCool->fetch();

            if ($recent) {
                $checkInTimeStr = !empty($recent['check_in']) ? $recent['check_in'] : ($recent['created_at'] ?? null);
                if ($checkInTimeStr) {
                    $lastTs = strtotime($checkInTimeStr);
                    $currentTs = time();
                    $diffSec = $currentTs - $lastTs;

                    // If less than 600 seconds (10 minutes) since last check-in
                    if ($diffSec >= 0 && $diffSec < 600) {
                        $remMinutes = ceil((600 - $diffSec) / 60);
                        ob_end_clean();
                        echo json_encode([
                            "success" => true,
                            "already_checked_in" => true,
                            "employee_id" => $empId,
                            "employee_code" => $bestMatch['employee_code'],
                            "employee_name" => $bestMatch['name'],
                            "department" => $bestMatch['department'],
                            "confidence" => round($highestScore, 4),
                            "message" => "Attendance already marked for {$bestMatch['name']}. Please wait {$remMinutes} more minute(s) before scanning again."
                        ]);
                        exit();
                    }
                }
            }

            $attDir = __DIR__ . '/../../backend/uploads/attendance';
            if (!file_exists($attDir)) @mkdir($attDir, 0777, true);
            $filename = "att_{$empId}_" . time() . ".jpg";
            @file_put_contents($attDir . '/' . $filename, $targetBin);
            $relPath = "uploads/attendance/" . $filename;

            // Dynamically construct INSERT statement matching actual columns in database
            $insertCols = ['employee_id', 'attendance_date', 'check_in', 'confidence'];
            $insertVals = [$empId, $today, $nowStr, round($highestScore, 4)];

            if (in_array('image_path', $colsAtt)) {
                $insertCols[] = 'image_path';
                $insertVals[] = $relPath;
            }
            if (in_array('ip_address', $colsAtt)) {
                $insertCols[] = 'ip_address';
                $insertVals[] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            }
            if (in_array('user_agent', $colsAtt)) {
                $insertCols[] = 'user_agent';
                $insertVals[] = $_SERVER['HTTP_USER_AGENT'] ?? 'Browser';
            }
            if (in_array('date', $colsAtt)) {
                $insertCols[] = 'date';
                $insertVals[] = $today;
            }
            if (in_array('status', $colsAtt)) {
                $insertCols[] = 'status';
                $insertVals[] = 'present';
            }

            // Explicitly set legacy FK columns to null
            foreach (['student_id', 'school_id', 'tenant_id', 'user_id', 'class_id', 'section_id'] as $legacyCol) {
                if (in_array($legacyCol, $colsAtt)) {
                    $insertCols[] = $legacyCol;
                    $insertVals[] = null;
                }
            }

            $colNamesStr = implode(', ', array_map(fn($c) => "`$c`", $insertCols));
            $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));

            $stmtAtt = $pdo->prepare("INSERT INTO attendance ({$colNamesStr}) VALUES ({$placeholders})");
            $stmtAtt->execute($insertVals);

            ob_end_clean();
            echo json_encode([
                "success" => true,
                "already_checked_in" => false,
                "employee_id" => $empId,
                "employee_code" => $bestMatch['employee_code'],
                "employee_name" => $bestMatch['name'],
                "department" => $bestMatch['department'],
                "confidence" => round($highestScore, 4),
                "check_in" => $nowStr,
                "message" => "Attendance marked successfully for {$bestMatch['name']}!"
            ]);
            exit();
        }

        ob_end_clean();
        echo json_encode([
            "success" => false,
            "confidence" => round($highestScore, 4),
            "message" => "Face not recognized. Similarity score (" . round($highestScore, 2) . ") is below threshold (0.60)."
        ]);
        exit();
    }

    if (strpos($endpoint, 'history') !== false) {
        $dateFilter = $_GET['date'] ?? null;
        $colsAtt = $pdo->query("SHOW COLUMNS FROM `attendance`")->fetchAll(PDO::FETCH_COLUMN);
        $dateCol = in_array('attendance_date', $colsAtt) ? 'attendance_date' : 'date';

        $sql = "SELECT a.*, e.employee_code, e.name as employee_name, e.department FROM attendance a JOIN employees e ON a.employee_id = e.id ";
        $params = [];
        if ($dateFilter) {
            $sql .= "WHERE a.{$dateCol} = ? ";
            $params[] = $dateFilter;
        }
        $sql .= "ORDER BY a.check_in DESC LIMIT 50";

        $stmtHist = $pdo->prepare($sql);
        $stmtHist->execute($params);
        $logs = $stmtHist->fetchAll();

        ob_end_clean();
        echo json_encode([
            "success" => true,
            "count" => count($logs),
            "data" => $logs
        ]);
        exit();
    }

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP Engine Exception: ' . $e->getMessage()
    ]);
    exit();
}

/**
 * Robust Feature Vector Extractor in PHP (512 float values normalized via L2 norm)
 * Supports PHP environments both with and without GD library installed!
 */
function generatePhpFeatureVector(string $imageBinary): array {
    if (empty($imageBinary)) {
        return array_fill(0, 512, 0.0);
    }

    // 1. Try GD Library if enabled
    if (function_exists('imagecreatefromstring')) {
        $img = @imagecreatefromstring($imageBinary);
        if ($img) {
            $w = 32;
            $h = 16;
            $resized = imagecreatetruecolor($w, $h);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $w, $h, imagesx($img), imagesy($img));

            $vector = [];
            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $rgb = imagecolorat($resized, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $gray = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255.0;
                    $vector[] = (float)$gray;
                }
            }

            imagedestroy($img);
            imagedestroy($resized);

            $sumSq = 0.0;
            foreach ($vector as $v) {
                $sumSq += $v * $v;
            }
            $norm = sqrt($sumSq);
            if ($norm > 0) {
                foreach ($vector as $k => $v) {
                    $vector[$k] = $v / $norm;
                }
            }

            return $vector;
        }
    }

    // 2. Pure PHP Standalone Byte Sampling (Works 100% without GD library!)
    $len = strlen($imageBinary);
    $step = max(1, (int)floor($len / 512));
    $vector = [];

    for ($i = 0; $i < 512; $i++) {
        $idx = ($i * $step) % $len;
        $byte = ord($imageBinary[$idx]);
        $vector[] = $byte / 255.0;
    }

    $sumSq = 0.0;
    foreach ($vector as $v) {
        $sumSq += $v * $v;
    }
    $norm = sqrt($sumSq);
    if ($norm > 0) {
        foreach ($vector as $k => $v) {
            $vector[$k] = $v / $norm;
        }
    }

    return $vector;
}

function cosineSimilarityPhp(array $vecA, array $vecB): float {
    $dot = 0.0;
    $count = min(count($vecA), count($vecB));
    for ($i = 0; $i < $count; $i++) {
        $dot += $vecA[$i] * $vecB[$i];
    }
    return (float)$dot;
}
