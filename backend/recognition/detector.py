import cv2
import numpy as np
from typing import Tuple, Optional, Any
from config import settings
from utils.image_utils import check_image_blur, check_image_brightness
from utils.logger import logger

# Haar cascade path (bundled with OpenCV)
_HAAR_CASCADE_PATH = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"


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
    Two-tier face detector:
      - Tier 1 (lightweight): OpenCV Haar cascade — near-zero memory, runs continuously
      - Tier 2 (full): InsightFace buffalo_l — heavy model, loaded on demand

    When InsightFace model is not loaded, uses Haar cascade to check for faces.
    When a face is found via Haar, InsightFace is loaded for accurate detection.
    """

    def __init__(self, model_getter, model_manager=None):
        if model_getter is None:
            raise RuntimeError(
                "FaceDetector requires a model_getter callable. "
                "Pass model_manager.get_model as the getter."
            )
        self._model_getter = model_getter
        self._model_manager = model_manager

        # Tier 1: Lightweight Haar cascade (loaded once, stays in memory, ~0MB)
        self._haar_cascade = cv2.CascadeClassifier(_HAAR_CASCADE_PATH)
        logger.info("Haar cascade loaded for lightweight face detection")

    def detect_any_face(self, img_bgr: np.ndarray) -> FaceDetectionResult:
        """
        Tier 1: Lightweight face detection using OpenCV Haar cascade.
        No InsightFace model needed. Used for continuous monitoring.
        When a face is found, notifies ModelManager to keep/load the model.
        """
        gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)
        faces = self._haar_cascade.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=5,
            minSize=(50, 50)
        )

        if len(faces) == 0:
            return FaceDetectionResult(
                is_valid=False,
                error_message="No face detected",
                face_count=0
            )

        if len(faces) > 1:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Multiple faces detected ({len(faces)})",
                face_count=len(faces)
            )

        # Face found via Haar — notify ModelManager to keep model alive
        if self._model_manager is not None:
            self._model_manager.face_detected()

        x, y, w, h = faces[0]
        return FaceDetectionResult(
            is_valid=True,
            face_count=1,
            bbox=(x, y, x + w, y + h)
        )

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
            faces = self._model_getter().get(img_bgr)
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

        # Notify ModelManager that a face was detected — keeps model alive
        if self._model_manager is not None:
            self._model_manager.face_detected()

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
