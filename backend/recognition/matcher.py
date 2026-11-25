import threading
import numpy as np
from typing import Dict, List, Tuple, Optional, Iterable, Set
from config import settings
from utils.logger import logger


def _normalize(embedding: np.ndarray) -> np.ndarray:
    norm = np.linalg.norm(embedding)
    if norm > 0:
        embedding = embedding / norm
    return embedding.astype(np.float32)


class FaceMatcher:
    """
    In-memory embedding cache: { user_id: [np.ndarray(512,), ...] }.
    All mutations happen under a lock; match() iterates over a snapshot so that
    concurrent registrations cannot mutate the dict mid-iteration.
    """

    def __init__(self):
        self._embedding_cache: Dict[int, List[np.ndarray]] = {}
        self._lock = threading.Lock()

    def load_cache(self, embeddings_dict: Dict[int, List[np.ndarray]]):
        """Replaces the whole in-memory embedding cache."""
        new_cache = {uid: [_normalize(e) for e in vecs] for uid, vecs in embeddings_dict.items()}
        with self._lock:
            self._embedding_cache = new_cache
        total_vectors = sum(len(vecs) for vecs in new_cache.values())
        logger.info(f"Loaded {total_vectors} embeddings for {len(new_cache)} employees into memory cache.")

    def add_employee_embedding(self, employee_id: int, embedding: np.ndarray):
        """Appends a new embedding vector to the in-memory cache for an employee."""
        emb = _normalize(embedding)
        with self._lock:
            # Copy-on-write so snapshots held by match() are never mutated
            self._embedding_cache[employee_id] = list(self._embedding_cache.get(employee_id, [])) + [emb]

    def replace_employee(self, employee_id: int, embeddings: Iterable[np.ndarray]):
        """Atomically replaces all cached embeddings of an employee (removes them if empty)."""
        vecs = [_normalize(e) for e in embeddings]
        with self._lock:
            if vecs:
                self._embedding_cache[employee_id] = vecs
            else:
                self._embedding_cache.pop(employee_id, None)

    def remove_employee(self, employee_id: int):
        """Removes employee from cache upon deletion."""
        with self._lock:
            self._embedding_cache.pop(employee_id, None)

    def match(
        self,
        target_embedding: np.ndarray,
        threshold: Optional[float] = None,
        allowed_ids: Optional[Set[int]] = None,
    ) -> Tuple[Optional[int], float]:
        """
        Calculates cosine similarity against all cached embeddings (optionally only
        those whose user_id is in allowed_ids, e.g. one tenant's users).
        Returns (best_matched_employee_id, highest_confidence_score).
        If highest confidence < threshold, returns (None, highest_confidence_score).
        """
        if threshold is None:
            threshold = settings.SIMILARITY_THRESHOLD

        with self._lock:
            snapshot = list(self._embedding_cache.items())

        if not snapshot:
            return None, 0.0

        target_embedding = _normalize(target_embedding)

        best_employee_id: Optional[int] = None
        highest_score: float = 0.0

        for emp_id, emb_list in snapshot:
            if allowed_ids is not None and emp_id not in allowed_ids:
                continue
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
