# Feature Inventory & Specifications

---

## 1. Students & Enrollment
* **Admissions Workflow:**
  * Multi-stage intake pipeline: Applied → Under Review → Interview/Assessment Scheduled → Provisionally Admitted → Enrolled.
  * Integration of a customizable student intake form supporting file uploads for birth certificates, diagnostic certificates, and legal guardian papers.
* **Profile Management:**
  * Detailed diagnostic record mapping: Disability category selection (e.g. ASD, Down Syndrome, Cerebral Palsy, ADHD, Dyslexia).
  * Care profile details: Food allergies, physical triggers, sensory sensitivities, communication style (verbal vs non-verbal, AAC device user), and mobility aids.
* **IEP Tracker:**
  * Visual interface to set quarterly milestones for speech therapy, occupational therapy, motor skills, and social development.
  * Multi-teacher commenting system with history log.

---

## 2. Parent Portal & Communications
* **Real-time Bus Tracking:**
  * Integrated interactive map display utilizing Leaflet.js / OpenStreetMap.
  * Live tracking showing current bus coordinates, path history, estimated arrival times, and status ("Approaching", "Delayed", "Arrived").
* **Communications & Alerts:**
  * Real-time notice boards with categories: Urgent, Announcements, Medical, Transport, Academics.
  * Direct chat system with teachers, utilizing secure WebSockets or polling with messaging limits.

---

## 3. Attendance Management
* **Student Attendance:**
  * Interactive grid layouts to mark students present/absent with diagnostic reasons (e.g., therapy, sensory overload day, medical illness).
  * Real-time sync displaying check-in statuses registered by transport drivers during morning pickups.
* **Staff Attendance:**
  * Biometric or web-based clock-in/clock-out, capturing browser client IP and location data.
  * Automatic synchronization with leave balances and payroll processing engines.

---

## 4. Transport, Drivers, & Safety
* **GPS Tracking & Logging:**
  * Lat/long coordinates transmitted by drivers' mobile tracking interface every 10 seconds.
  * Historical log of paths, stop durations, and timing details per route.
* **Safety Alerting Rules:**
  * Speed monitoring: Trigger alarm notifications when speed exceeds 50 km/h.
  * Route drift/geofencing: Alert administrators if the bus goes off-route by more than 500 meters.
  * Driver check-in checks: Track driver health, vehicle start checks (tires, fuel, brakes checklist), and validation records.

---

## 5. Medical Records & Incident Logging
* **Medication Inventory & Logs:**
  * Database records of medications submitted by parents, capturing names, strengths, expiration dates, and custom warnings.
  * Automatic warning badges highlighting inventory packages within 30 days of expiry.
* **Medication Logs:**
  * Time-stamped dosage tracking with double-authentication checks (requiring signatures/PINs from both the administering nurse and a witness for high-risk medications).
* **Incident Reporting:**
  * Clinical log creation for seizures, injuries, behavioral breakdowns, or sensory overload occurrences.
  * Triggers immediate push email/SMS messages to verified parent contacts.

---

## 6. Secure Digital Resource Management (DRM)
* **Tokenized Streaming:**
  * Under-the-hood file system isolation: Files stored in `storage/private` are hidden from public directories.
  * Streaming access restricted to controller pathways validating expiring tokens (tokens expire in 15 minutes).
* **Visual Protections:**
  * Dynamically generated SVG overlay watermarks displaying the viewing user's name, email, current IP address, and accessing timestamp.
  * File preview windows utilizing wrapper layers that blur the entire viewport automatically on window focus loss.
* **Interactive Barriers:**
  * JavaScript barriers blocking the browser context menu (right-click block), drag-and-drop actions, and clipboard copy operations.
  * Global and day-by-day access time restrictions (configured by School Admin) preventing staff logins during off-hours.
  * Active window event locks to block print and capture hotkeys:
    * Intercepting and canceling keyboard keys: `CTRL + P`, `PrintScreen` key, `CTRL + S`, `CMD + P`, and browser developer tool keys (`F12`, `CTRL + SHIFT + I`).

---

## 7. Educational Games Integration
* **HTML5 Games Support:**
  * Single-sign-on (SSO) context linking the student portal directly to game launchers:
    * **Money Counting:** Visual interface for coin/note calculation.
    * **Safe vs Unsafe Behavior:** Drag-and-drop scenarios testing safety rules.
    * **Safety Signs:** Multiple choice recognition of traffic and emergency signs.
    * **Sentence Builder:** Visual sorting of word cards into coherent sentences.
* **Analytics Harvesting:**
  * Games send payload structures upon session completion to the parent system via AJAX:
    ```json
    {
      "student_id": "UUID-123",
      "game_key": "money-counting",
      "score": 85,
      "time_spent_seconds": 240,
      "total_attempts": 12,
      "wrong_answers_log": ["dime_value_error", "quarter_addition_error"]
    }
    ```
  * Compile telemetry profiles into progress metrics displayed on parent/teacher dashboards.

---

## 8. Canva-Style Document Designer
* **Canvas Template Editor:**
  * Web-based design board supporting text boxes, shapes, lines, images, and variables (`{{student_name}}`, `{{academic_year}}`, `{{school_logo}}`).
* **Bulk Generation Processing:**
  * Selection checkboxes in data tables to execute batch generation routines, wrapping output elements into a multi-page PDF document.
* **Verification Infrastructure:**
  * Embedded unique QR codes on compiled documents linking to a verification landing page hosted on the public domain, checking authenticity.

