# Coding Standards and Architecture Specification

This document defines the coding conventions, architectural design patterns, testing practices, and Git workflows for the **AI Face Recognition Attendance System**.

---

## 1. Naming Conventions

Consistency in naming styles across different platforms is critical for project readability:

| Scope | Convention | Case Style | Example |
| :--- | :--- | :--- | :--- |
| **Backend Folders** | Singular or plural categories | snake_case | `core/`, `schemas/` |
| **Backend Files** | Module based naming | snake_case | `employee_routes.py` |
| **Python Classes** | Standard nouns | PascalCase | `DatabaseConnection` |
| **Python Functions/Variables**| Verbs / Descriptive | snake_case | `calculate_overtime()` |
| **Android Packages** | Domain prefixed | lowercase | `com.attendance.data.local`|
| **Android Classes** | Noun with suffix | PascalCase | `KioskViewModel` |
| **Database Tables** | Plural nouns | snake_case | `attendance_logs` |
| **Database Columns** | Singular nouns | snake_case | `clock_time` |
| **REST Endpoints** | Plural nouns | kebab-case | `/api/v1/employee-profiles`|

---

## 2. Architectural Design Patterns

```
┌───────────────────────────────────────────────────────────────┐
│                      CLEAN ARCHITECTURE                       │
├───────────────────────┬──────────────────────┬────────────────┤
│       UI Layer        │    Service Layer     │Data/Repo Layer │
│                       │                      │                │
│ • React Views (Web)   │ • Attendance Logic   │ • SQLAlchemy   │
│ • Activity/VM (Android) • Shift Calculators  │ • SQLite Room  │
│                       │ • Validation Engine  │ • Vector Store │
└───────────────────────┴──────────────────────┴────────────────┘
```

### 2.1 Clean Architecture & SOLID Principles
* **Separation of Concerns**: Keep business rules isolated from UI components and frameworks.
* **Single Responsibility**: Each class or module must handle a single, clear objective.
* **Dependency Injection (DI)**: In the backend, use FastAPI's `Depends` for dependency injection. In the Android application, use constructor injection to decouple classes and facilitate mock testing.

### 2.2 Repository Pattern & Service Layer
* **Repository Layer**: Handles direct database operations, abstracting data store implementations (e.g., SQLAlchemy queries, Room DAO calls) from higher-level logic.
* **Service Layer**: Implements business rules (e.g., calculating shift grace periods, evaluating overtime hours).

---

## 3. Code Quality, Logging, and Error Control

### 3.1 Exception Handling
* Do not use generic exceptions (e.g., `except Exception:`). Always catch specific exceptions (e.g., `SQLAlchemyError`, `IOException`).
* Backend controllers must capture exceptions and map them to standard HTTP status codes using the API error response wrapper.

### 3.2 Logging Standards
* Log entries must contain structured fields: Timestamp, Log Level (`INFO`, `WARNING`, `ERROR`, `CRITICAL`), Module/File Name, and Message.
* **Data Privacy Guard**: Do not log raw face embedding vectors or sensitive user credentials in application logs.

---

## 4. Git Workflows and Commit Rules

### 4.1 Git Branching Model
* `main`: Production-ready branch. Direct commits are blocked; updates require approved Pull Requests.
* `develop`: Integration branch for new features.
* Feature Branches: Named `feature/<issue-id>-<description>` (e.g., `feature/att-84-offline-sync`).

### 4.2 Commit Messages (Conventional Commits)
* Format: `<type>(<scope>): <short description>`
* **Types**:
  * `feat`: A new feature implementation.
  * `fix`: A bug fix.
  * `docs`: Documentation updates.
  * `refactor`: Code improvements that do not change functionality.

For detailed guidelines on UI design tokens and components, refer to [18_UI_UX_GUIDE.md](18_UI_UX_GUIDE.md).
