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
from database.models import FaceEmbedding, ERPUser
from recognition.detector import FaceDetector
from recognition.embedding import EmbeddingExtractor
from recognition.model_manager import model_manager
from recognition.matcher import matcher
from services.face_service import FaceService
from services.attendance_service import AttendanceService

from routes import register, verify, attendance, detect
from utils.logger import logger

# ─── Global detector reference (for routes/detect.py import) ────────────────────
detector = None

def _load_insightface():
    """Load InsightFace model — called lazily by ModelManager on first request."""
    import insightface
    app = insightface.app.FaceAnalysis(
        name=settings.MODEL_NAME,
        allowed_modules=['detection', 'recognition'],
        providers=['CPUExecutionProvider']
    )
    app.prepare(ctx_id=0, det_size=(settings.DETECTION_SIZE, settings.DETECTION_SIZE))
    return app


@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Application startup — configures lazy-loading ModelManager.
    InsightFace model loads on first request, not at startup.
    """
    logger.info("=" * 60)
    logger.info("  PSNF Face Recognition Attendance Backend")
    logger.info("  Engine: InsightFace (buffalo_l) — LAZY LOAD MODE")
    logger.info(f"  Idle timeout: {settings.MODEL_IDLE_TIMEOUT}s")
    logger.info(f"  No-face idle timeout: {settings.NO_FACE_IDLE_TIMEOUT}s")
    logger.info("=" * 60)

    # 1. Initialize Database Tables
    init_db()

    # 2. Configure ModelManager (lazy load — no model loaded yet)
    model_manager.configure(
        idle_timeout=settings.MODEL_IDLE_TIMEOUT,
        no_face_idle_timeout=settings.NO_FACE_IDLE_TIMEOUT,
        load_fn=_load_insightface
    )
    model_manager.start()

    # 3. Instantiate Recognition Components with getter pattern
    global detector
    detector  = FaceDetector(model_getter=model_manager.get_model, model_manager=model_manager)
    extractor = EmbeddingExtractor(model_getter=model_manager.get_model)

    # 4. Instantiate Services
    face_service       = FaceService(detector=detector, extractor=extractor)
    attendance_service = AttendanceService(detector=detector, extractor=extractor)

    # 5. Inject dependencies into routers
    register.set_face_service(face_service)
    verify.set_attendance_service(attendance_service)
    attendance.set_attendance_service_alt(attendance_service)

    # 6. Pre-load all face embeddings from MySQL into in-memory matcher cache
    db = SessionLocal()
    try:
        all_embeddings = db.query(FaceEmbedding).filter(FaceEmbedding.user_id != None).all()
        cache_dict = {}
        for item in all_embeddings:
            try:
                emb_list = json.loads(item.embedding)
                arr = np.array(emb_list, dtype=np.float32)
                uid = item.user_id
                if uid not in cache_dict:
                    cache_dict[uid] = []
                cache_dict[uid].append(arr)
            except Exception as ex:
                logger.error(f"Error parsing embedding ID {item.id}: {str(ex)}")

        matcher.load_cache(cache_dict)
        total = sum(len(v) for v in cache_dict.values())
        logger.info(f"Loaded {total} embeddings for {len(cache_dict)} employee(s) into cache.")
    except Exception as db_err:
        logger.error(f"Error populating embedding cache: {str(db_err)}")
    finally:
        db.close()

    logger.info("Service ready — model will load on first face recognition request.")
    yield

    # 7. Shutdown: stop auto-unloader thread and release model
    model_manager.stop()
    logger.info("Shutting down Face Recognition Service...")


app = FastAPI(
    title=settings.APP_NAME,
    version="2.0.0",
    description="InsightFace-powered Face Recognition Attendance API (Mandatory Python/InsightFace engine)",
    lifespan=lifespan
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Static uploads
uploads_dir = os.path.join(settings.BASE_DIR, "uploads")
os.makedirs(uploads_dir, exist_ok=True)
app.mount("/uploads", StaticFiles(directory=uploads_dir), name="uploads")

# Routers
app.include_router(register.router)
app.include_router(verify.router)
app.include_router(attendance.router)
app.include_router(detect.router, prefix="/api", tags=["Detect"])


@app.get("/", tags=["Health"])
@app.get("/health", tags=["Health"])
async def health_check():
    model_loaded = model_manager.is_loaded()
    return {
        "status": "healthy",
        "engine": "InsightFace (buffalo_l)",
        "model_loaded": model_loaded,
        "idle_timeout_seconds": settings.MODEL_IDLE_TIMEOUT,
        "service": settings.APP_NAME,
        "version": "2.0.0",
        "similarity_threshold": settings.SIMILARITY_THRESHOLD,
        "cooldown_minutes": settings.COOLDOWN_MINUTES
    }


from fastapi import Depends
from fastapi.responses import JSONResponse as _JSONResponse
from sqlalchemy import func as _func

@app.get("/api/users/list", tags=["Users"])
async def list_erp_users(db=Depends(lambda: next(__import__('database.connection', fromlist=['get_db']).get_db()))):
    """
    Returns active ERP users with their face embedding count.
    Used by face registration UI to let admin pick who to register.
    """
    from database.connection import get_db as _get_db
    from sqlalchemy import text
    db2 = next(_get_db())
    try:
        query = text("""
            SELECT u.id, u.name, u.email, u.employee_id, u.designation, u.is_active,
                   COUNT(fe.id) as embedding_count
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            LEFT JOIN face_embeddings fe ON fe.user_id = u.id
            WHERE u.is_active = 1 AND u.deleted_at IS NULL
              AND (r.slug IS NULL OR r.slug NOT IN ('parent', 'student'))
            GROUP BY u.id
            ORDER BY u.name
        """)
        rows = db2.execute(query).fetchall()

        data = [{
            "id": r[0], "name": r[1], "email": r[2],
            "employee_id": r[3], "designation": r[4],
            "embedding_count": r[6] or 0
        } for r in rows]
        return {"success": True, "count": len(data), "data": data}
    finally:
        db2.close()


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
