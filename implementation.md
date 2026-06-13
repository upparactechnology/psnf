# Implementation Roadmap

This multi-phase roadmap describes the development sequence from core architecture to production hardening.

---

## Phase 1: Foundations & Architecture
* **Objectives:** Initialize the shared database system and set up multi-school/multi-branch global scopes.
* **Database Migrations:** Create `schools`, `branches`, and the global `settings` key-value store.
* **Backend:** Build the DB client library with automatic tenant matching injection (`WHERE school_id = ?`).
* **Frontend:** Establish the CSS variable layout (from the design guide), the module launcher shell, and global high contrast switches.
* **Verification:** Run multi-tenant query unit tests verifying that user A cannot retrieve school B records.

---

## Phase 2: Authentication & RBAC
* **Objectives:** Establish secure user authentication and granular access controls.
* **Database Migrations:** Import schemas for `admins`, `staff`, and `activity_logs`. Create tables for `roles`, `permissions`, and `permission_role`.
* **Backend:** Implement core authentication controls, session timeout middleware (20 minutes), rate-limit filters, and RBAC endpoint checkers.
* **Frontend:** Formulate login portals for admin and staff portals with automatic error triggers.
* **Verification:** Verify that a user fails authorization checks when accessing an admin route with staff credentials.

---

## Phase 3: DRM Subsystem Integration
* **Objectives:** Integrate the core PHP MVC secure digital resource management module.
* **Database Migrations:** Import the existing structures for `folders`, `resources`, `folder_staff`, `resource_staff`, and `staff_favorites`.
* **Backend:**
  * Build the file streaming controller `/staff/resource/stream` masking real disk URLs.
  * Integrate check middleware validating staff access schedules and device rules (e.g. `block_mobile` or `access_mon_status`).
* **Frontend:**
  * Build the secure preview panel overlaying SVG watermarks and blurring on focus loss (`onblur`).
  * Add the script hooks disabling right-click context menus, and keys (`CTRL+P`, `PrintScreen`, `F12`).
* **Verification:** Test document access using an unauthorized session token to confirm a `403 Forbidden` response is returned.

---

## Phase 4: Students & Parent Portal
* **Objectives:** Deploy profile management structures, individualized education programs (IEPs), and parent landing hubs.
* **Database Migrations:** Create `students`, `parents`, and `parent_student`.
* **Backend:** Deploy CRUD services for student admissions pipelines and quarterly IEP milestone revisions.
* **Frontend:** Build the student onboarding wizard, the diagnostic care planner, and the parent dashboard layout.
* **Verification:** Create a test student diagnostic record and verify that it updates and logs to the audit tables correctly.

---

## Phase 5: Attendance Tracker
* **Objectives:** Deliver classroom registries synced with transport logs.
* **Database Migrations:** Create `student_attendance`.
* **Backend:** Implement automated missing-child verification logic checking transport scan lists against classroom entries.
* **Frontend:** Build the classroom attendance registry grid and missing-student popup indicators.
* **Verification:** Mock a scenario where a student is marked check-in on a bus but marked absent in class, confirming that an alert triggers.

---

## Phase 6: Transport & Live Tracking
* **Objectives:** Deploy GPS vehicle tracking, route geofencing, and parent navigation maps.
* **Database Migrations:** Create `vehicles`, `drivers`, and `routes`.
* **Backend:** Implement endpoint targets receiving vehicle lat/long updates, and geofence computation algorithms checking polygon ranges.
* **Frontend:** Render Leaflet maps showing real-time bus movements on the parent dashboard.
* **Verification:** Push custom coordinates exceeding geofence boundaries to check if route-drift warnings dispatch to admin consoles.

---

## Phase 7: Medical & Incidents
* **Objectives:** Deploy secure medication logs and double-authentication nurse checklists.
* **Database Migrations:** Create `medical_incidents` and `medication_administration`.
* **Backend:** Implement batch expiry warning checks, nurse PIN double-check routines, and clinical log email hooks.
* **Frontend:** Build the nurse dashboard listing active dosage rosters and incident submission panels.
* **Verification:** Log a high-risk medication administration check to verify that a witness confirmation PIN is successfully requested and logged.

---

## Phase 8: Educational Play Integration
* **Objectives:** Bind HTML5 games telemetry directly to student progress trackers.
* **Database Migrations:** Create `game_sessions`.
* **Backend:** Build the telemetry ingestion API `/api/game/telemetry` tracking scores, completion timers, and error profiles.
* **Frontend:** Integrate game shells (money counting, safety signs, etc.) and link telemetry triggers.
* **Verification:** Play through the money-counting game to verify that completion reports are correctly written to `game_sessions`.

---

## Phase 9: Canva-Style Document Designer
* **Objectives:** Deliver the design canvas editor, variables binder, and bulk PDF generation framework.
* **Backend:** Deploy layout compilers translating canvas JSON trees into HTML templates, PDF rendering pipelines, and QR generation engines.
* **Frontend:** Build the visual drag-and-drop workspace containing text blocks and database bindings.
* **Verification:** Generate a test bulk PDF of 50 student ID cards to verify that compile rendering durations remain under 5 seconds.

---

## Phase 10: Messaging & Reports
* **Objectives:** Deploy system announcements and module reporting suites.
* **Backend:** Create query managers generating Excel and PDF files for attendance summaries, transport safety logs, and medical records.
* **Frontend:** Deploy announcement creation cards and custom chart dashboards.
* **Verification:** Export an automated attendance report for 100 students to confirm it renders correctly.

---

## Phase 11: Production Hardening
* **Objectives:** Execute security diagnostics, performance optimization, and local-first backup sync configurations.
* **Actions:**
  * Perform penetration tests verifying DRM bypass blocks and session lock constraints.
  * Optimize database indexing on heavy search columns (`activity_logs.event`, `student_attendance.date`).
  * Deploy automated backup scripts dumping structural snapshots.

