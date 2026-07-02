import math
from fastapi import APIRouter, Depends, HTTPException, Request, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from typing import Optional, List
from pydantic import BaseModel
from app.core.database import get_db
from app.api.deps import get_current_user
from app.schemas.employee import EmployeeCreate, EmployeeUpdate, EmployeeOut, PaginatedEmployeeOut
from app.models.employee import Employee
from app.crud.employee import (
    get_employee_by_company_id, get_employee_by_email, create_employee,
    get_employee_by_id, list_employees, update_employee, delete_employee
)

router = APIRouter(prefix="/employees", tags=["Employee Directory"])

@router.post("", response_model=EmployeeOut, status_code=status.HTTP_201_CREATED)
async def add_employee(
    emp_in: EmployeeCreate,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    existing_code = await get_employee_by_company_id(db, emp_in.employee_id)
    if existing_code:
        raise HTTPException(status_code=400, detail="Employee ID already exists.")
    
    existing_email = await get_employee_by_email(db, emp_in.email)
    if existing_email:
        raise HTTPException(status_code=400, detail="Email already registered.")
    
    db_employee = Employee(
        employee_id=emp_in.employee_id,
        first_name=emp_in.first_name,
        last_name=emp_in.last_name,
        email=emp_in.email,
        phone=emp_in.phone,
        department_id=emp_in.department_id
    )
    return await create_employee(db, db_employee)

@router.get("", response_model=PaginatedEmployeeOut)
async def get_employees(
    page: int = Query(1, ge=1),
    limit: int = Query(20, ge=1, le=100),
    search: Optional[str] = None,
    department_id: Optional[int] = None,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    skip = (page - 1) * limit
    items, total = await list_employees(db, skip=skip, limit=limit, search=search, department_id=department_id)
    
    return {
        "items": items,
        "total": total,
        "page": page,
        "pages": math.ceil(total / limit)
    }

@router.get("/{id}", response_model=EmployeeOut)
async def get_employee(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    employee = await get_employee_by_id(db, id)
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found.")
    return employee

@router.put("/{id}", response_model=EmployeeOut)
async def modify_employee(
    id: int,
    emp_in: EmployeeUpdate,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    employee = await get_employee_by_id(db, id)
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found.")
    
    return await update_employee(db, employee, emp_in.model_dump(exclude_unset=True))

@router.delete("/{id}", status_code=status.HTTP_204_NO_CONTENT)
async def remove_employee(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    success = await delete_employee(db, id)
    if not success:
        raise HTTPException(status_code=404, detail="Employee not found.")
    return None

class EnrollmentEmbeddingItem(BaseModel):
    angle: str
    vector: List[float]

class EnrollRequest(BaseModel):
    embeddings: List[EnrollmentEmbeddingItem]

@router.post("/{id}/enroll", status_code=status.HTTP_200_OK)
async def enroll_employee_face(
    id: int,
    req_in: EnrollRequest,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    employee = await get_employee_by_id(db, id)
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found.")
        
    import json
    from app.models.employee import FaceEmbedding
    from sqlalchemy import delete
    
    # Remove existing embeddings for this employee first to allow re-enrollment
    await db.execute(delete(FaceEmbedding).where(FaceEmbedding.employee_id == id))
    
    for item in req_in.embeddings:
        new_embedding = FaceEmbedding(
            employee_id=id,
            embedding_vector=json.dumps(item.vector),
            capture_angle=item.angle
        )
        db.add(new_embedding)
        
    await db.commit()
    
    return {
        "employee_id": id,
        "total_embeddings_enrolled": len(req_in.embeddings),
        "status": "ENROLLED"
    }

from fastapi import UploadFile, File, Form

@router.post("/register-with-face", status_code=status.HTTP_201_CREATED)
async def register_with_face(
    employee_id: str = Form(...),
    first_name: str = Form(...),
    last_name: str = Form(...),
    email: str = Form(...),
    phone: Optional[str] = Form(None),
    department_id: Optional[int] = Form(None),
    file: UploadFile = File(...),
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    # 1. Check if employee already exists
    existing_code = await get_employee_by_company_id(db, employee_id)
    if existing_code:
        raise HTTPException(status_code=400, detail="Employee ID already exists.")
    
    existing_email = await get_employee_by_email(db, email)
    if existing_email:
        raise HTTPException(status_code=400, detail="Email already registered.")
        
    # 2. Extract embedding from image
    import cv2
    import numpy as np
    import json
    from app.ai.pipeline import FaceAIPipeline
    from app.models.employee import FaceEmbedding
    
    contents = await file.read()
    nparr = np.frombuffer(contents, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if img is None:
        raise HTTPException(status_code=400, detail="Invalid image file format.")
        
    pipeline = FaceAIPipeline(model_dir="models")
    embedding = pipeline.extract_embedding(img)
    if embedding is None:
        raise HTTPException(status_code=400, detail="No face detected in the image. Please take a clearer photo.")
    embedding_list = embedding.tolist()
    
    # 3. Create employee
    db_employee = Employee(
        employee_id=employee_id,
        first_name=first_name,
        last_name=last_name,
        email=email,
        phone=phone,
        department_id=department_id
    )
    db.add(db_employee)
    await db.commit()
    await db.refresh(db_employee)
    
    # 4. Save face embedding
    new_embedding = FaceEmbedding(
        employee_id=db_employee.id,
        embedding_vector=json.dumps(embedding_list),
        capture_angle="front"
    )
    db.add(new_embedding)
    await db.commit()
    
    return {
        "status": "SUCCESS",
        "employee": {
            "id": db_employee.id,
            "employee_id": db_employee.employee_id,
            "first_name": db_employee.first_name,
            "last_name": db_employee.last_name,
            "email": db_employee.email
        }
    }
