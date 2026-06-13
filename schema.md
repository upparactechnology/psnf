# Database Schema Specifications

This schema structure unifies the existing Core Resource Management (DRM) schema with the broader modular ERP database requirements. All tables must support audit columns: `created_at`, `updated_at`, `created_by`, `updated_by`, and `deleted_at` where applicable.

---

## 1. Core Digital Rights Management (DRM) Tables

These tables exist in the current database instance (`psnf_drm`):

### 1.1 `admins`
Stores platform-level Super Admins and School Admins.
* `id` INT AUTO_INCREMENT (PK)
* `name` VARCHAR(120) NOT NULL
* `email` VARCHAR(190) UNIQUE NOT NULL
* `password` VARCHAR(255) NOT NULL
* `is_active` TINYINT(1) DEFAULT 1
* `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 1.2 `staff`
Stores teacher, staff, and therapist user records.
* `id` INT AUTO_INCREMENT (PK)
* `name` VARCHAR(120) NOT NULL
* `email` VARCHAR(190) UNIQUE NOT NULL
* `password` VARCHAR(255) NOT NULL
* `is_active` TINYINT(1) DEFAULT 1
* `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 1.3 `folders`
Organizes files/resources hierarchically.
* `id` INT AUTO_INCREMENT (PK)
* `parent_id` INT NULL (FK -> `folders.id` ON DELETE SET NULL)
* `name` VARCHAR(190) NOT NULL
* `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 1.4 `resources`
Metadata for files uploaded into the secure stream registry.
* `id` INT AUTO_INCREMENT (PK)
* `folder_id` INT NULL (FK -> `folders.id` ON DELETE SET NULL)
* `title` VARCHAR(190) NOT NULL
* `file_name` VARCHAR(255) NOT NULL
* `stored_name` VARCHAR(255) NOT NULL (Masked disk storage file name)
* `mime_type` VARCHAR(190) NOT NULL (e.g. `application/pdf`)
* `file_size` BIGINT DEFAULT 0
* `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 1.5 `folder_staff`
Many-to-many lookup table for folder permissions.
* `folder_id` INT (FK -> `folders.id` ON DELETE CASCADE)
* `staff_id` INT (FK -> `staff.id` ON DELETE CASCADE)
* *Composite PK:* (`folder_id`, `staff_id`)

### 1.6 `resource_staff`
Many-to-many lookup table for individual resource overrides.
* `resource_id` INT (FK -> `resources.id` ON DELETE CASCADE)
* `staff_id` INT (FK -> `staff.id` ON DELETE CASCADE)
* *Composite PK:* (`resource_id`, `staff_id`)

### 1.7 `settings`
Key-value repository storing global configurations, mobile blocks, and access hours.
* `id` INT AUTO_INCREMENT (PK)
* `key` VARCHAR(120) UNIQUE NOT NULL (e.g. `block_mobile`, `global_access_start`)
* `value` TEXT NULL

### 1.8 `activity_logs`
Centralized action audit logs.
* `id` BIGINT AUTO_INCREMENT (PK)
* `admin_id` INT NULL (FK -> `admins.id`)
* `staff_id` INT NULL (FK -> `staff.id`)
* `event` VARCHAR(190) NOT NULL (e.g. `resource_viewed`, `staff_login_failed`)
* `meta` TEXT NULL (JSON string representation of parameters)
* `ip` VARCHAR(64) NULL
* `user_agent` TEXT NULL
* `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 1.9 `staff_favorites`
Enables staff members to flag resource folders or files.
* `id` INT AUTO_INCREMENT (PK)
* `staff_id` INT (FK -> `staff.id` ON DELETE CASCADE)
* `resource_id` INT NULL (FK -> `resources.id` ON DELETE CASCADE)
* `folder_id` INT NULL (FK -> `folders.id` ON DELETE CASCADE)
* *Constraints:* UNIQUE index on (`staff_id`, `resource_id`), UNIQUE index on (`staff_id`, `folder_id`)

---

## 2. Expanded Multi-Tenant ERP Tables

These tables are defined for the modular ERP layers:

### 2.1 `schools` & `branches`
* **`schools`**
  * `id` VARCHAR(36) or INT AUTO_INCREMENT (PK)
  * `name` VARCHAR(190) NOT NULL
  * `subdomain` VARCHAR(90) UNIQUE
  * `is_active` TINYINT(1) DEFAULT 1
* **`branches`**
  * `id` VARCHAR(36) or INT AUTO_INCREMENT (PK)
  * `school_id` (FK -> `schools.id` ON DELETE CASCADE)
  * `name` VARCHAR(190) NOT NULL

### 2.2 `students` & `parents`
* **`students`**
  * `id` VARCHAR(36) (PK)
  * `school_id` (FK -> `schools.id`)
  * `branch_id` (FK -> `branches.id`)
  * `first_name` VARCHAR(90) NOT NULL
  * `last_name` VARCHAR(90) NOT NULL
  * `disability_type` VARCHAR(100) (e.g. `Autism Spectrum Disorder`)
  * `care_instructions` TEXT NULL
  * `status` VARCHAR(30) (e.g. `Enrolled`)
* **`parents`**
  * `id` VARCHAR(36) (PK)
  * `first_name` VARCHAR(90) NOT NULL
  * `last_name` VARCHAR(90) NOT NULL
  * `phone` VARCHAR(30) NOT NULL
  * `email` VARCHAR(190) UNIQUE NOT NULL
  * `password` VARCHAR(255) NOT NULL
* **`parent_student`**
  * `parent_id` (FK -> `parents.id` ON DELETE CASCADE)
  * `student_id` (FK -> `students.id` ON DELETE CASCADE)
  * *Composite PK:* (`parent_id`, `student_id`)

### 2.3 `routes`, `vehicles` & `drivers`
* **`vehicles`**
  * `id` INT AUTO_INCREMENT (PK)
  * `school_id` (FK -> `schools.id`)
  * `plate_number` VARCHAR(30) NOT NULL
  * `model` VARCHAR(90)
* **`drivers`**
  * `id` INT AUTO_INCREMENT (PK)
  * `name` VARCHAR(120) NOT NULL
  * `license_number` VARCHAR(90) NOT NULL
  * `phone` VARCHAR(30) NOT NULL
* **`routes`**
  * `id` INT AUTO_INCREMENT (PK)
  * `school_id` (FK -> `schools.id`)
  * `route_name` VARCHAR(120) NOT NULL
  * `vehicle_id` INT (FK -> `vehicles.id`)
  * `driver_id` INT (FK -> `drivers.id`)
  * `geofence_polygon` TEXT NULL (JSON mapping coordinate points)

### 2.4 `student_attendance`
* `id` BIGINT AUTO_INCREMENT (PK)
* `student_id` (FK -> `students.id` ON DELETE CASCADE)
* `date` DATE NOT NULL
* `status` ENUM('present', 'absent', 'late') NOT NULL
* `absence_reason` VARCHAR(190) NULL
* `checked_in_by` INT/UUID NOT NULL

### 2.5 `medical_incidents` & `medication_administration`
* **`medical_incidents`**
  * `id` INT AUTO_INCREMENT (PK)
  * `student_id` (FK -> `students.id`)
  * `incident_type` VARCHAR(90) (e.g. `Seizure`, `Fall`)
  * `description` TEXT NOT NULL
  * `action_taken` TEXT NOT NULL
  * `logged_by` INT (FK -> `staff.id`)
  * `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **`medication_administration`**
  * `id` INT AUTO_INCREMENT (PK)
  * `student_id` (FK -> `students.id`)
  * `medication_name` VARCHAR(120) NOT NULL
  * `dosage` VARCHAR(60) NOT NULL
  * `administered_by` INT (FK -> `staff.id`)
  * `witnessed_by` INT NULL (FK -> `staff.id`)
  * `administered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### 2.6 `game_sessions` (Educational Play Telemetry)
Tracks output statistics generated by students during HTML5 gameplay.
* `id` BIGINT AUTO_INCREMENT (PK)
* `student_id` (FK -> `students.id` ON DELETE CASCADE)
* `game_key` VARCHAR(60) NOT NULL (e.g. `money-counting`, `safe-vs-unsafe`)
* `score` INT NOT NULL
* `time_spent_seconds` INT NOT NULL
* `total_attempts` INT NOT NULL
* `wrong_answers_log` TEXT NULL (JSON mapping mistake classifications)
* `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

