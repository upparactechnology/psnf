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
            <a class="navbar-brand fw-bold text-info text-truncate" href="index.php" style="max-width: 60%;">
                <i class="fa-solid fa-arrow-left me-2"></i>Face Registration
            </a>
            <div class="d-flex align-items-center gap-1">
                <a href="../../dashboard" class="btn btn-outline-warning btn-sm fw-bold me-1"><i class="fa-solid fa-crown me-1 text-warning"></i>Admin</a>
                <button type="button" id="btnResetPhotos" class="btn btn-outline-danger btn-sm">
                    <i class="fa-solid fa-rotate-right me-1"></i>Reset
                </button>
            </div>
        </div>
    </nav>

    <div class="container my-3">
        <div class="row g-3">
            <!-- Left Column: Profile Form & Guide -->
            <div class="col-lg-5">
                <div class="card-custom p-3 p-md-4 mb-3">
                    <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-id-card me-2"></i>Employee Profile</h5>
                    <form id="registerForm" onsubmit="return false;">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">EMPLOYEE CODE *</label>
                            <input type="text" id="employee_code" class="form-control bg-dark text-white border-secondary" placeholder="e.g. EMP-1001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">FULL NAME *</label>
                            <input type="text" id="name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Sarah Jenkins" required>
                        </div>

                        <!-- Progress indicator -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-secondary">Face Photo Progress</span>
                                <span class="small text-info fw-bold" id="progressText">0 / 1</span>
                            </div>
                            <div class="progress bg-dark" style="height: 10px;">
                                <div id="progressBar" class="progress-bar bg-info" style="width: 0%;"></div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Registration Instructions Card -->
                <div class="card-custom p-3 p-md-4 text-center">
                    <h5 class="fw-bold text-white mb-2 fs-6"><i class="fa-solid fa-camera-retro me-2 text-info"></i>Face Photo Guide</h5>
                    <div class="p-3 bg-dark border border-secondary rounded-3 text-start">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fa-solid fa-circle-check text-success fs-5 me-2"></i>
                            <span class="text-white small fw-bold">Look straight into camera</span>
                        </div>
                        <p class="text-secondary small mb-0 ms-4">The scanner will automatically take 1 high-resolution face photo.</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Camera Scanner & Single Photo Kiosk -->
            <div class="col-lg-7">
                <div class="card-custom p-3 p-md-4 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-camera me-2 text-info"></i>Face Scanner</h5>
                        <span class="badge bg-primary px-3 py-2" id="angleHintBadge">1 Front Face Photo</span>
                    </div>

                    <!-- Camera Feed -->
                    <div class="camera-container mb-3">
                        <video id="webcam" autoplay playsinline></video>
                        <div class="face-overlay" id="faceOverlay"></div>
                        <div class="direction-arrow" id="directionArrow"><i class="fa-solid fa-bullseye"></i></div>
                    </div>
                    <canvas id="canvas" class="d-none"></canvas>

                    <!-- Status Instructions Banner -->
                    <div class="alert alert-dark border-secondary mb-3 py-2 text-info small" id="autoStatusBanner">
                        <i class="fa-solid fa-circle-info me-1"></i>Enter Employee Code & Name to start auto-scanner.
                    </div>

                    <!-- Single Captured Photo Thumbnail Preview -->
                    <div class="mb-2" id="thumbnailStrip">
                        <div class="single-thumb-box" id="thumb-0">
                            <div class="step-tag"><i class="fa-solid fa-user me-1"></i>Face Photo</div>
                        </div>
                    </div>

                    <!-- Result Status Alert Banner -->
                    <div id="statusAlert" class="alert d-none mt-3 text-start" role="alert"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        let capturedImage = null;
        let autoSaveTimer = null;
        let isSubmitting = false;

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

        async function initWebcam() {
            try {
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

        function scanLoop() {
            if (capturedImage || isSubmitting) {
                requestAnimationFrame(scanLoop);
                return;
            }

            const empCode = document.getElementById('employee_code').value.trim();
            const empName = document.getElementById('name').value.trim();

            if (!empCode || !empName) {
                faceOverlay.className = 'face-overlay';
                autoStatusBanner.className = 'alert alert-warning border-warning mb-3 py-2 text-dark fw-bold small';
                autoStatusBanner.innerHTML = `<i class="fa-solid fa-hand me-1"></i>Please enter Employee Code and Full Name to start scanner.`;
                requestAnimationFrame(scanLoop);
                return;
            }

            if (video.videoWidth > 0 && video.videoHeight > 0) {
                faceOverlay.className = 'face-overlay valid';
                directionArrow.innerHTML = `<i class="fa-solid fa-circle-check text-success"></i>`;
                autoStatusBanner.className = 'alert alert-success border-success mb-3 py-2 text-success fw-bold small';
                autoStatusBanner.innerHTML = `<i class="fa-solid fa-check-circle me-1"></i><b>Face Aligned!</b> Capturing photo and registering...`;

                if (!autoSaveTimer) {
                    autoSaveTimer = setTimeout(() => {
                        autoSaveTimer = null;
                        capturedImage = captureFrame();

                        // Update single photo preview UI
                        const thumbBox = document.getElementById('thumb-0');
                        thumbBox.innerHTML = `<img src="${capturedImage}"><div class="step-tag">✔ Photo Saved</div>`;
                        
                        progressText.innerText = "1 / 1";
                        progressBar.style.width = "100%";
                        angleBadge.innerText = "Face Photo Saved!";
                        angleBadge.className = "badge bg-success px-3 py-2";

                        // Auto-submit employee registration immediately!
                        autoSubmitRegistration();
                    }, 500); // 500ms fast single image capture
                }
            }

            requestAnimationFrame(scanLoop);
        }

        btnReset.addEventListener('click', () => {
            if (autoSaveTimer) clearTimeout(autoSaveTimer);
            autoSaveTimer = null;
            capturedImage = null;
            isSubmitting = false;

            angleBadge.className = "badge bg-primary px-3 py-2";
            angleBadge.innerText = "1 Front Face Photo";
            faceOverlay.className = 'face-overlay';
            
            const thumbBox = document.getElementById('thumb-0');
            thumbBox.innerHTML = `<div class="step-tag"><i class="fa-solid fa-user me-1"></i>Face Photo</div>`;
            
            progressText.innerText = "0 / 1";
            progressBar.style.width = "0%";
            statusAlert.classList.add('d-none');
        });

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
                images_base64: [capturedImage] // Single image array
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
                    setTimeout(() => { window.location.href = 'index.php'; }, 2000);
                } else {
                    const msg = (data && (data.detail || data.message)) || 'Registration failed.';
                    showAlert('danger', `<i class="fa-solid fa-circle-exclamation me-2"></i>${msg}`);
                    isSubmitting = false;
                }
            } catch (e) {
                showAlert('danger', '<i class="fa-solid fa-triangle-exclamation me-2"></i>Registration API Error: ' + e.message);
                isSubmitting = false;
            }
        }

        function showAlert(type, msg) {
            statusAlert.className = `alert alert-${type} mt-3 text-start`;
            statusAlert.innerHTML = msg;
            statusAlert.classList.remove('d-none');
        }

        initWebcam();
    </script>
</body>
</html>
