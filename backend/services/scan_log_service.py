"""
Kiosk scan diagnostics: one face_scan_logs row per recognition attempt, plus a
snapshot of every FAILED scan in uploads/failed/ (kept FAILED_SCAN_IMAGE_DAYS).

Everything here is best-effort: a logging problem must never break attendance.
Scores are stored for admins only and are never returned to the kiosk.
"""
import os
import time
import threading
from datetime import timedelta
from typing import Any, Dict, Optional

import cv2
from sqlalchemy import text

from config import settings
from database.connection import SessionLocal
from database.models import FaceScanLog
from utils.logger import logger
from utils.timeutil import now_ist_naive

_PRUNE_INTERVAL_SECONDS = 3600
_last_prune = 0.0
_prune_lock = threading.Lock()

_FLOAT_FIELDS = ("score", "second_score", "threshold", "blur_score", "blur_threshold", "brightness",
                 "face_brightness", "face_contrast", "det_score", "yaw", "pitch")
_INT_FIELDS = ("matched_user_id", "best_user_id", "face_width", "img_width", "img_height",
               "attempt", "processing_ms", "tenant_id")


def _r(v, nd=4):
    try:
        return None if v is None else round(float(v), nd)
    except (TypeError, ValueError):
        return None


def save_failed_snapshot(img_bgr, outcome: str, score: Optional[float]) -> Optional[str]:
    """Writes the failed frame to uploads/failed/ and returns its relative path."""
    try:
        now = now_ist_naive()
        score_part = f"_{score:.2f}".replace(".", "p") if score is not None else ""
        filename = f"fail_{now.strftime('%Y%m%d_%H%M%S_%f')}_{outcome}{score_part}.jpg"
        path = os.path.join(settings.UPLOAD_FAILED_DIR, filename)
        if cv2.imwrite(path, img_bgr, [int(cv2.IMWRITE_JPEG_QUALITY), 88]):
            return f"uploads/failed/{filename}"
    except Exception as e:
        logger.warning(f"Could not save failed-scan snapshot: {e}")
    return None


def record_scan(diag: Dict[str, Any]) -> None:
    """Inserts one face_scan_logs row in its own session (never touches the caller's transaction)."""
    row = {}
    for k in _FLOAT_FIELDS:
        if k in diag:
            row[k] = _r(diag.get(k))
    for k in _INT_FIELDS:
        if diag.get(k) is not None:
            try:
                row[k] = int(diag[k])
            except (TypeError, ValueError):
                pass
    for k, n in (("outcome", 20), ("message", 255), ("image_path", 255), ("session_id", 40),
                 ("ip_address", 45), ("user_agent", 255)):
        if diag.get(k) is not None:
            row[k] = str(diag[k])[:n]
    if diag.get("client_meta"):
        row["client_meta"] = str(diag["client_meta"])[:4000]
    row.setdefault("outcome", "error")

    db = SessionLocal()
    try:
        db.add(FaceScanLog(**row))
        db.commit()
    except Exception as e:
        db.rollback()
        logger.warning(f"Could not write face scan log: {e}")
    finally:
        db.close()

    maybe_prune()


def maybe_prune(force: bool = False) -> None:
    """Deletes expired failed-scan snapshots and old log rows (at most once per hour)."""
    global _last_prune
    now = time.monotonic()
    if not force and now - _last_prune < _PRUNE_INTERVAL_SECONDS:
        return
    if not _prune_lock.acquire(blocking=False):
        return
    try:
        _last_prune = now
        _prune_images()
        _prune_rows()
    finally:
        _prune_lock.release()


def _prune_images() -> None:
    cutoff = time.time() - max(1, settings.FAILED_SCAN_IMAGE_DAYS) * 86400
    removed = 0
    try:
        for name in os.listdir(settings.UPLOAD_FAILED_DIR):
            path = os.path.join(settings.UPLOAD_FAILED_DIR, name)
            try:
                if os.path.isfile(path) and os.path.getmtime(path) < cutoff:
                    os.remove(path)
                    removed += 1
            except OSError:
                pass
    except OSError:
        return
    if removed:
        logger.info(f"Removed {removed} failed-scan snapshot(s) older than {settings.FAILED_SCAN_IMAGE_DAYS} days")

    db = SessionLocal()
    try:
        img_cutoff = now_ist_naive() - timedelta(days=max(1, settings.FAILED_SCAN_IMAGE_DAYS))
        db.execute(
            text("UPDATE face_scan_logs SET image_path = NULL "
                 "WHERE image_path LIKE 'uploads/failed/%' AND created_at < :c"),
            {"c": img_cutoff},
        )
        db.commit()
    except Exception as e:
        db.rollback()
        logger.warning(f"Could not clear expired snapshot paths: {e}")
    finally:
        db.close()


def _prune_rows() -> None:
    db = SessionLocal()
    try:
        cutoff = now_ist_naive() - timedelta(days=max(1, settings.SCAN_LOG_RETENTION_DAYS))
        db.execute(text("DELETE FROM face_scan_logs WHERE created_at < :c"), {"c": cutoff})
        db.commit()
    except Exception as e:
        db.rollback()
        logger.warning(f"Could not prune face scan logs: {e}")
    finally:
        db.close()
