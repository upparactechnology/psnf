import numpy as np
from typing import Dict, List, Tuple, Optional
from config import settings
from utils.logger import logger

class FaceMatcher:
    def __init__(self):
        # In-memory cache structure: { employee_id: [np.ndarray(512,), np.ndarray(512,), ...] }
        self._embedding_cache: Dict[int, List[np.ndarray]] = {}

    def load_cache(self, embeddings_dict: Dict[int, List[np.ndarray]]):
        """Populates or updates in-memory embedding cache."""
        self._embedding_cache = embeddings_dict
        total_vectors = sum(len(vecs) for vecs in self._embedding_cache.values())
        logger.info(f"Loaded {total_vectors} embeddings for {len(self._embedding_cache)} employees into memory cache.")

    def add_employee_embedding(self, employee_id: int, embedding: np.ndarray):
        """Appends a new embedding vector to the in-memory cache for an employee."""
        # Ensure L2 normalized
        norm = np.linalg.norm(embedding)
        if norm > 0:
            embedding = embedding / norm

        if employee_id not in self._embedding_cache:
            self._embedding_cache[employee_id] = []
        self._embedding_cache[employee_id].append(embedding.astype(np.float32))

    def remove_employee(self, employee_id: int):
        """Removes employee from cache upon deletion."""
        if employee_id in self._embedding_cache:
            del self._embedding_cache[employee_id]

    def match(self, target_embedding: np.ndarray, threshold: Optional[float] = None) -> Tuple[Optional[int], float]:
        """
        Calculates cosine similarity against all cached embeddings.
        Returns (best_matched_employee_id, highest_confidence_score).
        If highest confidence < threshold, returns (None, highest_confidence_score).
        """
        if threshold is None:
            threshold = settings.SIMILARITY_THRESHOLD

        if not self._embedding_cache:
            return None, 0.0

        # L2 normalize target embedding
        norm = np.linalg.norm(target_embedding)
        if norm > 0:
            target_embedding = target_embedding / norm
        target_embedding = target_embedding.astype(np.float32)

        best_employee_id: Optional[int] = None
        highest_score: float = 0.0

        for emp_id, emb_list in self._embedding_cache.items():
            for emb in emb_list:
                # Cosine similarity for normalized vectors is simply dot product
                score = float(np.dot(target_embedding, emb))
                if score > highest_score:
                    highest_score = score
                    best_employee_id = emp_id

        if highest_score >= threshold:
            return best_employee_id, round(highest_score, 4)

        return None, round(highest_score, 4)

# Global singleton matcher instance for in-memory speed
matcher = FaceMatcher()
