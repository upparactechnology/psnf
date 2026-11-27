import cv2
import numpy as np
from typing import Tuple, Optional, Any
from config import settings
from utils.image_utils import check_image_brightness, face_quality
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
        yaw: float = 0.0,
        face: Any = None,
        reason: Optional[str] = None,
        face_brightness: Optional[float] = None,
        face_contrast: Optional[float] = None,
        face_width: Optional[int] = None,
        det_score: Optional[float] = None
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
        # InsightFace Face object (carries .embedding) — lets callers skip a second inference
        self.face             = face
        # Machine-readable failure reason: blurry | dark | overexposed | no_face |
        # multiple_faces | error   (None when valid)
        self.reason           = reason
        # Face-region diagnostics (for the admin scan-log page)
        self.face_brightness  = face_brightness
        self.face_contrast    = face_contrast
        self.face_width       = face_width
        self.det_score        = det_score


class FaceDetector:
    """
    Two-tier face detector:
      - Tier 1 (lightweight): OpenCV Haar cascade — near-zero memory, runs continuously
      - Tier 2 (full): InsightFace buffalo_l — heavy model, loaded on demand

    When InsightFace model is not loaded, uses Haar cascade to check for faces.
    When >= 1 face is found via Haar, InsightFace is loaded for accurate detection.
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

        When >= 1 face is found:
          - notifies model_manager that a face is present
          - triggers InsightFace model load if not already loaded
          - returns face_count >= 1 (frontend/InsightFace decides multiple-face policy)
        """
        gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)
        faces = self._haar_cascade.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=5,
            minSize=(50, 50)
        )

        face_count = len(faces)

        if face_count == 0:
            return FaceDetectionResult(
                is_valid=False,
                error_message="No face detected",
                face_count=0
            )

        # At least one face found — notify model manager and ensure model is loaded
        if self._model_manager is not None:
            self._model_manager.mark_face_present()
            if not self._model_manager.is_loaded():
                try:
                    logger.info("Face detected via Haar — loading InsightFace model for recognition")
                    self._model_getter()
                    logger.info("InsightFace model loaded automatically after Haar detection")
                except Exception as e:
                    logger.warning(f"Failed to auto-load InsightFace model: {e}")

        # Return first face bbox for UI positioning
        x, y, w, h = faces[0]

        if face_count > 1:
            return FaceDetectionResult(
                is_valid=False,
                error_message=f"Multiple faces detected ({face_count}). Only one person must be in frame.",
                face_count=face_count,
                bbox=(x, y, x + w, y + h)
            )

        return FaceDetectionResult(
            is_valid=True,
            face_count=1,
            bbox=(x, y, x + w, y + h)
        )

    def validate_and_detect(self, img_bgr: np.ndarray) -> FaceDetectionResult:
        """
        Tier 2: InsightFace-based detection with quality checks.

        Pipeline:
          1. Frame brightness check (catches a covered / totally dark camera)
          2. InsightFace face detection
          3. Sharpness check on the FACE region only (settings.FACE_BLUR_THRESHOLD)

        Sharpness used to be measured on the whole frame before detection, which rejected
        sharp faces in front of plain walls or behind a hazy cover sheet.

        Updates model_manager face-presence state:
          - 0 faces  -> no-face state (timer starts ticking toward unload)
          - 1+ faces -> face-present state (timer resets)
        """
        # ── 1. Brightness check (whole frame) ────────────────────────────────────
        brightness_score = check_image_brightness(img_bgr)
        if brightness_score < settings.MIN_BRIGHTNESS:
            if self._model_manager is not None:
                self._model_manager.mark_no_face()
            return FaceDetectionResult(
                is_valid=False,
                reason="dark",
                error_message=(
                    f"Image is too dark (brightness {brightness_score:.1f}). "
                    "Please move to a well-lit area."
                ),
                brightness_score=brightness_score
            )
        if brightness_score > settings.MAX_BRIGHTNESS:
            if self._model_manager is not None:
                self._model_manager.mark_no_face()
            return FaceDetectionResult(
                is_valid=False,
                reason="overexposed",
                error_message=(
                    f"Image is overexposed (brightness {brightness_score:.1f}). "
                    "Please reduce glare or direct light."
                ),
                brightness_score=brightness_score
            )

        # ── 2. InsightFace detection (mandatory) ─────────────────────────────────
        try:
            faces = self._model_getter().get(img_bgr)
        except Exception as e:
            logger.error(f"InsightFace detection error: {str(e)}")
            if self._model_manager is not None:
                self._model_manager.mark_no_face()
            return FaceDetectionResult(
                is_valid=False,
                reason="error",
                error_message="Face detection failed. Please try again.",
                brightness_score=brightness_score
            )

        face_count = len(faces)

        if face_count == 0:
            # No face — start/reset no-face timer
            if self._model_manager is not None:
                self._model_manager.mark_no_face()
            return FaceDetectionResult(
                is_valid=False,
                reason="no_face",
                error_message="No face detected. Please align your face clearly in front of the camera.",
                face_count=0,
                brightness_score=brightness_score
            )

        # At least one face detected — face is present, reset no-face timer
        if self._model_manager is not None:
            self._model_manager.mark_face_present()

        if face_count > 1:
            return FaceDetectionResult(
                is_valid=False,
                reason="multiple_faces",
                error_message=f"Multiple faces detected ({face_count}). Only one person must be in frame.",
                face_count=face_count,
                brightness_score=brightness_score
            )

        face = faces[0]
        bbox = tuple(map(int, face.bbox))
        kps  = getattr(face, 'kps', None)
        det_score = float(getattr(face, 'det_score', 0.0) or 0.0)

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

        # ── 3. Sharpness on the face region only ─────────────────────────────────
        fq = face_quality(img_bgr, bbox)
        blur_score = fq.get("blur", 0.0)
        common = dict(
            face_count=1,
            bbox=bbox,
            kps=kps,
            blur_score=blur_score,
            brightness_score=brightness_score,
            pitch=pitch,
            yaw=yaw,
            face_brightness=fq.get("brightness"),
            face_contrast=fq.get("contrast"),
            face_width=fq.get("width"),
            det_score=det_score,
        )
        if blur_score < settings.FACE_BLUR_THRESHOLD:
            return FaceDetectionResult(
                is_valid=False,
                reason="blurry",
                error_message=(
                    f"Face is too blurry (score {blur_score:.1f} < {settings.FACE_BLUR_THRESHOLD}). "
                    "Please hold still."
                ),
                **common
            )

        return FaceDetectionResult(is_valid=True, face=face, **common)
