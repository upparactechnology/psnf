from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.orm import Session
from database.connection import get_db
from models.schemas import VerifyFaceRequest, VerifyFaceResponse
from services.attendance_service import AttendanceService
from utils.security import check_rate_limit, get_client_ip
from utils.logger import logger

router = APIRouter(prefix="/api", tags=["Face Recognition"])

attendance_service_instance: AttendanceService = None

def set_attendance_service(service: AttendanceService):
    global attendance_service_instance
    attendance_service_instance = service


@router.post("/recognize-face", response_model=VerifyFaceResponse)
def recognize_face(
    req: VerifyFaceRequest,
    request: Request,
    db: Session = Depends(get_db)
):
    """
    Primary recognition endpoint for the browser-based kiosk.

    The browser runs a lightweight local face detector (MediaPipe BlazeFace)
    and ONLY calls this endpoint when a face is actually present. This
    dramatically reduces VPS load compared to sending every camera frame.

    Flow:
      Browser local detector → face found → POST /api/recognize-face
      → InsightFace recognition → employee matching → attendance rules → DB
    """
    client_ip = get_client_ip(request)
    user_agent = request.headers.get("User-Agent", "Unknown Browser")
    check_rate_limit(client_ip)

    if not req.image_base64:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Image base64 parameter is required for face recognition."
        )

    logger.info(f"Recognition request from IP={client_ip}")

    try:
        res = attendance_service_instance.verify_and_mark_attendance(
            db=db,
            req=req,
            ip_address=client_ip,
            user_agent=user_agent
        )

        if res.get("success"):
            if res.get("already_checked_in"):
                logger.info(
                    f"Cooldown rejection: user_id={res.get('user_id')} "
                    f"employee={res.get('employee_name')}"
                )
            else:
                logger.info(
                    f"Attendance recorded: user_id={res.get('user_id')} "
                    f"employee={res.get('employee_name')} "
                    f"confidence={res.get('confidence', 0):.3f}"
                )
        else:
            logger.info(
                f"Recognition failed: {res.get('message', 'unknown')} "
                f"confidence={res.get('confidence', 0)}"
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
            message            = res.get("message", "Recognition completed.")
        )
    except Exception as e:
        logger.error(f"Error in recognize-face endpoint: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Face recognition internal error: {str(e)}"
        )
