import datetime
from sqlalchemy import Integer, String, Time, Date, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.models.base import Base

class Shift(Base):
    __tablename__ = "shifts"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(50), nullable=False)
    start_time: Mapped[datetime.time] = mapped_column(Time, nullable=False)
    end_time: Mapped[datetime.time] = mapped_column(Time, nullable=False)
    grace_period_minutes: Mapped[int] = mapped_column(Integer, nullable=False, default=15)
    half_day_minutes: Mapped[int] = mapped_column(Integer, nullable=False, default=240)

    employee_shifts: Mapped[list["EmployeeShift"]] = relationship("EmployeeShift", back_populates="shift", cascade="all, delete-orphan")

class EmployeeShift(Base):
    __tablename__ = "employee_shifts"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    employee_id: Mapped[int] = mapped_column(Integer, ForeignKey("employees.id", ondelete="CASCADE"), nullable=False)
    shift_id: Mapped[int] = mapped_column(Integer, ForeignKey("shifts.id", ondelete="CASCADE"), nullable=False)
    start_date: Mapped[datetime.date] = mapped_column(Date, nullable=False)
    end_date: Mapped[datetime.date] = mapped_column(Date, nullable=True)

    employee: Mapped["Employee"] = relationship("Employee", back_populates="shifts")
    shift: Mapped["Shift"] = relationship("Shift", back_populates="employee_shifts")
