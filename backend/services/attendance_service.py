import os
import cv2
from datetime import datetime, date, timedelta
from typing import Dict, Any, Optional, List
from sqlalchemy.orm import Session
from sqlalchemy import desc

from config import settings
from database.models import Employee, Attendance, FaceEmbedding
from models.schemas import VerifyFaceRequest, VerifyFaceResponse
from utils.image_utils import base64_to_cv2, resize_image_max
from utils.logger import logger
from recognition.detector import FaceDetector
from recognition.embedding import EmbeddingExtractor
from recognition.liveness import verify_face_liveness
from recognition.matcher import matcher

class AttendanceService:
    def __init__(self, detector: FaceDetector, extractor: EmbeddingExtractor):
        self.detector = detector
        self.extractor = extractor

    def verify_and_mark_attendance(
        self,
        db: Session,
        req: VerifyFaceRequest,
        ip_address: str,
        user_agent: str
    ) -> Dict[str, Any]:
        """
        Processes single frame verification:
        1. Decode & Resize image
        2. Detect face & quality check (single face, blur, light)
        3. Check Liveness
        4. Extract embedding
        5. Cosine similarity match against in-memory cache
        6. Cooldown duplicate prevention check
        7. Record attendance event
        """
        # 1. Decode & Resize image
        img_bgr = base64_to_cv2(req.image_base64)
        img_bgr = resize_image_max(img_bgr, max_dim=1024)

        # 2. Face Detection & Quality Validation
        det_res = self.detector.validate_and_detect(img_bgr)
        if not det_res.is_valid:
            return {
                "success": False,
                "message": det_res.error_message or "Face verification failed due to quality check.",
                "confidence": 0.0
            }

        # 3. Liveness Check
        liveness_ok, liveness_msg = verify_face_liveness(img_bgr, det_res.bbox)
        if not liveness_ok:
            return {
                "success": False,
                "message": f"Security Check: {liveness_msg}",
                "confidence": 0.0
            }

        # 4. Extract Embedding
        target_embedding = self.extractor.extract_embedding(img_bgr)

        # 5. Perform Cosine Similarity Match against memory cache
        matched_emp_id, confidence = matcher.match(target_embedding, threshold=settings.SIMILARITY_THRESHOLD)

        if not matched_emp_id:
            return {
                "success": False,
                "message": f"Face not recognized. Similarity score ({confidence:.2f}) is below the threshold ({settings.SIMILARITY_THRESHOLD}).",
                "confidence": confidence
            }

        # Fetch Employee from DB
        employee = db.query(Employee).filter(Employee.id == matched_emp_id, Employee.status == "active").first()
        if not employee:
            return {
                "success": False,
                "message": "Matched employee record is inactive or not found.",
                "confidence": confidence
            }

        now = datetime.now()
        today = now.date()

        # 6. Cooldown duplicate check (10 minutes window)
        cooldown_threshold_time = now - timedelta(minutes=settings.COOLDOWN_MINUTES)
        recent_attendance = db.query(Attendance).filter(
            Attendance.employee_id == employee.id,
            Attendance.check_in >= cooldown_threshold_time
        ).order_by(desc(Attendance.check_in)).first()

        if recent_attendance:
            minutes_ago = int((now - recent_attendance.check_in).total_seconds() / 60.0)
            return {
                "success": True,
                "already_checked_in": True,
                "employee_id": employee.id,
                "employee_code": employee.employee_code,
                "employee_name": employee.name,
                "department": employee.department,
                "confidence": confidence,
                "message": f"Attendance already marked for {employee.name} {minutes_ago} mins ago (Cooldown period: {settings.COOLDOWN_MINUTES} mins)."
            }

        # Save snapshot image
        timestamp_str = now.strftime("%Y%m%d_%H%M%S_%f")
        filename = f"att_{employee.id}_{timestamp_str}.jpg"
        save_path = os.path.join(settings.UPLOAD_ATTENDANCE_DIR, filename)
        rel_path = f"uploads/attendance/{filename}"
        cv2.imwrite(save_path, img_bgr)

        # 7. Record Attendance in DB
        attendance_record = Attendance(
            employee_id=employee.id,
            attendance_date=today,
            check_in=now,
            confidence=confidence,
            image_path=rel_path,
            ip_address=ip_address,
            user_agent=user_agent
        )
        db.add(attendance_record)
        db.commit()
        db.refresh(attendance_record)

        logger.info(f"Attendance marked successfully for {employee.name} (Code: {employee.employee_code}) with confidence {confidence:.2f}")

        return {
            "success": True,
            "already_checked_in": False,
            "employee_id": employee.id,
            "employee_code": employee.employee_code,
            "employee_name": employee.name,
            "department": employee.department,
            "confidence": confidence,
            "check_in": now.strftime("%Y-%m-%d %H:%M:%S"),
            "message": f"Attendance marked successfully for {employee.name}!"
        }

    def get_attendance_history(
        self,
        db: Session,
        target_date: Optional[date] = None,
        employee_id: Optional[int] = None,
        limit: int = 50
    ) -> List[Dict[str, Any]]:
        """Retrieves history of attendance logs."""
        query = db.query(Attendance, Employee).join(Employee, Attendance.employee_id == Employee.id)

        if target_date:
            query = query.filter(Attendance.attendance_date == target_date)
        if employee_id:
            query = query.filter(Attendance.employee_id == employee_id)

        records = query.order_by(desc(Attendance.check_in)).limit(limit).all()

        results = []
        for att, emp in records:
            results.append({
                "id": att.id,
                "employee_id": emp.id,
                "employee_code": emp.employee_code,
                "employee_name": emp.name,
                "department": emp.department,
                "attendance_date": att.attendance_date.strftime("%Y-%m-%d"),
                "check_in": att.check_in.strftime("%Y-%m-%d %H:%M:%S"),
                "confidence": att.confidence,
                "image_path": att.image_path,
                "ip_address": att.ip_address,
                "user_agent": att.user_agent
            })
        return results
