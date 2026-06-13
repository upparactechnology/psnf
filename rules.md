# Engineering Rules & Standards

These rules are mandatory guidelines enforced across all modules in the PSNF codebase.

---

## 1. Architecture & Isolation Rules

### Rule 1: Every module is independent
Modules must be stored in distinct directories. They must not have cross-dependencies in their view code or local controller logic. If a module is deleted, the remaining applications must build and load without throwing unresolved namespace exceptions.

### Rule 2: Module ownership of business logic
A module owns its localized controllers, models, and service classes. Shared functionalities (e.g., authentication checks, session validation, file streaming wrappers) must be called via the Core Platform Services library.

### Rule 3: Shared database isolation through services
Direct joins across database tables belonging to different core modules (e.g., joining `payroll` directly to `medical_records` in a raw query) are prohibited. Instead, retrieve target IDs through API endpoints or shared service contracts.

---

## 2. Database & Schema Policies

### Rule 4: Mandatory multi-school support (Multi-Tenancy)
Every database table containing school-level operational logs must incorporate:
* `school_id` (VARCHAR(36) or INT, foreign key to `schools`)
* `branch_id` (VARCHAR(36) or INT, foreign key to `branches`)

*Database Scoping Guideline:* Every SELECT query must append tenant matching clauses:
```sql
SELECT * FROM students WHERE school_id = :session_school_id AND branch_id = :session_branch_id AND deleted_at IS NULL;
```
If using an ORM or custom query builder, global scopes must automatically append these filters.

### Rule 5: No hard deletes
The database does not delete records using `DELETE` SQL commands.
* Every table must contain a nullable `deleted_at` timestamp.
* When a deletion is requested, update `deleted_at = NOW()` and record the operator's ID in `updated_by`.

### Rule 6: Primary Key & Metadata Standards
* Primary keys must utilize `id` (bigint AUTO_INCREMENT or UUID).
* Every table must contain the following columns:
  * `id`
  * `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  * `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
  * `created_by` INT/UUID NULL (Referencing `users.id`)
  * `updated_by` INT/UUID NULL (Referencing `users.id`)
  * `deleted_at` TIMESTAMP NULL

---

## 3. Auditing & Security Controls

### Rule 7: Mandatory Audit Logging
An event logging framework must capture all mutating operations (CREATE, UPDATE, DELETE) and authentication attempts.
* **Fields captured:** `id`, `user_id` (admin/staff identifier), `event_name` (e.g. `resource_viewed`), `metadata` (JSON detailing key identifiers), `ip_address`, `user_agent`, `created_at`.
* **Execution:** Log actions asynchronously or via event listeners to prevent database query blocks during primary saves.

### Rule 8: Endpoint Permissions Scoping
No route may exist without authorization verification.
* Controllers must define checking middleware inside their routing hooks.
* Use role-permission mapping lookup tables (`roles`, `permissions`, `permission_role`).

### Rule 9: Access Window Locking (Staff Restrictions)
System access for staff roles must validate active schedules:
* Read daily configurations from `settings` table keys (`access_mon_status`, `access_mon_start`, `access_mon_end`, etc.).
* Access is blocked automatically at the router level if the current server timestamp falls outside active windows.

---

## 4. Digital Rights Management (DRM) & Security Protocols

### Rule 10: Masked File Streaming
Files must never be linked directly via public folder paths.
* Implement a secure file controller routing: `/staff/resource/stream?id={id}&token={token}`.
* Validate that:
  1. The user has active folder/file assignments mapped in `resource_staff` or `folder_staff`.
  2. The browser request matches security limits (e.g., blocking mobile if `block_mobile` settings are active).
* Output file bytes directly to response buffers using matching mime headers (`application/pdf`, `video/mp4`) and standard security flags:
  ```php
  header("Content-Disposition: inline; filename=\"resource\"");
  header("X-Content-Type-Options: nosniff");
  header("Content-Security-Policy: default-src 'none'; frame-ancestors 'self'");
  ```

### Rule 11: Visual Watermarking & Focus Loss Blurring
* Embed SVG watermark overlays on resource preview panels showing the operator's detail: `{{username}} | {{ip_address}} | {{timestamp}}`.
* Preview containers must listen for window blurring events (`window.onblur`) and dynamically apply high-radius CSS blur filters (`filter: blur(15px);`) to block unauthorized visual capture.

### Rule 12: Keyboard shortcut interception
Frontend script managers must register event hooks blocking standard export and capturing actions:
* Intercept `keydown` keyboard signals.
* Block the default behaviors and show custom warning popups for:
  * Print commands: `CTRL + P` / `CMD + P`.
  * Save commands: `CTRL + S` / `CMD + S`.
  * Inspector consoles: `F12` / `CTRL + SHIFT + I` / `CMD + OPT + I`.
  * Blur the view immediately if `PrintScreen` (key code 44 / key `PrintScreen`) is tapped, logging a potential breach alert to the audit database.

