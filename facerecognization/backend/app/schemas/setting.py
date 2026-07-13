from pydantic import BaseModel, Field

class SettingBase(BaseModel):
    company_name: str = Field(..., max_length=100)
    similarity_threshold: float = Field(..., ge=0.0, le=1.0)
    liveness_threshold: float = Field(..., ge=0.0, le=1.0)
    cooldown_seconds: int = Field(..., ge=0)
    voice_enabled: bool

class SettingUpdate(SettingBase):
    pass
