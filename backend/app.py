import os
import json
import numpy as np
from contextlib import asynccontextmanager
from fastapi import FastAPI, Request, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.staticfiles import StaticFiles

from config import settings
from database.connection import init_db, SessionLocal
from database.models import FaceEmbedding, Employee
from recognition.detector import FaceDetector
from recognition.embedding import EmbeddingExtractor
from recognition.matcher import matcher
from services.face_service import FaceService
from services.attendance_service import AttendanceService

from routes import register, verify, attendance
from utils.logger import logger

# Global Service Handles
insight_app = None

@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Application startup and shutdown events handler.
    Loads InsightFace model and preloads in-memory embedding cache.
    """
    logger.info("Initializing Face Recognition Attendance Backend Service...")
    
    # 1. Initialize Database Tables
    init_db()

    # 2. Pre-load InsightFace / AI Model
    global insight_app
    try:
        import insightface
        insight_app = insightface.app.FaceAnalysis(
            name=settings.MODEL_NAME,
            allowed_modules=['detection', 'recognition'],
            providers=['CPUExecutionProvider']
        )
        insight_app.prepare(ctx_id=0, det_size=(settings.DETECTION_SIZE, settings.DETECTION_SIZE))
        logger.info(f"InsightFace model '{settings.MODEL_NAME}' initialized successfully on CPU.")
    except Exception as e:
        logger.warning(f"Could not load InsightFace model: {str(e)}. Running with high-precision fallback extractors.")

    # Instantiate Recognition Components
    detector = FaceDetector(app_model=insight_app)
    extractor = EmbeddingExtractor(app_model=insight_app)

    # Instantiate Services
    face_service = FaceService(detector=detector, extractor=extractor)
    attendance_service = AttendanceService(detector=detector, extractor=extractor)

    # Inject service dependencies into route handlers
    register.set_face_service(face_service)
    verify.set_attendance_service(attendance_service)
    attendance.set_attendance_service_alt(attendance_service)

    # 3. Pre-load face embeddings from MySQL into in-memory matcher cache
    db = SessionLocal()
    try:
        all_embeddings = db.query(FaceEmbedding).all()
        cache_dict = {}
        for item in all_embeddings:
            try:
                emb_list = json.loads(item.embedding)
                arr = np.array(emb_list, dtype=np.float32)
                if item.employee_id not in cache_dict:
                    cache_dict[item.employee_id] = []
                cache_dict[item.employee_id].append(arr)
            except Exception as ex:
                logger.error(f"Error parsing embedding ID {item.id}: {str(ex)}")

        matcher.load_cache(cache_dict)
    except Exception as db_err:
        logger.error(f"Error populating embedding cache: {str(db_err)}")
    finally:
        db.close()

    logger.info("Service Startup Complete. Ready to handle API requests.")
    yield
    logger.info("Shutting down Face Recognition Attendance Service...")

app = FastAPI(
    title=settings.APP_NAME,
    version="1.0.0",
    description="Production-Ready Web-Based Face Recognition Attendance System API",
    lifespan=lifespan
)

# CORS Middleware Setup
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Production setup allows requests from PHP web server / domain
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount Uploads directory for static photo viewing
uploads_dir = os.path.join(settings.BASE_DIR, "uploads")
os.makedirs(uploads_dir, exist_ok=True)
app.mount("/uploads", StaticFiles(directory=uploads_dir), name="uploads")

# Include API Routers
app.include_router(register.router)
app.include_router(verify.router)
app.include_router(attendance.router)

@app.get("/", tags=["Health Check"])
@app.get("/health", tags=["Health Check"])
async def health_check():
    return {
        "status": "healthy",
        "service": settings.APP_NAME,
        "version": "1.0.0",
        "similarity_threshold": settings.SIMILARITY_THRESHOLD,
        "cooldown_minutes": settings.COOLDOWN_MINUTES
    }

# Exception Handlers
@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    logger.error(f"Unhandled Exception: {str(exc)}")
    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={"success": False, "message": f"Internal Server Error: {str(exc)}"}
    )

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "app:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG
    )
