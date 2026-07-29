from fastapi import APIRouter, Depends, HTTPException, Query, Request
from sqlalchemy.orm import Session
from sqlalchemy import func, desc
from typing import Optional
from datetime import date

from database.connection import get_db
from database.models import ERPUser, FaceEmbedding, Attendance
from models.schemas import VerifyFaceRequest, VerifyFaceResponse, StandardAPIResponse
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
    """POST /api/attendance — process face verify & mark attendance."""
    client_ip  = get_client_ip(request)
    user_agent = request.headers.get("User-Agent", "Unknown")
    res = attendance_service_instance.verify_and_mark_attendance(db, req, client_ip, user_agent)
    return VerifyFaceResponse(
        success            = res.get("success", False),
        already_checked_in = res.get("already_checked_in", False),
        user_id            = res.get("user_id"),
        employee_id        = res.get("employee_id"),
        employee_code      = res.get("employee_code"),
        employee_name      = res.get("employee_name"),
        designation        = res.get("designation"),
        confidence         = res.get("confidence", 0.0),
        check_in           = res.get("check_in"),
        message            = res.get("message", "Attendance processed.")
    )


@router.get("/attendance/history")
async def get_history(
    target_date: Optional[str] = Query(None, alias="date"),
    user_id: Optional[int] = Query(None),
    limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db)
):
    """GET /api/attendance/history — recent check-in logs."""
    parsed_date = None
    if target_date:
        try:
            parsed_date = date.fromisoformat(target_date)
        except ValueError:
            raise HTTPException(status_code=400, detail="Invalid date format. Expected YYYY-MM-DD.")

    logs = attendance_service_instance.get_attendance_history(
        db=db, target_date=parsed_date, user_id=user_id, limit=limit
    )
    return {"success": True, "count": len(logs), "data": logs}


@router.get("/employees/list")
async def list_registered_employees(db: Session = Depends(get_db)):
    """
    GET /api/employees/list
    Returns ERP users who have at least one face embedding registered,
    along with their embedding count and latest face image.
    Used by the Face Registration UI "Registered Faces" panel.
    """
    rows = db.query(
        ERPUser.id,
        ERPUser.name,
        ERPUser.email,
        ERPUser.employee_id,
        ERPUser.designation,
        func.count(FaceEmbedding.id).label("embedding_count"),
        func.max(FaceEmbedding.image_path).label("image_path")
    ).join(FaceEmbedding, FaceEmbedding.user_id == ERPUser.id).filter(
        ERPUser.deleted_at == None
    ).group_by(ERPUser.id).order_by(ERPUser.name).all()

    data = [{
        "id":              r.id,
        "name":            r.name,
        "employee_code":   r.employee_id or f"USR-{r.id}",
        "employee_id":     r.employee_id,
        "designation":     r.designation,
        "embedding_count": r.embedding_count,
        "image_path":      r.image_path,
    } for r in rows]

    return {"success": True, "count": len(data), "data": data}


@router.delete("/employees/delete")
async def delete_employee_face(
    id: int = Query(..., description="ERP user ID whose face profiles to delete"),
    db: Session = Depends(get_db)
):
    """
    DELETE /api/employees/delete?id=<user_id>
    Removes all face embeddings for the given ERP user.
    Does NOT delete the user account itself.
    """
    user = db.query(ERPUser).filter(ERPUser.id == id).first()
    if not user:
        raise HTTPException(status_code=404, detail=f"User ID {id} not found.")

    deleted = db.query(FaceEmbedding).filter(FaceEmbedding.user_id == id).delete()
    db.commit()

    # Also remove from in-memory matcher cache
    from recognition.matcher import matcher
    matcher.remove_employee(id)

    logger.info(f"Deleted {deleted} face embedding(s) for user {user.name} (ID: {id})")
    return {
        "success": True,
        "deleted_count": deleted,
        "message": f"Removed {deleted} face profile(s) for {user.name}."
    }
