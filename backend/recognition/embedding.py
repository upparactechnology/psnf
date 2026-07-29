import numpy as np
import cv2
from typing import Optional, Any
from utils.logger import logger


class EmbeddingExtractor:
    """
    InsightFace-only embedding extractor.
    Produces a 512-dim L2-normalized face feature vector via buffalo_l model.
    No fallback — InsightFace is MANDATORY.
    """

    def __init__(self, app_model: Any):
        if app_model is None:
            raise RuntimeError(
                "EmbeddingExtractor requires an InsightFace app model. "
                "Ensure InsightFace is loaded before instantiating this class."
            )
        self.app_model = app_model

    def extract_embedding(self, img_bgr: np.ndarray) -> np.ndarray:
        """
        Extracts a 512-dim L2-normalized InsightFace embedding.
        Raises ValueError if no face is detected in the image.
        """
        try:
            faces = self.app_model.get(img_bgr)
        except Exception as e:
            logger.error(f"InsightFace embedding extraction error: {str(e)}")
            raise RuntimeError(f"InsightFace failed to process image: {str(e)}")

        if len(faces) == 0:
            raise ValueError("No face detected in the image by InsightFace.")

        # Use the face with the highest detection score
        best_face = max(faces, key=lambda f: getattr(f, 'det_score', 0.0))

        if not hasattr(best_face, 'embedding') or best_face.embedding is None:
            raise ValueError("InsightFace detected a face but could not extract an embedding.")

        emb = best_face.embedding.astype(np.float32)

        # L2 normalize
        norm = np.linalg.norm(emb)
        if norm > 0:
            emb = emb / norm

        return emb
