import base64
import cv2
import numpy as np
from PIL import Image
import io
from config import settings
from utils.logger import logger

def base64_to_cv2(base64_string: str) -> np.ndarray:
    """Decodes base64 string (data URI or raw base64) into OpenCV BGR image array."""
    try:
        if "," in base64_string:
            base64_string = base64_string.split(",")[1]
        
        image_bytes = base64.b64decode(base64_string)
        pil_image = Image.open(io.BytesIO(image_bytes))
        
        # Convert RGB PIL to BGR OpenCV
        if pil_image.mode != 'RGB':
            pil_image = pil_image.convert('RGB')
        
        cv2_img = cv2.cvtColor(np.array(pil_image), cv2.COLOR_RGB2BGR)
        return cv2_img
    except Exception as e:
        logger.error(f"Failed to decode base64 image: {str(e)}")
        raise ValueError("Invalid or corrupted base64 image input.")

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
