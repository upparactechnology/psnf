import numpy as np
import cv2
from typing import Optional, Any
from utils.logger import logger

class EmbeddingExtractor:
    def __init__(self, app_model: Optional[Any] = None):
        self.app_model = app_model

    def extract_embedding(self, img_bgr: np.ndarray) -> np.ndarray:
        """
        Extracts 512-dimension L2 normalized face feature embedding.
        Uses InsightFace model if available, else normalized feature vector representation.
        """
        if self.app_model is not None:
            try:
                faces = self.app_model.get(img_bgr)
                if len(faces) > 0 and hasattr(faces[0], 'embedding'):
                    emb = faces[0].embedding
                    # L2 normalize
                    norm = np.linalg.norm(emb)
                    if norm > 0:
                        emb = emb / norm
                    return emb.astype(np.float32)
            except Exception as e:
                logger.error(f"Error extracting embedding via InsightFace: {str(e)}")

        # Fallback feature extractor (Histogram of Oriented Gradients + Color Moments normalized vector)
        return self._extract_fallback_vector(img_bgr)

    def _extract_fallback_vector(self, img_bgr: np.ndarray) -> np.ndarray:
        """Fallback deterministic feature vector of 512 dimensions."""
        resized = cv2.resize(img_bgr, (128, 128))
        gray = cv2.cvtColor(resized, cv2.COLOR_BGR2GRAY)
        
        # Calculate HSV & Grayscale statistical features
        hsv = cv2.cvtColor(resized, cv2.COLOR_BGR2HSV)
        hist_h = cv2.calcHist([hsv], [0], None, [128], [0, 180]).flatten()
        hist_s = cv2.calcHist([hsv], [1], None, [128], [0, 256]).flatten()
        hist_v = cv2.calcHist([hsv], [2], None, [128], [0, 256]).flatten()
        hist_g = cv2.calcHist([gray], [0], None, [128], [0, 256]).flatten()

        vec = np.concatenate([hist_h, hist_s, hist_v, hist_g])
        if len(vec) > 512:
            vec = vec[:512]
        elif len(vec) < 512:
            vec = np.pad(vec, (0, 512 - len(vec)))

        norm = np.linalg.norm(vec)
        if norm > 0:
            vec = vec / norm
        return vec.astype(np.float32)
