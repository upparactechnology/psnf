from pydantic import BaseModel, Field
import datetime

class ShiftBase(BaseModel):
    name: str = Field(..., max_length=50)
    start_time: datetime.time
    end_time: datetime.time
    grace_period_minutes: int = Field(default=15)
    half_day_minutes: int = Field(default=240)

class ShiftCreate(ShiftBase):
    pass

class ShiftOut(ShiftBase):
    id: int

    class Config:
        from_attributes = True
