from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import List
from app.core.database import get_db
from app.api.deps import get_current_user
from app.models.shift import Shift
from app.models.auth import AuditLog
from app.schemas.shift import ShiftCreate, ShiftOut

router = APIRouter(prefix="/shifts", tags=["Shift Templates"])

@router.get("", response_model=List[ShiftOut])
async def list_shifts(
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    result = await db.execute(select(Shift))
    return result.scalars().all()

@router.post("", response_model=ShiftOut, status_code=status.HTTP_201_CREATED)
async def create_shift(
    shift_in: ShiftCreate,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    # Check if a shift with the same name already exists
    existing_result = await db.execute(select(Shift).where(Shift.name == shift_in.name))
    existing_shift = existing_result.scalar_one_or_none()
    if existing_shift:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="A shift template with this name already exists."
        )

    new_shift = Shift(
        name=shift_in.name,
        start_time=shift_in.start_time,
        end_time=shift_in.end_time,
        grace_period_minutes=shift_in.grace_period_minutes,
        half_day_minutes=shift_in.half_day_minutes
    )
    db.add(new_shift)
    
    # Write audit log
    audit_log = AuditLog(
        user_id=current_user.id,
        action="SHIFT_CREATE",
        details=f"Created shift template '{new_shift.name}' ({new_shift.start_time}-{new_shift.end_time})"
    )
    db.add(audit_log)
    
    await db.commit()
    await db.refresh(new_shift)
    return new_shift
