import json
import os
import cv2
import numpy as np
from datetime import datetime
from typing import List, Tuple, Dict, Any, Optional
from sqlalchemy.orm import Session

from config import settings
from database.models import Employee, FaceEmbedding
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
        Registers an employee and processes up to 10 multi-angle face images.
        Extracts 512D embeddings, saves face snapshots, stores in DB, and updates memory cache.
        """
        # 1. Create or retrieve Employee record
        employee = db.query(Employee).filter(Employee.employee_code == req.employee_code).first()
        if not employee:
            employee = Employee(
                employee_code=req.employee_code,
                name=req.name,
                department=req.department,
                designation=req.designation,
                status="active"
            )
            db.add(employee)
            db.commit()
            db.refresh(employee)
        else:
            # Update employee metadata if provided
            employee.name = req.name
            if req.department:
                employee.department = req.department
            if req.designation:
                employee.designation = req.designation
            db.commit()

        processed_count = 0
        failed_count = 0
        errors = []

        # Limit to max 10 images
        images_to_process = req.images_base64[:10]

        for idx, img_b64 in enumerate(images_to_process):
            try:
                img_bgr = base64_to_cv2(img_b64)
                img_bgr = resize_image_max(img_bgr, max_dim=1024)

                # Quality and Face Detection Check
                det_res = self.detector.validate_and_detect(img_bgr)
                if not det_res.is_valid:
                    failed_count += 1
                    errors.append(f"Image {idx+1}: {det_res.error_message}")
                    continue

                # Extract Embedding
                embedding = self.extractor.extract_embedding(img_bgr)

                # Save face crop image
                timestamp_str = datetime.now().strftime("%Y%m%d_%H%M%S_%f")
                filename = f"emp_{employee.employee_code}_{timestamp_str}_{idx}.jpg"
                save_path = os.path.join(settings.UPLOAD_FACES_DIR, filename)
                rel_path = f"uploads/faces/{filename}"
                cv2.imwrite(save_path, img_bgr)

                # Save Embedding to DB
                embedding_json = json.dumps(embedding.tolist())
                face_emb_record = FaceEmbedding(
                    employee_id=employee.id,
                    embedding=embedding_json,
                    image_path=rel_path
                )
                db.add(face_emb_record)

                # Add to in-memory matcher cache
                matcher.add_employee_embedding(employee.id, embedding)
                processed_count += 1

            except Exception as e:
                failed_count += 1
                logger.error(f"Error processing image {idx+1} for employee {req.employee_code}: {str(e)}")
                errors.append(f"Image {idx+1}: Processing exception - {str(e)}")

        db.commit()

        if processed_count == 0:
            raise ValueError(f"Failed to register face embeddings. Reason: {'; '.join(errors)}")

        return {
            "success": True,
            "employee_id": employee.id,
            "employee_code": employee.employee_code,
            "employee_name": employee.name,
            "embeddings_registered": processed_count,
            "failed_images": failed_count,
            "errors": errors,
            "message": f"Successfully registered {processed_count} face embeddings for {employee.name}."
        }
