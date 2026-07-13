from fastapi import APIRouter
from app.api.v1 import auth, employees, recognition, reports, attendance, shifts, settings, audit_logs

api_router = APIRouter(prefix="/api/v1")
api_router.include_router(auth.router)
api_router.include_router(employees.router)
api_router.include_router(recognition.router)
api_router.include_router(reports.router)
api_router.include_router(attendance.router)
api_router.include_router(shifts.router)
api_router.include_router(settings.router)
api_router.include_router(audit_logs.router)
