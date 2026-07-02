from pydantic import BaseModel, Field
import datetime
from typing import List

class AttendanceLogOut(BaseModel):
    id: int
    employee_id: int
    clock_time: datetime.datetime
    clock_type: str
    status: str
    device_id: str
    is_synced: bool

    class Config:
        from_attributes = True

class OfflineLogPayload(BaseModel):
    employee_id: int = Field(..., description="Internal DB employee ID")
    clock_time: datetime.datetime = Field(..., description="Device clock timestamp")
    clock_type: str = Field(..., description="CHECK_IN or CHECK_OUT")
    status: str = Field(..., description="Calculated offline attendance status")
    device_id: str = Field(..., description="Device unique identifier")

class SyncRequest(BaseModel):
    logs: List[OfflineLogPayload] = Field(..., description="Batch list of offline logs to synchronize")
