from pydantic import BaseModel, Field
from typing import List, Optional
from datetime import datetime, date

class EmployeeCreate(BaseModel):
    employee_code: str = Field(..., example="EMP-1001")
    name: str = Field(..., example="John Doe")
    department: Optional[str] = Field(None, example="Engineering")
    designation: Optional[str] = Field(None, example="Senior Developer")

class EmployeeResponse(BaseModel):
    id: int
    employee_code: str
    name: str
    department: Optional[str] = None
    designation: Optional[str] = None
    status: str
    created_at: datetime

    class Config:
        from_attributes = True

class RegisterFaceRequest(BaseModel):
    employee_code: str
    name: str
    department: Optional[str] = None
    designation: Optional[str] = None
    images_base64: List[str] = Field(..., description="List of base64 encoded face images (up to 10 photos)")

class VerifyFaceRequest(BaseModel):
    image_base64: str = Field(..., description="Base64 encoded target image from camera")

class VerifyFaceResponse(BaseModel):
    success: bool
    employee_id: Optional[int] = None
    employee_code: Optional[str] = None
    employee_name: Optional[str] = None
    department: Optional[str] = None
    confidence: Optional[float] = None
    message: str
    already_checked_in: bool = False

class AttendanceRecordResponse(BaseModel):
    id: int
    employee_id: int
    employee_code: str
    employee_name: str
    department: Optional[str] = None
    attendance_date: date
    check_in: datetime
    confidence: float
    image_path: Optional[str] = None
    ip_address: Optional[str] = None
    user_agent: Optional[str] = None

    class Config:
        from_attributes = True

class StandardAPIResponse(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None
