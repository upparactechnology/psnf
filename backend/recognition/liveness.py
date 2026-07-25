import cv2
import numpy as np
from typing import Tuple, Optional

def verify_face_liveness(img_bgr: np.ndarray, bbox: Optional[Tuple[int, int, int, int]] = None) -> Tuple[bool, str]:
    """
    Validates anti-spoofing / liveness criteria:
    - Minimum face bounding box dimensions (prevents small cropped low-res photos).
    - Aspect ratio bounds (prevents extreme distortion or stretched screen replays).
    - Texture variation check via standard deviation.
    """
    if bbox is not None:
        x1, y1, x2, y2 = bbox
        w = x2 - x1
        h = y2 - y1

        if w < 50 or h < 50:
            return False, "Face size too small. Please move closer to the camera."

        aspect_ratio = float(w) / float(h)
        if aspect_ratio < 0.5 or aspect_ratio > 1.8:
            return False, "Face posture / aspect ratio invalid. Please look straight at the camera."

        face_roi = img_bgr[max(0, y1):min(img_bgr.shape[0], y2), max(0, x1):min(img_bgr.shape[1], x2)]
        if face_roi.size > 0:
            gray_roi = cv2.cvtColor(face_roi, cv2.COLOR_BGR2GRAY)
            std_dev = np.std(gray_roi)
            if std_dev < 12.0:
                return False, "Liveness check failed (flat texture or static photo playback detected)."

    return True, "Liveness checks passed."
