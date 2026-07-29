import cv2
import numpy as np
from typing import Tuple, Optional, Any
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
        brightness_score: float = 0.0,
        pitch: float = 0.0,
        yaw: float = 0.0
    ):
        self.is_valid         = is_valid
        self.error_message    = error_message
        self.face_count       = face_count
        self.bbox             = bbox
        self.kps              = kps
        self.blur_score       = blur_score
        self.brightness_score = brightness_score
        self.pitch            = pitch
        self.yaw              = yaw


class FaceDetector:
    """
    InsightFace-only face detector.
    Uses buffalo_l detection module — no OpenCV Haar fallback.
    """

    def __init__(self, app_model: Any):
        if app_model is None:
            raise RuntimeError(
                "FaceDetector requires an InsightFace app model. "
                "Ensure InsightFace (buffalo_l) is loaded before instantiating this class."
            )
        self.app_model = app_model

    def validate_and_detect(self, img_bgr: np.ndarray) -> FaceDetectionResult:
        """
        Pipeline:
          1. Blur quality check (Laplacian variance)
          2. Brightness quality check
          3. InsightFace face detection (exactly 1 face required)
        """
        # ── 1. Blur check ────────────────────────────────────────────────────────
        blur_score = check_image_blur(img_bgr)
        if blur_score < settings.BLUR_THRESHOLD:
            return FaceDetectionResult(
                is_valid=False,
                error_message=(
                    f"Image is too blurry (score {blur_score:.1f} < {settings.BLUR_THRESHOLD}). "
                    "Please hold the camera still."
                ),
                blur_score=blur_score
            )

        # ── 2. Brightness check ──────────────────────────────────────────────────
        brightness_score = check_image_brightness(img_bgr)
        if brightness_score < settings.MIN_BRIGHTNESS:
            return FaceDetectionResult(
                is_valid=False,
                error_message=(
                    f"Image is too dark (brightness {brightness_score:.1f}). "
                    "Please move to a well-lit area."
                ),
                brightness_score=brightness_score
            )
        if brightness_score > settings.MAX_BRIGHTNESS:
            return FaceDetectionResult(
                is_valid=False,
                error_message=(
                    f"Image is overexposed (brightness {brightness_score:.1f}). "
                    "Please reduce glare or direct light."
                ),
                brightness_score=brightness_score
            )

        # ── 3. InsightFace detection (mandatory) ─────────────────────────────────
        try:
            faces = self.app_model.get(img_bgr)
        except Exception as e:
            logger.error(f"InsightFace detection error: {str(e)}")
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"InsightFace detection failed: {str(e)}",
                blur_score=blur_score,
                brightness_score=brightness_score
            )

        face_count = len(faces)

        if face_count == 0:
            return FaceDetectionResult(
                is_valid=False,
                error_message="No face detected. Please align your face clearly in front of the camera.",
                face_count=0,
                blur_score=blur_score,
                brightness_score=brightness_score
            )

        if face_count > 1:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Multiple faces detected ({face_count}). Only one person must be in frame.",
                face_count=face_count,
                blur_score=blur_score,
                brightness_score=brightness_score
            )

        face = faces[0]
        bbox = tuple(map(int, face.bbox))
        kps  = getattr(face, 'kps', None)

        pitch, yaw = 0.0, 0.0
        if kps is not None and len(kps) == 5:
            # kps[0]: left eye, kps[1]: right eye, kps[2]: nose, kps[3]: left mouth, kps[4]: right mouth
            eye_center_x = (kps[0][0] + kps[1][0]) / 2.0
            eye_dist = abs(kps[1][0] - kps[0][0])
            yaw = (kps[2][0] - eye_center_x) / (eye_dist + 1e-6)

            eye_center_y = (kps[0][1] + kps[1][1]) / 2.0
            mouth_center_y = (kps[3][1] + kps[4][1]) / 2.0
            nose_y = kps[2][1]
            upper_dist = abs(nose_y - eye_center_y)
            lower_dist = abs(mouth_center_y - nose_y)
            pitch = upper_dist / (lower_dist + 1e-6)

        return FaceDetectionResult(
            is_valid=True,
            face_count=1,
            bbox=bbox,
            kps=kps,
            blur_score=blur_score,
            brightness_score=brightness_score,
            pitch=pitch,
            yaw=yaw
        )
