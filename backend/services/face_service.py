import json
import os
import cv2
import numpy as np
from typing import List, Dict, Any
from sqlalchemy.orm import Session


from config import settings
from database.models import ERPUser, FaceEmbedding
from models.schemas import RegisterFaceRequest
from utils.image_utils import base64_to_cv2, resize_image_max
from utils.logger import logger
from utils.timeutil import now_ist
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

        if user and req.tenant_id is not None and user.tenant_id != req.tenant_id:
            user = None  # never register a user from another tenant

        if not user:
            raise ValueError(
                f"User with ID {req.user_id} not found in the ERP users table. "
                "Please add this user at Staff → User Accounts first."
            )

        if not user.is_active:
            raise ValueError(f"User '{user.name}' (ID: {req.user_id}) is inactive. Activate the user first.")

        logger.info(f"Registering faces for user: {user.name} (ID: {user.id}, emp_id: {user.employee_id})")

        processed_count = 0
        failed_count    = 0
        errors          = []
        new_rows: List[FaceEmbedding] = []
        new_vectors: List[np.ndarray] = []
        saved_files: List[str] = []

        images_to_process = req.images_base64[:settings.MAX_IMAGES_PER_REQUEST]

        # 2. Process all images first — existing embeddings stay untouched until we
        #    know at least one new embedding was produced.
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

                # InsightFace embedding (reuses the detection pass when possible)
                embedding = self.extractor.extract_embedding(img_bgr, face=det_res.face)

                # Save face image
                ts = now_ist().strftime("%Y%m%d_%H%M%S_%f")
                filename = f"user_{user.id}_angle{idx}_{ts}.jpg"
                save_path = os.path.join(settings.UPLOAD_FACES_DIR, filename)
                rel_path  = f"uploads/faces/{filename}"
                if cv2.imwrite(save_path, img_bgr):
                    saved_files.append(save_path)
                else:
                    rel_path = None

                new_rows.append(FaceEmbedding(
                    user_id    = user.id,
                    employee_id= None,          # no longer used
                    embedding  = json.dumps(embedding.tolist()),
                    image_path = rel_path
                ))
                new_vectors.append(embedding)
                processed_count += 1

            except ValueError as e:
                failed_count += 1
                errors.append(f"Angle {idx+1}: {str(e)}")
            except Exception as e:
                failed_count += 1
                logger.error(f"Error on image {idx+1} for user {user.id}: {str(e)}")
                errors.append(f"Angle {idx+1}: could not be processed.")

        if processed_count == 0:
            # Nothing changed in DB or cache — previous registration (if any) remains valid.
            raise ValueError(f"No embeddings saved. Errors: {'; '.join(errors)}")

        # 3. Replace old embeddings with the new ones in a single transaction
        try:
            db.query(FaceEmbedding).filter(FaceEmbedding.user_id == user.id).delete()
            db.add_all(new_rows)
            db.commit()
        except Exception:
            db.rollback()
            for path in saved_files:
                try:
                    os.remove(path)
                except OSError:
                    pass
            raise

        # 4. Keep the in-memory matcher cache identical to the DB (drop stale vectors)
        matcher.replace_employee(user.id, new_vectors)

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
