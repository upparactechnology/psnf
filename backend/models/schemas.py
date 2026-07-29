from pydantic import BaseModel, Field
from typing import List, Optional
from datetime import datetime, date


class RegisterFaceRequest(BaseModel):
    """
    Register face for a user from the ERP users table.
    Submit user_id (from /staff/users) — the system will look up their name/employee_id automatically.
    """
    user_id: int = Field(..., description="The ERP user ID (from staff/users page)")
    images_base64: List[str] = Field(..., description="List of base64-encoded face images (5-angle capture)")


class VerifyFaceRequest(BaseModel):
    image_base64: str = Field(..., description="Base64-encoded frame from camera")


class VerifyFaceResponse(BaseModel):
    success: bool
    user_id: Optional[int] = None
    employee_id: Optional[str] = None   # employee_id / code from users table
    employee_name: Optional[str] = None
    employee_code: Optional[str] = None
    department: Optional[str] = None
    designation: Optional[str] = None
    confidence: Optional[float] = None
    message: str
    already_checked_in: bool = False
    check_in: Optional[str] = None


class UserListItem(BaseModel):
    id: int
    name: str
    email: str
    employee_id: Optional[str] = None
    designation: Optional[str] = None
    is_active: int = 1
    embedding_count: int = 0

    class Config:
        from_attributes = True


class AttendanceRecordResponse(BaseModel):
    id: int
    user_id: Optional[int] = None
    employee_id: Optional[str] = None
    employee_name: str
    designation: Optional[str] = None
    attendance_date: date
    check_in: datetime
    confidence: float
    image_path: Optional[str] = None
    ip_address: Optional[str] = None

    class Config:
        from_attributes = True


class StandardAPIResponse(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None
