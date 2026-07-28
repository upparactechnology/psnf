# Detailed Modules & Pages Architecture

This document provides a comprehensive breakdown of all modules, sub-modules, database tables, view pages, and authorization scopes across the **PSNF ERP Platform**.

---

## 1. Administration & Governance Module
* **Purpose:** Core system management, user directory, access rules, security locks, and audit trails.

### Pages & Interfaces
1. **User Management (`/admin/users`)**
   - *Actions:* Create Staff/Teacher/Driver/Parent accounts, reset passwords, lock/unlock accounts.
   - *Access:* Super Admin, School Admin.
2. **Roles & Permissions (`/admin/roles`)**
   - *Actions:* Define role capabilities, assign permissions, restrict menu visibility.
   - *Access:* Super Admin (Full), School Admin (Read-Only).
3. **Branch Settings (`/admin/settings`)**
   - *Actions:* Configure school info, operational time windows (e.g. 7 AM - 6 PM access lock), logo, default branding.
   - *Access:* Super Admin, School Admin.
4. **Audit & Activity Logs (`/admin/logs`)**
   - *Actions:* Review security login history, failed password attempts, DRM file access logs, export history.
   - *Access:* Super Admin, School Admin, Manager.

---

## 2. Academic & IEP Management Module
* **Purpose:** Admission intake, disability profile mapping, individualized education program (IEP) tracking, timetable scheduling, and adaptive grading.

### Pages & Interfaces
1. **Student Directory (`/academic/students`)**
   - *Actions:* Manage profile, disability category (cognitive, physical, sensory), parent links, class assignment.
   - *Access:* Super Admin, School Admin, Manager, Teacher (Assigned Class).
2. **Online Admissions & Intake (`/academic/admissions`)**
   - *Actions:* Process web applications, document verification, stage approval pipeline.
   - *Access:* Super Admin, School Admin, Manager.
3. **IEP & Milestone Tracker (`/academic/iep`)**
   - *Actions:* Define IEP targets, quarterly milestone logging, therapy progress records.
   - *Access:* Teacher (Full), Manager (Review), Parent (Read-Only).
4. **Timetables & Room Allocations (`/academic/timetables`)**
   - *Actions:* Schedule classes, therapy sessions, room capacity checking.
   - *Access:* Super Admin, School Admin, Manager, Teacher/Student/Parent (Read-Only).
5. **Adaptive Evaluation & Report Cards (`/academic/examinations`)**
   - *Actions:* Cognitive-level grading, auto-generate report cards with QR codes.
   - *Access:* Teacher (Input), School Admin/Manager (Approve), Parent/Student (Read-Only).

---

## 3. Student Safety, Transport & GPS Live Tracking Module
* **Purpose:** Transport operations, driver monitoring, real-time GPS stream, geofence violations, and safety alerts.

### Pages & Interfaces
1. **Fleet & Driver Directory (`/transport/fleet`)**
   - *Actions:* Register vehicles, maintenance schedules, driver license checks.
   - *Access:* Super Admin, School Admin, Manager.
2. **Route & Stop Management (`/transport/routes`)**
   - *Actions:* Map bus routes, pick-up/drop-off stops, geofence zones.
   - *Access:* Super Admin, School Admin, Manager.
3. **Live GPS Bus Tracker (`/transport/live`)**
   - *Actions:* Stream real-time bus location, view speed indicators (>50 km/h warnings).
   - *Access:* Super Admin, School Admin, Manager, Teacher (Read-Only), Driver (App), Parent (Linked Bus Only).
4. **Safety & Speed Alerts Log (`/transport/alerts`)**
   - *Actions:* Over-speed logs, route divergence warnings, panic alerts.
   - *Access:* Super Admin, School Admin, Manager.

---

## 4. Attendance & Missing-Child Alert Module
* **Purpose:** Real-time cross-referencing between bus boarding records and classroom check-ins to eliminate child loss risks.

### Pages & Interfaces
1. **Classroom Attendance (`/attendance/classroom`)**
   - *Actions:* Daily check-in/check-out mark by teacher, scan barcode/QR.
   - *Access:* Teacher (Class), Manager, Admin.
2. **Bus Boarding Registry (`/attendance/bus`)**
   - *Actions:* Driver app check-in as students step onto bus.
   - *Access:* Driver (Route), Manager, Admin.
3. **Missing-Child Reconciliation Dashboard (`/attendance/reconciliation`)**
   - *Actions:* Auto-flag students checked onto bus but missing in class after 30 mins; dispatch emergency alerts.
   - *Access:* Super Admin, School Admin, Manager, Class Teacher.

---

## 5. Medical Logs & Clinical Incidents Module
* **Purpose:** Medical history, daily scheduled drug administration, and clinical incident reporting.

### Pages & Interfaces
1. **Student Medical Profiles (`/medical/profiles`)**
   - *Actions:* Record allergies, chronic conditions, emergency contacts.
   - *Access:* Nurse/Staff (Full), Teacher (Read-Only Allergies), Parent (Read-Only).
2. **Medication Administration Schedule (`/medical/medications`)**
   - *Actions:* Dosage timetables, double-staff signoff when administering medication.
   - *Access:* Nurse/Staff (Full), School Admin.
3. **Clinical Incident Log (`/medical/incidents`)**
   - *Actions:* Record injuries, seizures, behavioral episodes; automatic parent SMS/Email notification.
   - *Access:* Teacher, Nurse/Staff, Manager, Parent (Read-Only Alert).

---

## 6. HR, Leave Management & Payroll Module
* **Purpose:** Staff directory, leave requests, attendance tracking, and automated payslip generation.

### Pages & Interfaces
1. **Staff Directory (`/hr/staff`)**
   - *Actions:* Employee records, designation, contracts, assigned classes.
   - *Access:* Super Admin, School Admin, Manager.
2. **Leave Application & Approvals (`/hr/leaves`)**
   - *Actions:* Staff submit leave; multi-stage approval workflow (Manager → Admin).
   - *Access:* Staff/Teacher (Apply), Manager/Admin (Approve).
3. **Payroll & Salary Slips (`/hr/payroll`)**
   - *Actions:* Generate monthly salary slips based on attendance and leave logs.
   - *Access:* Super Admin, School Admin.

---

## 7. Canvas Document Engine & QR Verification
* **Purpose:** Drag-and-drop document design, variable SQL data binding, PDF rendering, and verification.

### Pages & Interfaces
1. **Document Designer (`/documents/designer`)**
   - *Actions:* Build visual templates (ID Cards, Report Cards, Certificates) with dynamic tags (`{{student_name}}`).
   - *Access:* Super Admin, School Admin.
2. **Batch Document Printer (`/documents/print`)**
   - *Actions:* Bulk generate PDFs with unique validation QR codes.
   - *Access:* Super Admin, School Admin, Manager, Staff.
3. **Public QR Verification (`/verify/{hash}`)**
   - *Actions:* Scan document QR to verify authenticity online without logging in.
   - *Access:* Public / Open Access.

---

## 8. Educational Games & Cognitive Analytics Module
* **Purpose:** Track cognitive game sessions, progress metrics, and conceptual errors.

### Pages & Interfaces
1. **Student Game Launcher (`/games/play`)**
   - *Actions:* Interactive games (Money counting, Safety signs, Behavioral choices).
   - *Access:* Student, Teacher (Demonstration).
2. **Cognitive Performance Analytics (`/games/analytics`)**
   - *Actions:* Graphs showing speed, accuracy, improvement over time.
   - *Access:* Teacher, Manager, Parent (Child metrics).