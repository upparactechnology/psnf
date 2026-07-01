# AI Prompt: Database Schema & Migrations Setup

Use this prompt to instruct an AI assistant to initialize the database and migrations:

```markdown
## Objective
Generate the relational database schema, SQLAlchemy models, and Alembic migrations for the AI Face Recognition Attendance System in a MySQL 8.0 database.

## Requirements
1. **Engine**: Implement SQLAlchemy 2.0 async engine wrappers.
2. **Tables**:
   - `departments`: id (PK), name, code (UK).
   - `employees`: id (PK), employee_id (UK), first_name, last_name, email (UK), phone, department_id (FK), status (enum), created_at.
   - `face_embeddings`: id (PK), employee_id (FK), embedding_vector (TEXT containing JSON array of 512 floats), capture_angle, created_at.
   - `shifts`: id (PK), name, start_time, end_time, grace_period_minutes, half_day_minutes.
   - `employee_shifts`: id (PK), employee_id (FK), shift_id (FK), start_date, end_date.
   - `attendance_logs`: id (PK), employee_id (FK), clock_time, clock_type (enum), status (enum), device_id, is_synced (boolean).
   - `users`: id (PK), username (UK), password_hash, role (enum), created_at.
   - `audit_logs`: id (PK), user_id (FK), action, details (text), created_at.
3. **Constraints & Cascade Rules**:
   - Deleting an employee must cascade and delete associated face embeddings.
   - Ensure foreign key constraints are defined for all relationships.
4. **Indexes**:
   - `idx_attendance_employee_time`: Compound index on `attendance_logs(employee_id, clock_time)`.
   - `idx_employees_status`: Index on `employees(status)`.

## Target Folder Structure
```
backend/
├── app/
│   ├── core/
│   │   └── database.py                 # Async connection engine & SessionLocal
│   └── models/
│       ├── __init__.py                 # Import all models for Base
│       ├── employee.py                 # Department, Employee, FaceEmbedding models
│       ├── shift.py                    # Shift, EmployeeShift models
│       ├── attendance.py               # AttendanceLog model
│       └── auth.py                     # User, AuditLog models
├── migrations/                         # Alembic migrations folder
└── alembic.ini                         # Alembic configuration
```

## Coding Standards
- Use SQLAlchemy 2.0 Declarative Base syntax.
- Implement explicit type annotations for columns.
- Ensure all model relations are represented with `relationship()` definitions.

## Expected Deliverables
1. Async connection code (`database.py`).
2. Complete python code files for all models inside `app/models/`.
3. Auto-generated Alembic migration script in `migrations/versions/`.

## Acceptance Criteria
- Running `alembic upgrade head` succeeds against a local MySQL database, creating all tables, indexes, constraints, and relationships.
```
