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

        best_id, best_score, _ = self.match_details(target_embedding, allowed_ids)
        if best_id is not None and best_score >= threshold:
            return best_id, best_score
        return None, best_score

    def match_details(
        self,
        target_embedding: np.ndarray,
        allowed_ids: Optional[Set[int]] = None,
    ) -> Tuple[Optional[int], float, float]:
        """
        Returns (closest_user_id, best_score, second_best_score) WITHOUT applying the
        threshold. second_best_score is the best score of any OTHER user — the gap
        between the two shows how confidently the face was separated from others.
        Used for diagnostics; never shown on the kiosk.
        """
        with self._lock:
            snapshot = list(self._embedding_cache.items())

        target_embedding = _normalize(target_embedding)

        per_user = []
        for emp_id, emb_list in snapshot:
            if allowed_ids is not None and emp_id not in allowed_ids:
                continue
            if not emb_list:
                continue
            # Cosine similarity for normalized vectors is simply dot product
            per_user.append((max(float(np.dot(target_embedding, emb)) for emb in emb_list), emp_id))

        if not per_user:
            return None, 0.0, 0.0

        per_user.sort(reverse=True)
        best_score, best_id = per_user[0]
        second = per_user[1][0] if len(per_user) > 1 else 0.0
        return best_id, round(max(best_score, 0.0), 4), round(max(second, 0.0), 4)


# Global singleton matcher instance for in-memory speed
matcher = FaceMatcher()
