import datetime
from sqlalchemy import Integer, ForeignKey, DateTime, Enum, String, Boolean, Index
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.models.base import Base

class AttendanceLog(Base):
    __tablename__ = "attendance_logs"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    employee_id: Mapped[int] = mapped_column(Integer, ForeignKey("employees.id", ondelete="CASCADE"), nullable=False)
    clock_time: Mapped[datetime.datetime] = mapped_column(DateTime, nullable=False)
    clock_type: Mapped[str] = mapped_column(Enum("CHECK_IN", "CHECK_OUT", name="attendance_clock_types"), nullable=False)
    status: Mapped[str] = mapped_column(Enum("PRESENT", "LATE", "EARLY_LEAVE", "OVERTIME", "REGULAR", "INCOMPLETE", name="attendance_status_types"), nullable=False, default="REGULAR")
    device_id: Mapped[str] = mapped_column(String(100), nullable=False)
    is_synced: Mapped[bool] = mapped_column(Boolean, nullable=False, default=True)

    employee: Mapped["Employee"] = relationship("Employee", back_populates="attendance_logs")

    # Composite Index for optimized logging/query performance
    __table_args__ = (
        Index("idx_attendance_employee_time", "employee_id", "clock_time"),
    )
