import json
import os
import cv2
import numpy as np
from datetime import datetime
from typing import List, Dict, Any
from sqlalchemy.orm import Session

from config import settings
from database.models import ERPUser, FaceEmbedding
from models.schemas import RegisterFaceRequest
from utils.image_utils import base64_to_cv2, resize_image_max
from utils.logger import logger
from recognition.detector import FaceDetector
from recognition.embedding import EmbeddingExtractor
from recognition.matcher import matcher


class FaceService:
    def __init__(self, detector: FaceDetector, extractor: EmbeddingExtractor):
        self.detector = detector
        self.extractor = extractor

    def register_employee_faces(self, db: Session, req: RegisterFaceRequest) -> Dict[str, Any]:
        """
        Registers multi-angle face embeddings for an ERP user (from users table).
        Looks up the user by user_id, processes all submitted images,
        deletes old embeddings, and saves new InsightFace 512D vectors.
        """
        # 1. Fetch the ERP user — MUST exist in users table
        user = db.query(ERPUser).filter(
            ERPUser.id == req.user_id,
            ERPUser.deleted_at == None
        ).first()

        if not user:
            raise ValueError(
                f"User with ID {req.user_id} not found in the ERP users table. "
                "Please add this user at Staff → User Accounts first."
            )

        if not user.is_active:
            raise ValueError(f"User '{user.name}' (ID: {req.user_id}) is inactive. Activate the user first.")

        logger.info(f"Registering faces for user: {user.name} (ID: {user.id}, emp_id: {user.employee_id})")

        # 2. Delete existing embeddings for this user
        db.query(FaceEmbedding).filter(FaceEmbedding.user_id == user.id).delete()
        db.commit()

        processed_count = 0
        failed_count    = 0
        errors          = []
        first_image_path = None

        images_to_process = req.images_base64[:10]

        for idx, img_b64 in enumerate(images_to_process):
            try:
                img_bgr = base64_to_cv2(img_b64)
                img_bgr = resize_image_max(img_bgr, max_dim=1024)

                # Quality & face detection
                det_res = self.detector.validate_and_detect(img_bgr)
                if not det_res.is_valid:
                    failed_count += 1
                    errors.append(f"Angle {idx+1}: {det_res.error_message}")
                    continue

                # InsightFace embedding
                embedding = self.extractor.extract_embedding(img_bgr)

                # Save face image
                ts = datetime.now().strftime("%Y%m%d_%H%M%S_%f")
                filename = f"user_{user.id}_angle{idx}_{ts}.jpg"
                save_path = os.path.join(settings.UPLOAD_FACES_DIR, filename)
                rel_path  = f"uploads/faces/{filename}"
                cv2.imwrite(save_path, img_bgr)

                if first_image_path is None:
                    first_image_path = rel_path

                # Store embedding row
                emb_json = json.dumps(embedding.tolist())
                face_emb = FaceEmbedding(
                    user_id    = user.id,
                    employee_id= None,          # no longer used
                    embedding  = emb_json,
                    image_path = rel_path
                )
                db.add(face_emb)

                # Update in-memory matcher cache (keyed by user_id)
                matcher.add_employee_embedding(user.id, embedding)
                processed_count += 1

            except Exception as e:
                failed_count += 1
                logger.error(f"Error on image {idx+1} for user {user.id}: {str(e)}")
                errors.append(f"Angle {idx+1}: {str(e)}")

        db.commit()

        if processed_count == 0:
            raise ValueError(f"No embeddings saved. Errors: {'; '.join(errors)}")

        return {
            "success": True,
            "user_id": user.id,
            "employee_id": user.employee_id,
            "employee_code": user.employee_id,
            "employee_name": user.name,
            "designation": user.designation,
            "embeddings_registered": processed_count,
            "failed_images": failed_count,
            "errors": errors,
            "message": f"Successfully registered {processed_count} face angle(s) for {user.name}."
        }
