<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Attendance History Logs — Face Recognition</title>
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
        .table-dark-custom {
            --bs-table-bg: #1e293b;
            --bs-table-border-color: #334155;
            color: #f8fafc;
        }
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .card-custom { padding: 1.25rem !important; border-radius: 12px; }
            .table th, .table td { font-size: 0.85rem; padding: 0.5rem; }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3 py-2">
        <div class="container-fluid px-0">
            <a class="navbar-brand fw-bold text-info text-truncate" href="index.php" style="max-width: 65%;">
                <i class="fa-solid fa-arrow-left me-2"></i>Attendance Logs
            </a>
            <div class="d-flex align-items-center gap-1">
                <a href="../../dashboard" class="btn btn-outline-warning btn-sm fw-bold me-1"><i class="fa-solid fa-crown me-1 text-warning"></i>Admin</a>
                <a href="verify.php" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-camera me-1"></i>Kiosk</a>
            </div>
        </div>
    </nav>

    <div class="container my-3 px-2 px-md-3">
        <div class="card-custom p-3 p-md-4">
            <div class="row align-items-center mb-3 g-2">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0 text-info fs-5 fs-md-4"><i class="fa-solid fa-list-check me-2"></i>Attendance Records</h4>
                    <p class="text-secondary small mb-0 d-none d-md-block">Real-time attendance logs verified by AI engine</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-inline-flex gap-2 w-100 justify-content-md-end">
                        <input type="date" id="filterDate" class="form-control bg-dark text-white border-secondary form-control-sm">
                        <button type="button" id="btnFetchLogs" class="btn btn-info btn-sm fw-bold text-dark px-3">
                            <i class="fa-solid fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-dark-custom table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th style="width: 40px;">#</th>
                            <th>EMPLOYEE ID</th>
                            <th>NAME</th>
                            <th>CHECK-IN TIME</th>
                            <th>DATE</th>
                            <th>SNAPSHOT</th>
                            <th class="text-center" style="width: 80px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                <span class="spinner-border spinner-border-sm me-2"></span>Loading attendance logs...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Script -->
    <script>
        const API_URL = "api.php?endpoint=/api/attendance/history";
        const DELETE_URL = "api.php?endpoint=/api/attendance/delete";

        const tableBody = document.getElementById('logsTableBody');
        const filterDate = document.getElementById('filterDate');
        const btnFetch = document.getElementById('btnFetchLogs');

        async function fetchLogs() {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary"><span class="spinner-border spinner-border-sm me-2"></span>Loading logs...</td></tr>`;
            
            let queryUrl = API_URL + (filterDate.value ? `&date=${filterDate.value}` : '');

            try {
                const res = await fetch(queryUrl);
                const resText = await res.text();
                let result = null;
                try {
                    result = JSON.parse(resText);
                } catch (e) {
                    result = null;
                }

                if (res.ok && result && result.success && result.data.length > 0) {
                    tableBody.innerHTML = '';
                    result.data.forEach((log, i) => {
                        const tr = document.createElement('tr');
                        
                        // Parse Check-In Time and Date separately
                        let dateStr = log.attendance_date || '';
                        let timeStr = log.check_in || '';

                        if (log.check_in && log.check_in.includes(' ')) {
                            const parts = log.check_in.split(' ');
                            dateStr = parts[0];
                            
                            // Format time to 12-hour AM/PM format
                            const timeParts = parts[1].split(':');
                            if (timeParts.length >= 2) {
                                let hours = parseInt(timeParts[0]);
                                const minutes = timeParts[1];
                                const seconds = timeParts[2] || '00';
                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                hours = hours % 12;
                                hours = hours ? hours : 12;
                                timeStr = `${hours}:${minutes}:${seconds} ${ampm}`;
                            }
                        }

                        const imageHtml = log.image_path 
                            ? `<a href="../../backend/${log.image_path}" target="_blank" class="btn btn-outline-info btn-sm px-2 py-1"><i class="fa-solid fa-image"></i><span class="d-none d-md-inline ms-1">View</span></a>` 
                            : `<span class="text-secondary small">N/A</span>`;

                        tr.innerHTML = `
                            <td>${i + 1}</td>
                            <td><span class="badge bg-dark border border-secondary text-info px-2 py-1 fs-6">${log.employee_code}</span></td>
                            <td class="fw-bold text-white text-truncate" style="max-width: 120px;">${log.employee_name}</td>
                            <td class="text-info fw-bold text-nowrap">${timeStr}</td>
                            <td class="text-secondary small text-nowrap">${dateStr}</td>
                            <td>${imageHtml}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" onclick="deleteRecord(${log.id})">
                                    <i class="fa-solid fa-trash-can"></i><span class="d-none d-md-inline ms-1">Delete</span>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(tr);
                    });
                } else {
                    tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary">No attendance logs found.</td></tr>`;
                }
            } catch (err) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Failed to fetch logs.</td></tr>`;
            }
        }

        async function deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this attendance record?')) return;

            try {
                const res = await fetch(`${DELETE_URL}&id=${id}`, {
                    method: 'POST'
                });
                const resText = await res.text();
                let data = null;
                try {
                    data = JSON.parse(resText);
                } catch (e) {
                    data = null;
                }

                if (res.ok && data && data.success) {
                    fetchLogs();
                } else {
                    alert((data && data.message) || 'Failed to delete record.');
                }
            } catch (err) {
                alert('Delete error: ' + err.message);
            }
        }

        btnFetch.addEventListener('click', fetchLogs);
        fetchLogs();
    </script>
</body>
</html>
