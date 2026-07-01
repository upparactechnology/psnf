from typing import Optional
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from app.models.auth import User, AuditLog

async def get_user_by_username(db: AsyncSession, username: str) -> Optional[User]:
    result = await db.execute(select(User).filter(User.username == username))
    return result.scalars().first()

async def create_audit_log(db: AsyncSession, user_id: int, action: str, details: Optional[str] = None) -> AuditLog:
    log = AuditLog(user_id=user_id, action=action, details=details)
    db.add(log)
    await db.commit()
    await db.refresh(log)
    return log
