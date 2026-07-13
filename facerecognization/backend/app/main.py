import logging
import time
import uuid
from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from app.core.config import settings

# Structured Logging Configuration
logging.basicConfig(
    level=settings.LOG_LEVEL,
    format="%(asctime)s [%(levelname)s] %(name)s - %(message)s",
    handlers=[logging.StreamHandler()]
)
logger = logging.getLogger("attendance_backend")

from app.api.router import api_router

app = FastAPI(
    title="AI Face Recognition Attendance API",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc"
)

app.include_router(api_router)

@app.on_event("startup")
async def startup_event():
    import socket
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
    except Exception:
        ip = "127.0.0.1"
        
    print("\n" + "="*65)
    print(f"  [STARTUP] AI ATTENDANCE BACKEND ACTIVE")
    print(f"  [STARTUP] Connect your physical phone by entering this Host URL:")
    print(f"     http://{ip}:8000")
    print("="*65 + "\n")

    # DB diagnostics
    from app.core.database import SessionLocal
    from sqlalchemy import text
    try:
        async with SessionLocal() as db:
            res_emp = await db.execute(text("SELECT COUNT(*) FROM employees"))
            count_emp = res_emp.scalar()
            res_face = await db.execute(text("SELECT COUNT(*) FROM face_embeddings"))
            count_face = res_face.scalar()
            print(f"[DIAGNOSTIC] Registered Employees: {count_emp}")
            print(f"[DIAGNOSTIC] Enrolled Face Profiles: {count_face}")
            if count_face > 0:
                res_v = await db.execute(text("SELECT id, employee_id, CHAR_LENGTH(embedding_vector) FROM face_embeddings LIMIT 3"))
                for row in res_v.fetchall():
                    print(f"  - FaceEmbedding ID {row[0]}: Employee FK {row[1]}, Vector length: {row[2]} chars")
    except Exception as e:
        print(f"[DIAGNOSTIC] DB Diagnostic query failed: {e}")

# CORS configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Adjust in production environments
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Middleware: Request ID and Request Logging
@app.middleware("http")
async def log_requests(request: Request, call_next):
    request_id = str(uuid.uuid4())
    request.state.request_id = request_id
    
    start_time = time.time()
    logger.info(f"[{request_id}] Start: {request.method} {request.url.path}")
    
    try:
        response = await call_next(request)
        process_time = time.time() - start_time
        response.headers["X-Request-ID"] = request_id
        logger.info(f"[{request_id}] End: {request.method} {request.url.path} - Status: {response.status_code} - Duration: {process_time:.4f}s")
        return response
    except Exception as e:
        process_time = time.time() - start_time
        logger.error(f"[{request_id}] Exception: {request.method} {request.url.path} - Details: {str(e)} - Duration: {process_time:.4f}s", exc_info=True)
        return JSONResponse(
            status_code=500,
            content={
                "success": False,
                "message": "Internal server error occurred.",
                "data": None,
                "errors": [{"detail": "An unexpected error occurred on the server."}],
                "timestamp": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
                "request_id": request_id
            }
        )

# Base health checks
@app.get("/api/v1/health")
async def health_check(request: Request):
    request_id = getattr(request.state, "request_id", "unknown")
    return {
        "success": True,
        "message": "Service is healthy and reachable.",
        "data": {
            "status": "healthy",
            "environment": settings.ENV_NAME,
            "version": "1.0.0"
        },
        "errors": None,
        "timestamp": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        "request_id": request_id
    }
