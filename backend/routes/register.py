from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.orm import Session
from database.connection import get_db
from models.schemas import RegisterFaceRequest, StandardAPIResponse
from services.face_service import FaceService
from utils.security import check_rate_limit, get_client_ip
from utils.logger import logger

router = APIRouter(prefix="/api", tags=["Face Registration"])

# The face service instance will be injected during app startup
face_service_instance: FaceService = None

def set_face_service(service: FaceService):
    global face_service_instance
    face_service_instance = service

@router.post("/register-face", response_model=StandardAPIResponse)
def register_face(
    req: RegisterFaceRequest,
    request: Request,
    db: Session = Depends(get_db)
):
    client_ip = get_client_ip(request)
    check_rate_limit(client_ip)

    if not req.images_base64:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="At least one base64 encoded face image is required for registration."
        )

    try:
        res = face_service_instance.register_employee_faces(db, req)
        return StandardAPIResponse(
            success=True,
            message=res["message"],
            data=res
        )
    except ValueError as ve:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail=str(ve))
    except Exception as e:
        logger.error(f"Error registering face: {str(e)}")
        raise HTTPException(status_code=status.HTTP_500_INTERNAL_SERVER_ERROR, detail=f"Registration failed: {str(e)}")
