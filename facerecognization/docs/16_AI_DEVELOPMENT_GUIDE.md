# AI Development and Implementation Guide

This document provides guidelines for AI coding assistants to build and deploy the **AI Face Recognition Attendance System** using the structured prompts in the `prompts/` directory.

---

## 1. Development Sequence

To ensure stable database migrations and API integrations, implement the modules in the following order:

```
┌───────────────────────────────────────────────────────────────┐
│                     DEVELOPMENT SEQUENCE                      │
├───────────────────────┬──────────────────────┬────────────────┤
│ 1. Database Setup     │ 2. Backend & AI API  │ 3. Client Apps │
│                       │                      │                │
│ • Schema Generation   │ • FastAPI Server     │ • Android App  │
│ • Alembic Migrations  │ • ArcFace Pipeline   │ • React UI     │
└───────────────────────┴──────────────────────┴────────────────┘
```

1. **Step 1: Database Setup** (`prompts/database.md`)
   * Generate schemas, relationships, constraints, and indexes.
   * Run the initial Alembic migration to create MySQL tables.
2. **Step 2: Backend API and AI Inference Setup** (`prompts/backend.md`, `prompts/face-ai.md`)
   * Build the FastAPI application structure, authentication layers, and crud operations.
   * Integrate the InsightFace, RetinaFace, and ArcFace ONNX inference engines.
3. **Step 3: Frontend Client Integration** (`prompts/android.md`, `prompts/dashboard.md`)
   * Implement the CameraX capture pipelines and local Room database in the Android application.
   * Build the React dashboard components and connect them to the backend API.
4. **Step 4: Deployment & Operations Setup** (`prompts/deployment.md`)
   * Configure Nginx proxy rules, SSL certificates, and Docker Compose configurations.

---

## 2. General AI Coding Standards

Assistant models must adhere to the following standards when generating code:
* **Strict Type Annotations**: Python code must use typing definitions (e.g., Pydantic schema validation, variable types). Kotlin code must use type declarations and avoid dynamic typing.
* **Error Handling**: Use explicit try-except-finally blocks. Do not swallow exceptions; log error traces and return clear HTTP status codes.
* **Asynchronous Operations**: Implement non-blocking asynchronous calls (`async/await` in Python/React, Coroutines and Flows in Kotlin) for database access, network connections, and file writing operations.
* **No Placeholders**: Avoid writing placeholder comments (e.g., `# TODO: implement this`). All generated code files must be complete and ready for production use.

For the detailed prompts corresponding to each module, refer to the [prompts/](file:///d:/business/facerecognization/prompts/) directory.
