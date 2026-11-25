from pydantic import BaseModel, Field, StringConstraints
from typing import List, Optional
from typing_extensions import Annotated
from datetime import datetime, date

# ~5 MB decoded image → ~7 MB of base64 text (+ data-URI prefix). Decoded size is
# re-checked in utils.image_utils.base64_to_cv2.
MAX_B64_LEN = (5 * 1024 * 1024 * 4) // 3 + 1024
Base64Image = Annotated[str, StringConstraints(max_length=MAX_B64_LEN)]

class RegisterFaceRequest(BaseModel):
    """
    Register face for a user from the ERP users table.
    Submit user_id (from /staff/users) — the system will look up their name/employee_id automatically.
    """
    user_id: int = Field(..., description="The ERP user ID (from staff/users page)")
    images_base64: List[Base64Image] = Field(..., max_length=10, description="List of base64-encoded face images (5-angle capture, max 10, max 5 MB each)")
    tenant_id: Optional[int] = Field(None, description="If set, the user must belong to this tenant (set by the PHP proxy)")


class VerifyFaceRequest(BaseModel):
    image_base64: Base64Image = Field(..., description="Base64-encoded frame from camera (max 5 MB)")
    tenant_id: Optional[int] = Field(None, description="Restrict matching to users of this tenant (set by the PHP proxy)")


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
