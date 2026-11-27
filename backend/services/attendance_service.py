import os
import time
import threading
import cv2
from datetime import datetime, date
from zoneinfo import ZoneInfo
from typing import Dict, Any, Optional, List
from sqlalchemy.orm import Session
from sqlalchemy import desc

from config import settings
from database.models import ERPUser, Attendance, FaceEmbedding
from models.schemas import VerifyFaceRequest
from utils.image_utils import base64_to_cv2, resize_image_max
from utils.logger import logger
from recognition.detector import FaceDetector
from recognition.embedding import EmbeddingExtractor
from recognition.liveness import verify_face_liveness
from recognition.matcher import matcher
from services.scan_log_service import record_scan, save_failed_snapshot

IST = ZoneInfo("Asia/Kolkata")

# Per-user locks so two concurrent scans of the same person cannot both pass the
# cooldown check and insert duplicate rows (single-process guard; the DB row lock
# below covers multi-process deployments).
_user_locks: Dict[int, threading.Lock] = {}
_user_locks_guard = threading.Lock()


def _lock_for(user_id: int) -> threading.Lock:
    with _user_locks_guard:
        lock = _user_locks.get(user_id)
        if lock is None:
            lock = threading.Lock()
            _user_locks[user_id] = lock
        return lock


def _to_aware(dt):
    """If a datetime from the DB is naive (no tzinfo), attach IST."""
    if dt is not None and dt.tzinfo is None:
        return dt.replace(tzinfo=IST)
    return dt


class AttendanceService:
    def __init__(self, detector: FaceDetector, extractor: EmbeddingExtractor):
        self.detector  = detector
        self.extractor = extractor

    def verify_and_mark_attendance(
        self,
        db: Session,
        req: VerifyFaceRequest,
        ip_address: str,
        user_agent: str
    ) -> Dict[str, Any]:
        """
        Runs the recognition pipeline and records one face_scan_logs row for the
        admin "Face Scan Logs" page (failed frames are also saved to uploads/failed/).
        Similarity scores are logged for admins only — failures return confidence 0.0.
        """
        t0 = time.monotonic()
        diag: Dict[str, Any] = {
            "tenant_id": getattr(req, "tenant_id", None),
            "session_id": getattr(req, "session_id", None),
            "attempt": getattr(req, "attempt", None),
            "client_meta": getattr(req, "client_meta", None),
            "ip_address": ip_address,
            "user_agent": user_agent,
            "threshold": settings.SIMILARITY_THRESHOLD,
            "blur_threshold": settings.FACE_BLUR_THRESHOLD,
        }
        img_holder: Dict[str, Any] = {}
        try:
            res = self._verify(db, req, ip_address, user_agent, diag, img_holder)
        except Exception as e:
            diag["outcome"] = "error"
            diag["message"] = str(e)[:255]
            diag["processing_ms"] = int((time.monotonic() - t0) * 1000)
            record_scan(diag)
            raise

        if res.get("success"):
            diag["outcome"] = "cooldown" if res.get("already_checked_in") else "success"
            diag["matched_user_id"] = res.get("user_id")
            diag["image_path"] = res.get("_image_path")
        else:
            diag["outcome"] = res.get("reason") or "error"
            img = img_holder.get("img")
            if img is not None and diag["outcome"] != "error":
                diag["image_path"] = save_failed_snapshot(img, diag["outcome"], diag.get("score"))
        diag["message"] = res.get("message")
        diag["processing_ms"] = int((time.monotonic() - t0) * 1000)
        record_scan(diag)

        res.pop("_image_path", None)
        return res

    def _verify(self, db: Session, req: VerifyFaceRequest, ip_address: str, user_agent: str,
                diag: Dict[str, Any], img_holder: Dict[str, Any]) -> Dict[str, Any]:
        """
        Full pipeline:
          1. Decode & resize image
          2. Detect face (InsightFace, mandatory) + face-region quality
          3. Liveness check
          4. Extract 512D embedding
          5. Match against in-memory cache (user_id keyed)
          6. Cooldown check (settings.COOLDOWN_MINUTES)
          7. Write attendance row to DB (user_id)
        """
        # 1. Decode & resize
        img_bgr = base64_to_cv2(req.image_base64)
        img_bgr = resize_image_max(img_bgr, max_dim=1024)
        img_holder["img"] = img_bgr
        diag["img_height"], diag["img_width"] = img_bgr.shape[:2]

        # 2. Face detection & quality
        det_res = self.detector.validate_and_detect(img_bgr)
        diag.update({
            "blur_score": det_res.blur_score if det_res.face_count == 1 else None,
            "brightness": det_res.brightness_score,
            "face_brightness": det_res.face_brightness,
            "face_contrast": det_res.face_contrast,
            "face_width": det_res.face_width,
            "det_score": det_res.det_score,
            "yaw": det_res.yaw if det_res.face_count == 1 else None,
            "pitch": det_res.pitch if det_res.face_count == 1 else None,
        })
        if not det_res.is_valid:
            logger.info(f"Detection failed: {det_res.error_message}")
            return {"success": False, "reason": det_res.reason or "error",
                    "message": det_res.error_message or "Face quality check failed.", "confidence": 0.0}

        # 3. Liveness check
        liveness_ok, liveness_msg = verify_face_liveness(img_bgr, det_res.bbox)
        if not liveness_ok:
            logger.warning(f"Liveness check failed: {liveness_msg}")
            return {"success": False, "reason": "liveness", "message": f"Security: {liveness_msg}", "confidence": 0.0}

        # 4. Extract embedding (reuses the detection pass when possible)
        try:
            target_embedding = self.extractor.extract_embedding(img_bgr, face=det_res.face)
        except ValueError as e:
            return {"success": False, "reason": "no_face", "message": str(e), "confidence": 0.0}
        except RuntimeError as e:
            logger.error(f"Embedding extraction failed: {e}")
            return {"success": False, "reason": "error", "message": "Face processing failed. Please try again.", "confidence": 0.0}

        # 5. Cosine match (optionally restricted to one tenant's users)
        allowed_ids = None
        tenant_id = getattr(req, "tenant_id", None)
        if tenant_id is not None:
            allowed_ids = {
                row[0] for row in db.query(ERPUser.id).filter(ERPUser.tenant_id == tenant_id).all()
            }
        best_id, best_score, second_score = matcher.match_details(target_embedding, allowed_ids=allowed_ids)
        diag.update({"best_user_id": best_id, "score": best_score, "second_score": second_score})
        matched_user_id = best_id if (best_id is not None and best_score >= settings.SIMILARITY_THRESHOLD) else None
        confidence = best_score

        if not matched_user_id:
            # Check if any faces are registered at all
            total = db.query(FaceEmbedding).count()
            if total == 0:
                return {
                    "success": False,
                    "reason": "not_recognized",
                    "no_faces_registered": True,
                    "message": "No face profiles registered yet. Please register users at Face Registration first.",
                    "confidence": 0.0
                }
            logger.info(f"Face not recognized: score={confidence:.4f} < threshold={settings.SIMILARITY_THRESHOLD}")
            return {
                "success": False,
                "reason": "not_recognized",
                "message": "Face not recognized. Please try again or contact the administrator.",
                "confidence": 0.0
            }

        with _lock_for(matched_user_id):
            return self._mark_for_user(db, matched_user_id, confidence, img_bgr, ip_address, user_agent)

    def _mark_for_user(self, db: Session, matched_user_id: int, confidence: float, img_bgr,
                       ip_address: str, user_agent: str) -> Dict[str, Any]:
        # End any transaction started by earlier reads so the checks below see fresh data
        db.rollback()

        # Fetch user from ERP users table — row lock serialises concurrent check-ins
        user = db.query(ERPUser).filter(
            ERPUser.id == matched_user_id,
            ERPUser.is_active == 1,
            ERPUser.deleted_at == None
        ).with_for_update().first()

        if not user:
            db.rollback()
            return {
                "success": False,
                "reason": "inactive_user",
                "message": "Matched user account is inactive or has been removed.",
                "confidence": round(confidence, 4)
            }

        now   = datetime.now(IST)
        today = now.date()

        # 6. Cooldown: reject a new check-in within COOLDOWN_MINUTES of the last one today
        cooldown_seconds = max(0, int(settings.COOLDOWN_MINUTES)) * 60
        recent = db.query(Attendance).filter(
            Attendance.user_id == user.id,
            Attendance.attendance_date == today
        ).order_by(desc(Attendance.check_in)).first()

        if recent:
            db_check_in = _to_aware(recent.check_in)
            time_diff = (now - db_check_in).total_seconds()
            if time_diff < cooldown_seconds:
                db.rollback()
                wait_min = max(1, int((cooldown_seconds - time_diff + 59) // 60))
                logger.info(f"Cooldown rejection: user_id={user.id} name={user.name} last_check_in={time_diff:.0f}s_ago")
                return {
                    "success": True,
                    "already_checked_in": True,
                    "user_id": user.id,
                    "employee_id": user.employee_id,
                    "employee_code": user.employee_id,
                    "employee_name": user.name,
                    "designation": user.designation,
                    "confidence": round(confidence, 4),
                    "message": (
                        f"{user.name} was already checked in at {db_check_in.strftime('%I:%M %p')}. "
                        f"Please wait {wait_min} more minute(s) before scanning again."
                    )
                }

        # Save snapshot
        ts       = now.strftime("%Y%m%d_%H%M%S_%f")
        filename = f"att_user{user.id}_{ts}.jpg"
        save_path = os.path.join(settings.UPLOAD_ATTENDANCE_DIR, filename)
        rel_path  = f"uploads/attendance/{filename}"
        try:
            if not cv2.imwrite(save_path, img_bgr):
                rel_path = None
        except Exception:
            rel_path = None

        # 7. Write attendance record
        att = Attendance(
            user_id         = user.id,
            employee_id     = None,
            tenant_id       = user.tenant_id,
            school_id       = user.school_id,
            branch_id       = user.branch_id,
            attendance_date = today,
            check_in        = now,
            confidence      = round(confidence, 4),
            image_path      = rel_path,
            ip_address      = ip_address,
            user_agent      = user_agent
        )
        try:
            db.add(att)
            db.commit()
        except Exception:
            db.rollback()
            # Don't leave an orphan snapshot behind if the insert failed
            if rel_path:
                try:
                    os.remove(save_path)
                except OSError:
                    pass
            raise

        logger.info(f"✅ Attendance marked: {user.name} (user_id={user.id}) confidence={confidence:.3f}")

        return {
            "success": True,
            "already_checked_in": False,
            "user_id": user.id,
            "employee_id": user.employee_id,
            "employee_code": user.employee_id,
            "employee_name": user.name,
            "designation": user.designation,
            "confidence": round(confidence, 4),
            "check_in": now.strftime("%Y-%m-%d %H:%M:%S"),
            "message": f"Attendance marked for {user.name}!",
            "_image_path": rel_path
        }

    def get_attendance_history(
        self,
        db: Session,
        target_date: Optional[date] = None,
        user_id: Optional[int] = None,
        limit: int = 50,
        tenant_id: Optional[int] = None
    ) -> List[Dict[str, Any]]:
        """Retrieves face attendance logs joined with ERP users."""
        query = db.query(Attendance, ERPUser).join(ERPUser, Attendance.user_id == ERPUser.id)

        if target_date:
            query = query.filter(Attendance.attendance_date == target_date)
        if user_id:
            query = query.filter(Attendance.user_id == user_id)
        if tenant_id is not None:
            query = query.filter(ERPUser.tenant_id == tenant_id)

        records = query.order_by(desc(Attendance.check_in)).limit(limit).all()

        results = []
        for att, user in records:
            results.append({
                "id":              att.id,
                "user_id":         user.id,
                "employee_id":     user.employee_id,
                "employee_name":   user.name,
                "designation":     user.designation,
                "attendance_date": att.attendance_date.strftime("%Y-%m-%d") if att.attendance_date else None,
                "check_in":        att.check_in.strftime("%Y-%m-%d %H:%M:%S") if att.check_in else None,
                "confidence":      att.confidence,
                "image_path":      att.image_path,
                "ip_address":      att.ip_address,
            })
        return results
