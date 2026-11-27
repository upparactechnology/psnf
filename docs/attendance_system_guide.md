# PSNF Attendance System: Comprehensive Guide

This document provides a detailed overview of the core architecture, workflows, rules, and modules of the PSNF Contactless & Manual Attendance System. The system manages attendance tracking dynamically for **Staff/Employees**, **Teachers**, **Students**, and **Parents**, integrated directly with face recognition AI and transport routing.

---

## 1. Core Architecture & AI Face Recognition (Kiosk)

The system features an automated, contactless **AI Face Recognition Attendance Kiosk** deployed at school entrances.

### AI Engine (InsightFace)
* **Backend Stack**: Powered by a Python uvicorn FastAPI backend (`backend/app.py`) running locally on port `8000`.
* **Database Sync**: The Python API connects directly to the MySQL database via SQLAlchemy, parsing credentials from the central PHP `config/database.php` config.
* **Extraction & Matching**:
  * Extracts **512-dimensional facial embeddings** using the `buffalo_l` InsightFace model.
  * Employs cosine similarity matching against in-memory face embeddings.
  * **Similarity Threshold**: Optimized at `0.55` (scores below this reject matches to ensure security).

### Image Quality & Liveness Verification
* **Blur Assessment**: Evaluates the Laplacian variance of the incoming video feed frame. Images below a threshold (`40.0`) are rejected as too blurry.
* **Illumination Limits**: Rejects images that are too dark (average brightness `< 30.0`) or overexposed (average brightness `> 230.0`).
* **Liveness Detection**: Employs structural bounds checks (using the face bounding box) to prevent spoofing attempts (e.g., trying to present printouts or video playbacks of employees on mobile screens).

### Scan Rules & Anti-Duplicate Cooldown
* **Scan Cooldown**: A strict **10-minute cooldown** (`COOLDOWN_MINUTES = 10`) is enforced.
* Subsequent scans within 10 minutes of the first check-in will not write duplicate logs. Instead, the backend returns a friendly warning indicating they are already checked in.
* **Daily Working Time Calculation**: The system automatically registers the *first scan of the day* as the check-in time and the *last scan of the day* (if multiple scan entries exist) as the check-out time.

---

## 2. Staff & Employee Attendance

Staff attendance integrates automated AI verification with manual administrative overrides.

### Kiosk-Marked Logs
* When an employee scans at the kiosk, their check-in time is recorded in the `attendance` table.
* **Shift Rules**:
  * **Dynamic Shifts**: Evaluated dynamically based on each employee's specific `min_clock_in` setting first. If an employee has no custom shift set, it falls back to the global `shift_templates` (default start: `09:00:00`).
  * **Grace Period**: Calculated as `10 minutes` for individual custom shifts, or falls back to the global template grace boundary (default: `15 minutes`).
  * **Late Status**: Marked dynamically if check-in occurs after their specific shift start + grace window.
  * **Half-Day Status**: Marked dynamically if check-in occurs after their custom half-day boundary (calculated as custom shift start + 3 hours, or global fallback `12:00:00`).

### Manual Overrides & Logs
* **Location**: Portal Path -> `/staff/attendance`
* Admins and HR personnel can manually add or adjust employee logs. Manual entries write directly to the `staff_attendance_logs` table.
* **Unified Attendance Calendar**: Under `/staff/attendance?view=calendar`, the system merges both raw Face Recognition Kiosk logs and manually logged values into a comprehensive monthly view grid.

### Leaves Management
* Staff can apply for leave requests which are classified by `leave_types`.
* Admins approve or reject these requests via `/staff/leaves`, updating balances and payroll parameters.

---

## 3. Teacher App & Teacher Portal Attendance

Teachers have access to mobile-app or web-based portals to track shift schedules.

### App Check-In/Check-Out
* **Location**: Teacher Portal or Flutter App (`StaffAppController.php` endpoints).
* **Check-In**: Logs check-in to the `teacher_attendance` table and returns calculated lateness statistics:
  * **Late Notification**: App warns the teacher if check-in is past the scheduled lecture start + grace window.
  * **Late Buffer Warning**: Displays detailed messages indicating how many minutes late the entry was.
* **Check-Out Cooldown**: To prevent accidental double-clicks, teachers can only check out **at least 10 minutes after** checking in.
* **History Tracker**: Shows a rolling history (up to last 30 days) of mixed face-scans and manually checked attendance records.

---

## 4. Student & Academic Class Attendance

Student attendance tracking ensures that children are accounted for inside classrooms.

### Class Teacher Roll Call
* **Location**: Academics Path -> `/academics/attendance`
* Class teachers can select their assigned class and section, load the roster of enrolled students, and mark individual statuses:
  * `Present`
  * `Absent`
  * `Late`
  * Add custom text remarks.
* Log entries are saved in the main `attendance` table.
* Logs write an activity trail (`ActivityLog`) under key `attendance_marked` to prevent unauthorized adjustments.

---

## 5. Parent Portal Declarations

Parents play an active role in flagging absences and transport requirements.

### Advanced Declarations
* **Location**: Parent Portal Path -> `/parent/students/{id}/attendance`
* Parents can declare attendance for a future date (e.g. tomorrow).
* **Declare Options**:
  * `Present`
  * `Absent` (e.g. travel, personal reasons)
  * `Sick`
* **Medical Proof**: If marked as sick, parents can upload scanned doctor/medical certificates directly. Uploads are securely saved under `storage/uploads/attendance/<student_id>/`.
* **Transport Setup**: Parents can configure the bus setting (`use_bus_transport`) for today or tomorrow.

### Lockout Rules
* **Deadline Policy**: Parents can only declare or edit attendance before **11:00 PM on the day prior**. After this deadline, the UI locks the entry form.
* **School Override Privilege**: If school admins or class teachers manually mark a student's attendance on a given day, that record is locked from parent modifications.

---

## 6. Dynamic Integration with Transport Routing (Drivers)

Student attendance directly coordinates with driver routes to optimize morning/afternoon bus trips.

* **Location**: Driver App (`TransportController.php` query scopes).
* **Dynamic Route Filtering**:
  * When a driver initiates a bus trip, the driver's route list maps assigned student pickup/drop points.
  * The system joins the daily `attendance` table records.
  * **Exclusion Logic**: If a student is marked as `Absent` or has a pending leave application, they are **automatically hidden** from the driver's pickup/drop stops for that trip.
  * **Override**: If the parent explicitly checked `use_bus_transport = 1` during their declaration, the student is kept on the driver's route map regardless of check-in status.
