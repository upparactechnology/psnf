from fastapi import APIRouter
from pydantic import BaseModel
from recognition.detector import FaceDetector
from recognition.model_manager import model_manager
from utils.image_utils import base64_to_cv2
from utils.logger import logger
import cv2

router = APIRouter()


@router.post("/model/load")
def load_model():
    """Force-load InsightFace model into RAM (for registration pages)."""
    try:
        if model_manager.is_loaded():
            model_manager.get_model()
            return {"success": True, "message": "Model already loaded"}
        model_manager.get_model()
        logger.info("Model force-loaded via /api/model/load")
        return {"success": True, "message": "Model loaded"}
    except Exception as e:
        return {"success": False, "message": str(e)}


@router.post("/model/unload")
def unload_model():
    """Unload InsightFace model from RAM to free memory."""
    try:
        if not model_manager.is_loaded():
            return {"success": True, "message": "Model not loaded"}
        model_manager.unload(reason="manual unload from registration UI")
        return {"success": True, "message": "Model unloaded"}
    except Exception as e:
        return {"success": False, "message": str(e)}


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
            "is_valid": bool(det_res.is_valid),
            "error_message": det_res.error_message,
            "bbox": [int(x) for x in det_res.bbox] if det_res.bbox else None,
            "pitch": float(det_res.pitch) if det_res.pitch is not None else None,
            "yaw": float(det_res.yaw) if det_res.yaw is not None else None,
            "blur_score": float(det_res.blur_score) if det_res.blur_score is not None else None,
            "img_width": int(img_bgr.shape[1]),
            "img_height": int(img_bgr.shape[0])
        }
    except Exception as e:
        return {
            "success": False,
            "is_valid": False,
            "error_message": str(e)
        }
