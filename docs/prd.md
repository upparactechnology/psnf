# Product Requirements Document (PRD)

## 1. Executive Summary & Objectives
The goal of this project is to build a high-performance, modular, and local-first ERP platform tailored for NGO-operated special-needs schools. The system centralizes administrative tasking, automates document generation, provides safe transportation tracking, integrates educational game logging, and safeguards confidential student data.

---

## 2. Business Goals
* **Operational Centralization:** Unify data silos across multiple branches and administrative departments.
* **Student Safety & Transport Monitoring:** Prevent delays and unsafe driving practices through geofencing and real-time alerts.
* **Unified Parent Communication:** Provide real-time updates to parent portals regarding medical events, attendance, and educational progress.
* **Compliant Medical Recording:** Securely archive medical logs, incident notes, and therapy records.
* **Document Automation:** Eliminate paper templates through an integrated canvas document generator with QR validation.

---

## 3. Core Modules & Technical Requirements

### 3.1 Authentication & Role-Based Access Control (RBAC)
* **Inputs:** Email, Password, Session tokens.
* **Processing:**
  * Strict login rate-limiting (maximum 5 failed attempts before temporary lockout).
  * System-wide RBAC policies scoping users into Super Admin, School Admin, Manager, Teacher, Staff, Driver, Parent, and Student.
  * Access time restrictions applied dynamically based on configuration tables (e.g. staff blocking windows).
* **Outputs:** Encrypted session cookies, audit logs, authentication status.

### 3.2 Students & Individualized Education Programs (IEPs)
* **Inputs:** Admission profiles, disability classifications (cognitive, physical, sensory), therapy guidelines, IEP goals.
* **Processing:**
  * Standardized admissions intake process.
  * Document mapping to attach scanned verification papers.
  * Multi-stage IEP review cycles with calendar milestones.
* **Outputs:** Digital student profiles, active IEP documents, therapy tracking dashboards.

### 3.3 Parent Portal & Communications
* **Inputs:** Account registration codes, feedback forms, real-time message queries.
* **Processing:**
  * Secure child-linking verification codes.
  * Automated push updates for transportation milestones (e.g. "bus departed").
* **Outputs:** Real-time bus tracking map, attendance histories, medical notifications, game performance metrics.

### 3.4 Attendance Management
* **Inputs:** Classroom scanning, manual teacher inputs, driver check-in lists.
* **Processing:**
  * Real-time cross-referencing between classroom logs and bus check-ins.
  * Automatic missing-child alert generation when a student checked onto a bus does not appear in the classroom.
* **Outputs:** Daily attendance registry reports, missing-child alerts, administrative audit tables.

### 3.5 Timetables & Examinations
* **Inputs:** Teacher availability matrices, room occupancy capacities, student grouping definitions.
* **Processing:**
  * Conflict-prevention scheduling algorithms for classes and therapy sessions.
  * Custom grading profiles adjusting evaluation standards based on cognitive disability levels.
* **Outputs:** Class timetables, exam room schedules, customized report cards.

### 3.6 Human Resources, Leaves, & Payroll
* **Inputs:** Staff attendance records, contract rules, leave requests, tax matrices.
* **Processing:**
  * Leave approval chains (Teacher/Staff → Manager → Admin).
  * Auto-generation of monthly salary slips linked with attendance check-ins.
* **Outputs:** Pay slips, annual leave summaries, tax submission logs.

### 3.7 Transport, Drivers & GPS Tracking
* **Inputs:** GPS device signals (latitude, longitude, speed), vehicle details, driver profiles.
* **Processing:**
  * Real-time location streaming and geofence verification.
  * Over-speed alert triggering (> 50 km/h) sent to school managers.
* **Outputs:** Live tracking page, speed violations log, route optimization maps.

### 3.8 Medical Logs & Clinical Incidents
* **Inputs:** Medication schedules, dosage logs, nurse notes, incident reports.
* **Processing:**
  * Expiry tracking for medication batches.
  * Double-signature workflow for controlled medication administrations.
  * Instant emergency notifications to parents upon clinical incident logging.
* **Outputs:** Daily medication administration schedules, incident logs, medical allergy alerts on student dashboards.

### 3.9 Document Designer & Template Engine
* **Inputs:** Canvas templates (drag-and-drop elements), SQL data bindings.
* **Processing:**
  * Layout compiler merging database parameters (e.g. Student Name, Grade) into design blocks.
  * Dynamic PDF rendering engine with custom print sizing (A4, Letter, ID Card).
  * Unique validation QR code generation matching the document hash.
* **Outputs:** PDF downloads, batch-printed documents, QR verification portals.

### 3.10 Educational Games Integration
* **Inputs:** Learning game sessions (e.g., money-counting, safe-vs-unsafe behavior, safety signs).
* **Processing:**
  * Real-time logging of scores, completion times, and conceptual errors.
  * Aggregation of play data to measure cognitive improvements over time.
* **Outputs:** Student game performance analytics on Teacher and Parent dashboards.

---

## 4. Success Metrics & KPIs
* **Attendance Integrity:** Discrepancy rate between transportation check-ins and classroom logs < 0.1%.
* **Transport Notification Speed:** Alerts dispatching within < 5 seconds of speed or route violations.
* **Administrative Automation:** Time spent generating end-of-term reports and ID cards reduced by > 80%.
* **System Availability:** Uptime of the local XAMPP network environment > 99.9%.

---

## 5. Non-Functional Requirements

### 5.1 Security & Access Restrictions
* **Data Scoping:** Strict database-level multi-tenant isolation utilizing global filters.
* **Resource Security (Digital Rights Management):**
  * Real file paths are masked; media and documents must stream through controllers checking permission tokens.
  * Disable right-click, print, and save shortcuts on the staff portal.
  * Blur sensitive file views automatically on window focus loss.
  * Global and day-by-day access time windows.
  * Keyboard-level controls (e.g., block CTRL+P, PrintScreen, and screen capture shortcuts).
* **Network Isolation:** Support local-only deployment configurations for centers lacking stable internet connections.

### 5.2 Legal Compliance
* **Data Privacy:** Full compliance with COPPA (Children's Online Privacy Protection Rule) and regional child safety data processing regulations.
* **Accessibility:** WCAG 2.1 Level AA conformance across both teacher portals and parent dashboards.

