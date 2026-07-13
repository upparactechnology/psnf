from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from app.core.database import get_db
from app.api.deps import get_current_user
from app.models.setting import SystemSetting
from app.models.auth import AuditLog
from app.schemas.setting import SettingBase, SettingUpdate

router = APIRouter(prefix="/settings", tags=["System Settings"])

@router.get("", response_model=SettingBase)
async def get_settings(
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    result = await db.execute(select(SystemSetting))
    db_settings = result.scalars().all()
    
    settings_dict = {}
    for setting in db_settings:
        settings_dict[setting.key] = setting.value
        
    # Set default values if keys are missing from database
    return {
        "company_name": settings_dict.get("company_name", "My Company"),
        "similarity_threshold": float(settings_dict.get("similarity_threshold", "0.65")),
        "liveness_threshold": float(settings_dict.get("liveness_threshold", "0.85")),
        "cooldown_seconds": int(settings_dict.get("cooldown_seconds", "10")),
        "voice_enabled": settings_dict.get("voice_enabled", "true").lower() in ("true", "1", "yes")
    }

@router.post("", response_model=SettingBase)
async def update_settings(
    settings_in: SettingUpdate,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    updates = {
        "company_name": settings_in.company_name,
        "similarity_threshold": str(settings_in.similarity_threshold),
        "liveness_threshold": str(settings_in.liveness_threshold),
        "cooldown_seconds": str(settings_in.cooldown_seconds),
        "voice_enabled": "true" if settings_in.voice_enabled else "false"
    }

    changed_details = []
    
    for key, value in updates.items():
        result = await db.execute(select(SystemSetting).where(SystemSetting.key == key))
        db_setting = result.scalar_one_or_none()
        
        if db_setting:
            if db_setting.value != value:
                changed_details.append(f"{key} changed to {value}")
                db_setting.value = value
        else:
            changed_details.append(f"added {key}={value}")
            db_setting = SystemSetting(key=key, value=value)
            db.add(db_setting)

    if changed_details:
        audit_log = AuditLog(
            user_id=current_user.id,
            action="SETTINGS_UPDATE",
            details="Updated settings: " + ", ".join(changed_details)
        )
        db.add(audit_log)
        await db.commit()
    
    return settings_in
