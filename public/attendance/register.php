<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PSNF_SESSION');
    session_start();
}

// Handle AJAX Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=psnf_drm;charset=utf8mb4", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            $_SESSION['user'] = $user;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
            $_SESSION['face_reg_authenticated'] = true;
            $_SESSION['face_reg_user'] = $user['name'] ?? 'Admin';
            echo json_encode(['success' => true, 'message' => 'Authentication successful!', 'userName' => $user['name']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Database authentication error: ' . $e->getMessage()]);
    }
    exit();
}

// Handle AJAX Logout / Lock Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    header('Content-Type: application/json');
    unset($_SESSION['face_reg_authenticated'], $_SESSION['face_reg_user']);
    echo json_encode(['success' => true]);
    exit();
}

// Clear registration auth token on GET so Admin login is required every time Add Employee is opened
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['face_reg_authenticated'], $_SESSION['face_reg_user']);
}

// Check registration login status (must unlock via Admin Login modal)
$isLoggedIn = !empty($_SESSION['face_reg_authenticated']);
$userName = $_SESSION['face_reg_user'] ?? 'Authorized Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Automatic Single Face Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .card-custom {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            border: 3px solid #38bdf8;
        }
        video {
            width: 100%;
            height: auto;
            display: block;
            transform: scaleX(-1);
        }
        .face-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 280px;
            border: 4px dashed #38bdf8;
            border-radius: 50%;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .face-overlay.valid {
            border-color: #22c55e !important;
            border-style: solid !important;
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.95), 0 0 0 9999px rgba(0, 0, 0, 0.4) !important;
        }
        .direction-arrow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3.5rem;
            color: rgba(56, 189, 248, 0.85);
            pointer-events: none;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: translate(-50%, -50%) scale(0.9); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
        }
        .single-thumb-box {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #38bdf8;
            background: #0f172a;
            margin: 0 auto;
        }
        .single-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .single-thumb-box .step-tag {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.85);
            color: #22c55e;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            padding: 3px 0;
        }
        .emp-avatar-thumb {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #38bdf8;
            background-color: #0f172a;
        }
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .card-custom { padding: 1.25rem !important; border-radius: 12px; }
            .face-overlay { width: 160px; height: 210px; }
            .container { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3 py-2">
        <div class="container-fluid px-0">
            <a class="navbar-brand fw-bold text-info text-truncate" href="verify.php" style="max-width: 50%;">
                <i class="fa-solid fa-arrow-left me-2"></i>Face Registration
            </a>
            <div class="d-flex align-items-center gap-2" id="authBadgeContainer">
                <?php if ($isLoggedIn): ?>
                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="handleAuthLogout()" title="Lock Kiosk Registration"><i class="fa-solid fa-lock me-1"></i>Lock</button>
                <?php else: ?>
                    <button type="button" class="btn btn-warning btn-sm fw-bold me-1" onclick="showAuthModal()"><i class="fa-solid fa-right-to-bracket me-1"></i>Admin Login</button>
                <?php endif; ?>
                <button type="button" id="btnResetPhotos" class="btn btn-outline-secondary btn-sm" title="Reset Camera Scanner">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Admin Authentication Required Modal -->
    <div class="modal fade <?php if (!$isLoggedIn) echo 'show d-block'; ?>" id="authModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="<?php if (!$isLoggedIn) echo 'background: rgba(15, 23, 42, 0.94); z-index: 1055;'; ?>">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-secondary px-4 pt-4 pb-2">
                    <h5 class="modal-title fw-bold text-info"><i class="fa-solid fa-shield-halved me-2 text-warning"></i>Admin Authentication Required</h5>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Please enter Admin / Staff credentials to unlock adding a new employee face.</p>
                    <div id="authAlert" class="alert alert-danger d-none small mb-3"></div>
                    <form id="authLoginForm" onsubmit="handleAuthLogin(event)">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">EMAIL ADDRESS</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-secondary text-white"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" id="authEmail" class="form-control bg-dark text-white border-secondary" placeholder="e.g. admin@psnf.edu" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-secondary text-white"><i class="fa-solid fa-key"></i></span>
                                <input type="password" id="authPassword" class="form-control bg-dark text-white border-secondary" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <a href="verify.php" class="btn btn-outline-secondary w-50 fw-bold"><i class="fa-solid fa-arrow-left me-1"></i>Cancel</a>
                            <button type="submit" id="authSubmitBtn" class="btn btn-info w-50 fw-bold"><i class="fa-solid fa-right-to-bracket me-1"></i>Login & Unlock</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-3">
        <div class="row g-3 justify-content-center" id="registrationRow">
            <!-- Step 1: Employee Details Form (Visible Initially) -->
            <div class="col-lg-6 col-md-8" id="step1Column">
                <div class="card-custom p-3 p-md-4 mb-3" id="step1Card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-info"><i class="fa-solid fa-1 me-2 text-warning"></i>Step 1: Employee Details</h5>
                        <span class="badge bg-secondary" id="step1Badge">Pending</span>
                    </div>
                    <form id="registerForm" onsubmit="handleStep1Submit(event)">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">EMPLOYEE CODE / ID *</label>
                            <input type="text" id="employee_code" class="form-control bg-dark text-white border-secondary" placeholder="e.g. EMP-1001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">FULL NAME *</label>
                            <input type="text" id="name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Sarah Jenkins" required>
                        </div>

                        <button type="submit" id="btnProceedToScan" class="btn btn-info btn-lg w-100 fw-bold shadow">
                            <i class="fa-solid fa-camera me-2"></i>Submit & Open Scanner
                        </button>
                    </form>

                    <!-- Confirmed Step 1 Details Box -->
                    <div id="step1Summary" class="d-none mt-3 p-3 bg-dark border border-success rounded-3 text-success small">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Employee Details Saved</span>
                            <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 text-xs" onclick="resetStep1()">Edit Details</button>
                        </div>
                        <div class="text-white" id="step1SummaryText"></div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Camera Scanner (Hidden initially until Step 1 submitted) -->
            <div class="col-lg-6 d-none" id="step2Column">
                <div class="card-custom p-3 p-md-4 text-center" id="step2Card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-camera me-2 text-warning"></i>Face Scanner</h5>
                        <span class="badge bg-warning text-dark px-3 py-2" id="angleHintBadge">Get Ready</span>
                    </div>

                    <!-- Scanning Employee Summary Badge -->
                    <div class="mb-3" id="scanningEmpSummary"></div>

                    <!-- Camera Feed -->
                    <div class="camera-container mb-3">
                        <video id="webcam" autoplay playsinline></video>
                        <div class="face-overlay" id="faceOverlay"></div>
                        <div class="direction-arrow" id="directionArrow"><i class="fa-solid fa-bullseye"></i></div>
                    </div>
                    <canvas id="canvas" class="d-none"></canvas>

                    <!-- Status Instructions Banner -->
                    <div class="alert alert-info border-info mb-3 py-2 text-info small" id="autoStatusBanner">
                        <i class="fa-solid fa-camera me-1"></i>Please align face inside circle to capture photo.
                    </div>

                    <!-- Single Captured Photo Thumbnail Preview -->
                    <div class="mb-2" id="thumbnailStrip">
                        <div class="single-thumb-box" id="thumb-0">
                            <div class="step-tag"><i class="fa-solid fa-user me-1"></i>Face Photo</div>
                        </div>
                    </div>

                    <!-- Result Status Alert Banner -->
                    <div id="statusAlert" class="alert d-none mt-3 text-start" role="alert"></div>

                    <!-- Action Buttons after completion -->
                    <div id="completionActions" class="d-none mt-3">
                        <button type="button" class="btn btn-success btn-lg w-100 fw-bold shadow" onclick="resetForNewEmployee()">
                            <i class="fa-solid fa-user-plus me-2"></i>+ Register Another Employee
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registered Employees Directory Section -->
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card-custom p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-info"><i class="fa-solid fa-users me-2"></i>Registered Employees Directory</h5>
                            <p class="text-secondary small mb-0">View all registered employee IDs, names, and face photos</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="regEmpSearch" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Search ID or Name..." oninput="filterRegisteredEmps()">
                            <span class="badge bg-primary px-3 py-2 text-nowrap" id="regEmpCountBadge">0 Registered</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 border-secondary">
                            <thead>
                                <tr class="text-secondary small text-uppercase border-secondary">
                                    <th style="width: 40px;">#</th>
                                    <th style="width: 70px;">FACE PHOTO</th>
                                    <th>EMPLOYEE ID</th>
                                    <th>FULL NAME</th>
                                    <th>REGISTERED DATE</th>
                                    <th class="text-center" style="width: 80px;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="registeredEmpsBody">
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-secondary">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Loading registered employees...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        let capturedImage = null;
        let autoSaveTimer = null;
        let isSubmitting = false;
        let step1Submitted = false;
        let webcamInitialized = false;

        const API_URL = "api.php?endpoint=/api/register-face";

        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const btnReset = document.getElementById('btnResetPhotos');
        const angleBadge = document.getElementById('angleHintBadge');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const statusAlert = document.getElementById('statusAlert');

        const faceOverlay = document.getElementById('faceOverlay');
        const directionArrow = document.getElementById('directionArrow');
        const autoStatusBanner = document.getElementById('autoStatusBanner');
        const step1Badge = document.getElementById('step1Badge');
        const step1Summary = document.getElementById('step1Summary');
        const step1SummaryText = document.getElementById('step1SummaryText');
        const completionActions = document.getElementById('completionActions');

        let countdownTimer = null;
        let countdownSeconds = 5;

        function handleStep1Submit(e) {
            e.preventDefault();
            const empCode = document.getElementById('employee_code').value.trim();
            const empName = document.getElementById('name').value.trim();

            if (!empCode || !empName) {
                showAlert('warning', 'Please fill in both Employee Code and Full Name.');
                return;
            }

            step1Submitted = true;

            // Hide Step 1 Column, Show ONLY Step 2 Column (centered)
            const step1Col = document.getElementById('step1Column');
            const step2Col = document.getElementById('step2Column');
            if (step1Col) step1Col.classList.add('d-none');
            if (step2Col) {
                step2Col.className = 'col-lg-7 col-md-9 mx-auto';
                step2Col.classList.remove('d-none');
                step2Col.scrollIntoView({ behavior: 'smooth' });
            }

            // Update Employee Summary Badge in Step 2 Header
            const empSummaryBadge = document.getElementById('scanningEmpSummary');
            if (empSummaryBadge) {
                empSummaryBadge.innerHTML = `<span class="badge bg-dark border border-info text-info px-3 py-2 fs-6"><i class="fa-solid fa-user-check me-2 text-success"></i>Registering: <b>${empCode}</b> (${empName})</span>`;
            }

            // Start 5-Second Countdown before capturing
            countdownSeconds = 5;
            startCountdown();

            if (!webcamInitialized) {
                initWebcam();
            }
        }

        function startCountdown() {
            if (countdownTimer) clearInterval(countdownTimer);

            angleBadge.className = 'badge bg-warning text-dark px-3 py-2 fw-bold';
            angleBadge.innerText = `Scanning in ${countdownSeconds}s`;

            autoStatusBanner.className = 'alert alert-warning border-warning mb-3 py-2 text-dark fw-bold fs-6';
            autoStatusBanner.innerHTML = `<i class="fa-solid fa-clock me-2"></i>Position face! Photo capture in <span class="badge bg-danger fs-5 px-3 py-1" id="countdownBadge">${countdownSeconds}</span> seconds...`;

            countdownTimer = setInterval(() => {
                countdownSeconds--;
                const countdownBadge = document.getElementById('countdownBadge');
                if (countdownBadge) countdownBadge.innerText = countdownSeconds;
                angleBadge.innerText = `Scanning in ${countdownSeconds}s`;

                if (countdownSeconds <= 0) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;

                    angleBadge.className = 'badge bg-success px-3 py-2';
                    angleBadge.innerText = 'Capturing Face...';

                    autoStatusBanner.className = 'alert alert-success border-success mb-3 py-2 text-success fw-bold small';
                    autoStatusBanner.innerHTML = `<i class="fa-solid fa-camera me-1"></i><b>Capturing & Registering Face Photo...</b>`;
                }
            }, 1000);
        }

        function resetStep1() {
            resetForNewEmployee();
        }

        async function initWebcam() {
            try {
                webcamInitialized = true;
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
                });
                video.srcObject = stream;
                requestAnimationFrame(scanLoop);
            } catch (err) {
                showAlert('danger', 'Camera access error: ' + err.message);
            }
        }

        function captureFrame() {
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.95);
        }

        function checkImageQuality(ctx, width, height) {
            const imgData = ctx.getImageData(0, 0, width, height);
            const data = imgData.data;
            let totalBrightness = 0;
            let pixelCount = 0;

            for (let i = 0; i < data.length; i += 16) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                totalBrightness += lum;
                pixelCount++;
            }

            const avgBrightness = totalBrightness / (pixelCount || 1);

            // 1. Dark / Black photo check
            if (avgBrightness < 38) {
                return {
                    valid: false,
                    reason: 'dark',
                    message: '⚠️ Photo is too dark / black! Please move to a well-lit area.'
                };
            }

            // 2. Blur / Sharpness edge variance check
            const startX = Math.floor(width * 0.25);
            const endX = Math.floor(width * 0.75);
            const startY = Math.floor(height * 0.25);
            const endY = Math.floor(height * 0.75);

            let diffSum = 0;
            let sampleCount = 0;

            for (let y = startY; y < endY; y += 4) {
                for (let x = startX; x < endX; x += 4) {
                    const idx = (y * width + x) * 4;
                    const nextIdx = (y * width + (x + 2)) * 4;
                    const lum1 = 0.299 * data[idx] + 0.587 * data[idx+1] + 0.114 * data[idx+2];
                    const lum2 = 0.299 * data[nextIdx] + 0.587 * data[nextIdx+1] + 0.114 * data[nextIdx+2];
                    diffSum += Math.abs(lum1 - lum2);
                    sampleCount++;
                }
            }

            const avgEdgeDiff = diffSum / (sampleCount || 1);

            if (avgEdgeDiff < 4.0) {
                return {
                    valid: false,
                    reason: 'blur',
                    message: '⚠️ Photo is blurry! Please hold still and look straight at the camera.'
                };
            }

            return { valid: true, brightness: avgBrightness, sharpness: avgEdgeDiff };
        }

        function scanLoop() {
            if (!step1Submitted || capturedImage || isSubmitting || countdownSeconds > 0) {
                requestAnimationFrame(scanLoop);
                return;
            }

            const empCode = document.getElementById('employee_code').value.trim();
            const empName = document.getElementById('name').value.trim();

            if (!empCode || !empName) {
                requestAnimationFrame(scanLoop);
                return;
            }

            if (video.videoWidth > 0 && video.videoHeight > 0) {
                if (!autoSaveTimer) {
                    autoSaveTimer = setTimeout(() => {
                        autoSaveTimer = null;
                        
                        // Check canvas image quality before capturing
                        const width = video.videoWidth || 640;
                        const height = video.videoHeight || 480;
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.translate(canvas.width, 0);
                        ctx.scale(-1, 1);
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                        const quality = checkImageQuality(ctx, width, height);

                        if (!quality.valid) {
                            faceOverlay.className = 'face-overlay';
                            angleBadge.className = 'badge bg-danger px-3 py-2';
                            angleBadge.innerText = quality.reason === 'dark' ? 'Too Dark / Black' : 'Image Blurry';

                            autoStatusBanner.className = 'alert alert-danger border-danger mb-3 py-2 text-danger fw-bold fs-6';
                            autoStatusBanner.innerHTML = quality.message;

                            // Reset 5-second countdown to retry
                            setTimeout(() => {
                                countdownSeconds = 5;
                                startCountdown();
                            }, 2000);
                            return;
                        }

                        faceOverlay.className = 'face-overlay valid';
                        directionArrow.innerHTML = `<i class="fa-solid fa-circle-check text-success"></i>`;
                        capturedImage = canvas.toDataURL('image/jpeg', 0.95);

                        // Update single photo preview UI
                        const thumbBox = document.getElementById('thumb-0');
                        thumbBox.innerHTML = `<img src="${capturedImage}"><div class="step-tag">✔ Photo Saved</div>`;
                        
                        if (progressText) progressText.innerText = "1 / 1";
                        if (progressBar) progressBar.style.width = "100%";
                        angleBadge.innerText = "Face Photo Saved!";
                        angleBadge.className = "badge bg-success px-3 py-2";

                        // Auto-submit employee registration immediately!
                        autoSubmitRegistration();
                    }, 300);
                }
            }

            requestAnimationFrame(scanLoop);
        }

        btnReset.addEventListener('click', () => {
            resetForNewEmployee();
        });

        function resetForNewEmployee() {
            if (countdownTimer) clearInterval(countdownTimer);
            countdownTimer = null;
            countdownSeconds = 5;

            if (autoSaveTimer) clearTimeout(autoSaveTimer);
            autoSaveTimer = null;
            capturedImage = null;
            isSubmitting = false;
            step1Submitted = false;

            document.getElementById('employee_code').value = '';
            document.getElementById('name').value = '';
            document.getElementById('employee_code').readOnly = false;
            document.getElementById('name').readOnly = false;

            // Hide Step 2, Re-show Step 1
            const step1Col = document.getElementById('step1Column');
            const step2Col = document.getElementById('step2Column');
            if (step1Col) {
                step1Col.className = 'col-lg-6 col-md-8';
                step1Col.classList.remove('d-none');
            }
            if (step2Col) step2Col.classList.add('d-none');

            // Stop Webcam Stream
            if (video && video.srcObject) {
                try { video.srcObject.getTracks().forEach(t => t.stop()); } catch(e) {}
                video.srcObject = null;
            }
            webcamInitialized = false;

            angleBadge.className = "badge bg-secondary px-3 py-2";
            angleBadge.innerText = "Waiting for Step 1";
            faceOverlay.className = 'face-overlay';
            
            const thumbBox = document.getElementById('thumb-0');
            thumbBox.innerHTML = `<div class="step-tag"><i class="fa-solid fa-user me-1"></i>Face Photo</div>`;
            
            if (progressText) progressText.innerText = "0 / 1";
            if (progressBar) progressBar.style.width = "0%";
            statusAlert.classList.add('d-none');
            completionActions.classList.add('d-none');
        }

        async function autoSubmitRegistration() {
            if (isSubmitting) return;
            isSubmitting = true;

            const empCode = document.getElementById('employee_code').value.trim();
            const empName = document.getElementById('name').value.trim();

            if (!empCode || !empName) {
                showAlert('warning', 'Please fill in Employee Code and Full Name to complete registration.');
                isSubmitting = false;
                return;
            }

            showAlert('info', `<span class="spinner-border spinner-border-sm me-2"></span><b>Submitting Registration to Database...</b>`);

            const payload = {
                employee_code: empCode,
                name: empName,
                department: '',
                designation: '',
                images_base64: [capturedImage]
            };

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const resText = await response.text();
                let data = null;
                try {
                    data = JSON.parse(resText);
                } catch (jsonErr) {
                    data = { success: false, message: "Server response error: " + resText.substring(0, 120) };
                }

                if (response.ok && data && data.success) {
                    showAlert('success', `<i class="fa-solid fa-circle-check me-2"></i><b>Registration Successful!</b> ${data.message}`);
                    completionActions.classList.remove('d-none');
                    fetchRegisteredEmps();
                } else {
                    showAlert('danger', `<i class="fa-solid fa-triangle-exclamation me-2"></i><b>Registration Failed:</b> ${(data && data.message) || 'Unknown error'}`);
                    isSubmitting = false;
                }
            } catch (err) {
                showAlert('danger', `<i class="fa-solid fa-triangle-exclamation me-2"></i><b>Connection Error:</b> ${err.message}`);
                isSubmitting = false;
            }
        }

        function showAlert(type, msg) {
            statusAlert.className = `alert alert-${type} mt-3 text-start`;
            statusAlert.innerHTML = msg;
            statusAlert.classList.remove('d-none');
        }

        async function handleAuthLogin(e) {
            e.preventDefault();
            const email = document.getElementById('authEmail').value.trim();
            const password = document.getElementById('authPassword').value.trim();
            const alertDiv = document.getElementById('authAlert');
            const btn = document.getElementById('authSubmitBtn');

            alertDiv.classList.add('d-none');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Authenticating...';

            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('email', email);
                formData.append('password', password);

                const res = await fetch('register.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    const modal = document.getElementById('authModal');
                    if (modal) {
                        modal.classList.remove('show', 'd-block');
                        modal.style.display = 'none';
                    }
                    const badgeContainer = document.getElementById('authBadgeContainer');
                    if (badgeContainer) {
                        badgeContainer.innerHTML = `<button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="handleAuthLogout()" title="Lock Kiosk Registration"><i class="fa-solid fa-lock me-1"></i>Lock</button><button type="button" id="btnResetPhotos" class="btn btn-outline-secondary btn-sm" title="Reset Camera Scanner"><i class="fa-solid fa-rotate-right"></i></button>`;
                    }
                    initWebcam();
                } else {
                    alertDiv.innerText = data.message || 'Invalid email or password.';
                    alertDiv.classList.remove('d-none');
                }
            } catch (err) {
                alertDiv.innerText = 'Authentication error: ' + err.message;
                alertDiv.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-right-to-bracket me-1"></i>Login & Unlock';
            }
        }

        async function handleAuthLogout() {
            if (confirm('Lock registration kiosk and log out?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                await fetch('register.php', { method: 'POST', body: formData });
                window.location.reload();
            }
        }

        function showAuthModal() {
            const modal = document.getElementById('authModal');
            if (modal) {
                modal.style.background = 'rgba(15, 23, 42, 0.94)';
                modal.classList.add('show', 'd-block');
            }
        }

        const EMP_LIST_URL = "api.php?endpoint=/api/employees/list";
        const EMP_DELETE_URL = "api.php?endpoint=/api/employees/delete";

        let allRegisteredEmps = [];

        async function fetchRegisteredEmps() {
            const body = document.getElementById('registeredEmpsBody');
            if (!body) return;

            try {
                const res = await fetch(EMP_LIST_URL);
                const data = await res.json();
                if (res.ok && data && data.success) {
                    allRegisteredEmps = data.data || [];
                    renderRegisteredEmps(allRegisteredEmps);
                } else {
                    body.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-secondary">No registered employees found.</td></tr>`;
                }
            } catch (err) {
                body.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-secondary">No registered employees found.</td></tr>`;
            }
        }

        function renderRegisteredEmps(list) {
            const body = document.getElementById('registeredEmpsBody');
            const badge = document.getElementById('regEmpCountBadge');
            if (!body) return;

            if (badge) badge.innerText = `${list.length} Registered`;

            if (!list || list.length === 0) {
                body.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-secondary">No matching employees found.</td></tr>`;
                return;
            }

            body.innerHTML = '';
            list.forEach((emp, i) => {
                const tr = document.createElement('tr');

                const imageHtml = emp.image_path 
                    ? `<a href="../../backend/${emp.image_path}" target="_blank" title="View Full Photo"><img src="../../backend/${emp.image_path}" class="emp-avatar-thumb" alt="Face Photo"></a>` 
                    : `<div class="emp-avatar-thumb bg-dark d-flex align-items-center justify-content-center text-secondary small"><i class="fa-solid fa-user"></i></div>`;

                let regDate = emp.photo_created_at || emp.created_at || '';
                if (!regDate || regDate.includes('0000-00-00')) {
                    regDate = 'Recently';
                }

                tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td>${imageHtml}</td>
                    <td><span class="badge bg-dark border border-secondary text-info px-2 py-1 fs-6">${emp.employee_code || ('EMP-' + emp.id)}</span></td>
                    <td class="fw-bold text-white fs-6">${emp.name}</td>
                    <td class="small text-secondary">${regDate}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm px-2 py-1" onclick="deleteEmp(${emp.id}, '${emp.name}')" title="Delete Face Profile">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                `;
                body.appendChild(tr);
            });
        }

        function filterRegisteredEmps() {
            const query = (document.getElementById('regEmpSearch').value || '').toLowerCase().trim();
            if (!query) {
                renderRegisteredEmps(allRegisteredEmps);
                return;
            }
            const filtered = allRegisteredEmps.filter(e => 
                (e.employee_code || '').toLowerCase().includes(query) || 
                (e.name || '').toLowerCase().includes(query)
            );
            renderRegisteredEmps(filtered);
        }

        async function deleteEmp(id, name) {
            if (!confirm(`Are you sure you want to delete registered face for ${name}?`)) return;
            try {
                const res = await fetch(`${EMP_DELETE_URL}&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (res.ok && data && data.success) {
                    fetchRegisteredEmps();
                } else {
                    alert(data.message || 'Delete failed.');
                }
            } catch (err) {
                alert('Delete error: ' + err.message);
            }
        }

        fetchRegisteredEmps();

        <?php if ($isLoggedIn): ?>
        initWebcam();
        <?php endif; ?>
    </script>
</body>
</html>
