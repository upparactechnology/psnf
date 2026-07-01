from pydantic import BaseModel, Field
from typing import Optional, List

class LoginRequest(BaseModel):
    username: str = Field(..., description="Administrator username")
    password: str = Field(..., description="Administrator password")

class TokenResponse(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int

class RefreshRequest(BaseModel):
    refresh_token: str

class ErrorDetail(BaseModel):
    field: Optional[str] = None
    detail: str

class APIEnvelope(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None
    errors: Optional[List[ErrorDetail]] = None
    timestamp: str
    request_id: str
