# System Requirements Specification (SRS)

This document details the functional and non-functional requirements for the **AI Face Recognition Attendance System**.

---

## 1. Functional Requirements

### 1.1 Employee Management Module
The system must support basic CRUD operations and profile setup for employees.

| ID | Feature | Description |
| :--- | :--- | :--- |
| **FR-EMP-1** | Add Employee | Admin can register a new employee with Name, Email, Phone, Employee ID, Department, and Designation. |
| **FR-EMP-2** | Face Association | Link multiple face embedding templates (extracted from enrollment video/frames) to the employee record. |
| **FR-EMP-3** | Edit Employee | Admin can modify employee details (e.g., department change or re-enrolling face templates). |
| **FR-EMP-4** | Delete Employee | Soft-delete employee record to preserve historical attendance logs while blocking new scans. |
| **FR-EMP-5** | Employee Search | Search and filter the employee roster by ID, Name, Department, or Status (Active/Inactive). |
| **FR-EMP-6** | Status Control | Manually toggle employee status between Active, Suspended, or Terminated. |

### 1.2 Face Enrollment Module
Provides step-by-step guidance for adding new face profiles.

| ID | Feature | Description |
| :--- | :--- | :--- |
| **FR-ENR-1** | Multi-Angle Capture | Guide employee through look directions: Straight, Turn Left, Turn Right, Look Up, Look Down, Blink, and Smile. |
| **FR-ENR-2** | Quality Filter | Reject frames with motion blur, poor lighting, or occlusions (e.g., sunglasses, masks). |
| **FR-ENR-3** | Embedding Generation | Generate 512-dimensional vector representations from 10–15 extracted high-quality frames. |

### 1.3 Attendance Engine Module
Handles real-time clock events and matching logic.

| ID | Feature | Description |
| :--- | :--- | :--- |
| **FR-ATT-1** | Auto Check-in/out | Detect and match face, then automatically categorize as Check-In or Check-Out based on active shifts. |
| **FR-ATT-2** | Duplicate Prevention | Ignore matching scans within a configurable cooldown period (default: 5 minutes) to prevent duplicate logs. |
| **FR-ATT-3** | Offline Capture | Log clock events locally in SQLite if network is down, and sync to the server when connection restores. |
| **FR-ATT-4** | Voice Feedback | Audio feedback for scan results: "Check-in Successful for John Doe" or "Access Denied / Unknown". |

### 1.4 Admin Dashboard & Shift Module
Enables administrative monitoring and scheduling.

| ID | Feature | Description |
| :--- | :--- | :--- |
| **FR-DSH-1** | Real-Time Monitor | Display today's counts: Present, Absent, Late Arrivals, Early Departures, and active employees. |
| **FR-SFT-1** | Multi-Shift Scheduler | Configure multiple shifts (e.g., Night, Morning) with grace periods and half-day thresholds. |
| **FR-REP-1** | Reporting Engine | Generate daily, weekly, monthly, and yearly reports, exporting to Excel, CSV, or PDF formats. |

---

## 2. Non-Functional Requirements

### 2.1 Performance & Latency
* **Real-time Detection**: Face detection, liveness check, and local face matching must complete in under 500ms on client devices.
* **API Response**: Backend match endpoints (`POST /recognition/match`) must respond in under 150ms for concurrent requests under normal load.
* **Concurrent Scans**: System must handle simultaneous scans across multiple kiosk tablets without dropping frames or blocking the database.

### 2.2 Reliability & Offline Resilience
* **Network Isolation**: The Android client must operate offline, caching face embeddings and logs locally.
* **Sync Recovery**: Local logs must sync to the database within 30 seconds of network restoration, resolving conflicts using device timestamp order.

### 2.3 Security, Privacy & Data Compliance
* **Data Encryption**: Encrypt all REST communication using HTTPS (TLS 1.3).
* **Embedding Protection**: Face embeddings must be stored as raw float vectors in the database; storing raw source images on the server is prohibited to protect user privacy.
* **Audit Trails**: Log all administrative actions (e.g., manual attendance adjustments, employee deletions) in an immutable audit table.

### 2.4 Device Compatibility
* **Android OS**: Compatible with Android API level 21 (Lollipop 5.0) and above.
* **Hardware Target**: Optimized for standard wall-mounted Android tablets (minimum 2GB RAM, 5MP front-facing camera).

For details on the database tables and relational constraints required to support these features, refer to [02_DATABASE_DESIGN.md](02_DATABASE_DESIGN.md).
