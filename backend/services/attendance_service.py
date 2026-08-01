import os
import cv2
from datetime import datetime, date, timedelta
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
        Full pipeline:
          1. Decode & resize image
          2. Detect face (InsightFace, mandatory)
          3. Liveness check
          4. Extract 512D embedding
          5. Match against in-memory cache (user_id keyed)
          6. Cooldown check (10 min)
          7. Write attendance row to DB (user_id)
        """
        # 1. Decode & resize
        img_bgr = base64_to_cv2(req.image_base64)
        img_bgr = resize_image_max(img_bgr, max_dim=1024)

        # 2. Face detection & quality
        det_res = self.detector.validate_and_detect(img_bgr)
        if not det_res.is_valid:
            return {"success": False, "message": det_res.error_message or "Face quality check failed.", "confidence": 0.0}

        # 3. Liveness check
        liveness_ok, liveness_msg = verify_face_liveness(img_bgr, det_res.bbox)
        if not liveness_ok:
            return {"success": False, "message": f"Security: {liveness_msg}", "confidence": 0.0}

        # 4. Extract embedding
        try:
            target_embedding = self.extractor.extract_embedding(img_bgr)
        except (ValueError, RuntimeError) as e:
            return {"success": False, "message": str(e), "confidence": 0.0}

        # 5. Cosine match
        matched_user_id, confidence = matcher.match(target_embedding, threshold=settings.SIMILARITY_THRESHOLD)

        if not matched_user_id:
            # Check if any faces are registered at all
            total = db.query(FaceEmbedding).count()
            if total == 0:
                return {
                    "success": False,
                    "no_faces_registered": True,
                    "message": "No face profiles registered yet. Please register users at Face Registration first.",
                    "confidence": 0.0
                }
            return {
                "success": False,
                "message": f"Face not recognized (score {confidence:.2f} < threshold {settings.SIMILARITY_THRESHOLD}).",
                "confidence": round(confidence, 4)
            }

        # Fetch user from ERP users table
        user = db.query(ERPUser).filter(
            ERPUser.id == matched_user_id,
            ERPUser.is_active == 1,
            ERPUser.deleted_at == None
        ).first()

        if not user:
            return {
                "success": False,
                "message": "Matched user account is inactive or has been removed.",
                "confidence": round(confidence, 4)
            }

        now   = datetime.now()
        today = now.date()

        # 6. Check if already checked in today
        recent = db.query(Attendance).filter(
            Attendance.user_id == user.id,
            Attendance.attendance_date == today
        ).order_by(desc(Attendance.check_in)).first()

        if recent:
            time_diff = (now - recent.check_in).total_seconds()
            if time_diff < 600:  # 10 minutes
                return {
                    "success": True,
                    "already_checked_in": True,
                    "user_id": user.id,
                    "employee_id": user.employee_id,
                    "employee_code": user.employee_id,
                    "employee_name": user.name,
                    "designation": user.designation,
                    "confidence": round(confidence, 4),
                    "message": f"Attendance already marked for {user.name} today ({recent.check_in.strftime('%I:%M %p')}). Please wait 10 minutes before scanning again."
                }

        # Save snapshot
        ts       = now.strftime("%Y%m%d_%H%M%S_%f")
        filename = f"att_user{user.id}_{ts}.jpg"
        save_path = os.path.join(settings.UPLOAD_ATTENDANCE_DIR, filename)
        rel_path  = f"uploads/attendance/{filename}"
        try:
            cv2.imwrite(save_path, img_bgr)
        except Exception:
            rel_path = None

        # 7. Write attendance record
        att = Attendance(
            user_id         = user.id,
            employee_id     = None,
            attendance_date = today,
            check_in        = now,
            confidence      = round(confidence, 4),
            image_path      = rel_path,
            ip_address      = ip_address,
            user_agent      = user_agent
        )
        db.add(att)
        db.commit()
        db.refresh(att)

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
            "message": f"Attendance marked for {user.name}!"
        }

    def get_attendance_history(
        self,
        db: Session,
        target_date: Optional[date] = None,
        user_id: Optional[int] = None,
        limit: int = 50
    ) -> List[Dict[str, Any]]:
        """Retrieves face attendance logs joined with ERP users."""
        query = db.query(Attendance, ERPUser).join(ERPUser, Attendance.user_id == ERPUser.id)

        if target_date:
            query = query.filter(Attendance.attendance_date == target_date)
        if user_id:
            query = query.filter(Attendance.user_id == user_id)

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
