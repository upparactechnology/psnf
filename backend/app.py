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
from recognition.matcher import matcher
from services.face_service import FaceService
from services.attendance_service import AttendanceService

from routes import register, verify, attendance, detect
from utils.logger import logger

# ─── InsightFace Global Handle ──────────────────────────────────────────────────
insight_app = None
detector = None

@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Application startup — loads InsightFace buffalo_l model (MANDATORY).
    If InsightFace fails to load, the service will NOT start.
    """
    logger.info("=" * 60)
    logger.info("  PSNF Face Recognition Attendance Backend")
    logger.info("  Engine: InsightFace (buffalo_l) — MANDATORY MODE")
    logger.info("=" * 60)

    # 1. Initialize Database Tables
    init_db()

    # 2. Load InsightFace — MANDATORY, no fallback
    global insight_app
    try:
        import insightface
        logger.info(f"Loading InsightFace model: '{settings.MODEL_NAME}' ...")
        insight_app = insightface.app.FaceAnalysis(
            name=settings.MODEL_NAME,
            allowed_modules=['detection', 'recognition'],
            providers=['CPUExecutionProvider']
        )
        insight_app.prepare(ctx_id=0, det_size=(settings.DETECTION_SIZE, settings.DETECTION_SIZE))
        logger.info(f"✅ InsightFace '{settings.MODEL_NAME}' loaded successfully on CPU.")
    except ImportError:
        logger.critical("❌ FATAL: 'insightface' package is NOT installed.")
        logger.critical("   Run:  pip install insightface onnxruntime opencv-python-headless")
        raise RuntimeError(
            "InsightFace is not installed. "
            "Please run: pip install -r requirements.txt  and restart the service."
        )
    except Exception as e:
        logger.critical(f"❌ FATAL: InsightFace model failed to load: {str(e)}")
        logger.critical(
            "   Ensure the model 'buffalo_l' is downloaded. "
            "Run once: python -c \"import insightface; insightface.app.FaceAnalysis(name='buffalo_l').prepare(ctx_id=0)\""
        )
        raise RuntimeError(f"InsightFace model load failed: {str(e)}")

    # 3. Instantiate Recognition Components (InsightFace only, no fallback)
    global detector
    detector  = FaceDetector(app_model=insight_app)
    extractor = EmbeddingExtractor(app_model=insight_app)

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
        logger.info(f"✅ Loaded {total} embeddings for {len(cache_dict)} employee(s) into cache.")
    except Exception as db_err:
        logger.error(f"Error populating embedding cache: {str(db_err)}")
    finally:
        db.close()

    logger.info("🚀 Service ready — InsightFace engine active.")
    yield
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
    model_loaded = insight_app is not None
    return {
        "status": "healthy" if model_loaded else "degraded",
        "engine": "InsightFace (buffalo_l)",
        "model_loaded": model_loaded,
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
    from sqlalchemy.orm import Session as _Session
    from database.models import ERPUser as _U, FaceEmbedding as _FE
    from sqlalchemy import func as _f
    db2 = next(_get_db())
    try:
        rows = db2.query(
            _U.id, _U.name, _U.email, _U.employee_id, _U.designation, _U.is_active,
            _f.count(_FE.id).label('embedding_count')
        ).outerjoin(_FE, _FE.user_id == _U.id).filter(
            _U.is_active == 1, _U.deleted_at == None
        ).group_by(_U.id).order_by(_U.name).all()

        data = [{
            "id": r.id, "name": r.name, "email": r.email,
            "employee_id": r.employee_id, "designation": r.designation,
            "embedding_count": r.embedding_count or 0
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
