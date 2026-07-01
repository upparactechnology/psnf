# Project Directory Structure

This document outlines the folder layout for the **AI Face Recognition Attendance System**, organizing backend services, Android clients, web dashboards, and deployment configurations.

---

## 📁 Repository Layout

```
facerecognization/
├── README.md                           # Main index documentation
├── PROJECT_OVERVIEW.md                  # System overview, business rules
├── TECH_STACK.md                        # Technology choices & rationale
├── ARCHITECTURE.md                      # High-level architecture, pipelines
├── FOLDER_STRUCTURE.md                  # Repository directory map (This file)
│
├── backend/                            # FastAPI Server Application
│   ├── app/                            # Application Source Code
│   │   ├── core/                       # Security, config parameters, database connection
│   │   ├── models/                     # SQLAlchemy relational model definitions
│   │   ├── schemas/                    # Pydantic data serialization schemas
│   │   ├── crud/                       # Database operation interfaces
│   │   ├── api/                        # REST endpoint controllers (V1 routers)
│   │   ├── services/                   # Business engines (Attendance rules, reports)
│   │   ├── ai/                         # Inference wrapper (ONNX engine, liveness)
│   │   └── main.py                     # ASGI application entrypoint
│   ├── migrations/                     # Alembic database migration scripts
│   ├── tests/                          # Backend test suites (unit, integration, load)
│   ├── alembic.ini                     # Migration engine settings
│   ├── Dockerfile                      # Server image blueprint
│   ├── docker-compose.yml              # Multi-container service definitions
│   └── requirements.txt                # Python package dependency list
│
├── android/                            # Native Android Application (Kiosk & Enrollment)
│   ├── app/
│   │   ├── src/
│   │   │   ├── main/
│   │   │   │   ├── java/com/attendance/# Kotlin codebase
│   │   │   │   │   ├── data/           # Database cache (Room), Network client (Retrofit)
│   │   │   │   │   ├── domain/         # Domain entities, validation rules
│   │   │   │   │   ├── ui/             # Views, ViewModels, visual flows
│   │   │   │   │   └── App.kt          # Global Application state initializer
│   │   │   │   └── res/                # XML Layouts, colors, strings, drawables
│   │   │   └── test/                   # Local Kotlin test units
│   │   └── build.gradle.kts            # Application package rules
│   └── build.gradle.kts                # Project build requirements
│
├── dashboard/                          # React Admin Dashboard
│   ├── src/
│   │   ├── assets/                     # Logos, static resources
│   │   ├── components/                 # Shared dashboard controls (Layout, DataGrids)
│   │   ├── hooks/                      # Global React hooks
│   │   ├── pages/                      # Screen layouts (Dashboard, Employee, Shift)
│   │   ├── services/                   # REST API backend client instances
│   │   ├── store/                      # Context state managers
│   │   ├── App.jsx                     # Dashboard application entrypoint
│   │   └── main.jsx                    # React bootstrap source
│   ├── index.html                      # Entry HTML file
│   ├── package.json                    # Project metadata & packages
│   └── vite.config.js                  # Vite configuration parameters
│
├── docker/                             # Infrastructure Configuration
│   ├── nginx/
│   │   ├── conf.d/
│   │   │   └── default.conf            # Routing rules, SSL, headers configuration
│   │   └── Dockerfile                  # Customized web server container configuration
│   └── mysql/
│       └── my.cnf                      # Customized performance tuning specifications
│
├── docs/                               # Detailed Design Specifications
│   ├── 01_SYSTEM_REQUIREMENTS.md
│   ├── 02_DATABASE_DESIGN.md
│   ├── 03_BACKEND_API.md
│   ├── 04_FACE_ENROLLMENT.md
│   ├── 05_FACE_RECOGNITION.md
│   ├── 06_ANDROID_APP.md
│   ├── 07_ADMIN_DASHBOARD.md
│   ├── 08_ATTENDANCE_ENGINE.md
│   ├── 09_SHIFT_MANAGEMENT.md
│   ├── 10_REPORTS.md
│   ├── 11_SETTINGS.md
│   ├── 12_SECURITY.md
│   ├── 13_DEPLOYMENT.md
│   ├── 14_TESTING.md
│   ├── 15_FUTURE_ROADMAP.md
│   └── 16_AI_DEVELOPMENT_GUIDE.md
│
└── prompts/                            # Automated AI Prompts
    ├── backend.md
    ├── android.md
    ├── database.md
    ├── dashboard.md
    ├── face-ai.md
    └── deployment.md
```

---

## 2. Directory Design Rationale

### Async Backend Architecture (`backend/app/`)
* Separates data definition (`models/`), validation schema contracts (`schemas/`), and direct querying operations (`crud/`) to prevent circular dependencies in Python.
* Keeps core AI operations isolated in the `ai/` folder, allowing it to run within isolated CPU/GPU threading groups without blocking FastAPI's async request handler.

### Clean Client Separation (`android/app/`)
* Uses MVVM design principles to split views, view-states, and operations.
* Organizes files into `data/`, `domain/`, and `ui/` directories, making it easy to mock data sources during unit and UI testing.

### Component-Driven Dashboard (`dashboard/src/`)
* Separates page-level views (`pages/`) from reusable dashboard components (`components/`) to maximize code reusability.
* Isolate backend communication services (`services/`) to simplify testing and updates to API endpoint paths.

For the system requirements detailing feature specifications, refer to [01_SYSTEM_REQUIREMENTS.md](docs/01_SYSTEM_REQUIREMENTS.md).
