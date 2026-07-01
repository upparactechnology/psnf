# Complete Project Directory Layout

This document describes the structure, file layouts, and structural responsibilities for all code components in the repository.

---

## 1. Directory Tree Map

```
facerecognization/
├── backend/                            # Python FastAPI Server
│   ├── app/
│   │   ├── api/                        # Route controllers
│   │   │   ├── router.py               # Root Router mapping
│   │   │   └── v1/
│   │   │       ├── auth.py             # Auth endpoints
│   │   │       ├── employees.py        # Staff endpoints
│   │   │       ├── attendance.py       # Attendance logging
│   │   │       └── health.py           # Health check endpoint
│   │   ├── core/
│   │   │   ├── config.py               # Env configuration loading
│   │   │   ├── database.py             # SQLAlchemy Async Engine
│   │   │   └── security.py             # Password hashing, JWT utils
│   │   ├── crud/                       # Database query layers
│   │   │   ├── employee.py
│   │   │   └── attendance.py
│   │   ├── models/                     # SQLAlchemy Models
│   │   │   ├── employee.py
│   │   │   ├── attendance.py
│   │   │   └── auth.py
│   │   ├── schemas/                    # Pydantic Schemas
│   │   │   ├── employee.py
│   │   │   ├── attendance.py
│   │   │   └── auth.py
│   │   ├── services/                   # Business Logic
│   │   │   ├── attendance_engine.py    # Attendance processor
│   │   │   └── report_generator.py     # PDF/Excel exporter
│   │   ├── ai/                         # Face AI Pipeline
│   │   │   ├── pipeline.py             # RetinaFace/ArcFace pipeline
│   │   │   └── liveness.py             # Anti-spoofing engine
│   │   └── main.py                     # FastAPI entry point
│   ├── migrations/                     # Alembic Versioning files
│   └── tests/                          # Automated backend test suites
│
├── android/                            # Native Kotlin App
│   ├── app/
│   │   ├── src/
│   │   │   └── main/
│   │   │       ├── java/com/attendance/
│   │   │       │   ├── data/           # Database cache and REST client
│   │   │       │   ├── domain/         # Models and sync logic
│   │   │       │   └── ui/             # Views and ViewModels
│   │   │       └── res/                # XML UI Layouts
│   │   └── build.gradle.kts
│
└── dashboard/                          # React Admin Panel
    ├── src/
    │   ├── components/                 # Reusable UI controls
    │   ├── pages/                      # Screen layouts
    │   └── services/                   # Axios API services
    └── package.json
```

---

## 2. Component Directory Responsibilities

### 2.1 Backend Monolith (`backend/`)
* **`app/api/`**: Maps endpoints, validates incoming JSON headers, and calls service engines.
* **`app/core/`**: Central configuration, loading variables from `.env` files.
* **`app/crud/`**: Implements raw database querying patterns, separating database access from business logic.
* **`app/services/`**: Computes shift statuses, overtime limits, and exports aggregated spreadsheets.
* **`app/ai/`**: Wraps the ONNX runtime model inference thread group, executing detection, alignment, liveness scoring, and feature extraction.

### 2.2 Android Mobile Client (`android/`)
* **`data/`**: Manages the local SQLite database cache (Room DB) and handles REST requests (Retrofit client).
* **`domain/`**: Orchestrates WorkManager synchronizations, scheduling uploads when internet connectivity is available.
* **`ui/`**: Binds XML views to ViewModels, observing live streams and presenting verification dialogs.

### 2.3 Dashboard UI (`dashboard/`)
* **`components/`**: Standardizes card configurations, side navigation panels, and interactive data grids.
* **`pages/`**: Assembles components into functional screens (e.g., Employee directory, reports generator).

For comprehensive coding styles and clean architecture rules, refer to [17_CODING_STANDARDS.md](17_CODING_STANDARDS.md).
