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
