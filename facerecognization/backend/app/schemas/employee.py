from pydantic import BaseModel, EmailStr, Field
from typing import Optional, List
import datetime

class DepartmentBase(BaseModel):
    name: str = Field(..., max_length=100)
    code: str = Field(..., max_length=10)

class DepartmentCreate(DepartmentBase):
    pass

class DepartmentOut(DepartmentBase):
    id: int

    class Config:
        from_attributes = True

class EmployeeBase(BaseModel):
    employee_id: str = Field(..., max_length=50)
    first_name: str = Field(..., max_length=100)
    last_name: str = Field(..., max_length=100)
    email: EmailStr
    phone: Optional[str] = Field(None, max_length=20)
    department_id: Optional[int] = None

class EmployeeCreate(EmployeeBase):
    pass

class EmployeeUpdate(BaseModel):
    first_name: Optional[str] = Field(None, max_length=100)
    last_name: Optional[str] = Field(None, max_length=100)
    email: Optional[EmailStr] = None
    phone: Optional[str] = Field(None, max_length=20)
    department_id: Optional[int] = None
    status: Optional[str] = None

class EmployeeOut(EmployeeBase):
    id: int
    status: str
    created_at: datetime.datetime

    class Config:
        from_attributes = True

class PaginatedEmployeeOut(BaseModel):
    items: List[EmployeeOut]
    total: int
    page: int
    pages: int
