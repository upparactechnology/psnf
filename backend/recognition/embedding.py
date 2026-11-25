import numpy as np
import cv2
from typing import Optional, Any
from utils.logger import logger

class EmbeddingExtractor:
    """
    InsightFace-only embedding extractor.
    Produces a 512-dim L2-normalized face feature vector via buffalo_l model.
    No fallback — InsightFace is MANDATORY.

    Accepts a model_getter callable that lazily loads and returns
    the InsightFace model on demand.
    """

    def __init__(self, model_getter):
        if model_getter is None:
            raise RuntimeError(
                "EmbeddingExtractor requires a model_getter callable. "
                "Pass model_manager.get_model as the getter."
            )
        self._model_getter = model_getter

    def extract_embedding(self, img_bgr: np.ndarray, face: Any = None) -> np.ndarray:
        """
        Extracts a 512-dim L2-normalized InsightFace embedding.
        Raises ValueError if no face is detected in the image.
        If `face` (from FaceDetector.validate_and_detect) carries an embedding,
        it is reused instead of running the model a second time.
        """
        if face is not None and getattr(face, 'embedding', None) is not None:
            return self.embedding_from_face(face)

        try:
            faces = self._model_getter().get(img_bgr)
        except Exception as e:
            logger.error(f"InsightFace embedding extraction error: {str(e)}")
            raise RuntimeError("InsightFace failed to process image.")

        if len(faces) == 0:
            raise ValueError("No face detected in the image by InsightFace.")

        # Use the face with the highest detection score
        best_face = max(faces, key=lambda f: getattr(f, 'det_score', 0.0))
        return self.embedding_from_face(best_face)

    def embedding_from_face(self, best_face: Any) -> np.ndarray:
        """L2-normalized embedding from an already-analysed InsightFace Face object."""
        if getattr(best_face, 'embedding', None) is None:
            raise ValueError("InsightFace detected a face but could not extract an embedding.")

        emb = best_face.embedding.astype(np.float32)

        # L2 normalize
        norm = np.linalg.norm(emb)
        if norm > 0:
            emb = emb / norm

        return emb
