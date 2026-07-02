from typing import Optional, Sequence
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from app.models.employee import Employee, FaceEmbedding
from app.repositories.base import BaseRepository

class EmployeeRepository(BaseRepository[Employee]):
    def __init__(self, db: AsyncSession):
        super().__init__(Employee, db)

    async def get(self, id: int) -> Optional[Employee]:
        from app.models.shift import EmployeeShift
        result = await self.db.execute(
            select(Employee)
            .filter(Employee.id == id)
            .options(
                selectinload(Employee.shifts)
                .selectinload(EmployeeShift.shift)
            )
        )
        return result.scalars().first()

    async def get_by_employee_id(self, employee_id: str) -> Optional[Employee]:
        result = await self.db.execute(
            select(Employee)
            .filter(Employee.employee_id == employee_id)
            .options(selectinload(Employee.face_embeddings))
        )
        return result.scalars().first()

    async def get_by_email(self, email: str) -> Optional[Employee]:
        result = await self.db.execute(select(Employee).filter(Employee.email == email))
        return result.scalars().first()

    async def get_active_employees(self) -> Sequence[Employee]:
        result = await self.db.execute(select(Employee).filter(Employee.status == "ACTIVE"))
        return result.scalars().all()

class FaceEmbeddingRepository(BaseRepository[FaceEmbedding]):
    def __init__(self, db: AsyncSession):
        super().__init__(FaceEmbedding, db)

    async def get_by_employee(self, employee_id: int) -> Sequence[FaceEmbedding]:
        result = await self.db.execute(
            select(FaceEmbedding).filter(FaceEmbedding.employee_id == employee_id)
        )
        return result.scalars().all()
