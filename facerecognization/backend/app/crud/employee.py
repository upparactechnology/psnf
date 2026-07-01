from typing import Optional, List, Tuple
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy import func, or_
from app.models.employee import Employee, Department

async def get_department_by_code(db: AsyncSession, code: str) -> Optional[Department]:
    result = await db.execute(select(Department).filter(Department.code == code))
    return result.scalars().first()

async def create_department(db: AsyncSession, dept_in: Department) -> Department:
    db.add(dept_in)
    await db.commit()
    await db.refresh(dept_in)
    return dept_in

async def get_employee_by_id(db: AsyncSession, emp_id: int) -> Optional[Employee]:
    result = await db.execute(select(Employee).filter(Employee.id == emp_id))
    return result.scalars().first()

async def get_employee_by_company_id(db: AsyncSession, employee_id: str) -> Optional[Employee]:
    result = await db.execute(select(Employee).filter(Employee.employee_id == employee_id))
    return result.scalars().first()

async def get_employee_by_email(db: AsyncSession, email: str) -> Optional[Employee]:
    result = await db.execute(select(Employee).filter(Employee.email == email))
    return result.scalars().first()

async def list_employees(
    db: AsyncSession,
    skip: int = 0,
    limit: int = 20,
    search: Optional[str] = None,
    department_id: Optional[int] = None
) -> Tuple[List[Employee], int]:
    query = select(Employee)
    if department_id is not None:
        query = query.filter(Employee.department_id == department_id)
    if search:
        query = query.filter(
            or_(
                Employee.first_name.ilike(f"%{search}%"),
                Employee.last_name.ilike(f"%{search}%"),
                Employee.employee_id.ilike(f"%{search}%")
            )
        )
    
    # Count total matching query
    count_query = select(func.count()).select_from(query.subquery())
    total_result = await db.execute(count_query)
    total = total_result.scalar() or 0
    
    # Fetch paginated items
    result = await db.execute(query.offset(skip).limit(limit))
    items = result.scalars().all()
    
    return list(items), total

async def create_employee(db: AsyncSession, employee: Employee) -> Employee:
    db.add(employee)
    await db.commit()
    await db.refresh(employee)
    return employee

async def update_employee(db: AsyncSession, db_obj: Employee, update_data: dict) -> Employee:
    for field, value in update_data.items():
        if value is not None:
            setattr(db_obj, field, value)
    await db.commit()
    await db.refresh(db_obj)
    return db_obj

async def delete_employee(db: AsyncSession, emp_id: int) -> bool:
    employee = await get_employee_by_id(db, emp_id)
    if employee:
        await db.delete(employee)
        await db.commit()
        return True
    return False
