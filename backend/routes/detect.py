from fastapi import APIRouter
from pydantic import BaseModel
from recognition.detector import FaceDetector
from recognition.model_manager import model_manager
from utils.image_utils import base64_to_cv2
import cv2

router = APIRouter()

class DetectFrameRequest(BaseModel):
    image_base64: str

@router.post("/detect-frame")
def detect_frame(req: DetectFrameRequest):
    """
    Real-time face detection endpoint for frontend feedback.
    Uses Haar cascade when InsightFace model not loaded (lightweight).
    Uses InsightFace when model is loaded (accurate with pitch/yaw).
    """
    from app import detector
    try:
        img_bgr = base64_to_cv2(req.image_base64)
        
        # Max resolution for detection speed
        h, w = img_bgr.shape[:2]
        if max(h, w) > 640:
            scale = 640 / max(h, w)
            img_bgr = cv2.resize(img_bgr, (int(w * scale), int(h * scale)))

        # Tier 1: Use Haar cascade if InsightFace model not loaded (saves RAM)
        # Tier 2: Use InsightFace if model is already loaded (accurate pitch/yaw)
        if model_manager.is_loaded():
            det_res = detector.validate_and_detect(img_bgr)
        else:
            det_res = detector.detect_any_face(img_bgr)

        return {
            "success": True,
            "is_valid": det_res.is_valid,
            "error_message": det_res.error_message,
            "bbox": det_res.bbox,
            "pitch": det_res.pitch,
            "yaw": det_res.yaw,
            "blur_score": det_res.blur_score,
            "img_width": img_bgr.shape[1],
            "img_height": img_bgr.shape[0]
        }
    except Exception as e:
        return {
            "success": False,
            "is_valid": False,
            "error_message": str(e)
        }
