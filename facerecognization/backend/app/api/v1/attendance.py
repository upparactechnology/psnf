import datetime
from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import Optional, List

from app.core.database import get_db
from app.api.deps import get_current_user
from app.models.attendance import AttendanceLog
from app.models.employee import Employee
from app.schemas.attendance import SyncRequest, AttendanceLogOut

router = APIRouter(prefix="/attendance", tags=["Attendance logs"])

@router.get("", response_model=dict)
async def get_attendance(
    date: Optional[str] = Query(None, description="Format YYYY-MM-DD"),
    department_id: Optional[int] = Query(None, description="Department ID"),
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    query = select(AttendanceLog).options(selectinload(AttendanceLog.employee))
    
    if date:
        try:
            parsed_date = datetime.datetime.strptime(date, "%Y-%m-%d").date()
            start = datetime.datetime.combine(parsed_date, datetime.time.min)
            end = datetime.datetime.combine(parsed_date, datetime.time.max)
            query = query.filter(AttendanceLog.clock_time >= start, AttendanceLog.clock_time <= end)
        except ValueError:
            raise HTTPException(status_code=400, detail="Invalid date format. Use YYYY-MM-DD.")
            
    if department_id:
        query = query.join(Employee).filter(Employee.department_id == department_id)
        
    result = await db.execute(query.order_by(AttendanceLog.clock_time.desc()))
    logs = result.scalars().all()
    
    formatted_items = []
    for log in logs:
        formatted_items.append({
            "id": log.id,
            "employee": {
                "employee_id": log.employee.employee_id,
                "name": f"{log.employee.first_name} {log.employee.last_name}"
            },
            "clock_time": log.clock_time.isoformat() + "Z",
            "clock_type": log.clock_type,
            "status": log.status,
            "device_id": log.device_id
        })
        
    return {"items": formatted_items}

@router.post("/sync", status_code=status.HTTP_200_OK)
async def sync_attendance_logs(
    req_in: SyncRequest,
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    synced_count = 0
    for log_item in req_in.logs:
        # Check if employee exists
        result = await db.execute(select(Employee).where(Employee.id == log_item.employee_id))
        emp = result.scalar_one_or_none()
        if not emp:
            # Skip logs with non-existent employees to prevent foreign key constraint violations
            continue
            
        new_log = AttendanceLog(
            employee_id=log_item.employee_id,
            clock_time=log_item.clock_time,
            clock_type=log_item.clock_type,
            status=log_item.status,
            device_id=log_item.device_id,
            is_synced=True
        )
        db.add(new_log)
        synced_count += 1
        
    if synced_count > 0:
        await db.commit()
        
    return {
        "success": True,
        "message": f"Successfully synchronized {synced_count} offline attendance logs.",
        "synced_records_count": synced_count
    }
