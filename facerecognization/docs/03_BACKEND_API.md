# Backend REST API Specifications

The **AI Face Recognition Attendance System** backend exposes a RESTful API using JSON payload formatting over HTTP/HTTPS, with `/api/v1` as the base routing prefix.

---

## 1. Authentication (JWT Stateless Bearer Tokens)

```
┌───────────────────────────────────────────────────────────────┐
│                      AUTHENTICATION FLOW                      │
├───────────────────────┬──────────────────────┬────────────────┤
│   POST /auth/login    │  Store Access Token  │  Bearer Header │
│                       │                      │                │
│ • Admin Credentials   │ • Memory (React UI)  │ • API Requests │
│ • Validates Bcrypt    │ • Room DB (Android)  │ • Exp: 1 Hour  │
└───────────────────────┴──────────────────────┴────────────────┘
```

### 1.1 Endpoint: `POST /api/v1/auth/login`
Exchanges admin credentials for access and refresh tokens.

* **Request Headers**: `Content-Type: application/json`
* **Request Body**:
  ```json
  {
    "username": "admin_main",
    "password": "SecurePassword123"
  }
  ```
* **Response Status**: `200 OK`
* **Response Body**:
  ```json
  {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
  ```

---

## 2. Employee Management Services

### 2.1 Endpoint: `GET /api/v1/employees`
Lists employees with filtering, sorting, and pagination.

* **Request Query Parameters**:
  * `page` (default: `1`): Target page number.
  * `limit` (default: `20`): Page size.
  * `search` (optional): Filter by name or employee ID.
  * `department_id` (optional): Filter by department.
* **Request Headers**: `Authorization: Bearer <token>`
* **Response Status**: `200 OK`
* **Response Body**:
  ```json
  {
    "items": [
      {
        "id": 12,
        "employee_id": "EMP0142",
        "first_name": "Sarah",
        "last_name": "Jenkins",
        "email": "sarah.j@company.com",
        "phone": "+15550199",
        "department": { "id": 2, "name": "Engineering" },
        "status": "ACTIVE",
        "face_enrolled": true,
        "created_at": "2026-06-25T14:30:00Z"
      }
    ],
    "total": 1,
    "page": 1,
    "pages": 1
  }
  ```

### 2.2 Endpoint: `POST /api/v1/employees`
Creates a new employee record.

* **Request Body**:
  ```json
  {
    "employee_id": "EMP0142",
    "first_name": "Sarah",
    "last_name": "Jenkins",
    "email": "sarah.j@company.com",
    "phone": "+15550199",
    "department_id": 2
  }
  ```
* **Response Status**: `201 Created`
* **Response Body**:
  ```json
  {
    "id": 12,
    "employee_id": "EMP0142",
    "first_name": "Sarah",
    "last_name": "Jenkins",
    "email": "sarah.j@company.com",
    "phone": "+15550199",
    "department_id": 2,
    "status": "ACTIVE",
    "created_at": "2026-06-25T18:10:00Z"
  }
  ```

---

## 3. Face Enrollment & Recognition

### 3.1 Endpoint: `POST /api/v1/employees/{id}/enroll`
Enrolls face embedding templates for an employee.

* **Request Headers**: `Authorization: Bearer <token>`, `Content-Type: application/json`
* **Request Body**:
  ```json
  {
    "embeddings": [
      {
        "angle": "STRAIGHT",
        "vector": [0.0124, -0.0543, 0.1293]
      },
      {
        "angle": "TURN_LEFT",
        "vector": [0.0211, -0.0491, 0.1102]
      }
    ]
  }
  ```
* **Response Status**: `200 OK`
* **Response Body**:
  ```json
  {
    "employee_id": 12,
    "total_embeddings_enrolled": 2,
    "status": "ENROLLED"
  }
  ```

### 3.2 Endpoint: `POST /api/v1/recognition/match`
Compares a captured face embedding against enrolled templates to log attendance.

* **Request Headers**: `Content-Type: application/json`
* **Request Body**:
  ```json
  {
    "embedding": [0.0125, -0.0542, 0.1290],
    "timestamp": "2026-06-25T08:58:32Z",
    "device_id": "TAB_ENTRY_A",
    "liveness_score": 0.98
  }
  ```
* **Response Status**: `200 OK`
* **Response Body (Match Found)**:
  ```json
  {
    "matched": true,
    "employee": {
      "id": 12,
      "employee_id": "EMP0142",
      "first_name": "Sarah",
      "last_name": "Jenkins"
    },
    "attendance_log": {
      "id": 845,
      "clock_time": "2026-06-25T08:58:32Z",
      "clock_type": "CHECK_IN",
      "status": "PRESENT"
    }
  }
  ```
* **Response Status (No Match / Below Threshold)**: `404 Not Found`
* **Response Body**:
  ```json
  {
    "matched": false,
    "detail": "Face profile match score below threshold (0.65)."
  }
  ```

---

## 4. Attendance & Reporting

### 4.1 Endpoint: `GET /api/v1/attendance`
Retrieves daily attendance logs.

* **Query Parameters**:
  * `date` (optional): Filter by specific date (`YYYY-MM-DD`).
  * `department_id` (optional): Filter by department.
* **Response Status**: `200 OK`
* **Response Body**:
  ```json
  {
    "items": [
      {
        "id": 845,
        "employee": {
          "employee_id": "EMP0142",
          "name": "Sarah Jenkins"
        },
        "clock_time": "2026-06-25T08:58:32Z",
        "clock_type": "CHECK_IN",
        "status": "PRESENT",
        "device_id": "TAB_ENTRY_A"
      }
    ]
  }
  ```

---

## 5. System Health Check

### 5.1 Endpoint: `GET /api/v1/health`
Monitors API and database connectivity.

* **Response Status**: `200 OK`
* **Response Body**:
  ```json
  {
    "status": "healthy",
    "timestamp": "2026-06-25T18:11:00Z",
    "services": {
      "database": "online",
      "inference_engine": "online"
    }
  }
  ```

For details on the face enrollment steps and video capture rules, refer to [04_FACE_ENROLLMENT.md](04_FACE_ENROLLMENT.md).
