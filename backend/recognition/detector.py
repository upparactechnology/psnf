import cv2
import numpy as np
from typing import Tuple, List, Dict, Optional, Any
from config import settings
from utils.image_utils import check_image_blur, check_image_brightness
from utils.logger import logger

class FaceDetectionResult:
    def __init__(
        self,
        is_valid: bool,
        error_message: Optional[str] = None,
        face_count: int = 0,
        bbox: Optional[Tuple[int, int, int, int]] = None,
        kps: Optional[np.ndarray] = None,
        blur_score: float = 0.0,
        brightness_score: float = 0.0
    ):
        self.is_valid = is_valid
        self.error_message = error_message
        self.face_count = face_count
        self.bbox = bbox
        self.kps = kps
        self.blur_score = blur_score
        self.brightness_score = brightness_score

class FaceDetector:
    def __init__(self, app_model: Optional[Any] = None):
        self.app_model = app_model

    def validate_and_detect(self, img_bgr: np.ndarray) -> FaceDetectionResult:
        """
        Runs quality checks:
        1. Blurriness validation (Laplacian variance >= BLUR_THRESHOLD)
        2. Brightness validation (MIN_BRIGHTNESS <= V <= MAX_BRIGHTNESS)
        3. Face Count validation (Exactly 1 face required)
        """
        # 1. Check Blurriness
        blur_score = check_image_blur(img_bgr)
        if blur_score < settings.BLUR_THRESHOLD:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Image is too blurry (quality score {blur_score:.1f} < threshold {settings.BLUR_THRESHOLD}). Please hold steady.",
                blur_score=blur_score
            )

        # 2. Check Brightness
        brightness_score = check_image_brightness(img_bgr)
        if brightness_score < settings.MIN_BRIGHTNESS:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Image is too dark (brightness {brightness_score:.1f}). Please ensure sufficient lighting.",
                brightness_score=brightness_score
            )
        if brightness_score > settings.MAX_BRIGHTNESS:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Image is overexposed (brightness {brightness_score:.1f}). Please adjust glare.",
                brightness_score=brightness_score
            )

        # 3. Detect Faces via InsightFace / OpenCV Haar fallback
        if self.app_model is not None:
            try:
                faces = self.app_model.get(img_bgr)
                face_count = len(faces)
                if face_count == 0:
                    return FaceDetectionResult(
                        is_valid=False,
                        error_message="No face detected in the image. Please position your face clearly in front of the camera.",
                        face_count=0,
                        blur_score=blur_score,
                        brightness_score=brightness_score
                    )
                if face_count > 1:
                    return FaceDetectionResult(
                        is_valid=False,
                        error_message=f"Multiple faces detected ({face_count}). Only one person must be visible in frame.",
                        face_count=face_count,
                        blur_score=blur_score,
                        brightness_score=brightness_score
                    )

                face = faces[0]
                bbox = tuple(map(int, face.bbox))
                kps = getattr(face, 'kps', None)

                return FaceDetectionResult(
                    is_valid=True,
                    face_count=1,
                    bbox=bbox,
                    kps=kps,
                    blur_score=blur_score,
                    brightness_score=brightness_score
                )
            except Exception as e:
                logger.error(f"InsightFace detection error: {str(e)}")

        # Fallback OpenCV Haar Cascade Detector if model is loading
        return self._fallback_haar_detect(img_bgr, blur_score, brightness_score)

    def _fallback_haar_detect(self, img_bgr: np.ndarray, blur_score: float, brightness_score: float) -> FaceDetectionResult:
        gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)
        cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        faces = cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(60, 60))
        face_count = len(faces)

        if face_count == 0:
            return FaceDetectionResult(
                is_valid=False,
                error_message="No face detected in the image.",
                face_count=0,
                blur_score=blur_score,
                brightness_score=brightness_score
            )
        if face_count > 1:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Multiple faces detected ({face_count}). Please ensure only 1 person is in frame.",
                face_count=face_count,
                blur_score=blur_score,
                brightness_score=brightness_score
            )

        x, y, w, h = faces[0]
        return FaceDetectionResult(
            is_valid=True,
            face_count=1,
            bbox=(x, y, x + w, y + h),
            blur_score=blur_score,
            brightness_score=brightness_score
        )
