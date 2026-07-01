import datetime
from typing import Sequence
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from app.models.attendance import AttendanceLog
from app.repositories.base import BaseRepository

class AttendanceRepository(BaseRepository[AttendanceLog]):
    def __init__(self, db: AsyncSession):
        super().__init__(AttendanceLog, db)

    async def get_by_date_range(
        self, start_date: datetime.datetime, end_date: datetime.datetime
    ) -> Sequence[AttendanceLog]:
        result = await self.db.execute(
            select(AttendanceLog)
            .filter(AttendanceLog.clock_time >= start_date)
            .filter(AttendanceLog.clock_time <= end_date)
        )
        return result.scalars().all()

    async def get_employee_logs_for_day(
        self, employee_id: int, date: datetime.date
    ) -> Sequence[AttendanceLog]:
        start = datetime.datetime.combine(date, datetime.time.min)
        end = datetime.datetime.combine(date, datetime.time.max)
        result = await self.db.execute(
            select(AttendanceLog)
            .filter(AttendanceLog.employee_id == employee_id)
            .filter(AttendanceLog.clock_time >= start)
            .filter(AttendanceLog.clock_time <= end)
            .order_by(AttendanceLog.clock_time.asc())
        )
        return result.scalars().all()

    async def get_unsynced_logs(self) -> Sequence[AttendanceLog]:
        result = await self.db.execute(
            select(AttendanceLog).filter(AttendanceLog.is_synced == False)
        )
        return result.scalars().all()
