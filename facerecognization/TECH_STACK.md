# Technology Stack Specifications

This document outlines the software components, runtime environments, libraries, and frameworks chosen for the **AI Face Recognition Attendance System**, including the rationale behind each choice.

---

## 1. Backend Service Stack

```
┌───────────────────────────────────────────────────────────────┐
│                        BACKEND STACK                          │
├───────────────────────┬──────────────────────┬────────────────┤
│ Python 3.12 & FastAPI │  SQLAlchemy & MySQL  │ Docker Compose │
│                       │                      │                │
│ • Async Event Loop    │ • Connection Pooling │ • Containerized│
│ • Pydantic schemas    │ • Relational Storage │ • Multi-stage  │
│ • High concurrency    │ • Indexed Queries    │ • Dev/Prod     │
└───────────────────────┴──────────────────────┴────────────────┘
```

### Python 3.12
* **Role**: Primary programming language for the backend API services and Face AI pipeline wrappers.
* **Why**: Excellent ecosystem for machine learning and AI inference. Version 3.12 introduces performance improvements, better error tracing, and enhanced typing features.

### FastAPI (v0.111.0+)
* **Role**: RESTful API framework.
* **Why**: 
  * **Speed**: Asynchronous request handling allows it to perform on par with Node.js and Go.
  * **Automatic Documentation**: Leverages OpenAPI (Swagger/ReDoc) out of the box, speeding up frontend and Android client integration.
  * **Type Validation**: Uses Pydantic for request and response validation, guaranteeing data integrity.

### SQLAlchemy (v2.0+) & Alembic
* **Role**: Object-Relational Mapper (ORM) and database migration tool.
* **Why**: SQLAlchemy 2.0 provides native async database operations. Alembic manages safe, repeatable schema transitions across development, staging, and production environments.

### MySQL 8.0
* **Role**: Primary transactional relational database.
* **Why**: Secure and reliable. Supports native spatial and vector index extensions. Standard indexing makes query execution on structured metadata and logs highly efficient.

### JWT Authentication & Passlib
* **Role**: Identity verification and credential management.
* **Why**: JSON Web Tokens (JWT) allow stateless authentication across the Admin React Dashboard and the Android app client. Passlib with the `bcrypt` hashing module ensures secure, hashed storage of admin passwords.

---

## 2. Face AI & Recognition Pipeline

### InsightFace
* **Role**: High-efficiency deep learning face analysis library.
* **Why**: It is the industry standard for 2D/3D face analysis, containing optimized algorithms for face detection (RetinaFace) and face recognition (ArcFace).

### ArcFace (ONNX Optimized Model)
* **Role**: Deep face recognition feature extraction.
* **Why**: Generates highly discriminative 512-dimensional face embeddings. ArcFace maximizes decision boundaries between different faces, ensuring low False Acceptance Rates (FAR) and high True Acceptance Rates (TAR).
* **Model Format**: The models are bundled in `.onnx` format (e.g., `buffalo_l` or `buffalo_m` models) to run efficiently on CPU/GPU without needing complete PyTorch or TensorFlow environments.

### OpenCV (opencv-python-headless v4.9.0+)
* **Role**: Image reading, manipulation, scaling, and conversion.
* **Why**: Optimized C++ binaries wrapped in Python provide efficient preprocessing operations (resizing, crop normalization, and color space transformations) on raw video frames before inference.

### ONNX Runtime (v1.18.0)
* **Role**: Inference engine execution wrapper.
* **Why**: Runs models efficiently across different operating systems. In VPS environments, ONNX Runtime manages multithreaded execution to utilize all virtual CPU cores.

---

## 3. Android Application Client

```
┌───────────────────────────────────────────────────────────────┐
│                         ANDROID STACK                         │
├───────────────────────┬──────────────────────┬────────────────┤
│    Kotlin & MVVM      │    CameraX & ML Kit  │ Room & Retrofit│
│                       │                      │                │
│ • Coroutines & Flow   │ • Real-time Stream   │ • Local Cache  │
│ • StateFlow UI Bind   │ • Face Tracking      │ • Offline Sync │
│ • Clean Separation    │ • Liveness Prep      │ • REST Client  │
└───────────────────────┴──────────────────────┴────────────────┘
```

### Kotlin (v1.9.24+)
* **Role**: Primary development language.
* **Why**: Modern, type-safe, and interoperable language features. Essential for modern Android architectures.

### Jetpack CameraX
* **Role**: Android camera control API.
* **Why**: Simplifies camera management across various Android devices. Resolves device-specific aspect ratio and orientation bugs. The `ImageAnalysis` class allows access to raw YUV frames for liveness and embedding extraction.

### MVVM Architecture & Jetpack Lifecycle
* **Role**: UI presentation pattern.
* **Why**: Separates business logic from UI elements. Employs `ViewModel`, `LiveData`, and `StateFlow` to maintain states across device rotations.

### Retrofit 2 & OkHttp 3
* **Role**: HTTP client library.
* **Why**: Handles RESTful network communication, connection pooling, automatic JSON serialization/deserialization, and interceptor-based JWT token injection.

### Room Database (SQLite Wrapper)
* **Role**: Local offline persistent database.
* **Why**: Caches recognized employee lists and logs attendance events locally when the device loses network connectivity, syncing them when the connection is restored.

---

## 4. Admin Dashboard Stack

### React 18 & Vite
* **Role**: Frontend framework and build tool.
* **Why**: Vite offers fast build and reload times. React's component-based architecture makes it easy to build dynamic interfaces for data visualization.

### Material UI (MUI v5)
* **Role**: UI component library.
* **Why**: Implements Material Design 3 guidelines. Provides polished inputs, data grids, modals, and navigation components.

### Chart.js & React-Chartjs-2
* **Role**: Interactive data visualizations.
* **Why**: Renders lightweight, responsive graphs for attendance rates, late status metrics, and department breakdowns.

---

## 5. Deployment & Hosting Infrastructure

```
┌───────────────────────────────────────────────────────────────┐
│                        DEPLOYMENT STACK                       │
├───────────────────────┬──────────────────────┬────────────────┤
│      Ubuntu VPS       │   Docker Compose     │  Nginx & SSL   │
│                       │                      │                │
│ • Lightweight OS      │ • Multi-container    │ • Reverse Proxy│
│ • Firewall Protected  │ • Shared networks    │ • Let's Encrypt│
│ • Reliable Hosting    │ • Easy Scaling       │ • Static Host  │
└───────────────────────┴──────────────────────┴────────────────┘
```

### Ubuntu Server (22.04 LTS or 24.04 LTS)
* **Role**: Base operating system for VPS hosting.
* **Why**: High stability, large support community, and compatibility with modern containerization tools.

### Docker & Docker Compose
* **Role**: Container orchestration.
* **Why**: Isolates backend API containers, MySQL instances, and Nginx setups. Avoids "it works on my machine" issues by standardizing execution configurations.

### Nginx
* **Role**: Reverse proxy, load balancer, and SSL termination.
* **Why**: Serves built React dashboard static files directly and forwards dynamic `/api/v1/` routes to the FastAPI service.

### Let's Encrypt (Certbot)
* **Role**: SSL Certificate Authority.
* **Why**: Auto-renewing, free SSL certificates, ensuring all network operations (including face data transmission) use secure HTTPS.

For details on how these components are organized in the project folder structure, refer to [FOLDER_STRUCTURE.md](FOLDER_STRUCTURE.md).
