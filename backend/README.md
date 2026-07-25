# Web-Based Face Recognition Attendance System — Python FastAPI Backend

Enterprise-grade, high-performance, and secure face recognition attendance backend powered by **InsightFace (buffalo_l)**, **ONNX Runtime**, **FastAPI**, and **MySQL**.

---

## 📌 System Features

1. **Multi-Angle Face Registration (`POST /api/register-face`)**:
   - Accepts up to 10 base64 face photos captured from different angles.
   - Performs Laplacian blurriness check (`BLUR_THRESHOLD = 40.0`).
   - Performs HSV brightness validation (`MIN_BRIGHTNESS = 30.0`, `MAX_BRIGHTNESS = 230.0`).
   - Enforces single face detection (rejects multiple faces or zero faces).
   - Generates 512-dimensional L2 normalized ArcFace embedding vectors.
   - Stores embeddings in MySQL database (`face_embeddings` table) and preloads into in-memory search cache.

2. **Real-time Face Verification (`POST /api/verify-face`)**:
   - HTML5 webcam camera frame verification.
   - Fast cosine similarity search against cached embeddings in memory (< 5ms response time for 20+ employees).
   - Configurable Similarity Threshold: `0.78` (range 0.75 - 0.80).
   - Anti-Spoofing & Liveness checks (texture variance & bounding box aspect ratio).
   - Duplicate Attendance Cooldown: **10 Minutes** block window.
   - Logs device IP address and browser User-Agent string.

3. **Attendance History (`GET /api/attendance/history`)**:
   - Filterable attendance history logs with confidence score percentages and saved snapshot URLs.

---

## 🚀 How to Run Backend locally

### 1. Install Dependencies
```bash
cd backend
pip install -r requirements.txt
```

### 2. Run Database Migration
Execute the MySQL migration or import SQL script into database `psnf_drm`:
```bash
mysql -u root psnf_drm < database/schema_face_attendance.sql
```

### 3. Start FastAPI Service
```bash
uvicorn app:app --host 0.0.0.0 --port 8000 --reload
```

The service will start on `http://localhost:8000`.
FastAPI Swagger Documentation: `http://localhost:8000/docs`

---

## 🌐 Web Kiosk Pages

- **Dashboard**: `http://localhost/psnf/public/attendance/index.php`
- **Employee Face Registration**: `http://localhost/psnf/public/attendance/register.php`
- **Attendance Camera Kiosk**: `http://localhost/psnf/public/attendance/verify.php`
- **Attendance Logs**: `http://localhost/psnf/public/attendance/history.php`

---

## ⚙️ Configuration (`backend/config.py` / `.env`)

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `DB_HOST` | `127.0.0.1` | MySQL Database Host |
| `DB_PORT` | `3306` | MySQL Port |
| `DB_USER` | `root` | MySQL User |
| `DB_PASSWORD` | `""` | MySQL Password |
| `DB_NAME` | `psnf_drm` | Target Database Name |
| `SIMILARITY_THRESHOLD` | `0.78` | Cosine similarity cutoff for recognition (0.75-0.80) |
| `COOLDOWN_MINUTES` | `10` | Time window to block duplicate check-ins |
| `MODEL_NAME` | `buffalo_l` | InsightFace model suite |
| `BLUR_THRESHOLD` | `40.0` | Laplacian variance threshold for image sharpness |

---

## 🛡️ Production Deployment (2 vCPU / 8GB VPS)

1. Use **Gunicorn** with **Uvicorn Workers**:
```bash
gunicorn -w 4 -k uvicorn.workers.UvicornWorker app:app --bind 0.0.0.0:8000
```
2. Enable HTTPS via Nginx Reverse Proxy.
