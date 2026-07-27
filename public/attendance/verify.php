<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Face Recognition Attendance Kiosk</title>
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
            max-width: 640px;
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
        .scanner-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
            box-shadow: 0 0 15px #38bdf8;
            animation: scan 2.5s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes scan {
            0% { top: 0%; }
            50% { top: 95%; }
            100% { top: 0%; }
        }
        .face-box-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 280px;
            border: 3px dashed rgba(56, 189, 248, 0.7);
            border-radius: 50%;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
        }
        .result-card {
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }
        .result-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid #22c55e;
            color: #4ade80;
        }
        .result-warning {
            background: rgba(245, 158, 11, 0.2);
            border: 2px solid #f59e0b;
            color: #fbbf24;
        }
        .result-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
        }
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .card-custom { padding: 1.25rem !important; border-radius: 12px; }
            .face-box-overlay { width: 160px; height: 210px; }
            .container { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3 py-2">
        <div class="container-fluid px-0">
            <a class="navbar-brand fw-bold text-info text-truncate" href="verify.php" style="max-width: 55%;">
                <i class="fa-solid fa-user-check me-2"></i>Attendance Kiosk
            </a>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-secondary me-1" id="clockBadge">00:00:00 AM</span>
                <a href="register.php" class="btn btn-outline-info btn-sm fw-bold"><i class="fa-solid fa-user-plus"></i></a>
                <a href="history.php" class="btn btn-outline-light btn-sm fw-bold"><i class="fa-solid fa-history"></i></a>
            </div>
        </div>
    </nav>

    <div class="container my-3">
        <div class="row g-3">
            <!-- Left Column: Camera Scanner -->
            <div class="col-lg-7">
                <div class="card-custom p-3 p-md-4 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-camera me-2 text-info"></i>Face Scanner</h5>
                        <span class="badge bg-success" id="liveScanStatus"><i class="fa-solid fa-eye me-1"></i>Auto Scanning</span>
                    </div>

                    <!-- Camera Feed -->
                    <div class="camera-container mb-3">
                        <video id="webcam" autoplay playsinline></video>
                        <div class="scanner-line"></div>
                        <div class="face-box-overlay"></div>
                    </div>
                    <canvas id="canvas" class="d-none"></canvas>

                    <!-- Instructions -->
                    <p class="text-secondary small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>Align face inside circle to mark attendance automatically.
                    </p>
                </div>
            </div>

            <!-- Right Column: Verification Result & Recent Logs -->
            <div class="col-lg-5">
                <!-- Result Panel -->
                <div class="card-custom p-3 p-md-4 mb-3">
                    <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-id-card-clip me-2"></i>Verification Status</h5>
                    
                    <div id="resultPanel" class="result-card bg-dark border-secondary text-center text-secondary">
                        <i class="fa-solid fa-user-clock fs-1 mb-2"></i>
                        <p class="mb-0 fw-bold">Waiting for face detection...</p>
                    </div>
                </div>

                <!-- Recent Verification Feed -->
                <div class="card-custom p-3 p-md-4">
                    <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Check-Ins</h5>
                    <div class="list-group list-group-flush bg-transparent" id="recentFeed">
                        <div class="list-group-item bg-transparent text-secondary text-center small border-secondary">
                            No check-ins recorded yet today.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Effects for Kiosk Feedback -->
    <audio id="soundSuccess" src="https://actions.google.com/sounds/v1/cartoon/clack.ogg" preload="auto"></audio>
    <audio id="soundError" src="https://actions.google.com/sounds/v1/cartoon/boing.ogg" preload="auto"></audio>
    <audio id="soundWarning" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" preload="auto"></audio>

    <!-- Script -->
    <script>
        let isProcessing = false;
        let scanPausedUntil = 0;
        let lastScannedPerson = null;
        let lastScanTime = 0;
        const SCAN_INTERVAL_MS = 600; // Throttle scans to every 600ms to keep CPU & network lightweight

        const API_URL = "api.php?endpoint=/api/verify-face";

        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const resultPanel = document.getElementById('resultPanel');
        const recentFeed = document.getElementById('recentFeed');
        const clockBadge = document.getElementById('clockBadge');

        // Synthesized Audio Feedback (Instant local sound, no network delays)
        function playBeep(type) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                if (type === 'success') {
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1);
                } else if (type === 'warning') {
                    osc.frequency.setValueAtTime(440, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(330, audioCtx.currentTime + 0.15);
                } else {
                    osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                }
                
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.3);
                
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
            } catch(e) {}
        }

        // Clean up camera stream and stop scan loop instantly on link clicks
        window.addEventListener('beforeunload', () => {
            scanPausedUntil = Date.now() + 999999;
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        });

        // Live Clock
        setInterval(() => {
            const now = new Date();
            clockBadge.innerText = now.toLocaleTimeString();
        }, 1000);

        // Webcam Setup
        async function initWebcam() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
                });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    video.play().catch(() => {});
                };
                requestAnimationFrame(processFrame);
            } catch (err) {
                showResult('error', 'Camera Error', 'Unable to access camera: ' + err.message);
            }
        }

        // Frame Capture & Auto-Scan Loop
        function processFrame() {
            const now = Date.now();
            const hasVideo = video.readyState >= 2 || video.videoWidth > 0;
            if (!isProcessing && now > scanPausedUntil && (now - lastScanTime >= SCAN_INTERVAL_MS) && hasVideo) {
                lastScanTime = now;
                verifyCurrentFrame();
            }
            requestAnimationFrame(processFrame);
        }

        async function verifyCurrentFrame() {
            isProcessing = true;

            const width = video.videoWidth || 640;
            const height = video.videoHeight || 480;
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, width, height);

            const base64Image = canvas.toDataURL('image/jpeg', 0.85);

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ image_base64: base64Image })
                });

                const resText = await response.text();
                let data = null;
                try {
                    data = JSON.parse(resText);
                } catch (e) {
                    data = null;
                }

                if (response.ok && data && data.success) {
                    if (data.already_checked_in) {
                        playBeep('warning');
                        showResult('warning', 'Already Checked In', data.message || `Attendance already marked for ${data.employee_name}. Please wait before scanning again.`);
                        scanPausedUntil = Date.now() + 6000;
                    } else {
                        playBeep('success');
                        showResult('success', 'Attendance Marked!', `Welcome, <b>${data.employee_name}</b> (${data.employee_code})`);
                        addRecentFeed(data.employee_name, data.employee_code, data.check_in || new Date().toLocaleTimeString());
                        scanPausedUntil = Date.now() + 6000;
                    }
                } else if (data && data.no_faces_registered) {
                    showResult('warning', 'No Faces Registered', data.message || 'No registered employee faces found in database. Please click "+" to register a face first.');
                    scanPausedUntil = Date.now() + 5000;
                } else if (data && !data.success) {
                    showResult('warning', 'Face Unrecognized', data.message || 'Face not recognized. Please align face inside the frame or register photo.');
                    scanPausedUntil = Date.now() + 2000;
                }
            } catch (err) {
                // API Error -> Brief pause before retry
                scanPausedUntil = Date.now() + 2000;
            } finally {
                isProcessing = false;
            }
        }

        function showResult(type, title, message) {
            resultPanel.className = `result-card result-${type}`;
            let icon = type === 'success' ? 'fa-circle-check' : (type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
            resultPanel.innerHTML = `
                <i class="fa-solid ${icon} fs-1 mb-2"></i>
                <h5 class="fw-bold mb-1">${title}</h5>
                <p class="mb-0 small">${message}</p>
            `;
        }

        function addRecentFeed(name, code, time) {
            if (recentFeed.children[0] && recentFeed.children[0].innerText.includes('No check-ins')) {
                recentFeed.innerHTML = '';
            }

            const item = document.createElement('div');
            item.className = 'list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center py-2 px-0';
            item.innerHTML = `
                <div>
                    <span class="fw-bold text-info me-2">${name}</span>
                    <span class="badge bg-dark border border-secondary text-secondary small">${code}</span>
                </div>
                <span class="badge bg-success">${time}</span>
            `;
            recentFeed.prepend(item);

            if (recentFeed.children.length > 5) {
                recentFeed.removeChild(recentFeed.lastChild);
            }
        }

        initWebcam();
    </script>
</body>
</html>
