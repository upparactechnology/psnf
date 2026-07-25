<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>AI Face Recognition Attendance System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0f172a;
            --card-bg: #1e293b;
            --accent-color: #38bdf8;
            --accent-green: #22c55e;
            --accent-amber: #f59e0b;
        }
        body {
            background-color: var(--primary-bg);
            color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .navbar-custom {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
        }
        .hero-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .hero-card:hover {
            transform: translateY(-4px);
            border-color: var(--accent-color);
        }
        .action-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 1.75rem 1.25rem;
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .action-card:hover {
            border-color: var(--accent-color);
            background-color: #334155;
        }
        .icon-box {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem auto;
        }
        .bg-icon-cyan { background-color: rgba(56, 189, 248, 0.15); color: #38bdf8; }
        .bg-icon-green { background-color: rgba(34, 197, 94, 0.15); color: #22c55e; }
        .bg-icon-amber { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .bg-icon-purple { background-color: rgba(168, 85, 247, 0.15); color: #c084fc; }

        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .action-card { padding: 1.25rem 1rem; }
            .container { padding-left: 12px; padding-right: 12px; }
            h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-3 py-2">
        <div class="container-fluid px-0">
            <a class="navbar-brand fw-bold text-info text-truncate" href="index.php" style="max-width: 55%;">
                <i class="fa-solid me-2 fa-user-check"></i>AI Face Recognition
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="../../dashboard" class="btn btn-outline-warning btn-sm fw-bold"><i class="fa-solid fa-crown me-1 text-warning"></i>Super Admin</a>
                <a href="verify.php" class="btn btn-outline-info btn-sm fw-bold"><i class="fa-solid fa-camera me-1"></i>Kiosk</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container my-4 my-md-5">
        <div class="row text-center mb-4 mb-md-5">
            <div class="col-lg-8 mx-auto">
                <h1 class="fw-bold mb-2">Web-Based Face Recognition System</h1>
                <p class="text-secondary small lead">Contactless, automated employee attendance verification connected with PSNF Super Admin Portal.</p>
            </div>
        </div>

        <!-- Quick Launch Cards (4 Columns Layout) -->
        <div class="row g-3 g-md-4 mb-4 mb-md-5">
            <!-- Mark Attendance Kiosk -->
            <div class="col-lg-3 col-sm-6">
                <div class="action-card">
                    <div>
                        <div class="icon-box bg-icon-green">
                            <i class="fa-solid fa-camera-retro"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-2">Mark Attendance</h3>
                        <p class="text-secondary small mb-3">Open the camera kiosk to verify face identity and mark attendance.</p>
                    </div>
                    <a href="verify.php" class="btn btn-success btn-md w-100 fw-bold">
                        <i class="fa-solid fa-play me-2"></i>Open Kiosk
                    </a>
                </div>
            </div>

            <!-- Face Registration -->
            <div class="col-lg-3 col-sm-6">
                <div class="action-card">
                    <div>
                        <div class="icon-box bg-icon-cyan">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-2">Register Employee</h3>
                        <p class="text-secondary small mb-3">Register new employee with automatic single face photo capture.</p>
                    </div>
                    <a href="register.php" class="btn btn-info btn-md w-100 fw-bold text-dark">
                        <i class="fa-solid fa-id-card me-2"></i>Register Face
                    </a>
                </div>
            </div>

            <!-- Registered Employees Directory -->
            <div class="col-lg-3 col-sm-6">
                <div class="action-card">
                    <div>
                        <div class="icon-box bg-icon-amber">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-2">Registered Employees</h3>
                        <p class="text-secondary small mb-3">View all registered employees, face photos, Employee IDs, and delete records.</p>
                    </div>
                    <a href="employees.php" class="btn btn-warning btn-md w-100 fw-bold text-dark">
                        <i class="fa-solid fa-users me-2"></i>View Employees
                    </a>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="col-lg-3 col-sm-6">
                <div class="action-card">
                    <div>
                        <div class="icon-box bg-icon-purple">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-2">Attendance Logs</h3>
                        <p class="text-secondary small mb-3">View detailed attendance logs, timestamps, snapshots, and delete records.</p>
                    </div>
                    <a href="history.php" class="btn btn-purple btn-md w-100 fw-bold text-white" style="background-color: #8b5cf6;">
                        <i class="fa-solid fa-table me-2"></i>View History
                    </a>
                </div>
            </div>
        </div>

        <!-- System Architecture & Config Card -->
        <div class="hero-card p-3 p-md-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold text-info fs-5 fs-md-4"><i class="fa-solid fa-microchip me-2"></i>Backend Engine Configuration</h4>
                    <p class="text-secondary mb-2 small">Target API Base URL: <code>http://localhost/psnf/public/attendance/api.php</code></p>
                    <ul class="list-inline text-secondary small mb-0">
                        <li class="list-inline-item me-3 mb-1"><i class="fa-solid fa-check text-success me-1"></i>Engine: <b>PHP Engine & AI</b></li>
                        <li class="list-inline-item me-3 mb-1"><i class="fa-solid fa-check text-success me-1"></i>Threshold: <b>0.60</b></li>
                        <li class="list-inline-item me-3 mb-1"><i class="fa-solid fa-check text-success me-1"></i>Cooldown: <b>10 Minutes</b></li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="../../dashboard" class="btn btn-warning fw-bold text-dark w-100 w-md-auto">
                        <i class="fa-solid fa-crown me-2"></i>Super Admin Portal
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
