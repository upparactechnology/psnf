import base64
import cv2
import numpy as np
from PIL import Image
import io
import warnings
from config import settings
from utils.logger import logger

# Decompression-bomb guard: refuse absurdly large images instead of only warning
Image.MAX_IMAGE_PIXELS = settings.MAX_IMAGE_PIXELS
warnings.simplefilter("error", Image.DecompressionBombWarning)

# Rough upper bound on base64 text length for MAX_IMAGE_BYTES (+ data-URI prefix)
MAX_BASE64_CHARS = (settings.MAX_IMAGE_BYTES * 4) // 3 + 1024
 
def base64_to_cv2(base64_string: str) -> np.ndarray:
    """Decodes base64 string (data URI or raw base64) into OpenCV BGR image array."""
    try:
        if len(base64_string) > MAX_BASE64_CHARS:
            raise ValueError("image too large")
        if "," in base64_string:
            base64_string = base64_string.split(",", 1)[1]

        image_bytes = base64.b64decode(base64_string)
        if len(image_bytes) > settings.MAX_IMAGE_BYTES:
            raise ValueError("image too large")
        pil_image = Image.open(io.BytesIO(image_bytes))
        
        # Convert RGB PIL to BGR OpenCV
        if pil_image.mode != 'RGB':
            pil_image = pil_image.convert('RGB')
        
        cv2_img = cv2.cvtColor(np.array(pil_image), cv2.COLOR_RGB2BGR)
        return cv2_img
    except Exception as e:
        logger.error(f"Failed to decode base64 image: {str(e)}")
        raise ValueError("Invalid, corrupted or too large image input (max 5 MB).")

def cv2_to_base64(cv2_img: np.ndarray, format_ext: str = ".jpg") -> str:
    """Encodes OpenCV image array to base64 data URI string."""
    _, buffer = cv2.imencode(format_ext, cv2_img)
    encoded = base64.b64encode(buffer).decode('utf-8')
    return f"data:image/jpeg;base64,{encoded}"

def check_image_blur(cv2_img: np.ndarray) -> float:
    """Calculates Laplacian variance to measure image sharpness/blurriness."""
    gray = cv2.cvtColor(cv2_img, cv2.COLOR_BGR2GRAY)
    variance = cv2.Laplacian(gray, cv2.CV_64F).var()
    return float(variance)

def face_quality(cv2_img: np.ndarray, bbox) -> dict:
    """
    Quality metrics measured on the face region only.
    The crop is resized to a fixed 128x128 so the sharpness score does not depend on
    how big the face is (people standing further back are not penalised for that alone).
    Returns {} if the bbox does not overlap the image.
    """
    h, w = cv2_img.shape[:2]
    x1, y1, x2, y2 = [int(v) for v in bbox]
    x1, y1 = max(0, x1), max(0, y1)
    x2, y2 = min(w, x2), min(h, y2)
    if x2 - x1 < 4 or y2 - y1 < 4:
        return {}
    gray = cv2.cvtColor(cv2_img[y1:y2, x1:x2], cv2.COLOR_BGR2GRAY)
    interp = cv2.INTER_AREA if (x2 - x1) > 128 else cv2.INTER_LINEAR
    norm = cv2.resize(gray, (128, 128), interpolation=interp)
    return {
        "blur": float(cv2.Laplacian(norm, cv2.CV_64F).var()),
        "brightness": float(gray.mean()),
        "contrast": float(gray.std()),
        "width": int(x2 - x1),
    }


def check_image_brightness(cv2_img: np.ndarray) -> float:
    """Calculates average brightness score (HSV V-channel)."""
    hsv = cv2.cvtColor(cv2_img, cv2.COLOR_BGR2HSV)
    brightness = hsv[:, :, 2].mean()
    return float(brightness)

def resize_image_max(cv2_img: np.ndarray, max_dim: int = 1024) -> np.ndarray:
    """Resizes image maintaining aspect ratio if larger than max_dim."""
    h, w = cv2_img.shape[:2]
    if max(h, w) > max_dim:
        scale = max_dim / float(max(h, w))
        new_w = int(w * scale)
        new_h = int(h * scale)
        return cv2.resize(cv2_img, (new_w, new_h), interpolation=cv2.INTER_AREA)
    return cv2_img
