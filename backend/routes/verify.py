from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.orm import Session
from database.connection import get_db
from models.schemas import VerifyFaceRequest, VerifyFaceResponse
from services.attendance_service import AttendanceService
from utils.security import check_rate_limit, get_client_ip
from utils.logger import logger

router = APIRouter(prefix="/api", tags=["Face Verification"])

attendance_service_instance: AttendanceService = None

def set_attendance_service(service: AttendanceService):
    global attendance_service_instance
    attendance_service_instance = service

@router.post("/verify-face", response_model=VerifyFaceResponse)
async def verify_face(
    req: VerifyFaceRequest,
    request: Request,
    db: Session = Depends(get_db)
):
    client_ip = get_client_ip(request)
    user_agent = request.headers.get("User-Agent", "Unknown Browser")
    check_rate_limit(client_ip)

    if not req.image_base64:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Image base64 parameter is required for face verification."
        )

    try:
        res = attendance_service_instance.verify_and_mark_attendance(
            db=db,
            req=req,
            ip_address=client_ip,
            user_agent=user_agent
        )

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
            message            = res.get("message", "Verification completed.")
        )
    except Exception as e:
        logger.error(f"Error in verify-face endpoint: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Face verification internal error: {str(e)}"
        )
