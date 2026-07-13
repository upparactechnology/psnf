from fastapi import APIRouter, Depends
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Dict, Any
from app.core.database import get_db
from app.api.deps import get_current_user
from app.models.auth import AuditLog

router = APIRouter(prefix="/audit-logs", tags=["Audit Trail Logs"])

@router.get("", response_model=List[Dict[str, Any]])
async def list_audit_logs(
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    result = await db.execute(
        select(AuditLog)
        .options(selectinload(AuditLog.user))
        .order_by(AuditLog.created_at.desc())
        .limit(100)
    )
    logs = result.scalars().all()
    
    return [
        {
            "id": log.id,
            "timestamp": log.created_at.isoformat(),
            "action": log.action,
            "details": log.details,
            "user_name": log.user.name or log.user.email if log.user else "System"
        }
        for log in logs
    ]
