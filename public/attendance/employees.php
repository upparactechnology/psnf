<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registered Employees Directory — Face Recognition</title>
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
        .emp-avatar {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #38bdf8;
        }
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .card-custom { padding: 1.25rem !important; border-radius: 12px; }
            .emp-avatar { width: 40px; height: 40px; }
            .table th, .table td { font-size: 0.85rem; padding: 0.5rem; }
            .mobile-stack { flex-direction: column; gap: 0.5rem; align-items: stretch !important; }
            .mobile-full { width: 100% !important; }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3 py-2">
        <div class="container-fluid px-0">
            <a class="navbar-brand fw-bold text-info text-truncate" href="index.php" style="max-width: 65%;">
                <i class="fa-solid fa-arrow-left me-2"></i>Employees Directory
            </a>
            <div class="d-flex align-items-center gap-1">
                <a href="../../dashboard" class="btn btn-outline-warning btn-sm fw-bold me-1"><i class="fa-solid fa-crown me-1 text-warning"></i>Admin</a>
                <a href="register.php" class="btn btn-info btn-sm fw-bold"><i class="fa-solid fa-user-plus me-1"></i>Register</a>
                <a href="verify.php" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-camera me-1"></i>Kiosk</a>
            </div>
        </div>
    </nav>

    <div class="container my-3 px-2 px-md-3">
        <div class="card-custom p-3 p-md-4">
            <div class="row align-items-center mb-3 g-2">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0 text-info fs-5 fs-md-4"><i class="fa-solid fa-users me-2"></i>Registered Employees</h4>
                    <p class="text-secondary small mb-0 d-none d-md-block">Manage registered employees and face profile data</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-inline-flex gap-2 w-100 justify-content-md-end">
                        <input type="text" id="searchFilter" class="form-control bg-dark text-white border-secondary form-control-sm" placeholder="Search ID or Name...">
                    </div>
                </div>
            </div>

            <!-- Mobile Responsive Table -->
            <div class="table-responsive">
                <table class="table table-dark-custom table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th style="width: 40px;">#</th>
                            <th style="width: 60px;">PHOTO</th>
                            <th>ID</th>
                            <th>FULL NAME</th>
                            <th class="d-none d-md-table-cell">STATUS</th>
                            <th>REGISTERED DATE</th>
                            <th class="text-center" style="width: 90px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="empTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                <span class="spinner-border spinner-border-sm me-2"></span>Loading registered employees...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Script -->
    <script>
        const LIST_URL = "api.php?endpoint=/api/employees/list";
        const DELETE_URL = "api.php?endpoint=/api/employees/delete";

        const tableBody = document.getElementById('empTableBody');
        const searchFilter = document.getElementById('searchFilter');

        let allEmployees = [];

        async function fetchEmployees() {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary"><span class="spinner-border spinner-border-sm me-2"></span>Loading employees...</td></tr>`;

            try {
                const res = await fetch(LIST_URL);
                const resText = await res.text();
                let result = null;
                try {
                    result = JSON.parse(resText);
                } catch (e) {
                    result = null;
                }

                if (res.ok && result && result.success) {
                    allEmployees = result.data || [];
                    renderTable(allEmployees);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary">No registered employees found.</td></tr>`;
                }
            } catch (err) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Failed to fetch employee list.</td></tr>`;
            }
        }

        function renderTable(list) {
            if (!list || list.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary">No matching employees found.</td></tr>`;
                return;
            }

            tableBody.innerHTML = '';
            list.forEach((emp, i) => {
                const tr = document.createElement('tr');

                const imageHtml = emp.image_path 
                    ? `<a href="../../backend/${emp.image_path}" target="_blank"><img src="../../backend/${emp.image_path}" class="emp-avatar" alt="Face"></a>` 
                    : `<div class="emp-avatar bg-dark d-flex align-items-center justify-content-center text-secondary small"><i class="fa-solid fa-user"></i></div>`;

                let regDate = emp.photo_created_at || emp.created_at || '';
                if (!regDate || regDate.includes('0000-00-00')) {
                    regDate = new Date().toISOString().slice(0, 19).replace('T', ' ');
                }

                tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td>${imageHtml}</td>
                    <td><span class="badge bg-dark border border-secondary text-info px-2 py-1 fs-6">${emp.employee_code}</span></td>
                    <td class="fw-bold text-white fs-6 text-truncate" style="max-width: 140px;">${emp.name}</td>
                    <td class="d-none d-md-table-cell"><span class="badge bg-success">Active</span></td>
                    <td class="text-secondary small text-nowrap">${regDate}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" onclick="deleteEmployee(${emp.id}, '${emp.name}')">
                            <i class="fa-solid fa-trash-can"></i><span class="d-none d-md-inline ms-1">Delete</span>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        async function deleteEmployee(id, name) {
            if (!confirm(`Are you sure you want to delete employee "${name}" and their face profile?`)) return;

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
                    fetchEmployees();
                } else {
                    alert((data && data.message) || 'Failed to delete employee.');
                }
            } catch (err) {
                alert('Delete error: ' + err.message);
            }
        }

        searchFilter.addEventListener('input', () => {
            const query = searchFilter.value.toLowerCase().trim();
            const filtered = allEmployees.filter(emp => 
                (emp.name && emp.name.toLowerCase().includes(query)) ||
                (emp.employee_code && emp.employee_code.toLowerCase().includes(query))
            );
            renderTable(filtered);
        });

        fetchEmployees();
    </script>
</body>
</html>
