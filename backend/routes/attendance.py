from fastapi import APIRouter, Depends, HTTPException, Query, Request
from sqlalchemy.orm import Session
from typing import Optional, List
from datetime import date

from database.connection import get_db
from database.models import Employee
from models.schemas import AttendanceRecordResponse, EmployeeResponse, VerifyFaceRequest, VerifyFaceResponse, StandardAPIResponse
from services.attendance_service import AttendanceService
from utils.security import get_client_ip
from utils.logger import logger

router = APIRouter(prefix="/api", tags=["Attendance Management"])

attendance_service_instance: AttendanceService = None

def set_attendance_service_alt(service: AttendanceService):
    global attendance_service_instance
    attendance_service_instance = service

@router.post("/attendance", response_model=VerifyFaceResponse)
async def post_attendance(
    req: VerifyFaceRequest,
    request: Request,
    db: Session = Depends(get_db)
):
    """POST /api/attendance endpoint to process attendance verification."""
    client_ip = get_client_ip(request)
    user_agent = request.headers.get("User-Agent", "Unknown Browser")
    res = attendance_service_instance.verify_and_mark_attendance(db, req, client_ip, user_agent)
    return VerifyFaceResponse(
        success=res.get("success", False),
        already_checked_in=res.get("already_checked_in", False),
        employee_id=res.get("employee_id"),
        employee_code=res.get("employee_code"),
        employee_name=res.get("employee_name"),
        department=res.get("department"),
        confidence=res.get("confidence", 0.0),
        message=res.get("message", "Attendance processed.")
    )

@router.get("/attendance/history")
async def get_history(
    target_date: Optional[str] = Query(None, alias="date", description="Format: YYYY-MM-DD"),
    employee_id: Optional[int] = Query(None, description="Employee ID filter"),
    limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db)
):
    """GET /api/attendance/history endpoint returning recent check-in logs."""
    parsed_date = None
    if target_date:
        try:
            parsed_date = date.fromisoformat(target_date)
        except ValueError:
            raise HTTPException(status_code=400, detail="Invalid date format. Expected YYYY-MM-DD.")

    logs = attendance_service_instance.get_attendance_history(
        db=db,
        target_date=parsed_date,
        employee_id=employee_id,
        limit=limit
    )

    return {
        "success": True,
        "count": len(logs),
        "data": logs
    }

@router.get("/employees", response_model=List[EmployeeResponse])
async def list_employees(db: Session = Depends(get_db)):
    """GET /api/employees returning list of active registered employees."""
    employees = db.query(Employee).filter(Employee.status == "active").order_by(Employee.name.asc()).all()
    return employees
