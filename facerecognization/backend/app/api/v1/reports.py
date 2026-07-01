import datetime
from fastapi import APIRouter, Depends, HTTPException, Request, status, Query
from fastapi.responses import StreamingResponse, Response
from sqlalchemy.ext.asyncio import AsyncSession
from typing import Optional, List
from app.core.database import get_db
from app.api.deps import get_current_user
from app.services.report_generator import ReportGenerator
from app.repositories.attendance import AttendanceRepository
from app.repositories.employee import EmployeeRepository

router = APIRouter(prefix="/reports", tags=["Reporting Services"])

async def get_report_records(db: AsyncSession, date: datetime.date) -> List[dict]:
    # Query database records and format for exporters
    attendance_repo = AttendanceRepository(db)
    employee_repo = EmployeeRepository(db)
    
    employees = await employee_repo.get_all(limit=1000)
    records = []
    
    for emp in employees:
        logs = await attendance_repo.get_employee_logs_for_day(emp.id, date)
        check_in = next((l.clock_time.strftime("%I:%M %p") for l in logs if l.clock_type == "CHECK_IN"), "N/A")
        check_out = next((l.clock_time.strftime("%I:%M %p") for l in logs if l.clock_type == "CHECK_OUT"), "N/A")
        status_val = logs[0].status if logs else "ABSENT"
        
        # Calculate total hours worked if both exist
        total_hours = 0.0
        in_log = next((l for l in logs if l.clock_type == "CHECK_IN"), None)
        out_log = next((l for l in logs if l.clock_type == "CHECK_OUT"), None)
        if in_log and out_log:
            total_hours = round((out_log.clock_time - in_log.clock_time).total_seconds() / 3600.0, 2)
            
        records.append({
            "employee_id": emp.employee_id,
            "employee_name": f"{emp.first_name} {emp.last_name}",
            "date": date.strftime("%Y-%m-%d"),
            "check_in": check_in,
            "check_out": check_out,
            "status": status_val,
            "total_hours": total_hours
        })
    return records

@router.get("/daily")
async def get_daily_reports(
    date: str = Query(..., description="Target date YYYY-MM-DD"),
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    try:
        parsed_date = datetime.datetime.strptime(date, "%Y-%m-%d").date()
    except ValueError:
        raise HTTPException(status_code=400, detail="Invalid date format. Use YYYY-MM-DD.")
        
    records = await get_report_records(db, parsed_date)
    return {
        "success": True,
        "message": f"Daily report records for {date} retrieved successfully.",
        "data": records,
        "errors": None,
        "timestamp": datetime.datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ"),
        "request_id": "req_report"
    }

@router.get("/export")
async def export_report(
    format: str = Query("csv", description="Format: csv, excel, pdf"),
    date: str = Query(..., description="Target date YYYY-MM-DD"),
    db: AsyncSession = Depends(get_db),
    current_user = Depends(get_current_user)
):
    try:
        parsed_date = datetime.datetime.strptime(date, "%Y-%m-%d").date()
    except ValueError:
        raise HTTPException(status_code=400, detail="Invalid date format. Use YYYY-MM-DD.")
        
    records = await get_report_records(db, parsed_date)
    
    if format.lower() == "csv":
        csv_content = ReportGenerator.generate_csv(records)
        return Response(
            content=csv_content,
            media_type="text/csv",
            headers={"Content-Disposition": f"attachment; filename=report_{date}.csv"}
        )
    elif format.lower() == "excel":
        excel_stream = ReportGenerator.generate_excel(records)
        return StreamingResponse(
            excel_stream,
            media_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            headers={"Content-Disposition": f"attachment; filename=report_{date}.xlsx"}
        )
    elif format.lower() == "pdf":
        pdf_stream = ReportGenerator.generate_pdf(records)
        return StreamingResponse(
            pdf_stream,
            media_type="application/pdf",
            headers={"Content-Disposition": f"attachment; filename=report_{date}.pdf"}
        )
    else:
        raise HTTPException(status_code=400, detail="Unsupported export format.")
