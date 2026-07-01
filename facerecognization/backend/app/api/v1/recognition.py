import datetime
import json
from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.ext.asyncio import AsyncSession
from typing import List
from pydantic import BaseModel, Field
from app.core.database import get_db
from app.ai.pipeline import FaceAIPipeline
from app.services.attendance_engine import AttendanceEngine
from app.models.employee import Employee, FaceEmbedding

router = APIRouter(prefix="/recognition", tags=["Face Recognition"])

class MatchRequest(BaseModel):
    embedding: List[float] = Field(..., description="512-dimensional face embedding vector")
    timestamp: datetime.datetime = Field(..., description="Device capture timestamp")
    device_id: str = Field(..., description="Kiosk device identifier")
    liveness_score: float = Field(..., description="Anti-spoofing liveness confidence score")

class MatchResponse(BaseModel):
    matched: bool
    employee: dict
    attendance_log: dict

@router.post("/match", response_model=MatchResponse)
async def match_face(
    req_in: MatchRequest,
    request: Request,
    db: AsyncSession = Depends(get_db)
):
    request_id = getattr(request.state, "request_id", "unknown")
    
    # 1. Reject spoofing attempts
    if req_in.liveness_score < 0.85:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Face liveness check failed. Spoofing detected."
        )

    # Fetch active face embeddings from the database
    # For a scaling deployment, we load vectors in-memory or query spatial tables.
    from sqlalchemy.future import select
    from sqlalchemy.orm import selectinload
    
    result = await db.execute(
        select(FaceEmbedding)
        .options(selectinload(FaceEmbedding.employee))
    )
    embeddings = result.scalars().all()

    best_match_employee = None
    highest_score = 0.0
    matching_threshold = 0.65

    # 2. Iterate and evaluate vectors
    for item in embeddings:
        try:
            stored_vector = json.loads(item.embedding_vector)
            score = FaceAIPipeline.calculate_cosine_similarity(req_in.embedding, stored_vector)
            if score > highest_score:
                highest_score = score
                best_match_employee = item.employee
        except Exception:
            continue

    if not best_match_employee or highest_score < matching_threshold:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Face profile match score below threshold."
        )

    # 3. Log attendance event via AttendanceEngine
    engine = AttendanceEngine(db)
    log = await engine.log_clock_event(best_match_employee.id, req_in.timestamp, req_in.device_id)
    if not log:
         raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Event logged within cooldown interval. Double scan ignored."
         )

    return {
        "matched": True,
        "employee": {
            "id": best_match_employee.id,
            "employee_id": best_match_employee.employee_id,
            "first_name": best_match_employee.first_name,
            "last_name": best_match_employee.last_name
        },
        "attendance_log": {
            "id": log.id,
            "clock_time": log.clock_time,
            "clock_type": log.clock_type,
            "status": log.status
        }
    }
