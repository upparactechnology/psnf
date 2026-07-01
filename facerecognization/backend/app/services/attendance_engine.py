import datetime
from typing import Optional, List
from sqlalchemy.ext.asyncio import AsyncSession
from app.models.employee import Employee
from app.models.shift import Shift
from app.models.attendance import AttendanceLog
from app.repositories.attendance import AttendanceRepository
from app.repositories.employee import EmployeeRepository

class AttendanceEngine:
    COOLDOWN_MINUTES = 5

    def __init__(self, db: AsyncSession):
        self.db = db
        self.attendance_repo = AttendanceRepository(db)
        self.employee_repo = EmployeeRepository(db)

    async def check_duplicate_event(self, employee_id: int, timestamp: datetime.datetime) -> bool:
        """
        Verifies if the employee clocked within the cooldown window (default: 5 minutes).
        Returns True if a duplicate exists, else False.
        """
        logs = await self.attendance_repo.get_employee_logs_for_day(employee_id, timestamp.date())
        if not logs:
            return False
        
        # Check last log timestamp
        last_log = logs[-1]
        time_diff = (timestamp - last_log.clock_time).total_seconds() / 60.0
        return abs(time_diff) < self.COOLDOWN_MINUTES

    def calculate_status(self, clock_time: datetime.datetime, shift: Shift, clock_type: str) -> str:
        """
        Determines the attendance status based on shift timings.
        """
        if clock_type == "CHECK_OUT":
            shift_end = datetime.datetime.combine(clock_time.date(), shift.end_time)
            # Handle cross-midnight end time shift
            if shift.end_time < shift.start_time:
                shift_end = shift_end + datetime.timedelta(days=1)
                
            if clock_time < shift_end:
                return "EARLY_LEAVE"
            elif (clock_time - shift_end).total_seconds() / 60.0 >= 30:
                return "OVERTIME"
            return "PRESENT"
            
        else: # CHECK_IN
            shift_start = datetime.datetime.combine(clock_time.date(), shift.start_time)
            grace_boundary = shift_start + datetime.timedelta(minutes=shift.grace_period_minutes)
            if clock_time > grace_boundary:
                return "LATE"
            return "PRESENT"

    async def log_clock_event(
        self, employee_id: int, timestamp: datetime.datetime, device_id: str
    ) -> Optional[AttendanceLog]:
        """
        Processes a raw clock event, categorizing it as Check-In or Check-Out
        and mapping it against assigned shifts.
        """
        # 1. Prevent duplicate scan events
        if await self.check_duplicate_event(employee_id, timestamp):
            return None

        # Fetch active logs for the day to determine clock category
        today_logs = await self.attendance_repo.get_employee_logs_for_day(employee_id, timestamp.date())
        clock_type = "CHECK_IN" if not today_logs else "CHECK_OUT"

        # 2. Match shift constraints (default to standard parameters if no shift mapping is found)
        employee = await self.employee_repo.get(employee_id)
        active_shift = None
        if employee and employee.shifts:
            # Pick first active shift mapping
            for emp_shift in employee.shifts:
                if emp_shift.start_date <= timestamp.date():
                    if not emp_shift.end_date or emp_shift.end_date >= timestamp.date():
                        active_shift = emp_shift.shift
                        break

        # Fallback default shift: 09:00 - 18:00
        if not active_shift:
            active_shift = Shift(
                name="Default Shift",
                start_time=datetime.time(9, 0),
                end_time=datetime.time(18, 0),
                grace_period_minutes=15,
                half_day_minutes=240
            )

        # 3. Calculate status and create log
        status = self.calculate_status(timestamp, active_shift, clock_type)
        
        log = AttendanceLog(
            employee_id=employee_id,
            clock_time=timestamp,
            clock_type=clock_type,
            status=status,
            device_id=device_id,
            is_synced=True
        )
        
        return await self.attendance_repo.create(log)
